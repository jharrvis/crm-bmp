<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Detail Divisi</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Informasi lengkap divisi.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('divisions.edit', $division) }}"
                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                    Edit
                </a>
                <a href="{{ route('divisions.index') }}"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm p-8">
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Divisi</p>
                    <p class="text-lg font-semibold text-slate-800 dark:text-white">{{ $division->name }}</p>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                    <p class="text-base text-slate-600 dark:text-slate-300">{{ $division->description ?? '-' }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Karyawan di Divisi Ini
                        ({{ $division->users->count() }})</p>
                    @if($division->users->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($division->users as $user)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    {{ $user->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400">Belum ada karyawan di divisi ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>