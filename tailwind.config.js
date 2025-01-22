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
                    '500': '#FF9300'
                },
            },

            typography: {
                theme: {
                    css: {
                        '--tw-prose-body': 'var(--primary-text-color)',
                        '--tw-prose-headings': 'var(--primary-text-color)',
                        '--tw-prose-lead': 'var(--primary-text-color)',
                        '--tw-prose-links': 'var(--tint-color)',
                        '--tw-prose-bold': 'var(--primary-text-color)',
                        '--tw-prose-counters': 'var(--secondary-text-color)',
                        '--tw-prose-bullets': 'var(--primary-text-color)',
                        '--tw-prose-hr': 'var(--border-color)',
                        '--tw-prose-quotes': 'var(--primary-text-color)',
                        '--tw-prose-quote-borders': 'var(--border-color)',
                        '--tw-prose-captions': 'var(--secondary-text-color)',
                        '--tw-prose-kbd': 'var(--primary-text-color)',
                        '--tw-prose-kbd-shadows': '17 24 39',
                        '--tw-prose-code': 'var(--primary-text-color)',
                        '--tw-prose-pre-code': 'var(--primary-text-color)',
                        '--tw-prose-pre-bg': 'var(--bg-secondary-color)',
                        '--tw-prose-th-borders': 'var(--border-color)',
                        '--tw-prose-td-borders': 'var(--border-color)'
                    },
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
