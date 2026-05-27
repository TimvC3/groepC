<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[18rem_minmax(25rem,1fr)_20rem] xl:gap-8">
                <aside class="order-1 xl:order-none xl:row-span-2">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-2xl font-bold">Zoning Library</h3>

                        <div class="mt-4">
                            <label for="designation-search" class="sr-only">Zoek faciliteiten</label>
                            <input
                                id="designation-search"
                                type="search"
                                placeholder="Search..."
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm dark:bg-gray-900"
                            >
                        </div>

                        <div class="mt-6 flex gap-3 overflow-x-auto pb-2 xl:block xl:max-h-[65vh] xl:space-y-6 xl:overflow-y-auto xl:pr-1">
                            @foreach ($groupedFacilities as $category => $facilities)
                                <section class="category-section min-w-56 flex-none xl:min-w-0">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $category }}</h4>

                                    <div class="mt-3 flex gap-3 xl:grid xl:grid-cols-1">
                                        @foreach ($facilities as $facility)
                                            <div
                                                class="zoning-item w-44 flex-none cursor-grab rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-400 active:cursor-grabbing dark:border-gray-700 dark:bg-gray-900 xl:w-full"
                                                draggable="true"
                                                data-id="{{ $facility->id }}"
                                                data-name="{{ $facility->name }}"
                                                data-category="{{ $facility->category->name }}"
                                                data-icon="{{ $facility->icon }}"
                                            >
                                                <div class="pointer-events-none flex items-start gap-3">
                                                    <div class="text-3xl">{{ $facility->icon }}</div>
                                                    <div>
                                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $facility->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $facility->category->name }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </aside>

                <section class="order-2 xl:col-start-2 xl:row-start-1">
                    <div class="mb-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="mb-4 text-xl font-bold">Simulation Settings</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium" for="simulation-date">Start Date</label>
                                <input
                                    id="simulation-date"
                                    type="date"
                                    class="w-full rounded-md border border-gray-300 px-4 py-2 dark:bg-gray-900"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium" for="simulation-time">Start Time</label>
                                <input
                                    id="simulation-time"
                                    type="time"
                                    class="w-full rounded-md border border-gray-300 px-4 py-2 dark:bg-gray-900"
                                >
                            </div>

                            <div class="flex items-end">
                                <button
                                    id="start-simulation"
                                    type="button"
                                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                                >
                                    Start Simulation
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                            <button class="sim-speed rounded-md border px-3 py-1" data-speed="-10" type="button">&lt;&lt;</button>
                            <button class="sim-speed rounded-md border px-3 py-1" data-speed="-2" type="button">&lt;</button>
                            <button class="sim-speed rounded-md border px-3 py-1" data-speed="0" type="button">Pause</button>
                            <button class="sim-speed rounded-md border px-3 py-1" data-speed="2" type="button">&gt;</button>
                            <button class="sim-speed rounded-md border px-3 py-1" data-speed="10" type="button">&gt;&gt;</button>
                        </div>

                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                            Current Simulation Time:
                            <span id="simulation-datetime" class="font-bold">Not started</span>
                        </div>

                        <div id="day-night-status" class="mt-2 text-sm font-semibold text-indigo-600">
                            Day Mode
                        </div>
                    </div>

                    <div class="flex flex-col items-center rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <div class="mb-6 flex w-full max-w-md flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-2xl font-bold">City Grid</h3>

                            <div class="flex gap-2">
                                <button
                                    id="export-pdf"
                                    type="button"
                                    class="rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    Export PDF
                                </button>

                                <button
                                    id="clear-grid"
                                    type="button"
                                    class="rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 justify-center gap-1 rounded-3xl border-4 border-gray-100 p-2 dark:border-gray-700 dark:bg-gray-900/50">
                            @for ($i = 1; $i <= 12; $i++)
                                <div
                                    class="grid-cell flex h-16 w-16 flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-1 text-center transition-all dark:border-gray-600 sm:h-24 sm:w-24"
                                    data-index="{{ $i }}"
                                >
                                    <span class="font-mono text-xs text-gray-400">{{ $i }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </section>

                <aside class="order-3 xl:col-start-3 xl:row-start-1">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-2xl font-bold">Upcoming Events</h3>

                        <div class="mt-4 flex gap-3 overflow-x-auto pb-2 xl:grid xl:max-h-[38vh] xl:overflow-y-auto xl:pr-1">
                            @forelse ($upcomingEvents as $upcomingEvent)
                                @php
                                    $event = $upcomingEvent->event;
                                    $occurrence = $upcomingEvent->occurrence;
                                    $status = $event->statusAt();
                                    $affectedCategory = $event->affectedCategory();
                                @endphp

                                <div
                                    class="event-item w-64 flex-none cursor-grab rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-400 active:cursor-grabbing dark:border-gray-700 dark:bg-gray-900 xl:w-full"
                                    draggable="true"
                                    data-id="{{ $event->id }}"
                                    data-name="{{ $event->name }}"
                                    data-status="{{ $status }}"
                                    data-date="{{ $occurrence['starts_at']->format('d-m-Y') }}"
                                    data-start-time="{{ $occurrence['starts_at']->format('H:i') }}"
                                    data-end-time="{{ $occurrence['ends_at']->format('H:i') }}"
                                    data-category-id="{{ $affectedCategory?->id }}"
                                    data-category="{{ $affectedCategory?->name }}"
                                    data-score="{{ $event->impactScore() }}"
                                >
                                    <div class="pointer-events-none flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $event->name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $occurrence['starts_at']->format('d-m-Y') }}
                                                {{ $occurrence['starts_at']->format('H:i') }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $affectedCategory?->name ?? __('No category') }}
                                                {{ $event->impactScore() > 0 ? '+'.$event->impactScore() : $event->impactScore() }}
                                            </div>
                                        </div>

                                        <span
                                            @class([
                                                'rounded-full px-2 py-1 text-xs font-semibold capitalize',
                                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $status === 'planned',
                                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $status === 'active',
                                                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $status === 'past',
                                            ])
                                        >
                                            {{ __($status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No upcoming events found.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>

                <section class="order-4 xl:col-start-2 xl:row-start-2">
                    <div class="flex flex-col rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <div class="mb-6 flex w-full items-center justify-between">
                            <h3 class="text-2xl font-bold">Effect View</h3>
                            <div class="text-right">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Quality Of Life Score</div>
                                <div id="effect-total-score" class="text-3xl font-bold text-gray-900 dark:text-gray-100">0</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ($effectData['categories'] as $category)
                                <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3 dark:border-gray-700">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $category['name'] }}</span>
                                    <span
                                        id="effect-category-score-{{ $category['id'] }}"
                                        class="text-sm font-bold text-gray-500 dark:text-gray-400"
                                    >
                                        0
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <p id="effect-empty-state" class="mt-4 text-sm text-gray-500" aria-live="polite">
                            Drag facilities in the grid to see the score change.
                        </p>
                        <div id="effect-status" class="sr-only" aria-live="polite" aria-atomic="true"></div>
                    </div>
                </section>

                <section class="order-5 xl:col-start-3 xl:row-start-2">
                    <div class="flex flex-col rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <div class="mb-6 flex w-full items-center justify-between">
                            <h3 class="text-2xl font-bold">Event Effects</h3>
                            <div class="text-right">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Total</div>
                                <div id="event-effect-total-score" class="text-3xl font-bold text-gray-900 dark:text-gray-100">0</div>
                            </div>
                        </div>

                        <div id="event-effect-list" class="space-y-3"></div>

                        <p id="event-effect-empty-state" class="text-sm text-gray-500" aria-live="polite">
                            Drag upcoming events in the grid to see their separate category influence.
                        </p>

                        <div class="mt-5 grid grid-cols-1 gap-3">
                            @foreach ($effectData['categories'] as $category)
                                <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3 dark:border-gray-700">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $category['name'] }}</span>
                                    <span
                                        id="event-effect-category-score-{{ $category['id'] }}"
                                        class="text-sm font-bold text-gray-500 dark:text-gray-400"
                                    >
                                        0
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        window.gridEffectData = {{ Illuminate\Support\Js::from($effectData) }};
        window.gridEventEffectData = {{ Illuminate\Support\Js::from($eventEffectData) }};
    </script>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

        <script
            src="https://cdn.jsdelivr.net/npm/@dragdroptouch/drag-drop-touch@latest/dist/drag-drop-touch.esm.min.js?autoload"
            type="module">
        </script>
        @vite('resources/js/grid/effectGrid.js')
    @endpush
</x-app-layout>
