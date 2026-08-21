<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bersihkan respons domain/info lama yang dapat mengandung EPP/auth code.
     * Nilai tidak dapat dan tidak boleh dipulihkan pada down(), karena secret
     * tidak pernah seharusnya tersimpan di provider_metadata.
     */
    public function up(): void
    {
        DB::table('subscription_domains')
            ->whereNotNull('provider_metadata')
            ->orderBy('id')
            ->eachById(function (object $domain): void {
                $metadata = json_decode((string) $domain->provider_metadata, true);
                if (! is_array($metadata)) {
                    return;
                }

                $before = $metadata;
                unset($metadata['authcode'], $metadata['auth_code'], $metadata['epp'], $metadata['eppcode']);

                if ($metadata !== $before) {
                    DB::table('subscription_domains')
                        ->where('id', $domain->id)
                        ->update(['provider_metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                }
            });
    }

    public function down(): void
    {
        // Tidak ada rollback: secret yang sudah dibersihkan tidak boleh ditulis ulang.
    }
};
