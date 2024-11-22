import { defineStore } from 'pinia'

export const useTeamStore = defineStore('team', {
    state: () => ({
        teams: [],
        currentTeam: null,
        isLoading: false,
        error: null
    }),

    actions: {
        async fetchTeams() {
            this.isLoading = true;
            try {
                const response = await api.get('/teams');
                this.teams = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'チームの取得に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async createTeam(teamData) {
            this.isLoading = true;
            try {
                const response = await api.post('/teams', teamData);
                this.teams.push(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'チームの作成に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async updateTeam(teamId, teamData) {
            this.isLoading = true;
            try {
                const response = await api.put(`/teams/${teamId}`, teamData);
                const index = this.teams.findIndex(team => team.id === teamId);
                if (index !== -1) {
                    this.teams[index] = response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'チームの更新に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async deleteTeam(teamId) {
            this.isLoading = true;
            try {
                await api.delete(`/teams/${teamId}`);
                this.teams = this.teams.filter(team => team.id !== teamId);
            } catch (error) {
                this.error = error.response?.data?.message || 'チームの削除に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async addMember(teamId, userData) {
            try {
                await api.post(`/teams/${teamId}/members`, userData);
                await this.fetchTeams();
            } catch (error) {
                this.error = error.response?.data?.message || 'メンバーの追加に失敗しました';
                throw error;
            }
        },

        async removeMember(teamId, userId) {
            try {
                await api.delete(`/teams/${teamId}/members/${userId}`);
                await this.fetchTeams();
            } catch (error) {
                this.error = error.response?.data?.message || 'メンバーの削除に失敗しました';
                throw error;
            }
        }
    }
});