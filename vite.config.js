import { defineConfig } from 'vite'
import laravel, { refreshPaths } from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // theme.css رو از اینجا حذف کن
            ],
            refresh: [
                ...refreshPaths,
                'app/Filament/**',
                'app/Providers/Filament/**',
            ],
        }),
    ],
})