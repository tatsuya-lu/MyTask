<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // テーブルのクリア
        DB::table('team_user')->truncate();
        DB::table('task_tag')->truncate();
        DB::table('tasks')->truncate();
        DB::table('tags')->truncate();
        DB::table('teams')->truncate();
        DB::table('roles')->truncate();
        DB::table('users')->truncate();

        Schema::enableForeignKeyConstraints();

        $this->call([
            RolesTableSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}