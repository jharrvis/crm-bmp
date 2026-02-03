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

// Register custom node types
const nodeTypes = {
    networkDevice: NetworkDeviceNode,
};

// Edge styles
const defaultEdgeOptions = {
    type: 'smoothstep',
    animated: true,
    style: { stroke: '#6366f1', strokeWidth: 2 },
};

const TopologyEditor = ({ subscriptionId, apiBaseUrl, canEdit }) => {
    const reactFlowWrapper = useRef(null);
    const [nodes, setNodes, onNodesChange] = useNodesState([]);
    const [edges, setEdges, onEdgesChange] = useEdgesState([]);
    const [reactFlowInstance, setReactFlowInstance] = useState(null);
    const [selectedNode, setSelectedNode] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [showHistory, setShowHistory] = useState(false);
    const [hasChanges, setHasChanges] = useState(false);
    const [version, setVersion] = useState(0);
    const [lastSaved, setLastSaved] = useState(null);

    // CSRF token for Laravel
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Load topology on mount
    useEffect(() => {
        loadTopology();
    }, [subscriptionId]);

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
        // Use the global toast function if available
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    };

    const onConnect = useCallback(
        (params) => {
            setEdges((eds) => addEdge({ ...params, ...defaultEdgeOptions }, eds));
            setHasChanges(true);
        },
        [setEdges]
    );

    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }, []);

    const onDrop = useCallback(
        (event) => {
            event.preventDefault();

            if (!canEdit) return;

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
        [reactFlowInstance, canEdit, setNodes]
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
            if (!canEdit) return;

            const { nodes: templateNodes, edges: templateEdges } = templateData;

            // Offset nodes to not overlap existing ones
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

            // Update edge references
            const idMap = {};
            templateNodes.forEach((node, index) => {
                idMap[node.id] = newNodes[index].id;
            });

            const newEdges = templateEdges.map((edge, index) => ({
                ...edge,
                id: `${edge.id}-${Date.now()}-${index}`,
                source: idMap[edge.source],
                target: idMap[edge.target],
            }));

            setNodes((nds) => [...nds, ...newNodes]);
            setEdges((eds) => [...eds, ...newEdges]);
            setHasChanges(true);
        },
        [nodes, canEdit, setNodes, setEdges]
    );

    const clearCanvas = useCallback(() => {
        if (!canEdit) return;
        if (confirm('Hapus semua elemen? Tindakan ini tidak dapat dibatalkan.')) {
            setNodes([]);
            setEdges([]);
            setHasChanges(true);
        }
    }, [canEdit, setNodes, setEdges]);

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
        <div className="flex h-[600px] bg-slate-50 dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
            {/* Left Sidebar - Device Palette */}
            {canEdit && <DevicePalette />}

            {/* Main Canvas */}
            <div className="flex-1 relative" ref={reactFlowWrapper}>
                <ReactFlow
                    nodes={nodes}
                    edges={edges}
                    onNodesChange={(changes) => {
                        onNodesChange(changes);
                        if (changes.some((c) => c.type !== 'select')) setHasChanges(true);
                    }}
                    onEdgesChange={(changes) => {
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
                    defaultEdgeOptions={defaultEdgeOptions}
                    fitView
                    snapToGrid
                    snapGrid={[15, 15]}
                    deleteKeyCode={canEdit ? ['Backspace', 'Delete'] : null}
                    className="bg-white dark:bg-slate-800"
                >
                    <Controls showInteractive={false} />
                    <MiniMap
                        nodeColor={(node) => node.data?.color || '#6366f1'}
                        maskColor="rgba(0, 0, 0, 0.1)"
                        className="!bg-slate-100 dark:!bg-slate-700"
                    />
                    <Background gap={15} size={1} color="#e2e8f0" />

                    {/* Top Toolbar */}
                    <Panel position="top-center">
                        <Toolbar
                            canEdit={canEdit}
                            hasChanges={hasChanges}
                            isSaving={isSaving}
                            version={version}
                            lastSaved={lastSaved}
                            onSave={saveTopology}
                            onShowHistory={() => setShowHistory(true)}
                            onClear={clearCanvas}
                            onApplyTemplate={applyTemplate}
                            subscriptionId={subscriptionId}
                            apiBaseUrl={apiBaseUrl}
                            csrfToken={csrfToken}
                        />
                    </Panel>

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

            {/* History Modal */}
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
        </div>
    );
};

export default TopologyEditor;
