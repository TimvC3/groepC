<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Facility;
use App\Support\GridEffectData;

class GridController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();

        $facilities = Facility::with(['category', 'scores.category', 'conditions.neighbourFacility'])
            ->orderBy('sort_order')
            ->get();

        $groupedFacilities = $facilities->groupBy('category.name');
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
        $upcomingEvents = Event::with('categories')
            ->get()
            ->map(fn (Event $event) => [
                'event' => $event,
                'occurrence' => [
                    'starts_at' => $event->startsAt(),
                    'ends_at' => $event->endsAt(),
                ],
            ])
            ->sortBy(fn (array $item) => $item['occurrence']['starts_at']->timestamp)
            ->values();

        $eventEffectData = [
            'events' => $upcomingEvents->map(fn (array $item) => [
                'id' => $item['event']->id,
                'name' => $item['event']->name,
                'eventDate' => $item['event']->event_date?->format('Y-m-d'),
                'date' => $item['occurrence']['starts_at']->format('d-m-Y'),
                'startTime' => $item['occurrence']['starts_at']->format('H:i'),
                'endTime' => $item['occurrence']['ends_at']->format('H:i'),
                'recurrenceType' => $item['event']->recurrence_type->value,
                'impacts' => $item['event']->impactScores(),
                'score' => $item['event']->impactScore(),
            ])->values(),
        ];

        $upcomingEvents = $upcomingEvents->map(fn (array $item) => (object) [
            'event' => $item['event'],
            'occurrence' => $item['occurrence'],
        ]);

        return view('grid.grid', compact(
            'categories',
            'facilities',
            'groupedFacilities',
            'effectData',
            'conditionData',
            'upcomingEvents',
            'eventEffectData',
        ));
    }
}
