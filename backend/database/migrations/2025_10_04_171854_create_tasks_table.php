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
            $table->uuid('id')->primary();
            $table->string('title'); 
            $table->text('description')->nullable(); 
            $table->json('required_skills')->nullable(); 
            $table->integer('estimated_duration')->nullable(); // in hours 
            $table->dateTime('deadline')->nullable(); 
            $table->enum('status', ['pending', 'assigned', 'in-progress', 'completed', 'cancelled'])->default('pending'); 
            $table->uuid('assigned_worker_id')->nullable(); 
            $table->uuid('created_by'); // company user_id
            $table->timestamps(); 

            $table->foreign('assigned_worker_id')->references('id')->on('users')->onDelete('set null'); 
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
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
