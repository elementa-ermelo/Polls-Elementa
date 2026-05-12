<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
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
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@elementa.local',
            'password' => bcrypt('Admin123!'),
            'is_admin' => true,
        ]);

        // Create test user
        $testUser = User::factory()->create([
            'name' => 'Test Account',
            'email' => 'testaccount@gmail.com',
            'password' => bcrypt('testaccount'),
            'is_admin' => false,
        ]);

        // Create sample polls with options for test user
        $poll1 = Poll::create([
            'title' => 'Favorite Color',
            'question' => 'What is your favorite color?',
            'type' => 'multiple_choice',
            'status' => 'active',
            'is_public' => true,
            'user_id' => $testUser->id,
        ]);

        PollOption::create(['poll_id' => $poll1->id, 'label' => 'Red', 'value' => 'red', 'position' => 0]);
        PollOption::create(['poll_id' => $poll1->id, 'label' => 'Blue', 'value' => 'blue', 'position' => 1]);
        PollOption::create(['poll_id' => $poll1->id, 'label' => 'Green', 'value' => 'green', 'position' => 2]);

        $poll2 = Poll::create([
            'title' => 'Best Restaurant',
            'question' => 'Where do you like to eat?',
            'type' => 'multiple_choice',
            'status' => 'active',
            'is_public' => true,
            'user_id' => $testUser->id,
        ]);

        PollOption::create(['poll_id' => $poll2->id, 'label' => 'Italian', 'value' => 'italian', 'position' => 0]);
        PollOption::create(['poll_id' => $poll2->id, 'label' => 'Asian', 'value' => 'asian', 'position' => 1]);
        PollOption::create(['poll_id' => $poll2->id, 'label' => 'Mexican', 'value' => 'mexican', 'position' => 2]);
    }
}
