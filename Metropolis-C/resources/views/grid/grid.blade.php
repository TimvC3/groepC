<x-app-layout>
    @php
        $zoningDesignations = \App\Models\ZoningDesignation::orderBy('category')->orderBy('name')->get();
        $groupedDesignations = $zoningDesignations->groupBy('category');
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                <aside class="xl:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-2xl font-bold">Zoning Library</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Drag the items to the grid.</p>

                        <div class="mt-4">
                            <input id="designation-search" type="search" placeholder="Search..."
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm dark:bg-gray-900">
                        </div>

                        <div class="mt-6 space-y-6 max-h-[65vh] overflow-y-auto pr-1">
                            @foreach ($groupedDesignations as $category => $designations)
                                <section class="category-section">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $category }}</h4>
                                    <div class="mt-3 grid grid-cols-1 gap-3">
                                        @foreach ($designations as $designation)
                                            <div 
                                                class="zoning-item cursor-grab active:cursor-grabbing w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4 shadow-sm transition hover:border-indigo-400"
                                                draggable="true"
                                                data-id="{{ $designation->id }}"
                                                data-name="{{ $designation->name }}"
                                                data-category="{{ $designation->category }}"
                                                data-icon="{{ $designation->icon }}"
                                            >
                                                <div class="flex items-start gap-3 pointer-events-none">
                                                    <div class="text-3xl">{{ $designation->icon }}</div>
                                                    <div>
                                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $designation->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $designation->category }}</div>
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
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">City Grid</h3>
                            <button id="clear-grid" class="px-4 py-2 text-sm border rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Clear</button>
                        </div>

                        <div class="grid grid-cols-4 gap-4 justify-items-center">
                            @for ($i = 1; $i <= 12; $i++)
                                <div 
                                    class="grid-cell w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center text-center p-1 transition-all"
                                    data-index="{{ $i }}"
                                >
                                    <span class="text-gray-400 text-xs font-mono">{{ $i }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let draggedData = null;

            // 1. Drag logic for Library Items
            document.querySelectorAll('.zoning-item').forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    draggedData = {
                        id: item.dataset.id,
                        name: item.dataset.name,
                        icon: item.dataset.icon
                    };
                    e.dataTransfer.setData('text/plain', item.dataset.id); 
                });
            });

            // 2. Drop logic for Grid Cells
            document.querySelectorAll('.grid-cell').forEach(cell => {
                cell.addEventListener('dragover', (e) => {
                    e.preventDefault(); // Required to allow a drop
                    cell.classList.add('bg-indigo-50', 'border-indigo-400');
                });

                cell.addEventListener('dragleave', () => {
                    cell.classList.remove('bg-indigo-50', 'border-indigo-400');
                });

                cell.addEventListener('drop', (e) => {
                    e.preventDefault();
                    cell.classList.remove('bg-indigo-50', 'border-indigo-400');

                    if (draggedData) {
                        // Update the cell content
                        cell.innerHTML = `
                            <div class="flex flex-col items-center">
                                <div class="text-2xl">${draggedData.icon}</div>
                                <div class="mt-1 text-[10px] font-bold leading-tight">${draggedData.name}</div>
                            </div>
                        `;
                        cell.classList.remove('border-dashed');
                        cell.classList.add('border-solid', 'bg-blue-50', 'dark:bg-blue-900/20');
                    }
                });
            });

            // 3. Search functionality
            const searchInput = document.getElementById('designation-search');
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.zoning-item').forEach(item => {
                    const text = (item.dataset.name + item.dataset.category).toLowerCase();
                    item.style.display = text.includes(query) ? 'block' : 'none';
                });
            });

            // 4. Clear Grid
            document.getElementById('clear-grid').addEventListener('click', () => {
                document.querySelectorAll('.grid-cell').forEach(cell => {
                    const index = cell.dataset.index;
                    cell.innerHTML = `<span class="text-gray-400 text-xs font-mono">${index}</span>`;
                    cell.classList.remove('border-solid', 'bg-blue-50', 'dark:bg-blue-900/20');
                    cell.classList.add('border-dashed');
                });
            });
        });
    </script>
</x-app-layout>