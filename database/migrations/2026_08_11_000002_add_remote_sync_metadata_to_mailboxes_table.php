<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mailboxes', function (Blueprint $table) {
            $table->string('remote_status', 32)->nullable()->after('managed_by_crm');
        });

        Schema::table('subscription_mail_hostings', function (Blueprint $table) {
            $table->timestamp('mailboxes_last_synced_at')->nullable()->after('provisioned_at');
            $table->string('mailboxes_sync_error')->nullable()->after('mailboxes_last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_mail_hostings', function (Blueprint $table) {
            $table->dropColumn(['mailboxes_last_synced_at', 'mailboxes_sync_error']);
        });

        Schema::table('mailboxes', function (Blueprint $table) {
            $table->dropColumn('remote_status');
        });
    }
};
