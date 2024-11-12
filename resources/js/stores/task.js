import { defineStore } from 'pinia'
import axios from 'axios'

export const useTaskStore = defineStore('task', {
    state: () => ({
        tasks: []
    }),

    actions: {
        async fetchTasks() {
            const response = await axios.get('/api/tasks')
            this.tasks = response.data.data
            return this.tasks
        },

        async createTask(taskData) {
            const response = await axios.post('/api/tasks', taskData)
            return response.data
        },

        async updateTask(taskId, taskData) {
            const response = await axios.put(`/api/tasks/${taskId}`, taskData)
            return response.data
        },

        async deleteTask(taskId) {
            await axios.delete(`/api/tasks/${taskId}`)
        }
    }
})