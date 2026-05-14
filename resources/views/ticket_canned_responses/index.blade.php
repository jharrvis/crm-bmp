<x-app-layout>
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Master Template Balasan Ticket</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola canned responses agar tim support bisa membalas lebih cepat dan konsisten.</p>
                </div>
                <button type="button"
                    data-modal-target="createCannedResponseModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Template
                </button>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 dark:border-slate-700 bg-blue-50/70 dark:bg-blue-900/10 p-5 md:p-6 mb-8">
                <h4 class="text-sm font-bold uppercase tracking-widest text-blue-700 dark:text-blue-300">Placeholder Tersedia</h4>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 mb-4">
                    Gunakan placeholder berikut di isi template agar sistem otomatis mengganti nilainya sesuai ticket yang sedang dibalas.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($placeholders as $placeholder => $label)
                        <div class="rounded-xl border border-blue-100 dark:border-blue-900/30 bg-white/80 dark:bg-slate-800 px-4 py-3">
                            <div class="font-mono text-sm font-bold text-blue-700 dark:text-blue-300">{{ '{' . '{' . $placeholder . '}' . '}' }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($responses->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-500 dark:text-slate-400">
                    Belum ada template balasan.
                </div>
            @else
                <div class="overflow-x-auto no-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                                <th class="p-4 pl-6">Template</th>
                                <th class="p-4">Kategori</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Urutan</th>
                                <th class="p-4">Isi Singkat</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($responses as $response)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="p-4 pl-6">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ $response->title }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $response->slug }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                            {{ $response->category ? ucfirst($response->category) : 'Semua kategori' }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $response->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                                            {{ $response->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                        {{ $response->sort_order }}
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm text-slate-600 dark:text-slate-300 max-w-[420px] truncate">
                                            {{ $response->message }}
                                        </div>
                                    </td>
                                    <td class="p-4 pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                data-modal-target="editCannedResponseModal-{{ $response->id }}"
                                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                                Edit
                                            </button>

                                            <form method="POST" action="{{ route('ticket-canned-responses.destroy', $response) }}"
                                                onsubmit="return confirm('Hapus template ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-bold bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div id="createCannedResponseModal" data-modal-root class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div class="w-full max-w-4xl rounded-[2rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl">
            <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h4 class="text-lg font-bold text-slate-800 dark:text-white">Tambah Template Baru</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Buat template balasan baru untuk tim support.</p>
                </div>
                <button type="button" data-modal-close class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('ticket-canned-responses.store') }}" class="p-6 space-y-5">
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
                    <textarea name="message" rows="8"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Isi template balasan..." required>{{ old('message') }}</textarea>
                </div>

                <label class="inline-flex items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Aktifkan template ini</span>
                </label>

                <div class="flex justify-end gap-3">
                    <button type="button" data-modal-close
                        class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach($responses as $response)
        <div id="editCannedResponseModal-{{ $response->id }}" data-modal-root class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-4xl rounded-[2rem] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl">
                <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Edit Template</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $response->title }}</p>
                    </div>
                    <button type="button" data-modal-close class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('ticket-canned-responses.update', $response) }}" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Judul</label>
                            <input type="text" name="title" value="{{ $response->title }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Kategori</label>
                            <select name="category"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua kategori</option>
                                @foreach(['connectivity', 'billing', 'technical', 'general'] as $category)
                                    <option value="{{ $category }}" {{ $response->category === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Urutan</label>
                            <input type="number" name="sort_order" value="{{ $response->sort_order }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                                min="0" max="9999">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pesan Template</label>
                        <textarea name="message" rows="8"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                            required>{{ $response->message }}</textarea>
                    </div>

                    <label class="inline-flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" {{ $response->is_active ? 'checked' : '' }}
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                    </label>

                    <div class="flex justify-end gap-3">
                        <button type="button" data-modal-close
                            class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Update Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function openModal(modalId) {
                    const modal = document.getElementById(modalId);

                    if (!modal) {
                        return;
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeModal(modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                document.querySelectorAll('[data-modal-target]').forEach((trigger) => {
                    trigger.addEventListener('click', function () {
                        openModal(trigger.getAttribute('data-modal-target'));
                    });
                });

                document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
                    trigger.addEventListener('click', function () {
                        const modal = trigger.closest('[data-modal-root]');

                        if (modal) {
                            closeModal(modal);
                        }
                    });
                });

                document.querySelectorAll('[data-modal-root]').forEach((modal) => {
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) {
                            closeModal(modal);
                        }
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    document.querySelectorAll('[data-modal-root]').forEach((modal) => {
                        if (!modal.classList.contains('hidden')) {
                            closeModal(modal);
                        }
                    });
                });

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        </script>
    @endpush
</x-app-layout>
