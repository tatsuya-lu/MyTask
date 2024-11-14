import { createApp } from "vue";
import { createPinia } from "pinia";
import { useAuthStore } from "./stores/auth";
import { createRouter, createWebHistory } from "vue-router";
import App from './App.vue';
import { setupAxios } from './utils/axios';

setupAxios();

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('./components/Auth/Login.vue')
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('./components/Auth/Register.vue')
    },
    {
        path: '/',
        redirect: { name: 'tasks' }
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
        path: '/tasks/:id/edit',
        name: 'task-edit',
        component: () => import('./components/Tasks/TaskForm.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/calendar',
        name: 'task-calendar',
        component: () => import('./components/Tasks/TaskCalendar.vue'),
        meta: { requiresAuth: true }
    }
];

const pinia = createPinia();
const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore(pinia);

    if (!authStore.isAuthenticated) {
        await authStore.checkAuth();
    }
    
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else {
        next();
    }
});

const app = createApp(App);
app.use(pinia);
app.use(router);
app.mount('#app');