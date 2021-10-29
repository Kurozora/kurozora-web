/**
 * A rugged, minimal framework for composing JavaScript behavior in your markup.
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'
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

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

window.iro = iro;

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
