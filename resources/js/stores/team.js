import { defineStore } from 'pinia'
import axios from 'axios'

export const useTeamStore = defineStore('team', {
    state: () => ({
        teams: [],
        currentTeam: null,
        isLoading: false,
        error: null,
        leaderRoleId: 1
    }),
    getters: {
        ownedTeamsCount: (state) => {
            return state.teams.filter(team =>
                team.owner_id === auth.user?.id
            ).length;
        },
        canCreateTeam: (state) => {
            const user = auth.user;
            if (!user) return false;
            return user.is_premium || state.teams.filter(team =>
                team.owner_id === user.id
            ).length < 3;
        }
    },
    actions: {
        async fetchTeam(teamId) {
            this.isLoading = true;
            try {
                const response = await axios.get(`/api/teams/${teamId}`);
                this.currentTeam = response.data;
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'チームの取得に失敗しました';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },
        async fetchTeams() {
            this.isLoading = true;
            try {
                const response = await axios.get('/api/teams');
                if (response.data) {
                    this.teams = response.data;
                    return response.data;
                }
                throw new Error('チームデータが取得できませんでした');
            } catch (error) {
                this.error = error.response?.data?.message || 'チームの取得に失敗しました';
                console.error('fetchTeams error:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },
        async createTeam(teamData) {
            this.isLoading = true;
            try {
                const response = await axios.post('/api/teams', teamData);
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
                const response = await axios.put(`/api/teams/${teamId}`, teamData);
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
                await axios.delete(`/api/teams/${teamId}`);
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
                console.log('Store addMember:', teamId, userData);
                const response = await axios.post(`/api/teams/${teamId}/members`, userData);
                if (!response.data) {
                    throw new Error('メンバーの追加に失敗しました');
                }
                await this.fetchTeams();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'メンバーの追加に失敗しました';
                console.error('addMember error:', error);
                throw error;
            }
        },

        async removeMember(teamId, userId) {
            try {
                await axios.delete(`/api/teams/${teamId}/members/${userId}`);
                await this.fetchTeams();
            } catch (error) {
                this.error = error.response?.data?.message || 'メンバーの削除に失敗しました';
                throw error;
            }
        }
    }
});