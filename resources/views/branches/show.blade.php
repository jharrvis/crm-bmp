<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Detail Cabang</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Informasi lengkap kantor cabang.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('branches.edit', $branch) }}"
                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                    Edit
                </a>
                <a href="{{ route('branches.index') }}"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm p-8">
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Cabang</p>
                        <p class="text-lg font-semibold text-slate-800 dark:text-white">{{ $branch->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Cabang</p>
                        <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $branch->code }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Telepon</p>
                    <p class="text-base text-slate-600 dark:text-slate-300">{{ $branch->phone ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat Lengkap</p>
                    <p class="text-base text-slate-600 dark:text-slate-300">{{ $branch->address ?? '-' }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Karyawan di Cabang Ini
                        ({{ $branch->users->count() }})</p>
                    @if($branch->users->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($branch->users as $user)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    {{ $user->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400">Belum ada karyawan di cabang ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>