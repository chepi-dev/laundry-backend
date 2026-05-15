<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Laundry',
                'email' => 'admin@laundry.com',
                'role' => 'admin',
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Melati No. 1, Jakarta',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@laundry.com',
                'role' => 'customer',
                'no_hp' => '081234567891',
                'alamat' => 'Jl. Kenanga No. 10, Bandung',
            ],
            [
                'name' => 'Siti Aisyah',
                'email' => 'siti@laundry.com',
                'role' => 'customer',
                'no_hp' => '081234567892',
                'alamat' => 'Jl. Mawar No. 5, Yogyakarta',
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi@laundry.com',
                'role' => 'customer',
                'no_hp' => '081234567893',
                'alamat' => 'Jl. Flamboyan No. 8, Surabaya',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'no_hp' => $user['no_hp'],
                    'alamat' => $user['alamat'],
                ]
            );
        }
    }
}
