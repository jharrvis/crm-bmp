<x-guest-layout>
    <div class="relative z-10 w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] backdrop-blur xl:grid xl:min-h-[720px] xl:grid-cols-[minmax(0,0.96fr)_minmax(0,1.04fr)]">
            <div class="px-6 py-8 sm:px-10 lg:px-12 xl:px-14 xl:py-12">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/25">
                        <i data-lucide="key-round" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-lg font-black tracking-tight text-slate-900">BMP<span class="text-blue-600">net</span></p>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Password Recovery</p>
                    </div>
                </div>

                <div class="mt-10 max-w-md">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-600">Forgot Password</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Reset akses akun internal.</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Masukkan email Anda. Kami akan mengirim tautan reset password untuk melanjutkan proses login.
                    </p>
                </div>

                <x-auth-session-status class="mt-6 max-w-md rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="mt-8 max-w-md space-y-5">
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
                                required autofocus>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-500">
                        Link reset akan dikirim ke email tersebut jika akun ditemukan di sistem.
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 hover:shadow-blue-600/35 active:scale-[0.99]">
                        <span>Kirim Link Reset</span>
                        <i data-lucide="send" class="h-4 w-4"></i>
                    </button>

                    <a href="{{ route('login') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:text-slate-800">
                        <i data-lucide="arrow-left" class="h-4 w-4"></i>
                        <span>Kembali ke login</span>
                    </a>
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
                        <i data-lucide="mail-check" class="h-8 w-8 text-blue-200"></i>
                    </div>
                    <p class="mt-8 text-sm font-semibold uppercase tracking-[0.34em] text-blue-200">Account Recovery</p>
                    <h2 class="mt-5 text-4xl font-black leading-tight">
                        Pulihkan akses akun internal Anda dengan lebih cepat.
                    </h2>
                    <p class="mt-5 text-base leading-8 text-slate-300">
                        Masukkan email yang terdaftar di sistem. Kami akan mengirim tautan reset untuk membuat password baru dan melanjutkan login ke CRM.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
