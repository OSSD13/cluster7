<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\CustomSprintSeeder;

class SeedCustomSprintsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sprints:seed-custom {--fresh : Whether to refresh the database before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with completed weekly sprints and custom reports with specified team members';

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
        
        $this->info('Seeding custom sprints with specific teams and members...');
        $seeder = new CustomSprintSeeder();
        $seeder->setContainer($this->laravel);
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('Custom sprints seeded successfully!');
        $this->info('Created:');
        $this->info('- All completed weekly sprints from January 1st to before April 4th');
        $this->info('- Each sprint is exactly 1 week long');
        $this->info('- 2 teams (Team Alpha, Team Beta)');
        $this->info('- 2 reports per team per sprint');
        $this->info('- Reports include the specified team members');
        $this->info('- Bug data is only included in the last sprint reports');
        $this->info('- Backlog of bugs from previous sprints (after Sprint 1)');
        
        $this->info('You can view them at: ' . route('sprints.index'));
        
        return Command::SUCCESS;
    }
} 