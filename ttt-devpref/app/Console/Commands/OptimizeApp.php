<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application for better performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting application optimization...');

        // Clear all caches first
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        
        // Optimize the application
        $this->info('Caching configuration...');
        $this->call('config:cache');
        
        $this->info('Caching routes...');
        $this->call('route:cache');
        
        $this->info('Compiling views...');
        $this->call('view:cache');
        
        $this->info('Running composer optimization...');
        $this->comment(shell_exec('composer dump-autoload -o'));
        
        $this->info('Optimizing class loader...');
        $this->call('optimize:clear');
        $this->call('optimize');
        
        $this->info('Application has been optimized successfully!');
        
        return Command::SUCCESS;
    }
} 