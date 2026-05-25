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
                    {{ __('Manage city events and their category impact scores.') }}
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

            <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_24rem]">
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Existing Events') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ __('These events can temporarily influence the simulation.') }}
                        </p>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($events as $event)
                            <article class="p-6 transition hover:bg-gray-50 dark:hover:bg-gray-700/40">
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
                                                {{ __('Date') }}
                                            </p>
                                            <p class="mt-1 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ $event->event_date ? \Illuminate\Support\Carbon::parse($event->event_date)->format('d-m-Y') : '-' }}
                                            </p>
                                        </div>

                                        <div class="min-w-[9rem]">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Time') }}
                                            </p>
                                            <p class="mt-1 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-200">
                                                {{ $event->start_time ? \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') : '-' }}
                                                -
                                                {{ $event->end_time ? \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') : '-' }}
                                            </p>
                                        </div>

                                        <div class="min-w-[8rem]">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Recurrence') }}
                                            </p>

                                            <span
                                                @class([
                                                    'mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $event->recurrence_type !== \App\Enums\RecurrenceType::None,
                                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $event->recurrence_type === \App\Enums\RecurrenceType::None,
                                                ])
                                            >
                                                {{ __($event->recurrence_type->label()) }}
                                            </span>
                                        </div>

                                        <div class="ml-auto min-w-[4rem] text-right">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                {{ __('Edit') }}
                                            </p>

                                            <a
                                                href="{{ route('events.edit', $event) }}"
                                                class="mt-1 inline-flex text-sm font-medium text-indigo-600 transition hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                {{ __('Edit') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {{-- Impact scores --}}
                                <div class="mt-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('Impact Scores') }}
                                    </p>

                                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
                                        @foreach ($categories as $category)
                                            @php
                                                $eventCategory = $event->categories->firstWhere('id', $category->id);
                                                $score = $eventCategory?->pivot?->score;
                                            @endphp

                                            <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                                    {{ $category->name }}
                                                </span>

                                                @if (! is_null($score))
                                                    <span
                                                        @class([
                                                            'inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-sm font-bold',
                                                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $score > 0,
                                                            'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $score < 0,
                                                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => $score === 0,
                                                        ])
                                                    >
                                                        {{ $score > 0 ? '+'.$score : $score }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </article>
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
                        {{ $isEditing ? __('Update event details and category scores.') : __('Add an event and set its category impact scores.') }}
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
                                {{ __('Category Scores') }}
                            </p>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ __('Every event affects all categories. Set the impact score from -5 to 5.') }}
                            </p>

                            <div class="mt-2 grid grid-cols-2 gap-3">
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
</x-app-layout>