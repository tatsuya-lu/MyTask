import { defineStore } from 'pinia'
import { useAuthStore } from '@/stores/auth'

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        loading: false,
        error: null
    }),

    getters: {
        hasUnreadNotifications: (state) => state.unreadCount > 0,
        sortedNotifications: (state) => [...state.notifications].sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at)
        })
    },

    actions: {
        async fetchNotifications() {
            this.loading = true
            try {
                const authStore = useAuthStore()
                if (!authStore.isAuthenticated) {
                    throw new Error('認証が必要です')
                }

                const response = await axios.get('/api/notifications', {
                    headers: {
                        'Authorization': `Bearer ${authStore.token}`
                    }
                })

                this.notifications = response.data.notifications.data || []
                this.unreadCount = response.data.unread_count || 0
                this.error = null
            } catch (error) {
                console.error('Notification fetch error:', error);
                this.notifications = [];
                this.unreadCount = 0;
                this.error = error.response?.status === 401
                    ? '認証が必要です。再度ログインしてください。'
                    : error.response?.data?.message || '通知の取得に失敗しました';
            } finally {
                this.loading = false
            }
        },

        async markAsRead(notificationId) {
            try {
                await axios.put(`/api/notifications/${notificationId}/read`)
                const notification = this.notifications.find(n => n.id === notificationId)
                if (notification && !notification.is_read) {
                    notification.is_read = true
                    this.unreadCount--
                }
            } catch (error) {
                this.error = error.response?.data?.message || '通知の既読処理に失敗しました'
                throw error
            }
        },

        async markAllAsRead() {
            try {
                await axios.post('/api/notifications/mark-all-read')
                this.notifications.forEach(notification => {
                    notification.is_read = true
                })
                this.unreadCount = 0
            } catch (error) {
                this.error = error.response?.data?.message || '全既読処理に失敗しました'
                throw error
            }
        },

        clearError() {
            this.error = null
        }
    }
})