import React, { useState, useEffect } from 'react';

const PropertiesPanel = ({ node, onUpdate, onDelete, onClose }) => {
    const [formData, setFormData] = useState({
        label: '',
        ip: '',
        model: '',
        serialNumber: '',
        notes: '',
    });

    useEffect(() => {
        if (node?.data) {
            setFormData({
                label: node.data.label || '',
                ip: node.data.ip || '',
                model: node.data.model || '',
                serialNumber: node.data.serialNumber || '',
                notes: node.data.notes || '',
            });
        }
    }, [node]);

    const handleChange = (field, value) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
    };

    const handleSave = () => {
        onUpdate(node.id, formData);
    };

    const handleDelete = () => {
        if (confirm('Hapus device ini?')) {
            onDelete(node.id);
        }
    };

    const deviceTypeLabels = {
        backbone: 'ISP Backbone',
        router: 'Router',
        olt: 'OLT',
        odp: 'ODP/Splitter',
        ont: 'ONT',
        switch: 'Switch',
        customer: 'Pelanggan',
        custom: 'Custom Device',
    };

    return (
        <div className="w-64 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 flex flex-col">
            {/* Header */}
            <div className="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
                <h3 className="font-bold text-sm text-slate-700 dark:text-slate-200">Properti Device</h3>
                <button
                    onClick={onClose}
                    className="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                >
                    <svg className="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {/* Body */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4">
                {/* Device Type Badge */}
                <div
                    className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-white text-xs font-bold"
                    style={{ backgroundColor: node.data?.color || '#6366f1' }}
                >
                    {deviceTypeLabels[node.data?.deviceType] || 'Device'}
                </div>

                {/* Label */}
                <div>
                    <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                        Nama / Label
                    </label>
                    <input
                        type="text"
                        value={formData.label}
                        onChange={(e) => handleChange('label', e.target.value)}
                        className="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"
                    />
                </div>

                {/* IP Address */}
                <div>
                    <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                        IP Address
                    </label>
                    <input
                        type="text"
                        value={formData.ip}
                        onChange={(e) => handleChange('ip', e.target.value)}
                        placeholder="192.168.1.1"
                        className="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none font-mono"
                    />
                </div>

                {/* Model */}
                <div>
                    <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                        Model / Tipe
                    </label>
                    <input
                        type="text"
                        value={formData.model}
                        onChange={(e) => handleChange('model', e.target.value)}
                        placeholder="Mikrotik CCR1036"
                        className="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"
                    />
                </div>

                {/* Serial Number */}
                <div>
                    <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                        Serial Number
                    </label>
                    <input
                        type="text"
                        value={formData.serialNumber}
                        onChange={(e) => handleChange('serialNumber', e.target.value)}
                        placeholder="HWTC12345678"
                        className="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none font-mono"
                    />
                </div>

                {/* Notes */}
                <div>
                    <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                        Catatan
                    </label>
                    <textarea
                        value={formData.notes}
                        onChange={(e) => handleChange('notes', e.target.value)}
                        rows={2}
                        className="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                    />
                </div>
            </div>

            {/* Footer */}
            <div className="p-4 border-t border-slate-200 dark:border-slate-700 space-y-2">
                <button
                    onClick={handleSave}
                    className="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-colors"
                >
                    Simpan Perubahan
                </button>
                <button
                    onClick={handleDelete}
                    className="w-full py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-bold rounded-xl transition-colors"
                >
                    Hapus Device
                </button>
            </div>
        </div>
    );
};

export default PropertiesPanel;
