<?php

namespace App\Http\Controllers;

use App\Models\ApprovedGridCell;
use App\Models\Category;
use App\Models\Event;
use App\Models\Facility;
use App\Models\FacilityCondition;
use App\Support\GridEffectData;
use Illuminate\Http\Request;

class GridController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();

<<<<<<< HEAD
        $facilities = Facility::with(['category', 'scores.category'])
=======
        $facilities = Facility::with([
            'category',
            'scores.category',
            'requiredNeighbour',
            'conditions.neighbourFacility',
        ])
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
            ->orderBy('sort_order')
            ->get();
        $conditions = FacilityCondition::with('neighbourFacility')->get();

        $groupedFacilities = $facilities->groupBy('category.name');
<<<<<<< HEAD
        $effectData = GridEffectData::from($categories, $facilities, $conditions);
=======
        $effectData = GridEffectData::from($categories, $facilities);
        $conditionData = $facilities->mapWithKeys(fn (Facility $facility) => [
            (string) $facility->id => [
                'name' => $facility->name,
                'conditions' => $facility->conditions->map(fn ($condition) => [
                    'type' => $condition->condition_type,
                    'neighbourFacilityId' => (string) $condition->neighbour_facility_id,
                    'neighbourFacilityName' => $condition->neighbourFacility->name,
                ])->values(),
            ],
        ]);
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073

        $eventEffectData = [
            'events' => Event::with('categories')
                ->orderBy('event_date')
                ->orderBy('start_time')
                ->get()
                ->map(function (Event $event) {
                    $eventDate = $event->event_date instanceof \DateTimeInterface
                        ? $event->event_date->format('Y-m-d')
                        : (string) $event->event_date;

                    return [
                        'id' => (string) $event->id,
                        'name' => $event->name,
                        'eventDate' => $eventDate,
                        'date' => $eventDate,
                        'startTime' => $this->formatTimeValue($event->start_time),
                        'endTime' => $this->formatTimeValue($event->end_time ?? null) ?? $this->formatTimeValue($event->start_time),
                        'recurrenceType' => $event->is_recurring ? ($event->recurrence_type ?? 'weekly') : 'none',
                        'status' => $event->status ?? 'planned',
                        'score' => $event->categories->sum(
                            fn ($category) => (int) ($category->pivot->score ?? 0)
                        ),
                        'impacts' => $event->categories->map(fn ($category) => [
                            'category_id' => $category->id,
                            'category_name' => $category->name,
                            'score' => (int) ($category->pivot->score ?? 0),
                        ])->values(),
                    ];
                })
                ->values(),
        ];

        $approvedGridCells = ApprovedGridCell::all()
            ->mapWithKeys(fn ($cell) => [
                (string) $cell->cell_index => [
                    'itemId' => (string) $cell->item_id,
                    'itemType' => $cell->item_type,
                    'name' => $cell->item_name,
                ],
            ]);

        $restrictions = $conditions
            ->where('condition_type', FacilityCondition::FORBIDDEN_NEIGHBOUR)
            ->map(fn (FacilityCondition $condition) => [
                'facility_id_1' => $condition->facility_id,
                'facility_id_2' => $condition->neighbour_facility_id,
            ])
            ->values();

        return view('grid.grid', compact(
            'categories',
            'facilities',
            'groupedFacilities',
            'effectData',
            'conditionData',
            'eventEffectData',
            'restrictions',
            'approvedGridCells',
        ));
    }

    public function approveCell(Request $request)
    {
        $userRole = auth()->user()?->role;

        if (! in_array($userRole, ['admin', 'policy_maker', 'municipal_policy_maker'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'cell_index' => ['required', 'integer', 'between:1,12'],
            'item_type' => ['required', 'string'],
            'item_id' => ['required', 'integer'],
            'item_name' => ['required', 'string', 'max:255'],
        ]);

        $approvedCell = ApprovedGridCell::updateOrCreate(
            ['cell_index' => $validated['cell_index']],
            [
                'item_type' => $validated['item_type'],
                'item_id' => $validated['item_id'],
                'item_name' => $validated['item_name'],
                'approved_by' => auth()->id(),
            ]
        );

        return response()->json([
            'success' => true,
            'cell' => $approvedCell,
        ]);
    }

    private function formatTimeValue($value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i');
        }

        return substr((string) $value, 0, 5);
    }
}
