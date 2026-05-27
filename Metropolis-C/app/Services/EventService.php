<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function create(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $categoryIds = $data['category_ids'];

            unset($data['category_ids']);

            $event = Event::create($data);

            $event->categories()->sync($categoryIds);

            return $event;
        });
    }

    public function update(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($event, $data) {
            $categoryIds = $data['category_ids'];

            unset($data['category_ids']);

            $event->update($data);

            $event->categories()->sync($categoryIds);

            return $event;
        });
    }
}