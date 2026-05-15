<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get()->keyBy('email');
        $layanans = Layanan::all()->keyBy('nama_layanan');

        $orders = [
            [
                'email' => 'budi@laundry.com',
                'kode_order' => 'ORD-202604230001',
                'tanggal_order' => '2026-04-20',
                'status' => 'pending',
                'alamat_pickup' => 'Jl. Kenanga No. 10, Bandung',
                'catatan' => 'Tolong fokus di bagian outsole yang sangat kotor.',
                'estimasi_selesai' => '2026-04-24',
                'metode_pembayaran' => 'transfer bank',
                'pembayaran_status' => 'pending',
                'details' => [
                    ['layanan' => 'Deep Clean', 'qty' => 1],
                    ['layanan' => 'Unyellowing', 'qty' => 1],
                ],
            ],
            [
                'email' => 'siti@laundry.com',
                'kode_order' => 'ORD-202604230002',
                'tanggal_order' => '2026-04-18',
                'status' => 'diproses',
                'alamat_pickup' => 'Jl. Mawar No. 5, Yogyakarta',
                'catatan' => 'Sepatu dipakai untuk kerja, mohon jangan terlalu lama.',
                'estimasi_selesai' => '2026-04-23',
                'metode_pembayaran' => 'e-wallet',
                'pembayaran_status' => 'lunas',
                'details' => [
                    ['layanan' => 'Cuci Reguler', 'qty' => 2],
                ],
            ],
            [
                'email' => 'andi@laundry.com',
                'kode_order' => 'ORD-202604230003',
                'tanggal_order' => '2026-04-15',
                'status' => 'selesai',
                'alamat_pickup' => 'Jl. Flamboyan No. 8, Surabaya',
                'catatan' => 'Ada noda lumpur di bagian upper.',
                'estimasi_selesai' => '2026-04-19',
                'metode_pembayaran' => 'cash',
                'pembayaran_status' => 'lunas',
                'details' => [
                    ['layanan' => 'Fast Clean', 'qty' => 1],
                    ['layanan' => 'Repaint Midsole', 'qty' => 1],
                ],
            ],
            [
                'email' => 'budi@laundry.com',
                'kode_order' => 'ORD-202604230004',
                'tanggal_order' => '2026-04-12',
                'status' => 'dibatalkan',
                'alamat_pickup' => 'Jl. Kenanga No. 10, Bandung',
                'catatan' => 'Customer batal karena sepatu sudah dibersihkan sendiri.',
                'estimasi_selesai' => null,
                'metode_pembayaran' => 'transfer bank',
                'pembayaran_status' => 'gagal',
                'details' => [
                    ['layanan' => 'Cuci Reguler', 'qty' => 1],
                ],
            ],
        ];

        foreach ($orders as $item) {
            $customer = $customers->get($item['email']);

            if (! $customer) {
                continue;
            }

            $totalHarga = 0;
            $detailRows = [];

            foreach ($item['details'] as $detail) {
                $layanan = $layanans->get($detail['layanan']);

                if (! $layanan) {
                    continue;
                }

                $harga = (int) $layanan->harga;
                $subtotal = $harga * $detail['qty'];

                $detailRows[] = [
                    'layanan_id' => $layanan->id,
                    'qty' => $detail['qty'],
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ];

                $totalHarga += $subtotal;
            }

            $order = Order::updateOrCreate(
                ['kode_order' => $item['kode_order']],
                [
                    'user_id' => $customer->id,
                    'tanggal_order' => $item['tanggal_order'],
                    'status' => $item['status'],
                    'total_harga' => $totalHarga,
                    'alamat_pickup' => $item['alamat_pickup'],
                    'catatan' => $item['catatan'],
                    'estimasi_selesai' => $item['estimasi_selesai'],
                ]
            );

            OrderDetail::where('order_id', $order->id)->delete();

            foreach ($detailRows as $detailRow) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'layanan_id' => $detailRow['layanan_id'],
                    'qty' => $detailRow['qty'],
                    'harga' => $detailRow['harga'],
                    'subtotal' => $detailRow['subtotal'],
                ]);
            }

            Pembayaran::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'metode_pembayaran' => $item['metode_pembayaran'],
                    'status' => $item['pembayaran_status'],
                    'jumlah_bayar' => $totalHarga,
                    'tanggal_bayar' => $item['pembayaran_status'] === 'lunas'
                        ? $item['tanggal_order'] . ' 10:00:00'
                        : null,
                ]
            );
        }
    }
}
