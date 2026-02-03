import React, { useState, useEffect } from 'react';

const Toolbar = ({
    canEdit,
    hasChanges,
    isSaving,
    version,
    lastSaved,
    onSave,
    onShowHistory,
    onClear,
    onApplyTemplate,
    subscriptionId,
    apiBaseUrl,
    csrfToken,
}) => {
    const [showTemplates, setShowTemplates] = useState(false);
    const [templates, setTemplates] = useState([]);
    const [loadingTemplates, setLoadingTemplates] = useState(false);
    const [showSaveAsTemplate, setShowSaveAsTemplate] = useState(false);
    const [templateName, setTemplateName] = useState('');
    const [templateDesc, setTemplateDesc] = useState('');

    const loadTemplates = async () => {
        setLoadingTemplates(true);
        try {
            const response = await fetch(`${apiBaseUrl}/topology-templates`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await response.json();
            if (data.success) {
                setTemplates(data.templates);
            }
        } catch (error) {
            console.error('Failed to load templates:', error);
        } finally {
            setLoadingTemplates(false);
        }
    };

    const handleTemplateClick = () => {
        setShowTemplates(!showTemplates);
        if (!showTemplates && templates.length === 0) {
            loadTemplates();
        }
    };

    const applyTemplate = (template) => {
        onApplyTemplate(template.topology_data);
        setShowTemplates(false);
    };

    const saveAsTemplate = async () => {
        if (!templateName.trim()) return;

        try {
            const response = await fetch(
                `${apiBaseUrl}/subscriptions/${subscriptionId}/topology/save-template`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        name: templateName,
                        description: templateDesc,
                    }),
                }
            );

            const data = await response.json();
            if (data.success) {
                setShowSaveAsTemplate(false);
                setTemplateName('');
                setTemplateDesc('');
                loadTemplates();
                if (window.showToast) {
                    window.showToast('Template berhasil disimpan!', 'success');
                }
            }
        } catch (error) {
            console.error('Failed to save template:', error);
        }
    };

    return (
        <div className="flex items-center gap-2 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-2">
            {/* Version Info */}
            <div className="px-3 py-1.5 text-xs text-slate-500">
                <span className="font-mono">v{version || 0}</span>
                {lastSaved && (
                    <span className="ml-2 text-slate-400">
                        • {new Date(lastSaved).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                    </span>
                )}
            </div>

            <div className="w-px h-6 bg-slate-200 dark:bg-slate-700" />

            {/* Templates Button */}
            <div className="relative">
                <button
                    onClick={handleTemplateClick}
                    className="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                >
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                    Template
                </button>

                {/* Templates Dropdown */}
                {showTemplates && (
                    <div className="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50">
                        <div className="p-3 border-b border-slate-200 dark:border-slate-700">
                            <h4 className="font-bold text-sm text-slate-700 dark:text-slate-200">Pilih Template</h4>
                        </div>
                        <div className="max-h-64 overflow-y-auto p-2">
                            {loadingTemplates ? (
                                <div className="text-center py-4 text-slate-400 text-sm">Memuat...</div>
                            ) : templates.length === 0 ? (
                                <div className="text-center py-4 text-slate-400 text-sm">Belum ada template</div>
                            ) : (
                                templates.map((template) => (
                                    <button
                                        key={template.id}
                                        onClick={() => applyTemplate(template)}
                                        className="w-full text-left p-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                    >
                                        <div className="font-medium text-sm text-slate-700 dark:text-slate-200">
                                            {template.name}
                                            {template.is_system && (
                                                <span className="ml-2 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">
                                                    System
                                                </span>
                                            )}
                                        </div>
                                        {template.description && (
                                            <div className="text-xs text-slate-400 mt-0.5">{template.description}</div>
                                        )}
                                    </button>
                                ))
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* History Button */}
            <button
                onClick={onShowHistory}
                className="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
            >
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                History
            </button>

            {canEdit && (
                <>
                    <div className="w-px h-6 bg-slate-200 dark:bg-slate-700" />

                    {/* Save as Template */}
                    <div className="relative">
                        <button
                            onClick={() => setShowSaveAsTemplate(!showSaveAsTemplate)}
                            className="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Simpan Template
                        </button>

                        {showSaveAsTemplate && (
                            <div className="absolute top-full right-0 mt-2 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50 p-4">
                                <h4 className="font-bold text-sm text-slate-700 dark:text-slate-200 mb-3">
                                    Simpan sebagai Template
                                </h4>
                                <input
                                    type="text"
                                    placeholder="Nama template"
                                    value={templateName}
                                    onChange={(e) => setTemplateName(e.target.value)}
                                    className="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white mb-2"
                                />
                                <textarea
                                    placeholder="Deskripsi (opsional)"
                                    value={templateDesc}
                                    onChange={(e) => setTemplateDesc(e.target.value)}
                                    rows={2}
                                    className="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white mb-3"
                                />
                                <div className="flex justify-end gap-2">
                                    <button
                                        onClick={() => setShowSaveAsTemplate(false)}
                                        className="px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        onClick={saveAsTemplate}
                                        className="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                    >
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Clear Button */}
                    <button
                        onClick={onClear}
                        className="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    >
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>

                    <div className="w-px h-6 bg-slate-200 dark:bg-slate-700" />

                    {/* Save Button */}
                    <button
                        onClick={() => onSave()}
                        disabled={isSaving || !hasChanges}
                        className={`
                            flex items-center gap-2 px-4 py-1.5 text-sm font-bold rounded-lg transition-all
                            ${hasChanges
                                ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none'
                                : 'bg-slate-100 text-slate-400 dark:bg-slate-700 cursor-not-allowed'
                            }
                        `}
                    >
                        {isSaving ? (
                            <>
                                <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                Menyimpan...
                            </>
                        ) : (
                            <>
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan
                            </>
                        )}
                    </button>
                </>
            )}
        </div>
    );
};

export default Toolbar;
