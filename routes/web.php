<?php

use App\Http\Controllers\GlobalSearchController;
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

    // Global Search
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');
    Route::get('/search/results', [GlobalSearchController::class, 'results'])->name('search.results');

    Route::resource('branches', \App\Http\Controllers\BranchController::class);
    Route::resource('divisions', \App\Http\Controllers\DivisionController::class);
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);

    Route::resource('routers', \App\Http\Controllers\RouterController::class);
    Route::resource('servers', \App\Http\Controllers\HostingServerController::class);
    Route::resource('vendors', \App\Http\Controllers\VendorController::class);
    Route::resource('metro-ethernets', \App\Http\Controllers\MetroEthernetController::class);
    Route::get('zabbix-monitors', [\App\Http\Controllers\ZabbixMonitorController::class, 'index'])->name('zabbix-monitors.index');
    Route::get('zabbix-monitors/chart-data', [\App\Http\Controllers\ZabbixMonitorController::class, 'chartData'])->name('zabbix-monitors.chart-data');
    Route::resource('ip-transits', \App\Http\Controllers\IpTransitController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Master Data: Products & Services (Owner, Admin, & Employee)
    Route::middleware(['role:Owner|Admin|Employee|Billing|NOC|CS|Sales|Finance'])->group(function () {
        Route::resource('services', \App\Http\Controllers\ServiceController::class);
        Route::post('packages/sync', [\App\Http\Controllers\PackageController::class, 'syncPackages'])->name('packages.sync');
        Route::resource('packages', \App\Http\Controllers\PackageController::class);

        // Core Business: Client Management
        // Core Business: Client Management
        Route::get('administrative-areas', [\App\Http\Controllers\AdministrativeAreaController::class, 'index'])
            ->name('administrative-areas.index');
        Route::get('map-locations/search', [\App\Http\Controllers\MapLocationController::class, 'search'])
            ->name('map-locations.search');
        Route::resource('clients', \App\Http\Controllers\ClientController::class);
        Route::resource('clients.contacts', \App\Http\Controllers\ClientContactController::class)->only(['store', 'update', 'destroy']);
        Route::resource('subscriptions', \App\Http\Controllers\SubscriptionController::class);
        Route::get('zabbix-monitors/groups', [\App\Http\Controllers\ZabbixMonitorController::class, 'groups'])->name('zabbix-monitors.groups');
        Route::get('zabbix-monitors/hosts', [\App\Http\Controllers\ZabbixMonitorController::class, 'hosts'])->name('zabbix-monitors.hosts');
        Route::get('zabbix-monitors/graphs', [\App\Http\Controllers\ZabbixMonitorController::class, 'graphs'])->name('zabbix-monitors.graphs');

        // Network Topology Editor
        Route::prefix('subscriptions/{subscription}/topology')->name('subscriptions.topology.')->group(function () {
            Route::get('/', [\App\Http\Controllers\TopologyController::class, 'show'])->name('show');
            Route::post('/', [\App\Http\Controllers\TopologyController::class, 'store'])->name('store');
            Route::get('/history', [\App\Http\Controllers\TopologyController::class, 'history'])->name('history');
            Route::post('/restore/{historyId}', [\App\Http\Controllers\TopologyController::class, 'restore'])->name('restore');
            Route::post('/save-template', [\App\Http\Controllers\TopologyController::class, 'saveAsTemplate'])->name('save-template');
        });
        Route::get('topology-templates', [\App\Http\Controllers\TopologyController::class, 'templates'])->name('topology.templates');
        Route::delete('topology-templates/{template}', [\App\Http\Controllers\TopologyController::class, 'deleteTemplate'])->name('topology.templates.delete');

        // Billing
        Route::post('invoices/generate', [\App\Http\Controllers\InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'send'])->name('invoices.send');
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);

        Route::resource('payments', \App\Http\Controllers\PaymentController::class)->except(['show', 'edit', 'update', 'destroy']);
        Route::post('payments/{payment}/verify', [\App\Http\Controllers\PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('payments/{payment}/reject', [\App\Http\Controllers\PaymentController::class, 'reject'])->name('payments.reject');

        // Financial Reports
        Route::get('reports/financial', [\App\Http\Controllers\FinancialReportController::class, 'index'])->name('reports.financial.index');

        Route::resource('tickets', \App\Http\Controllers\TicketController::class)->only(['index', 'store', 'show', 'update']);
        Route::post('tickets/bulk-update', [\App\Http\Controllers\TicketController::class, 'bulkUpdate'])->name('tickets.bulk-update');
        Route::post('tickets/{ticket}/reply', [\App\Http\Controllers\TicketController::class, 'reply'])->name('tickets.reply');
        Route::resource('ticket-canned-responses', \App\Http\Controllers\TicketCannedResponseController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware(['role:Owner|Admin'])->group(function () {
        Route::post('clients/{client}/portal-account', [\App\Http\Controllers\ClientPortalAccountController::class, 'store'])
            ->name('clients.portal-account.store');
        Route::put('clients/{client}/portal-account', [\App\Http\Controllers\ClientPortalAccountController::class, 'update'])
            ->name('clients.portal-account.update');
        Route::post('clients/{client}/portal-account/revoke-sessions', [\App\Http\Controllers\ClientPortalAccountController::class, 'revokeSessions'])
            ->name('clients.portal-account.revoke-sessions');
        Route::post('clients/{client}/portal-account/generate-otp', [\App\Http\Controllers\ClientPortalAccountController::class, 'generateOtp'])
            ->name('clients.portal-account.generate-otp');
    });

    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::post('roles/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'syncPermissions'])
        ->name('roles.permissions.sync');
    Route::get('system-updates', [\App\Http\Controllers\SystemUpdateController::class, 'index'])->name('system-updates.index');
    Route::post('system-updates/refresh', [\App\Http\Controllers\SystemUpdateController::class, 'refresh'])->name('system-updates.refresh');
    Route::get('documentation', [\App\Http\Controllers\DocumentationController::class, 'index'])->name('documentation.index');
    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('settings', [\App\Http\Controllers\SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\SystemSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
