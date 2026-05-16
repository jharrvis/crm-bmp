import React, { useState, useEffect } from 'react';

const HistoryPanel = ({ subscriptionId, apiBaseUrl, csrfToken, canEdit, onRestore, onClose }) => {
    const [history, setHistory] = useState([]);
    const [currentVersion, setCurrentVersion] = useState(0);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        loadHistory();
    }, []);

    const loadHistory = async () => {
        setIsLoading(true);
        try {
            const response = await fetch(`${apiBaseUrl}/subscriptions/${subscriptionId}/topology/history`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await response.json();

            if (data.success) {
                setHistory(data.history);
                setCurrentVersion(data.current_version);
            }
        } catch (error) {
            console.error('Failed to load history:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleRestore = async (historyId) => {
        const confirmed = window.confirmAction
            ? await window.confirmAction('Restore Versi?', 'Restore ke versi ini? Perubahan yang belum disimpan akan hilang.')
            : window.confirm('Restore ke versi ini? Perubahan yang belum disimpan akan hilang.');
        if (confirmed) {
            onRestore(historyId);
        }
    };

    return (
        <div className="w-80 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 flex flex-col shadow-xl">
            {/* Header */}
            <div className="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div className="flex items-center gap-2">
                    <svg className="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 className="font-bold text-sm text-slate-800 dark:text-white">Riwayat Versi</h3>
                </div>
                <button
                    onClick={onClose}
                    className="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors"
                >
                    <svg className="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {/* Current Version Banner */}
            <div className="p-4 border-b border-slate-200 dark:border-slate-700">
                <div className="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div className="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                        v{currentVersion}
                    </div>
                    <div>
                        <div className="font-bold text-sm text-blue-700 dark:text-blue-400">Versi Saat Ini</div>
                        <div className="text-xs text-blue-600 dark:text-blue-300">Terakhir disimpan</div>
                    </div>
                </div>
            </div>

            {/* Body - History List */}
            <div className="flex-1 overflow-y-auto p-4">
                {isLoading ? (
                    <div className="text-center py-8">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-3"></div>
                        <p className="text-slate-500 text-sm">Memuat history...</p>
                    </div>
                ) : history.length === 0 ? (
                    <div className="text-center py-8">
                        <div className="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg className="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p className="text-slate-500 text-sm font-medium">Belum ada history</p>
                        <p className="text-slate-400 text-xs mt-1">History akan tersimpan setelah perubahan</p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {history.map((item, index) => (
                            <div
                                key={item.id}
                                className="relative group"
                            >
                                {/* Timeline line */}
                                {index < history.length - 1 && (
                                    <div className="absolute left-5 top-12 w-0.5 h-6 bg-slate-200 dark:bg-slate-700" />
                                )}

                                <div className="p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <div className="flex items-start gap-3">
                                        <div className="w-10 h-10 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full flex items-center justify-center text-sm font-mono shrink-0">
                                            v{item.version}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="font-medium text-sm text-slate-700 dark:text-slate-200 truncate">
                                                {item.change_summary || 'Perubahan topologi'}
                                            </div>
                                            <div className="flex items-center gap-2 text-xs text-slate-400 mt-1">
                                                <span className="flex items-center gap-1">
                                                    <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    {item.changed_by}
                                                </span>
                                                <span>•</span>
                                                <span>{item.created_at}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {canEdit && (
                                        <button
                                            onClick={() => handleRestore(item.id)}
                                            className="mt-3 w-full text-xs px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors font-medium flex items-center justify-center gap-1"
                                        >
                                            <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Restore Versi Ini
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Footer */}
            <div className="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <button
                    onClick={loadHistory}
                    disabled={isLoading}
                    className="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors"
                >
                    <svg className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh History
                </button>
            </div>
        </div>
    );
};

export default HistoryPanel;
