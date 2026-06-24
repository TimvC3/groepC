<x-app-layout>
    @php
        $userRole = auth()->user()?->role;


    $canApproveFunctions = in_array($userRole, [
        'admin',
        'policy_maker',
        'municipal_policy_maker',
    ], true);

    $events = $eventEffectData['events'] ?? [];
@endphp

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3 xl:gap-8">

            <aside class="xl:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-2xl font-bold">Function Library</h3>

                    <div class="mt-4">
                        <label for="designation-search" class="sr-only">Zoek functies</label>
                        <input
                            id="designation-search"
                            type="search"
                            placeholder="Search..."
                            class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm dark:bg-gray-900"
                        >
                    </div>

                    <div class="mt-6 flex gap-3 overflow-x-auto pb-2 xl:block xl:max-h-[45vh] xl:space-y-6 xl:overflow-y-auto xl:pr-1">
                        @foreach ($groupedFunctions as $category => $functions)
                            <section class="category-section min-w-56 flex-none xl:min-w-0">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    {{ $category }}
                                </h4>

                                <div class="mt-3 flex gap-3 xl:grid xl:grid-cols-1">
                                    @foreach ($functions as $function)
                                        <div
                                            class="zoning-item w-44 flex-none cursor-grab active:cursor-grabbing rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4 shadow-sm transition hover:border-indigo-400 xl:w-full"
                                            draggable="true"
                                            data-id="{{ $function->id }}"
                                            data-name="{{ $function->name }}"
                                            data-category="{{ $function->category->name }}"
                                            data-icon="{{ $function->icon }}"
                                        >
                                            <div class="flex items-start gap-3 pointer-events-none">
                                                <div class="text-3xl">
                                                    {{ $function->icon }}
                                                </div>

                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $function->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $function->category->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>

                @if (count($events) > 0)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-2xl font-bold">Events</h3>

                        <div class="mt-4 space-y-3">
                            @foreach ($events as $event)
                                @php
                                    $eventId = data_get($event, 'id');
                                    $eventName = data_get($event, 'name');
                                    $eventDate = data_get($event, 'eventDate') ?? data_get($event, 'date');
                                    $startTime = data_get($event, 'startTime');
                                    $endTime = data_get($event, 'endTime');
                                    $status = data_get($event, 'status', 'planned');
                                    $impacts = collect(data_get($event, 'impacts', []));
                                    $firstImpact = $impacts->first();
                                    $eventScore = $impacts->sum(fn ($impact) => (int) data_get($impact, 'score', 0));
                                @endphp

                                <div
                                    class="event-item cursor-grab active:cursor-grabbing rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm transition hover:border-amber-400 dark:border-amber-900/40 dark:bg-amber-900/20 [.colorblind-mode_&]:border-2 [.colorblind-mode_&]:border-orange-950 [.colorblind-mode_&]:bg-white [.colorblind-mode_&]:hover:border-orange-950"
                                    draggable="true"
                                    data-id="{{ $eventId }}"
                                    data-name="{{ $eventName }}"
                                    data-category-id="{{ data_get($firstImpact, 'category_id') }}"
                                    data-category="{{ data_get($firstImpact, 'category_name') }}"
                                    data-score="{{ $eventScore }}"
                                    data-status="{{ $status }}"
                                    data-date="{{ $eventDate }}"
                                    data-start-time="{{ $startTime }}"
                                    data-end-time="{{ $endTime }}"
                                >
                                    <div class="pointer-events-none">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $eventName }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $eventDate }} {{ $startTime }} - {{ $endTime }}
                                        </div>
                                        <div class="mt-2 text-xs font-bold text-amber-700 dark:text-amber-300 [.colorblind-mode_&]:text-orange-950">                                            Score: {{ $eventScore > 0 ? '+' . $eventScore : $eventScore }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            <section class="xl:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col items-center">
                    <div class="flex justify-between items-center mb-6 w-full max-w-md">
                        <div>
                            <h3 class="text-2xl font-bold">City Grid</h3>

                            @if ($canApproveFunctions)
                                <p class="mt-1 text-xs font-bold text-green-700 dark:text-green-300 [.colorblind-mode_&]:text-sky-950">
                                    You are allowed to approve destinations.
                                </p>
                            @else
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    You can view the grid, but you cannot approve functions.
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <button
                                id="export-pdf"
                                type="button"
                                class="px-4 py-2 text-sm border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                Export PDF
                            </button>

                            <button
                                id="clear-grid"
                                type="button"
                                class="px-4 py-2 text-sm border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-1 justify-center border-4 border-gray-100 dark:border-gray-700 p-2 rounded-3xl dark:bg-gray-900/50">
                        @for ($i = 1; $i <= 12; $i++)
                            <div
                                class="grid-cell h-16 w-16 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center text-center p-1 transition-all sm:h-24 sm:w-24 [.colorblind-mode_&]:border-sky-950 [.colorblind-mode_&]:bg-white"
                                data-index="{{ $i }}"
                            >
                                <span class="text-gray-400 text-xs font-mono">
                                    {{ $i }}
                                </span>
                            </div>
                        @endfor
                    </div>

                    <div
                        id="condition-status"
                        class="mt-4 w-full max-w-md rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300 [.colorblind-mode_&]:border-2 [.colorblind-mode_&]:border-sky-950 [.colorblind-mode_&]:bg-white [.colorblind-mode_&]:text-sky-950 [.colorblind-mode_&]:font-bold"
                        aria-live="polite"
                        aria-atomic="true"
                    >
                        Function conditions are active. Place functions to evaluate neighbour rules and automatic Level 4 adjacency modifiers.
                    </div>
                                    </div>

                <section class="mt-6">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-2xl font-bold">Simulation</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Select a date and time to start the city simulation.
                                </p>
                            </div>

                            <button
                                id="start-simulation"
                                type="button"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                Start Simulation
                            </button>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="simulation-date" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Date
                                </label>
                                <input
                                    id="simulation-date"
                                    type="date"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:bg-gray-900"
                                >
                            </div>

                            <div>
                                <label for="simulation-time" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Time
                                </label>
                                <input
                                    id="simulation-time"
                                    type="time"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:bg-gray-900"
                                >
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" data-speed="0" class="sim-speed rounded-md border px-3 py-1 text-sm">Pause</button>
                            <button type="button" data-speed="1" class="sim-speed rounded-md border px-3 py-1 text-sm bg-indigo-600 text-white [.colorblind-mode_&]:bg-sky-700">1x</button>
                            <button type="button" data-speed="5" class="sim-speed rounded-md border px-3 py-1 text-sm">5x</button>
                            <button type="button" data-speed="10" class="sim-speed rounded-md border px-3 py-1 text-sm">10x</button>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                                <div class="text-xs font-bold uppercase text-gray-500">Current time</div>
                                <div id="simulation-datetime" class="mt-1 text-sm font-semibold">Not started</div>
                            </div>

                            <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                                <div class="text-xs font-bold uppercase text-gray-500">Day/Night</div>
                                <div id="day-night-status" class="mt-1 text-sm font-semibold">No simulation time selected</div>
                            </div>

                            <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                                <div class="text-xs font-bold uppercase text-gray-500">Active event</div>
                                <div id="simulation-event-status" class="mt-1 text-sm font-semibold">No dragged event active at simulation time</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-6">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col">
                        <div class="flex justify-between items-center mb-6 w-full">
                            <h3 class="text-2xl font-bold">Effect View</h3>

                            <div class="text-right">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    Total score
                                </div>
                                <div
                                    id="effect-total-score"
                                    class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    0
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($effectData['categories'] as $category)
                                <div class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 px-4 py-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $category['name'] }}
                                    </span>

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
                            Drag functions in the grid to see the score change.
                        </p>

                        <div
                            id="effect-status"
                            class="sr-only"
                            aria-live="polite"
                            aria-atomic="true"
                        ></div>
                    </div>
                </section>

                <section class="mt-6">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col">
                        <div class="flex justify-between items-center mb-6 w-full">
                            <h3 class="text-2xl font-bold">Event Effects</h3>

                            <div class="text-right">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    Event score
                                </div>
                                <div
                                    id="event-effect-total-score"
                                    class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    0
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($effectData['categories'] as $category)
                                <div class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 px-4 py-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $category['name'] }}
                                    </span>

                                    <span
                                        id="event-effect-category-score-{{ $category['id'] }}"
                                        class="text-sm font-bold text-gray-500 dark:text-gray-400"
                                    >
                                        0
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <p id="event-effect-empty-state" class="mt-4 text-sm text-gray-500" aria-live="polite">
                            Drag events in the grid to see event effects.
                        </p>

                        <div id="event-effect-list" class="mt-4 space-y-3"></div>
                    </div>
                </section>

                @if (count($events) > 0)
                    <section class="mt-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h3 class="text-2xl font-bold">Upcoming Events</h3>

                            <p data-upcoming-empty-message class="mt-3 text-sm text-gray-500">
                                Select a simulation date and time to see upcoming events.
                            </p>

                            <div data-upcoming-events-list class="mt-4 space-y-3">
                                @foreach ($events as $event)
                                    @php
                                        $eventId = data_get($event, 'id');
                                        $eventName = data_get($event, 'name');
                                        $eventDate = data_get($event, 'eventDate') ?? data_get($event, 'date');
                                        $startTime = data_get($event, 'startTime');
                                        $endTime = data_get($event, 'endTime');
                                    @endphp

                                    <div
                                        data-upcoming-event-card
                                        data-id="{{ $eventId }}"
                                        data-date="{{ $eventDate }}"
                                        data-start-time="{{ $startTime }}"
                                        data-end-time="{{ $endTime }}"
                                        class="hidden rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900"
                                    >
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $eventName }}
                                                </div>
                                                <div data-upcoming-date-line class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $eventDate }} {{ $startTime }} - {{ $endTime }}
                                                </div>
                                            </div>

                                            <span
                                                data-upcoming-status-badge
                                                class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                            >
                                                planned
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
            </section>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.gridEffectData = {{ Illuminate\Support\Js::from($effectData) }};
        window.gridConditionData = {{ Illuminate\Support\Js::from($conditionData) }};
        window.gridFacilityData = {{ Illuminate\Support\Js::from($facilityData) }};
        window.gridEventEffectData = {{ Illuminate\Support\Js::from($eventEffectData ?? ['events' => []]) }};
        window.gridRestrictions = {{ Illuminate\Support\Js::from($restrictions) }};
        window.approvedGridCells = {{ Illuminate\Support\Js::from($approvedGridCells ?? []) }};
        window.approveCellUrl = @json(route('grid.approve-cell'));
        window.gridPermissions = {
            canApproveFunctions: @json($canApproveFunctions),
        };
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/@dragdroptouch/drag-drop-touch@latest/dist/drag-drop-touch.esm.min.js?autoload"
        type="module">
    </script>

    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>

    @vite('resources/js/grid/effectGrid.js')
@endpush

</x-app-layout>
