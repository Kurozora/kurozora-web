import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { VitePWA } from 'vite-plugin-pwa'
import { copyFile, access, unlink, readdir, readFile, writeFile } from 'node:fs/promises'
import { resolve } from 'node:path'

function minifyHtml(html) {
    return html
        .replace(/<!--[\s\S]*?-->/g, '')
        .replace(/>\s+</g, '><')
        .replace(/\s{2,}/g, ' ')
        .trim()
}

function generateOfflineHtml() {
    return {
        name: 'kurozora:offline-html',
        apply: 'build',
        async closeBundle() {
            const manifestPath = resolve('public/build/manifest.json')
            const sourcePath = resolve('resources/offline.html')
            const outputPath = resolve('public/offline.html')

            try {
                await access(manifestPath)
            } catch {
                return
            }

            const manifest = JSON.parse(await readFile(manifestPath, 'utf8'))
            const appCss = manifest['resources/css/app.css']?.file
            if (!appCss) return

            const source = await readFile(sourcePath, 'utf8')
            const output = minifyHtml(source.replaceAll('__APP_CSS__', `/build/${appCss}`))
            await writeFile(outputPath, output)
        },
    }
}

function generateIconSprite() {
    const sets = [
        ['symbols', ''],
        ['brands', 'brands-'],
        ['badges', 'badges-'],
    ]

    function symbolFor(name, source) {
        const root = source.match(/<svg\b([^>]*)>([\s\S]*)<\/svg>/)
        if (!root) return null

        const viewBox = root[1].match(/viewBox="([^"]*)"/)
        if (!viewBox) return null

        const inner = root[2]
            .replace(/\bid="([^"]+)"/g, (_, id) => `id="${name}__${id}"`)
            .replace(/url\(#([^)]+)\)/g, (_, id) => `url(#${name}__${id})`)
            .replace(/\b(xlink:href|href)="#([^"]+)"/g, (_, attribute, id) => `${attribute}="#${name}__${id}"`)
            .trim()

        return `<symbol id="${name}" viewBox="${viewBox[1]}">${inner}</symbol>`
    }

    return {
        name: 'kurozora:icon-sprite',
        async buildStart() {
            const symbols = new Map()

            for (const [directory, prefix] of sets) {
                const source = resolve('public/images', directory)

                let entries
                try {
                    entries = await readdir(source)
                } catch {
                    continue
                }

                for (const entry of entries.filter((entry) => entry.endsWith('.svg'))) {
                    const name = prefix + entry.slice(0, -4)
                    const symbol = symbolFor(name, await readFile(resolve(source, entry), 'utf8'))

                    if (symbol) symbols.set(name, symbol)
                }
            }

            const sprite = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'
                + [...symbols.keys()].sort().map((name) => symbols.get(name)).join('')
                + '</svg>'

            await writeFile(resolve('public/images/sprite.svg'), sprite)
        },
    }
}

function relocateServiceWorker() {
    const files = ['service-worker.js', 'service-worker.js.map']

    return {
        name: 'kurozora:relocate-sw',
        apply: 'build',
        enforce: 'post',
        async closeBundle() {
            for (const name of files) {
                const from = resolve('public/build', name)
                const to = resolve('public', name)

                try {
                    await access(from)
                } catch {
                    continue
                }

                await copyFile(from, to)
                await unlink(from)
            }
        },
    }
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/chat.css',
                'resources/css/trailer.css',
                'resources/css/watch.css',
                'resources/js/app.js',
                'resources/js/charts.js',
                'resources/js/chat.js',
                'resources/js/db.js',
                'resources/js/debug.js',
                'resources/js/dropdown.js',
                'resources/js/gif.js',
                'resources/js/history.js',
                'resources/js/listen.js',
                'resources/js/lyrics.js',
                'resources/js/markdown.js',
                'resources/js/museum.js',
                'resources/js/recap-share.js',
                'resources/js/settings.js',
                'resources/js/submenu.js',
                'resources/js/trailer-hero.js',
                'resources/js/watch.js',
                'resources/js/worker.js',
            ],
            refresh: true,
        }),
        generateIconSprite(),
        generateOfflineHtml(),
        VitePWA({
            strategies: 'injectManifest',
            srcDir: 'resources/js',
            filename: 'service-worker.js',
            injectRegister: false,
            manifest: false,
            injectManifest: {
                globDirectory: 'public',
                globPatterns: [
                    'offline.html',
                    'images/static/icon/no_signal.webp',
                    'build/assets/*.css',
                ],
                maximumFileSizeToCacheInBytes: 5 * 1024 * 1024,
            },
            devOptions: {
                enabled: false,
                type: 'module',
            },
        }),
        relocateServiceWorker(),
    ],
    build: {
        sourcemap: true,
    },
})
