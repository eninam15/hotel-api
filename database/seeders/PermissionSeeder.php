<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'users.view', 'users.edit',
            'appointments.manage',
            // …otros permisos
        ];
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
    }
}
