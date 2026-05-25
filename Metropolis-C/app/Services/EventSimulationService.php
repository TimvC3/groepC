<?php

namespace App\Services;

use App\Models\Event;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class EventSimulationService
{
    public function activeEventsAt(CarbonInterface $simulationDateTime): Collection
    {
        return Event::with('categories')
            ->get()
            ->filter(fn (Event $event) => $event->isActiveAt($simulationDateTime))
            ->values();
    }

    public function categoryImpactScoresAt(CarbonInterface $simulationDateTime): array
    {
        $scores = [];

        $activeEvents = $this->activeEventsAt($simulationDateTime);

        foreach ($activeEvents as $event) {
            foreach ($event->categories as $category) {
                $scores[$category->id] = ($scores[$category->id] ?? 0)
                    + (int) $category->pivot->score;
            }
        }

        return $scores;
    }
}