<?php

namespace App\Models;

use App\Enums\RecurrenceType;
use Carbon\Carbon;
use Carbon\CarbonInterface;
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

    public function affectedCategory(): ?Category
    {
        return $this->categories->first();
    }

    public function impactScore(): int
    {
        return (int) ($this->affectedCategory()?->pivot?->score ?? 0);
    }

    public function statusAt(?CarbonInterface $dateTime = null): string
    {
        $dateTime ??= now();

        if ($this->isActiveAt($dateTime)) {
            return 'active';
        }

        if (
            $this->recurrence_type === RecurrenceType::None
            && $this->hasOriginalOccurrenceEndedAt($dateTime)
        ) {
            return 'past';
        }

        return 'planned';
    }

    /**
     * @return array{starts_at: CarbonInterface, ends_at: CarbonInterface}|null
     */
    public function nextOccurrenceAt(?CarbonInterface $dateTime = null): ?array
    {
        $dateTime ??= now();

        if ($this->recurrence_type === RecurrenceType::None) {
            return $this->hasOriginalOccurrenceEndedAt($dateTime)
                ? null
                : [
                    'starts_at' => $this->startsAt(),
                    'ends_at' => $this->endsAt(),
                ];
        }

        $candidateDate = $dateTime->copy()->startOfDay();
        $startDate = $this->event_date->copy()->startOfDay();

        if ($candidateDate->lt($startDate)) {
            $candidateDate = $startDate;
        }

        for ($daysChecked = 0; $daysChecked < 370; $daysChecked++) {
            if ($this->occursOn($candidateDate)) {
                $startsAt = $candidateDate->copy()->setTimeFromTimeString(
                    Carbon::parse($this->start_time)->format('H:i:s')
                );
                $endsAt = $candidateDate->copy()->setTimeFromTimeString(
                    Carbon::parse($this->end_time)->format('H:i:s')
                );

                if ($endsAt->lessThanOrEqualTo($startsAt)) {
                    $endsAt->addDay();
                }

                if ($dateTime->lessThanOrEqualTo($endsAt)) {
                    return [
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                    ];
                }
            }

            $candidateDate->addDay();
        }

        return null;
    }

    public function startsAt(): CarbonInterface
    {
        return Carbon::parse(
            $this->event_date->format('Y-m-d').' '.Carbon::parse($this->start_time)->format('H:i:s')
        );
    }

    public function endsAt(): CarbonInterface
    {
        $endDateTime = Carbon::parse(
            $this->event_date->format('Y-m-d').' '.Carbon::parse($this->end_time)->format('H:i:s')
        );

        if ($endDateTime->lessThanOrEqualTo($this->startsAt())) {
            $endDateTime->addDay();
        }

        return $endDateTime;
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

    public function hasOriginalOccurrenceEndedAt(CarbonInterface $simulationDateTime): bool
    {
        return $simulationDateTime->greaterThan($this->endsAt());
    }
}
