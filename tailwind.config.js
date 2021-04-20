const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

module.exports = {
    mode: 'jit',

    purge: {
        content: [
            './app/**/*.php',
            './public/**/*.html',
            './resources/**/*.{html,js,jsx,php,ts,tsx,twig,vue}'
        ],
        options: {
            defaultExtractor: (content) => content.match(/[\w-/.:]+(?<!:)/g) || [],
            whitelistPatterns: [/-active$/, /-enter$/, /-leave-to$/, /show$/],
        },
    },

    darkMode: 'class',

    theme: {
        pagination: theme => ({
            color: theme('colors.orange.500'),
            linkFirst: 'mr-6 border rounded',
            linkSecond: 'rounded-l border-l',
            linkBeforeLast: 'rounded-r border-r',
            linkLast: 'ml-6 border rounded',
        }),

        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            black: colors.black,
            white: colors.white,
            rose: colors.rose,
            pink: colors.pink,
            fuchsia: colors.fuchsia,
            purple: colors.purple,
            violet: colors.violet,
            indigo: colors.indigo,
            blue: colors.blue,
            lightBlue: colors.lightBlue,
            cyan: colors.cyan,
            teal: colors.teal,
            emerald: colors.emerald,
            green: colors.green,
            lime: colors.lime,
            yellow: colors.yellow,
            amber: colors.amber,
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
            red: colors.red,
            warmGray: colors.warmGray,
            trueGray: colors.trueGray,
            gray: colors.gray,
            coolGray: colors.coolGray,
            blueGray: colors.blueGray
        },

        extend: {
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
