<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SECRET_KEYS = ['authcode', 'auth_code', 'epp', 'eppcode'];

    /**
     * Bersihkan secret yang mungkin sempat tercatat di provider_metadata milik
     * SubscriptionDomain oleh worker lama. Tidak menyentuh event/log modul lain.
     */
    public function up(): void
    {
        $connection = config('activitylog.database_connection') ?: config('database.default');
        $table = config('activitylog.table_name', 'activity_log');

        if (! Schema::connection($connection)->hasTable($table)) {
            return;
        }

        DB::connection($connection)->table($table)
            ->whereNotNull('properties')
            ->where('subject_type', 'App\\Models\\SubscriptionDomain')
            ->orderBy('id')
            ->eachById(function (object $activity) use ($connection, $table): void {
                $properties = json_decode((string) $activity->properties, true);
                if (! is_array($properties)) {
                    return;
                }

                $scrubbed = $this->scrubDomainMetadata($properties);
                if ($scrubbed !== $properties) {
                    DB::connection($connection)->table($table)
                        ->where('id', $activity->id)
                        ->update(['properties' => json_encode($scrubbed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                }
            });
    }

    public function down(): void
    {
        // Secret yang dibersihkan tidak boleh dipulihkan.
    }

    private function scrubDomainMetadata(array $properties): array
    {
        // Spatie menyimpan nilai baru/lama model di attributes dan old.
        foreach (['attributes', 'old'] as $section) {
            if (! isset($properties[$section]['provider_metadata'])) {
                continue;
            }

            $metadata = $properties[$section]['provider_metadata'];
            $wasJson = is_string($metadata);
            if ($wasJson) {
                $metadata = json_decode($metadata, true);
            }
            if (! is_array($metadata)) {
                continue;
            }

            foreach (self::SECRET_KEYS as $key) {
                unset($metadata[$key]);
            }

            $properties[$section]['provider_metadata'] = $wasJson
                ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $metadata;
        }

        return $properties;
    }
};
