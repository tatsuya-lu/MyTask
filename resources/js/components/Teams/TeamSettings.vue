<template>
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-6">チーム設定</h2>

        <!-- チーム基本設定 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4">基本設定</h3>
            <form @submit.prevent="updateTeam" class="space-y-4" novalidate>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        チーム名*
                    </label>
                    <input v-model="teamForm.name" type="text" class="w-full border rounded-md px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        説明
                    </label>
                    <textarea v-model="teamForm.description" class="w-full border rounded-md px-3 py-2"
                        rows="3"></textarea>
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                        :disabled="isUpdating">
                        {{ isUpdating ? '更新中...' : '更新' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- メンバー管理 -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">メンバー管理</h3>
                <button @click="showAddMemberModal = true"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    メンバー追加
                </button>
            </div>

            <!-- メンバー一覧 -->
            <div class="space-y-4">
                <div v-for="member in team?.members" :key="member.id"
                    class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium">{{ member.name }}</span>
                        <span class="ml-2 text-sm text-gray-500">{{ member.email }}</span>
                        <span v-if="member.id === team?.owner_id"
                            class="ml-2 text-sm bg-purple-100 text-purple-800 px-2 py-1 rounded">
                            オーナー
                        </span>
                        <span v-else-if="member.pivot.role_id === leaderRoleId"
                            class="ml-2 text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            リーダー
                        </span>
                    </div>
                    <div class="flex space-x-2" v-if="member.id !== team?.owner_id">
                        <button v-if="member.pivot.role_id !== leaderRoleId" @click="changeLeader(member)"
                            class="text-blue-600 hover:text-blue-800">
                            リーダーに設定
                        </button>
                        <button @click="removeMember(member)" class="text-red-600 hover:text-red-800">
                            削除
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 危険な操作 -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-red-600 mb-4">危険な操作</h3>
            <div>
                <button @click="confirmDeleteTeam"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    チームを削除
                </button>
                <p class="mt-2 text-sm text-gray-500">
                    この操作は取り消すことができません。チーム内のすべてのデータが削除されます。
                </p>
            </div>
        </div>

        <!-- メンバー追加モーダル -->
        <AddMemberModal v-if="showAddMemberModal" :team="team" @close="showAddMemberModal = false"
            @submit="handleAddMember" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTeamStore } from '@/stores/team'
import AddMemberModal from './AddMemberModal.vue'

const router = useRouter()
const teamStore = useTeamStore()

// 状態管理
const showAddMemberModal = ref(false)
const isUpdating = ref(false)
const leaderRoleId = ref(1) // リーダーのrole_id

const team = computed(() => teamStore.currentTeam)

const teamForm = ref({
    name: '',
    description: ''
})

// チーム情報の初期化
onMounted(() => {
    if (team.value) {
        teamForm.value = {
            name: team.value.name,
            description: team.value.description
        }
    }
})

// チーム情報の更新
const updateTeam = async () => {
    if (!team.value) return

    isUpdating.value = true
    try {
        await teamStore.updateTeam(team.value.id, teamForm.value)
        alert('チーム情報を更新しました')
    } catch (error) {
        console.error('チーム情報の更新に失敗しました:', error)
        alert('チーム情報の更新に失敗しました')
    } finally {
        isUpdating.value = false
    }
}

// メンバー追加
const handleAddMember = async (memberData) => {
    try {
        await teamStore.addMember(team.value.id, memberData)
        showAddMemberModal.value = false
    } catch (error) {
        console.error('メンバーの追加に失敗しました:', error)
        alert('メンバーの追加に失敗しました')
    }
}

// リーダー変更
const changeLeader = async (member) => {
    if (!confirm(`${member.name}をチームリーダーに設定しますか？`)) return

    try {
        await teamStore.changeLeader(team.value.id, member.id)
        alert('チームリーダーを変更しました')
    } catch (error) {
        console.error('リーダーの変更に失敗しました:', error)
        alert('リーダーの変更に失敗しました')
    }
}

// メンバー削除
const removeMember = async (member) => {
    if (!confirm(`${member.name}をチームから削除しますか？`)) return

    try {
        await teamStore.removeMember(team.value.id, member.id)
        alert('メンバーを削除しました')
    } catch (error) {
        console.error('メンバーの削除に失敗しました:', error)
        alert('メンバーの削除に失敗しました')
    }
}

// チーム削除
const confirmDeleteTeam = async () => {
    if (!confirm('本当にこのチームを削除しますか？この操作は取り消すことができません。')) return

    try {
        await teamStore.deleteTeam(team.value.id)
        router.push('/teams')
    } catch (error) {
        console.error('チームの削除に失敗しました:', error)
        alert('チームの削除に失敗しました')
    }
}
</script>