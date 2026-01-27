<x-guest-layout>
    <div class="w-full max-w-md p-6 relative z-10">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="bg-blue-600 p-3 rounded-2xl shadow-lg shadow-blue-200 dark:shadow-blue-900/20 mb-4">
                <i data-lucide="key-round" class="text-white w-8 h-8"></i>
            </div>
            <div class="text-center">
                <span class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">Reset Password</span>
                <p class="text-xs text-slate-400 font-semibold tracking-widest uppercase mt-1">Buat Password Baru Anda
                </p>
            </div>
        </div>

        <!-- Card -->
        <div class="login-card p-8 rounded-[2rem] shadow-xl">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Password Baru 🔐</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Masukkan password baru untuk akun Anda.</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email -->
                <div class="space-y-1.5 container-input">
                    <label for="email" class="text-xs font-bold text-slate-600 dark:text-slate-300 ml-1">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}"
                            placeholder="nama@perusahaan.com"
                            class="block w-full pl-10 pr-3 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white/50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                            required autofocus autocomplete="username">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="space-y-1.5 container-input">
                    <label for="password" class="text-xs font-bold text-slate-600 dark:text-slate-300 ml-1">Password
                        Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            class="block w-full pl-10 pr-3 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white/50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                            required autocomplete="new-password">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1.5 container-input">
                    <label for="password_confirmation"
                        class="text-xs font-bold text-slate-600 dark:text-slate-300 ml-1">Konfirmasi Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="shield-check" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="••••••••"
                            class="block w-full pl-10 pr-3 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white/50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                            required autocomplete="new-password">
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Button -->
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-2">
                    <span>Reset Password</span>
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400 mt-8 font-medium">
            &copy; {{ date('Y') }} PT BMPnet ISP Management System.
        </p>
    </div>
</x-guest-layout>