import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { VitePWA } from 'vite-plugin-pwa'
import { copyFile, access, unlink, readFile, writeFile } from 'node:fs/promises'
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
                'resources/css/watch.css',
                'resources/js/app.js',
                'resources/js/chat.js',
                'resources/js/db.js',
                'resources/js/debug.js',
                'resources/js/gif.js',
                'resources/js/history.js',
                'resources/js/listen.js',
                'resources/js/markdown.js',
                'resources/js/settings.js',
                'resources/js/watch.js',
                'resources/js/worker.js',
            ],
            refresh: true,
        }),
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
