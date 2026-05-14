<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('client_last_read_at')->nullable()->after('closed_at');
            $table->timestamp('staff_last_read_at')->nullable()->after('client_last_read_at');
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->boolean('is_internal')->default(false)->after('author_type');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'client_last_read_at',
                'staff_last_read_at',
            ]);
        });
    }
};
