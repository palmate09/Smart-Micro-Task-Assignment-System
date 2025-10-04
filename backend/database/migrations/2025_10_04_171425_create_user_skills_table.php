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
        Schema::create('user_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); 
            $table->uuid('skill_id');
            $table->unsignedTinyInteger('proficiency')->default(5); 
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); 
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_skills');
    }
};
