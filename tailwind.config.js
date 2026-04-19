/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: ['selector', '[data-theme="dark"]'],
    theme: {
        extend: {},
    },
    plugins: [require("daisyui"), require("@tailwindcss/typography")],
    daisyui: { themes: ["light", "dark"] },
};
