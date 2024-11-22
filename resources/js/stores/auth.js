import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null
    }),
    
    actions: {
        async register(userData) {
            try {
                const response = await axios.post('/api/auth/register', userData)
                return response.data
            } catch (error) {
                throw error.response.data
            }
        },

        async login(credentials) {
            try {
                const response = await axios.post('/api/auth/login', credentials)
                this.user = response.data.user
                this.token = response.data.token
                localStorage.setItem('token', this.token)
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
                return response.data
            } catch (error) {
                throw error.response.data
            }
        },

        async logout() {
            try {
                await axios.post('/api/auth/logout')
                this.user = null
                this.token = null
                localStorage.removeItem('token')
                delete axios.defaults.headers.common['Authorization']
            } catch (error) {
                throw error.response.data
            }
        },

        async fetchUser() {
            if (!this.token) return

            try {
                const response = await axios.get('/api/auth/profile')
                this.user = response.data.user
            } catch (error) {
                this.logout()
            }
        }
    },

    getters: {
        isAuthenticated() {
            return !!this.token
        }
    }
})