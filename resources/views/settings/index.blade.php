<x-app-layout>
    @php
        $groupLabels = [
            'billing' => 'Billing / Tagihan',
        ];

        $fieldLabels = [
            'billing.ppn_rate' => 'Tarif PPN',
            'billing.pph23_rate' => 'Tarif PPh23',
            'billing.default_due_days' => 'Jatuh Tempo Default',
            'billing.auto_generate_day' => 'Tanggal Generate Bulanan',
            'billing.auto_generate_enabled' => 'Auto-Generate Invoice',
            'billing.proration_enabled' => 'Penghitungan Prorata',
            'billing.reminder_days_before' => 'Reminder Sebelum Jatuh Tempo',
            'billing.reminder_days_after' => 'Reminder Setelah Overdue',
            'billing.reminder_channel' => 'Channel Reminder',
        ];

        $fieldSuffixes = [
            'billing.ppn_rate' => '%',
            'billing.pph23_rate' => '%',
            'billing.default_due_days' => 'hari',
        ];

        $fieldHelps = [
            'billing.ppn_rate' => 'Tarif Pajak Pertambahan Nilai yang digunakan pada invoice.',
            'billing.pph23_rate' => 'Tarif Pajak Penghasilan Pasal 23 untuk jasa.',
            'billing.default_due_days' => 'Jumlah hari dari tanggal generate sampai jatuh tempo.',
            'billing.auto_generate_day' => 'Invoice bulanan akan di-generate otomatis pada tanggal ini (1-28).',
            'billing.auto_generate_enabled' => 'Jika aktif, invoice bulanan akan di-generate otomatis sesuai jadwal.',
            'billing.proration_enabled' => 'Jika aktif, sistem menghitung biaya proporsional saat register baru, upgrade/downgrade, atau suspend/terminate di tengah siklus.',
            'billing.reminder_days_before' => 'Daftar hari sebelum jatuh tempo untuk kirim reminder. Format JSON array, contoh: [7,3,1]',
            'billing.reminder_days_after' => 'Daftar hari setelah overdue untuk kirim reminder. Format JSON array, contoh: [1,7,14]',
            'billing.reminder_channel' => 'Channel pengiriman reminder: email, whatsapp, atau both.',
        ];
    @endphp

    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pengaturan</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Konfigurasi sistem yang berlaku secara global.
                </p>
            </div>

            @if (session('success'))
                <div class="mx-6 mt-6 md:mx-8 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                @forelse ($groups as $group => $settings)
                    <div class="px-6 py-6 md:px-8 {{ !$loop->last ? 'border-b border-slate-200 dark:border-slate-700' : '' }}">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-6">
                            {{ $groupLabels[$group] ?? ucfirst($group) }}
                        </h2>

                        <div class="space-y-6">
                            @foreach ($settings as $setting)
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                    <div class="lg:pt-2">
                                        <label for="setting_{{ $setting->key }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                            {{ $fieldLabels[$setting->key] ?? $setting->key }}
                                        </label>
                                        @if (isset($fieldHelps[$setting->key]))
                                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                                {{ $fieldHelps[$setting->key] }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="lg:col-span-2">
                                        @if ($setting->type === 'boolean')
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="hidden" name="settings[{{ $setting->key }}]" value="">
                                                <input type="checkbox"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="true"
                                                    id="setting_{{ $setting->key }}"
                                                    class="sr-only peer"
                                                    {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 dark:peer-focus:ring-blue-900 rounded-full peer dark:bg-slate-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-slate-500 peer-checked:bg-blue-600"></div>
                                                <span class="ms-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                                    {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </label>
                                        @elseif ($setting->type === 'json')
                                            <input type="text"
                                                name="settings[{{ $setting->key }}]"
                                                id="setting_{{ $setting->key }}"
                                                value="{{ $setting->value }}"
                                                class="w-full max-w-md rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @elseif ($setting->type === 'integer' || $setting->type === 'float')
                                            <div class="flex items-center gap-2 max-w-xs">
                                                <input type="number"
                                                    name="settings[{{ $setting->key }}]"
                                                    id="setting_{{ $setting->key }}"
                                                    value="{{ $setting->value }}"
                                                    step="{{ $setting->type === 'float' ? '0.01' : '1' }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                @if (isset($fieldSuffixes[$setting->key]))
                                                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                                        {{ $fieldSuffixes[$setting->key] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <input type="text"
                                                name="settings[{{ $setting->key }}]"
                                                id="setting_{{ $setting->key }}"
                                                value="{{ $setting->value }}"
                                                class="w-full max-w-md rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @endif

                                        @error("settings.{$setting->key}")
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 md:px-8 text-center">
                        <p class="text-slate-500 dark:text-slate-400">Belum ada pengaturan yang terdaftar.</p>
                    </div>
                @endforelse

                @can('settings.update')
                    <div class="px-6 py-6 md:px-8 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Simpan Pengaturan
                        </button>
                    </div>
                @endcan
            </form>
        </div>
    </div>
</x-app-layout>
