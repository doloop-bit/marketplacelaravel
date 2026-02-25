<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@marketplace.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Create Vendor User
        $vendorUser = User::firstOrCreate(
            ['email' => 'vendor@marketplace.com'],
            [
                'name' => 'Test Vendor',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
                'email_verified_at' => now(),
            ]
        );
        $vendorUser->assignRole('vendor');

        // Create Vendor Profile
        Vendor::firstOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'shop_name' => 'Test Shop',
                'shop_slug' => 'test-shop',
                'shop_description' => 'This is a test shop',
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]
        );

        // Create Customer User
        $customer = User::firstOrCreate(
            ['email' => 'customer@marketplace.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('password'),
                'phone' => '081234567892',
                'email_verified_at' => now(),
            ]
        );
        $customer->assignRole('customer');

        $this->command->info('Users created successfully!');
        $this->command->info('Admin: admin@marketplace.com / password');
        $this->command->info('Vendor: vendor@marketplace.com / password');
        $this->command->info('Customer: customer@marketplace.com / password');
    }
}
