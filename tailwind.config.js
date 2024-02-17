const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            spacing: {
                "fluid-3xs": "clamp(0.19rem, calc(0.15rem + 0.13vw), 0.25rem)",
                "fluid-2xs": "clamp(0.38rem, calc(0.30rem + 0.25vw), 0.50rem)",
                "fluid-xs": "clamp(0.56rem, calc(0.45rem + 0.38vw), 0.75rem)",
                "fluid-s": "clamp(0.75rem, calc(0.60rem + 0.50vw), 1.00rem)",
                "fluid-m": "clamp(1.13rem, calc(0.90rem + 0.75vw), 1.50rem)",
                "fluid-l": "clamp(1.50rem, calc(1.20rem + 1.00vw), 2.00rem)",
                "fluid-xl": "clamp(2.25rem, calc(1.80rem + 1.50vw), 3.00rem)",
                "fluid-2xl": "clamp(3.00rem, calc(2.40rem + 2.00vw), 4.00rem)",
                "fluid-3xl": "clamp(4.50rem, calc(3.60rem + 3.00vw), 6.00rem)",
                "fluid-3xs-2xs": "clamp(0.19rem, calc(0.00rem + 0.63vw), 0.50rem)",
                "fluid-2xs-xs": "clamp(0.38rem, calc(0.15rem + 0.75vw), 0.75rem)",
                "fluid-xs-s": "clamp(0.56rem, calc(0.30rem + 0.88vw), 1.00rem)",
                "fluid-s-m": "clamp(0.75rem, calc(0.30rem + 1.50vw), 1.50rem)",
                "fluid-m-l": "clamp(1.13rem, calc(0.60rem + 1.75vw), 2.00rem)",
                "fluid-l-xl": "clamp(1.50rem, calc(0.60rem + 3.00vw), 3.00rem)",
                "fluid-xl-2xl": "clamp(2.25rem, calc(1.20rem + 3.50vw), 4.00rem)",
                "fluid-2xl-3xl": "clamp(3.00rem, calc(1.20rem + 6.00vw), 6.00rem)",
                "fluid-s-l": "clamp(0.75rem, calc(0.00rem + 2.50vw), 2.00rem)",
            },
            colors: {
                //                midnight: "hsl(228, 70%, 6%)",
                midnight: {
                    DEFAULT: "#02040D",
                    950: "#02040D",
                    900: "#040816",
                    800: "#060C1E",
                    700: "#081026",
                    600: "#0A152F",
                    500: "#0C1B37",
                    400: "#0E213F",
                    300: "#102747",
                    200: "#122D4E",
                    100: "#153456",
                },
                dusk: {
                    DEFAULT: "hsl(229, 50%, 6%)",
                    950: "#080A17",
                    900: "#0B0F1E",
                    800: "#0F1324",
                    700: "#13182A",
                    600: "#181E30",
                    500: "#1D2335",
                    400: "#22293A",
                    300: "#282F3E",
                    200: "#2E3442",
                    100: "#353A46",
                },
                ltblue: {
                    15: "hsl(210, 15%, 60%)",
                    20: "hsl(210, 20%, 60%)",
                    25: "hsl(210, 25%, 60%)",
                    30: "hsl(210, 30%, 60%)",
                    DEFAULT: "hsl(210, 35%, 60%)",
                    40: "hsl(210, 40%, 60%)",
                    45: "hsl(210, 45%, 60%)",
                    50: "hsl(210, 50%, 60%)",
                    55: "hsl(210, 55%, 60%)",
                    60: "hsl(210, 60%, 60%)",
                    65: "hsl(210, 65%, 60%)",
                    70: "hsl(210, 70%, 60%)",
                    75: "hsl(210, 75%, 60%)",
                    80: "hsl(210, 80%, 60%)",
                    85: "hsl(210, 85%, 60%)",
                    txt: "hsl(210, 30%, 70%)",
                },
                ltblack: "hsl(0, 0%, 3%)",
            },
            fontFamily: {
                sans: ['"Albert Sans"', 'Nunito', ...defaultTheme.fontFamily.sans],
                albert: ['"Albert Sans", sans-serif'],
                mono: ['"Noto Sans Mono", monospace'],
            },
            boxShadow: {
                glow: "0px 0px 5px 0px",
            },
            gridTemplateColumns: {
                furnace: "minmax(12ch, 22ch) minmax(5ch, 10ch) minmax(30ch, 50vw)",
            },
        },
    },
    plugins: [
//        require('@tailwindcss/forms'),
        require("daisyui"),
    ],

    daisyui: {
        themes: [
            {
                mytheme: {
                    primary: "#6199d1",
                    secondary: "#153456",
                    accent: "#582f08",
                    neutral: "#202439",
                    "base-100": "#02040D",
                    info: "#060C1E",
                    success: "#4ade80",
                    warning: "#f59e0b",
                    error: "#e11d48",
                },
            },
            "dark",
            "night",
            "sunset",
            "dracula",
            "synthwave",
            "dim",
        ],
        darkTheme: "mytheme", // name of one of the included themes for dark mode
        base: false, // applies background color and foreground color for root element by default
        styled: true, // include daisyUI colors and design decisions for all components
        utils: true, // adds responsive and modifier utility classes
        prefix: "", // prefix for daisyUI classnames (components, modifiers and responsive class names. Not colors)
        logs: true, // Shows info about daisyUI version and used config in the console when building your CSS
        themeRoot: ":root", // The element that receives theme color CSS variables
    },
}
