<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Seed a default super-admin user.
     *
     * @return void
     */
    public function run(): void
    {
        // Crea el usuario con factory
        $u = User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@hotel.com',
            'password' => bcrypt('secret123'),
        ]);

        // Asegúrate de que el rol 'super-admin' ya exista (corre primero RoleSeeder)
        $u->assignRole('super-admin');
    }
}
