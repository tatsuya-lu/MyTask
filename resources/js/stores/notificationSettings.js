import { defineStore } from 'pinia'

export const useNotificationSettingsStore = defineStore('notificationSettings', {
    state: () => ({
        settings: {
            email_notifications_enabled: true,
            in_app_notifications_enabled: true,
            notification_timing: [1, 3, 7]
        },
        loading: false,
        saving: false,
        error: null
    }),

    getters: {
        isEmailEnabled: (state) => state.settings.email_notifications_enabled,
        isInAppEnabled: (state) => state.settings.in_app_notifications_enabled,
        notificationDays: (state) => state.settings.notification_timing
    },

    actions: {
        async fetchSettings() {
            this.loading = true
            try {
                const response = await axios.get('/api/notification-settings')
                this.settings = response.data
            } catch (error) {
                this.error = error.response?.data?.message || '設定の取得に失敗しました'
                throw error
            } finally {
                this.loading = false
            }
        },

        async saveSettings() {
            this.saving = true
            try {
                await axios.put('/api/notification-settings', this.settings)
                return true
            } catch (error) {
                this.error = error.response?.data?.message || '設定の保存に失敗しました'
                throw error
            } finally {
                this.saving = false
            }
        },

        updateSettings(newSettings) {
            this.settings = {
                ...this.settings,
                ...newSettings
            }
        },

        clearError() {
            this.error = null
        }
    }
})