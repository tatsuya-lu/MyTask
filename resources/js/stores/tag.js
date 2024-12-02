import axios from 'axios';
import { defineStore } from 'pinia'

export const useTagStore = defineStore('tag', {
    state: () => ({
        tags: [],
        isLoading: false,
        error: null
    }),

    actions: {
        async fetchTags() {
            this.isLoading = true;
            try {
                const response = await axios.get('/api/tags');
                console.log('タグレスポンス:', response.data);  // レスポンスの中身を確認
                this.tags = response.data;
            } catch (error) {
                console.error('タグ取得エラー:', error);
                this.error = error.response?.data?.message || 'タグの取得に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async createTag(tagData) {
            this.isLoading = true;
            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.post('/api/tags', tagData);
                this.tags.push(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'タグの作成に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async updateTag(tagId, tagData) {
            this.isLoading = true;
            try {
                const response = await api.put(`/tags/${tagId}`, tagData);
                const index = this.tags.findIndex(tag => tag.id === tagId);
                if (index !== -1) {
                    this.tags[index] = response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'タグの更新に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async deleteTag(tagId) {
            this.isLoading = true;
            try {
                await api.delete(`/tags/${tagId}`);
                this.tags = this.tags.filter(tag => tag.id !== tagId);
            } catch (error) {
                this.error = error.response?.data?.message || 'タグの削除に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        }
    }
});