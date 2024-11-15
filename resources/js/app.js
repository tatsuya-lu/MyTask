import { createApp } from "vue";
import { createPinia } from "pinia";
import { useAuthStore } from "./stores/auth";
import { createRouter, createWebHistory } from "vue-router";
import App from './App.vue';
import Login from "./components/Auth/Login.vue";
import Register from "./components/Auth/Register.vue";
import TaskList from './components/Tasks/TaskList.vue';
import TaskForm from './components/Tasks/TaskForm.vue';
import TagManager from "./components/Tags/TagManager.vue";
import TeamList from './components/Teams/TeamList.vue';
import TeamFormModal from './components/Teams/TeamFormModal.vue';
import AddMemberModal from './components/Teams/AddMemberModal.vue';
import { setupAxios } from './utils/axios';

setupAxios();

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login
    },
    {
        path: '/register',
        name: 'register',
        component: Register
    },
    {
        path: '/',
        redirect: { name: 'tasks' }
    },
    {
        path: '/tasks',
        name: 'tasks',
        component: TaskList,
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/create',
        name: 'task-create',
        component: TaskForm,
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/:id/edit',
        name: 'task-edit',
        component: TaskForm,
        meta: { requiresAuth: true }
    },
    {
        path: '/tags',
        name: 'tags',
        component: TagManager,
        meta: { requiresAuth: true }
    },
    {
        path: '/calendar',
        name: 'task-calendar',
        component: () => import('./components/Tasks/TaskCalendar.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/teams',
        name: 'teams',
        component: TeamList,
        meta: { requiresAuth: true, requiresPremium: true }
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
    } else if (to.meta.requiresPremium && !authStore.user?.is_premium) {
        next({ name: 'tasks' });
    } else {
        next();
    }
});

const app = createApp(App);
app.use(pinia);
app.use(router);
app.mount('#app');