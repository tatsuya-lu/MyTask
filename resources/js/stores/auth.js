import { defineStore } from "pinia";
import { api } from '../utils/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: null
    }),
    
    actions: {
        async login(credentials) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.post('/login', credentials);
                this.user = response.data.user;
                this.isAuthenticated = true;
                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'ログインに失敗しました';
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async register(userData) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.post('/register', userData);
                this.user = response.data.user;
                this.isAuthenticated = true;
                return true;
            } catch (error) {
                this.error = error.response?.data?.message || '登録に失敗しました';
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            try {
                await api.post('/logout');
                this.user = null;
                this.isAuthenticated = false;
                return true;
            } catch (error) {
                console.error('Logout failed:', error);
                return false;
            }
        },

        async checkAuth() {
            try {
                const response = await api.get('/api/user');
                this.user = response.data;
                this.isAuthenticated = true;
                return true;
            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;
                return false;
            }
        }
    },

    getters: {
        currentUser: (state) => state.user,
        authError: (state) => state.error
    }
});