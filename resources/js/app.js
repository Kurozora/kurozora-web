import ThemeRoller from './components/ThemeRoller'

// Require vue
window.Vue = require('vue');

// Vue instance
const app = new Vue({
    el: '#app',
    components: {
        ThemeRoller
    }
});