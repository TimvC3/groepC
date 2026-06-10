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
                    <div class="flex flex-col items-center rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6">
                        <div class="mb-6 flex w-full max-w-md flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-2xl font-bold">City Grid</h3>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    id="export-pdf"
                                    type="button"
                                    class="flex-1 rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 sm:flex-none"
                                >
                                    Export PDF
                                </button>

                                <button
                                    id="clear-grid"
                                    type="button"
                                    class="flex-1 rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 sm:flex-none"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 justify-center gap-1 rounded-2xl border-4 border-gray-100 p-2 dark:border-gray-700 dark:bg-gray-900/50 sm:rounded-3xl">
                            @for ($i = 1; $i <= 12; $i++)
                                <div
                                    class="grid-cell flex h-16 w-16 flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-1 text-center transition-all dark:border-gray-600 xs:h-20 xs:w-20 sm:h-24 sm:w-24 sm:rounded-xl"
                                    data-index="{{ $i }}"
                                >
                                    <span class="font-mono text-xs text-gray-400">{{ $i }}</span>
                                </div>
                            @endfor
                        </div>

                        <div
                            id="condition-status"
                            class="mt-4 w-full max-w-md rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300"
                            aria-live="polite"
                            aria-atomic="true"
                        >
                            Function conditions are active. Place a function to evaluate its neighbour rules.
                        </div>

                        <div class="mt-6 w-full max-w-3xl rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
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
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row lg:w-72 lg:flex-col">
                                    <button
                                        id="start-simulation"
                                        type="button"
                                        class="w-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                                    >
                                        Start Simulation
                                    </button>
                                    <span class="hidden bg-amber-600 hover:bg-amber-700 bg-green-600 hover:bg-green-700"></span>

                                    <div class="flex items-center justify-between gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Speed</span>
                                        <div class="flex gap-1">
                                            <button class="sim-speed rounded-md border px-2 py-1 text-xs" data-speed="0.5" type="button">0.5x</button>
                                            <button class="sim-speed rounded-md border bg-indigo-600 px-2 py-1 text-xs text-white" data-speed="1" type="button">1x</button>
                                            <button class="sim-speed rounded-md border px-2 py-1 text-xs" data-speed="2" type="button">2x</button>
                                            <button class="sim-speed rounded-md border px-2 py-1 text-xs" data-speed="5" type="button">5x</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-2 text-sm text-gray-600 dark:text-gray-300 md:grid-cols-3">
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-500">Current Time</span>
                                    <span id="simulation-datetime" class="font-semibold text-gray-900 dark:text-gray-100">Not started</span>
                                </div>

                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-500">Mode</span>
                                    <span id="day-night-status" class="font-semibold text-indigo-600">Day Mode</span>
                                </div>

                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-500">Active Events</span>
                                    <span id="simulation-event-status" class="font-semibold text-gray-900 dark:text-gray-100">No dragged event active at simulation time</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="order-3 xl:col-start-3 xl:row-start-1">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="text-2xl font-bold">Upcoming Events</h3>

                        <div class="mt-4 flex gap-3 overflow-x-auto pb-2 xl:grid xl:max-h-[38vh] xl:overflow-y-auto xl:pr-1" data-upcoming-events-list>
                            @forelse ($upcomingEvents as $upcomingEvent)
                                @php
                                    $event = $upcomingEvent->event;
                                    $occurrence = $upcomingEvent->occurrence;
                                    $impactScores = $event->impactScores();
                                @endphp

                                <div
                                    class="event-item hidden w-64 flex-none cursor-grab rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm transition hover:border-indigo-400 active:cursor-grabbing dark:border-gray-700 dark:bg-gray-900 xl:w-full"
                                    draggable="true"
                                    data-upcoming-event-card
                                    data-id="{{ $event->id }}"
                                    data-name="{{ $event->name }}"
                                    data-event-date="{{ $event->event_date?->format('Y-m-d') }}"
                                    data-recurrence-type="{{ $event->recurrence_type->value }}"
                                    data-date="{{ $occurrence['starts_at']->format('d-m-Y') }}"
                                    data-start-time="{{ $occurrence['starts_at']->format('H:i') }}"
                                    data-end-time="{{ $occurrence['ends_at']->format('H:i') }}"
                                    data-score="{{ $event->impactScore() }}"
                                >
                                    <div class="pointer-events-none flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $event->name }}</div>
                                            <div class="text-sm text-gray-500" data-upcoming-date-line>
                                                {{ $occurrence['starts_at']->format('d-m-Y') }}
                                                {{ $occurrence['starts_at']->format('H:i') }}
                                            </div>
                                            <div class="mt-1 space-y-1 text-xs text-gray-500">
                                                @foreach ($impactScores as $impact)
                                                    <div>
                                                        {{ $impact['category_name'] }}
                                                        {{ $impact['score'] > 0 ? '+'.$impact['score'] : $impact['score'] }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <span
                                            class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold capitalize text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                            data-upcoming-status-badge
                                        >
                                            planned
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No upcoming events found.</p>
                            @endforelse

                            <p class="w-64 flex-none text-sm text-gray-500 xl:w-full" data-upcoming-empty-message>
                                Select and start a simulation date and time to see upcoming events.
                            </p>
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

                        <div class="mt-5 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                Bonuses and Penalties
                            </h4>
                            <p id="facility-adjustment-empty" class="mt-2 text-sm text-gray-500">
                                No proximity or duplicate adjustments are active.
                            </p>
                            <div id="facility-adjustment-list" class="mt-3 space-y-2"></div>
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
        window.gridConditionData = {{ Illuminate\Support\Js::from($conditionData) }};
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
