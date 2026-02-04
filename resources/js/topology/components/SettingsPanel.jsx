import React, { useState } from 'react';

const edgeTypes = [
    { id: 'smoothstep', label: 'Smooth Step', desc: 'Garis yang halus dengan sudut' },
    { id: 'bezier', label: 'Bezier', desc: 'Garis lengkung fleksibel' },
    { id: 'straight', label: 'Straight', desc: 'Garis lurus langsung' },
    { id: 'step', label: 'Step', desc: 'Garis dengan sudut 90°' },
];

const gridSizes = [
    { id: 10, label: 'Small (10px)' },
    { id: 15, label: 'Medium (15px)' },
    { id: 20, label: 'Large (20px)' },
    { id: 25, label: 'Extra Large (25px)' },
];

const edgeColors = [
    { id: '#6366f1', label: 'Indigo' },
    { id: '#3b82f6', label: 'Blue' },
    { id: '#10b981', label: 'Emerald' },
    { id: '#f97316', label: 'Orange' },
    { id: '#ef4444', label: 'Red' },
    { id: '#64748b', label: 'Slate' },
    { id: '#000000', label: 'Black' },
];

const SettingsPanel = ({ settings, onUpdateSettings, onClose }) => {
    const [localSettings, setLocalSettings] = useState(settings);

    const handleChange = (key, value) => {
        setLocalSettings(prev => ({ ...prev, [key]: value }));
    };

    const handleSave = () => {
        onUpdateSettings(localSettings);
        onClose();
    };

    const handleReset = () => {
        const defaultSettings = {
            edgeType: 'smoothstep',
            edgeAnimated: true,
            edgeColor: '#6366f1',
            edgeWidth: 2,
            snapToGrid: true,
            gridSize: 15,
            showMinimap: true,
            showBackground: true,
            backgroundGap: 15,
        };
        setLocalSettings(defaultSettings);
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            {/* Backdrop */}
            <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" onClick={onClose} />

            {/* Modal */}
            <div className="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden">
                {/* Header */}
                <div className="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <svg className="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 className="font-bold text-lg text-slate-800 dark:text-white">Pengaturan Editor</h3>
                            <p className="text-xs text-slate-500">Kustomisasi tampilan dan perilaku editor</p>
                        </div>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        <svg className="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {/* Body */}
                <div className="p-4 overflow-y-auto max-h-[60vh] space-y-6">
                    {/* Edge Settings Section */}
                    <div>
                        <h4 className="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                            <svg className="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Garis Koneksi
                        </h4>

                        {/* Edge Type */}
                        <div className="mb-4">
                            <label className="block text-xs font-medium text-slate-500 mb-2">Tipe Garis</label>
                            <div className="grid grid-cols-2 gap-2">
                                {edgeTypes.map((type) => (
                                    <button
                                        key={type.id}
                                        onClick={() => handleChange('edgeType', type.id)}
                                        className={`p-3 rounded-xl border-2 text-left transition-all ${localSettings.edgeType === type.id
                                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                                            }`}
                                    >
                                        <div className="font-medium text-sm text-slate-700 dark:text-slate-200">{type.label}</div>
                                        <div className="text-xs text-slate-400">{type.desc}</div>
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Edge Color */}
                        <div className="mb-4">
                            <label className="block text-xs font-medium text-slate-500 mb-2">Warna Garis</label>
                            <div className="flex gap-2 flex-wrap">
                                {edgeColors.map((color) => (
                                    <button
                                        key={color.id}
                                        onClick={() => handleChange('edgeColor', color.id)}
                                        className={`w-8 h-8 rounded-lg transition-transform hover:scale-110 ${localSettings.edgeColor === color.id ? 'ring-2 ring-offset-2 ring-blue-500' : ''
                                            }`}
                                        style={{ backgroundColor: color.id }}
                                        title={color.label}
                                    />
                                ))}
                            </div>
                        </div>

                        {/* Edge Width */}
                        <div className="mb-4">
                            <label className="block text-xs font-medium text-slate-500 mb-2">
                                Ketebalan Garis: {localSettings.edgeWidth}px
                            </label>
                            <input
                                type="range"
                                min="1"
                                max="5"
                                value={localSettings.edgeWidth}
                                onChange={(e) => handleChange('edgeWidth', parseInt(e.target.value))}
                                className="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                            />
                        </div>

                        {/* Edge Animation Toggle */}
                        <div className="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                            <div>
                                <div className="text-sm font-medium text-slate-700 dark:text-slate-200">Animasi Garis</div>
                                <div className="text-xs text-slate-400">Garis bergerak menunjukkan arah koneksi</div>
                            </div>
                            <button
                                onClick={() => handleChange('edgeAnimated', !localSettings.edgeAnimated)}
                                className={`relative w-12 h-6 rounded-full transition-colors ${localSettings.edgeAnimated ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-600'
                                    }`}
                            >
                                <div className={`absolute w-5 h-5 bg-white rounded-full top-0.5 transition-transform shadow ${localSettings.edgeAnimated ? 'translate-x-6' : 'translate-x-0.5'
                                    }`} />
                            </button>
                        </div>
                    </div>

                    {/* Grid Settings Section */}
                    <div>
                        <h4 className="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                            <svg className="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            Grid & Canvas
                        </h4>

                        {/* Snap to Grid Toggle */}
                        <div className="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl mb-3">
                            <div>
                                <div className="text-sm font-medium text-slate-700 dark:text-slate-200">Snap to Grid</div>
                                <div className="text-xs text-slate-400">Node otomatis menempel ke grid</div>
                            </div>
                            <button
                                onClick={() => handleChange('snapToGrid', !localSettings.snapToGrid)}
                                className={`relative w-12 h-6 rounded-full transition-colors ${localSettings.snapToGrid ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-600'
                                    }`}
                            >
                                <div className={`absolute w-5 h-5 bg-white rounded-full top-0.5 transition-transform shadow ${localSettings.snapToGrid ? 'translate-x-6' : 'translate-x-0.5'
                                    }`} />
                            </button>
                        </div>

                        {/* Grid Size */}
                        <div className="mb-4">
                            <label className="block text-xs font-medium text-slate-500 mb-2">Ukuran Grid</label>
                            <div className="grid grid-cols-4 gap-2">
                                {gridSizes.map((size) => (
                                    <button
                                        key={size.id}
                                        onClick={() => handleChange('gridSize', size.id)}
                                        className={`p-2 rounded-lg text-center text-sm font-medium transition-all ${localSettings.gridSize === size.id
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200'
                                            }`}
                                    >
                                        {size.id}px
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Display Settings Section */}
                    <div>
                        <h4 className="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                            <svg className="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Tampilan
                        </h4>

                        {/* Show Minimap */}
                        <div className="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl mb-3">
                            <div>
                                <div className="text-sm font-medium text-slate-700 dark:text-slate-200">Minimap</div>
                                <div className="text-xs text-slate-400">Tampilkan peta mini di pojok</div>
                            </div>
                            <button
                                onClick={() => handleChange('showMinimap', !localSettings.showMinimap)}
                                className={`relative w-12 h-6 rounded-full transition-colors ${localSettings.showMinimap ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-600'
                                    }`}
                            >
                                <div className={`absolute w-5 h-5 bg-white rounded-full top-0.5 transition-transform shadow ${localSettings.showMinimap ? 'translate-x-6' : 'translate-x-0.5'
                                    }`} />
                            </button>
                        </div>

                        {/* Show Background */}
                        <div className="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                            <div>
                                <div className="text-sm font-medium text-slate-700 dark:text-slate-200">Background Grid</div>
                                <div className="text-xs text-slate-400">Tampilkan grid di background</div>
                            </div>
                            <button
                                onClick={() => handleChange('showBackground', !localSettings.showBackground)}
                                className={`relative w-12 h-6 rounded-full transition-colors ${localSettings.showBackground ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-600'
                                    }`}
                            >
                                <div className={`absolute w-5 h-5 bg-white rounded-full top-0.5 transition-transform shadow ${localSettings.showBackground ? 'translate-x-6' : 'translate-x-0.5'
                                    }`} />
                            </button>
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="p-4 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                    <button
                        onClick={handleReset}
                        className="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors"
                    >
                        Reset Default
                    </button>
                    <div className="flex-1" />
                    <button
                        onClick={onClose}
                        className="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        onClick={handleSave}
                        className="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-colors"
                    >
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    );
};

export default SettingsPanel;
