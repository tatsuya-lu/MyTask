<template>
    <div class="max-w-2xl mx-auto p-4">
        <h2 class="text-2xl font-bold mb-6">通知設定</h2>

        <div v-if="loading" class="text-center">
            <div class="spinner">読み込み中...</div>
        </div>

        <form v-else @submit.prevent="saveSettings" class="space-y-6">
            <!-- メール通知設定 -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">メール通知</h3>
                <div class="flex items-center justify-between">
                    <label class="text-gray-700">メール通知を有効にする</label>
                    <toggle-switch v-model="notificationSettingsStore.settings.email_notifications_enabled" class="ml-4" />
                </div>
            </div>

            <!-- アプリ内通知設定 -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">アプリ内通知</h3>
                <div class="flex items-center justify-between">
                    <label class="text-gray-700">アプリ内通知を有効にする</label>
                    <toggle-switch v-model="settings.in_app_notifications_enabled" class="ml-4" />
                </div>
            </div>

            <!-- 通知タイミング設定 -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">通知タイミング</h3>
                <div class="space-y-3">
                    <div class="flex flex-wrap gap-3">
                        <label v-for="days in availableDays" :key="days" class="inline-flex items-center">
                            <input type="checkbox" :value="days" v-model="settings.notification_timing"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2">
                                {{ days }}日前
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 保存ボタン -->
            <div class="flex justify-end">
                <button type="submit" :disabled="saving"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                    {{ saving ? '保存中...' : '設定を保存' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useNotificationSettingsStore } from '@/stores/notificationSettings'
import ToggleSwitch from '@/components/Common/ToggleSwitch.vue'

const notificationSettingsStore = useNotificationSettingsStore()
const availableDays = [1, 2, 3, 5, 7, 14, 30]

const saveSettings = async () => {
    const success = await notificationSettingsStore.saveSettings()
    if (success) {
        alert('設定を保存しました')
    } else {
        alert('設定の保存に失敗しました')
    }
}

onMounted(() => {
    notificationSettingsStore.fetchSettings()
})
</script>