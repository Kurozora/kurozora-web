/**
 * The largest chroma scale a glow field stores.
 *
 * @type {number}
 */
const maxChroma = 1.1

/**
 * The strength of the overlay grain.
 *
 * @type {number}
 */
const grainStrength = 0.09

/**
 * The decoded glow fields keyed by URL.
 *
 * @type {Map<string, Promise<Object>>}
 */
const fields = new Map()

self.addEventListener('message', async (event) => {
    const { id, url, splits, width, height, first, second, background } = event.data

    try {
        const field = await loadField(url)
        const pixels = paint(field, splits, width, height, channels(first), channels(second), channels(background))
        self.postMessage({ id, pixels }, [pixels.buffer])
    } catch (error) {
        self.postMessage({ id, error: error.message })
    }
})

/**
 * Returns the decoded glow field at the given URL.
 *
 * @param {string} url
 * @returns {Promise<{width: number, height: number, channels: Float32Array[]}>}
 */
function loadField(url) {
    if (!fields.has(url)) {
        fields.set(url, fetch(url).then((response) => response.arrayBuffer()).then(decodePNG))
    }

    return fields.get(url)
}

/**
 * Returns the channels of an 8-bit RGB or RGBA PNG.
 *
 * @param {ArrayBuffer} buffer
 * @returns {Promise<{width: number, height: number, channels: Float32Array[]}>}
 */
async function decodePNG(buffer) {
    const view = new DataView(buffer)
    const bytes = new Uint8Array(buffer)
    const chunks = []
    let width = 0
    let height = 0
    let bytesPerPixel = 3

    for (let offset = 8; offset < bytes.length;) {
        const length = view.getUint32(offset)
        const type = String.fromCharCode(...bytes.subarray(offset + 4, offset + 8))
        const data = bytes.subarray(offset + 8, offset + 8 + length)

        if (type === 'IHDR') {
            width = view.getUint32(offset + 8)
            height = view.getUint32(offset + 12)
            bytesPerPixel = data[9] === 6 ? 4 : 3
        } else if (type === 'IDAT') {
            chunks.push(data)
        }

        offset += 12 + length
    }

    const stream = new Blob(chunks).stream().pipeThrough(new DecompressionStream('deflate'))
    const scanlines = new Uint8Array(await new Response(stream).arrayBuffer())
    const rowLength = width * bytesPerPixel
    const pixels = new Uint8Array(rowLength * height)

    // Undoes the filter each scanline was stored with.
    for (let row = 0; row < height; row++) {
        const filter = scanlines[row * (rowLength + 1)]
        const source = row * (rowLength + 1) + 1
        const target = row * rowLength

        for (let column = 0; column < rowLength; column++) {
            const left = column >= bytesPerPixel ? pixels[target + column - bytesPerPixel] : 0
            const up = row > 0 ? pixels[target - rowLength + column] : 0
            const upLeft = row > 0 && column >= bytesPerPixel ? pixels[target - rowLength + column - bytesPerPixel] : 0
            const value = scanlines[source + column]
            let predicted = 0

            switch (filter) {
                case 1: predicted = left; break
                case 2: predicted = up; break
                case 3: predicted = (left + up) >> 1; break
                case 4: {
                    const estimate = left + up - upLeft
                    const toLeft = Math.abs(estimate - left)
                    const toUp = Math.abs(estimate - up)
                    const toUpLeft = Math.abs(estimate - upLeft)
                    predicted = toLeft <= toUp && toLeft <= toUpLeft ? left : toUp <= toUpLeft ? up : upLeft
                    break
                }
            }

            pixels[target + column] = (value + predicted) & 255
        }
    }

    const count = width * height
    const fieldChannels = [new Float32Array(count), new Float32Array(count), new Float32Array(count)]
    for (let index = 0; index < count; index++) {
        fieldChannels[0][index] = pixels[index * bytesPerPixel] / 255
        fieldChannels[1][index] = pixels[index * bytesPerPixel + 1] / 255
        fieldChannels[2][index] = pixels[index * bytesPerPixel + 2] / 255
    }

    return { width, height, channels: fieldChannels }
}

/**
 * Returns the glow painted in the recap colors over the background as RGBA pixels.
 *
 * @param {Object} field
 * @param {number[]} splits
 * @param {number} width
 * @param {number} height
 * @param {number[]} first
 * @param {number[]} second
 * @param {number[]} background
 * @returns {Uint8ClampedArray}
 */
function paint(field, splits, width, height, first, second, background) {
    const table = mixTable(first, second)
    const pixels = new Uint8ClampedArray(width * height * 4)
    const bounds = [0, ...splits, height]
    const scale = height / field.height

    for (let region = 0; region < bounds.length - 1; region++) {
        const top = bounds[region]
        const bottom = bounds[region + 1]
        const gridTop = Math.round(top / scale)
        const gridBottom = Math.round(bottom / scale)
        const [coverage, mix, chroma] = field.channels.map((channel) => upsample(
            channel.subarray(gridTop * field.width, gridBottom * field.width),
            field.width,
            gridBottom - gridTop,
            width,
            bottom - top,
        ))

        for (let y = top; y < bottom; y++) {
            for (let x = 0; x < width; x++) {
                const index = (y - top) * width + x
                const alpha = Math.min(Math.max(coverage[index], 0), 1)
                const step = Math.round(Math.min(Math.max(mix[index], 0), 1) * 255)
                const level = Math.round(Math.min(Math.max(chroma[index], 0), 1) * 63)
                const color = (step * 64 + level) * 3
                const grain = Math.random()
                const pixel = (y * width + x) * 4

                for (let channel = 0; channel < 3; channel++) {
                    const value = background[channel] + (table[color + channel] - background[channel]) * alpha
                    const overlay = value < 0.5 ? 2 * value * grain : 1 - 2 * (1 - value) * (1 - grain)
                    pixels[pixel + channel] = Math.round(Math.min(Math.max(value + (overlay - value) * grainStrength, 0), 1) * 255)
                }
                pixels[pixel + 3] = 255
            }
        }
    }

    return pixels
}

