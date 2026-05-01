import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./app/Livewire/**/*.php",
    ],

    // Support both Alpine class toggle and data-theme attribute
    darkMode: ['class', '[data-theme="dark"]'],

    theme: {
        extend: {
            colors: {
                // Brand accent: purple-700 from new design
                accent: {
                    50:  '#faf5ff',
                    100: '#f3e8ff',
                    200: '#e9d5ff',
                    300: '#d8b4fe',
                    400: '#c084fc',
                    500: '#a855f7',
                    600: '#9333ea',
                    700: '#7e22ce',
                    800: '#6b21a8',
                    900: '#581c87',
                    950: '#3b0764',
                },
                // Keep semantic aliases
                success: {
                    50:  '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0',
                    300: '#86efac', 400: '#4ade80', 500: '#22c55e',
                    600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d',
                },
                danger: {
                    50:  '#fef2f2', 100: '#fee2e2', 200: '#fecaca',
                    300: '#fca5a5', 400: '#f87171', 500: '#ef4444',
                    600: '#dc2626', 700: '#b91c1c', 800: '#991b1b', 900: '#7f1d1d',
                },
                warning: {
                    50:  '#fffbeb', 100: '#fef3c7', 200: '#fde68a',
                    300: '#fcd34d', 400: '#fbbf24', 500: '#f59e0b',
                    600: '#d97706', 700: '#b45309', 800: '#92400e', 900: '#78350f',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
                mono: ['Fira Code', 'Monaco', 'Consolas', 'monospace'],
            },
            boxShadow: {
                soft: '0 18px 55px rgba(15, 23, 42, .08)',
                glow: '0 14px 35px rgba(109, 40, 217, .24)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
            animation: {
                'spin-slow':  'spin 3s linear infinite',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
        },
    },

    // Module badge colors are dynamically generated — safelist prevents purging
    safelist: [
        'bg-purple-600', 'bg-blue-600', 'bg-green-600', 'bg-red-600',
        'bg-yellow-600', 'bg-orange-600', 'bg-pink-600', 'bg-teal-600',
    ],

    plugins: [forms, typography],

    future: {
        hoverOnlyWhenSupported: true,
    },
};
