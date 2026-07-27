import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        // NOTE: intentionally NOT scanning ./storage/framework/views/*.php — after
        // `view:cache` that dir holds hundreds of compiled Filament vendor views, whose
        // utility classes would bleed into and conflict with the app's own styles.
        // All app classes live literally in the Blade source below, so this is sufficient.
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
