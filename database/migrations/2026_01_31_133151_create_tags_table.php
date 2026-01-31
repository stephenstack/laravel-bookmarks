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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable();
            $table->timestamps();

            // Enforce unique slug per user, but allow global duplicates if scoped correctly? 
            // Better: unique(['user_id', 'slug']) allows null user_id?
            // In SQL unique with nulls behaves differently across engines. 
            // For simplicity, we just won't enforce unique constraint at DB level for now or use application logic.
            // But let's verify if user_id + slug can be unique.
            $table->unique(['user_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
