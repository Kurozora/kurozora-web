/**
 * PicMo is a plain JavaScript emoji picker widget. It can be used in two ways:
 *
 * - As a standalone emoji picker inline in the page. The picker is rendered immediately on the page.
 * - As a popup emoji picker. The popup is triggered by a button or other interactive element.
 *
 * PicMo's emoji data comes from the Emojibase project. The data is cached locally in an IndexedDB database.
 */
import {createPopup} from "@picmo/popup-picker"

let emojiButton = document.querySelector('.emoji-button')
window.picmo = createPopup({}, {
    // The element that triggers the popup
    triggerElement: emojiButton,

    // The element to position the picker relative to - often this is also the trigger element,
    referenceElement: emojiButton,

    // specify how to position the popup
    position: 'bottom-start'
})
