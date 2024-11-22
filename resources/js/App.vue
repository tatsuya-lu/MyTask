<template>
    <div class="min-h-screen bg-gray-100">
        <AppHeader v-if="authStore.token" />
        <main class="container mx-auto px-4 py-6">
            <router-view></router-view>
        </main>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppHeader from './components/Layouts/AppHeader.vue'

const authStore = useAuthStore()

onMounted(() => {
    if (authStore.token) {
        authStore.fetchUser()
    }
})
</script>