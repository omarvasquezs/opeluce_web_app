<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default admin user if it doesn't exist
        if (!User::where('email', 'admin@opeluce.com')->exists()) {
            User::create([
                'name' => 'Administrador Opeluce',
                'username' => 'ADMIN',
                'email' => 'admin@opeluce.com',
                'password' => Hash::make('admin123'),
                'role' => true, // Admin role
                'email_verified_at' => now(),
            ]);
        }

        // Create a default normal user if it doesn't exist
        if (!User::where('email', 'user@opeluce.com')->exists()) {
            User::create([
                'name' => 'Usuario Demo',
                'username' => 'USERDEMO',
                'email' => 'user@opeluce.com',
                'password' => Hash::make('user123'),
                'role' => false, // Normal user role
                'email_verified_at' => now(),
            ]);
        }
    }
}
