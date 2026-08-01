<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

final class CrmAccess
{
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_MANAGEMENT = 'manajemen';

    public const ROLE_CASHIER = 'kasir';

    public const PERMISSION_ACCESS = 'CRM:Access';

    public const PERMISSION_MANAGE_MEMBERS = 'CRM:ManageMembers';

    public const PERMISSION_MANAGE_POINTS = 'CRM:ManagePoints';

    public const PERMISSION_VIEW_HISTORY = 'CRM:ViewHistory';

    public const PERMISSION_EXPORT_HISTORY = 'CRM:ExportHistory';

    public const PERMISSION_DELETE_MEMBERS = 'CRM:DeleteMembers';

    public const PERMISSION_MANAGE_RETENTION = 'CRM:ManageRetention';

    /**
     * @return array<int, string>
     */
    public static function panelRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_MANAGEMENT,
            self::ROLE_CASHIER,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_MANAGEMENT => 'Manajemen',
            self::ROLE_CASHIER => 'Kasir',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        return [
            self::PERMISSION_ACCESS,
            self::PERMISSION_MANAGE_MEMBERS,
            self::PERMISSION_MANAGE_POINTS,
            self::PERMISSION_VIEW_HISTORY,
            self::PERMISSION_EXPORT_HISTORY,
            self::PERMISSION_DELETE_MEMBERS,
            self::PERMISSION_MANAGE_RETENTION,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function cashierPermissions(): array
    {
        return [
            self::PERMISSION_ACCESS,
            self::PERMISSION_MANAGE_MEMBERS,
            self::PERMISSION_MANAGE_POINTS,
            self::PERMISSION_VIEW_HISTORY,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function managementPermissions(): array
    {
        return [
            self::PERMISSION_ACCESS,
            self::PERMISSION_VIEW_HISTORY,
            self::PERMISSION_EXPORT_HISTORY,
            self::PERMISSION_MANAGE_RETENTION,
        ];
    }

    public static function canAccessPanel(?Authenticatable $user): bool
    {
        return $user instanceof User
            && $user->hasAnyRole(self::panelRoles());
    }

    public static function isSuperAdmin(?Authenticatable $user): bool
    {
        return $user instanceof User
            && $user->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public static function canUseCashierWorkspace(
        ?Authenticatable $user,
    ): bool {
        return $user instanceof User
            && $user->hasAnyRole([
                self::ROLE_SUPER_ADMIN,
                self::ROLE_CASHIER,
            ])
            && self::canAccessCrm($user)
            && self::canManageMembers($user)
            && self::canManagePoints($user);
    }

    public static function canUseManagementWorkspace(
        ?Authenticatable $user,
    ): bool {
        return $user instanceof User
            && $user->hasAnyRole([
                self::ROLE_SUPER_ADMIN,
                self::ROLE_MANAGEMENT,
            ])
            && self::canAccessCrm($user);
    }

    public static function canAccessCrm(?Authenticatable $user): bool
    {
        return self::hasPermission($user, self::PERMISSION_ACCESS);
    }

    public static function canManageMembers(?Authenticatable $user): bool
    {
        return self::hasPermission($user, self::PERMISSION_MANAGE_MEMBERS);
    }

    public static function canManagePoints(?Authenticatable $user): bool
    {
        return self::hasPermission($user, self::PERMISSION_MANAGE_POINTS);
    }

    public static function canViewHistory(?Authenticatable $user): bool
    {
        return self::hasPermission($user, self::PERMISSION_VIEW_HISTORY);
    }

    public static function canExportHistory(?Authenticatable $user): bool
    {
        return self::hasPermission($user, self::PERMISSION_EXPORT_HISTORY);
    }

    public static function canDeleteMembers(?Authenticatable $user): bool
    {
        return self::hasPermission($user, self::PERMISSION_DELETE_MEMBERS);
    }

    public static function canManageRetention(?Authenticatable $user): bool
    {
        return self::hasPermission($user, self::PERMISSION_MANAGE_RETENTION);
    }

    private static function hasPermission(
        ?Authenticatable $user,
        string $permission,
    ): bool {
        if (! $user instanceof User) {
            return false;
        }

        if (self::isSuperAdmin($user)) {
            return true;
        }

        return $user->can($permission);
    }
}
