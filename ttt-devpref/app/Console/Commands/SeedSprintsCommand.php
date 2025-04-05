<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\SprintSeeder;

class SeedSprintsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sprints:seed {--fresh : Whether to refresh the database before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with 5 sample sprints and reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fresh = $this->option('fresh');
        
        if ($fresh) {
            $this->info('Refreshing migrations...');
            $this->call('migrate:fresh');
            
            // Also create at least one admin user
            $this->call('db:seed', ['--class' => 'AdminUserSeeder']);
        }
        
        $this->info('Seeding sprints and reports...');
        $seeder = new SprintSeeder();
        $seeder->setContainer($this->laravel);
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('Sprints seeded successfully!');
        $this->info('You can view them at: ' . route('sprints.index'));
        
        return Command::SUCCESS;
    }
} 