<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Detail Tiket</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="font-mono text-sm font-bold text-slate-500">{{ $ticket->ticket_number }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                </div>
            </div>
            <a href="{{ route('tickets.index') }}"
                class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-8 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
                    <div class="flex flex-col gap-2">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ $ticket->subject }}</h3>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold">{{ ucfirst($ticket->category) }}</span>
                            <span class="px-2.5 py-1 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-bold">{{ strtoupper($ticket->priority) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-700/30 border border-slate-200 dark:border-slate-700 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Pelanggan</p>
                            <p class="mt-2 font-bold text-slate-800 dark:text-white">{{ $ticket->client->name }}</p>
                            <p class="text-sm text-slate-500">{{ $ticket->client->client_code }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-700/30 border border-slate-200 dark:border-slate-700 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Kontak Utama</p>
                            <p class="mt-2 font-bold text-slate-800 dark:text-white">{{ $ticket->client->primaryContact?->name ?? '-' }}</p>
                            <p class="text-sm text-slate-500">{{ $ticket->client->primaryContact?->phone ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-700/30 border border-slate-200 dark:border-slate-700 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Langganan</p>
                            <p class="mt-2 font-bold text-slate-800 dark:text-white">{{ $ticket->subscription?->subscription_code ?? '-' }}</p>
                            <p class="text-sm text-slate-500">{{ $ticket->subscription?->package?->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Thread Percakapan</h3>
                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ $ticket->replies->count() }} balasan</span>
                    </div>

                    <div class="mt-6 space-y-4">
                        @foreach($ticket->replies as $reply)
                            <div class="rounded-2xl border {{ $reply->is_internal ? 'border-amber-200 dark:border-amber-900/30 bg-amber-50/80 dark:bg-amber-900/10' : ($reply->author_type === 'staff' ? 'border-blue-200 dark:border-blue-900/30 bg-blue-50/70 dark:bg-blue-900/10' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30') }} p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white">
                                            {{ $reply->author_type === 'staff' ? ($reply->user?->name ?? 'Staff') : ($reply->portalAccount?->client?->name ?? 'Client') }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <p class="text-xs uppercase tracking-widest text-slate-400">{{ $reply->author_type }}</p>
                                            @if($reply->is_internal)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                    Internal note
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $reply->created_at?->format('d M Y H:i') }}</p>
                                </div>
                                <div class="mt-4 text-sm leading-6 text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $reply->message }}</div>
                                @if($reply->attachments->isNotEmpty())
                                    @php
                                        $imageAttachments = $reply->attachments->filter(fn ($attachment) => str_starts_with((string) $attachment->mime_type, 'image/'));
                                        $fileAttachments = $reply->attachments->reject(fn ($attachment) => str_starts_with((string) $attachment->mime_type, 'image/'));
                                    @endphp

                                    @if($imageAttachments->isNotEmpty())
                                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3">
                                            @foreach($imageAttachments as $attachment)
                                                <button type="button"
                                                    class="group block w-full text-left rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 ticket-image-trigger"
                                                    data-image-url="{{ $attachment->public_url }}"
                                                    data-image-name="{{ $attachment->original_name }}">
                                                    <img src="{{ $attachment->public_url }}" alt="{{ $attachment->original_name }}" class="w-full h-36 object-cover group-hover:scale-[1.02] transition-transform">
                                                    <div class="px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 truncate">
                                                        {{ $attachment->original_name }}
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($fileAttachments->isNotEmpty())
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach($fileAttachments as $attachment)
                                                <a href="{{ $attachment->public_url }}" target="_blank"
                                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                                    <i data-lucide="paperclip" class="w-4 h-4"></i>
                                                    <span>{{ $attachment->original_name }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Balas Tiket</h3>
                    <form method="POST" action="{{ route('tickets.reply', $ticket) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Template Balasan</label>
                            <select id="cannedResponseSelect"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih template balasan</option>
                                @foreach(config('tickets.canned_responses', []) as $key => $template)
                                    <option value="{{ $key }}" data-message="{{ $template['message'] }}">
                                        {{ $template['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <textarea name="message" rows="5"
                            id="ticketReplyMessage"
                            class="w-full rounded-2xl border border-slate-200 dark:border-slate-600 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Tulis balasan untuk client..." required></textarea>
                        <label class="inline-flex items-center gap-3 rounded-2xl border border-amber-200 dark:border-amber-900/30 bg-amber-50/80 dark:bg-amber-900/10 px-4 py-3 cursor-pointer">
                            <input type="checkbox" name="is_internal" value="1"
                                class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm text-slate-700 dark:text-slate-200">
                                Simpan sebagai <strong>catatan internal</strong> saja. Tidak dikirim ke client portal.
                            </span>
                        </label>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Lampiran</label>
                            <input type="file" name="attachments[]" multiple
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Maksimal 5MB per file. Format: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, ZIP, TXT.</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                                Kirim Balasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="xl:col-span-4 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Tindakan</h3>
                    <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                            <select name="status"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['open', 'in_progress', 'waiting_client', 'resolved', 'closed'] as $status)
                                    <option value="{{ $status }}" {{ $ticket->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Priority</label>
                            <select name="priority"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['low', 'normal', 'high', 'urgent'] as $priority)
                                    <option value="{{ $priority }}" {{ $ticket->priority === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Assign Staff</label>
                            <select name="assigned_to"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Belum di-assign</option>
                                @foreach($staffUsers as $user)
                                    <option value="{{ $user->id }}" {{ $ticket->assigned_to === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold bg-slate-900 dark:bg-blue-600 text-white hover:bg-slate-800 dark:hover:bg-blue-700 transition-colors">
                                Simpan Tindakan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Timeline</h3>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Dibuat</span>
                            <span class="font-medium text-slate-800 dark:text-white">{{ $ticket->created_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">First response</span>
                            <span class="font-medium text-slate-800 dark:text-white">{{ $ticket->first_response_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Resolved</span>
                            <span class="font-medium text-slate-800 dark:text-white">{{ $ticket->resolved_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Closed</span>
                            <span class="font-medium text-slate-800 dark:text-white">{{ $ticket->closed_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="ticketImageLightbox"
        class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-5xl">
            <button type="button"
                id="ticketImageLightboxClose"
                class="absolute -top-12 right-0 inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-bold text-white hover:bg-white/20 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
                Tutup
            </button>
            <div class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-900 shadow-2xl">
                <img id="ticketImageLightboxPreview" src="" alt="" class="w-full max-h-[78vh] object-contain bg-slate-950">
                <div class="flex items-center justify-between gap-4 px-5 py-4 bg-slate-900/90">
                    <div id="ticketImageLightboxName" class="text-sm font-medium text-slate-200 truncate"></div>
                    <a id="ticketImageLightboxLink" href="#" target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 transition-colors">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Buka File
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const cannedResponseSelect = document.getElementById('cannedResponseSelect');
                const replyMessage = document.getElementById('ticketReplyMessage');
                const lightbox = document.getElementById('ticketImageLightbox');
                const lightboxPreview = document.getElementById('ticketImageLightboxPreview');
                const lightboxName = document.getElementById('ticketImageLightboxName');
                const lightboxLink = document.getElementById('ticketImageLightboxLink');
                const lightboxClose = document.getElementById('ticketImageLightboxClose');

                if (cannedResponseSelect && replyMessage) {
                    cannedResponseSelect.addEventListener('change', function () {
                        const selectedOption = cannedResponseSelect.options[cannedResponseSelect.selectedIndex];
                        const templateMessage = selectedOption?.getAttribute('data-message');

                        if (!templateMessage) {
                            return;
                        }

                        replyMessage.value = templateMessage;
                        replyMessage.focus();
                    });
                }

                if (!lightbox || !lightboxPreview || !lightboxName || !lightboxLink || !lightboxClose) {
                    return;
                }

                function closeLightbox() {
                    lightbox.classList.add('hidden');
                    lightbox.classList.remove('flex');
                    lightboxPreview.setAttribute('src', '');
                    lightboxPreview.setAttribute('alt', '');
                }

                document.querySelectorAll('.ticket-image-trigger').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        const imageUrl = trigger.getAttribute('data-image-url');
                        const imageName = trigger.getAttribute('data-image-name') || 'Lampiran gambar';

                        if (!imageUrl) {
                            return;
                        }

                        lightboxPreview.setAttribute('src', imageUrl);
                        lightboxPreview.setAttribute('alt', imageName);
                        lightboxName.textContent = imageName;
                        lightboxLink.setAttribute('href', imageUrl);
                        lightbox.classList.remove('hidden');
                        lightbox.classList.add('flex');
                    });
                });

                lightboxClose.addEventListener('click', closeLightbox);

                lightbox.addEventListener('click', function (event) {
                    if (event.target === lightbox) {
                        closeLightbox();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) {
                        closeLightbox();
                    }
                });

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        </script>
    @endpush
</x-app-layout>
