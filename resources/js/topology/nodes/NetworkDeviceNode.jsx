import React, { memo } from 'react';
import { Handle, Position } from '@xyflow/react';

// SVG icons for device types
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
    const Icon = deviceIcons[data.deviceType] || deviceIcons.custom;
    const bgColor = data.color || '#6366f1';

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
                    {Icon}
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
