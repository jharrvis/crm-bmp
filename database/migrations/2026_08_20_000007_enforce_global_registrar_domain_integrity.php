<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->abortIfDuplicates(['subscription_id'], 'Satu subscription memiliki lebih dari satu domain.');
        $this->abortIfDuplicates(
            ['registrar_account_id', 'provider_domain_id'],
            'Provider domain ID yang sama ditemukan lebih dari sekali pada akun registrar yang sama.',
            'registrar_account_id IS NOT NULL AND provider_domain_id IS NOT NULL'
        );
        $this->abortIfRawDuplicates(
            'LOWER(domain_name)',
            'Domain yang sama sudah tertaut ke lebih dari satu akun registrar.',
            'registrar_account_id IS NOT NULL'
        );

        if (! Schema::hasColumn('subscription_domains', 'domain_name_registrar_lower')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                // NULL untuk domain manual agar tetap kompatibel dengan data lama.
                $table->string('domain_name_registrar_lower', 253)
                    ->nullable()
                    ->virtualAs('CASE WHEN registrar_account_id IS NULL THEN NULL ELSE LOWER(domain_name) END');
            });
        }

        $this->addUniqueIfMissing('sd_subscription_unique', ['subscription_id']);
        $this->addUniqueIfMissing('sd_reg_acct_provider_unique', ['registrar_account_id', 'provider_domain_id']);
        $this->addUniqueIfMissing('sd_reg_domain_global_unique', ['domain_name_registrar_lower']);
    }

    public function down(): void
    {
        if ($this->indexExists('sd_reg_domain_global_unique')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->dropUnique('sd_reg_domain_global_unique');
            });
        }

        if (Schema::hasColumn('subscription_domains', 'domain_name_registrar_lower')) {
            Schema::table('subscription_domains', function (Blueprint $table) {
                $table->dropColumn('domain_name_registrar_lower');
            });
        }
    }

    private function addUniqueIfMissing(string $name, array $columns): void
    {
        if ($this->indexExists($name)) {
            return;
        }

        Schema::table('subscription_domains', function (Blueprint $table) use ($name, $columns) {
            $table->unique($columns, $name);
        });
    }

    private function indexExists(string $name): bool
    {
        return Schema::hasIndex('subscription_domains', $name);
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

    private function abortIfRawDuplicates(string $expression, string $message, string $where): void
    {
        $duplicates = DB::table('subscription_domains')
            ->selectRaw("{$expression} AS duplicate_value, COUNT(*) AS total")
            ->whereRaw($where)
            ->groupByRaw($expression)
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->get();

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException("{$message} Selesaikan konflik sebelum migrasi: ".$duplicates->pluck('duplicate_value')->implode(', '));
        }
    }
};