/**
 * Returns a hex color as channels between 0 and 1.
 *
 * @param {string} hex
 * @returns {number[]}
 */
function channels(hex) {
    const value = parseInt(hex.trim().replace('#', '').slice(0, 6), 16)
    return [((value >> 16) & 255) / 255, ((value >> 8) & 255) / 255, (value & 255) / 255]
}

/**
 * Returns the OKLCH form of an sRGB color.
 *
 * @param {number[]} color
 * @returns {number[]}
 */
function toOklch(color) {
    const [red, green, blue] = color.map((channel) => (channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4))
    const long = Math.cbrt(0.4122214708 * red + 0.5363325363 * green + 0.0514459929 * blue)
    const medium = Math.cbrt(0.2119034982 * red + 0.6806995451 * green + 0.1073969566 * blue)
    const short = Math.cbrt(0.0883024619 * red + 0.2817188376 * green + 0.6299787005 * blue)
    const a = 1.9779984951 * long - 2.428592205 * medium + 0.4505937099 * short
    const b = 0.0259040371 * long + 0.7827717662 * medium - 0.808675766 * short

    return [0.2104542553 * long + 0.793617785 * medium - 0.0040720468 * short, Math.hypot(a, b), Math.atan2(b, a)]
}

/**
 * Returns the sRGB form of an OKLCH color.
 *
 * @param {number[]} color
 * @returns {number[]}
 */
function fromOklch([lightness, chroma, hue]) {
    const a = chroma * Math.cos(hue)
    const b = chroma * Math.sin(hue)
    const long = (lightness + 0.3963377774 * a + 0.2158037573 * b) ** 3
    const medium = (lightness - 0.1055613458 * a - 0.0638541728 * b) ** 3
    const short = (lightness - 0.0894841775 * a - 1.291485548 * b) ** 3

    return [
        4.0767416621 * long - 3.3077115913 * medium + 0.2309699292 * short,
        -1.2684380046 * long + 2.6097574011 * medium - 0.3413193965 * short,
        -0.0041960863 * long - 0.7034186147 * medium + 1.707614701 * short,
    ].map((channel) => {
        const clamped = Math.min(Math.max(channel, 0), 1)
        return clamped <= 0.0031308 ? clamped * 12.92 : 1.055 * clamped ** (1 / 2.4) - 0.055
    })
}

/**
 * Returns the colors along the hue path between two colors by mix and chroma step.
 *
 * @param {number[]} from
 * @param {number[]} to
 * @returns {Float32Array}
 */
function mixTable(from, to) {
    const [fromLightness, fromChroma, fromHue] = toOklch(from)
    const [toLightness, toChroma, toHue] = toOklch(to)
    let hueDelta = toHue - fromHue
    if (hueDelta > Math.PI) hueDelta -= 2 * Math.PI
    if (hueDelta < -Math.PI) hueDelta += 2 * Math.PI

    const table = new Float32Array(256 * 64 * 3)
    for (let step = 0; step < 256; step++) {
        const mix = step / 255
        for (let level = 0; level < 64; level++) {
            table.set(fromOklch([
                fromLightness + (toLightness - fromLightness) * mix,
                (fromChroma + (toChroma - fromChroma) * mix) * (level / 63) * maxChroma,
                fromHue + hueDelta * mix,
            ]), (step * 64 + level) * 3)
        }
    }

    return table
}

/**
 * Returns the Catmull-Rom weights at the given fraction.
 *
 * @param {number} fraction
 * @returns {number[]}
 */
function cubicWeights(fraction) {
    const squared = fraction * fraction
    const cubed = squared * fraction

    return [-0.5 * cubed + squared - 0.5 * fraction, 1.5 * cubed - 2.5 * squared + 1, -1.5 * cubed + 2 * squared + 0.5 * fraction, 0.5 * cubed - 0.5 * squared]
}

/**
 * Returns one channel of a grid upsampled to the given size.
 *
 * @param {Float32Array} values
 * @param {number} gridWidth
 * @param {number} gridHeight
 * @param {number} width
 * @param {number} height
 * @returns {Float32Array}
 */
function upsample(values, gridWidth, gridHeight, width, height) {
    const horizontal = new Float32Array(width * gridHeight)
    for (let x = 0; x < width; x++) {
        const source = ((x + 0.5) * gridWidth) / width - 0.5
        const base = Math.floor(source)
        const weights = cubicWeights(source - base)
        for (let row = 0; row < gridHeight; row++) {
            let sum = 0
            for (let tap = 0; tap < 4; tap++) {
                sum += values[row * gridWidth + Math.min(Math.max(base - 1 + tap, 0), gridWidth - 1)] * weights[tap]
            }
            horizontal[row * width + x] = sum
        }
    }

    const upsampled = new Float32Array(width * height)
    for (let y = 0; y < height; y++) {
        const source = ((y + 0.5) * gridHeight) / height - 0.5
        const base = Math.floor(source)
        const weights = cubicWeights(source - base)
        const rows = [0, 1, 2, 3].map((tap) => Math.min(Math.max(base - 1 + tap, 0), gridHeight - 1) * width)
        for (let x = 0; x < width; x++) {
            upsampled[y * width + x] = horizontal[rows[0] + x] * weights[0] + horizontal[rows[1] + x] * weights[1] + horizontal[rows[2] + x] * weights[2] + horizontal[rows[3] + x] * weights[3]
        }
    }

    return upsampled
}
