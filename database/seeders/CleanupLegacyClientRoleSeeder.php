<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CleanupLegacyClientRoleSeeder extends Seeder
{
    /**
     * Remove legacy dummy client user/role from the old users-based portal approach.
     */
    public function run(): void
    {
        $legacyUser = User::query()
            ->where('email', 'client@gmail.com')
            ->where('name', 'Pelanggan A')
            ->first();

        if ($legacyUser) {
            $legacyUser->syncRoles([]);
            $legacyUser->delete();

            $this->command?->info('Legacy dummy client user "Pelanggan A" berhasil dihapus.');
        }

        $clientRole = Role::query()
            ->where('name', 'Client')
            ->first();

        if ($clientRole && $clientRole->users()->count() === 0) {
            $clientRole->delete();

            $this->command?->info('Legacy role "Client" berhasil dihapus karena sudah tidak dipakai.');
        }
    }
}
