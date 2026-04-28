<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin','guard_name' => 'api']);
        $avocatoRole = Role::firstOrCreate(['name' => 'avocato','guard_name' => 'api']);
        $clientRole = Role::firstOrCreate(['name' => 'client','guard_name' => 'api']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'type' => 'admin',
                'mobile' => '01020304050',
            ]
        );
        $avocato = User::firstOrCreate(
            ['email' => 'avocato@test.com'],
            [
                'name' => 'Avocato',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => false,
                'type' => 'avocato',
                'mobile' => '01020304050',
            ]
        );
        $client = User::firstOrCreate(
            ['email' => 'client@test.com'],
            [
                'name' => 'Client',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => false,
                'type' => 'client',
                'mobile' => '01020304050',
            ]
        );

        // Assign role
        $admin->assignRole($adminRole);
        $avocato->assignRole($avocatoRole);
        $client->assignRole($clientRole);
    }
}