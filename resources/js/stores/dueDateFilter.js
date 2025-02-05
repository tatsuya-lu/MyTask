import { defineStore } from 'pinia';
import axios from 'axios';

export const useDueDateFilterStore = defineStore('dueDateFilter', {
    state: () => ({
        customFilters: [],
        selectedFilter: null,
        isLoading: false,
        error: null
    }),

    getters: {
        defaultFilters: () => [
            { id: 'week', name: '1週間以内', duration_value: 1, duration_unit: 'week' },
            { id: 'month', name: '1ヶ月以内', duration_value: 1, duration_unit: 'month' }
        ]
    },

    actions: {
        async fetchCustomFilters() {
            this.isLoading = true;
            try {
                const response = await axios.get('/api/due-date-filters');
                this.customFilters = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'フィルターの取得に失敗しました';
            } finally {
                this.isLoading = false;
            }
        },

        async createFilter(filterData) {
            this.isLoading = true;
            try {
                const response = await axios.post('/api/due-date-filters', filterData);
                this.customFilters.unshift(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'フィルターの作成に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async deleteFilter(filterId) {
            this.isLoading = true;
            try {
                await axios.delete(`/api/due-date-filters/${filterId}`);
                this.customFilters = this.customFilters.filter(f => f.id !== filterId);
            } catch (error) {
                this.error = error.response?.data?.message || 'フィルターの削除に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        }
    }
});