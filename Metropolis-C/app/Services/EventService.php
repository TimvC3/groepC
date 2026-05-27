<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function create(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $categoryScore = [
                $data['category_id'] => ['score' => (int) $data['score']],
            ];

            unset($data['category_id'], $data['score']);

            $event = Event::create($data);

            $event->categories()->sync($categoryScore);

            return $event;
        });
    }

    public function update(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($event, $data) {
            $categoryScore = [
                $data['category_id'] => ['score' => (int) $data['score']],
            ];

            unset($data['category_id'], $data['score']);

            $event->update($data);

            $event->categories()->sync($categoryScore);

            return $event;
        });
    }
}
