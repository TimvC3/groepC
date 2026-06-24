<x-app-layout>
    <div class="min-h-screen bg-gray-100 py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Create New Function
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Add a new function to the zoning designation library.
                </p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form method="POST" action="{{ route('admin.functions.store') }}" class="space-y-6">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Example: Police Station"
                            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                        >

                        @error('name')
                            <p class="mt-2 text-sm text-red-600 [.colorblind-mode_&]:text-orange-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Icon --}}
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700">
                            Icon
                        </label>

                        <div class="mt-2 flex items-center gap-3">
                            <input
                                type="text"
                                id="icon"
                                name="icon"
                                value="{{ old('icon') }}"
                                required
                                placeholder="Choose an icon"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                            >

                            <button
                                type="button"
                                id="openIconPicker"
                                class="rounded-lg bg-gray-100 px-4 py-2 text-xl shadow-sm ring-1 ring-gray-300 transition hover:bg-gray-200"
                                aria-label="Open icon picker"
                            >
                                😀
                            </button>
                        </div>

                        @error('icon')
                            <p class="mt-2 text-sm text-red-600 [.colorblind-mode_&]:text-orange-700">{{ $message }}</p>
                        @enderror

                        <div class="mt-3 flex items-center gap-2 text-sm text-gray-600">
                            <span>Selected icon:</span>
                            <span id="iconPreview" class="text-3xl">
                                {{ old('icon') }}
                            </span>
                        </div>

                        <div
                            id="iconPicker"
                            class="mt-4 hidden rounded-xl border border-gray-200 bg-gray-50 p-4 shadow-sm"
                        >
                            <p class="mb-3 text-sm font-medium text-gray-700">
                                Choose an icon
                            </p>

                            <div class="grid grid-cols-6 gap-3 sm:grid-cols-8">
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🏠</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🏢</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🏫</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🏥</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🚓</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🚒</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🌳</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🌲</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🛒</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🏭</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">🚉</button>
                                <button type="button" class="icon-option rounded-lg bg-white p-3 text-2xl shadow-sm ring-1 ring-gray-200 transition hover:bg-indigo-50 hover:ring-indigo-400">⚡</button>
                            </div>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">
                            Category
                        </label>

                        <select
                            name="category"
                            id="category"
                            required
                            class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">Select a category</option>
                            <option value="building" @selected(old('category') === 'building')>
                                Security
                            </option>
                            <option value="green" @selected(old('category') === 'green')>
                                Recreation
                            </option>
                            <option value="public_service" @selected(old('category') === 'public_service')>
                                Environmental Quality
                            </option>
                            <option value="emergency" @selected(old('category') === 'emergency')>
                                Infrastructure
                            </option>
                            <option value="mobility" @selected(old('category') === 'mobility')>
                                Mobility
                            </option>
                        </select>

                        @error('category')
                            <p class="mt-2 text-sm text-red-600 [.colorblind-mode_&]:text-orange-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                        <a
                            href="{{ route('admin.functions.index') }}"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Save function
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>