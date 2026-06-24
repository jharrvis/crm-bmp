<x-guest-layout>
    <div class="relative z-10 w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] backdrop-blur xl:grid xl:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)]">
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

            <div class="relative hidden xl:flex">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.24),_transparent_42%),radial-gradient(circle_at_bottom_right,_rgba(15,23,42,0.24),_transparent_45%)]"></div>
                <div class="relative flex min-h-full w-full flex-col justify-between bg-slate-950 px-10 py-12 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-300">Account Security</p>
                        <h2 class="mt-4 max-w-sm text-3xl font-black leading-tight">
                            Recovery flow yang lebih rapi untuk akun staf internal.
                        </h2>
                        <p class="mt-4 max-w-md text-sm leading-7 text-slate-300">
                            Gunakan email yang terdaftar di sistem. Setelah menerima tautan reset, buat password baru lalu login kembali ke dashboard.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-400/10 text-emerald-300">
                                    <i data-lucide="shield-check" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold">Tautan aman</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-300">Reset password dikirim lewat email dan hanya berlaku untuk sesi pemulihan akun.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-400/10 text-blue-300">
                                    <i data-lucide="mail-check" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold">Verifikasi email</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-300">Pastikan alamat email aktif agar tautan reset dapat diterima tanpa kendala.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Need help?</p>
                            <p class="mt-3 text-sm leading-6 text-slate-300">Jika email tidak diterima, hubungi administrator sistem atau tim IT internal BMPnet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
