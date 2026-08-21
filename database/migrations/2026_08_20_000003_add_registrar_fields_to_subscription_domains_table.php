<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subscription_domains', 'registrar_account_id')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->foreignId('registrar_account_id')->nullable()->after('subscription_id')->constrained('registrar_accounts')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'provider_domain_id')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->string('provider_domain_id')->nullable()->after('registrar_account_id')->comment('ID domain di provider');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'provider_status')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->string('provider_status')->nullable()->after('provider_domain_id')->comment('Status dari provider');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'provider_metadata')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->json('provider_metadata')->nullable()->after('provider_status')->comment('Metadata ter-sync dari provider');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'last_synced_at')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->timestamp('last_synced_at')->nullable()->after('provider_metadata');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'sync_status')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->string('sync_status', 30)->nullable()->after('last_synced_at')->comment('synced, failed, pending, manual_review');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'sync_error_summary')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->text('sync_error_summary')->nullable()->after('sync_status');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'managed_by_crm')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->boolean('managed_by_crm')->default(false)->after('sync_error_summary')->comment('true jika didaftarkan via CRM (new), false jika tautkan existing');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'domain_account_mode')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->string('domain_account_mode', 20)->nullable()->after('managed_by_crm')->comment('new|existing - mode saat layanan dibuat');
            });
        }
        if (! Schema::hasColumn('subscription_domains', 'not_found_at')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->timestamp('not_found_at')->nullable()->after('domain_account_mode')->comment('Domain hilang dari provider saat sync');
            });
        }

        // Indexes — try to create, ignore if exists
        try {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->index(['registrar_account_id', 'provider_domain_id'], 'sd_reg_acct_provider_idx');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->index(['registrar_account_id', 'sync_status'], 'sd_reg_acct_sync_idx');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->index(['expires_at'], 'sd_expires_at_idx');
            });
        } catch (\Throwable $e) {
        }

        // Unique per-account domain (lowercase) — add after data is normalized in app layer
        // We use a plain index now; unique is enforced in service layer to allow manual domains (null FK) duplicates.
    }

    public function down(): void
    {
        Schema::table('subscription_domains', function (Blueprint $table) {
            try {
                $table->dropForeign(['registrar_account_id']);
            } catch (\Throwable $e) {
            }
            // Drop indexes if they exist — Laravel will ignore missing via try/catch
            try {
                $table->dropIndex('sd_reg_acct_provider_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('sd_reg_acct_sync_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('sd_expires_at_idx');
            } catch (\Throwable $e) {
            }

            $table->dropColumn([
                'registrar_account_id',
                'provider_domain_id',
                'provider_status',
                'provider_metadata',
                'last_synced_at',
                'sync_status',
                'sync_error_summary',
                'managed_by_crm',
                'domain_account_mode',
                'not_found_at',
            ]);
        });
    }
};
