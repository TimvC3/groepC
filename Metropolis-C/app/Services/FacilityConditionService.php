<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\FacilityCondition;

class FacilityConditionService
{
    public function createCondition(Facility $facility, array $data): FacilityCondition
    {
        return $facility->conditions()->create($data);
    }

    public function updateCondition(FacilityCondition $condition, array $data): FacilityCondition
    {
        $condition->update($data);

        return $condition;
    }

    public function deleteCondition(FacilityCondition $condition): void
    {
        $condition->delete();
    }
}