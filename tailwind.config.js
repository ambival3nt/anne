const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/gils-html/test.html',
    ],

    theme: {
        // borderColor: ({ theme }) => ({
        //     ...theme('colors'),
        //     DEFAULT: theme('colors.transparent', 'currentColor'),
        // }),
        // ringColor: ({ theme }) => ({
        //     DEFAULT: theme('colors.ltblue.45', 'hsl(210, 35%, 60%)'),
        //     ...theme('colors'),
        // }),
        extend: {
            colors: {
                midnight: {
                    dk: 'hsl(229, 70%, 3%)',
                    DEFAULT: 'hsl(229, 70%, 6%)',
                    lt: 'hsl(229, 70%, 9%)',
                },
                midnight2: {
                    950: '#02040D',
                    900: '#040816',
                    800: '#060C1E',
                    700: '#081026',
                    600: '#0A152F',
                    500: '#0C1B37',
                    400: '#0E213F',
                    300: '#102747',
                    200: '#122D4E',
                    100: '#153456',
                },
                dkblue: {
                    950: '#02040D',
                    900: '#040916',
                    800: '#060E1E',
                    700: '#081526',
                    600: '#0A1D2F',
                    500: '#0C2537',
                    400: '#0E2F3F',
                    300: '#103A47',
                    200: '#12454E',
                    100: '#155256',
                },
                dusk: {
                    DEFAULT: 'hsl(229, 50%, 6%)',
                    950: '#080A17',
                    900: '#0B0F1E',
                    800: '#0F1324',
                    700: '#13182A',
                    600: '#181E30',
                    500: '#1D2335',
                    400: '#22293A',
                    300: '#282F3E',
                    200: '#2E3442',
                    100: '#353A46',
                },
                ltblue: {
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
                    txt: 'hsl(210, 30%, 70%)',
                },
                ltblack: 'hsl(0, 0%, 3%)',
                bittersweet: '#EB5E55',
                raspberry: '#D81E5B',
            },
            fontFamily: {
                sans: ['Nunito Sans', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                glow: '0px 0px 5px 0px',
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
