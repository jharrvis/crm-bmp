<x-guest-layout>
    <div class="relative z-10 w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] backdrop-blur xl:grid xl:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)]">
            <div class="px-6 py-8 sm:px-10 lg:px-12 xl:px-14 xl:py-12">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/25">
                        <i data-lucide="wifi" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-lg font-black tracking-tight text-slate-900">BMP<span class="text-blue-600">net</span></p>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">CRM Portal</p>
                    </div>
                </div>

                <div class="mt-10 max-w-md">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Sign In</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Masuk ke dashboard operasional.</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Gunakan akun internal Anda untuk mengakses data pelanggan, layanan, billing, dan support.
                    </p>
                </div>

                <x-auth-session-status class="mt-6 max-w-md rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 max-w-md space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-bold text-slate-700">Email</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i data-lucide="mail" class="h-5 w-5"></i>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="nama@bmp.net.id"
                                class="block w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 py-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                required autofocus autocomplete="username">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <label for="password" class="text-sm font-bold text-slate-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                    Lupa password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i data-lucide="lock" class="h-5 w-5"></i>
                            </div>
                            <input type="password" id="password" name="password" placeholder="Masukkan password Anda"
                                class="block w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-12 py-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                required autocomplete="current-password">
                            <button type="button" id="togglePasswordButton"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 transition-colors"
                                aria-label="Tampilkan atau sembunyikan password">
                                <i data-lucide="eye" class="h-5 w-5" id="eye-icon"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <label class="flex items-center gap-3 text-sm font-medium text-slate-600">
                            <input type="checkbox" name="remember"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                        <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-400">
                            <i data-lucide="shield-check" class="h-4 w-4"></i>
                            <span>Koneksi aman</span>
                        </div>
                    </div>

                    <button type="submit" id="login-btn"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 hover:shadow-blue-600/35 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70">
                        <span id="btn-text">Masuk Sekarang</span>
                        <i data-lucide="arrow-right" class="h-4 w-4" id="btn-icon"></i>
                        <svg id="btn-spinner" class="hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <p class="mt-8 max-w-md text-xs font-medium text-slate-400">
                    &copy; {{ date('Y') }} PT BMPnet ISP Management System. All rights reserved.
                </p>
            </div>

            <div class="relative hidden xl:flex">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.24),_transparent_42%),radial-gradient(circle_at_bottom_right,_rgba(15,23,42,0.24),_transparent_45%)]"></div>
                <div class="relative flex min-h-full w-full flex-col justify-between bg-slate-950 px-10 py-12 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-300">Network Operations</p>
                            <h2 class="mt-4 max-w-sm text-3xl font-black leading-tight">
                                Satu workspace untuk billing, layanan, dan support pelanggan.
                            </h2>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right backdrop-blur">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Status</p>
                            <p class="mt-2 text-lg font-black text-emerald-300">Online</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Akses Cepat</p>
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div class="rounded-2xl bg-white/5 p-4">
                                    <p class="text-2xl font-black">CRM</p>
                                    <p class="mt-1 text-xs text-slate-400">Pelanggan & layanan</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4">
                                    <p class="text-2xl font-black">NOC</p>
                                    <p class="mt-1 text-xs text-slate-400">Monitoring & koneksi</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4">
                                    <p class="text-2xl font-black">Billing</p>
                                    <p class="mt-1 text-xs text-slate-400">Invoice & pembayaran</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-sm font-bold">Lebih fokus</p>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Alur kerja operasional diringkas dalam satu dashboard internal.</p>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-sm font-bold">Lebih aman</p>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Hak akses modular per divisi dan aktivitas user tercatat lebih rapi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const togglePasswordButton = document.getElementById('togglePasswordButton');

        togglePasswordButton?.addEventListener('click', function () {
            const nextType = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = nextType;
            eyeIcon.setAttribute('data-lucide', nextType === 'password' ? 'eye' : 'eye-off');
            lucide.createIcons();
        });

        const loginForm = document.querySelector('form');
        loginForm?.addEventListener('submit', function () {
            const btn = document.getElementById('login-btn');
            const text = document.getElementById('btn-text');
            const icon = document.getElementById('btn-icon');
            const spinner = document.getElementById('btn-spinner');

            if (btn && text && icon && spinner) {
                btn.disabled = true;
                text.classList.add('hidden');
                icon.classList.add('hidden');
                spinner.classList.remove('hidden');
            }
        });
    </script>
</x-guest-layout>
