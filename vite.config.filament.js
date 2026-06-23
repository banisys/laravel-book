import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    server: {
        port: 5174,
    },
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/filament/admin/theme.css'],
            buildDirectory: 'build/filament',
            refresh: false,
        }),
    ],
})