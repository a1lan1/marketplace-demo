<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'user.profile.view', 'user.profile.update',
            'products.view', 'orders.create', 'orders.view-own', 'cart.manage',
            'products.create', 'products.edit-own', 'products.delete-own',
            'orders.view-own-seller', 'orders.update-status',
            'products.edit-any', 'products.delete-any', 'orders.view-all',
            'orders.update-any', 'users.view',
            'users.create', 'users.edit', 'users.delete',
            'roles.manage', 'permissions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $userRole = Role::create(['name' => RoleEnum::USER->value]);
        $userRole->givePermissionTo(['user.profile.view', 'user.profile.update']);

        $buyerRole = Role::create(['name' => RoleEnum::BUYER->value]);
        $buyerRole->givePermissionTo(['products.view', 'orders.create', 'orders.view-own', 'cart.manage']);

        $sellerRole = Role::create(['name' => RoleEnum::SELLER->value]);
        $sellerRole->givePermissionTo([
            'products.create', 'products.edit-own', 'products.delete-own',
            'orders.view-own-seller', 'orders.update-status',
        ]);

        $managerRole = Role::create(['name' => RoleEnum::MANAGER->value]);
        $managerRole->givePermissionTo([
            'products.edit-any', 'products.delete-any', 'orders.view-all',
            'orders.update-any', 'users.view',
        ]);

        $adminRole = Role::create(['name' => RoleEnum::ADMIN->value]);
        $adminRole->givePermissionTo(Permission::all());
    }
}
