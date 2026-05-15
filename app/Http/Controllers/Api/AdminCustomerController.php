<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(){
        $customers = User::where('role', 'customer')->get();

        return response()->json([
            'message' => 'Semua data customer berhasil diambil',
            'data' => $customers
        ]);
    }

    public function show($id)
    {
        $customer = User::where('role', 'customer')
            ->with(['orders.details.layanan', 'orders.pembayaran'])
            ->find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail customer berhasil diambil',
            'data' => $customer
        ], 200);
    }
}
