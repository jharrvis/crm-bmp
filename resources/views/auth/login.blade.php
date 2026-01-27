<x-guest-layout>
    <div class="w-full max-w-md p-6 relative z-10">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="bg-blue-600 p-3 rounded-2xl shadow-lg shadow-blue-200 dark:shadow-blue-900/20 mb-4">
                <i data-lucide="wifi" class="text-white w-8 h-8"></i>
            </div>
            <div class="text-center">
                <span class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">BMP<span
                        class="text-blue-600">net</span></span>
                <p class="text-xs text-slate-400 font-semibold tracking-widest uppercase mt-1">ISP Management System</p>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Card -->
        <div class="login-card p-8 rounded-[2rem] shadow-xl">
            <div class="mb-8 text-center">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Selamat Datang 👋</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Silahkan login untuk mengakses dashboard.</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <!-- Email -->
                <div class="space-y-1.5 container-input">
                    <label for="email" class="text-xs font-bold text-slate-600 dark:text-slate-300 ml-1">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="nama@perusahaan.com"
                            class="block w-full pl-10 pr-3 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white/50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                            required autofocus autocomplete="username">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="space-y-1.5 container-input">
                    <div class="flex justify-between items-center ml-1">
                        <label for="password"
                            class="text-xs font-bold text-slate-600 dark:text-slate-300">Password</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            class="block w-full pl-10 pr-10 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white/50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                            required autocomplete="current-password">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer"
                            onclick="togglePassword()">
                            <i data-lucide="eye"
                                class="w-5 h-5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                id="eye-icon"></i>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between mt-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="remember"
                                class="peer h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:focus:ring-offset-slate-900">
                        </div>
                        <span
                            class="text-xs font-medium text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300 transition-colors">Ingat
                            saya</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">Lupa
                            Password?</a>
                    @endif
                </div>

                <!-- Button -->
                <button type="submit" id="login-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">
                    <span id="btn-text">Masuk Dashboard</span>
                    <i data-lucide="arrow-right" class="w-4 h-4" id="btn-icon"></i>
                    <!-- Loading Spinner (Hidden by default) -->
                    <svg id="btn-spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400 mt-8 font-medium">
            &copy; {{ date('Y') }} PT BMPnet ISP Management System. <br class="hidden sm:block">All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        // Loading State Logic
        const loginForm = document.querySelector('form');
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                const btn = document.getElementById('login-btn');
                const text = document.getElementById('btn-text');
                const icon = document.getElementById('btn-icon');
                const spinner = document.getElementById('btn-spinner');

                if(btn && text && icon && spinner) {
                    // Disable button
                    btn.disabled = true;
                    
                    // Hide text/icon & Show spinner
                    text.classList.add('hidden');
                    icon.classList.add('hidden');
                    spinner.classList.remove('hidden');
                }
            });
        }
    </script>
</x-guest-layout>