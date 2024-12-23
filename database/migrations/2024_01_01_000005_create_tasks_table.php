<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->name('fk_tasks_user');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('not_started');
            $table->integer('progress')->default(0);
            $table->date('due_date')->nullable();
            $table->foreignId('team_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->name('fk_tasks_team');
            $table->foreignId('assignee_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_premium_feature')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('notify_before_due')->default(true);
            $table->integer('notify_days_before')->default(1);

            $table->index(['user_id', 'status'], 'idx_tasks_user_status');
            $table->index(['user_id', 'due_date'], 'idx_tasks_user_due_date');
            $table->index(['team_id', 'status'], 'idx_tasks_team_status');
            $table->index(['is_archived'], 'idx_tasks_archived');
            $table->index(['sort_order'], 'idx_tasks_sort_order');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
