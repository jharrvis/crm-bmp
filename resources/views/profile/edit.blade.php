<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Pengaturan Profil</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola informasi akun dan keamanan Anda.</p>
            </div>
        </div>

        <!-- Profile Information -->
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                    </div>
                    Informasi Profil
                </h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-2 ml-12">Perbarui informasi profil dan alamat
                    email akun Anda.</p>
            </div>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="ml-12 space-y-5 max-w-xl" enctype="multipart/form-data">
                @csrf
                @method('patch')

                {{-- Avatar Upload --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Foto Profil</label>
                    <div class="flex items-center gap-5">
                        <div class="relative shrink-0">
                            <div id="avatarPreview"
                                class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg overflow-hidden">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="avatar"
                                class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl text-sm font-bold hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors border border-blue-200 dark:border-blue-800">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                                Pilih Foto
                            </label>
                            <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                            <p class="text-xs text-slate-400">Format: JPG, PNG. Maks: 2MB</p>
                            @error('avatar')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama
                        Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus
                        autocomplete="name"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nomor
                        Handphone/WA</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                        autocomplete="tel" placeholder="08..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat
                        Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                        autocomplete="username"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <div
                            class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                Alamat email Anda belum terverifikasi.
                                <button form="send-verification"
                                    class="underline font-bold hover:text-yellow-900 dark:hover:text-yellow-100">
                                    Klik di sini untuk mengirim ulang email verifikasi.
                                </button>
                            </p>
                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                    Link verifikasi baru telah dikirim ke email Anda.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Perubahan
                    </button>

                    @if (session('status') === 'profile-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm font-bold text-green-600 dark:text-green-400 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Tersimpan!
                        </p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Update Password -->
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <div
                        class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <i data-lucide="key" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                    </div>
                    Ubah Password
                </h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-2 ml-12">Pastikan akun Anda menggunakan password
                    yang kuat dan aman.</p>
            </div>

            <form method="post" action="{{ route('password.update') }}" class="ml-12 space-y-5 max-w-xl">
                @csrf
                @method('put')

                <div>
                    <label for="update_password_current_password"
                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password Saat
                        Ini</label>
                    <input type="password" id="update_password_current_password" name="current_password"
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    @error('current_password', 'updatePassword')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="update_password_password"
                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
                    <input type="password" id="update_password_password" name="password" autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    @error('password', 'updatePassword')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="update_password_password_confirmation"
                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi Password
                        Baru</label>
                    <input type="password" id="update_password_password_confirmation" name="password_confirmation"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    @error('password_confirmation', 'updatePassword')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold bg-green-600 text-white hover:bg-green-700 shadow-lg shadow-green-200 dark:shadow-none transition-all flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        Ubah Password
                    </button>

                    @if (session('status') === 'password-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm font-bold text-green-600 dark:text-green-400 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Tersimpan!
                        </p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Delete Account -->
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-red-200 dark:border-red-900/50 shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                        <i data-lucide="trash-2" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                    </div>
                    Hapus Akun
                </h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-2 ml-12">
                    Setelah akun dihapus, semua data dan informasi akan dihapus secara permanen.
                    Pastikan untuk mengunduh data yang ingin Anda simpan sebelum menghapus akun.
                </p>
            </div>

            <div class="ml-12">
                <button type="button" onclick="openDeleteAccountModal()"
                    class="px-5 py-2.5 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200 dark:shadow-none transition-all flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    Hapus Akun Saya
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div id="deleteAccountModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0"
            id="deleteAccountBackdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md transform scale-95 opacity-0 transition-all duration-300"
                id="deleteAccountPanel">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="p-6 text-center">
                        <div
                            class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Hapus Akun Anda?</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
                            Setelah akun dihapus, semua data akan dihapus permanen. Masukkan password untuk konfirmasi.
                        </p>

                        <div class="text-left mb-6">
                            <label for="delete_password"
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password</label>
                            <input type="password" id="delete_password" name="password" required
                                placeholder="Masukkan password Anda"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                            @error('password', 'userDeletion')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 justify-center">
                            <button type="button" onclick="closeDeleteAccountModal()"
                                class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200 dark:shadow-none transition-all flex items-center gap-2">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Ya, Hapus Akun
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function previewAvatar(input) {
                const preview = document.getElementById('avatarPreview');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function openDeleteAccountModal() {
                const modal = document.getElementById('deleteAccountModal');
                const backdrop = document.getElementById('deleteAccountBackdrop');
                const panel = document.getElementById('deleteAccountPanel');

                modal.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                }, 10);
                lucide.createIcons();
            }

            function closeDeleteAccountModal() {
                const modal = document.getElementById('deleteAccountModal');
                const backdrop = document.getElementById('deleteAccountBackdrop');
                const panel = document.getElementById('deleteAccountPanel');

                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');

                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }

            // Show modal if there are userDeletion errors
            @if ($errors->userDeletion->isNotEmpty())
                document.addEventListener('DOMContentLoaded', function () {
                    openDeleteAccountModal();
                });
            @endif
        </script>
    @endpush
</x-app-layout>