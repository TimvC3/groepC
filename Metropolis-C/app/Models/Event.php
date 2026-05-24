<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'name',
        'event_date',
        'start_time',
        'is_recurring',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'is_recurring' => 'boolean',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'event_category', 'event_id', 'category_id')
            ->withPivot('score')
            ->withTimestamps();
    }
}
