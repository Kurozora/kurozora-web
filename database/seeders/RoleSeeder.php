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
        Role::create(['name' => 'superAdmin']);
        /** @var Role $adminRole */
        $adminRole = Role::create(['name' => 'admin']);

        // Create permissions
        Permission::create(['name' => '*']);

        // Give permissions to roles
        $adminRole->givePermissionTo('*');
    }
}
