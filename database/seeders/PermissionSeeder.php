<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'case-list',
            'case-create',
            'case-show',
            'case-update',
            'case-delete',

            'hearing-create',
            'hearing-update',
            'hearing-delete',

            'document-upload',
            'document-delete',

            'payment-create',
            'payment-update',
            'payment-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::where('name', 'admin')->first();
        $advocate = Role::where('name', 'advocato')->first();
        $client = Role::where('name', 'client')->first();

        if ($admin) {
            $admin->givePermissionTo(Permission::all());
        }

        if ($advocate) {
            $advocate->givePermissionTo([
                'case-list',
                'case-create',
                'case-show',
                'case-update',
                'hearing-create',
                'hearing-update',
                'document-upload',
                'payment-create',
            ]);
        }

        if ($client) {
            $client->givePermissionTo([
                'case-list',
                'case-show',
            ]);
        }
    }
}