/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
                'sena-green': '#39B54A',
                'sena-orange': '#F7941E',
            },
            boxShadow: {
                soft: '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                glow: '0 0 20px rgba(57, 181, 74, 0.12)',
                card: '0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -2px rgba(0, 0, 0, 0.04)',
                'card-hover':
                    '0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04), 0 0 20px rgba(57, 181, 74, 0.08)',
            },
            transitionDuration: {
                250: '250ms',
                350: '350ms',
            },
            transitionTimingFunction: {
                smooth: 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },
    plugins: [],
};
