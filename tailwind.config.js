const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    darkMode: 'class',

    content: [
        './app/**/*.php',
        './public/**/*.html',
        './resources/**/*.{html,js,jsx,md,php,ts,tsx,twig,vue}'
    ],

    theme: {
        pagination: theme => ({
            color: theme('colors.orange.500'),
            linkFirst: 'mr-6 border rounded',
            linkSecond: 'rounded-l border-l',
            linkBeforeLast: 'rounded-r border-r',
            linkLast: 'ml-6 border rounded',
        }),

        extend: {
            colors: {
                orange: {
                    DEFAULT: '#FF9300',
                    '50': '#FFF4E5',
                    '100': '#FFE9CC',
                    '200': '#FFD499',
                    '300': '#FFBE66',
                    '400': '#FFA933',
                    '500': '#FF9300',
                    '600': '#E68400',
                    '700': '#CC7600',
                    '800': '#B36700',
                    '900': '#995800'
                },
            },

            fontFamily: {
                sans: ['Inter var', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        require('@tailwindcss/aspect-ratio'),
        require('@tailwindcss/forms'),
        require('@tailwindcss/line-clamp'),
        require('@tailwindcss/typography')
    ],
}
