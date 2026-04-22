<?php

namespace Tests\Unit;

use App\Support\ZoningDesignationLibrary;
use PHPUnit\Framework\TestCase;

class ZoningDesignationLibraryTest extends TestCase
{
    public function test_it_returns_all_zoning_designations(): void
    {
        $designations = ZoningDesignationLibrary::all();

        $this->assertIsArray($designations);
        $this->assertCount(13, $designations);
    }

    public function test_each_designation_has_required_fields(): void
    {
        $designations = ZoningDesignationLibrary::all();

        foreach ($designations as $designation) {
            $this->assertArrayHasKey('id', $designation);
            $this->assertArrayHasKey('name', $designation);
            $this->assertArrayHasKey('category', $designation);
            $this->assertArrayHasKey('icon', $designation);

            $this->assertNotEmpty($designation['id']);
            $this->assertNotEmpty($designation['name']);
            $this->assertNotEmpty($designation['category']);
            $this->assertNotEmpty($designation['icon']);
        }
    }

    public function test_it_contains_expected_designations(): void
    {
        $designations = ZoningDesignationLibrary::all();
        $names = array_column($designations, 'name');

        $this->assertContains('Police Station', $names);
        $this->assertContains('Fire Station', $names);
        $this->assertContains('Park', $names);
        $this->assertContains('Cinema', $names);
        $this->assertContains('Hospital', $names);
    }

    public function test_designations_have_valid_categories(): void
    {
        $designations = ZoningDesignationLibrary::all();
        $allowedCategories = ['Safety', 'Recreation', 'Environment', 'Facility', 'Mobility'];

        foreach ($designations as $designation) {
            $this->assertContains($designation['category'], $allowedCategories);
        }
    }
}