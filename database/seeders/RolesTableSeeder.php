<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'チームリーダー', 'slug' => 'leader']);
        Role::create(['name' => 'メンバー', 'slug' => 'member']);
    }
}