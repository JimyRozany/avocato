<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'api';

        $clientPermissions = [
            'cases.view-own',
            'cases.create',
            'cases.show',
            'documents.view-own',
            'sessions.view-own',
            'profile.view',
            'profile.update',
        ];

        $lawyerPermissions = [
            'cases.list',
            'cases.create',
            'cases.show',
            'cases.update',
            'cases.delete',
            'clients.view-assigned',
            'sessions.create',
            'sessions.update',
            'sessions.delete',
            'sessions.list',
            'documents.upload',
            'documents.delete',
            'documents.view',
            'lawyer-documents.manage',
            'warnings.view',
            'payments.create',
            'payments.update',
        ];

        $allPermissions = array_unique(array_merge($clientPermissions, $lawyerPermissions, [
            'clients.manage',
            'users.manage',
            'roles.manage',
            'settings.view',
            'contact-us.manage',
            'warnings.manage',
        ]));

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        $admin  = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $avocato = Role::firstOrCreate(['name' => 'avocato', 'guard_name' => $guard]);
        $client  = Role::firstOrCreate(['name' => 'client', 'guard_name' => $guard]);

        $admin->syncPermissions(Permission::all());

        $avocato->syncPermissions($lawyerPermissions);
        $client->syncPermissions($clientPermissions);
    }
}
