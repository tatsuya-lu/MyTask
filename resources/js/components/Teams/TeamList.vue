<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">チーム管理</h2>
            <button @click="showCreateModal = true"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :disabled="!canCreateTeam">
                新規チーム作成
            </button>
        </div>

        <!-- チーム数制限の警告 -->
        <div v-if="showTeamLimitWarning" class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
            <p class="text-yellow-700">
                無料プランでは最大3つまでのチームを作成できます。
                <a href="/pricing" class="underline">プレミアムプランにアップグレード</a>
                すると、より多くのチームを作成できます。
            </p>
        </div>

        <!-- チーム一覧 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="team in teamStore.teams" :key="team.id" class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <!-- チーム名をリンクに変更 -->
                        <router-link :to="{ name: 'team-detail', params: { id: team.id } }"
                            class="text-xl font-bold text-blue-600 hover:text-blue-800">
                            {{ team.name }}
                        </router-link>
                        <p class="text-sm text-gray-500">
                            オーナー: {{ team.owner.name }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button v-if="team.owner_id === authStore.user?.id" @click="editTeam(team)"
                            class="text-blue-500 hover:text-blue-700">
                            編集
                        </button>
                        <button v-if="team.owner_id === authStore.user?.id" @click="confirmDelete(team)"
                            class="text-red-500 hover:text-red-700">
                            削除
                        </button>
                    </div>
                </div>

                <p class="text-gray-600 mb-4">{{ team.description }}</p>

                <!-- メンバー一覧セクション -->
                <div class="mb-4">
                    <h4 class="font-semibold mb-2">メンバー</h4>
                    <div class="space-y-2">
                        <div v-for="member in team.members" :key="member.id" class="flex justify-between items-center">
                            <span>{{ member.name }}</span>
                            <button v-if="team.owner_id === authStore.user?.id && member.id !== team.owner_id"
                                @click="removeMember(team.id, member.id)"
                                class="text-red-500 hover:text-red-700 text-sm">
                                削除
                            </button>
                        </div>
                    </div>
                </div>

                <!-- メンバー追加ボタン -->
                <button v-if="team.owner_id === authStore.user?.id" @click="handleAddMemberClick(team)"
                    class="text-blue-500 hover:text-blue-700 text-sm">
                    メンバーを追加
                </button>
            </div>
        </div>

        <!-- チーム作成/編集モーダル -->
        <TeamFormModal v-if="showCreateModal || showEditModal" :team="editingTeam" :is-editing="showEditModal"
            @close="closeModal" @submit="handleTeamSubmit" />

        <!-- メンバー追加モーダル -->
        <AddMemberModal v-if="showAddMemberModal && selectedTeam" :team="selectedTeam" @close="closeAddMemberModal"
            @submit="handleAddMember" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTeamStore } from '../../stores/team';
import { useAuthStore } from '../../stores/auth';
import TeamFormModal from './TeamFormModal.vue';
import AddMemberModal from './AddMemberModal.vue';

const router = useRouter();
const teamStore = useTeamStore();
const authStore = useAuthStore();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingTeam = ref(null);
const selectedTeam = ref(null);
const showAddMemberModal = ref(false);

// チーム作成可能かどうかを判定
const canCreateTeam = computed(() => {
    const ownedTeamsCount = teamStore.teams.filter(
        team => team.owner_id === authStore.user?.id
    ).length;
    return authStore.user?.is_premium || ownedTeamsCount < 3;
});

// チーム数制限の警告表示判定
const showTeamLimitWarning = computed(() => {
    const ownedTeamsCount = teamStore.teams.filter(
        team => team.owner_id === authStore.user?.id
    ).length;
    return !authStore.user?.is_premium && ownedTeamsCount >= 3;
});

const closeModal = () => {
    showCreateModal.value = false
    showEditModal.value = false
    editingTeam.value = null
}

const editTeam = (team) => {
    editingTeam.value = team
    showEditModal.value = true
}

const handleTeamSubmit = async (formData) => {
    try {
        let team;
        if (showEditModal.value) {
            team = await teamStore.updateTeam(editingTeam.value.id, formData);
        } else {
            team = await teamStore.createTeam(formData);
        }
        closeModal();

        try {
            await teamStore.fetchTeams();
        } catch (error) {
            console.error('チーム一覧の更新に失敗しました:', error);
        }

        if (team && team.id) {
            router.push({ name: 'team-detail', params: { id: team.id } });
        }
    } catch (error) {
        console.error('チームの保存に失敗しました:', error);
    }
};

const handleAddMember = async (memberData) => {
    if (!selectedTeam.value) return;

    try {
        console.log('Adding member:', memberData); // デバッグ用
        await teamStore.addMember(selectedTeam.value.id, memberData);
        await teamStore.fetchTeams(); // チーム一覧を更新
        closeAddMemberModal();
    } catch (error) {
        console.error('メンバーの追加に失敗しました:', error);
    }
};

const handleAddMemberClick = (team) => {
    selectedTeam.value = team;
    showAddMemberModal.value = true;
};

const closeAddMemberModal = () => {
    showAddMemberModal.value = false;
    selectedTeam.value = null;
};

const removeMember = async (teamId, memberId) => {
    if (!confirm('このメンバーを削除しますか？')) return

    try {
        await teamStore.removeMember(teamId, memberId)
    } catch (error) {
        console.error('メンバーの削除に失敗しました:', error)
    }
}

const confirmDelete = async (team) => {
    if (!confirm('このチームを削除しますか？この操作は取り消せません。')) return

    try {
        await teamStore.deleteTeam(team.id)
    } catch (error) {
        console.error('チームの削除に失敗しました:', error)
    }
}

onMounted(async () => {
    try {
        await teamStore.fetchTeams();
    } catch (error) {
        console.error('チーム一覧の取得に失敗しました:', error);
    }
});
</script>