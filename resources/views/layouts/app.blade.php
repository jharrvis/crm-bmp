<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BMPnet CRM') }}</title>

    @stack('vite')

    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151e2e',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Vite (Optional if strictly using Tailwind CDN, but good to keep for future) -->
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
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
            class="flex-1 lg:ml-72 sidebar-transition flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900 transition-all duration-300">
            <!-- Top Bar -->
            @include('layouts.header')

            <!-- Dashboard Content Container -->
            <div id="content-area" class="p-4 md:p-8 flex-1 overflow-y-auto">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>

</html>