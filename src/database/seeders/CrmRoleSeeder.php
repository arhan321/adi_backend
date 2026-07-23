<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\CrmAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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

        /*
         * givePermissionTo digunakan agar permission lama
         * dari Raugadh dan Filament Shield tidak dihapus.
         */
        $cashier->givePermissionTo(
            CrmAccess::cashierPermissions(),
        );

        $management->givePermissionTo(
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
}