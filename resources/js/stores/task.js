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
        },
        isCustomOrder: false
    }),

    getters: {
        filteredTasks: (state) => {
            let filtered = state.tasks.filter(task => {
                const matchesStatus = !state.filters.status || task.status === state.filters.status;
                const matchesPriority = !state.filters.priority || task.priority === state.filters.priority;
                const matchesTag = !state.filters.tagId || task.tags.some(tag => tag.id === parseInt(state.filters.tagId));
                const matchesSearch = !state.filters.searchQuery ||
                    task.title.toLowerCase().includes(state.filters.searchQuery.toLowerCase()) ||
                    task.description.toLowerCase().includes(state.filters.searchQuery.toLowerCase());

                return matchesStatus && matchesPriority && matchesTag && matchesSearch;
            });

            if (state.isCustomOrder) {
                filtered.sort((a, b) => a.sort_order - b.sort_order);
            }

            return filtered;
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
                this.tasks = response.data.data.map(task => ({
                    ...task,
                    tags: task.tags || []
                }));
                // レスポンスからカスタム並び順の状態を設定
                this.isCustomOrder = response.data.isCustomOrder || false;
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
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.post('/api/tasks', taskData);
                const newTask = response.data.data || response.data;
                this.tasks.push(newTask);
                return newTask;
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
        },

        async updateTaskOrder(newOrder) {
            this.isLoading = true;
            try {
                const taskIds = newOrder.map(task => task.id);
                const response = await axios.put('/api/tasks/order', {
                    taskOrder: taskIds,
                    isCustomOrder: true
                });

                // 成功したら状態を更新
                this.tasks = newOrder.map((task, index) => ({
                    ...task,
                    sort_order: index
                }));
                this.isCustomOrder = true;
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'タスクの並び順の更新に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async applySort(sortType) {
            if (this.isCustomOrder) {
                const result = confirm('現在の並び順を保存しますか？キャンセルを押した場合現在の並び順は削除されます。');

                if (result) {
                    return;
                }
            }

            // ソートを適用
            const sortedTasks = [...this.tasks];
            switch (sortType) {
                case 'created_desc':
                    sortedTasks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    break;
                case 'due_date':
                    sortedTasks.sort((a, b) => new Date(a.due_date) - new Date(b.due_date));
                    break;
            }

            // ソートされた順序でタスクIDの配列を作成
            const taskIds = sortedTasks.map(task => task.id);

            try {
                // サーバーに新しい順序を送信
                await axios.put('/api/tasks/order', {
                    taskOrder: taskIds,
                    isCustomOrder: false
                });

                // 状態を更新
                this.tasks = sortedTasks;
                this.isCustomOrder = false;
            } catch (error) {
                this.error = error.response?.data?.message || 'ソートの適用に失敗しました';
                throw error;
            }
        }
    }
});