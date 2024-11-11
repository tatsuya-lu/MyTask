import { createRouter, createWebHashHistory } from "vue-router";

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../components/Tasks/TaskList.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../components/Auth/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../components/Auth/Register.vue'),
        meta: { guest: true }
    },
    {
        path: '/tasks/create',
        name: 'task-create',
        component: () => import('../components/Tasks/TaskForm.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/calendar',
        name: 'task-calender',
        component: () => import('../components/Tasks/TaskCalender.vue'),
        meta: { requiresAuth: true }
    }
]

const router = createRouter({
    history: createWebHashHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('token')

    if (to.meta.requiresAuth && !isAuthenticated) {
        next('login')
    } else if (to.meta.guest && isAuthenticated) {
        next('/')
    } else {
        next()
    }
})

export default router