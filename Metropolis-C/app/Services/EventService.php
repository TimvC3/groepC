<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function create(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $categoryScores = collect($data['scores'])
                ->mapWithKeys(fn ($score, $categoryId) => [
                    (int) $categoryId => ['score' => (int) $score],
                ])
                ->filter(fn (array $scoreData) => $scoreData['score'] !== 0)
                ->all();

            unset($data['scores']);

            $event = Event::create($data);

            $event->categories()->sync($categoryScores);

            return $event;
        });
    }

    public function update(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($event, $data) {
            $categoryScores = collect($data['scores'])
                ->mapWithKeys(fn ($score, $categoryId) => [
                    (int) $categoryId => ['score' => (int) $score],
                ])
                ->filter(fn (array $scoreData) => $scoreData['score'] !== 0)
                ->all();

            unset($data['scores']);

            $event->update($data);

            $event->categories()->sync($categoryScores);

            return $event;
        });
    }
}
