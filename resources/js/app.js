import { createApp } from "vue";
import { createPinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from './stores/auth';
import axios from 'axios';
import App from './App.vue';

axios.defaults.baseURL = 'http://127.0.0.1:8000';  // localhostではなく127.0.0.1を使用
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const routes = [
    {
        path: '/',
        redirect: '/tasks'
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('./components/Auth/Login.vue'),
        meta: { guest: true, layout: "empty" }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('./components/Auth/Register.vue'),
        meta: { guest: true, layout: "empty" }
    },
    {
        path: '/tasks',
        name: 'tasks',
        component: () => import('./components/Tasks/TaskList.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/create',
        name: 'task-create',
        component: () => import('./components/Tasks/TaskForm.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/calendar',
        name: 'task-calendar',
        component: () => import('./components/Tasks/TaskCalendar.vue'),
        meta: { requiresAuth: true }
    }
];

// Piniaストアの作成
const pinia = createPinia();

// ルーターの作成
const router = createRouter({
    history: createWebHistory(),
    routes
});

// ナビゲーションガード
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore(pinia);

    // 初回ロード時の認証チェック
    if (!authStore.isLoaded) {
        try {
            await authStore.checkAuth();
        } catch (error) {
            console.error('Auth check failed:', error);
            authStore.clearUser();
        }
    }

    // 認証が必要なルートの処理
    if (to.meta.requiresAuth) {
        if (!authStore.isLoggedIn) {
            next({ name: 'login' });
            return;
        }
    }

    // ゲスト専用ルートの処理
    if (to.meta.guest) {
        if (authStore.isLoggedIn) {
            next({ name: 'tasks' });
            return;
        }
    }

    next();
});

// アプリケーションの作成とマウント
const app = createApp(App);
app.use(pinia);
app.use(router);
app.mount('#app');

export default router;