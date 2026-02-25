<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Product permissions
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            
            // Order permissions
            'view-orders',
            'manage-orders',
            
            // User permissions
            'view-users',
            'manage-users',
            
            // Vendor permissions
            'manage-vendors',
            'approve-vendors',
            
            // Admin permissions
            'view-dashboard',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);
        $vendorRole->givePermissionTo([
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'view-orders',
            'manage-orders',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $customerRole->givePermissionTo([
            'view-products',
            'view-orders',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
