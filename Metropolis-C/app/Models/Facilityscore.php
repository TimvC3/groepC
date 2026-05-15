<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class FacilityScore extends Model
{
    protected $fillable = ['facility_id', 'category_id', 'score'];
 
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
 
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}