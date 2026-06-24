<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Existing Functions
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            Overview of all functions used in the Metropolis simulation.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Icon
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Name
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Category
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Last updated
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($functions as $function)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 [.colorblind-mode_&]:hover:bg-white [.colorblind-mode_&]:hover:ring-2 [.colorblind-mode_&]:hover:ring-sky-950">
                                    <td class="px-4 py-3 text-2xl">
                                        {{ $function->icon }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $function->name }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $function->category }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $function->updated_at?->format('M j, Y') ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.functions.edit', $function) }}" class="font-medium text-indigo-600 hover:text-indigo-900 [.colorblind-mode_&]:font-bold [.colorblind-mode_&]:text-sky-950 [.colorblind-mode_&]:underline">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No functions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>