/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./*.html",
        "./pwa/**/*.html",
        "./assets/js/**/*.js"
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
            colors: {
                primary: '#3c50e0',
                success: '#219653',
                danger: '#d34053',
                warning: '#f0950c',
                boxdark: '#24303f',
                boxdark2: '#1a222c',
            }
        }
    },
    plugins: [],
}
