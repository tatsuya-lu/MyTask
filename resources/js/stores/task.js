import { defineStore } from 'pinia'
import { api } from '../utils/axios'

export const useTaskStore = defineStore('task', {
    state: () => ({
        tasks: [],
        currentTask: null,
        isLoading: false,
        error: null
    }),

    actions: {
        async fetchTasks() {
            this.isLoading = true;
            try {
                const response = await api.get('/tasks');
                this.tasks = response.data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'タスクの取得に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async createTask(taskData) {
            this.isLoading = true;
            try {
                const response = await api.post('/tasks', taskData);
                this.tasks.push(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'タスクの作成に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async updateTask(taskId, taskData) {
            this.isLoading = true;
            try {
                const response = await api.put(`/tasks/${taskId}`, taskData);
                const index = this.tasks.findIndex(task => task.id === taskId);
                if (index !== -1) {
                    this.tasks[index] = response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'タスクの更新に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async deleteTask(taskId) {
            this.isLoading = true;
            try {
                await api.delete(`/tasks/${taskId}`);
                this.tasks = this.tasks.filter(task => task.id !== taskId);
            } catch (error) {
                this.error = error.response?.data?.message || 'タスクの削除に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        }
    }
});