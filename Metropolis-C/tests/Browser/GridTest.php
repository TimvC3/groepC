<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GridTest extends DuskTestCase
{
    public function test_grid_is_visible()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5)
                ->assertPresent('#grid');
        });
    }

    public function test_grid_has_12_cells()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5);

            $count = $browser->script("
                return document.querySelectorAll('#grid [data-testid^=\"district-\"]').length;
            ")[0];

            $this->assertEquals(12, $count);
        });
    }

    public function test_grid_has_4_columns()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5)
                ->assertAttributeContains('#grid', 'class', 'grid-cols-4');
        });
    }

    public function test_all_cells_are_visible()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5);

            for ($i = 1; $i <= 12; $i++) {
                $browser->assertVisible("[data-testid=\"district-{$i}\"]");
            }
        });
    }

    public function test_cell_can_be_selected()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5)
                ->click('[data-testid="district-1"]')
                ->pause(250)
                ->assertAttributeContains('[data-testid="district-1"]', 'class', 'selected')
                ->assertAttribute('[data-testid="district-1"]', 'aria-pressed', 'true');
        });
    }

    public function test_cell_toggle_selection()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5)
                ->click('[data-testid="district-1"]')
                ->pause(200)
                ->click('[data-testid="district-1"]')
                ->pause(200);

            $classes = $browser->attribute('[data-testid="district-1"]', 'class');
            $ariaPressed = $browser->attribute('[data-testid="district-1"]', 'aria-pressed');

            $this->assertStringNotContainsString('selected', $classes);
            $this->assertEquals('false', $ariaPressed);
        });
    }

    public function test_multiple_cells_can_be_selected()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5)
                ->click('[data-testid="district-1"]')
                ->click('[data-testid="district-2"]')
                ->pause(250)
                ->assertAttributeContains('[data-testid="district-1"]', 'class', 'selected')
                ->assertAttributeContains('[data-testid="district-2"]', 'class', 'selected');
        });
    }

    public function test_cells_are_buttons()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5);

            $tag = $browser->script("
                return document.querySelector('[data-testid=\"district-1\"]').tagName.toLowerCase();
            ")[0];

            $this->assertEquals('button', $tag);
        });
    }

    public function test_cells_are_focusable()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5);

            $browser->script("
                document.querySelector('[data-testid=\"district-1\"]').focus();
            ");

            $isFocused = $browser->script("
                return document.activeElement === document.querySelector('[data-testid=\"district-1\"]');
            ")[0];

            $this->assertTrue($isFocused);
        });
    }

    public function test_cell_can_be_selected_with_keyboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/grid')
                ->waitFor('#grid', 5);

            $browser->script("
                const cell = document.querySelector('[data-testid=\"district-1\"]');
                cell.focus();
                cell.click();
            ");

            $browser->pause(250);

            $classes = $browser->attribute('[data-testid="district-1"]', 'class');
            $ariaPressed = $browser->attribute('[data-testid="district-1"]', 'aria-pressed');

            $this->assertStringContainsString('selected', $classes);
            $this->assertEquals('true', $ariaPressed);
        });
    }
}