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
    define: {
        'process.env.NODE_ENV': '"development"',  // Forces development mode
    },
    server: {
        cors: {
            https: true,
            origin: '*', // Allow all origins
            // OR specify your ngrok domain:
            // origin: 'https://premilitary-kristie-bioecologic.ngrok-free.dev'
        }
    }
});
