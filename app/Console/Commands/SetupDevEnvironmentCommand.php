<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
            
            // Run the optimized seed with fresh migrations
            $this->info('Running fresh migrations and seeding data...');
            $this->call('seed:optimized', ['--fresh' => true]);
            
            $this->info('Development environment setup completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error setting up development environment: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
} 