import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true, //  force un refresh complet quand nécessaire
        }),
        vue(),
    ],
    server: {
        host: '127.0.0.1', //  évite les soucis IPv6 (::1)
        port: 5173,
        hmr: {
            protocol: 'ws', //  websocket forcé (évite certaines erreurs Windows)
            host: '127.0.0.1',
        },
    },
    optimizeDeps: {
        force: true, //  reconstruit les deps au démarrage
    },
    build: {
        sourcemap: true, //  utile pour debugger
    },
})
