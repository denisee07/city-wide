<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RendersStatusPatches;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\LostFound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LostFoundController extends Controller
{
    use RendersStatusPatches, ScopesAdminQueries;

    public function index(Request $request): View
    {
        $query = $this->scopedVisible(LostFound::query())->with('reporter:id,name');

        if (in_array($request->input('status'), array_keys(LostFound::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('item_name', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%')
                ->orWhere('location', 'like', '%'.$term.'%'));
        }

        $items = $query->latest()->get();

        $counts = [
            'all' => $this->scopedVisible(LostFound::query())->count(),
            'lost' => $this->scopedVisible(LostFound::query())->where('status', 'lost')->count(),
            'found' => $this->scopedVisible(LostFound::query())->where('status', 'found')->count(),
            'claimed' => $this->scopedVisible(LostFound::query())->where('status', 'claimed')->count(),
        ];

        return view('admin.lost-found.index', [
            'items' => $items,
            'counts' => $counts,
            'scopeLabel' => $this->scopeLabel(),
            'active' => in_array($request->input('status'), array_keys(LostFound::STATUSES), true)
                ? $request->input('status')
                : 'all',
        ]);
    }

    public function create(): View
    {
        return view('admin.lost-found.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        LostFound::create([
            ...$data,
            'reported_by' => $request->user()?->id,
            'barangay_id' => $this->defaultBarangayId(),
        ]);

        return redirect()
            ->route('admin.lost-found.index')
            ->with('success', '"'.$data['item_name'].'" was added to the lost and found board.');
    }

    public function edit(LostFound $lostFound): View
    {
        $this->guardInScope($lostFound);

        return view('admin.lost-found.edit', ['item' => $lostFound]);
    }

    public function update(Request $request, LostFound $lostFound): RedirectResponse
    {
        $this->guardInScope($lostFound);

        $lostFound->update($this->validated($request));

        return redirect()
            ->route('admin.lost-found.index')
            ->with('success', '"'.$lostFound->item_name.'" was updated.');
    }

    public function destroy(LostFound $lostFound): RedirectResponse
    {
        $this->guardInScope($lostFound);

        $name = $lostFound->item_name;
        $lostFound->delete();

        return redirect()
            ->route('admin.lost-found.index')
            ->with('success', '"'.$name.'" was removed from the board.');
    }

    /** Quick status switch from the board (lost / found / claimed). */
    /** Status -> badge class, shared by the board and the in-place ajax patch. */
    public const STATUS_BADGES = [
        'lost' => 'badge-rose',
        'found' => 'badge-green',
        'claimed' => 'badge-violet',
    ];

    public function status(Request $request, LostFound $lostFound): RedirectResponse|JsonResponse
    {
        $this->guardInScope($lostFound);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(LostFound::STATUSES))],
        ]);

        $lostFound->update(['status' => $data['status']]);

        $message = '"'.$lostFound->item_name.'" is now marked as '.LostFound::STATUSES[$data['status']].'.';

        return $this->savedResponse(
            $request,
            'admin.lost-found.index',
            $message,
            [
                /* Only the badge moves - the row keeps the select the official
                   just used, and the active status filter stays put. */
                [
                    'selector' => $this->statusCellSelector($lostFound->id),
                    'badge' => self::STATUS_BADGES[$lostFound->status] ?? 'badge-neutral',
                    'label' => $lostFound->statusLabel(),
                ],
            ],
            ['status' => $data['status']],
        );
    }

    /**
     * Same rule as announcements: a barangay board shows the city-wide items
     * but only the city office may change them, so anything outside this
     * official's own barangay is invisible to the route.
     */
    private function guardInScope(LostFound $lostFound): void
    {
        $id = $this->scopeBarangayId();

        if ($id !== null && $lostFound->barangay_id !== $id) {
            abort(404);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'item_name' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', Rule::in(array_keys(LostFound::CATEGORIES))],
            'description' => ['required', 'string', 'min:10', 'max:4000'],
            'status' => ['required', Rule::in(array_keys(LostFound::STATUSES))],
            'location' => ['required', 'string', 'max:190'],
            'date_occurred' => ['nullable', 'date'],
            'contact_info' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'string', 'max:400'],
        ]);
    }
}
