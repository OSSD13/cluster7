<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SeedOptimizedReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:optimized {--fresh : Refresh migrations before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with optimized sprint reports and backlog data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to seed optimized reports and backlog data');
        
        try {
            // If --fresh option is specified, refresh migrations
            if ($this->option('fresh')) {
                $this->info('Handling migrations in the correct order...');
                $this->handleMigrations();
            }
            
            // Seed the admin user
            $this->info('Seeding admin user...');
            $this->call('db:seed', ['--class' => 'AdminUserSeeder']);
            
            $this->info('Creating optimized dataset:');
            $this->info('- Creating weekly sprints from January 1st to before April 4th');
            $this->info('- Creating 2 reports per team for each sprint');
            $this->info('- Adding backlog data to reports after Sprint 1');
            $this->info('- Teams include: Frontend Team, Backend Team, and QA Team');
            
            // Run the seeder
            $this->call('db:seed', ['--class' => 'OptimizedReportSeeder']);
            
            $this->info('Optimized reports and backlog data seeded successfully!');
            $this->info('You can now view your sprints at ' . url('/sprints'));
            
        } catch (\Exception $e) {
            $this->error('Error seeding optimized reports: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Handle migrations in the correct order to prevent dependency issues
     */
    protected function handleMigrations()
    {
        // Check if we have any migrations with "fix_double_encoded_sprint_report_data"
        $fixMigrationFiles = glob(database_path('migrations/*fix_double_encoded_sprint_report_data.php'));
        
        if (!empty($fixMigrationFiles)) {
            // Temporarily move fix migrations to ensure they run after tables are created
            $tempDir = storage_path('temp_migrations');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir);
            }
            
            $movedFiles = [];
            foreach ($fixMigrationFiles as $file) {
                $filename = basename($file);
                $this->info("Temporarily moving $filename to ensure correct migration sequence");
                $targetPath = $tempDir . '/' . $filename;
                File::copy($file, $targetPath);
                File::delete($file);
                $movedFiles[] = [
                    'original' => $file,
                    'temp' => $targetPath
                ];
            }
            
            // Run migrations without the fix migrations
            $this->info('Running base migrations...');
            $this->call('migrate:fresh');
            
            // Move the fix migrations back and run them
            foreach ($movedFiles as $filePair) {
                $this->info("Restoring " . basename($filePair['original']));
                File::copy($filePair['temp'], $filePair['original']);
                File::delete($filePair['temp']);
            }
            
            // Run just the fix migrations
            $this->info('Running fix migrations...');
            $this->call('migrate');
        } else {
            // No fix migrations, just run all migrations
            $this->info('Running all migrations...');
            $this->call('migrate:fresh');
        }
    }
} 