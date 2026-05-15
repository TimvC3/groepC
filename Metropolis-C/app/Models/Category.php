<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
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
}