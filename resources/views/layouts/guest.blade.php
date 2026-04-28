<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BMPnet CRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Scripts & Styles -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        .login-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .dark .login-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(51, 65, 85, 0.7);
        }
    </style>
</head>

<body
    class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen flex items-center justify-center transition-colors duration-300 relative overflow-hidden">

    <!-- Background Decoration -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div
            class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] bg-blue-400/20 dark:bg-blue-600/10 rounded-full blur-3xl opacity-60 mix-blend-multiply dark:mix-blend-screen animate-blob">
        </div>
        <div
            class="absolute -bottom-[20%] -right-[10%] w-[70vw] h-[70vw] bg-purple-400/20 dark:bg-purple-600/10 rounded-full blur-3xl opacity-60 mix-blend-multiply dark:mix-blend-screen animate-blob animation-delay-2000">
        </div>
    </div>

    <!-- Toggle Dark Mode -->
    <button onclick="toggleDarkMode()"
        class="absolute top-6 right-6 p-3 bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 transition-all shadow-sm z-50">
        <i data-lucide="moon" id="dark-icon" class="w-5 h-5 hidden dark:block"></i>
        <i data-lucide="sun" id="light-icon" class="w-5 h-5 block dark:hidden"></i>
    </button>

    <!-- Main Content -->
    {{ $slot }}

    <script>
        lucide.createIcons();

        // Dark Mode Logic
        function toggleDarkMode() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Check Local Storage
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</body>

</html>
