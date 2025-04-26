<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Admin@123'),
            'is_admin' => true,
            'image' => null,
        ], [
            'name' => 'Mohamed Walid',
            'email' => 'User@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('User123'),
            'is_admin' => false,
            'image' => null,
        ]);
    }
}
