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
            'products.view', 'products.create', 'products.edit-own',
            'products.delete-own', 'products.edit-any', 'products.delete-any',
            'cart.manage',
            'orders.view-own-seller', 'orders.update-status', 'orders.update-any',
            'orders.view-all', 'orders.create', 'orders.view-own',
            'user.profile.view', 'user.profile.update',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.manage', 'permissions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $userRole = Role::findOrCreate(RoleEnum::USER->value);
        $userRole->givePermissionTo(['user.profile.view', 'user.profile.update']);

        $buyerRole = Role::findOrCreate(RoleEnum::BUYER->value);
        $buyerRole->givePermissionTo(['products.view', 'orders.create', 'orders.view-own', 'cart.manage']);

        $sellerRole = Role::findOrCreate(RoleEnum::SELLER->value);
        $sellerRole->givePermissionTo([
            'products.create', 'products.edit-own', 'products.delete-own',
            'orders.view-own-seller',
        ]);

        $managerRole = Role::findOrCreate(RoleEnum::MANAGER->value);
        $managerRole->givePermissionTo([
            'products.edit-any', 'products.delete-any', 'orders.view-all',
            'orders.update-any', 'users.view', 'orders.update-status',
        ]);

        $adminRole = Role::findOrCreate(RoleEnum::ADMIN->value);
        $adminRole->givePermissionTo(Permission::all());
    }
}
