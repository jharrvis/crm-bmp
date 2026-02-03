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

    const handleRestore = (historyId) => {
        if (confirm('Restore ke versi ini? Perubahan yang belum disimpan akan hilang.')) {
            onRestore(historyId);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            {/* Backdrop */}
            <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" onClick={onClose} />

            {/* Modal */}
            <div className="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] overflow-hidden">
                {/* Header */}
                <div className="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 className="font-bold text-lg text-slate-800 dark:text-white">Version History</h3>
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
                <div className="p-4 overflow-y-auto max-h-[60vh]">
                    {/* Current Version */}
                    <div className="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                v{currentVersion}
                            </div>
                            <div>
                                <div className="font-bold text-sm text-blue-700 dark:text-blue-400">Versi Saat Ini</div>
                                <div className="text-xs text-blue-600 dark:text-blue-300">Belum disimpan ke history</div>
                            </div>
                        </div>
                    </div>

                    {/* History List */}
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
                            <p className="text-slate-500 text-sm">Belum ada history</p>
                            <p className="text-slate-400 text-xs mt-1">History akan tersimpan setiap kali ada perubahan</p>
                        </div>
                    ) : (
                        <div className="space-y-2">
                            {history.map((item) => (
                                <div
                                    key={item.id}
                                    className="p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                                >
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-start gap-3">
                                            <div className="w-8 h-8 bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full flex items-center justify-center text-sm font-mono">
                                                v{item.version}
                                            </div>
                                            <div>
                                                <div className="font-medium text-sm text-slate-700 dark:text-slate-200">
                                                    {item.change_summary || 'Perubahan topologi'}
                                                </div>
                                                <div className="text-xs text-slate-400 mt-0.5">
                                                    {item.changed_by} • {item.created_at}
                                                </div>
                                            </div>
                                        </div>

                                        {canEdit && (
                                            <button
                                                onClick={() => handleRestore(item.id)}
                                                className="text-xs px-2 py-1 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                            >
                                                Restore
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
                        onClick={onClose}
                        className="w-full py-2 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    );
};

export default HistoryPanel;
