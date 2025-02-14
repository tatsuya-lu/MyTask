import { defineStore } from 'pinia';
import dayjs from 'dayjs';
import axios from 'axios';

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
            searchQuery: '',
            dueDateFilter: null
        },
        pagination: {
            enabled: JSON.parse(localStorage.getItem('taskPaginationEnabled')) || false,
            currentPage: 1,
            perPage: 9,
            total: 0
        },
        isCustomOrder: false,
        savedOrders: [],
        currentOrderId: null,
        viewMode: localStorage.getItem('taskViewMode') || 'list',
        displaySettings: JSON.parse(localStorage.getItem('taskDisplaySettings')) || {
            title: true,
            description: true,
            priority: true,
            status: true,
            progress: {
                show: true,
                type: 'percentage'
            },
            dueDate: true,
            tags: true
        }
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

                let matchesDueDate = true;
                if (state.filters.dueDateFilter) {
                    const filter = state.filters.dueDateFilter;
                    if (!task.due_date) {
                        matchesDueDate = false;
                    } else {
                        const dueDate = dayjs(task.due_date);
                        const now = dayjs();
                        let endDate = now;

                        switch (filter.duration_unit) {
                            case 'day':
                                endDate = now.add(filter.duration_value, 'day');
                                break;
                            case 'week':
                                endDate = now.add(filter.duration_value * 7, 'day');
                                break;
                            case 'month':
                                endDate = now.add(filter.duration_value, 'month');
                                break;
                        }

                        matchesDueDate = dueDate.isBefore(endDate) || dueDate.isSame(endDate, 'day');
                    }
                }

                return matchesStatus && matchesPriority && matchesTag && matchesSearch && matchesDueDate;
            });

            if (state.isCustomOrder) {
                filtered.sort((a, b) => a.sort_order - b.sort_order);
            }

            return filtered;
        }
    },

    actions: {
        setViewMode(mode) {
            this.viewMode = mode;
            localStorage.setItem('taskViewMode', mode);
        },
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
                const queryParams = {
                    paginate: this.pagination.enabled,
                    per_page: this.pagination.perPage,
                    page: this.pagination.currentPage,
                    ...filters
                };

                // 期限日フィルターが存在する場合、JSONとしてシリアライズ
                if (filters.dueDateFilter) {
                    queryParams.due_date_filter = JSON.stringify(filters.dueDateFilter);
                }

                const response = await axios.get('/api/tasks', { params: queryParams });

                if (this.pagination.enabled) {
                    this.tasks = response.data.data;
                    this.pagination.total = response.data.meta.total;
                    this.pagination.currentPage = response.data.meta.current_page;
                } else {
                    this.tasks = response.data.data;
                }
                
                this.isCustomOrder = response.data.isCustomOrder || false;
            } catch (error) {
                console.error('Error fetching tasks:', error);
                this.error = error.response?.data?.message || 'タスクの取得に失敗しました';
            } finally {
                this.isLoading = false;
            }
        },

        setPaginationEnabled(enabled) {
            this.pagination.enabled = enabled;
            this.pagination.currentPage = 1;
            localStorage.setItem('taskPaginationEnabled', JSON.stringify(enabled));
        },

        setPage(page) {
            this.pagination.currentPage = page;
            this.fetchTasks(this.filters);
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

        async updateTaskOrder(taskIds) {
            this.isLoading = true;
            try {
                if (!Array.isArray(taskIds)) {
                    throw new Error('Invalid task order format');
                }

                const response = await axios.put('/api/tasks/order', {
                    taskOrder: taskIds,
                    isCustomOrder: true
                });

                this.tasks = this.tasks.map(task => ({
                    ...task,
                    sort_order: taskIds.indexOf(task.id)
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

        async fetchSavedOrders() {
            try {
                const response = await axios.get('/api/tasks/orders');
                this.savedOrders = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || '保存済みの並び順の取得に失敗しました';
                throw error;
            }
        },

        async saveTaskOrder(taskIds, name = null, description = null) {
            try {
                const response = await axios.post('/api/tasks/orders', {
                    taskOrder: taskIds,
                    isCustomOrder: true,
                    name,
                    description
                });
                await this.fetchSavedOrders();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || '並び順の保存に失敗しました';
                throw error;
            }
        },

        async applySavedOrder(order) {
            try {
                const orderedTasks = [];
                const taskIds = order.task_order;

                for (const taskId of taskIds) {
                    const task = this.tasks.find(t => t.id === taskId);
                    if (task) orderedTasks.push(task);
                }

                await this.updateTaskOrder(taskIds);

                this.tasks = orderedTasks;
                this.isCustomOrder = true;
                this.currentOrderId = order.id;
            } catch (error) {
                this.error = error.response?.data?.message || '並び順の適用に失敗しました';
                throw error;
            }
        },

        async updateSavedOrder(orderId, data) {
            try {
                await axios.put(`/api/tasks/orders/${orderId}`, data);
                await this.fetchSavedOrders();
            } catch (error) {
                this.error = error.response?.data?.message || '並び順の更新に失敗しました';
                throw error;
            }
        },

        async deleteSavedOrder(orderId) {
            try {
                await axios.delete(`/api/tasks/orders/${orderId}`);
                await this.fetchSavedOrders();
                if (this.currentOrderId === orderId) {
                    this.currentOrderId = null;
                    this.isCustomOrder = false;
                }
            } catch (error) {
                this.error = error.response?.data?.message || '並び順の削除に失敗しました';
                throw error;
            }
        },

        async applySort(sortType) {
            if (this.isCustomOrder && this.currentOrderId !== null) {
                const result = confirm('現在の並び順を保存しますか？');

                if (result) {
                    const name = prompt('並び順の名前を入力してください（省略可）:');
                    const description = prompt('説明を入力してください（省略可）:');

                    try {
                        await this.saveTaskOrder(
                            this.tasks.map(task => task.id),
                            name || null,
                            description || null
                        );
                    } catch (error) {
                        if (error.response?.status !== 422) {
                            return;
                        }
                    }
                }
            }

            const sortedTasks = [...this.tasks];
            switch (sortType) {
                case 'created_desc':
                    sortedTasks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    break;
                case 'due_date':
                    sortedTasks.sort((a, b) => {
                        if (!a.due_date) return 1;
                        if (!b.due_date) return -1;
                        return new Date(a.due_date) - new Date(b.due_date);
                    });
                    break;
            }

            try {
                const taskIds = sortedTasks.map(task => task.id);
                await this.updateTaskOrder(taskIds);

                this.tasks = sortedTasks;
                this.isCustomOrder = false;
                this.currentOrderId = null;
            } catch (error) {
                this.error = error.response?.data?.message || 'ソートの適用に失敗しました';
                throw error;
            }
        },

        updateDisplaySettings(settings) {
            this.displaySettings = settings;
            localStorage.setItem('taskDisplaySettings', JSON.stringify(settings));
        }
    }
});