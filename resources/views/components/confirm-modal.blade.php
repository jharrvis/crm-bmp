{{-- Confirm Delete Modal --}}
<div id="confirmModal" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="confirmBackdrop">
    </div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300"
            id="confirmPanel">
            <div class="p-6 text-center">
                <div
                    class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2" id="confirmTitle">Konfirmasi</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6" id="confirmText">
                    Apakah Anda yakin ingin melanjutkan?
                </p>
                <div class="flex gap-3 justify-center">
                    <button onclick="hideConfirmModal()"
                        class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button id="confirmYesBtn"
                        class="px-5 py-2.5 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50">
                        <svg id="confirmSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span id="confirmBtnText">Ya, Lanjutkan!</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toast-container" class="fixed top-4 right-4 z-[80] flex flex-col gap-2 pointer-events-none"></div>

{{-- All modal/toast functions are defined in /assets/js/script.js --}}