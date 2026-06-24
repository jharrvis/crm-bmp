<x-guest-layout>
    <div class="relative z-10 w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] backdrop-blur xl:grid xl:min-h-[720px] xl:grid-cols-[minmax(0,0.96fr)_minmax(0,1.04fr)]">
            <div class="px-6 py-8 sm:px-10 lg:px-12 xl:px-16 xl:py-12">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-slate-600">
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                    <span>Back to dashboard</span>
                </a>

                <div class="mt-20 max-w-md">
                    <h1 class="text-4xl font-black tracking-tight text-slate-900">Sign In</h1>
                    <p class="mt-3 text-base leading-7 text-slate-500">
                        Enter your email and password to sign in!
                    </p>
                </div>

                <x-auth-session-status class="mt-6 max-w-md rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 max-w-md space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-bold text-slate-700">Email <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i data-lucide="mail" class="h-5 w-5"></i>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="info@bmp.net.id"
                                class="block w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 py-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                required autofocus autocomplete="username">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <label for="password" class="text-sm font-bold text-slate-700">Password <span class="text-red-500">*</span></label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-700">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i data-lucide="lock" class="h-5 w-5"></i>
                            </div>
                            <input type="password" id="password" name="password" placeholder="Enter your password"
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
                            <span>Keep me logged in</span>
                        </label>
                        <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-400">
                            <i data-lucide="shield-check" class="h-4 w-4"></i>
                            <span>Secure access</span>
                        </div>
                    </div>

                    <button type="submit" id="login-btn"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 hover:shadow-blue-600/35 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70">
                        <span id="btn-text">Sign In</span>
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

            <div class="relative hidden xl:flex items-center justify-center overflow-hidden bg-[#0B1533] px-12 py-16 text-white">
                <img src="{{ asset('assets/img/auth-grid-01.svg') }}" alt="" class="pointer-events-none absolute left-0 top-0 w-56 opacity-70">
                <img src="{{ asset('assets/img/auth-grid-01.svg') }}" alt="" class="pointer-events-none absolute bottom-0 right-0 w-56 rotate-180 opacity-70">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.22),_transparent_40%),radial-gradient(circle_at_bottom,_rgba(37,99,235,0.2),_transparent_42%)]"></div>

                <div class="relative mx-auto flex max-w-md flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] border border-white/15 bg-white/10 backdrop-blur">
                        <i data-lucide="shield-check" class="h-8 w-8 text-blue-200"></i>
                    </div>
                    <p class="mt-8 text-sm font-semibold uppercase tracking-[0.34em] text-blue-200">BMPnet CRM</p>
                    <h2 class="mt-5 text-4xl font-black leading-tight">
                        Satu portal internal untuk operasional jaringan dan billing.
                    </h2>
                    <p class="mt-5 text-base leading-8 text-slate-300">
                        Kelola pelanggan, layanan, tagihan, dan support dari satu workspace yang lebih terstruktur untuk tim internal BMPnet.
                    </p>
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
