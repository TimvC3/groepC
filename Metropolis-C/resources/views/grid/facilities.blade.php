<x-app-layout>
    @php
        $isEditing = isset($editingFacility) && $editingFacility;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ __('Facilities') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Manage facilities and their impact scores.') }}
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

            @if (auth()->user()?->role === 'admin')
                <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_24rem]">
                    <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Existing Facilities') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ __('These facilities are available in the simulation and scoring matrix.') }}
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            {{ __('Icon') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            {{ __('Name') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            {{ __('Category') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            {{ __('Slug') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            {{ __('Last Updated') }}
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse ($facilities as $facility)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 text-2xl">
                                                {{ $facility->icon }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                                {{ $facility->name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                                {{ $facility->category->name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $facility->slug }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $facility->updated_at?->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium">
                                                <a
                                                    href="{{ route('facilities.edit', $facility) }}"
                                                    class="text-indigo-600 transition hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    {{ __('Edit') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
                                                {{ __('No facilities found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="{{ $isEditing ? 'edit' : 'create' }}" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $isEditing ? __('Edit Facility') : __('Create Facility') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ $isEditing ? __('Update facility details and category scores.') : __('Add a facility and set its initial category scores.') }}
                        </p>

                        <form
                            method="POST"
                            action="{{ $isEditing ? route('facilities.update', $editingFacility) : route('facilities.store') }}"
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
                                    value="{{ old('name', $editingFacility?->name) }}"
                                    required
                                    placeholder="Example: Police Station"
                                    class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ __('Category') }}
                                </label>
                                <select
                                    id="category_id"
                                    name="category_id"
                                    required
                                    class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="">{{ __('Select a category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('category_id', $editingFacility?->category_id) === $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ __('Icon') }}
                                </label>
                                <input
                                    id="icon"
                                    name="icon"
                                    type="text"
                                    value="{{ old('icon', $editingFacility?->icon) }}"
                                    placeholder="Example: hospital"
                                    class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                                @error('icon')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <p class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $isEditing ? __('Scores') : __('Initial Scores') }}
                                </p>
                                <div class="mt-2 grid grid-cols-2 gap-3">
                                    @foreach ($categories as $category)
                                        @php
                                            $scoreValue = $editingFacility?->scores->firstWhere('category_id', $category->id)?->score ?? 0;
                                        @endphp
                                        <label class="block">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $category->name }}</span>
                                            <input
                                                name="scores[{{ $category->id }}]"
                                                type="number"
                                                min="-5"
                                                max="5"
                                                value="{{ old('scores.'.$category->id, $scoreValue) }}"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                            >
                                        </label>
                                    @endforeach
                                </div>
                                @error('scores.*')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ $isEditing ? __('Update Facility') : __('Save Facility') }}
                            </button>

                            @if ($isEditing)
                                <a
                                    href="{{ route('facilities') }}"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                >
                                    {{ __('Cancel Edit') }}
                                </a>
                            @endif
                        </form>
                    </section>
                </div>
            @endif

            @if (auth()->user()?->role === 'policy_maker')
                <section class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Function Conditions') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Define which functions must or must not be placed directly next to each function.') }}
                    </p>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-2">
                    @foreach ($facilities as $facility)
                        <article class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $facility->icon }}</span>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $facility->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $facility->category->name }}</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3">
                                @forelse ($facility->conditions as $condition)
                                    <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-900/50">
                                        <form
                                            method="POST"
                                            action="{{ route('facilities.conditions.update', [$facility, $condition]) }}"
                                            class="grid gap-2 sm:grid-cols-[1fr_1fr_auto]"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <select
                                                name="condition_type"
                                                aria-label="Condition type for {{ $facility->name }}"
                                                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                                            >
                                                <option value="required_neighbour" @selected($condition->condition_type === 'required_neighbour')>
                                                    Requires neighbour
                                                </option>
                                                <option value="forbidden_neighbour" @selected($condition->condition_type === 'forbidden_neighbour')>
                                                    Forbids neighbour
                                                </option>
                                            </select>

                                            <select
                                                name="neighbour_facility_id"
                                                aria-label="Neighbour function for {{ $facility->name }}"
                                                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                                            >
                                                @foreach ($facilities->where('id', '!=', $facility->id) as $neighbourFacility)
                                                    <option
                                                        value="{{ $neighbourFacility->id }}"
                                                        @selected($condition->neighbour_facility_id === $neighbourFacility->id)
                                                    >
                                                        {{ $neighbourFacility->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button
                                                type="submit"
                                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                                            >
                                                Update
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('facilities.conditions.destroy', [$facility, $condition]) }}"
                                            class="mt-2 text-right"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400"
                                            >
                                                Delete condition
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No conditions configured.</p>
                                @endforelse
                            </div>

                            @if ($facilities->count() > 1)
                                <form
                                    method="POST"
                                    action="{{ route('facilities.conditions.store', $facility) }}"
                                    class="mt-4 grid gap-2 border-t border-gray-200 pt-4 sm:grid-cols-[1fr_1fr_auto] dark:border-gray-700"
                                >
                                    @csrf

                                    <select
                                        name="condition_type"
                                        aria-label="New condition type for {{ $facility->name }}"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                                    >
                                        <option value="required_neighbour">Requires neighbour</option>
                                        <option value="forbidden_neighbour">Forbids neighbour</option>
                                    </select>

                                    <select
                                        name="neighbour_facility_id"
                                        aria-label="New neighbour function for {{ $facility->name }}"
                                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                                    >
                                        @foreach ($facilities->where('id', '!=', $facility->id) as $neighbourFacility)
                                            <option value="{{ $neighbourFacility->id }}">
                                                {{ $neighbourFacility->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button
                                        type="submit"
                                        class="rounded-md border border-indigo-600 px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400"
                                    >
                                        Add
                                    </button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>

                @if ($errors->has('condition_type') || $errors->has('neighbour_facility_id'))
                    <div class="border-t border-red-200 bg-red-50 px-6 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                        {{ $errors->first('condition_type') ?: $errors->first('neighbour_facility_id') }}
                    </div>
                @endif
                </section>
            @endif

            <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Facility Score Matrix') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        @if (auth()->user()?->role === 'policy_maker')
                            {{ __('Facility scores are read-only for policy makers.') }}
                        @else
                            {{ __('Click a score to edit its value from -5 to 5.') }}
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-48">
                                    {{ __('Facility') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-36">
                                    {{ __('Category') }}
                                </th>
                                @foreach ($categories as $category)
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ $category->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @php $currentCategory = null; @endphp

                            @foreach ($facilities as $facility)
                                @if ($currentCategory !== $facility->category->name)
                                    @php $currentCategory = $facility->category->name; @endphp
                                    <tr class="bg-indigo-50 dark:bg-indigo-900/20">
                                        <td colspan="{{ $categories->count() + 2 }}"
                                            class="px-6 py-2 text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                                            {{ $currentCategory }}
                                        </td>
                                    </tr>
                                @endif

                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">{{ $facility->icon }}</span>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $facility->name }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $facility->category->name }}
                                    </td>

                                    @foreach ($categories as $category)
                                        @php
                                            $facilityScore = $facility->scores->firstWhere('category_id', $category->id);
                                            $score = $facilityScore?->score;
                                        @endphp
                                        <td class="px-4 py-4 text-center">
                                            @if (! is_null($score))
                                                <span
                                                    @if (auth()->user()?->role !== 'policy_maker')
                                                        data-score-id="{{ $facilityScore->id }}"
                                                        data-score="{{ $score }}"
                                                        title="Click to edit"
                                                    @endif
                                                    @class([
                                                        'inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold select-none transition',
                                                        'cursor-pointer hover:ring-2 hover:ring-indigo-300' => auth()->user()?->role !== 'policy_maker',
                                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $score > 0,
                                                        'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300' => $score < 0,
                                                        'text-gray-500 dark:bg-gray-700 dark:text-gray-400' => $score === 0,
                                                    ])>
                                                    {{ $score > 0 ? '+'.$score : $score }}
                                                </span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        @if (auth()->user()?->role !== 'policy_maker')
            @vite('resources/js/facilities/scoreEditor.js')
        @endif
    @endpush
</x-app-layout>
