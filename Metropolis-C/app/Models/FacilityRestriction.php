<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityRestriction extends Model
{
    protected $fillable = ['facility_id_1', 'facility_id_2'];

    public function facility1(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id_1');
    }

    public function facility2(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id_2');
    }
}
