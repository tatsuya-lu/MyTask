<template>
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <router-link :to="{ name: 'tasks' }" class="flex items-center">
                        <span class="text-xl font-bold">MyTask</span>
                    </router-link>
                </div>

                <div v-if="isLoggedIn" class="flex items-center space-x-4">
                    <router-link :to="{ name: 'task-create' }" class="button">
                        新規タスク
                    </router-link>
                    <router-link :to="{ name: 'task-calendar' }" class="button">
                        カレンダー
                    </router-link>
                    <button @click="handleLogout" class="button">ログアウト</button>
                </div>
                <div v-else>
                    <router-link :to="{ name: 'login' }" class="button">
                        ログイン
                    </router-link>
                    <router-link :to="{ name: 'register' }" class="button">
                        新規登録
                    </router-link>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const isLoggedIn = computed(() => authStore.isAuthenticated)

const handleLogout = async () => {
    await authStore.logout()
    router.push({ name: 'login' })
}
</script>