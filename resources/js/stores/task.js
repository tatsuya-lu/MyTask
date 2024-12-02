import { defineStore } from 'pinia'

export const useTaskStore = defineStore('task', {
    state: () => ({
        tasks: [],
        currentTask: null,
        isLoading: false,
        error: null,
        filters: {
            status: '',
            priority: '',
            tagId: '',
            searchQuery: ''
        }
    }),

    getters: {
        filteredTasks: (state) => {
            return state.tasks.filter(task => {
                const matchesStatus = !state.filters.status || task.status === state.filters.status;
                const matchesPriority = !state.filters.priority || task.priority === state.filters.priority;
                const matchesTag = !state.filters.tagId || task.tags.some(tag => tag.id === parseInt(state.filters.tagId));
                const matchesSearch = !state.filters.searchQuery ||
                    task.title.toLowerCase().includes(state.filters.searchQuery.toLowerCase()) ||
                    task.description.toLowerCase().includes(state.filters.searchQuery.toLowerCase());

                return matchesStatus && matchesPriority && matchesTag && matchesSearch;
            });
        }
    },

    actions: {
        setFilter(filterType, value) {
            this.filters[filterType] = value;
        },

        clearFilters() {
            this.filters = {
                status: '',
                priority: '',
                tagId: '',
                searchQuery: ''
            };
        },

        async fetchTasks(filters = {}) {
            this.isLoading = true;
            try {
                const queryParams = new URLSearchParams(filters).toString();
                const response = await axios.get(`/api/tasks?${queryParams}`);
                console.log('タスクレスポンス:', response.data);  // レスポンスの中身を確認
                this.tasks = response.data.data.map(task => ({
                    ...task,
                    tags: task.tags || []
                }));
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
                // CSRFトークンを明示的に取得
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.post('/api/tasks', taskData);
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
                const response = await axios.put(`/api/tasks/${taskId}`, taskData);
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
                await axios.delete(`/api/tasks/${taskId}`);
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