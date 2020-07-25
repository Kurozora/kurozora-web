/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import Vue from 'vue'
import './plugins'

// Require vue
window.Vue = require('vue')

// Disable console messages
Vue.config.productionTip = false
Vue.config.devtools = false

/**
 * Next, we'll setup some of Kurozora's Vue components that need to be global
 * so that they are always available. Then, we will be ready to create
 * the actual Vue instance and start up this JavaScript application.
 */
import './components'

const app = new Vue({
    el: '#app',
});
