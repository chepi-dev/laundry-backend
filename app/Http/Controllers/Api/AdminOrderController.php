<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'details.layanan', 'pembayaran'])
            ->latest()
            ->get()
            ->map(fn (Order $order) => $this->formatOrder($order));

        return response()->json([
            'message' => 'Semua data order berhasil diambil',
            'data' => $orders
        ], 200);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'details.layanan', 'pembayaran'])->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail order berhasil diambil',
            'data' => $this->formatOrder($order)
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,diproses,selesai,diambil,dibatalkan',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        $order->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'message' => 'Status order berhasil diupdate',
            'data' => $this->formatOrder($order->load(['user', 'details.layanan', 'pembayaran']))
        ], 200);
    }

    private function formatOrder(Order $order): array
    {
        $data = $order->toArray();
        $customerName = $order->user?->name;
        $customerNoHp = $order->user?->no_hp;
        $customer = [
            'id' => $order->user?->id,
            'name' => $customerName,
            'email' => $order->user?->email,
            'no_hp' => $customerNoHp,
            'alamat' => $order->user?->alamat,
        ];

        $data['customer'] = $customer;
        $data['customer_name'] = $customerName;
        $data['customer_no_hp'] = $customerNoHp;
        $data['nama_pelanggan'] = $customerName;
        $data['no_hp'] = $customerNoHp;

        if ($order->pembayaran) {
            $data['pembayaran']['customer'] = $customer;
            $data['pembayaran']['customer_name'] = $customerName;
            $data['pembayaran']['customer_no_hp'] = $customerNoHp;
            $data['pembayaran']['nama_pelanggan'] = $customerName;
            $data['pembayaran']['no_hp'] = $customerNoHp;
        }

        return $data;
    }
}
