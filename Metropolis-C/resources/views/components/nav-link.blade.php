@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-sm px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-4 dark:focus-visible:ring-offset-gray-800 transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-sm px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-4 dark:focus-visible:ring-offset-gray-800 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
