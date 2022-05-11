require('./bootstrap');
window.Vue = require('vue');
import VueRouter from 'vue-router'

Vue.use(VueRouter)
Vue.component('index', require('./view/public/footer.vue').default);
const app = new Vue({
    el: '#app',
});
