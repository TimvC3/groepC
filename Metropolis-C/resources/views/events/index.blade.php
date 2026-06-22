<x-app-layout>
    @php
        $isEditing = isset($editingEvent) && $editingEvent;
        $recurrenceTypes = \App\Enums\RecurrenceType::cases();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ __('Events') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Manage city events, statuses, and category impacts.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                    <p class="font-semibold">
                        {{ __('The event could not be saved:') }}
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Upcoming Events') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('The next three events are based on the selected simulation date and time.') }}
                    </p>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-3" data-upcoming-events-list>
                    @forelse ($upcomingEvents as $upcomingEvent)
                        @php
                            $event = $upcomingEvent['event'];
                            $occurrence = $upcomingEvent['occurrence'];
                            $impactScores = $event->impactScores();
                        @endphp

                        <article
                            class="hidden rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40"
                            data-upcoming-event-card
                            data-event-date="{{ $event->event_date?->format('Y-m-d') }}"
                            data-start-time="{{ \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') }}"
                            data-end-time="{{ \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }}"
                            data-recurrence-type="{{ $event->recurrence_type->value }}"
                            data-next-start="{{ $occurrence['starts_at']->timestamp }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $event->name }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300" data-upcoming-date-line>
                                        {{ $occurrence['starts_at']->format('d-m-Y') }}
                                        {{ $occurrence['starts_at']->format('H:i') }}
                                        -
                                        {{ $occurrence['ends_at']->format('H:i') }}
                                    </p>
                                </div>

                                <span
                                    data-event-status-badge
                                    class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold capitalize text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                >
                                    planned
                                </span>
                            </div>

                            <div class="mt-3 space-y-2 text-sm">
                                @foreach ($impactScores as $impact)
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-gray-600 dark:text-gray-300">
                                            {{ $impact['category_name'] }}
                                        </span>
                                        <span
                                            @class([
                                                'font-bold',
                                                'text-green-700 dark:text-green-300' => $impact['score'] > 0,
                                                'text-red-600 dark:text-red-300' => $impact['score'] < 0,
                                                'text-gray-500 dark:text-gray-400' => $impact['score'] === 0,
                                            ])
                                        >
                                            {{ $impact['score'] > 0 ? '+'.$impact['score'] : $impact['score'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('No upcoming events found.') }}
                        </p>
                    @endforelse

                    <p class="col-span-full text-sm text-gray-500 dark:text-gray-400" data-upcoming-empty-message>
                        {{ __('Select and start a simulation date and time on the grid to see upcoming events.') }}
                    </p>
                </div>
            </section>

            <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_24rem]">
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Existing Events') }}
                        </h3>
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="grid gap-3 md:grid-cols-3">
                                <input
                                    id="event-search"
                                    type="text"
                                    placeholder="Search by event name..."
                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                >

                                <input
                                    id="event-date-search"
                                    type="date"
                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                >

                                <input
                                    id="event-type-search"
                                    type="text"
                                    placeholder=" by type..."
                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @php
                            $statusSections = [
                                'active' => __('Active Events'),
                                'planned' => __('Planned Events'),
                                'past' => __('Past Events'),
                            ];
                        @endphp

                        @forelse ($statusSections as $sectionStatus => $sectionTitle)
                            @php
                                $sectionEvents = $groupedEvents->get($sectionStatus, collect());
                            @endphp

                            <section class="p-6" data-event-section="{{ $sectionStatus }}">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ $sectionTitle }}
                                    </h4>
                                    <span
                                        data-event-count="{{ $sectionStatus }}"
                                        class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    >
                                        {{ $sectionEvents->count() }}
                                    </span>
                                </div>

                                <div class="space-y-5" data-event-list="{{ $sectionStatus }}">
                                    @forelse ($sectionEvents as $eventItem)
                                        @php
                                            $event = $eventItem['event'];
                                            $status = $eventItem['status'];
                                            $occurrence = $eventItem['occurrence'];
                                            $impactScores = $event->impactScores();
                                        @endphp

                                        <article
                                            class="rounded-lg p-0 transition hover:bg-gray-50 dark:hover:bg-gray-700/40"
                                            data-event-card
                                            data-event-id="{{ $event->id }}"
                                            data-event-name="{{ strtolower($event->name) }}"
                                            data-event-type="{{ strtolower($event->event_type) }}"
                                            data-event-date="{{ $event->event_date?->format('Y-m-d') }}"
                                            data-start-time="{{ \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') }}"
                                            data-end-time="{{ \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }}"
                                            data-recurrence-type="{{ $event->recurrence_type->value }}"
                                        >
                                {{-- Event info bar --}}
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                                    <div class="flex flex-wrap items-center gap-4">
                                        <div class="min-w-[10rem] flex-1">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Event') }}
                                            </p>
                                            <p class="mt-1 truncate font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $event->name }}
                                            </p>

                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($impactScores as $impact)
                                                    <span
                                                        @class([
                                                            'inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold',
                                                            'border-green-200 bg-green-50 text-green-700 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300' => $impact['score'] > 0,
                                                            'border-red-200 bg-red-50 text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300' => $impact['score'] < 0,
                                                            'border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300' => $impact['score'] === 0,
                                                        ])
                                                    >
                                                        <span>{{ $impact['category_name'] }}</span>
                                                        <span>{{ $impact['score'] > 0 ? '+'.$impact['score'] : $impact['score'] }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="min-w-[8rem]">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Type') }}
                                            </p>
                                            <p class="mt-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ $event->event_type }}
                                            </p>
                                        </div>

                                        <div class="min-w-[7rem]">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ $event->recurrence_type->isRecurring() ? __('Next Date') : __('Date') }}
                                            </p>
                                            <p class="mt-1 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ $occurrence ? $occurrence['starts_at']->format('d-m-Y') : \Illuminate\Support\Carbon::parse($event->event_date)->format('d-m-Y') }}
                                            </p>
                                            @if ($event->recurrence_type->isRecurring())
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('Started') }} {{ \Illuminate\Support\Carbon::parse($event->event_date)->format('d-m-Y') }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="min-w-[9rem]">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Time') }}
                                            </p>
                                            <p class="mt-1 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ $occurrence ? $occurrence['starts_at']->format('H:i') : \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') }}
                                                -
                                                {{ $occurrence ? $occurrence['ends_at']->format('H:i') : \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }}
                                            </p>
                                        </div>

                                        <div class="min-w-[8rem]">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Simulation Status') }}
                                            </p>

                                            <span
                                                data-event-status-badge
                                                @class([
                                                    'mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize',
                                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $status === 'planned',
                                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $status === 'active',
                                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $status === 'past',
                                                ])
                                            >
                                                {{ __($status) }}
                                            </span>
                                        </div>

                                        <div class="min-w-[8rem]">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Recurrence') }}
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ __($event->recurrence_type->label()) }}
                                            </p>
                                        </div>

                                        <div class="ml-auto min-w-[4rem] text-right">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Edit') }}
                                            </p>

                                            <div class="mt-1 flex flex-col items-end gap-2">
                                                <a
                                                    href="{{ route('events.edit', $event) }}"
                                                    class="inline-flex text-sm font-medium text-indigo-600 transition hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    {{ __('Edit') }}
                                                </a>

                                                <button
                                                    type="button"
                                                    class="hidden inline-flex text-sm font-medium text-indigo-600 transition hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                    data-reschedule-btn
                                                    data-event-id="{{ $event->id }}"
                                                    data-event-name="{{ $event->name }}"
                                                >
                                                    {{ __('Reschedule') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                        </article>
                                    @empty
                                        <div
                                            data-empty-event-message
                                            class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
                                        >
                                            {{ __('No events in this status.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </section>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No events found.') }}
                            </div>
                        @endforelse
                    </div>
                </section>

                <section id="{{ $isEditing ? 'edit' : 'create' }}" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $isEditing ? __('Edit Event') : __('Create Event') }}
                    </h3>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ $isEditing ? __('Update event details and category impacts.') : __('Add an event and set category impacts.') }}
                    </p>

                    <form
                        method="POST"
                        action="{{ $isEditing ? route('events.update', $editingEvent) : route('events.store') }}"
                        class="mt-6 space-y-5"
                        onsubmit="this.querySelector('[type=submit]').disabled=true; this.querySelector('[type=submit]').innerText='Saving...';"
                    >
                        @csrf

                        @if ($isEditing)
                            @method('PATCH')
                        @endif

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Name') }}
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $editingEvent?->name) }}"
                                required
                                placeholder="Example: Music Festival"
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >

                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="event_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Event Type') }}
                            </label>

                            <input
                                id="event_type"
                                name="event_type"
                                type="text"
                                value="{{ old('event_type', $editingEvent?->event_type) }}"
                                required
                                placeholder="Example: Festival, Roadwork, Market"
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >

                            @error('event_type')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="event_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Date') }}
                            </label>

                            <input
                                id="event_date"
                                name="event_date"
                                type="date"
                                value="{{ old('event_date', $editingEvent?->event_date ? \Illuminate\Support\Carbon::parse($editingEvent->event_date)->format('Y-m-d') : '') }}"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >

                            @error('event_date')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Start Time') }}
                            </label>

                            <input
                                id="start_time"
                                name="start_time"
                                type="time"
                                value="{{ old('start_time', $editingEvent ? \Illuminate\Support\Carbon::parse($editingEvent->start_time)->format('H:i') : '') }}"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >

                            @error('start_time')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('End Time') }}
                            </label>

                            <input
                                id="end_time"
                                name="end_time"
                                type="time"
                                value="{{ old('end_time', $editingEvent ? \Illuminate\Support\Carbon::parse($editingEvent->end_time)->format('H:i') : '') }}"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >

                            @error('end_time')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="recurrence_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Recurrence') }}
                            </label>

                            <select
                                id="recurrence_type"
                                name="recurrence_type"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >
                                @foreach ($recurrenceTypes as $recurrenceType)
                                    <option
                                        value="{{ $recurrenceType->value }}"
                                        @selected(old('recurrence_type', $editingEvent?->recurrence_type?->value ?? 'none') === $recurrenceType->value)
                                    >
                                        {{ __($recurrenceType->label()) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('recurrence_type')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <p class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Category Impact Scores') }}
                            </p>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ __('Set -5 to 5 per category. Use 0 for categories this event should not affect.') }}
                            </p>

                            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach ($categories as $category)
                                    @php
                                        $existingCategory = $editingEvent?->categories->firstWhere('id', $category->id);
                                        $scoreValue = old(
                                            'scores.'.$category->id,
                                            $existingCategory?->pivot?->score ?? 0
                                        );
                                    @endphp

                                    <label class="block">
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                            {{ $category->name }}
                                        </span>

                                        <input
                                            name="scores[{{ $category->id }}]"
                                            type="number"
                                            min="-5"
                                            max="5"
                                            value="{{ $scoreValue }}"
                                            required
                                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                        >
                                    </label>
                                @endforeach
                            </div>

                            @error('scores')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            @error('scores.*')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ $isEditing ? __('Update Event') : __('Save Event') }}
                        </button>

                        @if ($isEditing)
                            <a
                                href="{{ route('events.index') }}"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                {{ __('Cancel Edit') }}
                            </a>
                        @endif
                    </form>
                </section>
            </div>
        </div>
    </div>
    <div id="reschedule-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('Reschedule Event') }}
            </h3>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300" id="reschedule-event-name"></p>

            <form id="reschedule-form" class="mt-4 space-y-4">
                <input type="hidden" name="event_id" id="reschedule-event-id">

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ __('New Date') }}
                    </label>

                    <input
                        type="date"
                        name="event_date"
                        id="reschedule-date"
                        required
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900"
                    >
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="reschedule-cancel"
                        class="rounded-lg border px-4 py-2 text-sm dark:border-gray-600">
                        {{ __('Cancel') }}
                    </button>

                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const simulationStorageKey = 'metropolis.simulationDateTime';
            const simulationValue = localStorage.getItem(simulationStorageKey);
            const storedSimulationDateTime = simulationValue ? new Date(simulationValue) : null;
            const simulationDateTime = storedSimulationDateTime && !Number.isNaN(storedSimulationDateTime.getTime())
                ? storedSimulationDateTime
                : null;

            if (simulationValue && !simulationDateTime) {
                localStorage.removeItem(simulationStorageKey);
            }

            const sections = {
                active: document.querySelector('[data-event-list="active"]'),
                planned: document.querySelector('[data-event-list="planned"]'),
                past: document.querySelector('[data-event-list="past"]'),
            };

            const emptyMessages = {};

            const modal = document.getElementById('reschedule-modal');
            const form = document.getElementById('reschedule-form');
            const cancelBtn = document.getElementById('reschedule-cancel');

            const eventIdInput = document.getElementById('reschedule-event-id');
            const dateInput = document.getElementById('reschedule-date');
            const nameLabel = document.getElementById('reschedule-event-name');

            Object.entries(sections).forEach(([status, section]) => {
                emptyMessages[status] = section?.querySelector('[data-empty-event-message]') ?? null;
            });

            function sameDate(dateTime, dateString) {
                const year = dateTime.getFullYear();
                const month = String(dateTime.getMonth() + 1).padStart(2, '0');
                const day = String(dateTime.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}` === dateString;
            }

            function timeToMinutes(time) {
                const [hours, minutes] = String(time || '00:00').split(':').map(Number);

                return (hours * 60) + minutes;
            }

            function occursOn(card, dateTime) {
                const eventDate = card.dataset.eventDate;
                if (!eventDate) return false;

                const original = new Date(`${eventDate}T00:00`);
                const simulation = new Date(dateTime.getFullYear(), dateTime.getMonth(), dateTime.getDate());

                if (simulation < original) return false;

                switch (card.dataset.recurrenceType) {
                    case 'daily':
                        return true;
                    case 'weekly':
                        return simulation.getDay() === original.getDay();
                    case 'monthly':
                        return simulation.getDate() === original.getDate();
                    case 'yearly':
                        return simulation.getMonth() === original.getMonth()
                            && simulation.getDate() === original.getDate();
                    default:
                        return sameDate(dateTime, eventDate);
                }
            }

            function hasEnded(card, dateTime) {
                if (card.dataset.recurrenceType !== 'none') return false;

                const eventDate = card.dataset.eventDate;
                const start = timeToMinutes(card.dataset.startTime);
                const end = timeToMinutes(card.dataset.endTime);
                const endDate = new Date(`${eventDate}T00:00`);
                endDate.setMinutes(end);

                if (end <= start) {
                    endDate.setDate(endDate.getDate() + 1);
                }

                return dateTime > endDate;
            }

            function isActive(card, dateTime) {
                if (!dateTime || !occursOn(card, dateTime)) return false;

                const current = (dateTime.getHours() * 60) + dateTime.getMinutes();
                const start = timeToMinutes(card.dataset.startTime);
                const end = timeToMinutes(card.dataset.endTime);

                if (end <= start) {
                    return current >= start || current <= end;
                }

                return current >= start && current <= end;
            }

            function formatDisplayDate(dateTime) {
                const day = String(dateTime.getDate()).padStart(2, '0');
                const month = String(dateTime.getMonth() + 1).padStart(2, '0');
                const year = dateTime.getFullYear();
                const hours = String(dateTime.getHours()).padStart(2, '0');
                const minutes = String(dateTime.getMinutes()).padStart(2, '0');

                return `${day}-${month}-${year} ${hours}:${minutes}`;
            }

            function occurrenceFor(card, dateTime) {
                if (!dateTime || !card.dataset.eventDate) return null;

                const original = new Date(`${card.dataset.eventDate}T00:00`);
                const start = timeToMinutes(card.dataset.startTime);
                const end = timeToMinutes(card.dataset.endTime);
                let candidate = new Date(dateTime.getFullYear(), dateTime.getMonth(), dateTime.getDate());

                if (candidate < original) {
                    candidate = original;
                }

                for (let daysChecked = 0; daysChecked < 370; daysChecked += 1) {
                    if (occursOn(card, candidate)) {
                        const startsAt = new Date(candidate);
                        startsAt.setMinutes(start);

                        const endsAt = new Date(candidate);
                        endsAt.setMinutes(end);

                        if (end <= start) {
                            endsAt.setDate(endsAt.getDate() + 1);
                        }

                        if (dateTime <= endsAt) {
                            return { startsAt, endsAt };
                        }
                    }

                    candidate.setDate(candidate.getDate() + 1);
                }

                return null;
            }

            function statusFor(card) {
                if (!simulationDateTime) return 'planned';
                if (isActive(card, simulationDateTime)) return 'active';
                if (hasEnded(card, simulationDateTime)) return 'past';

                return 'planned';
            }

            function updateBadge(card, status) {
                const badge = card.querySelector('[data-event-status-badge]');
                if (!badge) return;

                badge.textContent = status;
                badge.className = [
                    'mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize',
                    status === 'active'
                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                        : status === 'past'
                            ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                ].join(' ');
            }

            document.querySelectorAll('[data-event-card]').forEach((card) => {
                const status = statusFor(card);
                sections[status]?.appendChild(card);
                updateBadge(card, status);
                updateRescheduleButton(card, status);
            });

            const upcomingList = document.querySelector('[data-upcoming-events-list]');
            const upcomingCards = Array.from(document.querySelectorAll('[data-upcoming-event-card]'));
            const upcomingEmptyMessage = document.querySelector('[data-upcoming-empty-message]');

            if (!simulationDateTime) {
                upcomingCards.forEach((card) => card.classList.add('hidden'));
                upcomingEmptyMessage?.classList.remove('hidden');
            } else {
                const upcomingItems = upcomingCards
                    .map((card) => ({
                        card,
                        status: statusFor(card),
                        occurrence: occurrenceFor(card, simulationDateTime),
                    }))
                    .filter((item) => item.occurrence !== null)
                    .sort((a, b) => {
                        if (a.status === 'active' && b.status !== 'active') return -1;
                        if (a.status !== 'active' && b.status === 'active') return 1;
                        return a.occurrence.startsAt - b.occurrence.startsAt;
                    });

                upcomingCards.forEach((card) => card.classList.add('hidden'));
                upcomingEmptyMessage?.classList.toggle('hidden', upcomingItems.length > 0);

                upcomingItems
                    .slice(0, 3)
                    .forEach((item) => {
                        const dateLine = item.card.querySelector('[data-upcoming-date-line]');
                        if (dateLine) {
                            dateLine.textContent = `${formatDisplayDate(item.occurrence.startsAt)} - ${String(item.occurrence.endsAt.getHours()).padStart(2, '0')}:${String(item.occurrence.endsAt.getMinutes()).padStart(2, '0')}`;
                        }

                        item.card.classList.remove('hidden');
                        updateBadge(item.card, item.status);
                        updateRescheduleButton(item.card, item.status);
                        upcomingList?.appendChild(item.card);
                    });
            }

            Object.entries(sections).forEach(([status, section]) => {
                if (!section) return;

                const count = section.querySelectorAll('[data-event-card]').length;
                const countElement = document.querySelector(`[data-event-count="${status}"]`);
                countElement.textContent = count;

                const emptyMessage = emptyMessages[status];
                if (emptyMessage) {
                    emptyMessage.classList.toggle('hidden', count > 0);
                }
            });
            function applyEventFilters() {
                const name = (document.getElementById("event-search")?.value || "")
                    .toLowerCase()
                    .trim();

                const date = document.getElementById("event-date-search")?.value || "";

                const type = (document.getElementById("event-type-search")?.value || "")
                    .toLowerCase()
                    .trim();

                document.querySelectorAll("[data-event-card]").forEach(card => {
                    const cardName = (card.dataset.eventName || "").toLowerCase();
                    const cardType = (card.dataset.eventType || "").toLowerCase();
                    const cardDate = card.dataset.eventDate || "";

                    const matchesName = !name || cardName.includes(name);
                    const matchesDate = !date || cardDate === date;
                    const matchesType = !type || cardType.includes(type);

                    const shouldShow = matchesName && matchesDate && matchesType;

                    card.classList.toggle("hidden", !shouldShow);
                });
            }
            function bindEventSearch() {
                const nameInput = document.getElementById("event-search");
                const dateInput = document.getElementById("event-date-search");
                const typeInput = document.getElementById("event-type-search");

                const handler = () => applyEventFilters();

                nameInput?.addEventListener("input", handler);
                dateInput?.addEventListener("input", handler);
                typeInput?.addEventListener("input", handler);
            }
            

            document.querySelectorAll('[data-reschedule-btn]').forEach(btn => {
                btn.addEventListener('click', () => {
                    eventIdInput.value = btn.dataset.eventId;
                    nameLabel.textContent = btn.dataset.eventName;

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            cancelBtn?.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const eventId = eventIdInput.value;

                const res = await fetch(`/events/${eventId}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        event_date: dateInput.value
                    })
                });

                if (!res.ok) return;

                const data = await res.json();
                const newEvent = data.event;

                // create a lightweight new card dataset object
                const cards = document.querySelectorAll('[data-event-card]');

                // optionally reload page if you want simplest correctness:
                // window.location.reload();

                // OR just re-run classification logic by injecting a new card clone:
                const originalCard = document.querySelector(`[data-event-id="${eventId}"]`);

                if (originalCard) {
                    const clone = originalCard.cloneNode(true);

                    clone.dataset.eventDate = newEvent.event_date;
                    clone.dataset.startTime = newEvent.start_time.slice(0,5);
                    clone.dataset.endTime = newEvent.end_time.slice(0,5);

                    clone.classList.remove('hidden');

                    document.querySelector('[data-event-list="planned"]').appendChild(clone);

                    // re-run your existing logic
                    const status = statusFor(clone);
                    updateBadge(clone, status);
                    sections[status]?.appendChild(clone);
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
            function updateRescheduleButton(card, status) {
                const btn = card.querySelector('[data-reschedule-btn]');
                if (!btn) return;

                if (status === 'past') {
                    btn.classList.remove('hidden');
                } else {
                    btn.classList.add('hidden');
                }
            }
            bindEventSearch();
        });
    </script>
</x-app-layout>
