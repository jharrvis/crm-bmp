<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data: Organization Structure (Owner & Admin only)
    Route::middleware(['role:Owner|Admin'])->group(function () {
        Route::resource('branches', \App\Http\Controllers\BranchController::class);
        Route::resource('divisions', \App\Http\Controllers\DivisionController::class);
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class);

        // Master Data: Infrastructure
        Route::resource('routers', \App\Http\Controllers\RouterController::class);
        Route::resource('servers', \App\Http\Controllers\HostingServerController::class);
    });

    // Master Data: Products & Services (Owner, Admin, & Employee)
    Route::middleware(['role:Owner|Admin|Employee'])->group(function () {
        Route::resource('services', \App\Http\Controllers\ServiceController::class);
        Route::resource('packages', \App\Http\Controllers\PackageController::class);

        // Core Business: Client Management
        Route::resource('clients', \App\Http\Controllers\ClientController::class);
        Route::resource('subscriptions', \App\Http\Controllers\SubscriptionController::class);

        // Billing
        Route::post('invoices/generate', [\App\Http\Controllers\InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    });

    // Role & Permission Management (Owner & Admin)
    Route::middleware(['role:Owner|Admin'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleController::class);
        Route::post('roles/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'syncPermissions'])
            ->name('roles.permissions.sync');
    });
});

require __DIR__ . '/auth.php';
