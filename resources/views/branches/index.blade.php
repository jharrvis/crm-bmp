<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Manajemen Cabang</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Daftar kantor cabang operasional.</p>
            </div>
            <a href="{{ route('branches.create') }}"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Cabang
            </a>
        </div>

        @if(session('success'))
            <div
                class="p-4 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl text-sm font-bold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                {{ session('success') }}
            </div>
        @endif

        <div
            class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-700/20">
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-6">
                                Kode</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                Nama Cabang</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                Alamat</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                Telepon</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right pr-6">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($branches as $branch)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="p-4 pl-6 text-sm font-bold text-slate-800 dark:text-white">{{ $branch->code }}
                                </td>
                                <td class="p-4 text-sm font-medium text-slate-600 dark:text-slate-300">{{ $branch->name }}
                                </td>
                                <td class="p-4 text-sm text-slate-500 dark:text-slate-400">
                                    {{ Str::limit($branch->address, 50) }}
                                </td>
                                <td class="p-4 text-sm text-slate-500 dark:text-slate-400">{{ $branch->phone ?? '-' }}</td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('branches.show', $branch) }}"
                                            class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('branches.edit', $branch) }}"
                                            class="p-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-colors">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route('branches.destroy', $branch) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                                    Belum ada data cabang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>