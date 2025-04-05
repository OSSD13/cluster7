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
        Schema::create('sprint_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sprint_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('board_id')->nullable();
            $table->string('board_name')->nullable();
            $table->string('report_name');
            $table->text('notes')->nullable();
            $table->json('story_points_data')->nullable();
            $table->json('bug_cards_data')->nullable();
            $table->boolean('is_auto_generated')->default(false);
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('board_id');
            $table->index('report_name');
            $table->index('is_auto_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprint_reports');
    }
}; 