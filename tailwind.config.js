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
        extend: {},
        colors: {
            orange: '#FF9300',
        },
    },
    variants: {},
    plugins: [
        require('tailwindcss-plugins/pagination')
    ],
}
