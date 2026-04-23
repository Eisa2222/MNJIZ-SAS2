// resources/js/bootstrap.js

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// تعيين axios إلى window
window.axios = axios;

// إعداد رؤوس الطلبات الافتراضية
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// تعيين Pusher إلى window
window.Pusher = Pusher;

// استيراد Bootstrap's JS
import 'bootstrap';

// تسجيل المتغيرات البيئية للتحقق منها
// console.log('VITE_PUSHER_APP_KEY:', import.meta.env.VITE_PUSHER_APP_KEY);
// console.log('VITE_PUSHER_APP_CLUSTER:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

// تهيئة Laravel Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true, // استخدام TLS لضمان الأمان
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
        }
    },
    clientLogLevel: 'debug', // لتفعيل سجل التصحيح
});

// تسجيل Echo في وحدة التحكم للتحقق
// console.log('Echo initialized:', window.Echo);
