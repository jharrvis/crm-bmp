<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscription_mail_hostings', function (Blueprint $table) {
            $table->string('provisioning_status')->default('pending')->after('status');
            $table->text('provisioning_error')->nullable()->after('provisioning_status');
            $table->timestamp('provisioned_at')->nullable()->after('provisioning_error');
            $table->unique('subscription_id', 'subscription_mail_hostings_subscription_unique');
            $table->unique(['mail_server_id', 'domain'], 'subscription_mail_hostings_server_domain_unique');
        });

        Schema::table('mailboxes', function (Blueprint $table) {
            $table->string('provisioning_status')->default('pending')->after('is_active');
            $table->text('provisioning_error')->nullable()->after('provisioning_status');
            $table->timestamp('provisioned_at')->nullable()->after('provisioning_error');
            $table->boolean('suspended_by_subscription')->default(false)->after('is_active');
            $table->unique('email', 'mailboxes_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('mailboxes', function (Blueprint $table) {
            $table->dropUnique('mailboxes_email_unique');
            $table->dropColumn(['provisioning_status', 'provisioning_error', 'provisioned_at', 'suspended_by_subscription']);
        });

        Schema::table('subscription_mail_hostings', function (Blueprint $table) {
            $table->dropUnique('subscription_mail_hostings_subscription_unique');
            $table->dropUnique('subscription_mail_hostings_server_domain_unique');
            $table->dropColumn(['provisioning_status', 'provisioning_error', 'provisioned_at']);
        });
    }
};
