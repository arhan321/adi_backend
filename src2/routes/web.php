<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Crm\PointController;
use App\Http\Controllers\Crm\MemberController;
use App\Http\Controllers\Crm\CrmSettingController;

Route::redirect('/', '/admin');

Route::middleware([
    'web',
    'auth',
    'crm.role:super_admin,manajemen,kasir',
])
    ->prefix('crm')
    ->name('crm.')
    ->group(function (): void {
        Route::middleware('crm.role:super_admin,kasir')
            ->group(function (): void {
                Route::get('/members/search', [MemberController::class, 'search'])
                    ->name('members.search');

                Route::post('/members', [MemberController::class, 'store'])
                    ->name('members.store');

                Route::post('/members/{member}/points/earn', [PointController::class, 'earn'])
                    ->name('members.points.earn');

                Route::post('/members/{member}/points/redeem', [PointController::class, 'redeem'])
                    ->name('members.points.redeem');
            });

        Route::middleware('crm.role:super_admin,manajemen')
            ->group(function (): void {
                Route::get('/settings', [CrmSettingController::class, 'show'])
                    ->name('settings.show');

                Route::put('/settings', [CrmSettingController::class, 'update'])
                    ->name('settings.update');
            });
    });
