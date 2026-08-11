<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mailboxes', function (Blueprint $table) {
            // Existing local mailboxes were created by CRM before import support.
            $table->boolean('managed_by_crm')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('mailboxes', function (Blueprint $table) {
            $table->dropColumn('managed_by_crm');
        });
    }
};
