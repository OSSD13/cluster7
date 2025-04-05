<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we have a trello_api_secret setting
        $apiSecret = DB::table('settings')->where('key', 'trello_api_secret')->first();
        
        if ($apiSecret) {
            // Copy the value to trello_api_token
            DB::table('settings')->updateOrInsert(
                ['key' => 'trello_api_token'],
                [
                    'value' => $apiSecret->value,
                    'description' => 'Trello API Token',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            // Log the migration
            \Log::info('Migrated Trello API settings from trello_api_secret to trello_api_token');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible
    }
}; 