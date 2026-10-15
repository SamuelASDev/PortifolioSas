import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/public-portfolio.css', 'resources/js/app.js', 'resources/js/public-carousel.js', 'resources/js/public-preview.js', 'resources/js/share-links.js'],
            refresh: true,
        }),
    ],
});
