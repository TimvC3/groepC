<x-app-layout>
    @php
        $zoningDesignations = [
            ['id' => 'police-station', 'name' => 'Police Station', 'category' => 'Safety', 'icon' => '🚓'],
            ['id' => 'fire-station', 'name' => 'Fire Station', 'category' => 'Safety', 'icon' => '🚒'],
            ['id' => 'park', 'name' => 'Park', 'category' => 'Recreation', 'icon' => '🌳'],
            ['id' => 'cinema', 'name' => 'Cinema', 'category' => 'Recreation', 'icon' => '🎬'],
            ['id' => 'sports-park', 'name' => 'Sports Park', 'category' => 'Recreation', 'icon' => '⚽'],
            ['id' => 'water-purification', 'name' => 'Water Purification', 'category' => 'Environment', 'icon' => '💧'],
            ['id' => 'primary-school', 'name' => 'Primary School', 'category' => 'Facility', 'icon' => '🏫'],
            ['id' => 'store', 'name' => 'Store', 'category' => 'Facility', 'icon' => '🏪'],
            ['id' => 'hospital', 'name' => 'Hospital', 'category' => 'Facility', 'icon' => '🏥'],
            ['id' => 'train-station', 'name' => 'Train Station', 'category' => 'Mobility', 'icon' => '🚉'],
            ['id' => 'road', 'name' => 'Road', 'category' => 'Mobility', 'icon' => '🛣️'],
            ['id' => 'cycling-path', 'name' => 'Cycling Path', 'category' => 'Mobility', 'icon' => '🚲'],
            ['id' => 'petrol-station', 'name' => 'Petrol Station', 'category' => 'Mobility', 'icon' => '⛽'],
        ];

        $groupedDesignations = collect($zoningDesignations)->groupBy('category');
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Grid
        </h2>
    </x-slot>

    <div
        class="py-8"
        x-data="metropolisGrid()"
        x-init="init()"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                <aside class="xl:col-span-1">
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="text-2xl font-bold text-gray-900">
                            Zoning Library
                        </h3>

                        <p class="mt-2 text-sm text-gray-600">
                            Kies een functie uit de library en klik daarna op een vak in het grid.
                        </p>

                        <div class="mt-4">
                            <label for="designation-search" class="sr-only">
                                Search zoning designations
                            </label>

                            <input
                                id="designation-search"
                                type="search"
                                x-model="search"
                                placeholder="Search..."
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-900 bg-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="mt-6 space-y-6 overflow-y-auto pr-1">
                            @foreach ($groupedDesignations as $category => $designations)
                                <section>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                        {{ $category }}
                                    </h4>

                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-3">
                                        @foreach ($designations as $designation)
                                            <button
                                                type="button"
                                                title="{{ $designation['name'] }}"
                                                aria-label="{{ $designation['name'] }}"
                                                x-show="matchesSearch(@js($designation['name']), @js($designation['category']))"
                                                x-on:click="selectDesignation(@js($designation))"
                                                :class="selectedDesignation && selectedDesignation.id === @js($designation['id'])
                                                    ? 'border-indigo-500 ring-2 ring-indigo-300 bg-indigo-50'
                                                    : 'border-gray-200 bg-gray-50'"
                                                class="w-full rounded-lg border p-4 text-left shadow-sm transition hover:shadow-md"
                                            >
                                                <div class="flex items-start gap-3">
                                                    <div class="text-3xl leading-none">
                                                        {{ $designation['icon'] }}
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="font-semibold text-gray-900">
                                                            {{ $designation['name'] }}
                                                        </div>

                                                        <div class="mt-1 text-sm text-gray-500">
                                                            {{ $designation['category'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>

                        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="text-sm font-semibold text-gray-700">
                                Selected designation
                            </p>

                            <template x-if="selectedDesignation">
                                <div class="mt-2 flex items-center gap-3">
                                    <span class="text-2xl" x-text="selectedDesignation.icon"></span>
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="selectedDesignation.name"></p>
                                        <p class="text-sm text-gray-500" x-text="selectedDesignation.category"></p>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!selectedDesignation">
                                <p class="mt-2 text-sm text-gray-500">
                                    Nothing selected yet.
                                </p>
                            </template>
                        </div>
                    </div>
                </aside>

                <section class="xl:col-span-2">
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    City Grid
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Klik op een vak om het te selecteren. Als je een designation hebt gekozen, wordt die meteen geplaatst.
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    x-on:click="clearAssignments()"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Clear grid
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <div id="grid" class="grid grid-cols-4 gap-4">
                                <template x-for="cell in cells" :key="cell.id">
                                    <button
                                        type="button"
                                        :data-testid="'district-' + cell.id"
                                        :aria-pressed="cell.selected ? 'true' : 'false'"
                                        x-on:click="toggleDistrict(cell.id)"
                                        :class="cell.selected
                                            ? 'selected bg-blue-300 border-blue-500'
                                            : 'bg-white border-gray-300'"
                                        class="district w-24 h-24 rounded-2xl border text-2xl shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 overflow-hidden"
                                        :title="cell.designation ? cell.designation.name : 'District ' + cell.id"
                                    >
                                        <template x-if="cell.designation">
                                            <div class="flex h-full w-full flex-col items-center justify-center px-1">
                                                <div class="text-2xl leading-none" x-text="cell.designation.icon"></div>
                                                <div class="mt-1 text-[10px] font-semibold leading-tight text-center" x-text="cell.designation.name"></div>
                                            </div>
                                        </template>

                                        <template x-if="!cell.designation">
                                            <span x-text="cell.id"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <script>
        function metropolisGrid() {
            return {
                search: '',
                selectedDesignation: null,
                cells: [],

                init() {
                    this.cells = Array.from({ length: 12 }, (_, index) => ({
                        id: index + 1,
                        selected: false,
                        designation: null,
                    }));
                },

                matchesSearch(name, category) {
                    const query = this.search.trim().toLowerCase();

                    if (!query) {
                        return true;
                    }

                    return name.toLowerCase().includes(query) || category.toLowerCase().includes(query);
                },

                selectDesignation(designation) {
                    this.selectedDesignation = designation;
                },

                toggleDistrict(cellId) {
                    this.cells = this.cells.map((cell) => {
                        if (cell.id !== cellId) {
                            return cell;
                        }

                        const nextSelected = !cell.selected;

                        return {
                            ...cell,
                            selected: nextSelected,
                            designation: this.selectedDesignation ? this.selectedDesignation : cell.designation,
                        };
                    });
                },

                clearAssignments() {
                    this.cells = this.cells.map((cell) => ({
                        ...cell,
                        selected: false,
                        designation: null,
                    }));
                },
            };
        }
    </script>
</x-app-layout>