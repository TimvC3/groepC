<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FacilityCondition;

class Facility extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'icon', 'sort_order'];
 
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
 
    public function scores(): HasMany
    {
        return $this->hasMany(FacilityScore::class);
    }
 
    /**
     * Retrieve the score for a given category slug.
     * Example: $facility->scoreFor('security')
     */
    public function scoreFor(string $categorySlug): ?int
    {
        $score = $this->scores()
            ->whereHas('category', fn ($q) => $q->where('slug', $categorySlug))
            ->first();
 
        return $score?->score;
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(FacilityCondition::class);
    }

    public function requiredNeighbours(): HasMany
    {
        return $this->conditions()
            ->where('condition_type', 'required_neighbour');
    }

    public function forbiddenNeighbours(): HasMany
    {
        return $this->conditions()
            ->where('condition_type', 'forbidden_neighbour');
    }
}
