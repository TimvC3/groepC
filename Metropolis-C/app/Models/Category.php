<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
 
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];
 
    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class)->orderBy('sort_order');
    }
 
    /** All scores that target this category (column scores) */
    public function scores(): HasMany
    {
        return $this->hasMany(FacilityScore::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_category', 'category_id', 'event_id')
            ->withTimestamps();
    }
}