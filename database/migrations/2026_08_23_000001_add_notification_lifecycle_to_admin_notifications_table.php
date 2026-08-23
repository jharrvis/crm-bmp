<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->string('category', 30)->nullable()->after('type')->comment('domain, hosting, billing, ticket, infrastructure, approval, system');
            $table->string('severity', 20)->nullable()->after('category')->comment('info, warning, high, critical');
            $table->boolean('action_required')->default(false)->after('severity');
            $table->string('action_key', 50)->nullable()->after('action_required')->comment('view_domain, sync_domain, request_renew, view_invoice, etc. Resolver server-side.');
            $table->string('source_type', 100)->nullable()->after('action_key')->comment('Model class: SubscriptionDomain, Invoice, Ticket, etc.');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->string('dedupe_key', 100)->nullable()->after('source_id')->comment('SHA1(type:source_type:source_id:state) untuk incident dedupe');
            $table->timestamp('resolved_at')->nullable()->after('dismissed_at')->comment('Hanya setelah action sumber berhasil, bukan read');
            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('snoozed_until')->nullable()->after('resolved_by');

            $table->index(['source_type', 'source_id']);
            $table->index(['dedupe_key']);
            $table->index(['action_required', 'resolved_at']);
            $table->index(['category', 'severity']);
            $table->index(['snoozed_until']);
        });
    }

    public function down(): void
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropIndex(['dedupe_key']);
            $table->dropIndex(['action_required', 'resolved_at']);
            $table->dropIndex(['category', 'severity']);
            $table->dropIndex(['snoozed_until']);
            $table->dropColumn(['category', 'severity', 'action_required', 'action_key', 'source_type', 'source_id', 'dedupe_key', 'resolved_at', 'resolved_by', 'snoozed_until']);
        });
    }
};