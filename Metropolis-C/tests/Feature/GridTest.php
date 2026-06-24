<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Facility;
use App\Models\FacilityScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GridTest extends TestCase
{
    use RefreshDatabase;

    public function test_grid_page_displays_effect_view_with_score_data(): void
    {
        $user = User::factory()->create();
        $security = Category::create([
            'name' => 'Security',
            'slug' => 'security',
            'sort_order' => 1,
        ]);
        $mobility = Category::create([
            'name' => 'Mobility',
            'slug' => 'mobility',
            'sort_order' => 2,
        ]);
        $policeStation = Facility::create([
            'category_id' => $security->id,
            'name' => 'Police Station',
            'slug' => 'police-station',
            'icon' => 'P',
            'sort_order' => 1,
        ]);

        FacilityScore::create([
            'facility_id' => $policeStation->id,
            'category_id' => $security->id,
            'score' => 5,
        ]);
        FacilityScore::create([
            'facility_id' => $policeStation->id,
            'category_id' => $mobility->id,
            'score' => -2,
        ]);
        $event = Event::create([
            'name' => 'Road Closure',
            'event_type' => 'Roadwork',
            'event_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '12:00',
            'recurrence_type' => 'none',
        ]);
        $event->categories()->sync([
            $mobility->id => ['score' => -4],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('grid'));

        $response
            ->assertOk()
            ->assertSee('Effect View')
            ->assertSee('Upcoming Events')
            ->assertSee('Event Effects')
            ->assertSee('Road Closure')
            ->assertSee('Total score')
            ->assertSee('id="effect-status"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('window.gridEffectData', false)
            ->assertSee('window.gridEventEffectData', false)
            ->assertViewHas('effectData', function (array $effectData) use ($policeStation, $security, $mobility): bool {
                return $effectData['scoreMatrix'][$policeStation->id][$security->id] === 5
                    && $effectData['scoreMatrix'][$policeStation->id][$mobility->id] === -2;
            })
            ->assertViewHas('eventEffectData', function (array $eventEffectData) use ($event, $mobility): bool {
                $eventData = $eventEffectData['events']->firstWhere('id', $event->id);

                $impact = collect($eventData['impacts'])->firstWhere('category_id', $mobility->id);

                return $impact
                    && $impact['score'] === -4;
            });
    }
}
