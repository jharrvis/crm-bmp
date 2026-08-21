<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // P1: Integritas multi-akun diperkuat di level database — bukan hanya aplikasi.
        // Dua request paralel tidak boleh menautkan domain sama / akun sama dua kali.

        $this->abortIfDuplicates(['subscription_id'], 'Satu subscription memiliki lebih dari satu domain.');
        $this->abortIfDuplicates(
            ['registrar_account_id', 'provider_domain_id'],
            'Provider domain ID yang sama ditemukan lebih dari sekali pada akun registrar yang sama.',
            'registrar_account_id IS NOT NULL AND provider_domain_id IS NOT NULL'
        );

        // 1) Satu subscription hanya boleh punya satu baris domain (relasi hasOne).
        Schema::table('subscription_domains', function (Blueprint $table) {
            $table->unique('subscription_id', 'sd_subscription_unique');
        });

        // 2) Domain provider yang sama tidak boleh ditautkan dua kali ke akun yang sama.
        //    Kolom NULL diizinkan (MySQL unique memperlakukan NULL sebagai beda),
        //    sehingga domain manual (tanpa registrar) tetap bisa duplikat.
        Schema::table('subscription_domains', function (Blueprint $table) {
            $table->dropIndex('sd_reg_acct_provider_idx');
            $table->unique(['registrar_account_id', 'provider_domain_id'], 'sd_reg_acct_provider_unique');
        });

        // 3) Nama domain yang sama (case-insensitive) tidak boleh tertaut dua kali ke akun yang sama.
        //    Gunakan generated column agar deterministik terlepas dari collation kolom.
        if (! Schema::hasColumn('subscription_domains', 'domain_name_lower')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->string('domain_name_lower', 253)->nullable()->virtualAs('LOWER(domain_name)');
            });
        }
        Schema::table('subscription_domains', function (Blueprint $table) {
            $table->unique(['registrar_account_id', 'domain_name_lower'], 'sd_reg_acct_domain_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_domains', function (Blueprint $table) {
            try {
                $table->dropUnique('sd_subscription_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique('sd_reg_acct_provider_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique('sd_reg_acct_domain_unique');
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('subscription_domains', 'domain_name_lower')) {
                $table->dropColumn('domain_name_lower');
            }
        });
    }

    private function abortIfDuplicates(array $columns, string $message, string $where = '1 = 1'): void
    {
        $duplicates = DB::table('subscription_domains')
            ->select($columns)
            ->selectRaw('COUNT(*) AS total')
            ->whereRaw($where)
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $values = $duplicates->map(fn ($row) => implode('|', array_map(fn ($column) => $row->{$column}, $columns)))->implode(', ');
            throw new RuntimeException("{$message} Selesaikan konflik sebelum migrasi: {$values}");
        }
    }
};
