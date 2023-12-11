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

mix.extract()
    .js('resources/js/app.js', 'public/js/app.js')
    .js('resources/js/debug.js', 'public/js/debug.js')
    .js('resources/js/db.js', 'public/js/db.js')
    .js('resources/js/chat.js', 'public/js/chat.js')
    .js('resources/js/listen.js', 'public/js/listen.js')
    .js('resources/js/watch.js', 'public/js/watch.js')
    .js('resources/js/worker.js', 'public/js/worker.js')
    .postCss('resources/css/app.css', 'public/css/app.css')
    .postCss('resources/css/chat.css', 'public/css/chat.css')
    .postCss('resources/css/watch.css', 'public/css/watch.css')
    .sourceMaps()

if (mix.inProduction()) {
    mix.version()
}
