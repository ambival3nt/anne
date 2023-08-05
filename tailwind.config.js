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
                midnight: {
                    DEFAULT: '#03081c',
                },
                steelgray: {
                    DEFAULT: 'hsl(240, 18%, 15%)',
                    lt: '#383856',
                    vlt: '#4E4E78',
                },
                spacecadet: {
                    DEFAULT: '#373348',
                    lt: '#514B6A',
                    vlt: '#665F86',
                },
                oslogray: {
                    vdk: 'hsl(210, 7%, 35%)',
                    dk: 'hsl(210, 7%, 45%)',
                    DEFAULT: 'hsl(210, 7%, 55%)',
                    lt: 'hsl(210, 7%, 65%)',
                    vlt: 'hsl(210, 7%, 75%)',
                },
                waterloo: {
                    xdk: 'hsl(231, 12%, 35%)',
                    vdk: 'hsl(231, 12%, 45%)',
                    dk: 'hsl(231, 12%, 55%)',
                    DEFAULT: 'hsl(231, 12%, 65%)',
                    lt: 'hsl(231, 12%, 75%)',
                    vlt: 'hsl(231, 12%, 85%)',
                },
                xanthous: {
                    vdk: '#866313',
                    dk: '#b38519',
                    DEFAULT: '#E3B23C',
                    lt: '#e9c163',
                    vlt: '#efd28f',
                    xlt: '#eee1b4'
                },
                amaranth: {
                    vdk: 'hsl(350, 70%, 30%)',
                    dk: 'hsl(350, 70%, 40%)',
                    DEFAULT: 'hsl(350, 70%, 53%)',
                    lt: 'hsl(350, 70%, 63%)',
                    vlt: 'hsl(350, 70%, 73%)',
                    xlt: 'hsl(350, 70%, 83%)'
                },
                verdigris: {
                    vdk: 'hsl(180, 63%, 25%)',
                    dk: 'hsl(180, 63%, 35%)',
                    DEFAULT: 'hsl(180, 63%, 42%)',
                    lt: 'hsl(180, 63%, 60%)',
                    vlt: 'hsl(180, 63%, 75%)',
                    xlt: 'hsl(180, 63%, 85%)',
                },
                violet1: '#15172c',
                violet2: '#1d1a35',
                violet3: '#403a52',
                ltblue: 'hsl(210, 100%, 75%)',
                medblue: 'hsl(210, 100%, 50%)',
                brightblue: 'hsl(220, 100%, 50%)',
                deepblue: 'hsl(220, 100%, 30%)',
                dimblue: {
                    DEFAULT: 'hsl(220, 43%, 60%)',
                    lt: 'hsl(220, 43%, 70%)',
                },
            },
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            backgroundImage: {
                'disdain': 'url("/resources/img/disdain.svg")',
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
