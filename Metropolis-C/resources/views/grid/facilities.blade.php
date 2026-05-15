<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Facilities & Scores') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700">

                        {{-- Header --}}
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-48">
                                    Facility
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500 w-36">
                                    Categorie
                                </th>
                                @foreach ($categories as $category)
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ $category->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- Body --}}
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                            @php $currentCategory = null; @endphp

                            @foreach ($facilities as $facility)

                                {{-- Category group header row --}}
                                @if ($currentCategory !== $facility->category->name)
                                    @php $currentCategory = $facility->category->name; @endphp
                                    <tr class="bg-indigo-50 dark:bg-indigo-900/20">
                                        <td colspan="{{ $categories->count() + 2 }}"
                                            class="px-6 py-2 text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                                            {{ $currentCategory }}
                                        </td>
                                    </tr>
                                @endif

                                {{-- Facility row --}}
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

                                    {{-- Icon + naam --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">{{ $facility->icon }}</span>
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $facility->name }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Categorie label --}}
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $facility->category->name }}
                                    </td>

                                    {{-- Score per category --}}
                                    @foreach ($categories as $category)
                                        @php
                                            $facilityScore = $facility->scores->firstWhere('category_id', $category->id);
                                            $score = $facilityScore?->score;
                                        @endphp
                                        <td class="px-4 py-4 text-center">
                                            @if (!is_null($score))
                                                <span
                                                    data-score-id="{{ $facilityScore->id }}"
                                                    data-score="{{ $score }}"
                                                    title="Klik om aan te passen"
                                                    @class([
                                                        'inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold cursor-pointer select-none transition hover:ring-2 hover:ring-indigo-300',
                                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $score > 0,
                                                        'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300'         => $score < 0,
                                                        'text-gray-500 dark:bg-gray-700 dark:text-gray-400'                    => $score === 0,
                                                    ])>
                                                    {{ $score > 0 ? "+" . $score : $score }}
                                                </span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                    @endforeach

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/facilities/scoreEditor.js')
    @endpush

</x-app-layout>