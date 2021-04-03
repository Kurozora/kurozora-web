<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        /** @var Role[] $roles */
        $roles = [];

        $roles['admin'] = Role::create(['name' => 'admin']);

        // Create permissions
        Permission::create(['name' => '*']);

        // Give permissions to roles
        $roles['admin']->givePermissionTo('*');
    }
}
