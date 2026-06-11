<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityRestriction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FacilityRestrictionTest extends DuskTestCase
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
        FacilityRestriction::create([
            'facility_id_1' => min($this->facilityA->id, $this->facilityB->id),
            'facility_id_2' => max($this->facilityA->id, $this->facilityB->id),
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

    public function test_admin_can_add_restriction_via_facilities_ui(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $idA = $this->facilityA->id;
        $idB = $this->facilityB->id;

        $this->browse(function (Browser $browser) use ($admin, $idA, $idB) {
            $browser->loginAs($admin)
                ->visit('/facilities')
                ->waitFor('#restriction_facility_1', 5)
                ->select('#restriction_facility_1', $idA)
                ->select('#restriction_facility_2', $idB)
                ->press('Add Restriction')
                ->waitForText('Restriction added successfully.')
                ->assertSee('Police Station')
                ->assertSee('Night Club');
        });

        $this->assertDatabaseHas('facility_restrictions', [
            'facility_id_1' => min($idA, $idB),
            'facility_id_2' => max($idA, $idB),
        ]);
    }

    public function test_admin_can_remove_restriction_via_facilities_ui(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        FacilityRestriction::create([
            'facility_id_1' => min($this->facilityA->id, $this->facilityB->id),
            'facility_id_2' => max($this->facilityA->id, $this->facilityB->id),
        ]);

        $restrictionId = FacilityRestriction::first()->id;

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/facilities')
                ->waitForText('Police Station')
                ->press('Remove')
                ->waitForText('Restriction removed successfully.');
        });

        $this->assertDatabaseMissing('facility_restrictions', ['id' => $restrictionId]);
    }
}
