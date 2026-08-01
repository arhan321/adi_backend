<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\CrmAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class CrmRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (CrmAccess::allPermissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $superAdmin = Role::findOrCreate(
            CrmAccess::ROLE_SUPER_ADMIN,
            'web',
        );

        $management = Role::findOrCreate(
            CrmAccess::ROLE_MANAGEMENT,
            'web',
        );

        $cashier = Role::findOrCreate(
            CrmAccess::ROLE_CASHIER,
            'web',
        );

        $cashier->givePermissionTo(
            CrmAccess::cashierPermissions(),
        );

        $management->givePermissionTo(
            CrmAccess::managementPermissions(),
        );

        /*
         * Hanya permission CRM yang tidak sesuai role yang dicabut.
         * Permission lain dari Raugadh dan Filament Shield tetap dipertahankan.
         */
        $this->revokeDisallowedCrmPermissions(
            $cashier,
            CrmAccess::cashierPermissions(),
        );

        $this->revokeDisallowedCrmPermissions(
            $management,
            CrmAccess::managementPermissions(),
        );

        /*
         * Super admin mendapatkan seluruh permission yang tersedia.
         */
        $superAdmin->givePermissionTo(
            Permission::query()
                ->where('guard_name', 'web')
                ->get(),
        );

        /*
         * Admin default lama otomatis menjadi super admin.
         */
        $defaultAdmin = User::query()
            ->where('email', 'admin@admin.com')
            ->first();

        if ($defaultAdmin) {
            $defaultAdmin->assignRole($superAdmin);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $allowedPermissions
     */
    private function revokeDisallowedCrmPermissions(
        Role $role,
        array $allowedPermissions,
    ): void {
        $disallowedPermissions = array_diff(
            CrmAccess::allPermissions(),
            $allowedPermissions,
        );

        foreach ($disallowedPermissions as $permissionName) {
            if ($role->hasPermissionTo($permissionName)) {
                $role->revokePermissionTo($permissionName);
            }
        }
    }
}
