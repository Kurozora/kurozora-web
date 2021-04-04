const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: {
        content: [
            './app/**/*.php',
            './resources/**/*.html',
            './resources/**/*.js',
            './resources/**/*.jsx',
            './resources/**/*.ts',
            './resources/**/*.tsx',
            './resources/**/*.php',
            './resources/**/*.vue',
            './resources/**/*.twig',
        ],
        options: {
            defaultExtractor: (content) => content.match(/[\w-/.:]+(?<!:)/g) || [],
            whitelistPatterns: [/-active$/, /-enter$/, /-leave-to$/, /show$/],
        },
    },

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
                grayBlue: {
                    DEFAULT: '#353A50',
                    '100': '#EBEBEE',
                    '200': '#CDCED3',
                    '300': '#AEB0B9',
                    '400': '#727585',
                    '500': '#353A50',
                    '600': '#303448',
                    '700': '#202330',
                    '800': '#181A24',
                    '900': '#101118',
                },
                orange: {
                    DEFAULT: '#FF9300',
                    '100': '#FFF4E6',
                    '200': '#FFE4BF',
                    '300': '#FFD499',
                    '400': '#FFB34D',
                    '500': '#FF9300',
                    '600': '#dd6b20',
                    '700': '#c05621',
                    '800': '#9c4221',
                    '900': '#7B341E',
                },
            },

            fontFamily: {
                sans: ['Inter var', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    variants: {
        opacity: ['responsive', 'hover', 'focus', 'disabled'],

        extend: {
            backgroundColor: ['active'],
        },
    },

    plugins: [
        require('@tailwindcss/aspect-ratio'),
        require('@tailwindcss/forms'),
        require('@tailwindcss/line-clamp'),
        require('@tailwindcss/typography')
    ],
}
