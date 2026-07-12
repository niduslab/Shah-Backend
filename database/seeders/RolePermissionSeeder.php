<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Modules that get the full view/create/edit/delete permission set.
     */
    protected array $fullCrudModules = [
        'products',
        'categories',
        'brands',
        'variations',
        'inventory',
        'shipping',
        'users',
        'reviews',
        'flash_deals',
        'coupons',
        'promotions',
        'content',
        'media',
        'galleries',
        'campaigns',
        'orders',
        'pos',
        'returns',
        'refunds',
    ];

    /**
     * Modules that only ever need a "view" permission (read-only surfaces).
     */
    protected array $viewOnlyModules = [
        'dashboard',
        'analytics',
        'reports',
        'notifications',
        'contact_messages',
        'visitor_popups',
    ];

    public function run(): void
    {
        Cache::forget('spatie.permission.cache');

        $allPermissions = [];

        foreach ($this->fullCrudModules as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $allPermissions[] = "{$module}.{$action}";
            }
        }

        foreach ($this->viewOnlyModules as $module) {
            $allPermissions[] = "{$module}.view";
        }

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions($allPermissions);

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions($this->allExcept($allPermissions, [
            'users.create', 'users.edit', 'users.delete',
        ]));

        $orderHandler = Role::firstOrCreate(['name' => 'Order Handler', 'guard_name' => 'web']);
        $orderHandler->syncPermissions($this->only($allPermissions, [
            'dashboard.view',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
            'pos.view', 'pos.create', 'pos.edit', 'pos.delete',
            'returns.view', 'returns.create', 'returns.edit', 'returns.delete',
            'refunds.view', 'refunds.create', 'refunds.edit', 'refunds.delete',
            'reviews.view',
            'users.view',
        ]));

        $productHandler = Role::firstOrCreate(['name' => 'Product Handler', 'guard_name' => 'web']);
        $productHandler->syncPermissions($this->only($allPermissions, [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'brands.view', 'brands.create', 'brands.edit', 'brands.delete',
            'variations.view', 'variations.create', 'variations.edit', 'variations.delete',
            'flash_deals.view', 'flash_deals.create', 'flash_deals.edit', 'flash_deals.delete',
            'promotions.view', 'promotions.create', 'promotions.edit', 'promotions.delete',
            'coupons.view', 'coupons.create', 'coupons.edit', 'coupons.delete',
            'media.view', 'media.create', 'media.edit', 'media.delete',
            'galleries.view', 'galleries.create', 'galleries.edit', 'galleries.delete',
        ]));

        $stockManager = Role::firstOrCreate(['name' => 'Stock Manager', 'guard_name' => 'web']);
        $stockManager->syncPermissions($this->only($allPermissions, [
            'dashboard.view',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'products.view',
            'reports.view',
        ]));

        // Make sure nobody with an existing admin account gets locked out.
        User::query()
            ->where('user_type', 'admin')
            ->get()
            ->each(function (User $user) use ($admin) {
                if ($user->roles()->count() === 0) {
                    $user->assignRole($admin);
                }
            });
    }

    protected function only(array $all, array $wanted): array
    {
        return array_values(array_intersect($all, $wanted));
    }

    protected function allExcept(array $all, array $excluded): array
    {
        return array_values(array_diff($all, $excluded));
    }
}
