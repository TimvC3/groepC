<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityScore;
use App\Support\GridEffectData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GridEffectDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_effect_categories_and_facility_score_matrix(): void
    {
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

        $effectData = GridEffectData::from(
            Category::orderBy('sort_order')->get(),
            Facility::with(['category', 'scores'])->get(),
        );

        $this->assertSame([
            ['id' => $security->id, 'name' => 'Security', 'slug' => 'security'],
            ['id' => $mobility->id, 'name' => 'Mobility', 'slug' => 'mobility'],
        ], $effectData['categories']->all());

        $this->assertSame(5, $effectData['scoreMatrix'][$policeStation->id][$security->id]);
        $this->assertSame(-2, $effectData['scoreMatrix'][$policeStation->id][$mobility->id]);
        $this->assertSame('police-station', $effectData['facilities'][$policeStation->id]['slug']);
        $this->assertSame('security', $effectData['facilities'][$policeStation->id]['categorySlug']);
    }
}
