<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create default organization
        $org = Organization::firstOrCreate(
            ['slug' => 'elementa'],
            [
                'name' => 'Elementa',
                'description' => 'Default organisatie',
                'email' => 'info@elementa.local',
            ]
        );

        // Update admin user
        $user = User::where('email', 'admin@elementa.local')->first();
        if ($user) {
            $user->update([
                'is_admin' => true,
                'organization_id' => $org->id,
            ]);
        }
    }
}
