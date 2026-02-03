import React from 'react';

// Device types available in palette
const deviceTypes = [
    {
        deviceType: 'backbone',
        label: 'ISP Backbone',
        icon: 'globe',
        color: '#8b5cf6',
        description: 'Internet upstream',
    },
    {
        deviceType: 'router',
        label: 'Router',
        icon: 'router',
        color: '#3b82f6',
        description: 'Gateway router',
    },
    {
        deviceType: 'olt',
        label: 'OLT',
        icon: 'server',
        color: '#f97316',
        description: 'Optical Line Terminal',
    },
    {
        deviceType: 'odp',
        label: 'ODP/Splitter',
        icon: 'git-branch',
        color: '#22c55e',
        description: 'Distribution Point',
    },
    {
        deviceType: 'ont',
        label: 'ONT',
        icon: 'box',
        color: '#14b8a6',
        description: 'Customer terminal',
    },
    {
        deviceType: 'switch',
        label: 'Switch',
        icon: 'network',
        color: '#6366f1',
        description: 'Network switch',
    },
    {
        deviceType: 'customer',
        label: 'Pelanggan',
        icon: 'home',
        color: '#64748b',
        description: 'Customer premises',
    },
    {
        deviceType: 'custom',
        label: 'Custom Device',
        icon: 'cpu',
        color: '#94a3b8',
        description: 'User-defined device',
    },
];

// Minimal icons for palette
const paletteIcons = {
    backbone: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="12" cy="12" r="10" />
            <path d="M2 12h20" />
        </svg>
    ),
    router: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="2" y="6" width="20" height="12" rx="2" />
            <circle cx="17" cy="12" r="1" fill="currentColor" />
        </svg>
    ),
    olt: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="4" y="2" width="16" height="20" rx="2" />
            <line x1="8" y1="6" x2="16" y2="6" />
            <line x1="8" y1="10" x2="16" y2="10" />
        </svg>
    ),
    odp: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="12" cy="6" r="3" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="13" x2="6" y2="19" />
            <line x1="12" y1="13" x2="18" y2="19" />
        </svg>
    ),
    ont: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <circle cx="8" cy="12" r="1" fill="currentColor" />
        </svg>
    ),
    switch: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="3" y="8" width="18" height="8" rx="2" />
            <circle cx="7" cy="12" r="1" fill="currentColor" />
            <circle cx="12" cy="12" r="1" fill="currentColor" />
        </svg>
    ),
    customer: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M3 12l9-9 9 9" />
            <path d="M5 10v10h14V10" />
        </svg>
    ),
    custom: (
        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M9 9h6M9 12h6" />
        </svg>
    ),
};

const DevicePalette = () => {
    const onDragStart = (event, device) => {
        event.dataTransfer.setData('application/reactflow', 'networkDevice');
        event.dataTransfer.setData('application/deviceData', JSON.stringify(device));
        event.dataTransfer.effectAllowed = 'move';
    };

    return (
        <div className="w-56 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 p-4 overflow-y-auto">
            <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                Perangkat
            </h3>
            <p className="text-xs text-slate-500 mb-4">
                Drag ke canvas untuk menambah
            </p>

            <div className="space-y-2">
                {deviceTypes.map((device) => (
                    <div
                        key={device.deviceType}
                        draggable
                        onDragStart={(e) => onDragStart(e, device)}
                        className="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 
                                   bg-slate-50 dark:bg-slate-700/50 cursor-grab hover:border-blue-300 
                                   hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all active:cursor-grabbing"
                    >
                        <div
                            className="w-9 h-9 rounded-lg flex items-center justify-center text-white shrink-0"
                            style={{ backgroundColor: device.color }}
                        >
                            {paletteIcons[device.deviceType]}
                        </div>
                        <div className="min-w-0">
                            <div className="font-semibold text-sm text-slate-700 dark:text-slate-200 truncate">
                                {device.label}
                            </div>
                            <div className="text-xs text-slate-400 truncate">
                                {device.description}
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Tips */}
            <div className="mt-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                <h4 className="text-xs font-bold text-blue-700 dark:text-blue-400 mb-1">💡 Tips</h4>
                <ul className="text-xs text-blue-600 dark:text-blue-300 space-y-1">
                    <li>• Klik device untuk edit properti</li>
                    <li>• Drag dari titik untuk koneksi</li>
                    <li>• Delete/Backspace untuk hapus</li>
                </ul>
            </div>
        </div>
    );
};

export default DevicePalette;
