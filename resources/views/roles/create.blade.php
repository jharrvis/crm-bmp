<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('roles.index') }}"
                class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-slate-500"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Tambah Role Baru</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Buat role baru untuk mengatur hak akses</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="p-6 space-y-6">
                    {{-- Role Name --}}
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                            Nama Role <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            placeholder="Contoh: Supervisor, Kasir, Teknisi Lapangan">
                        @error('name')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description"
                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                            Deskripsi
                        </label>
                        <input type="text" id="description" name="description" value="{{ old('description') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            placeholder="Deskripsi singkat tentang role ini">
                        @error('description')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Copy Permissions From --}}
                    <div>
                        <label for="copy_from" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                            Copy Permissions dari Role Lain
                            <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <select id="copy_from" name="copy_from"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                            <option value="">-- Tidak copy, mulai dari kosong --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('copy_from') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }} ({{ $role->permissions->count() }} permissions)
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-400">
                            Pilih role untuk menyalin permissions-nya sebagai titik awal. Anda dapat mengubahnya nanti.
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div
                    class="px-6 py-4 bg-slate-50 dark:bg-slate-700/30 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <a href="{{ route('roles.index') }}"
                        class="px-5 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl font-semibold text-sm transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-lg shadow-blue-200 dark:shadow-blue-900/30 transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Buat Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>