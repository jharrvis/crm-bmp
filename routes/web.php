<?php

use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ip.restrict'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
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
    Route::prefix('servers/{server}')->group(function () {
        Route::get('manage', [\App\Http\Controllers\ServerManageController::class, 'show'])->name('servers.manage');
        Route::post('test-connection', [\App\Http\Controllers\ServerManageController::class, 'testConnection'])->name('servers.test-connection');
        Route::get('users', [\App\Http\Controllers\ServerManageController::class, 'users'])->name('servers.users');
        Route::get('users/{username}', [\App\Http\Controllers\ServerManageController::class, 'userShow'])
            ->where('username', '[a-zA-Z0-9_]{1,32}')
            ->name('servers.users.show');
        Route::post('users/link', [\App\Http\Controllers\ServerManageController::class, 'link'])->name('servers.users.link');
        Route::post('users/suspend', [\App\Http\Controllers\ServerManageController::class, 'suspend'])->name('servers.users.suspend');
        Route::post('users/activate', [\App\Http\Controllers\ServerManageController::class, 'activate'])->name('servers.users.activate');
        Route::post('users/password', [\App\Http\Controllers\ServerManageController::class, 'resetPassword'])->name('servers.users.password');
        Route::delete('users', [\App\Http\Controllers\ServerManageController::class, 'destroy'])->name('servers.users.destroy');
        Route::post('refresh', [\App\Http\Controllers\ServerManageController::class, 'refresh'])->name('servers.refresh');
    });
    Route::resource('vendors', \App\Http\Controllers\VendorController::class);
    Route::resource('metro-ethernets', \App\Http\Controllers\MetroEthernetController::class);
    Route::get('zabbix-monitors', [\App\Http\Controllers\ZabbixMonitorController::class, 'index'])->name('zabbix-monitors.index');
    Route::get('zabbix-monitors/chart-data', [\App\Http\Controllers\ZabbixMonitorController::class, 'chartData'])->name('zabbix-monitors.chart-data');
    Route::resource('ip-transits', \App\Http\Controllers\IpTransitController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Domain Registrar SRS-X (Fase 1 read-only)
    Route::resource('registrar-accounts', \App\Http\Controllers\RegistrarAccountController::class)->except(['create', 'edit']);
    Route::post('registrar-accounts/{registrarAccount}/test-connection', [\App\Http\Controllers\RegistrarAccountController::class, 'testConnection'])->name('registrar-accounts.test-connection');
    Route::post('registrar-accounts/{registrarAccount}/sync', [\App\Http\Controllers\RegistrarAccountController::class, 'sync'])->name('registrar-accounts.sync');
    Route::post('registrar-accounts/{registrarAccount}/import-manual', [\App\Http\Controllers\RegistrarAccountController::class, 'manualImport'])->name('registrar-accounts.import-manual');
    Route::get('registrar-accounts/{registrarAccount}/operations/{operation}', [\App\Http\Controllers\RegistrarAccountController::class, 'showOperation'])->name('registrar-accounts.operations.show');
    Route::post('registrar-accounts/{registrarAccount}/operations/{operation}/link', [\App\Http\Controllers\RegistrarAccountController::class, 'linkOperationDomain'])->name('registrar-accounts.operations.link');
    Route::get('registrar-accounts/{registrarAccount}/domains', [\App\Http\Controllers\RegistrarAccountController::class, 'domains'])->name('registrar-accounts.domains');
    Route::get('registrar-accounts/{registrarAccount}/check', [\App\Http\Controllers\RegistrarAccountController::class, 'checkDomain'])->name('registrar-accounts.check');

    // Pusat Notifikasi Admin
    Route::get('notifications', [\App\Http\Controllers\AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/count', [\App\Http\Controllers\AdminNotificationController::class, 'count'])->name('notifications.count');
    Route::get('notifications/{notification}', [\App\Http\Controllers\AdminNotificationController::class, 'show'])->name('notifications.show');
    Route::post('notifications/{notification}/read', [\App\Http\Controllers\AdminNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [\App\Http\Controllers\AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/dismiss', [\App\Http\Controllers\AdminNotificationController::class, 'dismiss'])->name('notifications.dismiss');
    Route::post('notifications/{notification}/resolve', [\App\Http\Controllers\AdminNotificationController::class, 'resolve'])->name('notifications.resolve');
    Route::post('notifications/{notification}/snooze', [\App\Http\Controllers\AdminNotificationController::class, 'snooze'])->name('notifications.snooze');

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
        Route::get('subscriptions/hosting-servers/{server}/users', [\App\Http\Controllers\SubscriptionController::class, 'hestiaUsers'])->name('subscriptions.hestia-users');
        Route::get('subscriptions/hosting-servers/{server}/domains', [\App\Http\Controllers\SubscriptionController::class, 'hestiaUserDomains'])->name('subscriptions.hestia-user-domains');
        Route::resource('subscriptions', \App\Http\Controllers\SubscriptionController::class);
        Route::get('subscriptions/clients/{client}/mail-domains', [\App\Http\Controllers\SubscriptionController::class, 'clientMailDomains'])->name('subscriptions.clients.mail-domains');
        Route::get('subscriptions/{subscription}/mail-hosting/admin-credential', [\App\Http\Controllers\SubscriptionController::class, 'mailHostingAdminCredential'])->name('subscriptions.mail-hosting.admin-credential');
        Route::get('subscriptions/{subscription}/mailboxes', [\App\Http\Controllers\MailboxController::class, 'index'])->name('subscriptions.mailboxes.index');
        Route::post('subscriptions/{subscription}/mailboxes', [\App\Http\Controllers\MailboxController::class, 'store'])->name('subscriptions.mailboxes.store');
        Route::post('subscriptions/{subscription}/mailboxes/sync', [\App\Http\Controllers\MailboxController::class, 'sync'])->name('subscriptions.mailboxes.sync');
        Route::post('subscriptions/{subscription}/mailboxes/{mailbox}/suspend', [\App\Http\Controllers\MailboxController::class, 'suspend'])->name('subscriptions.mailboxes.suspend');
        Route::post('subscriptions/{subscription}/mailboxes/{mailbox}/activate', [\App\Http\Controllers\MailboxController::class, 'activate'])->name('subscriptions.mailboxes.activate');
        Route::delete('subscriptions/{subscription}/mailboxes/{mailbox}', [\App\Http\Controllers\MailboxController::class, 'destroy'])->name('subscriptions.mailboxes.destroy');
        Route::get('zabbix-monitors/groups', [\App\Http\Controllers\ZabbixMonitorController::class, 'groups'])->name('zabbix-monitors.groups');
        Route::get('zabbix-monitors/hosts', [\App\Http\Controllers\ZabbixMonitorController::class, 'hosts'])->name('zabbix-monitors.hosts');
        Route::get('zabbix-monitors/graphs', [\App\Http\Controllers\ZabbixMonitorController::class, 'graphs'])->name('zabbix-monitors.graphs');

        // Domain Registrar — Fase 2: operasi domain terkontrol (nameserver, EPP, DNS managed)
        Route::prefix('subscriptions/{subscription}/domains/{domain}')->name('domain-operations.')->group(function () {
            Route::post('sync', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'sync'])->name('sync');
            // Fase 3a: approval internal saja; tidak ada mutasi provider.
            Route::post('renewals', [\App\Http\Controllers\SubscriptionDomainRenewalController::class, 'requestRenewal'])->name('renewals.request');
            Route::post('renewals/{operation}/approve', [\App\Http\Controllers\SubscriptionDomainRenewalController::class, 'approveRenewal'])->name('renewals.approve');
            Route::post('nameservers', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'updateNameservers'])->name('nameservers');
            Route::post('epp/fetch', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'fetchEpp'])->name('epp.fetch');
            Route::post('epp/set', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'setEpp'])->name('epp.set');
            Route::post('dns/info', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'getDns'])->name('dns.info');
            Route::post('dns/edit', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'editDns'])->name('dns.edit');
            Route::post('dns/toggle', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'toggleManagedDns'])->name('dns.toggle');
            Route::post('operations/{operation}/retry', [\App\Http\Controllers\SubscriptionDomainOperationController::class, 'retryOperation'])->name('operations.retry');
        });

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
