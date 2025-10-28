import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {

    darkMode: 'class', // desactivation du mode sombre

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                // 💙 Palette principale
                primary: {
                    light: '#9498F2',   // lavande douce
                    DEFAULT: '#115D8C', // bleu moyen
                    foreground: '#FFFFFF',
                },

                // 💎 Accent cyan pour CTA
                accent: {
                    DEFAULT: '#05DBF2',
                    foreground: '#0B3B59',
                },

                // ☁️ Arrière-plan et surfaces
                background: '#FFFFFF',
                surface: '#FFFFFF',

                // 🖋️ Texte et variantes
                'on-surface': '#0B3B59',
                'on-surface-variant': '#115D8C',

                // 🔲 Bordures et outline
                outline: '#D3D6DA',
            },

            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            boxShadow: {
                soft: '0 4px 12px rgba(11, 59, 89, 0.08)',
            },

            borderRadius: {
                xl: '1rem',
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms, typography],
}
