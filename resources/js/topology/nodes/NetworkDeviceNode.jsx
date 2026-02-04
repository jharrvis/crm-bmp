import React, { memo } from 'react';
import { Handle, Position } from '@xyflow/react';

// Dynamic icon renderer based on icon ID
const IconRenderer = ({ iconId, className = "w-6 h-6" }) => {
    // Icon definitions mapping
    const iconPaths = {
        // Network icons
        globe: "M12 2a10 10 0 1010 10A10 10 0 0012 2zm0 18a8 8 0 118-8 8 8 0 01-8 8zm0-14a6 6 0 00-6 6h2a4 4 0 014-4z",
        router: "M4 11h16M4 11a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v4a2 2 0 01-2 2M4 11v8a2 2 0 002 2h12a2 2 0 002-2v-8",
        server: "M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01",
        'radio-tower': "M4.9 19.1C1 15.2 1 8.8 4.9 4.9M7.8 16.2c-2.3-2.3-2.3-6.1 0-8.5M16.2 7.8c2.3 2.3 2.3 6.1 0 8.5m2.9-11.4C23 8.8 23 15.1 19.1 19m-7.1-7a2 2 0 11-2-2 2 2 0 012 2z",
        wifi: "M5 12.55a11 11 0 0114.08 0M1.42 9a16 16 0 0121.16 0M8.53 16.11a6 6 0 016.95 0M12 20h.01",
        cpu: "M18 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2zM9 9h6v6H9z",
        box: "M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z",
        home: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
        zap: "M13 2L3 14h9l-1 8 10-12h-9l1-8z",
        cloud: "M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z",
        database: "M12 2C6.48 2 2 4.02 2 6.5v11C2 19.98 6.48 22 12 22s10-2.02 10-4.5v-11C22 4.02 17.52 2 12 2zm0 2c4.42 0 8 1.57 8 3.5S16.42 11 12 11 4 9.43 4 7.5 7.58 4 12 4z",
        satellite: "M13 10V3L4 14h7v7l9-11h-7z",
        link: "M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71",
        monitor: "M8 21h8m-4-4v4m-8-8h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v6a2 2 0 002 2z",
        smartphone: "M17 2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2zM12 18h.01",
        // Device type specific icons (fallbacks)
        backbone: "M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z",
        olt: "M4 2h16a2 2 0 012 2v16a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2zM8 6h8M8 10h8M8 14h8",
        odp: "M12 6a4 4 0 100-8 4 4 0 000 8zM12 10v4M12 14l-6 6M12 14l6 6M12 14v8",
        ont: "M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zM8 12h.01M12 8v8M16 8v8",
        switch: "M3 8h18a2 2 0 012 2v4a2 2 0 01-2 2H3a2 2 0 01-2-2v-4a2 2 0 012-2z",
        customer: "M3 12l9-9 9 9M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10M9 21v-6h6v6",
        custom: "M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zM9 9h6M9 12h6M9 15h4",
    };

    const path = iconPaths[iconId] || iconPaths.custom;

    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
            <path d={path} />
        </svg>
    );
};

