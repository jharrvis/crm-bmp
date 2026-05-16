import React, { useState, useCallback, useRef, useEffect } from 'react';
import {
    ReactFlow,
    Controls,
    MiniMap,
    Background,
    useNodesState,
    useEdgesState,
    addEdge,
    Panel,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';

import NetworkDeviceNode from './nodes/NetworkDeviceNode';
import DevicePalette from './components/DevicePalette';
import Toolbar from './components/Toolbar';
import HistoryPanel from './components/HistoryPanel';
import PropertiesPanel from './components/PropertiesPanel';
import SettingsPanel from './components/SettingsPanel';

// Register custom node types
const nodeTypes = {
    networkDevice: NetworkDeviceNode,
};

// Default settings
const defaultSettings = {
    edgeType: 'bezier',
    edgeAnimated: true,
    edgeColor: '#6366f1',
    edgeWidth: 2,
    snapToGrid: true,
    gridSize: 15,
    showMinimap: true,
    showBackground: true,
    backgroundGap: 15,
};

const TopologyEditor = ({ subscriptionId, apiBaseUrl, canEdit }) => {
    const reactFlowWrapper = useRef(null);
    const containerRef = useRef(null);
    const [nodes, setNodes, onNodesChange] = useNodesState([]);
    const [edges, setEdges, onEdgesChange] = useEdgesState([]);
    const [reactFlowInstance, setReactFlowInstance] = useState(null);
    const [selectedNode, setSelectedNode] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [showHistory, setShowHistory] = useState(false);
    const [showSettings, setShowSettings] = useState(false);
    const [hasChanges, setHasChanges] = useState(false);
    const [version, setVersion] = useState(0);
    const [lastSaved, setLastSaved] = useState(null);

    // Enhanced features states
    const [isLocked, setIsLocked] = useState(false);
    const [isFullscreen, setIsFullscreen] = useState(false);
    const [settings, setSettings] = useState(defaultSettings);

    // CSRF token for Laravel
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Dynamic edge options based on settings
    const getEdgeOptions = useCallback(() => ({
        type: settings.edgeType,
        animated: settings.edgeAnimated,
        style: {
            stroke: settings.edgeColor,
            strokeWidth: settings.edgeWidth
        },
    }), [settings]);

    // Load topology on mount
    useEffect(() => {
        loadTopology();
        loadSettings();
    }, [subscriptionId]);

    // Fullscreen change listener
    useEffect(() => {
        const handleFullscreenChange = () => {
            setIsFullscreen(!!document.fullscreenElement);
        };
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        return () => document.removeEventListener('fullscreenchange', handleFullscreenChange);
    }, []);

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyDown = (e) => {
            // Ctrl+S to save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                if (hasChanges && canEdit) saveTopology();
            }
            // Ctrl+L to toggle lock
            if (e.ctrlKey && e.key === 'l') {
                e.preventDefault();
                setIsLocked(prev => !prev);
            }
            // F11 or Ctrl+Shift+F for fullscreen
            if (e.key === 'F11' || (e.ctrlKey && e.shiftKey && e.key === 'F')) {
                e.preventDefault();
                toggleFullscreen();
            }
            // Escape to exit fullscreen
            if (e.key === 'Escape' && isFullscreen) {
                exitFullscreen();
            }
        };
        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [hasChanges, canEdit, isFullscreen]);

    // Load settings from localStorage
    const loadSettings = () => {
        try {
            const saved = localStorage.getItem(`topology-settings-${subscriptionId}`);
            if (saved) {
                setSettings({ ...defaultSettings, ...JSON.parse(saved) });
            }
        } catch (e) {
            console.error('Failed to load settings:', e);
        }
    };

    // Save settings to localStorage
    const saveSettings = (newSettings) => {
        try {
            localStorage.setItem(`topology-settings-${subscriptionId}`, JSON.stringify(newSettings));
        } catch (e) {
            console.error('Failed to save settings:', e);
        }
    };

    const handleUpdateSettings = (newSettings) => {
        setSettings(newSettings);
        saveSettings(newSettings);

        // Update existing edges with new style
        setEdges(eds => eds.map(edge => ({
            ...edge,
            type: newSettings.edgeType,
            animated: newSettings.edgeAnimated,
            style: {
                stroke: newSettings.edgeColor,
                strokeWidth: newSettings.edgeWidth,
            }
        })));

        setHasChanges(true);
    };

    const toggleFullscreen = () => {
        if (!document.fullscreenElement) {
            containerRef.current?.requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    };

    const exitFullscreen = () => {
        if (document.fullscreenElement) {
            document.exitFullscreen?.();
        }
    };

    const loadTopology = async () => {
        setIsLoading(true);
        try {
            const response = await fetch(`${apiBaseUrl}/subscriptions/${subscriptionId}/topology`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await response.json();

            if (data.success && data.topology) {
                const { nodes: loadedNodes, edges: loadedEdges } = data.topology.topology_data;
                setNodes(loadedNodes || []);
                setEdges(loadedEdges || []);
                setVersion(data.topology.version);
                setLastSaved(data.topology.updated_at);
            }
        } catch (error) {
            console.error('Failed to load topology:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const saveTopology = async (summary = null) => {
        if (!canEdit) return;

        setIsSaving(true);
        try {
            const topologyData = {
                nodes: nodes,
                edges: edges,
                viewport: reactFlowInstance?.getViewport(),
            };

            const response = await fetch(`${apiBaseUrl}/subscriptions/${subscriptionId}/topology`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    topology_data: topologyData,
                    change_summary: summary,
                }),
            });

            const data = await response.json();

            if (data.success) {
                setVersion(data.topology.version);
                setLastSaved(new Date().toISOString());
                setHasChanges(false);
                showToast('Topologi berhasil disimpan!', 'success');
            } else {
                showToast(data.message || 'Gagal menyimpan', 'error');
            }
        } catch (error) {
            console.error('Failed to save topology:', error);
            showToast('Gagal menyimpan topologi', 'error');
        } finally {
            setIsSaving(false);
        }
    };

    const showToast = (message, type = 'info') => {
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    };

    const onConnect = useCallback(
        (params) => {
            if (isLocked) return;
            const edgeOptions = getEdgeOptions();
            setEdges((eds) => addEdge({ ...params, ...edgeOptions }, eds));
            setHasChanges(true);
        },
        [setEdges, isLocked, getEdgeOptions]
    );

    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }, []);

    const onDrop = useCallback(
        (event) => {
            event.preventDefault();

            if (!canEdit || isLocked) return;

            const type = event.dataTransfer.getData('application/reactflow');
            const deviceData = JSON.parse(event.dataTransfer.getData('application/deviceData') || '{}');

            if (!type) return;

            const position = reactFlowInstance.screenToFlowPosition({
                x: event.clientX,
                y: event.clientY,
            });

            const newNode = {
                id: `${deviceData.deviceType}-${Date.now()}`,
                type: 'networkDevice',
                position,
                data: {
                    label: deviceData.label || 'New Device',
                    deviceType: deviceData.deviceType || 'custom',
                    icon: deviceData.icon || 'cpu',
                    color: deviceData.color || '#64748b',
                    ...deviceData,
                },
            };

            setNodes((nds) => nds.concat(newNode));
            setHasChanges(true);
        },
        [reactFlowInstance, canEdit, setNodes, isLocked]
    );

    const onNodeClick = useCallback((event, node) => {
        setSelectedNode(node);
    }, []);

    const onPaneClick = useCallback(() => {
        setSelectedNode(null);
    }, []);

    const updateNodeData = useCallback(
        (nodeId, newData) => {
            setNodes((nds) =>
                nds.map((node) => {
                    if (node.id === nodeId) {
                        return {
                            ...node,
                            data: { ...node.data, ...newData },
                        };
                    }
                    return node;
                })
            );
            setHasChanges(true);
            setSelectedNode(prev => prev && prev.id === nodeId ? { ...prev, data: { ...prev.data, ...newData } } : prev);
        },
        [setNodes]
    );

    const deleteNode = useCallback(
        (nodeId) => {
            setNodes((nds) => nds.filter((node) => node.id !== nodeId));
            setEdges((eds) => eds.filter((edge) => edge.source !== nodeId && edge.target !== nodeId));
            setSelectedNode(null);
            setHasChanges(true);
        },
        [setNodes, setEdges]
    );

    const applyTemplate = useCallback(
        (templateData) => {
            if (!canEdit || isLocked) return;

            const { nodes: templateNodes, edges: templateEdges } = templateData;
            const edgeOptions = getEdgeOptions();

            const offsetX = nodes.length > 0 ? Math.max(...nodes.map((n) => n.position.x)) + 200 : 0;
            const offsetY = 0;

            const newNodes = templateNodes.map((node, index) => ({
                ...node,
                id: `${node.id}-${Date.now()}-${index}`,
                position: {
                    x: node.position.x + offsetX,
                    y: node.position.y + offsetY,
                },
            }));

            const idMap = {};
            templateNodes.forEach((node, index) => {
                idMap[node.id] = newNodes[index].id;
            });

            const newEdges = templateEdges.map((edge, index) => ({
                ...edge,
                ...edgeOptions,
                id: `${edge.id}-${Date.now()}-${index}`,
                source: idMap[edge.source],
                target: idMap[edge.target],
            }));

            setNodes((nds) => [...nds, ...newNodes]);
            setEdges((eds) => [...eds, ...newEdges]);
            setHasChanges(true);
        },
        [nodes, canEdit, setNodes, setEdges, isLocked, getEdgeOptions]
    );

    const clearCanvas = useCallback(async () => {
        if (!canEdit || isLocked) return;
        const confirmed = window.confirmAction
            ? await window.confirmAction('Kosongkan Kanvas?', 'Hapus semua elemen? Tindakan ini tidak dapat dibatalkan.')
            : window.confirm('Hapus semua elemen? Tindakan ini tidak dapat dibatalkan.');
        if (confirmed) {
            setNodes([]);
            setEdges([]);
            setHasChanges(true);
        }
    }, [canEdit, setNodes, setEdges, isLocked]);

    const restoreVersion = useCallback(
        async (historyId) => {
            try {
                const response = await fetch(
                    `${apiBaseUrl}/subscriptions/${subscriptionId}/topology/restore/${historyId}`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    }
                );

                const data = await response.json();

                if (data.success) {
                    const { nodes: restoredNodes, edges: restoredEdges } = data.topology.topology_data;
                    setNodes(restoredNodes || []);
                    setEdges(restoredEdges || []);
                    setVersion(data.topology.version);
                    setShowHistory(false);
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Gagal restore', 'error');
                }
            } catch (error) {
                console.error('Failed to restore:', error);
                showToast('Gagal restore versi', 'error');
            }
        },
        [subscriptionId, apiBaseUrl, csrfToken, setNodes, setEdges]
    );

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-96 bg-slate-50 dark:bg-slate-900 rounded-2xl">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p className="text-slate-500">Memuat topologi...</p>
                </div>
            </div>
        );
    }

    return (
        <div
            ref={containerRef}
            className={`flex bg-slate-50 dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 ${isFullscreen ? 'fixed inset-0 z-50 rounded-none' : 'h-full'}`}
            style={{ minHeight: isFullscreen ? '100vh' : '600px' }}
        >
            {/* Left Sidebar - Device Palette */}
            {canEdit && !isLocked && <DevicePalette />}

            {/* Main Canvas */}
            <div className="flex-1 relative" ref={reactFlowWrapper}>
                <ReactFlow
                    nodes={nodes}
                    edges={edges}
                    onNodesChange={(changes) => {
                        if (isLocked) return;
                        onNodesChange(changes);
                        if (changes.some((c) => c.type !== 'select')) setHasChanges(true);
                    }}
                    onEdgesChange={(changes) => {
                        if (isLocked) return;
                        onEdgesChange(changes);
                        if (changes.some((c) => c.type !== 'select')) setHasChanges(true);
                    }}
                    onConnect={onConnect}
                    onInit={setReactFlowInstance}
                    onDrop={onDrop}
                    onDragOver={onDragOver}
                    onNodeClick={onNodeClick}
                    onPaneClick={onPaneClick}
                    nodeTypes={nodeTypes}
                    defaultEdgeOptions={getEdgeOptions()}
                    fitView
                    snapToGrid={settings.snapToGrid}
                    snapGrid={[settings.gridSize, settings.gridSize]}
                    deleteKeyCode={canEdit && !isLocked ? ['Backspace', 'Delete'] : null}
                    nodesDraggable={!isLocked}
                    nodesConnectable={!isLocked}
                    elementsSelectable={!isLocked}
                    panOnDrag={!isLocked}
                    zoomOnScroll={!isLocked}
                    zoomOnPinch={!isLocked}
                    zoomOnDoubleClick={!isLocked}
                    className="bg-white dark:bg-slate-800"
                >
                    <Controls showInteractive={false} />
                    {settings.showMinimap && (
                        <MiniMap
                            nodeColor={(node) => node.data?.color || '#6366f1'}
                            maskColor="rgba(0, 0, 0, 0.1)"
                            className="!bg-slate-100 dark:!bg-slate-700"
                        />
                    )}
                    {settings.showBackground && (
                        <Background gap={settings.gridSize} size={1} color="#e2e8f0" />
                    )}

                    {/* Top Toolbar */}
                    <Panel position="top-center">
                        <Toolbar
                            canEdit={canEdit}
                            hasChanges={hasChanges}
                            isSaving={isSaving}
                            version={version}
                            lastSaved={lastSaved}
                            onSave={saveTopology}
                            onShowHistory={() => setShowHistory(!showHistory)}
                            onShowSettings={() => setShowSettings(true)}
                            onClear={clearCanvas}
                            onApplyTemplate={applyTemplate}
                            subscriptionId={subscriptionId}
                            apiBaseUrl={apiBaseUrl}
                            csrfToken={csrfToken}
                            isLocked={isLocked}
                            onToggleLock={() => setIsLocked(!isLocked)}
                            isFullscreen={isFullscreen}
                            onToggleFullscreen={toggleFullscreen}
                            showHistory={showHistory}
                        />
                    </Panel>

                    {/* Lock Banner */}
                    {isLocked && (
                        <Panel position="top-left" className="mt-16">
                            <div className="flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-3 py-2 rounded-lg text-sm font-medium">
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Tampilan terkunci
                            </div>
                        </Panel>
                    )}

                    {/* Empty State */}
                    {nodes.length === 0 && (
                        <Panel position="center">
                            <div className="text-center p-8 bg-white/80 dark:bg-slate-800/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-700">
                                <div className="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Belum ada topologi
                                </h3>
                                <p className="text-slate-500 text-sm max-w-xs">
                                    {canEdit
                                        ? 'Drag device dari panel kiri ke canvas untuk memulai membuat topologi jaringan.'
                                        : 'Belum ada topologi jaringan untuk layanan ini.'}
                                </p>
                            </div>
                        </Panel>
                    )}

                    {/* Keyboard Shortcuts Hint */}
                    <Panel position="bottom-left" className="mb-8 ml-12">
                        <div className="text-xs text-slate-400 space-y-1">
                            <div>Ctrl+S: Simpan | Ctrl+L: Lock | F11: Fullscreen</div>
                        </div>
                    </Panel>
                </ReactFlow>
            </div>

            {/* Right Sidebar - Properties Panel */}
            {selectedNode && canEdit && (
                <PropertiesPanel
                    node={selectedNode}
                    onUpdate={updateNodeData}
                    onDelete={deleteNode}
                    onClose={() => setSelectedNode(null)}
                />
            )}

            {/* History Sidebar */}
            {showHistory && (
                <HistoryPanel
                    subscriptionId={subscriptionId}
                    apiBaseUrl={apiBaseUrl}
                    csrfToken={csrfToken}
                    canEdit={canEdit}
                    onRestore={restoreVersion}
                    onClose={() => setShowHistory(false)}
                />
            )}

            {/* Settings Modal */}
            {showSettings && (
                <SettingsPanel
                    settings={settings}
                    onUpdateSettings={handleUpdateSettings}
                    onClose={() => setShowSettings(false)}
                />
            )}
        </div>
    );
};

export default TopologyEditor;
