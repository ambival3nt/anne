const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                'midnight': 'hsl(228, 70%, 6%)',
                'ltblue': {
                    15: 'hsl(210, 15%, 60%)',
                    20: 'hsl(210, 20%, 60%)',
                    25: 'hsl(210, 25%, 60%)',
                    30: 'hsl(210, 30%, 60%)',
                    DEFAULT: 'hsl(210, 35%, 60%)',
                    40: 'hsl(210, 40%, 60%)',
                    45: 'hsl(210, 45%, 60%)',
                    50: 'hsl(210, 50%, 60%)',
                    55: 'hsl(210, 55%, 60%)',
                    60: 'hsl(210, 60%, 60%)',
                    65: 'hsl(210, 65%, 60%)',
                    70: 'hsl(210, 70%, 60%)',
                    75: 'hsl(210, 75%, 60%)',
                    80: 'hsl(210, 80%, 60%)',
                    85: 'hsl(210, 85%, 60%)',
                },
                'ltblack': 'hsl(0, 0%, 3%)',
            },
            fontFamily: {
                sans: ['Nunito Sans', 'Albert Sans', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require("daisyui")],
};
