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

        return view('events.index', compact('events', 'categories', 'editingEvent'));
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
        $syncData = [];

        Category::orderBy('sort_order')->each(function (Category $category) use (&$syncData, $validated) {
            $syncData[$category->id] = [
                'score' => (int) ($validated['scores'][$category->id] ?? 0),
            ];
        });

        return $syncData;
    }
}