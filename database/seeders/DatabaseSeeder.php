<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 外部キー制約を一時的に無効化
        Schema::disableForeignKeyConstraints();

        // 関連テーブルのクリア
        DB::table('tags')->truncate();
        DB::table('users')->truncate();

        // 外部キー制約を再度有効化
        Schema::enableForeignKeyConstraints();

        // テストユーザーの作成
        User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        // 追加のランダムユーザーが必要な場合
        // User::factory(5)->create();
    }
}