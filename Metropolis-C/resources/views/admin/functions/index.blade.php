<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">

                <h1 class="text-2xl font-bold mb-6">
                    Existing Functions
                </h1>

                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-100 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 dark:border-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left">Icon</th>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Slug</th>
                                <th class="px-4 py-3 text-left">Category</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($functions as $function)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-3 text-2xl">
                                        {{ $function->icon }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $function->name }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $function->slug }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $function->category }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.functions.edit', $function) }}"
                                           class="rounded-md bg-indigo-600 px-3 py-2 text-sm text-black hover:bg-indigo-700">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>