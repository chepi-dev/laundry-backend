<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
}
