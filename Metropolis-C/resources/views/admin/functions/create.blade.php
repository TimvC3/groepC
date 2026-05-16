<x-app-layout>
<form method="POST" action="{{ route('admin.functions.store') }}">
    @csrf

    <div>
        <label for="name">Name</label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            value="{{ old('name') }}"
            required
        >

        @error('name')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-4">
    <label for="icon" class="block text-sm font-medium text-gray-700">
        Icon
    </label>

    <div class="flex items-center gap-2 mt-1">
            <input
                type="text"
                id="icon"
                name="icon"
                value="{{ old('icon') }}"
                required
                class="w-full rounded-md border-gray-300 shadow-sm"
                placeholder="Choose an icon"
            >

            <button
                type="button"
                id="openIconPicker"
                class="rounded-md bg-gray-200 px-3 py-2 hover:bg-gray-300"
            >
                😀
            </button>
        </div>

        @error('icon')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div
            id="iconPicker"
            class="mt-2 hidden grid grid-cols-6 gap-2 rounded-md border bg-white p-3 shadow"
        >
            <button type="button" class="icon-option text-2xl">🏠</button>
            <button type="button" class="icon-option text-2xl">🏢</button>
            <button type="button" class="icon-option text-2xl">🏫</button>
            <button type="button" class="icon-option text-2xl">🏥</button>
            <button type="button" class="icon-option text-2xl">🚓</button>
            <button type="button" class="icon-option text-2xl">🚒</button>
            <button type="button" class="icon-option text-2xl">🌳</button>
            <button type="button" class="icon-option text-2xl">🌲</button>
            <button type="button" class="icon-option text-2xl">🛒</button>
            <button type="button" class="icon-option text-2xl">🏭</button>
            <button type="button" class="icon-option text-2xl">🚉</button>
            <button type="button" class="icon-option text-2xl">⚡</button>
        </div>
    </div>

    <div>
        <label for="category">Category</label>

        <select name="category" id="category" required>
            <option value="">Select a category</option>
            <option value="building">Security</option>
            <option value="green">Recreation</option>
            <option value="public_service">Environmental Quality</option>
            <option value="emergency">Infrastructure</option>
            <option value="mobility">Mobility</option>
        </select>

        @error('category')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <button type="submit">Save function</button>
</form>
</x-app-layout>