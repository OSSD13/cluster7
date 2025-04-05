<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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
            // If --fresh option is specified, refresh migrations and seed admin user
            if ($this->option('fresh')) {
                $this->info('Refreshing migrations...');
                $this->call('migrate:fresh');
                
                $this->info('Seeding admin user...');
                $this->call('db:seed', ['--class' => 'AdminUserSeeder']);
            }
            
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
} 