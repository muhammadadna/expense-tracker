import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', 'sans-serif'],
            },
            colors: {
                "primary": "#13ec80",
                "background-light": "#f6f8f7",
                "background-dark": "#102219",
                "card-light": "#ffffff",
                "card-dark": "#1C2E24",
                "border-light": "#dbe6e0",
                "border-dark": "#2a3c32",
                "text-main-light": "#111814",
                "text-main-dark": "#ffffff",
                "text-sub-light": "#637588",
                "text-sub-dark": "#9ca3af",
            },
        },
    },

    plugins: [forms],
};
