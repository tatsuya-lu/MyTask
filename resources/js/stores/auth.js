import { defineStore } from "pinia";
import axios from "axios";

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isLoaded: false
    }),

    getters: {
        isLoggedIn: (state) => !!state.user
    },

    actions: {
        // async register(userData) {
        //     try {
        //         await axios.get('/sanctum/csrf-cookie');
        //         const response = await axios.post('/api/register', userData);
        
        //         if (response.data.token) {
        //             this.token = response.data.token;
        //             this.isAuthenticated = true;
        //             localStorage.setItem('token', this.token);
        //             axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        //             await this.fetchUser();
        //             return true;
        //         }
                
        //         throw new Error('Registration failed: No token received');
        //     } catch (error) {
        //         console.error('Registration error:', error);
        //         this.clearUser();
        //         throw error;
        //     }
        // },

        async checkAuth() {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    this.clearUser();
                    return false;
                }

                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

                const response = await axios.get('/api/user');
                if (response.data) {
                    this.user = response.data;
                    this.isAuthenticated = true;
                    this.isLoaded = true;
                    return true;
                }

                this.clearUser();
                return false;
            } catch (error) {
                console.error('Auth check failed:', error);
                this.clearUser();
                return false;
            } finally {
                this.isLoaded = true;
            }
        },

        async fetchUser() {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    this.clearUser();
                    return;
                }

                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                const response = await axios.get('/api/user');
                this.setUser(response.data);
            } catch (error) {
                console.error('Failed to fetch user:', error);
                this.clearUser();
            }
        },

        async login(credentials) {
            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.post('/api/login', credentials);

                this.token = response.data.token;
                localStorage.setItem('token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                await this.fetchUser();
                return true;
            } catch (error) {
                this.clearUser();
                throw error;
            }
        },

        setUser(userData) {
            this.user = userData;
            this.isLoaded = true;
        },

        clearUser() {
            this.user = null;
            this.isLoaded = true;
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
        },

        async logout() {
            try {
                if (this.user) {
                    await axios.post('/api/logout');
                }
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.clearUser();
                window.location.href = '/login';
            }
        }
    }
});