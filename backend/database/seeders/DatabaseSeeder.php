<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test workers
        User::factory()->create([
            'name' => 'Worker One',
            'email' => 'worker1@farm.local',
            'password' => bcrypt('password'),
            'role' => 'worker'
        ]);

        User::factory()->create([
            'name' => 'Supervisor',
            'email' => 'supervisor@farm.local',
            'password' => bcrypt('password'),
            'role' => 'supervisor'
        ]);

        User::factory()->create([
            'name' => 'Farm Owner',
            'email' => 'owner@farm.local',
            'password' => bcrypt('password'),
            'role' => 'owner'
        ]);
    }
}
