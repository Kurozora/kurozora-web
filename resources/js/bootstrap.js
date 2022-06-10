/**
 * A rugged, minimal framework for composing JavaScript behavior in your markup.
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
/**
 * iro is a modular, design-conscious color picker widget.
 * It works with colors in hex, RGB, HSV and HSL formats.
 */
import iro from "@jaames/iro";
/**
 * LazySizes is an SEO-friendly and self-initializing lazyloader for images
 * iframes, scripts/widgets and much more. It prioritizes resources  by
 * differentiating between crucial in view and near view elements to make
 * perceived performance even faster.
 */
import 'lazysizes';
/**
 * PicMo is a plain JavaScript emoji picker widget. It can be used in two ways:
 *
 * - As a standalone emoji picker inline in the page. The picker is rendered immediately on the page.
 * - As a popup emoji picker. The popup is triggered by a button or other interactive element.
 *
 * PicMo's emoji data comes from the Emojibase project. The data is cached locally in an IndexedDB database.
 */
import {createPopup} from "@picmo/popup-picker";

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.plugin(intersect);
Alpine.start();

window.iro = iro;

var emojiButton = document.querySelector('.emoji-button')
window.picmo = createPopup({}, {
    // The element that triggers the popup
    triggerElement: emojiButton,

    // The element to position the picker relative to - often this is also the trigger element,
    referenceElement: emojiButton,

    // specify how to position the popup
    position: 'bottom-start'
})

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from "laravel-echo"

// window.Pusher = require("pusher-js");

// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
