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
        Schema::create('task_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id'); 
            $table->uuid('worker_id'); 
            $table->enum('status', ['assigned', 'in-progress', 'completed']); 
            $table->dateTime('start_time')->nullable(); 
            $table->dateTime('end_time')->nullable(); 
            $table->text('comments')->nullable(); 
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete(); 
            $table->foreign('worker_id')->references('id')->on('users')->cascadeOnDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};
