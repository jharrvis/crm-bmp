import React, { useState, useEffect } from 'react';

// Available icons for network devices
const availableIcons = [
    { id: 'globe', label: 'Globe', svg: 'M12 2a10 10 0 1010 10A10 10 0 0012 2zm0 18a8 8 0 118-8 8 8 0 01-8 8zm0-14a6 6 0 00-6 6h2a4 4 0 014-4z' },
    { id: 'router', label: 'Router', svg: 'M4 11h16M4 11a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v4a2 2 0 01-2 2M4 11v8a2 2 0 002 2h12a2 2 0 002-2v-8' },
    { id: 'server', label: 'Server', svg: 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01' },
    { id: 'radio-tower', label: 'Radio Tower', svg: 'M4.9 19.1C1 15.2 1 8.8 4.9 4.9M7.8 16.2c-2.3-2.3-2.3-6.1 0-8.5M16.2 7.8c2.3 2.3 2.3 6.1 0 8.5m2.9-11.4C23 8.8 23 15.1 19.1 19m-7.1-7a2 2 0 11-2-2 2 2 0 012 2z' },
    { id: 'wifi', label: 'WiFi', svg: 'M5 12.55a11 11 0 0114.08 0M1.42 9a16 16 0 0121.16 0M8.53 16.11a6 6 0 016.95 0M12 20h.01' },
    { id: 'cpu', label: 'CPU', svg: 'M18 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2zM9 9h6v6H9z' },
    { id: 'box', label: 'Box', svg: 'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z' },
    { id: 'home', label: 'Home/Customer', svg: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { id: 'zap', label: 'Zap/Power', svg: 'M13 2L3 14h9l-1 8 10-12h-9l1-8z' },
    { id: 'cloud', label: 'Cloud', svg: 'M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z' },
    { id: 'database', label: 'Database', svg: 'M12 2C6.48 2 2 4.02 2 6.5v11C2 19.98 6.48 22 12 22s10-2.02 10-4.5v-11C22 4.02 17.52 2 12 2zm0 2c4.42 0 8 1.57 8 3.5S16.42 11 12 11 4 9.43 4 7.5 7.58 4 12 4z' },
    { id: 'satellite', label: 'Satellite', svg: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { id: 'link', label: 'Link', svg: 'M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71' },
    { id: 'monitor', label: 'Monitor', svg: 'M8 21h8m-4-4v4m-8-8h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v6a2 2 0 002 2z' },
    { id: 'smartphone', label: 'Smartphone', svg: 'M17 2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2zM12 18h.01' },
];

// Available colors
const availableColors = [
    { id: '#6366f1', label: 'Indigo' },
    { id: '#3b82f6', label: 'Blue' },
    { id: '#0ea5e9', label: 'Sky' },
    { id: '#06b6d4', label: 'Cyan' },
    { id: '#14b8a6', label: 'Teal' },
    { id: '#10b981', label: 'Emerald' },
    { id: '#22c55e', label: 'Green' },
    { id: '#84cc16', label: 'Lime' },
    { id: '#eab308', label: 'Yellow' },
    { id: '#f97316', label: 'Orange' },
    { id: '#ef4444', label: 'Red' },
    { id: '#ec4899', label: 'Pink' },
    { id: '#a855f7', label: 'Purple' },
    { id: '#64748b', label: 'Slate' },
];

const PropertiesPanel = ({ node, onUpdate, onDelete, onClose }) => {
    const [formData, setFormData] = useState({
        label: '',
        ip: '',
        model: '',
        serialNumber: '',
        notes: '',
        icon: 'cpu',
        color: '#6366f1',
    });
    const [showIconPicker, setShowIconPicker] = useState(false);
    const [showColorPicker, setShowColorPicker] = useState(false);

    useEffect(() => {
        if (node?.data) {
            setFormData({
                label: node.data.label || '',
                ip: node.data.ip || '',
                model: node.data.model || '',
                serialNumber: node.data.serialNumber || '',
                notes: node.data.notes || '',
                icon: node.data.icon || 'cpu',
                color: node.data.color || '#6366f1',
            });
        }
    }, [node]);

    const handleChange = (field, value) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
    };

    const handleIconSelect = (iconId) => {
        handleChange('icon', iconId);
        setShowIconPicker(false);
        // Auto-save icon change
        onUpdate(node.id, { ...formData, icon: iconId });
    };

    const handleColorSelect = (colorId) => {
        handleChange('color', colorId);
        setShowColorPicker(false);
        // Auto-save color change
        onUpdate(node.id, { ...formData, color: colorId });
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

    // Get current icon
    const currentIcon = availableIcons.find(i => i.id === formData.icon) || availableIcons[0];

    return (
        <div className="w-72 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 flex flex-col">
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
                    style={{ backgroundColor: formData.color }}
                >
                    {deviceTypeLabels[node.data?.deviceType] || 'Device'}
                </div>

                {/* Icon and Color Picker Row */}
                <div className="flex gap-3">
                    {/* Icon Picker */}
                    <div className="flex-1 relative">
                        <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                            Icon
                        </label>
                        <button
                            onClick={() => { setShowIconPicker(!showIconPicker); setShowColorPicker(false); }}
                            className="w-full flex items-center justify-center gap-2 px-3 py-3 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style={{ color: formData.color }}>
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={currentIcon.svg} />
                            </svg>
                        </button>

                        {/* Icon Picker Dropdown */}
                        {showIconPicker && (
                            <div className="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50 p-3">
                                <div className="text-xs font-bold text-slate-500 mb-2">Pilih Icon</div>
                                <div className="grid grid-cols-5 gap-2">
                                    {availableIcons.map((icon) => (
                                        <button
                                            key={icon.id}
                                            onClick={() => handleIconSelect(icon.id)}
                                            className={`p-2 rounded-lg transition-colors ${formData.icon === icon.id
                                                    ? 'bg-blue-100 dark:bg-blue-900/30 ring-2 ring-blue-500'
                                                    : 'hover:bg-slate-100 dark:hover:bg-slate-700'
                                                }`}
                                            title={icon.label}
                                        >
                                            <svg className="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={icon.svg} />
                                            </svg>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Color Picker */}
                    <div className="flex-1 relative">
                        <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                            Warna
                        </label>
                        <button
                            onClick={() => { setShowColorPicker(!showColorPicker); setShowIconPicker(false); }}
                            className="w-full flex items-center justify-center gap-2 px-3 py-3 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors"
                        >
                            <div
                                className="w-6 h-6 rounded-lg border-2 border-white shadow-sm"
                                style={{ backgroundColor: formData.color }}
                            />
                        </button>

                        {/* Color Picker Dropdown */}
                        {showColorPicker && (
                            <div className="absolute top-full right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50 p-3">
                                <div className="text-xs font-bold text-slate-500 mb-2">Pilih Warna</div>
                                <div className="grid grid-cols-5 gap-2">
                                    {availableColors.map((color) => (
                                        <button
                                            key={color.id}
                                            onClick={() => handleColorSelect(color.id)}
                                            className={`w-7 h-7 rounded-lg transition-transform hover:scale-110 ${formData.color === color.id ? 'ring-2 ring-offset-2 ring-blue-500' : ''
                                                }`}
                                            style={{ backgroundColor: color.id }}
                                            title={color.label}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
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
