<?php

use App\Http\Controllers\Api\ClientPortal\ClientPortalAuthController;
use App\Http\Controllers\Api\ClientPortal\ClientPortalDashboardController;
use App\Http\Controllers\Api\ClientPortal\ClientPortalInvoiceController;
use App\Http\Controllers\Api\ClientPortal\ClientPortalNotificationController;
use App\Http\Controllers\Api\ClientPortal\ClientPortalSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('client-portal')->group(function () {
    Route::post('auth/request-otp', [ClientPortalAuthController::class, 'requestOtp']);
    Route::post('auth/verify-otp', [ClientPortalAuthController::class, 'verifyOtp']);

    Route::middleware('client_portal.auth')->group(function () {
        Route::post('auth/logout', [ClientPortalAuthController::class, 'logout']);
        Route::get('auth/me', [ClientPortalAuthController::class, 'me']);

        Route::get('dashboard', [ClientPortalDashboardController::class, 'show']);

        Route::get('subscriptions', [ClientPortalSubscriptionController::class, 'index']);
        Route::get('subscriptions/{subscription}', [ClientPortalSubscriptionController::class, 'show']);
        Route::get('subscriptions/{subscription}/usage', [ClientPortalSubscriptionController::class, 'usage']);
        Route::get('subscriptions/{subscription}/usage/chart', [ClientPortalSubscriptionController::class, 'chart']);

        Route::get('invoices', [ClientPortalInvoiceController::class, 'index']);
        Route::get('invoices/{invoice}', [ClientPortalInvoiceController::class, 'show']);
        Route::get('invoices/{invoice}/download', [ClientPortalInvoiceController::class, 'download']);

        Route::get('notifications', [ClientPortalNotificationController::class, 'index']);
        Route::post('notifications/{notification}/read', [ClientPortalNotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [ClientPortalNotificationController::class, 'markAllRead']);
    });
});
