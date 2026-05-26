</h1>
<x-app-layout>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3 xl:gap-8">

                <aside class="xl:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-2xl font-bold">Zoning Library</h3>

                        <div class="mt-4">
                            <label for="designation-search" class="sr-only">Zoek faciliteiten</label>
                            <input id="designation-search" type="search" placeholder="Search..."
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm dark:bg-gray-900">
                        </div>

                        <div class="mt-6 flex gap-3 overflow-x-auto pb-2 xl:block xl:max-h-[65vh] xl:space-y-6 xl:overflow-y-auto xl:pr-1">
                            @foreach ($groupedFacilities as $category => $facilities)
                                <section class="category-section min-w-56 flex-none xl:min-w-0">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $category }}</h4>

                                    <div class="mt-3 flex gap-3 xl:grid xl:grid-cols-1">
                                        @foreach ($facilities as $facility)
                                            <div
                                                class="zoning-item w-44 flex-none cursor-grab active:cursor-grabbing rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4 shadow-sm transition hover:border-indigo-400 xl:w-full"
                                                draggable="true"
                                                data-id="{{ $facility->id }}"
                                                data-name="{{ $facility->name }}"
                                                data-category="{{ $facility->category->name }}"
                                                data-icon="{{ $facility->icon }}"
                                            >
                                                <div class="flex items-start gap-3 pointer-events-none">
                                                    <div class="text-3xl">{{ $facility->icon }}</div>

                                                    <div>
                                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                            {{ $facility->name }}
                                                        </div>

                                                        <div class="text-sm text-gray-500">
                                                            {{ $facility->category->name }}
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
                </aside>

                <section class="xl:col-span-2">

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">
                            Simulation Settings
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Start Date
                                </label>

                                <input
                                    type="date"
                                    id="simulation-date"
                                    class="w-full rounded-md border border-gray-300 px-4 py-2 dark:bg-gray-900"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Start Time
                                </label>

                                <input
                                    type="time"
                                    id="simulation-time"
                                    class="w-full rounded-md border border-gray-300 px-4 py-2 dark:bg-gray-900"
                                >
                            </div>

                            <div class="flex items-end">
                                <button
                                    id="start-simulation"
                                    type="button"
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Start Simulation
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                            Current Simulation Time:
                            <span id="simulation-datetime" class="font-bold">
                                Not started
                            </span>
                        </div>

                        <div
                            id="day-night-status"
                            class="mt-2 text-sm font-semibold text-indigo-600"
                        >
                            Day Mode
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col items-center">
                        <div class="flex justify-between items-center mb-6 w-full max-w-md">
                            <h3 class="text-2xl font-bold">
                                City Grid
                            </h3>

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
                                    class="grid-cell h-16 w-16 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center text-center p-1 transition-all sm:h-24 sm:w-24"
                                    data-index="{{ $i }}"
                                >
                                    <span class="text-gray-400 text-xs font-mono">
                                        {{ $i }}
                                    </span>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <section class="mt-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col">
                            <div class="flex justify-between items-center mb-6 w-full">
                                <h3 class="text-2xl font-bold">
                                    Effect View
                                </h3>

                                <div class="text-right">
                                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                        Totale score
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

                            <p
                                id="effect-empty-state"
                                class="mt-4 text-sm text-gray-500"
                                aria-live="polite"
                            >
                                Drag facilities in the grid to see the score change.
                            </p>

                            <div
                                id="effect-status"
                                class="sr-only"
                                aria-live="polite"
                                aria-atomic="true"
                            ></div>
                        </div>
                    </section>
                </section>
            </div>
        </div>
    </div>

    <script>
        window.gridEffectData = {{ Illuminate\Support\Js::from($effectData) }};
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