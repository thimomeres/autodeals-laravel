<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Wajib untuk enkripsi password

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat akun admin tiruan untuk Pak Rendra
        User::create([
            'name' => 'Timo',
            'email' => 'timo@gmail.com',
            'password' => Hash::make('password123'), // Password dienkripsi demi keamanan
        ]);
         User::create([
            'name' => 'Mima',
            'email' => 'mima@gmail.com',
            'password' => Hash::make('password123'), // Password dienkripsi demi keamanan
        ]);
        

    }
}