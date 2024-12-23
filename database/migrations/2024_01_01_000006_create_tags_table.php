<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->default('#000000');
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name', 'team_id'], 'unique_tag_name');
        });

        DB::statement('ALTER TABLE tags ADD CONSTRAINT check_ids CHECK (user_id IS NOT NULL OR team_id IS NOT NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE tags DROP CONSTRAINT IF EXISTS check_ids');
        Schema::dropIfExists('tags');
    }
};
