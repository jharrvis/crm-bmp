<x-guest-layout>
    <!-- Full-screen overlay to hide default guest layout centering and blobs -->
    <div class="fixed inset-0 z-40 flex flex-wrap min-h-screen w-full bg-white dark:bg-slate-900 overflow-y-auto">
        
        <!-- Left Form Column -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 md:px-24 xl:px-32 py-10 relative">
            
            <div class="w-full max-w-md mx-auto mt-12 lg:mt-0">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">Verify Email</h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400">Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda? Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan yang lain.</p>

                @if (session('status') == 'verification-link-sent')
                    <div class="mt-8 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm leading-6 text-green-600 dark:text-green-400">
                        Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
                    </div>
                @endif

                <div class="mt-8 flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" id="verify-btn" class="rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 flex justify-center items-center gap-2 disabled:opacity-70">
                            <span id="btn-text">Kirim Ulang Email Verifikasi</span>
                            <svg id="btn-spinner" class="hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white underline transition">
                            Log Out
                        </button>
                    </form>
                </div>

                <p class="mt-12 text-center text-xs text-slate-500 dark:text-slate-500">
                    &copy; 2026 PT BMPnet ISP Management System. All rights reserved.
                </p>
            </div>
        </div>

        <!-- Right Branding Column -->
        <div class="hidden lg:flex lg:w-1/2 bg-[#0B1533] items-center justify-center relative overflow-hidden">
            <!-- Grid Pattern from SVG -->
            <img src="{{ asset('assets/img/auth-grid-01.svg') }}" alt="" class="pointer-events-none absolute left-0 top-0 w-56 opacity-70">
            <img src="{{ asset('assets/img/auth-grid-01.svg') }}" alt="" class="pointer-events-none absolute bottom-0 right-0 w-56 rotate-180 opacity-70">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.22),_transparent_40%),radial-gradient(circle_at_bottom,_rgba(37,99,235,0.2),_transparent_42%)]"></div>
            
            <div class="relative z-10 text-center px-12">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/25">
                        <i data-lucide="shield-check" class="h-6 w-6"></i>
                    </div>
                    <span class="text-white text-4xl font-bold">BMP<span class="text-blue-400">net</span> CRM</span>
                </div>
                <p class="text-slate-300 text-lg max-w-sm mx-auto mt-6">
                    Satu portal internal untuk operasional jaringan dan billing.
                </p>
            </div>
        </div>
    </div>

    <script>
        const verifyForm = document.querySelector('form[action="{{ route('verification.send') }}"]');
        verifyForm?.addEventListener('submit', function () {
            const btn = document.getElementById('verify-btn');
            const text = document.getElementById('btn-text');
            const spinner = document.getElementById('btn-spinner');

            if (btn && text && spinner) {
                btn.disabled = true;
                text.classList.add('hidden');
                spinner.classList.remove('hidden');
            }
        });
    </script>
</x-guest-layout>
