import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTeamStore } from '@/stores/team'

import Register from '@/components/Auth/Register.vue'
import Login from '@/components/Auth/Login.vue'
import TaskList from '@/components/Tasks/TaskList.vue'
import TaskForm from '@/components/Tasks/TaskForm.vue'
import TagManager from "@/components/Tags/TagManager.vue"
import TeamList from '@/components/Teams/TeamList.vue'
import TeamDetail from '@/components/Teams/TeamDetail.vue'
import TeamTasks from '@/components/Teams/TeamTasks.vue'
import TeamSettings from '@/components/Teams/TeamSettings.vue'
import NotificationList from '@/components/Notification/NotificationList.vue'
import NotificationSettings from '@/components/Settings/NotificationSettings.vue'


const routes = [
    // 認証関連ルート
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { requiresGuest: true }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { requiresGuest: true }
    },
    // ダッシュボード・タスク関連ルート
    {
        path: '/',
        redirect: '/dashboard'
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: TaskList,
        meta: { requiresAuth: true }
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
    // タグ管理ルート
    {
        path: '/tags',
        name: 'tags',
        component: TagManager,
        meta: { requiresAuth: true }
    },
    // カレンダールート
    {
        path: '/calendar',
        name: 'calendar',
        component: () => import('./components/Tasks/TaskCalendar.vue'),
        meta: { requiresAuth: true }
    },
    // 通知関連ルート
    {
        path: '/notifications',
        name: 'notifications',
        component: NotificationList,
        meta: { requiresAuth: true }
    },
    {
        path: '/settings/notifications',
        name: 'notification-settings',
        component: NotificationSettings,
        meta: { requiresAuth: true }
    },
    // チーム関連ルート
    {
        path: '/teams',
        name: 'teams',
        component: TeamList,
        meta: { requiresAuth: true }
    },
    {
        path: '/teams/:id',
        name: 'team-detail',
        component: TeamDetail,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'team-dashboard',
                component: TeamTasks
            },
            {
                path: 'tasks',
                name: 'team-tasks',
                component: TeamTasks
            },
            {
                path: 'settings',
                name: 'team-settings',
                component: TeamSettings,
                meta: { requiresTeamLeader: true }
            }
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // ゲストのみのルート（ログイン・登録画面）
    if (to.meta.requiresGuest) {
        if (authStore.isAuthenticated) {
            next('/dashboard');
            return;
        }
        next();
        return;
    }

    // 認証が必要なルート
    if (to.meta.requiresAuth) {
        if (!authStore.token) {
            next('/login');
            return;
        }

        // ユーザー情報の取得（まだ取得していない場合）
        if (!authStore.user) {
            try {
                const success = await authStore.fetchUser();
                if (!success) {
                    next('/login');
                    return;
                }
            } catch (error) {
                console.error('ユーザー情報の取得に失敗:', error);
                next('/login');
                return;
            }
        }

        // チームリーダー権限のチェック
        if (to.meta.requiresTeamLeader && to.params.id) {
            const teamStore = useTeamStore();
            try {
                const team = await teamStore.fetchTeam(to.params.id);
                const isLeader = team?.members?.some(member =>
                    member.id === authStore.user?.id &&
                    member.pivot.role_id === teamStore.leaderRoleId
                );

                if (!isLeader) {
                    next('/teams');
                    return;
                }
            } catch (error) {
                console.error('チーム情報の取得に失敗:', error);
                next('/teams');
                return;
            }
        }
    }

    next();
});

export default router