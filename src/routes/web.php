<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Crm\PointController;
use App\Http\Controllers\Crm\MemberController;
use App\Http\Controllers\Crm\CrmSettingController;
use App\Http\Controllers\Crm\WhatsappWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth'])
    ->prefix('crm')
    ->name('crm.')
    ->group(function (): void {
        Route::get('/members/search', [MemberController::class, 'search'])->name('members.search');
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');

        Route::post('/members/{member}/points/earn', [PointController::class, 'earn'])->name('members.points.earn');
        Route::post('/members/{member}/points/redeem', [PointController::class, 'redeem'])->name('members.points.redeem');

        Route::get('/settings', [CrmSettingController::class, 'show'])->name('settings.show');
        Route::put('/settings', [CrmSettingController::class, 'update'])->name('settings.update');
    });

Route::post('/twilio/whatsapp/status-callback', [WhatsappWebhookController::class, 'statusCallback'])
    ->name('twilio.whatsapp.status-callback');
