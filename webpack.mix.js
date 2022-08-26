const mix = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.options({
    terser: {
        extractComments: false,
    }
})

mix.js('resources/js/app.js', 'public/js/app.js')
    .js('resources/js/chat.js', 'public/js/chat.js')
    .js('resources/js/watch.js', 'public/js/watch.js')
    .postCss('resources/css/app.css', 'public/css/app.css')
    .postCss('resources/css/watch.css', 'public/css/watch.css')
    .sourceMaps()

if (mix.inProduction()) {
    mix.version()
}
