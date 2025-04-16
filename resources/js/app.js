import './bootstrap';
import {createApp} from 'vue';
import app from './components/app.vue';
import 'bootstrap/dist/css/bootstrap.min.css'; // Bootstrap CSS dosyasını dahil et
import router from './router';


createApp(app).use(router).mount('#app');