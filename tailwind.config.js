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
                },
                ltblack: "hsl(0, 0%, 3%)",
            },
            fontFamily: {
                sans: [
                    "Nunito Sans",
                    "Albert Sans",
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            gridTemplateColumns: {
                "auto-fit": "repeat(auto-fit, minmax(0, 1fr))",
            },
        },
    },
    daisyui: {
        themes: [
            {
                mytheme: {
                    primary: "#38bdf8",

                    secondary: "#7c3aed",

                    accent: "#db2777",

                    neutral: "#2a323c",

                    "base-100": "#1d232a",

                    info: "#0d9488",

                    success: "#4ade80",

                    warning: "#fbbd23",

                    error: "#fb7185",
                },
            },
        ],
    },
    plugins: [
        //        require('@tailwindcss/forms'),
        require("daisyui"),
    ],
};
