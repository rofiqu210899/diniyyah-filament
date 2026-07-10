<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $inputerRole = Role::firstOrCreate(['name' => 'inputer']);

        // Find the existing default admin user and assign the admin role
        $adminUser = User::where('email', 'admin@admin.com')->first();
        if ($adminUser) {
            // Assign role using Spatie method
            $adminUser->assignRole($adminRole);
            $this->command->info("Role 'admin' assigned to user admin@admin.com");
        } else {
            $this->command->warn("User admin@admin.com not found. Role not assigned.");
        }
    }
}
