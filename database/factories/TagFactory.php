<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        static $tagNumber = 1;

        return [
            'user_id' => User::factory(),
            'name' => 'テストタグ' . $tagNumber++,
            'color' => fake()->randomElement([
                '#FF0000', // 赤
                '#00FF00', // 緑
                '#0000FF', // 青
                '#FFFF00', // 黄
                '#FF00FF', // マゼンタ
                '#00FFFF', // シアン
                '#FFA500', // オレンジ
                '#800080', // 紫
                '#008000', // 深緑
                '#000080'  // ネイビー
            ]),
            'usage_count' => fake()->numberBetween(0, 100),
        ];
    }

    public function resetTagNumber(): self
    {
        static $tagNumber = 1;
        return $this->state(function (array $attributes) use (&$tagNumber) {
            return [
                'name' => 'テストタグ' . $tagNumber++,
            ];
        });
    }
}