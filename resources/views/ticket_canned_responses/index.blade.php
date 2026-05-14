<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Master Template Balasan Ticket</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola canned responses agar tim support bisa membalas lebih cepat dan konsisten.</p>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/30 p-5 md:p-6 mb-8">
                <div class="mb-5">
                    <h4 class="text-sm font-bold uppercase tracking-widest text-slate-500">Tambah Template Baru</h4>
                </div>

                <form method="POST" action="{{ route('ticket-canned-responses.store') }}" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Judul</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Contoh: Sedang Investigasi" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kategori</label>
                            <select name="category"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua kategori</option>
                                @foreach(['connectivity', 'billing', 'technical', 'general'] as $category)
                                    <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Urutan</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                min="0" max="9999">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pesan Template</label>
                        <textarea name="message" rows="6"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Isi template balasan..." required>{{ old('message') }}</textarea>
                    </div>

                    <label class="inline-flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktifkan template ini</span>
                    </label>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Simpan Template
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                @forelse($responses as $response)
                    <div class="rounded-[1.5rem] border border-slate-200 dark:border-slate-700 p-5 bg-white dark:bg-slate-800">
                        <form method="POST" action="{{ route('ticket-canned-responses.update', $response) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Judul</label>
                                    <input type="text" name="title" value="{{ old('title', $response->title) }}"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kategori</label>
                                    <select name="category"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Semua kategori</option>
                                        @foreach(['connectivity', 'billing', 'technical', 'general'] as $category)
                                            <option value="{{ $category }}" {{ $response->category === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Urutan</label>
                                    <input type="number" name="sort_order" value="{{ $response->sort_order }}"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                        min="0" max="9999">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pesan</label>
                                <textarea name="message" rows="5"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                    required>{{ $response->message }}</textarea>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <label class="inline-flex items-center gap-3">
                                    <input type="checkbox" name="is_active" value="1" {{ $response->is_active ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                                </label>

                                <div class="flex items-center gap-3">
                                    <button type="submit"
                                        class="px-4 py-2 rounded-xl font-bold bg-slate-900 dark:bg-blue-600 text-white hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors">
                                        Update
                                    </button>
                                </div>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('ticket-canned-responses.destroy', $response) }}"
                            onsubmit="return confirm('Hapus template ini?')" class="mt-3 flex justify-end">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 rounded-xl font-bold bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                        Belum ada template balasan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
