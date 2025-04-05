<?php

namespace Database\Seeders;

// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            // Using the optimized report seeder that combines sprint and report creation
            // with a more realistic data structure including backlog items
            OptimizedReportSeeder::class,
            // Removed SprintSeeder as OptimizedReportSeeder handles both sprints and reports
        ]);
    }
}
