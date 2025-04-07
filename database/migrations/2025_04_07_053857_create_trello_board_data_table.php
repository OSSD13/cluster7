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
        Schema::create('trello_board_data', function (Blueprint $table) {
            $table->id();
            $table->string('board_id')->index();
            $table->string('board_name')->nullable();
            $table->json('story_points')->nullable();
            $table->json('cards_by_list')->nullable();
            $table->json('member_points')->nullable();
            $table->json('board_details')->nullable();
            $table->json('backlog_data')->nullable();
            $table->timestamp('last_fetched_at');
            $table->timestamps();
            
            // Create a unique index on board_id
            $table->unique('board_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trello_board_data');
    }
};
