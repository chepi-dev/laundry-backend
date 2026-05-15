<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        $layanan = Layanan::all();
        return response()->json([
            'data' => $layanan,
            'success' => true
        ], 200);
    }

    public function show($id)
    {
        $layanan = Layanan::find($id);

        if(!$layanan) {
            return response()->json([
                'massage' => 'Data layanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'data' => $layanan,
            'success' => true
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        $layanan = Layanan::create($validated);

        return response()->json([
            'data' => $layanan,
            'success' => true
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $layanan = Layanan::find($id);
        
        if(!$layanan) {
            return response()->json([
                'massage' => 'Data layanan tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        $layanan->update($validated);

        return response()->json([
            'data' => $layanan,
            'success' => true
        ], 200);
    }

    public function destroy($id)
    {
        $layanan = Layanan::find($id);

        if(!$layanan) {
            return response()->json([
                'massage' => 'Data layanan tidak ditemukan',
            ], 404);
        }

        $layanan->delete();

        return response()->json([
            'success' => true
        ], 200);
    }
}