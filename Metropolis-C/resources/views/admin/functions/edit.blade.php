<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">

                <h1 class="text-2xl font-bold mb-6">
                    Edit Function
                </h1>

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-100 px-4 py-3 text-red-800">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.functions.update', $function) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium">
                            Name
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $function->name) }}"
                            class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"
                            required
                        >
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium">
                            Slug
                        </label>
                        <input
                            id="slug"
                            name="slug"
                            type="text"
                            value="{{ old('slug', $function->slug) }}"
                            class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"
                            required
                        >
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium">
                            Category
                        </label>
                        <input
                            id="category"
                            name="category"
                            type="text"
                            value="{{ old('category', $function->category) }}"
                            class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"
                            required
                        >
                    </div>

                    <div>
                        <label for="icon" class="block text-sm font-medium">
                            Icon
                        </label>
                        <input
                            id="icon"
                            name="icon"
                            type="text"
                            value="{{ old('icon', $function->icon) }}"
                            class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"
                            required
                        >

                        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <h2 class="mb-3 text-lg font-semibold">
                                Live preview
                            </h2>

                            <div class="flex items-center gap-4 rounded-md bg-white p-4 shadow-sm">
                                <div id="preview-icon" class="text-4xl">
                                    {{ $function->icon }}
                                </div>

                                <div>
                                    <p id="preview-name" class="font-bold text-gray-900">
                                        {{ $function->name }}
                                    </p>

                                    <p id="preview-slug" class="text-sm text-gray-500">
                                        {{ $function->slug }}
                                    </p>

                                    <p id="preview-category" class="text-sm text-gray-600">
                                        {{ $function->category }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('admin.functions.index') }}"
                           class="rounded-md border px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                            Back
                        </a>

                        <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-black hover:bg-indigo-700">
                            Save changes
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</x-app-layout>

