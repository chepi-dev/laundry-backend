<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function show(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        $pembayaran = $order->pembayaran;

        if (!$pembayaran) {
            return response()->json([
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $pembayaran
        ]);
    }

    public function adminShow($orderId)
    {
        $order = Order::with('pembayaran')->find($orderId);

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        if (!$order->pembayaran) {
            return response()->json([
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $order->pembayaran
        ], 200);
    }

    public function store(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        if ($order->pembayaran) {
            return response()->json([
                'message' => 'Pembayaran untuk order ini sudah ada'
            ], 400);
        }

        $validated = $request->validate([
            'metode_pembayaran' => 'required|string',
            'bukti_pembayaran' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $buktiPembayaranPath = null;

        if ($request->hasFile('bukti_pembayaran')) {
            $buktiPembayaranPath = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');
        }

        $pembayaran = Pembayaran::create([
            'order_id' => $order->id,
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'status' => 'pending',
            'jumlah_bayar' => $order->total_harga,
            'bukti_pembayaran' => $buktiPembayaranPath,
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil dibuat',
            'data' => $pembayaran
        ], 201);
    }

    public function updateStatus(Request $request, $orderId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,lunas,gagal',
            'bukti_pembayaran' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $pembayaran = Pembayaran::where('order_id', $orderId)->first();

        if (!$pembayaran) {
            return response()->json([
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        $dataUpdate = [
            'status' => $validated['status'],
        ];

        if ($request->hasFile('bukti_pembayaran')) {
            if ($pembayaran->bukti_pembayaran) {
                Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
            }

            $dataUpdate['bukti_pembayaran'] = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');
        }

        if ($validated['status'] === 'lunas') {
            $dataUpdate['tanggal_bayar'] = now();
        }

        $pembayaran->update($dataUpdate);

        return response()->json([
            'message' => 'Status pembayaran berhasil diupdate',
            'data' => $pembayaran
        ], 200);
    }
}
