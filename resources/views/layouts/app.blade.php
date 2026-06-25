<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BMPnet CRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('vite')

    <!-- Scripts & Styles -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body
    class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 overflow-x-hidden transition-colors duration-300">

    <!-- Mobile Overlay -->
    <div id="sidebar-overlay" onclick="toggleSidebar()"
        class="fixed inset-0 bg-slate-900/40 z-40 hidden backdrop-blur-sm lg:hidden transition-opacity duration-300 opacity-0">
    </div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 sidebar-transition sidebar-hide lg:sidebar-show flex flex-col">
            <!-- Sidebar Header -->
            <div class="sidebar-header p-6 flex items-center justify-between h-20">
                <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap">
                    <div class="bg-blue-600 p-2.5 rounded-2xl shadow-lg shadow-blue-200 shrink-0">
                        <i data-lucide="wifi" class="text-white w-6 h-6"></i>
                    </div>
                    <div class="logo-text">
                        <span class="text-xl font-bold tracking-tight text-slate-800 dark:text-white">BMP<span
                                class="text-blue-600">net</span></span>
                        <p class="text-[10px] text-slate-400 font-semibold tracking-widest uppercase">ISP Management</p>
                    </div>
                </div>
                <button onclick="toggleSidebarMobile()"
                    class="lg:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl text-slate-500 dark:text-slate-400">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Divisi Context (Dynamic soon) -->
            <div class="px-6 mb-4 logo-text">
                <div
                    class="p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                            <i data-lucide="network" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase leading-none">
                                {{ Auth::user()->roles->first()->name ?? 'Guest' }}
                            </p>
                            <span
                                class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ Auth::user()->name ?? 'User' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            @include('layouts.sidebar')

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-700">
                <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-2xl sidebar-footer-box">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-600 shadow-md flex items-center justify-center text-white shrink-0">
                            <i data-lucide="headphones" class="w-5 h-5"></i>
                        </div>
                        <div class="sidebar-footer-text">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Butuh Bantuan?</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Kontak Support IT</p>
                        </div>
                    </div>
                    <button
                        class="w-full bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-500 transition-colors sidebar-footer-text">Buka
                        Dokumentasi</button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main id="main-content"
            class="flex-1 main-content-expanded sidebar-transition flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 transition-all duration-300">
            <!-- Top Bar -->
            @include('layouts.header')

            <!-- Dashboard Content Container -->
            <div id="content-area" class="p-4 md:p-8 flex-1 overflow-y-auto">
                <div class="space-y-6">
                    @if (!empty($breadcrumbs))
                        <x-breadcrumbs :items="$breadcrumbs" />
                    @endif

                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    <div id="searchQuickViewModal" class="fixed inset-0 z-[75] hidden">
        <div id="searchQuickViewBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6">
            <div id="searchQuickViewPanel"
                class="modal-panel w-full max-w-3xl rounded-[2rem] bg-white shadow-2xl dark:bg-slate-800 scale-95 opacity-0 transition-all duration-300 overflow-hidden">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-700 sm:px-8">
                    <div class="min-w-0">
                        <div id="searchQuickViewLabel" class="text-[11px] font-bold uppercase tracking-[0.2em] text-blue-500">Quick View</div>
                        <h2 id="searchQuickViewTitle" class="mt-2 truncate text-2xl font-bold text-slate-800 dark:text-white">Detail</h2>
                        <p id="searchQuickViewSubtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400"></p>
                    </div>
                    <button
                        type="button"
                        onclick="closeModal('searchQuickViewModal')"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <div id="searchQuickViewBody" class="max-h-[70vh] overflow-y-auto px-6 py-6 dark:bg-slate-800 sm:px-8">
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">
                        <svg class="h-4 w-4 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat detail...
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-900/40 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <p id="searchQuickViewHint" class="text-xs text-slate-500 dark:text-slate-400">
                        Gunakan quick view untuk melihat informasi penting tanpa pindah halaman.
                    </p>
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            onclick="closeModal('searchQuickViewModal')"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                            Tutup
                        </button>
                        <a
                            id="searchQuickViewPageLink"
                            href="#"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                            <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
                            Buka Halaman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>

</html>
