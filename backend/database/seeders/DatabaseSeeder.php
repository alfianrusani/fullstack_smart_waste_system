<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat 1 Akun Admin Default
        User::factory()->admin()->create([
            'name' => 'Administrator Smart Waste',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin12345'), // Ubah password sesuai keinginan
            'phone' => '081234567890',
            'address' => 'Kantor Pusat Smart Waste, Jl. Lingkungan No. 1',
        ]);

        // Membuat 1 Akun User / Warga Default
        User::factory()->create([
            'name' => 'Warga Pengguna',
            'email' => 'user@gmail.com',
            'password' => Hash::make('user12345'), // Ubah password sesuai keinginan
            'phone' => '089876543210',
            'address' => 'Jl. Perintis Kemerdekaan No. 10, Makassar',
        ]);
    }
}
