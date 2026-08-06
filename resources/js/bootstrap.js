/**
 * Load custom extensions.
 */
import './extensions/index'
/**
 * iro is a modular, design-conscious color picker widget.
 * It works with colors in hex, RGB, HSV and HSL formats.
 */
import iro from "@jaames/iro"
/**
 * LazySizes is an SEO-friendly and self-initializing lazyloader for images
 * iframes, scripts/widgets and much more. It prioritizes resources  by
 * differentiating between crucial in view and near view elements to make
 * perceived performance even faster.
 */
import 'lazysizes'

window.iro = iro

/**
 * Toggles the page viewport between zoom-locked and pinch-zoomable.
 */
window.allowZoom = function (allow) {
    const meta = document.getElementById('app-viewport')

    if (!meta) {
        return
    }

    meta.setAttribute(
        'content',
        allow
            ? 'width=device-width, initial-scale=1, viewport-fit=cover'
            : 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'
    )
}
