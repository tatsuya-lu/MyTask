<template>
    <div class="max-w-4xl mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">通知一覧</h2>
            <button @click="notificationStore.markAllAsRead"
                class="px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800">
                すべて既読にする
            </button>
        </div>

        <div v-if="notificationStore.loading" class="text-center py-8">
            <div class="spinner">読み込み中...</div>
        </div>

        <div v-else-if="notificationStore.notifications.length === 0" class="text-center py-8 text-gray-500">
            通知はありません
        </div>

        <div v-else class="space-y-4">
            <div v-for="notification in notificationStore.notifications" :key="notification.id" :class="[
                'p-4 rounded-lg border',
                notification.is_read ? 'bg-white' : 'bg-indigo-50'
            ]" @click="notificationStore.markAsRead(notification.id)">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-medium text-gray-900">
                            {{ notification.title }}
                        </h3>
                        <p class="mt-1 text-gray-600">
                            {{ notification.content }}
                        </p>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ new Date(notification.created_at).toLocaleString() }}
                        </p>
                    </div>
                    <div v-if="!notification.is_read" class="w-2 h-2 bg-blue-600 rounded-full">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'

const notificationStore = useNotificationStore()

onMounted(() => {
    notificationStore.fetchNotifications()
})
</script>