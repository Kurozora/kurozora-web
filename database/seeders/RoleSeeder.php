<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdminRole = Role::create(['name' => 'superAdmin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'mod']);
        Role::create(['name' => 'editor']);

        // Create permissions
        Permission::create(['name' => '*']);

        // Give permissions to roles
        $superAdminRole->givePermissionTo('*');
    }
}
