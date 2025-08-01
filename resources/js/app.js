import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

console.log('App.js loaded');
console.log('Vue:', createApp);
console.log('App component:', App);

// Create the Vue app and mount it to #app
try {
    const app = createApp(App);
    app.use(router);
    const mounted = app.mount('#app');
    console.log('Vue app created and mounted:', mounted);
} catch (error) {
    console.error('Error mounting Vue app:', error);
}
