<?php

namespace App\Models;

use App\Enums\RecurrenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'name',
        'event_type',
        'event_date',
        'start_time',
        'recurrence_type',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'recurrence_type' => RecurrenceType::class,
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'event_category', 'event_id', 'category_id')
            ->withPivot('score')
            ->withTimestamps();
    }
}
