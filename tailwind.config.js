import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', 'sans-serif'],
            },
            colors: {
                // Primary green color
                "primary": "#13ec80",
                "primary-dark": "#0fd671",

                // Light mode - White background with green accents
                "background-light": "#f6f8f7",
                "card-light": "#ffffffff",
                "border-light": "#dbe6e0",
                "text-main-light": "#111814",
                "text-sub-light": "#5a6b5e",

                // Dark mode - Keep as is (green-dark theme)
                "background-dark": "#102219",
                "card-dark": "#1C2E24",
                "border-dark": "#2a3c32",
                "text-main-dark": "#ffffff",
                "text-sub-dark": "#9ab0a5",
            },
        },
    },

    plugins: [forms],
};
