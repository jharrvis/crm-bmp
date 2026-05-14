<?php

namespace App\Http\Controllers;

use App\Models\TicketCannedResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketCannedResponseController extends Controller
{
    public function index(): View
    {
        $responses = TicketCannedResponse::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('ticket_canned_responses.index', compact('responses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        TicketCannedResponse::create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('ticket-canned-responses.index')
            ->with('success', 'Template balasan berhasil ditambahkan.');
    }

    public function update(Request $request, TicketCannedResponse $ticketCannedResponse): RedirectResponse
    {
        $validated = $this->validatePayload($request, $ticketCannedResponse);

        $ticketCannedResponse->update([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title'], $ticketCannedResponse->id),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('ticket-canned-responses.index')
            ->with('success', 'Template balasan berhasil diperbarui.');
    }

    public function destroy(TicketCannedResponse $ticketCannedResponse): RedirectResponse
    {
        $ticketCannedResponse->delete();

        return redirect()
            ->route('ticket-canned-responses.index')
            ->with('success', 'Template balasan berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?TicketCannedResponse $response = null): array
    {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ticket_canned_responses', 'title')->ignore($response?->id),
            ],
            'category' => ['nullable', Rule::in(['connectivity', 'billing', 'technical', 'general'])],
            'message' => 'required|string',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            TicketCannedResponse::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
