<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * テスト用の日本語説明文を生成
     */
    private function generateDescription(): string
    {
        $templates = [
            'このタスクは%sに関する作業です。%sを目標として進めています。',
            '%sについての調査や検討が必要なタスクです。%sまでに完了予定です。',
            '%sの実装を行うタスクです。%sに注意して作業を進めます。',
            '%sに関する定期的な確認タスクです。%sを意識して進めてください。',
            '%sの修正や改善を行うタスクです。%sの観点から作業を行います。'
        ];

        $subjects = [
            'データベース',
            'ユーザーインターフェース',
            'セキュリティ対策',
            'パフォーマンス改善',
            'バグ修正',
            '機能追加',
            'ドキュメント作成',
            'テスト実装',
            'コード最適化',
            'エラーハンドリング'
        ];

        $points = [
            '品質向上',
            '作業効率',
            'ユーザビリティ',
            '保守性',
            'スケジュール遵守',
            'コスト削減',
            'リソース効率',
            '拡張性',
            'セキュリティ',
            '安定性'
        ];

        $template = fake()->randomElement($templates);
        $subject = fake()->randomElement($subjects);
        $point = fake()->randomElement($points);

        return sprintf($template, $subject, $point);
    }

    public function definition(): array
    {
        static $taskNumber = 1;

        return [
            'user_id' => User::factory(),
            'title' => 'テスト用タスク' . $taskNumber++,
            'description' => fake()->optional(0.7)->passthrough(fn() => $this->generateDescription()),
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'status' => fake()->randomElement(TaskStatus::cases())->value,
            'progress' => fake()->numberBetween(0, 100),
            'due_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'is_archived' => false,
            'is_premium_feature' => false,
            'sort_order' => $taskNumber,
            'notify_before_due' => true,
            'notify_days_before' => fake()->randomElement([1, 3, 7])
        ];
    }

    public function resetTaskNumber(): self
    {
        $this->state(function (array $attributes) {
            static $taskNumber = 1;
            return [
                'title' => 'テスト用タスク' . $taskNumber++,
                'sort_order' => $taskNumber
            ];
        });

        return $this;
    }
}
