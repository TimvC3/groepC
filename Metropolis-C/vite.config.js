import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/facilities/scoreEditor.js',
                'resources/js/grid/effectGrid.js',
            ],
            refresh: true,
        }),
    ],

    server: {
        host: 'metropolis-c.test', // Je domeinnaam
        hmr: {
            host: 'metropolis-c.test',
        },
    },
});
