<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestTagSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $premium = User::where('email', 'premium@example.com')->first();
        $user1 = User::where('email', 'user1@example.com')->first();
        
        // 管理者のタグ
        Tag::create([
            'user_id' => $admin->id,
            'name' => '重要',
            'color' => '#FF0000',
            'usage_count' => 10
        ]);
        
        Tag::create([
            'user_id' => $admin->id,
            'name' => '緊急',
            'color' => '#FF4500',
            'usage_count' => 5
        ]);
        
        // プレミアムユーザーのタグ
        Tag::create([
            'user_id' => $premium->id,
            'name' => 'プロジェクト',
            'color' => '#00FF00',
            'usage_count' => 5
        ]);
        
        Tag::create([
            'user_id' => $premium->id,
            'name' => '定期タスク',
            'color' => '#008000',
            'usage_count' => 8
        ]);
        
        // 一般ユーザーのタグ
        Tag::create([
            'user_id' => $user1->id,
            'name' => '個人タスク',
            'color' => '#0000FF',
            'usage_count' => 3
        ]);
    }
}