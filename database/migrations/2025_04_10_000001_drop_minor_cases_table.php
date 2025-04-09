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
        Schema::dropIfExists('minor_cases');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't need to recreate the table in the down method
        // as the original migration will handle that
    }
}; 