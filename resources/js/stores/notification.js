import { defineStore } from 'pinia'

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
                const token = localStorage.getItem('token')
                if (token) {
                    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
                }

                const response = await axios.get('/api/notifications')
                this.notifications = response.data.notifications
                this.unreadCount = response.data.unread_count
            } catch (error) {
                this.error = error.response?.data?.message || '通知の取得に失敗しました'
                throw error
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