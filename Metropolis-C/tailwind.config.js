import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            screens: {
                xs: '380px',
            },
        },
    },

    safelist: [
        'bg-amber-600',
        'hover:bg-amber-700',
        'bg-green-600',
        'hover:bg-green-700',
        'bg-indigo-600',
        'hover:bg-indigo-700',
        'text-white',
        'bg-green-100',
        'text-green-700',
        'dark:bg-green-900/30',
        'dark:text-green-300',
        'bg-blue-100',
        'text-blue-700',
        'dark:bg-blue-900/30',
        'dark:text-blue-300',
        'bg-gray-100',
        'text-gray-700',
        'dark:bg-gray-700',
        'dark:text-gray-300',
        'bg-red-100',
        'text-red-600',
        'dark:bg-red-900/40',
        'dark:text-red-300',
    ],

    plugins: [forms],
};
