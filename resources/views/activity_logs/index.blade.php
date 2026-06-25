<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-slate-200 dark:border-slate-700">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Activity Log</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Audit aktivitas user dan perubahan penting yang tercatat di sistem.
                </p>
            </div>

            <form method="GET" class="px-6 py-5 md:px-8 grid gap-4 md:grid-cols-2 xl:grid-cols-5 border-b border-slate-200 dark:border-slate-700">
                <div class="xl:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Cari</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Cari deskripsi, subject, atau activity"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Log Name</label>
                    <select name="log_name" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        @foreach($logNames as $logName)
                            <option value="{{ $logName }}" @selected(request('log_name') === $logName)>{{ $logName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Event</label>
                    <select name="event" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pelaku</label>
                    <select name="causer_id" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        @foreach($causers as $causer)
                            <option value="{{ $causer->id }}" @selected((string) request('causer_id') === (string) $causer->id)>{{ $causer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:col-span-5 flex items-center justify-end gap-3">
                    <a href="{{ route('activity-logs.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                        Reset
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                        Terapkan
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 md:px-8">Waktu</th>
                            <th class="px-6 py-4">Pelaku</th>
                            <th class="px-6 py-4">Event</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4 md:pr-8">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($activities as $activity)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="px-6 py-4 md:px-8 align-top">
                                    <div class="text-sm font-medium text-slate-800 dark:text-white">{{ $activity->created_at?->format('d M Y H:i') }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $activity->log_name ?: 'default' }}</div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-medium text-slate-800 dark:text-white">{{ $activity->causer?->name ?? 'System' }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ class_basename($activity->causer_type ?? 'System') }}</div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $activity->event ?: 'activity' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm font-medium text-slate-800 dark:text-white">{{ class_basename($activity->subject_type ?? '-') }}</div>
                                    <div class="mt-1 text-xs text-slate-400">ID: {{ $activity->subject_id ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 md:pr-8 align-top text-sm text-slate-600 dark:text-slate-300">
                                    {{ $activity->description }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Belum ada activity log yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 md:px-8 border-t border-slate-200 dark:border-slate-700">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
