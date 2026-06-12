<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityCondition extends Model
{
    public const REQUIRED_NEIGHBOUR = 'required_neighbour';

    public const FORBIDDEN_NEIGHBOUR = 'forbidden_neighbour';

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
        return $this->condition_type === self::REQUIRED_NEIGHBOUR;
    }

    public function isForbiddenNeighbour(): bool
    {
        return $this->condition_type === self::FORBIDDEN_NEIGHBOUR;
    }
}