// Legacy SVG icons for device types (used as fallback when no custom icon)
const deviceIcons = {
    backbone: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <circle cx="12" cy="12" r="10" />
            <path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" />
        </svg>
    ),
    router: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <rect x="2" y="6" width="20" height="12" rx="2" />
            <line x1="6" y1="10" x2="6" y2="14" />
            <line x1="10" y1="10" x2="10" y2="14" />
            <line x1="14" y1="10" x2="14" y2="14" />
            <circle cx="18" cy="12" r="1.5" fill="currentColor" />
        </svg>
    ),
    olt: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <rect x="4" y="2" width="16" height="20" rx="2" />
            <line x1="8" y1="6" x2="16" y2="6" />
            <line x1="8" y1="10" x2="16" y2="10" />
            <line x1="8" y1="14" x2="16" y2="14" />
            <circle cx="12" cy="18" r="1.5" fill="currentColor" />
        </svg>
    ),
    odp: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <circle cx="12" cy="6" r="4" />
            <line x1="12" y1="10" x2="12" y2="14" />
            <line x1="12" y1="14" x2="6" y2="20" />
            <line x1="12" y1="14" x2="18" y2="20" />
            <line x1="12" y1="14" x2="12" y2="22" />
        </svg>
    ),
    ont: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <circle cx="8" cy="12" r="1.5" fill="currentColor" />
            <line x1="12" y1="8" x2="12" y2="16" />
            <line x1="16" y1="8" x2="16" y2="16" />
        </svg>
    ),
    customer: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <path d="M3 12l9-9 9 9" />
            <path d="M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10" />
            <path d="M9 21v-6h6v6" />
        </svg>
    ),
    switch: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <rect x="3" y="8" width="18" height="8" rx="2" />
            <line x1="7" y1="12" x2="7" y2="12" strokeWidth="3" strokeLinecap="round" />
            <line x1="11" y1="12" x2="11" y2="12" strokeWidth="3" strokeLinecap="round" />
            <line x1="15" y1="12" x2="15" y2="12" strokeWidth="3" strokeLinecap="round" />
        </svg>
    ),
    custom: (
        <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M9 9h6M9 12h6M9 15h4" />
        </svg>
    ),
};

const NetworkDeviceNode = memo(({ data, selected }) => {
    const bgColor = data.color || '#6366f1';

    // Determine which icon to use: custom icon from data.icon or fallback to device type icon
    const customIcon = data.icon;
    const hasCustomIcon = customIcon && customIcon !== data.deviceType;

    // Use custom IconRenderer if we have a custom icon, otherwise use legacy device icons
    const renderIcon = () => {
        if (hasCustomIcon) {
            return <IconRenderer iconId={customIcon} className="w-6 h-6" />;
        }
        return deviceIcons[data.deviceType] || deviceIcons.custom;
    };

    return (
        <div
            className={`
                relative px-4 py-3 rounded-xl shadow-lg border-2 transition-all
                ${selected ? 'ring-2 ring-blue-500 ring-offset-2' : ''}
                bg-white dark:bg-slate-800
            `}
            style={{ borderColor: bgColor }}
        >
            {/* Top Handle for connections */}
            <Handle
                type="target"
                position={Position.Top}
                className="!w-3 !h-3 !bg-slate-400 !border-2 !border-white"
            />

            {/* Node Content */}
            <div className="flex items-center gap-3">
                {/* Icon */}
                <div
                    className="w-10 h-10 rounded-lg flex items-center justify-center text-white"
                    style={{ backgroundColor: bgColor }}
                >
                    {renderIcon()}
                </div>

                {/* Label & Details */}
                <div className="min-w-0">
                    <div className="font-bold text-slate-800 dark:text-white text-sm truncate max-w-[120px]">
                        {data.label}
                    </div>
                    {data.ip && (
                        <div className="text-xs text-slate-500 font-mono truncate">
                            {data.ip}
                        </div>
                    )}
                    {data.model && !data.ip && (
                        <div className="text-xs text-slate-400 truncate">
                            {data.model}
                        </div>
                    )}
                </div>
            </div>

            {/* Status indicator */}
            {data.status && (
                <div
                    className={`
                        absolute -top-1 -right-1 w-3 h-3 rounded-full border-2 border-white
                        ${data.status === 'online' ? 'bg-green-500' : 'bg-red-500'}
                    `}
                />
            )}

            {/* Bottom Handle for connections */}
            <Handle
                type="source"
                position={Position.Bottom}
                className="!w-3 !h-3 !bg-slate-400 !border-2 !border-white"
            />

            {/* Left Handle */}
            <Handle
                type="target"
                position={Position.Left}
                id="left"
                className="!w-3 !h-3 !bg-slate-400 !border-2 !border-white"
            />

            {/* Right Handle */}
            <Handle
                type="source"
                position={Position.Right}
                id="right"
                className="!w-3 !h-3 !bg-slate-400 !border-2 !border-white"
            />
        </div>
    );
});

NetworkDeviceNode.displayName = 'NetworkDeviceNode';

export default NetworkDeviceNode;
