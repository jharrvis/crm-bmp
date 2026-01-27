<x-guest-layout>
    <div class="w-full max-w-md p-6 relative z-10">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8">
            <div class="bg-blue-600 p-3 rounded-2xl shadow-lg shadow-blue-200 dark:shadow-blue-900/20 mb-4">
                <i data-lucide="key-round" class="text-white w-8 h-8"></i>
            </div>
            <div class="text-center">
                <span class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">Reset Password</span>
                <p class="text-xs text-slate-400 font-semibold tracking-widest uppercase mt-1">Kami akan mengirimkan
                    link ke email anda</p>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Card -->
        <div class="login-card p-8 rounded-[2rem] shadow-xl">
            <div class="mb-6 text-sm text-slate-500 dark:text-slate-400 text-center">
                {{ __('Lupa password? Tidak masalah. Cukup beritahu kami alamat email Anda dan kami akan mengirimkan tautan reset password.') }}
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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
                            required autofocus>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Button -->
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-2">
                    <span>Kirim Link Reset</span>
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}"
                    class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 flex items-center justify-center gap-2 transition-colors">
                    <i data-lucide="arrow-left" class="w-3 h-3"></i>
                    Kembali ke Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400 mt-8 font-medium">
            &copy; {{ date('Y') }} PT BMPnet ISP Management System.
        </p>
    </div>
</x-guest-layout>