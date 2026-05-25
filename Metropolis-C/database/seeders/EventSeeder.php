<?php

namespace Database\Seeders;

use App\Enums\RecurrenceType;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $categories = DB::table('categories')->pluck('id', 'slug');

        $events = [
            [
                'name' => 'Road Maintenance',
                'event_type' => 'Roadwork',
                'event_date' => Carbon::today()->addDays(2)->toDateString(),
                'start_time' => '08:00',
                'end_time' => '16:00',
                'recurrence_type' => RecurrenceType::None,
                'category_slug' => 'mobility',
                'score' => -3,
            ],
            [
                'name' => 'City Festival',
                'event_type' => 'Festival',
                'event_date' => Carbon::today()->addDays(5)->toDateString(),
                'start_time' => '12:00',
                'end_time' => '22:00',
                'recurrence_type' => RecurrenceType::None,
                'category_slug' => 'recreation',
                'score' => 4,
            ],
            [
                'name' => 'Weekly Market',
                'event_type' => 'Market',
                'event_date' => Carbon::today()->subWeek()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '13:00',
                'recurrence_type' => RecurrenceType::Weekly,
                'category_slug' => 'facilities',
                'score' => 2,
            ],
            [
                'name' => 'Park Cleanup',
                'event_type' => 'Community',
                'event_date' => Carbon::today()->subDays(3)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'recurrence_type' => RecurrenceType::None,
                'category_slug' => 'environmental-quality',
                'score' => 3,
            ],
            [
                'name' => 'Security Drill',
                'event_type' => 'Safety',
                'event_date' => Carbon::today()->toDateString(),
                'start_time' => Carbon::now()->subHour()->format('H:i'),
                'end_time' => Carbon::now()->addHours(2)->format('H:i'),
                'recurrence_type' => RecurrenceType::None,
                'category_slug' => 'security',
                'score' => 1,
            ],
        ];

        foreach ($events as $eventData) {
            $categorySlug = $eventData['category_slug'];
            $score = $eventData['score'];

            unset($eventData['category_slug'], $eventData['score']);

            $event = Event::create($eventData);

            $event->categories()->sync([
                $categories[$categorySlug] => ['score' => $score],
            ]);
        }
    }
}
