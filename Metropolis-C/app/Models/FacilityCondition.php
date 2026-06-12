<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityCondition extends Model
{
    protected $fillable = [
        'facility_id',
        'condition_type',
        'neighbour_facility_id',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function neighbourFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'neighbour_facility_id');
    }

    public function isRequiredNeighbour(): bool
    {
        return $this->condition_type === 'required_neighbour';
    }

    public function isForbiddenNeighbour(): bool
    {
        return $this->condition_type === 'forbidden_neighbour';
    }
}