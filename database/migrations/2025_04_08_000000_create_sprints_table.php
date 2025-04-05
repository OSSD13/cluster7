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
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->integer('sprint_number')->index();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('duration')->default(7); // Sprint duration in days
            $table->enum('status', ['planned', 'active', 'completed'])->default('planned');
            $table->float('progress_percentage')->default(0);
            $table->integer('days_elapsed')->default(0);
            $table->integer('days_remaining')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprints');
    }
}; 