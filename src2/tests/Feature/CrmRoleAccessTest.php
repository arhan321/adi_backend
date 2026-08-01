<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\CrmAccess;
use Spatie\Permission\Models\Role;
use Database\Seeders\CrmRoleSeeder;
use App\Filament\Admin\Pages\CrmHistory;
use App\Filament\Admin\Pages\CrmAddMember;
use App\Filament\Admin\Pages\CrmDashboard;
use App\Filament\Admin\Pages\CrmEditMember;
use App\Filament\Admin\Pages\CrmSettingsPage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(CrmRoleSeeder::class);
});

test('management can only access history and CRM settings', function (): void {
    $management = User::factory()->create();
    $management->assignRole(CrmAccess::ROLE_MANAGEMENT);

    $this->actingAs($management);

    expect(CrmDashboard::canAccess())
        ->toBeFalse()
        ->and(CrmAddMember::canAccess())
        ->toBeFalse()
        ->and(CrmEditMember::canAccess())
        ->toBeFalse()
        ->and(CrmHistory::canAccess())
        ->toBeTrue()
        ->and(CrmSettingsPage::canAccess())
        ->toBeTrue();
});

test('cashier access remains unchanged', function (): void {
    $cashier = User::factory()->create();
    $cashier->assignRole(CrmAccess::ROLE_CASHIER);

    $this->actingAs($cashier);

    expect(CrmDashboard::canAccess())
        ->toBeTrue()
        ->and(CrmAddMember::canAccess())
        ->toBeTrue()
        ->and(CrmEditMember::canAccess())
        ->toBeTrue()
        ->and(CrmHistory::canAccess())
        ->toBeTrue()
        ->and(CrmSettingsPage::canAccess())
        ->toBeFalse();
});

test('super admin keeps full CRM access', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(CrmAccess::ROLE_SUPER_ADMIN);

    $this->actingAs($superAdmin);

    expect(CrmDashboard::canAccess())
        ->toBeTrue()
        ->and(CrmAddMember::canAccess())
        ->toBeTrue()
        ->and(CrmEditMember::canAccess())
        ->toBeTrue()
        ->and(CrmHistory::canAccess())
        ->toBeTrue()
        ->and(CrmSettingsPage::canAccess())
        ->toBeTrue();
});

test('rerunning the seeder removes disallowed management CRM permissions', function (): void {
    $managementRole = Role::findByName(
        CrmAccess::ROLE_MANAGEMENT,
        'web',
    );

    $managementRole->givePermissionTo([
        CrmAccess::PERMISSION_MANAGE_MEMBERS,
        CrmAccess::PERMISSION_MANAGE_POINTS,
        CrmAccess::PERMISSION_DELETE_MEMBERS,
    ]);

    $this->seed(CrmRoleSeeder::class);

    $managementRole->refresh();

    expect($managementRole->hasPermissionTo(CrmAccess::PERMISSION_MANAGE_MEMBERS))
        ->toBeFalse()
        ->and($managementRole->hasPermissionTo(CrmAccess::PERMISSION_MANAGE_POINTS))
        ->toBeFalse()
        ->and($managementRole->hasPermissionTo(CrmAccess::PERMISSION_DELETE_MEMBERS))
        ->toBeFalse()
        ->and($managementRole->hasPermissionTo(CrmAccess::PERMISSION_VIEW_HISTORY))
        ->toBeTrue()
        ->and($managementRole->hasPermissionTo(CrmAccess::PERMISSION_EXPORT_HISTORY))
        ->toBeTrue()
        ->and($managementRole->hasPermissionTo(CrmAccess::PERMISSION_MANAGE_RETENTION))
        ->toBeTrue();
});

test('management is blocked from cashier API routes', function (): void {
    $management = User::factory()->create();
    $management->assignRole(CrmAccess::ROLE_MANAGEMENT);

    $this->actingAs($management);

    $this->getJson(route('crm.members.search', [
        'phone' => '081234567890',
    ]))->assertForbidden();

    $this->postJson(route('crm.members.store'))
        ->assertForbidden();
});

test('cashier is blocked from management settings routes', function (): void {
    $cashier = User::factory()->create();
    $cashier->assignRole(CrmAccess::ROLE_CASHIER);

    $this->actingAs($cashier);

    $this->getJson(route('crm.settings.show'))
        ->assertForbidden();
});