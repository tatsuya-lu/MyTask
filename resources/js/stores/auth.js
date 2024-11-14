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
                await api.get('/sanctum/csrf-cookie');
                
                const response = await api.post('/login', credentials);
                this.user = response.data.user;
                this.isAuthenticated = true;
                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'ログインに失敗しました';
                throw new Error(this.error);
            } finally {
                this.isLoading = false;
            }
        },

        async register(userData) {
            this.isLoading = true;
            this.error = null;
            try {
                await api.get('/sanctum/csrf-cookie');
                
                const response = await api.post('/register', userData);
                this.user = response.data.user;
                this.isAuthenticated = true;
                return true;
            } catch (error) {
                this.error = error.response?.data?.message || '登録に失敗しました';
                throw new Error(this.error);
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            try {
                await api.post('/logout');
            } catch (error) {
                console.error('Logout failed:', error);
            } finally {
                this.user = null;
                this.isAuthenticated = false;
            }
        },

        async checkAuth() {
            try {
                const response = await api.get('/user');
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