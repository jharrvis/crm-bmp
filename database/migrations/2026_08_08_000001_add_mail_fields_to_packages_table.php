<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedInteger('max_mailboxes')->nullable()->after('quota');
            $table->unsignedInteger('mailbox_quota_mb')->nullable()->after('max_mailboxes');
            $table->unsignedInteger('alias_max')->nullable()->after('mailbox_quota_mb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['max_mailboxes', 'mailbox_quota_mb', 'alias_max']);
        });
    }
};