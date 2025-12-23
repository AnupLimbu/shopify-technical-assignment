import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';


export default defineConfig({
    plugins: [
        // Laravel Vite plugin
        laravel({
            input: ['resources/js/app.jsx', 'resources/css/app.css'],
            refresh: true,
        }),
        // React plugin
        react(),
    ],
    server: {
        port: 3001, // Change port to 3001 or any port you prefer
    },
});
