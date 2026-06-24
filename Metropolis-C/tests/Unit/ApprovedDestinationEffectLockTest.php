<?php

namespace Tests\Unit;

use App\Http\Controllers\FacilityController;
use App\Models\ApprovedGridCell;
use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ApprovedDestinationEffectLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_effect_score_of_approved_destination_cannot_be_changed(): void
    {
        $score = $this->createFacilityScore(2);

        ApprovedGridCell::create([
            'cell_index' => 1,
            'item_type' => 'facility',
            'item_id' => $score->facility_id,
            'item_name' => $score->facility->name,
        ]);

        $response = (new FacilityController)->update(
            Request::create('/facilities/scores/'.$score->id, 'PATCH', ['score' => 5]),
            $score,
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame(
            'This destination has already been approved and its effects can no longer be changed.',
            $response->getData(true)['message'],
        );
        $this->assertSame(2, $score->fresh()->score);
    }

    public function test_effect_score_of_unapproved_destination_can_be_changed(): void
    {
        $score = $this->createFacilityScore(2);

        $response = (new FacilityController)->update(
            Request::create('/facilities/scores/'.$score->id, 'PATCH', ['score' => 5]),
            $score,
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(5, $response->getData(true)['score']);
        $this->assertSame(5, $score->fresh()->score);
    }

    private function createFacilityScore(int $score): FacilityScore
    {
        $category = Category::create([
            'name' => 'Security',
            'slug' => 'security',
            'sort_order' => 1,
        ]);

        $facility = Facility::create([
            'category_id' => $category->id,
            'name' => 'Police Station',
            'slug' => 'police-station',
            'icon' => 'police',
            'sort_order' => 1,
        ]);

        return FacilityScore::create([
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'score' => $score,
        ]);
    }
}
