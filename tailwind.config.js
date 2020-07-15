module.exports = {
    purge: {
        content: [
            "resources/**/*.html",
            "resources/**/*.js",
            "resources/**/*.jsx",
            "resources/**/*.ts",
            "resources/**/*.tsx",
            "resources/**/*.php",
            "resources/**/*.vue",
            "resources/**/*.twig",
        ],
        defaultExtractor: (content) => content.match(/[\w-/.:]+(?<!:)/g) || [],
        whitelistPatterns: [/-active$/, /-enter$/, /-leave-to$/, /show$/],
    },
    theme: {
        extend: {
            colors: {
                orange: {
                    '100': '#FFFAF0',
                    '200': '#FEEBC8',
                    '300': '#FBD38D',
                    '400': '#F6AD55',
                    '500': '#FF9300',
                    '600': '#DD6B20',
                    '700': '#C05621',
                    '800': '#9C4221',
                    '900': '#7B341E',
                }
            }
        }
    },
    variants: {},
    plugins: [
        require('tailwindcss-plugins/pagination')
    ],
}
