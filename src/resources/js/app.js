window.Vue = require('vue').default

import 'particles.js'
import particlesConfig from './particles.js'

Vue.component('night-mode', require('./components/NightMode').default)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

new Vue({
    el: '#app',
});

if (document.querySelector('#particles-js')) {
    particlesJS('particles-js', particlesConfig);
}
