<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed stations first
        $this->call(StationSeeder::class);

        // Seed trips
        $this->call(TripSeeder::class);

        // Create admin user
        $this->call(AdminUserSeeder::class);

        // Create a test user
        User::factory()->create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
