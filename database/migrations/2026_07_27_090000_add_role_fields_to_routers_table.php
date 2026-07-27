<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->string('router_role', 50)->nullable()->after('type');
            $table->string('custom_role', 100)->nullable()->after('router_role');
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['router_role', 'custom_role']);
        });
    }
};
