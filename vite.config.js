import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/css/style.css', // Make sure this matches your CSS file
            'resources/js/app.js',
        ]),
    ],
});
