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
            ->get();

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
            'data' => $order
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
            'data' => $order->load(['user', 'details.layanan', 'pembayaran'])
        ], 200);
    }
}
