<x-app-layout>
    <!-- SKELETON LOADER -->
    <div id="skeleton-loader" class="hidden space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="h-32 rounded-3xl skeleton"></div>
            <div class="h-32 rounded-3xl skeleton"></div>
            <div class="h-32 rounded-3xl skeleton"></div>
            <div class="h-32 rounded-3xl skeleton"></div>
        </div>
        <div class="h-96 rounded-3xl skeleton"></div>
    </div>

    <!-- MAIN DASHBOARD CONTENT -->
    <div id="real-content" class="space-y-8 animate-in fade-in duration-700">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-white">Halo,
                    {{ Auth::user()->name }}! 👋</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Status jaringan BMPnet terpantau <span
                        class="text-green-600 dark:text-green-400 font-bold italic underline">Stabil</span> hari ini.
                </p>
            </div>
            <div
                class="flex items-center gap-2 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl w-fit">
                <button
                    class="px-4 py-2 text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/40 rounded-xl">24
                    Jam</button>
                <button
                    class="px-4 py-2 text-xs font-bold text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 rounded-xl">7
                    Hari</button>
                <button
                    class="px-4 py-2 text-xs font-bold text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 rounded-xl">30
                    Hari</button>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Stat Item 1 -->
            <div
                class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="flex items-center gap-1 text-xs font-bold text-green-500 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-lg">+42</span>
                </div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Pelanggan</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">4,284</h3>
            </div>
            <!-- Stat Item 2 -->
            <div
                class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="flex items-center gap-1 text-xs font-bold text-blue-500 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded-lg">99.9%</span>
                </div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">SLA Uptime</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">23d 12h</h3>
            </div>
            <!-- Stat Item 3 -->
            <div
                class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="flex items-center gap-1 text-xs font-bold text-orange-500 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 px-2 py-1 rounded-lg">High</span>
                </div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Tiket Masalah</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">12 <span
                        class="text-sm font-normal text-slate-400">Terbuka</span></h3>
            </div>
            <!-- Stat Item 4 -->
            <div
                class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110">
                        <i data-lucide="trending-up" class="w-6 h-6"></i>
                    </div>
                    <span
                        class="flex items-center gap-1 text-xs font-bold text-green-500 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-lg">+12.4%</span>
                </div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Estimasi Revenue</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">842.5M</h3>
            </div>
        </div>

        <!-- Charts Area -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue/Growth Chart -->
            <div
                class="bg-white dark:bg-slate-800 p-6 md:p-8 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-white">Pertumbuhan Pelanggan</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Data bulanan tahun 2025</p>
                    </div>
                    <button class="p-2 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-slate-400">
                        <i data-lucide="download" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="h-[300px]">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>

            <!-- Ticket Chart -->
            <div
                class="bg-white dark:bg-slate-800 p-6 md:p-8 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-white">Kategori Tiket Masalah</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi keluhan teknis</p>
                    </div>
                    <select
                        class="text-xs font-bold bg-slate-50 dark:bg-slate-700 border-none rounded-xl focus:ring-0 text-slate-600 dark:text-slate-200">
                        <option>Bulan Ini</option>
                        <option>Bulan Lalu</option>
                    </select>
                </div>
                <div class="h-[300px] flex justify-center">
                    <canvas id="ticketChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div
            class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div
                class="p-8 border-b border-slate-100 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h4 class="font-bold text-slate-800 dark:text-white">Aktivitas Teknik Terakhir</h4>
                <button class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">Lihat Log
                    Lengkap</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-700/30 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            <th class="px-8 py-4">Teknisi / Agen</th>
                            <th class="px-8 py-4">Aktivitas</th>
                            <th class="px-8 py-4">Lokasi / Area</th>
                            <th class="px-8 py-4">Waktu</th>
                            <th class="px-8 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-700 text-sm">
                        @for ($i = 0; $i < 3; $i++)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center">
                                            <i data-lucide="user" class="w-4 h-4 text-slate-500"></i>
                                        </div>
                                        <span class="font-semibold text-slate-700 dark:text-slate-200">Teknisi
                                            {{ $i + 1 }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-slate-500 dark:text-slate-400 italic">Maintenance ODP</td>
                                <td class="px-8 py-5 font-medium text-slate-600 dark:text-slate-300">Area Salatiga</td>
                                <td class="px-8 py-5 text-slate-400">{{ now()->subMinutes($i * 15)->format('H:i') }} WIB</td>
                                <td class="px-8 py-5">
                                    <span
                                        class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-[10px] font-bold uppercase">Selesai</span>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart Init Script (temporary for demo) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctxGrowth = document.getElementById('growthChart').getContext('2d');
            const ctxTicket = document.getElementById('ticketChart').getContext('2d');

            new Chart(ctxGrowth, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Pelanggan Baru',
                        data: [120, 190, 300, 500, 200, 300],
                        borderColor: '#2563eb',
                        tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            new Chart(ctxTicket, {
                type: 'doughnut',
                data: {
                    labels: ['Koneksi LOS', 'Modem Rusak', 'Billing'],
                    datasets: [{
                        data: [12, 5, 3],
                        backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
    </script>
</x-app-layout>