<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SetupDevEnvironmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:setup {--no-key : Skip key generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup development environment with key generation and seed data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up development environment...');
        
        try {
            // Generate application key if not skipped
            if (!$this->option('no-key')) {
                $this->info('Generating application key...');
                $this->call('key:generate');
            }
            
            // Handle migrations in the correct order
            $this->handleMigrations();
            
            // Run the optimized seed
            $this->info('Seeding data...');
            $this->call('db:seed', ['--class' => 'AdminUserSeeder']);
            $this->call('db:seed', ['--class' => 'OptimizedReportSeeder']);
            
            $this->info('Development environment setup completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error setting up development environment: ' . $e->getMessage());
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
        $this->info('Running migrations in proper sequence...');
        
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