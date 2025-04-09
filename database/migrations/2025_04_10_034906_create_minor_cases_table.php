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
        Schema::create('minor_cases', function (Blueprint $table) {
            $table->id();
            $table->string('board_id');
            $table->string('sprint');
            $table->string('card');
            $table->text('description')->nullable();
            $table->string('member');
            $table->float('points');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Add indexes
            $table->index('board_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minor_cases');
    }
};
