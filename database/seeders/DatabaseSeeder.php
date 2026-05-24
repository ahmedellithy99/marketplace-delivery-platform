<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'phone' => '01000000001',
            'email' => 'admin@marketplace.test',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);
    }
}
