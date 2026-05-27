<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_planner_can_create_event_for_multiple_affected_categories(): void
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

        $response = $this
            ->actingAs($user)
            ->post(route('events.store'), [
                'name' => 'Music Festival',
                'event_type' => 'Festival',
                'event_date' => '2026-05-26',
                'start_time' => '10:00',
                'end_time' => '14:00',
                'recurrence_type' => 'none',
                'scores' => [
                    $security->id => -3,
                    $mobility->id => 2,
                ],
            ]);

        $response->assertRedirect(route('events.index'));

        $event = Event::with('categories')->firstOrFail();

        $this->assertSame('Music Festival', $event->name);
        $this->assertTrue($event->categories->contains($security));
        $this->assertTrue($event->categories->contains($mobility));
        $this->assertSame(-3, (int) $event->categories->firstWhere('id', $security->id)->pivot->score);
        $this->assertSame(2, (int) $event->categories->firstWhere('id', $mobility->id)->pivot->score);
    }

    public function test_event_overview_shows_statuses_and_upcoming_events(): void
    {
        Carbon::setTestNow('2026-05-25 12:00:00');

        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Mobility',
            'slug' => 'mobility',
            'sort_order' => 1,
        ]);

        $active = $this->eventWithCategory('Road Closure', '2026-05-25', '10:00', '14:00', $category, -2);
        $planned = $this->eventWithCategory('Market', '2026-05-26', '09:00', '11:00', $category, 1);
        $past = $this->eventWithCategory('Parade', '2026-05-24', '09:00', '11:00', $category, 2);

        $response = $this
            ->actingAs($user)
            ->get(route('events.index'));

        $response
            ->assertOk()
            ->assertSee('Upcoming Events')
            ->assertSee($active->name)
            ->assertSee($planned->name)
            ->assertSee($past->name)
            ->assertSee('active')
            ->assertSee('planned')
            ->assertSee('past');

        Carbon::setTestNow();
    }

    public function test_recurring_event_with_past_start_date_shows_next_occurrence(): void
    {
        Carbon::setTestNow('2026-05-25 12:00:00');

        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Mobility',
            'slug' => 'mobility',
            'sort_order' => 1,
        ]);

        $event = Event::create([
            'name' => 'Weekly Market',
            'event_type' => 'Market',
            'event_date' => '2026-05-18',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'recurrence_type' => 'weekly',
        ]);
        $event->categories()->sync([
            $category->id => ['score' => 2],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('events.index'));

        $response
            ->assertOk()
            ->assertSee('Weekly Market')
            ->assertSee('01-06-2026')
            ->assertSee('planned');

        Carbon::setTestNow();
    }

    private function eventWithCategory(
        string $name,
        string $date,
        string $startTime,
        string $endTime,
        Category $category,
        int $score
    ): Event {
        $event = Event::create([
            'name' => $name,
            'event_type' => 'City event',
            'event_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'recurrence_type' => 'none',
        ]);

        $event->categories()->sync([
            $category->id => ['score' => $score],
        ]);

        return $event->load('categories');
    }
}
