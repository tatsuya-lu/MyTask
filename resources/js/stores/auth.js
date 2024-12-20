import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token
    },

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
            } catch (error) {
                console.warn('Logout error:', error)
            } finally {
                this.user = null
                this.token = null
                localStorage.removeItem('token')
                delete axios.defaults.headers.common['Authorization']

                window.location.href = '/login'
            }
        },

        async fetchUser() {
            if (!this.token) return false;

            try {
                await axios.get('/sanctum/csrf-cookie');
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                const response = await axios.get('/api/auth/profile');
                this.user = response.data.user;
                return true;
            } catch (error) {
                console.error('ユーザー情報取得エラー:', error);
                this.token = null;
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
                this.user = null;
                return false;
            }
        }
    },

    getters: {
        isAuthenticated() {
            return !!this.token
        }
    }
})