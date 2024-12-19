<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // プレミアムユーザー（管理者）
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_premium' => true,
            'premium_expires_at' => now()->addYear(),
        ]);

        // プレミアムユーザー
        User::create([
            'name' => 'Premium User',
            'email' => 'premium@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_premium' => true,
            'premium_expires_at' => now()->addYear(),
        ]);

        // 一般ユーザー1
        User::create([
            'name' => 'Test User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_premium' => false,
        ]);

        // 一般ユーザー2
        User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_premium' => false,
        ]);

        // 一般ユーザー3
        User::create([
            'name' => 'Test User 3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_premium' => false,
        ]);
    }
}