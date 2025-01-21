import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)

    const fetchNotifications = async () => {
        loading.value = true
        try {
            const response = await axios.get('/api/notifications')
            notifications.value = response.data.notifications
            unreadCount.value = response.data.unread_count
        } catch (error) {
            console.error('通知の取得に失敗しました:', error)
        } finally {
            loading.value = false
        }
    }

    const markAsRead = async (notificationId) => {
        try {
            await axios.put(`/api/notifications/${notificationId}/read`)
            const notification = notifications.value.find(n => n.id === notificationId)
            if (notification && !notification.is_read) {
                notification.is_read = true
                unreadCount.value--
            }
        } catch (error) {
            console.error('通知の既読処理に失敗しました:', error)
        }
    }

    const markAllAsRead = async () => {
        try {
            await axios.post('/api/notifications/mark-all-read')
            notifications.value.forEach(notification => {
                notification.is_read = true
            })
            unreadCount.value = 0
        } catch (error) {
            console.error('全既読処理に失敗しました:', error)
        }
    }

    return {
        notifications,
        unreadCount,
        loading,
        fetchNotifications,
        markAsRead,
        markAllAsRead
    }
})