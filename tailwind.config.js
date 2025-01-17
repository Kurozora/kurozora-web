const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    experimental: {
        optimizeUniversalDefaults: true,
    },

    content: [
        './app/**/*.php',
        './public/**/*.html',
        './resources/**/*.{html,js,jsx,md,mdx,php,ts,tsx,twig,vue}'
    ],

    theme: {
        extend: {
            colors: {
                orange: {
                    DEFAULT: '#FF9300',
                    '400': '#FFA933',
                    '500': '#FF9300',
                    '600': '#E68400',
                },
            },

            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['Fira Code VF', ...defaultTheme.fontFamily.mono],
                source: ['Source Sans Pro', ...defaultTheme.fontFamily.sans],
                'ubuntu-mono': ['Ubuntu Mono', ...defaultTheme.fontFamily.mono],
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography')
    ],
}
