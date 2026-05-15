<?php

namespace Database\Seeders;

use App\Models\Layanan;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $layanans = [
            [
                'nama_layanan' => 'Cuci Reguler',
                'harga' => 35000,
                'deskripsi' => 'Pembersihan dasar untuk sepatu harian dengan estimasi 3 hari.',
            ],
            [
                'nama_layanan' => 'Deep Clean',
                'harga' => 60000,
                'deskripsi' => 'Pembersihan menyeluruh untuk upper, midsole, outsole, dan insole.',
            ],
            [
                'nama_layanan' => 'Repaint Midsole',
                'harga' => 85000,
                'deskripsi' => 'Peremajaan warna midsole agar tampilan sepatu lebih bersih.',
            ],
            [
                'nama_layanan' => 'Unyellowing',
                'harga' => 70000,
                'deskripsi' => 'Treatment untuk mengurangi warna kuning pada outsole atau midsole.',
            ],
            [
                'nama_layanan' => 'Fast Clean',
                'harga' => 45000,
                'deskripsi' => 'Layanan cepat untuk kotoran ringan dengan estimasi 1 hari.',
            ],
        ];

        foreach ($layanans as $layanan) {
            Layanan::updateOrCreate(
                ['nama_layanan' => $layanan['nama_layanan']],
                [
                    'harga' => $layanan['harga'],
                    'deskripsi' => $layanan['deskripsi'],
                ]
            );
        }
    }
}
