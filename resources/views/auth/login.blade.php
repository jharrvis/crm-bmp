<x-guest-layout>
    <!-- Full-screen overlay to hide default guest layout centering and blobs -->
    <div class="fixed inset-0 z-40 flex flex-wrap min-h-screen w-full bg-white dark:bg-slate-900 overflow-y-auto">
        
        <!-- Left Form Column -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 md:px-24 xl:px-32 py-10 relative">
            


            <div class="w-full max-w-md mx-auto mt-12 lg:mt-0">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">Sign In</h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400">Enter your email and password to sign in!</p>

                <x-auth-session-status class="mt-8" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Email<span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="info@gmail.com"
                                class="block w-full rounded-lg border border-slate-300 bg-transparent px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-600 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                required autofocus autocomplete="username">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <i data-lucide="mail" class="h-5 w-5 text-slate-400"></i>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Password<span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password"
                                placeholder="Enter your password"
                                class="block w-full rounded-lg border border-slate-300 bg-transparent px-4 py-3 pr-20 text-slate-900 placeholder-slate-400 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:border-slate-600 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                required autocomplete="current-password">
                            
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-2">
                                <i data-lucide="lock" class="h-5 w-5 text-slate-400 pointer-events-none"></i>
                                <button type="button" id="togglePasswordButton" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none flex items-center justify-center p-1">
                                    <i data-lucide="eye" class="h-5 w-5" id="eye-icon"></i>
                                </button>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:checked:bg-blue-500">
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Keep me logged in</span>
                        </label>
                        
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" id="login-btn" class="w-full rounded-lg bg-blue-600 px-4 py-3 text-center text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/50 disabled:opacity-70 flex justify-center items-center gap-2">
                        <span id="btn-text">Sign In</span>
                        <svg id="btn-spinner" class="hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                @if (config('auth.registration_enabled') && Route::has('register'))
                    <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-500">Sign Up</a>
                    </p>
                @endif

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
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const togglePasswordButton = document.getElementById('togglePasswordButton');

        togglePasswordButton?.addEventListener('click', function () {
            const nextType = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = nextType;
            eyeIcon.setAttribute('data-lucide', nextType === 'password' ? 'eye' : 'eye-off');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        const loginForm = document.querySelector('form');
        loginForm?.addEventListener('submit', function () {
            const btn = document.getElementById('login-btn');
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
