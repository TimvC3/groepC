<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\Request;

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

        $groupedEvents = $events
            ->map(fn (Event $event) => [
                'event' => $event,
                'status' => 'planned',
                'occurrence' => $event->nextOccurrenceAt(),
            ])
            ->groupBy('status');

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

        return view('events.index', compact('events', 'groupedEvents', 'categories', 'editingEvent', 'upcomingEvents'));
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

            $event->categories()->sync($this->categoryScores($validated));

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

            $event->categories()->sync($this->categoryScores($validated));
        });

        return redirect()
            ->route('events.index')
            ->with('success', "{$event->name} was updated successfully.");
    }

    private function categoryScores(array $validated): array
    {
        return collect($validated['scores'])
            ->mapWithKeys(fn ($score, $categoryId) => [
                (int) $categoryId => ['score' => (int) $score],
            ])
            ->filter(fn (array $data) => $data['score'] !== 0)
            ->all();
    }
    public function reschedule(Request $request, Event $event)
    {
        $validated = $request->validate([
            'event_date' => ['required', 'date'],
        ]);

        $newEvent = $event->replicate();
        $newEvent->event_date = $validated['event_date'];
        $newEvent->save();

        $newEvent->categories()->sync(
            $event->categories->mapWithKeys(fn ($c) => [
                $c->id => ['score' => $c->pivot->score]
            ])
        );

        return response()->json([
            'event' => $newEvent->load('categories'),
        ]);
    }
}
