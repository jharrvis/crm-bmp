<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Manajemen Karyawan</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Daftar pengguna staff dan teknisi.</p>
            </div>
            <a href="{{ route('employees.create') }}"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah Karyawan
            </a>
        </div>

        @if(session('success'))
            <div
                class="p-4 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl text-sm font-bold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="p-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl text-sm font-bold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                {{ session('error') }}
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
                                Nama</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                Email</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                Role</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                Cabang</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                Divisi</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right pr-6">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="p-4 pl-6">
                                    <span
                                        class="font-bold text-slate-800 dark:text-white block">{{ $employee->name }}</span>
                                </td>
                                <td class="p-4 text-sm text-slate-500 dark:text-slate-400">{{ $employee->email }}</td>
                                <td class="p-4">
                                    @foreach($employee->roles as $role)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="p-4 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $employee->branch ? $employee->branch->name : '-' }}
                                </td>
                                <td class="p-4 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $employee->division ? $employee->division->name : '-' }}
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('employees.show', $employee) }}"
                                            class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('employees.edit', $employee) }}"
                                            class="p-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-colors">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </a>
                                        @if($employee->id !== auth()->id())
                                            <form action="{{ route('employees.destroy', $employee) }}" method="POST"
                                                class="inline-block"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                                    Belum ada data karyawan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>