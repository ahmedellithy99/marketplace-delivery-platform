<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Marwan Allam',
            'phone' => '01201296861',
            'email' => 'admin@maywaay.com',
            'password' => Hash::make('123456789'),
            'role' => 'super_admin',
        ]);
    }
}
