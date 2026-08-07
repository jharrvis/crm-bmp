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
const SIDEBAR_STATE_KEY = 'crm.sidebar.collapsed';

function applySidebarState(isCollapsed) {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    if (!sidebar || !mainContent || window.innerWidth < 1024) {
        return;
    }

    sidebar.classList.toggle('sidebar-collapsed', isCollapsed);
    mainContent.classList.toggle('main-content-collapsed', isCollapsed);
    mainContent.classList.toggle('main-content-expanded', !isCollapsed);
}

function persistSidebarState(isCollapsed) {
    localStorage.setItem(SIDEBAR_STATE_KEY, isCollapsed ? 'true' : 'false');
}

function loadSidebarState() {
    return localStorage.getItem(SIDEBAR_STATE_KEY) === 'true';
}

function toggleSidebar() {
    if (window.innerWidth >= 1024) {
        // Desktop Collapse
        const sidebar = document.getElementById('sidebar');
        const isCollapsed = !sidebar.classList.contains('sidebar-collapsed');
        applySidebarState(isCollapsed);
        persistSidebarState(isCollapsed);
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

function moveModalToBody(modal) {
    if (!modal || modal.dataset.portalized === 'true') {
        return modal;
    }

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    modal.dataset.portalized = 'true';
    return modal;
}

function normalizeModalRoots() {
    document.querySelectorAll('[id$="Modal"], [id$="Drawer"], #confirmModal').forEach((modal) => {
        if (modal.classList.contains('fixed') && (modal.classList.contains('inset-0') || modal.classList.contains('inset-x-0') || modal.classList.contains('inset-y-0'))) {
            moveModalToBody(modal);
        }
    });
}

// Confirm Modal System
let confirmCallback = null;
let confirmPromiseResolve = null;
let confirmMode = 'callback';

function ensureConfirmModal() {
    if (document.getElementById('confirmModal')) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div id="confirmModal" class="fixed inset-0 z-[70] hidden">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="confirmBackdrop"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300" id="confirmPanel">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2" id="confirmTitle">Konfirmasi</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6" id="confirmText">Apakah Anda yakin ingin melanjutkan?</p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" onclick="hideConfirmModal()" class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                Batal
                            </button>
                            <button id="confirmYesBtn" type="button" class="px-5 py-2.5 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200 dark:shadow-none transition-all flex items-center gap-2 disabled:opacity-50">
                                <svg id="confirmSpinner" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="confirmBtnText">Ya, Lanjutkan!</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(wrapper.firstElementChild);
}

function showConfirmModal(title, text, callback) {
    ensureConfirmModal();
    if (typeof callback === 'function') {
        confirmMode = 'callback';
        confirmCallback = callback;
        confirmPromiseResolve = null;
    } else if (confirmMode !== 'promise') {
        confirmMode = 'callback';
        confirmCallback = null;
    }
    const modal = document.getElementById('confirmModal');
    const backdrop = document.getElementById('confirmBackdrop');
    const panel = document.getElementById('confirmPanel');
    const confirmYesBtn = document.getElementById('confirmYesBtn');

    if (!modal || !backdrop || !panel) return;
    moveModalToBody(modal);

    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmText').innerText = text;

    // Setup confirm button handler
    if (confirmYesBtn) {
        confirmYesBtn.onclick = () => {
            if (confirmMode === 'promise') {
                const resolver = confirmPromiseResolve;
                confirmPromiseResolve = null;
                closeConfirmModal();
                if (resolver) resolver(true);
                return;
            }

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

function confirmAction(title, text) {
    ensureConfirmModal();
    confirmMode = 'promise';
    confirmCallback = null;

    return new Promise((resolve) => {
        confirmPromiseResolve = resolve;
        showConfirmModal(title, text);
    });
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
        confirmMode = 'callback';
    }, 300);
}

// Alias for backwards compatibility
function hideConfirmModal() {
    if (confirmMode === 'promise' && confirmPromiseResolve) {
        const resolver = confirmPromiseResolve;
        confirmPromiseResolve = null;
        closeConfirmModal();
        resolver(false);
        return;
    }

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
function openAppModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    moveModalToBody(modal);
    const backdrop = modal.querySelector('[id$="Backdrop"]') || modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('[id$="Panel"]') || modal.querySelector('.modal-panel');

    modal.classList.remove('hidden');
    setTimeout(() => {
        if (backdrop) backdrop.classList.remove('opacity-0');
        if (panel) {
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        }
    }, 10);
}

function closeAppModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const backdrop = modal.querySelector('[id$="Backdrop"]') || modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('[id$="Panel"]') || modal.querySelector('.modal-panel');

    if (backdrop) backdrop.classList.add('opacity-0');
    if (panel) {
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Legacy aliases for existing module dialogs. Quick View uses the app-specific
// functions above so module pages cannot override its modal behavior.
function openModal(modalId) {
    openAppModal(modalId);
}

function closeModal(modalId) {
    closeAppModal(modalId);
}

window.openAppModal = openAppModal;
window.closeAppModal = closeAppModal;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function escapeJsString(value) {
    return String(value ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/\r/g, '\\r')
        .replace(/\n/g, '\\n');
}

function renderCopyValue(value, label) {
    if (!value) {
        return '<span class="text-sm text-slate-400 dark:text-slate-500">-</span>';
    }

    const escapedValue = escapeHtml(value);
    const escapedLabel = escapeHtml(label);
    const jsValue = escapeJsString(value);
    const jsLabel = escapeJsString(label);

    return `
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-100 break-all">${escapedValue}</span>
            <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-600 dark:text-slate-300 dark:hover:border-blue-500/40 dark:hover:bg-blue-900/20 dark:hover:text-blue-300"
                onclick="copySearchQuickViewValue('${jsValue}', '${jsLabel}')"
            >
                <i data-lucide="copy" class="h-3.5 w-3.5"></i>
            </button>
        </div>
    `;
}

function renderQuickViewField(label, value, copyable = false) {
    return `
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">${escapeHtml(label)}</div>
            <div class="mt-2">
                ${copyable ? renderCopyValue(value, label) : `<div class="text-sm font-semibold text-slate-700 dark:text-slate-100">${escapeHtml(value || '-')}</div>`}
            </div>
        </div>
    `;
}

function renderQuickViewList(items) {
    if (!items || items.length === 0) {
        return '<div class="text-sm text-slate-400 dark:text-slate-500">Tidak ada data.</div>';
    }

    return `
        <div class="space-y-3">
            ${items.map((item) => `
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="text-sm font-semibold text-slate-800 dark:text-white">${escapeHtml(item.title || '-')}</div>
                    ${item.subtitle ? `<div class="mt-1 text-sm text-slate-500 dark:text-slate-400">${escapeHtml(item.subtitle)}</div>` : ''}
                </div>
            `).join('')}
        </div>
    `;
}

function buildSearchQuickViewContent(item, data) {
    const branchName = data.branch?.name || '-';
    const vendorName = data.vendor?.name || '-';
    const contacts = Array.isArray(data.contacts) ? data.contacts : [];
    const primaryContact = contacts.find((contact) => contact.is_primary) || contacts[0] || null;
    const roles = Array.isArray(data.roles) ? data.roles.map((role) => role.name).filter(Boolean) : [];

    const sections = {
        client: {
            subtitle: [data.client_code, data.city, branchName].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Status', data.status)}
                    ${renderQuickViewField('Cabang', branchName)}
                    ${renderQuickViewField('Tipe', data.type)}
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    ${renderQuickViewField('Client Code', data.client_code, true)}
                    ${renderQuickViewField('Nomor Kontak Utama', primaryContact?.phone || primaryContact?.whatsapp, true)}
                    ${renderQuickViewField('Nama Kontak Utama', primaryContact?.name)}
                    ${renderQuickViewField('WhatsApp', primaryContact?.whatsapp, true)}
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Alamat</div>
                    <div class="mt-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">${escapeHtml(data.address || '-')}</div>
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Kontak Lain</div>
                    <div class="mt-2">${renderQuickViewList(contacts.map((contact) => ({
                        title: contact.name,
                        subtitle: [contact.phone, contact.whatsapp, contact.email, contact.position].filter(Boolean).join(' | '),
                    })))}</div>
                </div>
            `,
        },
        router: {
            subtitle: [data.host, data.branch?.name].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Host / IP', data.host, true)}
                    ${renderQuickViewField('Port', data.port, true)}
                    ${renderQuickViewField('Status', data.is_active ? 'Aktif' : 'Nonaktif')}
                    ${renderQuickViewField('User', data.user, true)}
                    ${renderQuickViewField('Cabang', branchName)}
                    ${renderQuickViewField('Deskripsi', data.description)}
                </div>
            `,
        },
        server: {
            subtitle: [data.host, data.location, data.type].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Host / IP', data.host, true)}
                    ${renderQuickViewField('Port', data.port, true)}
                    ${renderQuickViewField('Status', data.is_active ? 'Aktif' : 'Nonaktif')}
                    ${renderQuickViewField('Tipe', data.type)}
                    ${renderQuickViewField('Lokasi', data.location)}
                    ${renderQuickViewField('Username', data.username, true)}
                    ${renderQuickViewField('Max Akun', data.max_accounts)}
                    ${renderQuickViewField('Deskripsi', data.description)}
                </div>
            `,
        },
        vendor: {
            subtitle: [data.cid, data.address].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-2">
                    ${renderQuickViewField('CID', data.cid, true)}
                    ${renderQuickViewField('Alamat', data.address)}
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Kontak Vendor</div>
                    <div class="mt-2">${renderQuickViewList((data.contacts || []).map((contact) => ({
                        title: contact.name,
                        subtitle: [contact.phone, contact.email, contact.position].filter(Boolean).join(' | '),
                    })))}</div>
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Catatan</div>
                    <div class="mt-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">${escapeHtml(data.notes || '-')}</div>
                </div>
            `,
        },
        metro_ethernet: {
            subtitle: [vendorName, data.ip_address, data.bandwidth ? `${data.bandwidth} Mbps` : null].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Vendor', vendorName)}
                    ${renderQuickViewField('CID', data.cid, true)}
                    ${renderQuickViewField('Bandwidth', data.bandwidth ? `${data.bandwidth} Mbps` : null)}
                    ${renderQuickViewField('IP Address', data.ip_address, true)}
                    ${renderQuickViewField('Nama Display', data.display_name || data.name)}
                </div>
            `,
        },
        ip_transit: {
            subtitle: [vendorName, data.ip_address, data.bandwidth ? `${data.bandwidth} Mbps` : null].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Vendor', vendorName)}
                    ${renderQuickViewField('CID', data.cid, true)}
                    ${renderQuickViewField('Bandwidth', data.bandwidth ? `${data.bandwidth} Mbps` : null)}
                    ${renderQuickViewField('IP Address', data.ip_address, true)}
                    ${renderQuickViewField('IP Gateway', data.ip_gateway, true)}
                    ${renderQuickViewField('AS Number', data.as_number, true)}
                </div>
            `,
        },
        branch: {
            subtitle: [data.code, data.phone].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-2">
                    ${renderQuickViewField('Kode Cabang', data.code, true)}
                    ${renderQuickViewField('Telepon', data.phone, true)}
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Alamat</div>
                    <div class="mt-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">${escapeHtml(data.address || '-')}</div>
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Karyawan Terkait</div>
                    <div class="mt-2">${renderQuickViewList((data.users || []).map((user) => ({
                        title: user.name,
                    })))}</div>
                </div>
            `,
        },
        division: {
            subtitle: data.description || 'Divisi internal',
            body: `
                <div class="grid gap-3 md:grid-cols-2">
                    ${renderQuickViewField('Nama Divisi', data.name)}
                    ${renderQuickViewField('Deskripsi', data.description)}
                </div>
            `,
        },
        employee: {
            subtitle: [data.email, data.division?.name, data.branch?.name].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Email', data.email, true)}
                    ${renderQuickViewField('Telepon', data.phone, true)}
                    ${renderQuickViewField('Role', roles.join(', '))}
                    ${renderQuickViewField('Cabang', data.branch?.name)}
                    ${renderQuickViewField('Divisi', data.division?.name)}
                </div>
            `,
        },
        service: {
            subtitle: [data.code, data.type].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Kode', data.code, true)}
                    ${renderQuickViewField('Tipe', data.type)}
                    ${renderQuickViewField('Status', data.is_active ? 'Aktif' : 'Nonaktif')}
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Deskripsi</div>
                    <div class="mt-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">${escapeHtml(data.description || '-')}</div>
                </div>
            `,
        },
        package: {
            subtitle: [data.service?.name, data.price ? formatSearchQuickViewCurrency(data.price) : null].filter(Boolean).join(' | '),
            body: `
                <div class="grid gap-3 md:grid-cols-3">
                    ${renderQuickViewField('Layanan', data.service?.name)}
                    ${renderQuickViewField('Harga', data.price ? formatSearchQuickViewCurrency(data.price) : null)}
                    ${renderQuickViewField('Status', data.is_active ? 'Aktif' : 'Nonaktif')}
                    ${renderQuickViewField('Bandwidth Down', data.bandwidth_down)}
                    ${renderQuickViewField('Bandwidth Up', data.bandwidth_up)}
                    ${renderQuickViewField('Kuota', data.quota)}
                </div>
                <div class="mt-5">
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Deskripsi</div>
                    <div class="mt-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">${escapeHtml(data.description || '-')}</div>
                </div>
            `,
        },
    };

    return sections[item.detail_type] || {
        subtitle: item.subtitle || '',
        body: `<div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">Detail cepat belum tersedia untuk modul ini.</div>`,
    };
}

function formatSearchQuickViewCurrency(value) {
    const numericValue = Number(value || 0);
    return `Rp ${numericValue.toLocaleString('id-ID')}`;
}

function copySearchQuickViewValue(value, label) {
    if (!value || !navigator.clipboard) {
        return;
    }

    navigator.clipboard.writeText(String(value))
        .then(() => showToast(`${label} berhasil disalin.`))
        .catch(() => showToast(`Gagal menyalin ${label}.`, 'error'));
}

async function openGlobalSearchQuickView(item) {
    if (!item || !item.detail_url) {
        return;
    }

    const body = document.getElementById('searchQuickViewBody');
    const title = document.getElementById('searchQuickViewTitle');
    const subtitle = document.getElementById('searchQuickViewSubtitle');
    const label = document.getElementById('searchQuickViewLabel');
    const pageLink = document.getElementById('searchQuickViewPageLink');

    if (!body || !title || !subtitle || !label || !pageLink) {
        return;
    }

    title.textContent = item.title || 'Detail';
    subtitle.textContent = item.subtitle || '';
    label.textContent = item.action === 'quick_view' ? 'Quick View' : 'Detail';
    pageLink.href = item.page_url || item.url || '#';
    pageLink.classList.toggle('hidden', !pageLink.href || pageLink.href === '#');
    body.innerHTML = `
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">
            <svg class="h-4 w-4 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memuat detail...
        </div>
    `;

    openAppModal('searchQuickViewModal');

    try {
        const response = await fetch(item.detail_url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to fetch quick view');
        }

        const data = await response.json();
        const content = buildSearchQuickViewContent(item, data);

        subtitle.textContent = content.subtitle || item.subtitle || '';
        body.innerHTML = content.body;

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Quick view error:', error);
        body.innerHTML = `
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-600 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                Detail cepat tidak dapat dimuat. Coba buka halaman modul lengkap.
            </div>
        `;
    }
}

window.copySearchQuickViewValue = copySearchQuickViewValue;
window.openGlobalSearchQuickView = openGlobalSearchQuickView;

document.addEventListener('click', function (event) {
    const backdrop = document.getElementById('confirmBackdrop');
    const quickViewBackdrop = document.getElementById('searchQuickViewBackdrop');

    if (backdrop && event.target === backdrop) {
        hideConfirmModal();
    }

    const quickViewTrigger = event.target.closest('[data-search-quick-view]');
    if (quickViewTrigger) {
        const payload = quickViewTrigger.getAttribute('data-search-quick-view');
        if (!payload) {
            return;
        }

        try {
            openGlobalSearchQuickView(JSON.parse(payload));
        } catch (error) {
            console.error('Invalid quick view payload:', error);
        }
    }

    if (quickViewBackdrop && event.target === quickViewBackdrop) {
        closeAppModal('searchQuickViewModal');
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !document.getElementById('confirmModal')?.classList.contains('hidden')) {
        hideConfirmModal();
    }

    if (event.key === 'Escape' && !document.getElementById('searchQuickViewModal')?.classList.contains('hidden')) {
        closeAppModal('searchQuickViewModal');
    }
});

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirmText) {
        return;
    }

    if (form.dataset.confirmSubmitted === 'true') {
        form.dataset.confirmSubmitted = 'false';
        return;
    }

    event.preventDefault();

    confirmAction(
        form.dataset.confirmTitle || 'Konfirmasi',
        form.dataset.confirmText
    ).then((confirmed) => {
        if (!confirmed) {
            return;
        }

        form.dataset.confirmSubmitted = 'true';
        form.requestSubmit();
    });
});

window.confirmAction = confirmAction;

// Initialize App
document.addEventListener('DOMContentLoaded', () => {
    applySidebarState(loadSidebarState());
    normalizeModalRoots();

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

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        applySidebarState(loadSidebarState());
    }
});
