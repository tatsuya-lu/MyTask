<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">チーム管理</h2>
            <button
                v-if="authStore.user?.is_premium"
                @click="showCreateModal = true"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            >
                新規チーム作成
            </button>
            <div v-else class="text-gray-600">
                プレミアムプランにアップグレードしてチーム機能を利用する
            </div>
        </div>

        <div v-if="!authStore.user?.is_premium" class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
            <p class="text-yellow-700">
                チーム機能はプレミアムプラン限定の機能です。
                <a href="/pricing" class="underline">プランをアップグレード</a>
                して、チームでの協力を始めましょう。
            </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="team in teamStore.teams"
                :key="team.id"
                class="border rounded-lg p-4"
            >
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-semibold">{{ team.name }}</h3>
                    <div class="flex space-x-2">
                        <button
                            v-if="team.owner_id === authStore.user?.id"
                            @click="editTeam(team)"
                            class="text-blue-500 hover:text-blue-700"
                        >
                            編集
                        </button>
                        <button
                            v-if="team.owner_id === authStore.user?.id"
                            @click="confirmDelete(team)"
                            class="text-red-500 hover:text-red-700"
                        >
                            削除
                        </button>
                    </div>
                </div>

                <p class="text-gray-600 mb-4">{{ team.description }}</p>

                <div class="mb-4">
                    <h4 class="font-semibold mb-2">メンバー</h4>
                    <div class="space-y-2">
                        <div
                            v-for="member in team.members"
                            :key="member.id"
                            class="flex justify-between items-center"
                        >
                            <span>{{ member.name }}</span>
                            <button
                                v-if="team.owner_id === authStore.user?.id && member.id !== team.owner_id"
                                @click="removeMember(team.id, member.id)"
                                class="text-red-500 hover:text-red-700 text-sm"
                            >
                                削除
                            </button>
                        </div>
                    </div>
                </div>

                <button
                    v-if="team.owner_id === authStore.user?.id"
                    @click="showAddMemberModal(team)"
                    class="text-blue-500 hover:text-blue-700 text-sm"
                >
                    メンバーを追加
                </button>
            </div>
        </div>

        <!-- チーム作成/編集モーダル -->
        <TeamFormModal
            v-if="showCreateModal || showEditModal"
            :team="editingTeam"
            :is-editing="showEditModal"
            @close="closeModal"
            @submit="handleTeamSubmit"
        />

        <!-- メンバー追加モーダル -->
        <AddMemberModal
            v-if="showAddMemberModal"
            :team="selectedTeam"
            @close="closeAddMemberModal"
            @submit="handleAddMember"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTeamStore } from '../../stores/team';
import { useAuthStore } from '../../stores/auth';
import TeamFormModal from './TeamFormModal.vue';
import AddMemberModal from './AddMemberModal.vue';

const teamStore = useTeamStore();
const authStore = useAuthStore();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showAddMemberModal = ref(false);
const editingTeam = ref(null);
const selectedTeam = ref(null);

onMounted(async () => {
    if (authStore.user?.is_premium) {
        await teamStore.fetchTeams();
    }
});

// ... 他のメソッド実装
</script>