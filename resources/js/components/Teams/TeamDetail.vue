<template>
    <div class="container mx-auto px-4 py-8">
        <!-- チーム情報ヘッダー -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold mb-2">{{ team?.name }}</h1>
                    <p class="text-gray-600">{{ team?.description }}</p>
                </div>
                <div class="flex space-x-4">
                    <!-- チームリーダーの場合のみ表示 -->
                    <button v-if="isTeamLeader" @click="showEditModal = true"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        チーム編集
                    </button>
                    <button v-if="isTeamLeader" @click="showAddMemberModal = true"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        メンバー追加
                    </button>
                </div>
            </div>

            <!-- メンバー一覧 -->
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-4">チームメンバー</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="member in team?.members" :key="member.id"
                        class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <div>
                            <span class="font-medium">{{ member.name }}</span>
                            <span v-if="member.pivot.role_id === leaderRoleId"
                                class="ml-2 text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                リーダー
                            </span>
                        </div>
                        <!-- チームリーダーの場合のみ表示 -->
                        <div v-if="isTeamLeader && member.id !== team?.owner_id" class="flex space-x-2">
                            <button @click="changeLeader(member.id)" v-if="member.pivot.role_id !== leaderRoleId"
                                class="text-blue-600 hover:text-blue-800">
                                リーダーに設定
                            </button>
                            <button @click="removeMember(member.id)" class="text-red-600 hover:text-red-800">
                                削除
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- タスク一覧やその他のコンテンツ -->
        <router-view></router-view>

        <!-- モーダル -->
        <TeamFormModal v-if="showEditModal" :team="team" :is-editing="true" @close="showEditModal = false"
            @submit="handleTeamUpdate" />

        <AddMemberModal v-if="showAddMemberModal" :team="team" @close="showAddMemberModal = false"
            @submit="handleAddMember" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useTeamStore } from '@/stores/team';
import { useAuthStore } from '@/stores/auth';
import TeamFormModal from './TeamFormModal.vue';
import AddMemberModal from './AddMemberModal.vue';

const route = useRoute();
const router = useRouter();
const teamStore = useTeamStore();
const authStore = useAuthStore();

const showEditModal = ref(false);
const showAddMemberModal = ref(false);
const leaderRoleId = ref(1); // リーダーのrole_id（実際の値に応じて調整）

// チーム情報の取得
const team = computed(() => teamStore.currentTeam);

// チームリーダーかどうかの判定
const isTeamLeader = computed(() => {
    if (!team.value || !authStore.user) return false;
    return team.value.members.some(member =>
        member.id === authStore.user.id &&
        member.pivot.role_id === leaderRoleId.value
    );
});

// 初期データの取得
onMounted(async () => {
    try {
        await teamStore.fetchTeam(route.params.id);
    } catch (error) {
        console.error('チーム情報の取得に失敗しました:', error);
        router.push('/teams');
    }
});

// チーム更新
const handleTeamUpdate = async (formData) => {
    try {
        await teamStore.updateTeam(team.value.id, formData);
        showEditModal.value = false;
    } catch (error) {
        console.error('チームの更新に失敗しました:', error);
    }
};

// メンバー追加
const handleAddMember = async (memberData) => {
    try {
        await teamStore.addMember(team.value.id, memberData);
        showAddMemberModal.value = false;
    } catch (error) {
        console.error('メンバーの追加に失敗しました:', error);
    }
};

// リーダー変更
const changeLeader = async (userId) => {
    if (!confirm('このメンバーをチームリーダーに設定しますか？')) return;

    try {
        await teamStore.changeLeader(team.value.id, userId);
    } catch (error) {
        console.error('リーダーの変更に失敗しました:', error);
    }
};

// メンバー削除
const removeMember = async (userId) => {
    if (!confirm('このメンバーをチームから削除しますか？')) return;

    try {
        await teamStore.removeMember(team.value.id, userId);
    } catch (error) {
        console.error('メンバーの削除に失敗しました:', error);
    }
};
</script>