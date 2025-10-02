import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.js",
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#6750A4', // violet Material 3
                    foreground: '#FFFFFF',
                },
                secondary: {
                    DEFAULT: '#625B71',
                    foreground: '#FFFFFF',
                },
                background: '#FFFBFE',
                surface: '#FFFBFE',
                'surface-variant': '#E7E0EC',
                'on-surface': '#1C1B1F',
                'on-surface-variant': '#49454F',
                outline: '#79747E',
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui'],
            },
        },
    },

    plugins: [forms, typography],
};
