<x-app-layout>
    @php
        $isEditing = isset($editingEvent) && $editingEvent;
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

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ __('Name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ __('Date') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ __('Start Time') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ __('Recurring') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($events as $event)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $event->name }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $event->event_date?->format('M j, Y') }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            <span
                                                @class([
                                                    'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $event->is_recurring,
                                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => ! $event->is_recurring,
                                                ])
                                            >
                                                {{ $event->is_recurring ? __('Yes') : __('No') }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <a
                                                href="{{ route('events.edit', $event) }}"
                                                class="text-indigo-600 transition hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                {{ __('Edit') }}
                                            </a>
                                        </td>
                                    </tr>

                                    <tr class="bg-gray-50/70 dark:bg-gray-900/30">
                                        <td colspan="5" class="px-6 py-4">
                                            <div class="mb-2 flex items-center justify-between">
                                                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                    {{ __('Category Impact Scores') }}
                                                </p>
                                            </div>

                                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
                                                @foreach ($categories as $category)
                                                    @php
                                                        $eventCategory = $event->categories->firstWhere('id', $category->id);
                                                        $score = (int) ($eventCategory?->pivot?->score ?? 0);
                                                    @endphp

                                                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                                                {{ $category->name }}
                                                            </span>

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
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">
                                            {{ __('No events found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                            <label for="event_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ __('Date') }}
                            </label>

                            <input
                                id="event_date"
                                name="event_date"
                                type="date"
                                value="{{ old('event_date', $editingEvent?->event_date?->format('Y-m-d')) }}"
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
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                <input
                                    type="checkbox"
                                    name="is_recurring"
                                    value="1"
                                    @checked(old('is_recurring', $editingEvent?->is_recurring))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >

                                <span>{{ __('Recurring event') }}</span>
                            </label>

                            @error('is_recurring')
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

                            <div class="mt-3 space-y-3">
                                @foreach ($categories as $category)
                                    @php
                                        $existingCategory = $editingEvent?->categories->firstWhere('id', $category->id);

                                        $scoreValue = old(
                                            'scores.'.$category->id,
                                            $existingCategory?->pivot?->score ?? 0
                                        );
                                    @endphp

                                    <label class="block rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/40">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            {{ $category->name }}
                                        </span>

                                        <input
                                            name="scores[{{ $category->id }}]"
                                            type="number"
                                            min="-5"
                                            max="5"
                                            value="{{ $scoreValue }}"
                                            required
                                            class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
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