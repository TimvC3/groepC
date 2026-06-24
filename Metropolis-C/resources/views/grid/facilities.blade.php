<x-app-layout>
    @php
        $isEditing = isset($editingFacility) && $editingFacility;
        $isAdmin = auth()->user()?->role === 'admin';
        $isLibraryManager = auth()->user()?->role === 'library_manager';
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ __('Functions') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $isLibraryManager
                        ? __('Manage required and forbidden neighbour conditions for functions.')
                        : __('Manage functions and their impact scores.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300 [.colorblind-mode_&]:border-sky-300 [.colorblind-mode_&]:bg-sky-50 [.colorblind-mode_&]:text-sky-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300 [.colorblind-mode_&]:border-orange-300 [.colorblind-mode_&]:bg-orange-50 [.colorblind-mode_&]:text-orange-800">
                    {{ session('error') }}
                </div>
            @endif

            @if (in_array(auth()->user()?->role, ['admin', 'library_manager'], true))
                <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_24rem]">
                    <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Existing Functions') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ __('These functions are available in the simulation and scoring matrix.') }}
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
                                            {{ __('Conditions') }}
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
                                    @forelse ($functions as $function)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 text-2xl">
                                                {{ $function->icon }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                                {{ $function->name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                                {{ $function->category->name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $function->slug }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $conditions->where('facility_id', $function->id)->count() }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $function->updated_at?->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium">
                                                <a
                                                    href="{{ route('functions.edit', $function) }}"
                                                    class="text-indigo-600 transition hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    {{ __('Edit') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-6 text-center text-sm text-gray-500">
                                                {{ __('No functions found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="{{ $isEditing ? 'edit' : 'create' }}" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $isEditing ? __('Edit Function') : __('Create Function') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ $isEditing ? __('Update function details and category scores.') : __('Add a function and set its initial category scores.') }}
                        </p>

                        <form
                            method="POST"
                            action="{{ $isEditing ? route('functions.update', $editingFacility) : route('functions.store') }}"
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
                                {{ $isEditing ? __('Update Function') : __('Save Function') }}
                            </button>

                            @if ($isEditing)
                                <a
                                    href="{{ route('functions.index') }}"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                                >
                                    {{ __('Cancel Edit') }}
                                </a>
                            @endif
                        </form>
                    </section>
                </div>

            @endif

            @if ($isLibraryManager)
                <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Function Conditions') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Required neighbours must be present. Forbidden neighbours cannot be placed directly next to each other. Level 4 adjacency modifiers are applied automatically and are not configurable here.') }}
                        </p>
                    </div>

                    <div class="p-6 space-y-6">
                        <form method="POST" action="{{ route('functions.conditions.store') }}" class="grid gap-4 lg:grid-cols-4 lg:items-end">
                            @csrf

                            <div>
                                <label for="condition_facility" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ __('Function') }}
                                </label>
                                <select id="condition_facility" name="facility_id" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="">{{ __('Select function') }}</option>
                                    @foreach ($functions as $function)
                                        <option value="{{ $function->id }}" @selected((int) old('facility_id') === $function->id)>
                                            {{ $function->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="condition_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ __('Condition') }}
                                </label>
                                <select id="condition_type" name="type" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="required_neighbour" @selected(old('type') === 'required_neighbour')>{{ __('Required neighbour') }}</option>
                                    <option value="forbidden_neighbour" @selected(old('type') === 'forbidden_neighbour')>{{ __('Forbidden neighbour') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="condition_related_facility" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ __('Neighbouring function') }}
                                </label>
                                <select id="condition_related_facility" name="related_facility_id" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="">{{ __('Select function') }}</option>
                                    @foreach ($functions as $function)
                                        <option value="{{ $function->id }}" @selected((int) old('related_facility_id') === $function->id)>
                                            {{ $function->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                {{ __('Add Condition') }}
                            </button>
                        </form>

                        @if ($errors->any())
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
                        @endif

                        <div class="space-y-3">
                            @php
                                $editableConditions = $conditions->whereIn('condition_type', ['required_neighbour', 'forbidden_neighbour']);
                            @endphp

                            @forelse ($editableConditions as $condition)
                                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                    <form method="POST" action="{{ route('functions.conditions.update', $condition) }}" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                                        @csrf
                                        @method('PATCH')

                                        <select name="facility_id" required class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                            @foreach ($functions as $function)
                                                <option value="{{ $function->id }}" @selected($condition->facility_id === $function->id)>{{ $function->name }}</option>
                                            @endforeach
                                        </select>

                                        <select name="type" required class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                            <option value="required_neighbour" @selected($condition->condition_type === 'required_neighbour')>{{ __('Required neighbour') }}</option>
                                            <option value="forbidden_neighbour" @selected($condition->condition_type === 'forbidden_neighbour')>{{ __('Forbidden neighbour') }}</option>
                                        </select>

                                        <select name="related_facility_id" required class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                            @foreach ($functions as $function)
                                                <option value="{{ $function->id }}" @selected($condition->neighbour_facility_id === $function->id)>{{ $function->name }}</option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                            {{ __('Save') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('functions.conditions.destroy', $condition) }}" class="mt-2 text-right" onsubmit="return confirm('Delete this condition?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No conditions configured yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endif

            <section class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Function Score Matrix') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                @if (in_array(auth()->user()?->role, ['policy_maker', 'library_manager'], true))
                                    {{ __('Function scores are read-only for this role.') }}
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-48">
                                    {{ __('Function') }}
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

                            @foreach ($functions as $function)
                                @if ($currentCategory !== $function->category->name)
                                    @php $currentCategory = $function->category->name; @endphp
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
                                            <span class="text-2xl">{{ $function->icon }}</span>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $function->name }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $function->category->name }}
                                    </td>

                                    @foreach ($categories as $category)
                                        @php
                                            $functionScore = $function->scores->firstWhere('category_id', $category->id);
                                            $score = $functionScore?->score;
                                        @endphp
                                        <td class="px-4 py-4 text-center">
                                            @if (! is_null($score))
                                                <span
                                                    data-score="{{ $score }}"
                                                    @if (! in_array(auth()->user()?->role, ['policy_maker', 'library_manager'], true))
                                                        data-score-id="{{ $functionScore->id }}"
                                                        data-score="{{ $score }}"
                                                        title="Click to edit"
                                                    @endif
                                                    @class([
                                                        'inline-flex items-center justify-center gap-1 min-w-11 h-9 rounded-full px-2 text-sm font-bold select-none transition',
                                                        'cursor-pointer hover:ring-2 hover:ring-indigo-300' => ! in_array(auth()->user()?->role, ['policy_maker', 'library_manager'], true),
                                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 [.colorblind-mode_&]:bg-sky-100 [.colorblind-mode_&]:text-sky-800' => $score > 0,
                                                        'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300 [.colorblind-mode_&]:bg-orange-100 [.colorblind-mode_&]:text-orange-800' => $score < 0,
                                                        'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' => $score === 0,
                                                    ])>
                                                    <span aria-hidden="true">
                                                        @if ($score > 0)
                                                            ▲
                                                        @elseif ($score < 0)
                                                            ▼
                                                        @else
                                                            •
                                                        @endif
                                                    </span>
                                                    <span>{{ $score > 0 ? '+'.$score : $score }}</span>
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
        @vite('resources/js/facilities/scoreEditor.js')
    @endpush
</x-app-layout>
