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
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
    try {
        await authStore.logout()
        router.push('/login')
    } catch (error) {
        console.error('Logout failed:', error)
    }
}
</script>