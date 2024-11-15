<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Tag;
use App\Models\Team;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // プレミアムユーザーの作成
        $premiumUser = User::create([
            'name' => 'プレミアムユーザー',
            'email' => 'premium@example.com',
            'password' => Hash::make('password'),
            'is_premium' => true,
            'premium_expires_at' => now()->addYear(),
        ]);

        // 一般ユーザーの作成
        $normalUser = User::create([
            'name' => '一般ユーザー',
            'email' => 'normal@example.com',
            'password' => Hash::make('password'),
        ]);

        // テストユーザーの作成
        $testUsers = User::factory(3)->create();

        // タグの作成
        $tags = [
            ['name' => '仕事', 'color' => '#FF0000'],
            ['name' => 'プライベート', 'color' => '#00FF00'],
            ['name' => '緊急', 'color' => '#FF6B6B'],
            ['name' => '重要', 'color' => '#4ECDC4'],
        ];

        foreach ($tags as $tag) {
            // プレミアムユーザーのタグ
            Tag::create(array_merge($tag, ['user_id' => $premiumUser->id]));
            // 一般ユーザーのタグ
            Tag::create(array_merge($tag, ['user_id' => $normalUser->id]));
        }

        // タスクの作成
        $taskStatuses = ['not_started', 'in_progress', 'completed'];
        $taskPriorities = ['low', 'medium', 'high'];

        // プレミアムユーザーのタスク
        for ($i = 0; $i < 10; $i++) {
            $task = Task::create([
                'user_id' => $premiumUser->id,
                'title' => "プレミアムユーザーのタスク {$i}",
                'description' => "タスクの説明 {$i}",
                'status' => $taskStatuses[array_rand($taskStatuses)],
                'priority' => $taskPriorities[array_rand($taskPriorities)],
                'progress' => rand(0, 100),
                'due_date' => now()->addDays(rand(1, 30)),
            ]);

            // タグの関連付け
            $task->tags()->attach(
                Tag::where('user_id', $premiumUser->id)
                    ->inRandomOrder()
                    ->take(rand(1, 3))
                    ->pluck('id')
            );
        }

        // 一般ユーザーのタスク
        for ($i = 0; $i < 5; $i++) {
            $task = Task::create([
                'user_id' => $normalUser->id,
                'title' => "一般ユーザーのタスク {$i}",
                'description' => "タスクの説明 {$i}",
                'status' => $taskStatuses[array_rand($taskStatuses)],
                'priority' => $taskPriorities[array_rand($taskPriorities)],
                'progress' => rand(0, 100),
                'due_date' => now()->addDays(rand(1, 30)),
            ]);

            // タグの関連付け
            $task->tags()->attach(
                Tag::where('user_id', $normalUser->id)
                    ->inRandomOrder()
                    ->take(rand(1, 3))
                    ->pluck('id')
            );
        }

        // チームの作成（プレミアムユーザーのみ）
        $team1 = Team::create([
            'name' => '開発チーム',
            'description' => 'アプリケーション開発チーム',
            'owner_id' => $premiumUser->id,
        ]);

        $team2 = Team::create([
            'name' => 'マーケティングチーム',
            'description' => 'マーケティング戦略チーム',
            'owner_id' => $premiumUser->id,
        ]);

        // チームメンバーの追加
        $leaderRole = Role::where('slug', 'leader')->first();
        $memberRole = Role::where('slug', 'member')->first();

        // チーム1のメンバー追加
        $team1->members()->attach([
            $premiumUser->id => ['role_id' => $leaderRole->id],
            $testUsers[0]->id => ['role_id' => $memberRole->id],
            $testUsers[1]->id => ['role_id' => $memberRole->id],
        ]);

        // チーム2のメンバー追加
        $team2->members()->attach([
            $premiumUser->id => ['role_id' => $leaderRole->id],
            $testUsers[1]->id => ['role_id' => $memberRole->id],
            $testUsers[2]->id => ['role_id' => $memberRole->id],
        ]);

        // チームタスクの作成
        for ($i = 0; $i < 5; $i++) {
            Task::create([
                'user_id' => $premiumUser->id,
                'team_id' => $team1->id,
                'title' => "チームタスク {$i}",
                'description' => "チームタスクの説明 {$i}",
                'status' => $taskStatuses[array_rand($taskStatuses)],
                'priority' => $taskPriorities[array_rand($taskPriorities)],
                'progress' => rand(0, 100),
                'due_date' => now()->addDays(rand(1, 30)),
            ]);
        }
    }
}