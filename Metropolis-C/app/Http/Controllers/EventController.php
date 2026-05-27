<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return $this->eventsView();
    }

    public function edit(Event $event): View
    {
        $event->load('categories');

        return $this->eventsView($event);
    }

    private function eventsView(?Event $editingEvent = null): View
    {
        $categories = Category::orderBy('sort_order')->get();

        $events = Event::with('categories')
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

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

        return view('events.index', compact('events', 'categories', 'editingEvent', 'upcomingEvents'));
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $event = DB::transaction(function () use ($validated) {
            $event = Event::create([
                'name' => $validated['name'],
                'event_type' => $validated['event_type'],
                'event_date' => $validated['event_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'recurrence_type' => $validated['recurrence_type'],
            ]);

            $event->categories()->sync($this->categoryScore($validated));

            return $event;
        });

        return redirect()
            ->route('events.index')
            ->with('success', "{$event->name} was created successfully.");
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($event, $validated) {
            $event->update([
                'name' => $validated['name'],
                'event_type' => $validated['event_type'],
                'event_date' => $validated['event_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'recurrence_type' => $validated['recurrence_type'] ?? 'none',
            ]);

            $event->categories()->sync($this->categoryScore($validated));
        });

        return redirect()
            ->route('events.index')
            ->with('success', "{$event->name} was updated successfully.");
    }

    private function categoryScore(array $validated): array
    {
        return [
            (int) $validated['category_id'] => [
                'score' => (int) $validated['score'],
            ],
        ];
    }
}
