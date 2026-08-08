<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscription_hostings', function (Blueprint $table) {
            // These timestamps prove a remote lifecycle action originated from CRM.
            $table->timestamp('remote_user_created_at')->nullable()->after('provisioned_at');
            $table->timestamp('delete_requested_at')->nullable()->after('remote_user_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_hostings', function (Blueprint $table) {
            $table->dropColumn(['remote_user_created_at', 'delete_requested_at']);
        });
    }
};
