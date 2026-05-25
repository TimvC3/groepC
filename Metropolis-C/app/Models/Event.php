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
        'end_time',
        'recurrence_type',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'recurrence_type' => RecurrenceType::class,
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'event_category', 'event_id', 'category_id')
            ->withPivot('score')
            ->withTimestamps();
    }

    public function isActiveAt(CarbonInterface $simulationDateTime): bool
    {
        if (! $this->occursOn($simulationDateTime)) {
            return false;
        }

        $startDateTime = $simulationDateTime->copy()->setTimeFromTimeString(
            Carbon::parse($this->start_time)->format('H:i:s')
        );

        $endDateTime = $simulationDateTime->copy()->setTimeFromTimeString(
            Carbon::parse($this->end_time)->format('H:i:s')
        );

        if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
            $endDateTime->addDay();
        }

        return $simulationDateTime->betweenIncluded($startDateTime, $endDateTime);
    }

    private function occursOn(CarbonInterface $simulationDateTime): bool
    {
        $eventDate = Carbon::parse($this->event_date)->startOfDay();
        $simulationDate = $simulationDateTime->copy()->startOfDay();

        if ($simulationDate->lt($eventDate)) {
            return false;
        }

        return match ($this->recurrence_type) {
            RecurrenceType::None => $simulationDate->isSameDay($eventDate),

            RecurrenceType::Daily => true,

            RecurrenceType::Weekly => $simulationDate->dayOfWeek === $eventDate->dayOfWeek,

            RecurrenceType::Monthly => $simulationDate->day === $eventDate->day,

            RecurrenceType::Yearly => $simulationDate->month === $eventDate->month
                && $simulationDate->day === $eventDate->day,
        };
    }
}
