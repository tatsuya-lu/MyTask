<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;
    private User $user2;
    private User $user3;

    protected function setUp(): void
    {
        parent::setUp();
        
        // シーダーを実行
        $this->seed();
        
        // テストで使用するユーザーを取得
        $this->user1 = User::where('email', 'user1@example.com')->first();
        $this->user2 = User::where('email', 'user2@example.com')->first();
        $this->user3 = User::where('email', 'user3@example.com')->first();
    }

    public function test_task_policies()
    {
        // タスクを作成
        $user1Task = Task::factory()->create(['user_id' => $this->user1->id]);
        $user2Task = Task::factory()->create(['user_id' => $this->user2->id]);

        // 表示権限のテスト
        $this->assertTrue($this->user1->can('view', $user1Task));
        $this->assertFalse($this->user2->can('view', $user1Task));
        $this->assertFalse($this->user3->can('view', $user1Task));

        // 更新権限のテスト
        $this->assertTrue($this->user1->can('update', $user1Task));
        $this->assertFalse($this->user2->can('update', $user1Task));

        // アーカイブされたタスクの更新テスト
        // $archivedTask = Task::factory()->create([
        //     'user_id' => $this->user1->id,
        //     'is_archived' => true
        // ]);
        // $this->assertFalse($this->user1->can('update', $archivedTask));

        // 削除権限のテスト
        $this->assertTrue($this->user1->can('delete', $user1Task));
        $this->assertFalse($this->user2->can('delete', $user1Task));

        // アーカイブ権限テスト
        // $completedTask = Task::factory()->create([
        //     'user_id' => $this->user1->id,
        //     'status' => 'completed'
        // ]);
        // $this->assertTrue($this->user1->can('archive', $completedTask));

        // $pendingTask = Task::factory()->create([
        //     'user_id' => $this->user1->id,
        //     'status' => 'pending'
        // ]);
        // $this->assertFalse($this->user1->can('archive', $pendingTask));
    }

    public function test_tag_policies()
    {
        // タグを作成
        $user1Tag = Tag::factory()->create(['user_id' => $this->user1->id]);
        $user2Tag = Tag::factory()->create(['user_id' => $this->user2->id]);
        
        // タグ表示権限のテスト
        $this->assertTrue($this->user1->can('view', $user1Tag));
        $this->assertFalse($this->user2->can('view', $user1Tag));
        $this->assertFalse($this->user3->can('view', $user1Tag));
        
        // タグ更新権限のテスト
        $this->assertTrue($this->user1->can('update', $user1Tag));
        $this->assertFalse($this->user2->can('update', $user1Tag));
        $this->assertFalse($this->user3->can('update', $user1Tag));

        // タグ削除権限のテスト
        $this->assertTrue($this->user1->can('delete', $user1Tag));
        $this->assertFalse($this->user2->can('delete', $user1Tag));
        $this->assertTrue($this->user2->can('delete', $user2Tag));
    }
}