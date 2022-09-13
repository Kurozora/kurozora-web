/**
 * PicMo is a plain JavaScript emoji picker widget. It can be used in two ways:
 *
 * - As a standalone emoji picker inline in the page. The picker is rendered immediately on the page.
 * - As a popup emoji picker. The popup is triggered by a button or other interactive element.
 *
 * PicMo's emoji data comes from the Emojibase project. The data is cached locally in an IndexedDB database.
 */
import {createPopup} from "@picmo/popup-picker"

(function() {
    let rootElement = document.querySelector('body')

    window.picmo = createPopup({
        // picker options go here
        rootElement: rootElement,
    }, {
        className: 'z-50',

        // Specify how to position the popup
        position: 'bottom-start',

        // Whether to show the close button
        showCloseButton: false
    })
})()
