// Icons will be initialized in layout after DOM is ready

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
    const sidebar = document.getElementById('sidebar');
    const menu = document.getElementById(id);

    if (!menu) return;

    const content = menu.querySelector('.submenu-content');
    const chevron = menu.querySelector('.chevron-icon');

    // If sidebar is collapsed on desktop, let CSS handle hover
    if (sidebar && sidebar.classList.contains('sidebar-collapsed') && window.innerWidth >= 1024) {
        return; // Don't toggle via click, CSS hover handles it
    }

    // Toggle submenu active state
    menu.classList.toggle('submenu-active');

    // Rotate chevron
    if (chevron) chevron.classList.toggle('rotate-180');

    // Toggle visibility using max-height animation
    if (content) {
        if (content.classList.contains('submenu-open')) {
            content.classList.remove('submenu-open');
            content.style.maxHeight = '0';
        } else {
            content.classList.add('submenu-open');
            content.style.maxHeight = content.scrollHeight + 'px';
        }
    }
}

// Initialize submenus that should be open (active routes)
function initSubmenus() {
    document.querySelectorAll('.submenu-container').forEach(menu => {
        const content = menu.querySelector('.submenu-content');
        const chevron = menu.querySelector('.chevron-icon');

        if (!content) return;

        // Check if submenu should be open (has active class or active child)
        const isActive = menu.classList.contains('submenu-active');
        const hasActiveChild = content.querySelector('a.text-blue-600, a.font-bold');

        if (isActive || hasActiveChild) {
            content.classList.add('submenu-open');
            content.style.maxHeight = content.scrollHeight + 'px';
            menu.classList.add('submenu-active');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            content.classList.remove('submenu-open');
            content.style.maxHeight = '0';
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });
}

function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    if (!dropdown) return;

    const allDropdowns = ['profile-menu', 'notification-menu'];

    // Close other dropdowns
    allDropdowns.forEach(d => {
        const el = document.getElementById(d);
        if (el && d !== id) el.classList.add('hidden');
    });

    dropdown.classList.toggle('hidden');
}

// Close dropdowns on outside click
document.addEventListener('click', function (event) {
    const profileMenu = document.getElementById('profile-menu');
    const notificationMenu = document.getElementById('notification-menu');

    // Check if click is on dropdown toggle buttons
    const isProfileToggle = event.target.closest('[onclick*="profile-menu"]');
    const isNotificationToggle = event.target.closest('[onclick*="notification-menu"]');

    // Close profile menu if clicked outside
    if (profileMenu && !profileMenu.classList.contains('hidden')) {
        if (!event.target.closest('#profile-menu') && !isProfileToggle) {
            profileMenu.classList.add('hidden');
        }
    }

    // Close notification menu if clicked outside
    if (notificationMenu && !notificationMenu.classList.contains('hidden')) {
        if (!event.target.closest('#notification-menu') && !isNotificationToggle) {
            notificationMenu.classList.add('hidden');
        }
    }
});

// Section Change with Skeleton Loader
function changeSection(id) {
    const skeleton = document.getElementById('skeleton-loader');
    const realContent = document.getElementById('real-content');

    if (realContent) realContent.classList.add('opacity-0');
    if (skeleton) skeleton.classList.remove('hidden');

    setTimeout(() => {
        if (skeleton) skeleton.classList.add('hidden');
        if (realContent) realContent.classList.remove('opacity-0');
        console.log('Navigated to:', id);
    }, 600);
}

// Chart.js Configuration
function initCharts() {
    const growthCanvas = document.getElementById('growthChart');
    const ticketCanvas = document.getElementById('ticketChart');

    if (!growthCanvas || !ticketCanvas) return; // Skip if charts don't exist

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? '#334155' : '#f1f5f9';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    // Growth Chart
    const ctxGrowth = growthCanvas.getContext('2d');
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
    const ctxTicket = ticketCanvas.getContext('2d');
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

// Toast Notification System
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-[80] flex flex-col gap-2 pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-white dark:bg-slate-800' : 'bg-red-50 dark:bg-red-900/50';
    const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
    const icon = type === 'success' ? 'check-circle' : 'alert-circle';
    const textColor = 'text-slate-800 dark:text-white';

    toast.className = `${bgColor} border border-slate-200 dark:border-slate-700 shadow-xl rounded-2xl p-4 flex items-center gap-3 transform transition-all duration-300 translate-x-full opacity-0 pointer-events-auto min-w-[300px]`;
    toast.innerHTML = `
        <i data-lucide="${icon}" class="${iconColor} w-6 h-6 shrink-0"></i>
        <p class="font-bold text-sm ${textColor}">${message}</p>
    `;

    container.appendChild(toast);
    lucide.createIcons();

    // Animate In
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    });

    // Remove after 3s
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Confirm Modal System
let confirmCallback = null;

function showConfirmModal(title, text, callback) {
    confirmCallback = callback;
    const modal = document.getElementById('confirmModal');
    const backdrop = document.getElementById('confirmBackdrop');
    const panel = document.getElementById('confirmPanel');
    const confirmYesBtn = document.getElementById('confirmYesBtn');

    if (!modal || !backdrop || !panel) return;

    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmText').innerText = text;

    // Setup confirm button handler
    if (confirmYesBtn) {
        confirmYesBtn.onclick = () => {
            if (confirmCallback) confirmCallback();
        };
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
    }, 10);

    // Re-init lucide icons
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    const backdrop = document.getElementById('confirmBackdrop');
    const panel = document.getElementById('confirmPanel');

    if (!modal || !backdrop || !panel) return;

    backdrop.classList.add('opacity-0');
    panel.classList.remove('scale-100', 'opacity-100');
    panel.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        confirmCallback = null;
    }, 300);
}

// Alias for backwards compatibility
function hideConfirmModal() {
    closeConfirmModal();
}

// Button Loading State Helper
function setButtonLoading(btn, spinner, text, isLoading, originalText) {
    if (!btn) return;
    if (isLoading) {
        btn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (text) text.textContent = 'Memproses...';
    } else {
        btn.disabled = false;
        if (spinner) spinner.classList.add('hidden');
        if (text) text.textContent = originalText;
    }
}

// Generic Modal Open/Close
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = modal.querySelector('[id$="Backdrop"]') || modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('[id$="Panel"]') || modal.querySelector('.modal-panel');

    if (!modal) return;

    modal.classList.remove('hidden');
    setTimeout(() => {
        if (backdrop) backdrop.classList.remove('opacity-0');
        if (panel) {
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        }
    }, 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = modal.querySelector('[id$="Backdrop"]') || modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('[id$="Panel"]') || modal.querySelector('.modal-panel');

    if (!modal) return;

    if (backdrop) backdrop.classList.add('opacity-0');
    if (panel) {
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Initialize App
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Initialize submenus
    initSubmenus();

    // Initialize charts if on dashboard
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
});
