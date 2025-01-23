<template>
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <router-link :to="{ name: 'tasks' }" class="flex items-center">
                        <span class="text-xl font-bold">MyTask</span>
                    </router-link>
                </div>

                <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4">
                    <div class="relative">
                        <button @click="toggleNotifications" class="relative p-2">
                            <i class="fas fa-bell text-gray-600"></i>
                            <span v-if="notificationStore.unreadCount > 0"
                                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                {{ notificationStore.unreadCount }}
                            </span>
                        </button>

                        <div v-if="showNotifications"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50">
                            <!-- <div class="py-2">
                                <div v-if="notificationStore.loading" class="px-4 py-2 text-sm text-gray-500">
                                    読み込み中...
                                </div>
                                <div v-else-if="notificationStore.error" class="px-4 py-2 text-sm text-red-500">
                                    {{ notificationStore.error }}
                                </div>
                                <div v-else-if="!notificationStore.notifications?.length"
                                    class="px-4 py-2 text-sm text-gray-500">
                                    通知はありません
                                </div>
                                <div v-else v-for="notification in notificationStore.notifications"
                                    :key="notification?.id" class="px-4 py-2 hover:bg-gray-100"
                                    @click="notificationStore.markAsRead(notification.id)">
                                    <div class="flex items-center">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ notification.title }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ notification.content }}
                                            </p>
                                        </div>
                                        <div v-if="!notification.is_read" class="ml-2 w-2 h-2 bg-blue-600 rounded-full">
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="py-2">
                                <div v-if="notificationStore.loading" class="px-4 py-2 text-sm text-gray-500">
                                    読み込み中...
                                </div>
                                <div v-else-if="notificationStore.error" class="px-4 py-2 text-sm text-red-500">
                                    {{ notificationStore.error }}
                                </div>
                                <div v-else-if="!notificationStore.notifications?.length"
                                    class="px-4 py-2 text-sm text-gray-500">
                                    通知はありません
                                </div>
                                <template v-else>
                                    <div v-for="notification in notificationStore.notifications" :key="notification.id"
                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                        @click="notificationStore.markAsRead(notification.id)">
                                        <div class="text-sm">
                                            {{ notification.title }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ notification.content }}
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="border-t border-gray-200">
                                <router-link :to="{ name: 'notifications' }"
                                    class="block px-4 py-2 text-sm text-center text-gray-700 hover:bg-gray-100">
                                    すべての通知を見る
                                </router-link>
                            </div>
                        </div>
                    </div>

                    <router-link :to="{ name: 'notification-settings' }"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-200 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        通知設定
                    </router-link>
                    <router-link :to="{ name: 'task-create' }"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        新規タスク
                    </router-link>
                    <router-link :to="{ name: 'calendar' }"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        カレンダー
                    </router-link>
                    <router-link :to="{ name: 'tags' }"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        タグ一覧
                    </router-link>
                    <button @click="logout" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        ログアウト
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const router = useRouter()
const showNotifications = ref(false)

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value
    if (showNotifications.value) {
        notificationStore.fetchNotifications()
    }
}

onMounted(() => {
    notificationStore.fetchNotifications()
})

const logout = async () => {
    try {
        await authStore.logout()
        router.push('/login')
    } catch (error) {
        console.error('Logout failed:', error)
    }
}
</script>