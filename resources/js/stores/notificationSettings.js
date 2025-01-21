import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationSettingsStore = defineStore('notificationSettings', () => {
    const settings = ref({
        email_notifications_enabled: true,
        in_app_notifications_enabled: true,
        notification_timing: [1, 3, 7]
    })
    const loading = ref(false)
    const saving = ref(false)

    const fetchSettings = async () => {
        loading.value = true
        try {
            const response = await axios.get('/api/notification-settings')
            settings.value = response.data
        } catch (error) {
            console.error('設定の取得に失敗しました:', error)
        } finally {
            loading.value = false
        }
    }

    const saveSettings = async () => {
        saving.value = true
        try {
            await axios.put('/api/notification-settings', settings.value)
            return true
        } catch (error) {
            console.error('設定の保存に失敗しました:', error)
            return false
        } finally {
            saving.value = false
        }
    }

    return {
        settings,
        loading,
        saving,
        fetchSettings,
        saveSettings
    }
})