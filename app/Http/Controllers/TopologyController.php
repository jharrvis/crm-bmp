<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionTopology;
use App\Models\TopologyTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopologyController extends Controller
{
    /**
     * Check if user has permission to edit topology.
     * Only users in NOC division or admins can edit.
     */
    private function canEdit(): bool
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            return true;
        }

        // Check if user is in NOC division
        $division = $user->division;
        if ($division && strtoupper($division->name) === 'NOC') {
            return true;
        }

        return false;
    }

    /**
     * Get topology for a subscription.
     */
    public function show(Subscription $subscription)
    {
        $topology = $subscription->topology;

        return response()->json([
            'success' => true,
            'topology' => $topology ? [
                'id' => $topology->id,
                'topology_data' => $topology->topology_data,
                'version' => $topology->version,
                'updated_at' => $topology->updated_at,
                'updated_by' => $topology->updater?->name,
            ] : null,
            'can_edit' => $this->canEdit(),
        ]);
    }

    /**
     * Save/update topology for a subscription.
     */
    public function store(Request $request, Subscription $subscription)
    {
        if (!$this->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit topologi. Hanya divisi NOC yang diizinkan.',
            ], 403);
        }

        $request->validate([
            'topology_data' => 'required|array',
            'topology_data.nodes' => 'present|array',
            'topology_data.edges' => 'present|array',
            'change_summary' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $topology = $subscription->topology;

        if ($topology) {
            // Save current version to history before updating
            $topology->saveToHistory($user->id, $request->change_summary ?? 'Update topologi');

            // Update existing topology
            $topology->update([
                'topology_data' => $request->topology_data,
                'version' => $topology->version + 1,
                'updated_by' => $user->id,
            ]);
        } else {
            // Create new topology
            $topology = $subscription->topology()->create([
                'topology_data' => $request->topology_data,
                'version' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Topologi berhasil disimpan',
            'topology' => [
                'id' => $topology->id,
                'version' => $topology->version,
                'updated_at' => $topology->updated_at,
            ],
        ]);
    }

    /**
     * Get version history for a topology.
     */
    public function history(Subscription $subscription)
    {
        $topology = $subscription->topology;

        if (!$topology) {
            return response()->json([
                'success' => true,
                'history' => [],
            ]);
        }

        $history = $topology->histories()
            ->with('changedBy:id,name')
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'version' => $h->version,
                    'changed_by' => $h->changedBy?->name ?? 'Unknown',
                    'change_summary' => $h->change_summary,
                    'created_at' => $h->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'history' => $history,
            'current_version' => $topology->version,
        ]);
    }

    /**
     * Restore a specific version from history.
     */
    public function restore(Request $request, Subscription $subscription, int $historyId)
    {
        if (!$this->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit topologi.',
            ], 403);
        }

        $topology = $subscription->topology;
        if (!$topology) {
            return response()->json([
                'success' => false,
                'message' => 'Topologi tidak ditemukan.',
            ], 404);
        }

        $historyItem = $topology->histories()->find($historyId);
        if (!$historyItem) {
            return response()->json([
                'success' => false,
                'message' => 'Versi history tidak ditemukan.',
            ], 404);
        }

        $user = Auth::user();

        // Save current to history before restoring
        $topology->saveToHistory($user->id, 'Sebelum restore ke versi ' . $historyItem->version);

        // Restore the old version
        $topology->update([
            'topology_data' => $historyItem->topology_data,
            'version' => $topology->version + 1,
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Topologi berhasil di-restore ke versi ' . $historyItem->version,
            'topology' => [
                'id' => $topology->id,
                'topology_data' => $topology->topology_data,
                'version' => $topology->version,
            ],
        ]);
    }

    /**
     * Get all templates.
     */
    public function templates()
    {
        $templates = TopologyTemplate::with('creator:id,name')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'description' => $t->description,
                    'is_system' => $t->is_system,
                    'created_by' => $t->creator?->name ?? 'System',
                    'topology_data' => $t->topology_data,
                ];
            });

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Save current topology as a template.
     */
    public function saveAsTemplate(Request $request, Subscription $subscription)
    {
        if (!$this->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $topology = $subscription->topology;
        if (!$topology) {
            return response()->json([
                'success' => false,
                'message' => 'Topologi tidak ditemukan.',
            ], 404);
        }

        $template = TopologyTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'topology_data' => $topology->topology_data,
            'is_system' => false,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil disimpan',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
            ],
        ]);
    }

    /**
     * Delete a user-created template.
     */
    public function deleteTemplate(TopologyTemplate $template)
    {
        if (!$this->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        if ($template->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Template sistem tidak dapat dihapus.',
            ], 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil dihapus',
        ]);
    }
}
