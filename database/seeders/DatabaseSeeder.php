<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@lasater.local',
                'display_name' => 'Administrator',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'is_active' => true,
                'force_password_change' => false,
            ]
        );
    }
}
