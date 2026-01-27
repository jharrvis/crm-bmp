        // Initialize Icons
        lucide.createIcons();

        // Dark Mode Logic
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            if (document.documentElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        }

        // Check local storage for theme
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Sidebar & UI Logic
        function toggleSidebar() {
            if (window.innerWidth >= 1024) {
                // Desktop Collapse
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('main-content');

                sidebar.classList.toggle('sidebar-collapsed');

                if (sidebar.classList.contains('sidebar-collapsed')) {
                    mainContent.classList.toggle('lg:ml-72', false);
                    mainContent.classList.toggle('lg:ml-20', true);
                } else {
                    mainContent.classList.toggle('lg:ml-20', false);
                    mainContent.classList.toggle('lg:ml-72', true);
                }
            } else {
                // Mobile Toggle
                toggleSidebarMobile();
            }
        }

        function toggleSidebarMobile() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (sidebar.classList.contains('sidebar-hide')) {
                sidebar.classList.replace('sidebar-hide', 'sidebar-show');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.replace('opacity-0', 'opacity-100'), 10);
            } else {
                sidebar.classList.replace('sidebar-show', 'sidebar-hide');
                overlay.classList.replace('opacity-100', 'opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        function toggleSubmenu(id) {
            // If sidebar is collapsed on desktop, auto-expand it first or handle differently
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('sidebar-collapsed') && window.innerWidth >= 1024) {
                toggleSidebar(); // Expand sidebar to show menu
                setTimeout(() => {
                    const menu = document.getElementById(id);
                    menu.classList.toggle('submenu-active');
                }, 200);
                return;
            }

            const menu = document.getElementById(id);
            menu.classList.toggle('submenu-active');
        }

        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = ['profile-menu', 'notification-menu'];

            // Close other dropdowns
            allDropdowns.forEach(d => {
                if (d !== id) document.getElementById(d).classList.add('hidden');
            });

            dropdown.classList.toggle('hidden');
        }

        // Close dropdowns on outside click
        window.onclick = function (event) {
            if (!event.target.closest('#profile-menu') && !event.target.closest('#notification-menu') && !event.target.closest('button')) {
                document.getElementById('profile-menu').classList.add('hidden');
                document.getElementById('notification-menu').classList.add('hidden');
            }
        }

        // Section Change with Skeleton Loader
        function changeSection(id) {
            const skeleton = document.getElementById('skeleton-loader');
            const realContent = document.getElementById('real-content');

            realContent.classList.add('opacity-0');
            skeleton.classList.remove('hidden');

            setTimeout(() => {
                skeleton.classList.add('hidden');
                realContent.classList.remove('opacity-0');
                console.log('Navigated to:', id);
            }, 600);
        }

        // Chart.js Configuration
        function initCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? '#334155' : '#f1f5f9';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            // Growth Chart
            const ctxGrowth = document.getElementById('growthChart').getContext('2d');
            new Chart(ctxGrowth, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'],
                    datasets: [{
                        label: 'Pelanggan Baru',
                        data: [450, 620, 580, 890, 1100, 950, 1300, 1550],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: isDark ? '#1e293b' : '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { border: { display: false }, grid: { color: gridColor }, ticks: { color: textColor } },
                        x: { border: { display: false }, grid: { display: false }, ticks: { color: textColor } }
                    }
                }
            });

            // Ticket Category Chart
            const ctxTicket = document.getElementById('ticketChart').getContext('2d');
            new Chart(ctxTicket, {
                type: 'doughnut',
                data: {
                    labels: ['LOS (Kabel Putus)', 'Lemot', 'Router Error', 'Billing'],
                    datasets: [{
                        data: [40, 25, 20, 15],
                        backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                color: textColor
                            }
                        }
                    }
                }
            });
        }

        // Initialize App
        window.onload = () => {
            initCharts();
            // Show dynamic greeting based on time
            const hour = new Date().getHours();
            const welcome = document.querySelector('h1');
            if (hour < 12) welcome.innerHTML = "Selamat Pagi, Rizky! 🌅";
            else if (hour < 18) welcome.innerHTML = "Selamat Siang, Rizky! ☀️";
            else welcome.innerHTML = "Selamat Malam, Rizky! 🌙";
        };
