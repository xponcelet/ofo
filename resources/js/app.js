import './bootstrap';
import '../css/app.css';

import mapboxgl from 'mapbox-gl';
import '@mapbox/mapbox-gl-geocoder/dist/mapbox-gl-geocoder.css';
import 'mapbox-gl/dist/mapbox-gl.css';

window.mapboxgl = mapboxgl;

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import RootLayout from '@/Layouts/RootLayout.vue';

import axios from 'axios'; // ✅ pour gérer les requêtes et CSRF

// 🔑 Configuration CSRF pour Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('❌ CSRF token not found in app.blade.php');
}

const appName = import.meta.env.VITE_APP_NAME || 'OFO';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')).then((page) => {
            page.default.layout ??= RootLayout;
            return page;
        }),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
        showSpinner: false,
        includeCSS: true,
        delay: 200,
    },
});
