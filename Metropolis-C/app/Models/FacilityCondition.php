<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityCondition extends Model
{
<<<<<<< HEAD
    public const REQUIRED_NEIGHBOUR = 'required_neighbour';

    public const FORBIDDEN_NEIGHBOUR = 'forbidden_neighbour';

=======
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
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
<<<<<<< HEAD
}
=======

    public function isRequiredNeighbour(): bool
    {
        return $this->condition_type === 'required_neighbour';
    }

    public function isForbiddenNeighbour(): bool
    {
        return $this->condition_type === 'forbidden_neighbour';
    }
}
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
