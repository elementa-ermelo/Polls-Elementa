<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;

class SetupAdmin extends Command
{
    protected $signature = 'app:setup-admin';
    protected $description = 'Setup admin account and default organization';

    public function handle()
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
            $this->info('✓ Admin account bijgewerkt!');
            $this->info("  Email: {$user->email}");
            $this->info("  Is Admin: Ja");
            $this->info("  Organisatie: {$org->name}");
        } else {
            $this->error('Admin account not found');
        }
    }
}
