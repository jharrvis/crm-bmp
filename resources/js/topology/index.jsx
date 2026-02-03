import React from 'react';
import { createRoot } from 'react-dom/client';
import TopologyEditor from './TopologyEditor';

// Find the topology editor root element
const container = document.getElementById('topology-editor-root');

if (container) {
    // Get configuration from data attributes
    const subscriptionId = container.dataset.subscriptionId;
    const apiBaseUrl = container.dataset.apiBaseUrl;
    const canEdit = container.dataset.canEdit === 'true';

    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <TopologyEditor
                subscriptionId={subscriptionId}
                apiBaseUrl={apiBaseUrl}
                canEdit={canEdit}
            />
        </React.StrictMode>
    );
}
