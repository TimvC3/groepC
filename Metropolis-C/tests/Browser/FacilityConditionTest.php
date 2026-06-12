<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityCondition;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FacilityConditionTest extends DuskTestCase
{
    use DatabaseMigrations;

    private Category $category;

    private Facility $facilityA;

    private Facility $facilityB;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['name' => 'General', 'slug' => 'general', 'sort_order' => 1]);

        $this->facilityA = Facility::create([
            'category_id' => $this->category->id,
            'name' => 'Police Station',
            'slug' => 'police-station',
            'icon' => '🚔',
            'sort_order' => 1,
        ]);

        $this->facilityB = Facility::create([
            'category_id' => $this->category->id,
            'name' => 'Night Club',
            'slug' => 'night-club',
            'icon' => '🎵',
            'sort_order' => 2,
        ]);

        $this->user = User::factory()->create(['role' => 'city_planner']);
    }

    public function test_error_appears_when_dropping_restricted_facility_onto_adjacent_cell(): void
    {
        FacilityCondition::create([
            'facility_id' => min($this->facilityA->id, $this->facilityB->id),
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => max($this->facilityA->id, $this->facilityB->id),
        ]);

        $idA = $this->facilityA->id;
        $idB = $this->facilityB->id;

        $this->browse(function (Browser $browser) use ($idA, $idB) {
            $browser->loginAs($this->user)
                ->visit('/grid')
                ->waitFor('.grid-cell[data-index="1"]', 5);

            // Drag facility A onto cell 1
            $browser->script("
                document.querySelector('.zoning-item[data-id=\"{$idA}\"]')
                    ?.dispatchEvent(new DragEvent('dragstart', { bubbles: true, dataTransfer: new DataTransfer() }));
                document.querySelector('.grid-cell[data-index=\"1\"]')
                    ?.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: new DataTransfer() }));
            ");

            $browser->pause(300);

            // Verify facility A is placed in cell 1
            $placedA = $browser->script("
                const cell = document.querySelector('.grid-cell[data-index=\"1\"]');
                return cell?.dataset.itemId === '{$idA}';
            ")[0];

            $this->assertTrue($placedA, 'Facility A should be placed in cell 1 first');

            // Try to drag facility B onto cell 2 (adjacent to cell 1 — restricted combination)
            $browser->script("
                document.querySelector('.zoning-item[data-id=\"{$idB}\"]')
                    ?.dispatchEvent(new DragEvent('dragstart', { bubbles: true, dataTransfer: new DataTransfer() }));
                document.querySelector('.grid-cell[data-index=\"2\"]')
                    ?.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: new DataTransfer() }));
            ");

            $browser->pause(300);

            // Assert the error toast is shown
            $browser->waitFor('#restriction-error', 3)
                ->assertSeeIn('#restriction-error', 'Placement not allowed')
                ->assertSeeIn('#restriction-error', 'Night Club');

            // Assert cell 2 is still empty
            $cell2Empty = $browser->script("
                return !document.querySelector('.grid-cell[data-index=\"2\"]')?.dataset.itemId;
            ")[0];

            $this->assertTrue($cell2Empty, 'Cell 2 should remain empty after rejected placement');
        });
    }

    public function test_non_restricted_facility_can_be_placed_next_to_another(): void
    {
        // No restriction between facilityA and facilityB

        $idA = $this->facilityA->id;
        $idB = $this->facilityB->id;

        $this->browse(function (Browser $browser) use ($idA, $idB) {
            $browser->loginAs($this->user)
                ->visit('/grid')
                ->waitFor('.grid-cell[data-index="1"]', 5);

            // Place A in cell 1
            $browser->script("
                document.querySelector('.zoning-item[data-id=\"{$idA}\"]')
                    ?.dispatchEvent(new DragEvent('dragstart', { bubbles: true, dataTransfer: new DataTransfer() }));
                document.querySelector('.grid-cell[data-index=\"1\"]')
                    ?.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: new DataTransfer() }));
            ");

            $browser->pause(300);

            // Place B in cell 2 (adjacent, no restriction)
            $browser->script("
                document.querySelector('.zoning-item[data-id=\"{$idB}\"]')
                    ?.dispatchEvent(new DragEvent('dragstart', { bubbles: true, dataTransfer: new DataTransfer() }));
                document.querySelector('.grid-cell[data-index=\"2\"]')
                    ?.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: new DataTransfer() }));
            ");

            $browser->pause(300);

            // No error toast should appear
            $errorVisible = $browser->script("return !!document.getElementById('restriction-error');")[0];
            $this->assertFalse($errorVisible, 'No error toast should appear for non-restricted facilities');

            // Facility B should be placed in cell 2
            $placedB = $browser->script("
                return document.querySelector('.grid-cell[data-index=\"2\"]')?.dataset.itemId === '{$idB}';
            ")[0];

            $this->assertTrue($placedB, 'Facility B should be placed in cell 2');
        });
    }

    public function test_library_manager_can_add_condition_via_facilities_ui(): void
    {
        $libraryManager = User::factory()->create(['role' => 'library_manager']);
        $idA = $this->facilityA->id;
        $idB = $this->facilityB->id;

        $this->browse(function (Browser $browser) use ($libraryManager, $idA, $idB) {
            $browser->loginAs($libraryManager)
                ->visit('/facilities')
                ->waitFor('#condition_facility', 5)
                ->select('#condition_facility', $idA)
                ->select('#condition_type', FacilityCondition::FORBIDDEN_NEIGHBOUR)
                ->select('#condition_related_facility', $idB)
                ->press('Add Condition')
                ->waitForText('Condition created successfully.')
                ->assertSee('Police Station')
                ->assertSee('Night Club');
        });

        $this->assertDatabaseHas('facility_conditions', [
            'facility_id' => min($idA, $idB),
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => max($idA, $idB),
        ]);
    }

    public function test_library_manager_can_remove_condition_via_facilities_ui(): void
    {
        $libraryManager = User::factory()->create(['role' => 'library_manager']);

        FacilityCondition::create([
            'facility_id' => min($this->facilityA->id, $this->facilityB->id),
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => max($this->facilityA->id, $this->facilityB->id),
        ]);

        $conditionId = FacilityCondition::first()->id;

        $this->browse(function (Browser $browser) use ($libraryManager) {
            $browser->loginAs($libraryManager)
                ->visit('/facilities')
                ->waitForText('Police Station')
                ->press('Delete')
                ->acceptDialog()
                ->waitForText('Condition deleted successfully.');
        });

        $this->assertDatabaseMissing('facility_conditions', ['id' => $conditionId]);
    }
}
