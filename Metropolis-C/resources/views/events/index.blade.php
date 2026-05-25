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
                    {{ __('Manage city events, statuses, and selected category impact.') }}
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
                        {{ __('The next three planned or active events are shown here.') }}
                    </p>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-3">
                    @forelse ($upcomingEvents as $upcomingEvent)
                        @php
                            $event = $upcomingEvent['event'];
                            $occurrence = $upcomingEvent['occurrence'];
                            $status = $event->statusAt();
                            $affectedCategory = $event->affectedCategory();
                        @endphp

                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $event->name }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $occurrence['starts_at']->format('d-m-Y') }}
                                        {{ $occurrence['starts_at']->format('H:i') }}
                                        -
                                        {{ $occurrence['ends_at']->format('H:i') }}
                                    </p>
                                </div>

                                <span
                                    @class([
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $status === 'planned',
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $status === 'active',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $status === 'past',
                                    ])
                                >
                                    {{ __($status) }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">
                                    {{ $affectedCategory?->name ?? __('No category') }}
                                </span>
                                <span
                                    @class([
                                        'font-bold',
                                        'text-green-700 dark:text-green-300' => $event->impactScore() > 0,
                                        'text-red-600 dark:text-red-300' => $event->impactScore() < 0,
                                        'text-gray-500 dark:text-gray-400' => $event->impactScore() === 0,
                                    ])
                                >
                                    {{ $event->impactScore() > 0 ? '+'.$event->impactScore() : $event->impactScore() }}
                                </span>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('No upcoming events found.') }}
                        </p>
                    @endforelse
                </div>
            </section>

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
                            @php
                                $status = $event->statusAt();
                                $affectedCategory = $event->affectedCategory();
                            @endphp
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
                                                {{ __('Status') }}
                                            </p>

                                            <span
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
                                        {{ __('Affected Category') }}
                                    </p>

                                    <div class="mt-3 flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                            {{ $affectedCategory?->name ?? __('No category selected') }}
                                        </span>

                                        <span
                                            @class([
                                                'inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-sm font-bold',
                                                'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $event->impactScore() > 0,
                                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $event->impactScore() < 0,
                                                'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => $event->impactScore() === 0,
                                            ])
                                        >
                                            {{ $event->impactScore() > 0 ? '+'.$event->impactScore() : $event->impactScore() }}
                                        </span>
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
                        {{ $isEditing ? __('Update event details and affected category.') : __('Add an event and set its affected category.') }}
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
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Affected Category') }}
                            </label>

                            @php
                                $selectedCategoryId = old('category_id', $editingEvent?->affectedCategory()?->id);
                            @endphp

                            <select
                                id="category_id"
                                name="category_id"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">{{ __('Select a category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="score" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Impact Score') }}
                            </label>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ __('This event only adjusts the selected category. Use a score from -5 to 5.') }}
                            </p>

                            <input
                                id="score"
                                name="score"
                                type="number"
                                min="-5"
                                max="5"
                                value="{{ old('score', $editingEvent?->impactScore() ?? 0) }}"
                                required
                                class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >

                            @error('score')
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
