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

        $facilities = Facility::with(['category', 'scores.category'])
            ->orderBy('sort_order')
            ->get();

        $groupedFacilities = $facilities->groupBy('category.name');
        $effectData = GridEffectData::from($categories, $facilities);
        $upcomingEvents = Event::with('categories')
            ->get()
            ->map(fn (Event $event) => [
                'event' => $event,
                'occurrence' => $event->nextOccurrenceAt(),
            ])
            ->filter(fn (array $item) => $item['occurrence'] !== null)
            ->sortBy(fn (array $item) => $item['occurrence']['starts_at']->timestamp)
            ->take(3)
            ->values();

        $eventEffectData = [
            'events' => $upcomingEvents->map(fn (array $item) => [
                'id' => $item['event']->id,
                'name' => $item['event']->name,
                'status' => $item['event']->statusAt(),
                'date' => $item['occurrence']['starts_at']->format('d-m-Y'),
                'startTime' => $item['occurrence']['starts_at']->format('H:i'),
                'endTime' => $item['occurrence']['ends_at']->format('H:i'),
                'categoryId' => $item['event']->affectedCategory()?->id,
                'categoryName' => $item['event']->affectedCategory()?->name,
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
            'upcomingEvents',
            'eventEffectData',
        ));
    }
}
