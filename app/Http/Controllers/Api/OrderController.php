<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Pembayaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class OrderController extends Controller
{
    public function index(Request $request){
        $orders = $request->user()->orders()
            ->with('details.layanan')
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders,
            'success' => true
        ], 200);
    }

    public function show(Request $request, $id){
        $order = $request->user()->orders()
            ->with('details.layanan')
            ->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail order berhasil diambil',
            'data' => $order
        ], 200);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'alamat_pickup' => 'required|string',
            'catatan' => 'nullable|string',
            'estimasi_selesai' => 'nullable|date',
            'layanans' => 'required|array|min:1',
            'layanans.*.layanan_id' => 'required|exists:layanans,id',
            'layanans.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'kode_order' => 'ORD-' . Carbon::now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'tanggal_order' => Carbon::now()->toDateString(),
                'status' => 'pending',
                'total_harga' => 0,
                'alamat_pickup' => $validated['alamat_pickup'],
                'catatan' => $validated['catatan'] ?? '',
                'estimasi_selesai' => $validated['estimasi_selesai'] ?? null,
            ]);

            $totalHarga = 0;

            foreach ($validated['layanans'] as $item) {
                $layanan = Layanan::findOrFail($item['layanan_id']);
                $subtotal = $layanan->harga * $item['qty'];

                OrderDetail::create([
                    'order_id' => $order->id,
                    'layanan_id' => $layanan->id,
                    'qty' => $item['qty'],
                    'harga' => $layanan->harga,
                    'subtotal' => $subtotal,
                ]);

                $totalHarga += $subtotal;
            }

            $order->update([
                'total_harga' => $totalHarga
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data' => $order->load('details.layanan')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order gagal dibuat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeWalkIn(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'name' => 'required_without:customer_id|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_hp' => 'required_without:customer_id|nullable|string|max:20',
            'alamat' => 'required_without:customer_id|nullable|string',
            'alamat_pickup' => 'nullable|string',
            'catatan' => 'nullable|string',
            'estimasi_selesai' => 'nullable|date',
            'layanan_id' => 'required_without:layanans|exists:layanans,id',
            'qty' => 'required_without:layanans|integer|min:1',
            'layanans' => 'nullable|array|min:1',
            'layanans.*.layanan_id' => 'required|exists:layanans,id',
            'layanans.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $customer = $this->resolveWalkInCustomer($validated);
            $items = $validated['layanans'] ?? [[
                'layanan_id' => $validated['layanan_id'],
                'qty' => $validated['qty'],
            ]];

            $order = Order::create([
                'user_id' => $customer->id,
                'kode_order' => 'ORD-' . Carbon::now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'tanggal_order' => Carbon::now()->toDateString(),
                'status' => 'selesai',
                'total_harga' => 0,
                'alamat_pickup' => $validated['alamat_pickup'] ?? $customer->alamat ?? '-',
                'catatan' => $validated['catatan'] ?? '',
                'estimasi_selesai' => $validated['estimasi_selesai'] ?? null,
            ]);

            $totalHarga = 0;

            foreach ($items as $item) {
                $layanan = Layanan::findOrFail($item['layanan_id']);
                $qty = (int) $item['qty'];
                $subtotal = (int) $layanan->harga * $qty;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'layanan_id' => $layanan->id,
                    'qty' => $qty,
                    'harga' => $layanan->harga,
                    'subtotal' => $subtotal,
                ]);

                $totalHarga += $subtotal;
            }

            $order->update([
                'total_harga' => $totalHarga,
            ]);

            Pembayaran::create([
                'order_id' => $order->id,
                'metode_pembayaran' => 'cash',
                'status' => 'lunas',
                'jumlah_bayar' => $totalHarga,
                'tanggal_bayar' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cash di toko berhasil dibuat',
                'data' => $order->load(['user', 'details.layanan', 'pembayaran']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order cash di toko gagal dibuat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function resolveWalkInCustomer(array $validated): User
    {
        if (! empty($validated['customer_id'])) {
            return User::where('role', 'customer')->findOrFail($validated['customer_id']);
        }

        $email = $validated['email'] ?? null;

        if ($email) {
            $existingCustomer = User::where('email', $email)->first();

            if ($existingCustomer) {
                $existingCustomer->update([
                    'name' => $validated['name'] ?? $existingCustomer->name,
                    'no_hp' => $validated['no_hp'] ?? $existingCustomer->no_hp,
                    'alamat' => $validated['alamat'] ?? $existingCustomer->alamat,
                    'role' => 'customer',
                ]);

                return $existingCustomer;
            }
        }

        return User::create([
            'name' => $validated['name'],
            'email' => $email ?: 'walkin-' . Str::uuid() . '@stmiklaundry.local',
            'password' => Hash::make(Str::random(16)),
            'role' => 'customer',
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ]);
    }
}
