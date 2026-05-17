<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::firstOrCreate(
            ['phone' => '0100000001'],
            [
                'name' => 'مدير النظام',
                'email' => 'superadmin@marketplace.com',
                'password' => 'password',
                'role' => 'super_admin',
            ]
        );

        // Admin
        User::firstOrCreate(
            ['phone' => '0100000002'],
            [
                'name' => 'مدير العمليات',
                'email' => 'admin@marketplace.com',
                'password' => 'password',
                'role' => 'admin',
            ]
        );
    }
}
