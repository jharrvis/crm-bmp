<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Detail Karyawan</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Informasi lengkap karyawan.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('employees.edit', $employee) }}"
                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                    Edit
                </a>
                <a href="{{ route('employees.index') }}"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </a>
            </div>
        </div>

        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm p-8">
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $employee->name }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            @foreach($employee->roles as $role)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email</p>
                        <p class="text-base text-slate-600 dark:text-slate-300">{{ $employee->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Bergabung Sejak</p>
                        <p class="text-base text-slate-600 dark:text-slate-300">
                            {{ $employee->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Cabang</p>
                        <p class="text-base text-slate-600 dark:text-slate-300">
                            @if($employee->branch)
                                <a href="{{ route('branches.show', $employee->branch) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">{{ $employee->branch->name }}</a>
                            @else
                                <span class="text-slate-400">Tidak Ada</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Divisi</p>
                        <p class="text-base text-slate-600 dark:text-slate-300">
                            @if($employee->division)
                                <a href="{{ route('divisions.show', $employee->division) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">{{ $employee->division->name }}</a>
                            @else
                                <span class="text-slate-400">Tidak Ada</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>