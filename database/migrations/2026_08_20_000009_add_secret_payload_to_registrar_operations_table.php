<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrar_operations', function (Blueprint $table) {
            // Secret mutasi (mis. EPP code) disimpan terenkripsi di sini, BUKAN di payload queue job.
            // Job hanya membawa operation ID; secret dibaca saat eksekusi dan dihapus setelah sukses.
            $table->text('request_secret_encrypted')->nullable()->after('request_payload_redacted');
        });
    }

    public function down(): void
    {
        Schema::table('registrar_operations', function (Blueprint $table) {
            $table->dropColumn('request_secret_encrypted');
        });
    }
};