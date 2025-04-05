<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Master Admin',
            'email' => 'admin@devperf.com',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin',
            'is_approved' => true,
        ]);
        
        $this->command->info('Master admin account created:');
        $this->command->info('Email: admin@devperf.com');
        $this->command->info('Password: Admin123!');
    }
}
