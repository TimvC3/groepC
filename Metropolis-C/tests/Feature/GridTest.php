<?php

namespace Tests\Feature;

use App\Models\Category;
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
        $safety = Category::create([
            'name' => 'Safety',
            'slug' => 'safety',
            'sort_order' => 1,
        ]);
        $mobility = Category::create([
            'name' => 'Mobility',
            'slug' => 'mobility',
            'sort_order' => 2,
        ]);
        $policeStation = Facility::create([
            'category_id' => $safety->id,
            'name' => 'Police Station',
            'slug' => 'police-station',
            'icon' => 'P',
            'sort_order' => 1,
        ]);

        FacilityScore::create([
            'facility_id' => $policeStation->id,
            'category_id' => $safety->id,
            'score' => 5,
        ]);
        FacilityScore::create([
            'facility_id' => $policeStation->id,
            'category_id' => $mobility->id,
            'score' => -2,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('grid'));

        $response
            ->assertOk()
            ->assertSee('Effect View')
            ->assertSee('Totale score')
            ->assertSee('id="effect-status"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('window.gridEffectData', false)
            ->assertViewHas('effectData', function (array $effectData) use ($policeStation, $safety, $mobility): bool {
                return $effectData['scoreMatrix'][$policeStation->id][$safety->id] === 5
                    && $effectData['scoreMatrix'][$policeStation->id][$mobility->id] === -2;
            });
    }
}
