<template>
    <div class="bg-white shadow rounded-lg p-6">
        <!-- タスク作成ボタン -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">チームタスク</h2>
            <button @click="showCreateModal = true"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                新規タスク作成
            </button>
        </div>

        <!-- フィルター -->
        <div class="mb-6 flex flex-wrap gap-4">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium">ステータス:</label>
                <select v-model="filters.status" class="border rounded px-2 py-1">
                    <option value="">全て</option>
                    <option value="not_started">未着手</option>
                    <option value="in_progress">進行中</option>
                    <option value="completed">完了</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium">優先度:</label>
                <select v-model="filters.priority" class="border rounded px-2 py-1">
                    <option value="">全て</option>
                    <option value="low">低</option>
                    <option value="medium">中</option>
                    <option value="high">高</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium">担当者:</label>
                <select v-model="filters.assignee" class="border rounded px-2 py-1">
                    <option value="">全て</option>
                    <option v-for="member in team?.members" :key="member.id" :value="member.id">
                        {{ member.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- タスク一覧 -->
        <div v-if="tasks.length > 0" class="space-y-4">
            <div v-for="task in tasks" :key="task.id" class="border rounded-lg p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ task.title }}</h3>
                        <p class="text-gray-600 text-sm">{{ task.description }}</p>
                        <div class="mt-2 flex items-center space-x-4 text-sm">
                            <span class="flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                {{ task.assignee?.name || '未割り当て' }}
                            </span>
                            <span :class="getStatusClass(task.status)">
                                {{ getStatusLabel(task.status) }}
                            </span>
                            <span :class="getPriorityClass(task.priority)">
                                {{ getPriorityLabel(task.priority) }}
                            </span>
                            <span v-if="task.due_date" class="text-gray-600">
                                期限: {{ formatDate(task.due_date) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button @click="editTask(task)" class="text-blue-600 hover:text-blue-800">
                            編集
                        </button>
                        <button v-if="canDeleteTask(task)" @click="deleteTask(task)"
                            class="text-red-600 hover:text-red-800">
                            削除
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-gray-500 py-8">
            タスクがありません
        </div>

        <!-- ページネーション -->
        <div v-if="totalPages > 1" class="mt-6 flex justify-center">
            <nav class="flex space-x-2">
                <button v-for="page in totalPages" :key="page" @click="currentPage = page" :class="[
                    'px-3 py-1 rounded',
                    currentPage === page
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200 hover:bg-gray-300'
                ]">
                    {{ page }}
                </button>
            </nav>
        </div>

        <!-- タスク作成/編集モーダル -->
        <TaskFormModal v-if="showCreateModal || showEditModal" :task="editingTask" :team="team"
            :is-editing="showEditModal" @close="closeModal" @submit="handleTaskSubmit" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useTeamStore } from '@/stores/team'
import { useAuthStore } from '@/stores/auth'
import TaskFormModal from '../Tasks/TaskForm.vue'
import { format } from 'date-fns'
import { ja } from 'date-fns/locale'

const route = useRoute()
const teamStore = useTeamStore()
const authStore = useAuthStore()

// 状態管理
const tasks = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingTask = ref(null)
const filters = ref({
    status: '',
    priority: '',
    assignee: ''
})

// チーム情報の取得
const team = computed(() => teamStore.currentTeam)

// タスクの取得
const fetchTasks = async () => {
    try {
        const response = await fetch(`/api/teams/${route.params.id}/tasks?` + new URLSearchParams({
            page: currentPage.value,
            ...filters.value
        }))
        const data = await response.json()
        tasks.value = data.data
        totalPages.value = Math.ceil(data.total / data.per_page)
    } catch (error) {
        console.error('タスクの取得に失敗しました:', error)
    }
}

// フィルターとページ変更時にタスクを再取得
watch([filters, currentPage], () => {
    fetchTasks()
})

// 初期データの取得
onMounted(() => {
    fetchTasks()
})

// タスクの編集・削除権限チェック
const canDeleteTask = (task) => {
    return authStore.user?.id === task.user_id || isTeamLeader.value
}

const isTeamLeader = computed(() => {
    if (!team.value || !authStore.user) return false
    return team.value.members.some(
        member =>
            member.id === authStore.user.id &&
            member.pivot.role_id === teamStore.leaderRoleId
    )
})

// モーダル関連の処理
const closeModal = () => {
    showCreateModal.value = false
    showEditModal.value = false
    editingTask.value = null
}

const editTask = (task) => {
    editingTask.value = task
    showEditModal.value = true
}

const handleTaskSubmit = async (taskData) => {
    try {
        if (showEditModal.value) {
            await fetch(`/api/tasks/${editingTask.value.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(taskData)
            })
        } else {
            await fetch(`/api/teams/${route.params.id}/tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(taskData)
            })
        }
        await fetchTasks()
        closeModal()
    } catch (error) {
        console.error('タスクの保存に失敗しました:', error)
    }
}

const deleteTask = async (task) => {
    if (!confirm('このタスクを削除してもよろしいですか？')) return

    try {
        await fetch(`/api/tasks/${task.id}`, {
            method: 'DELETE'
        })
        await fetchTasks()
    } catch (error) {
        console.error('タスクの削除に失敗しました:', error)
    }
}

// ユーティリティ関数
const formatDate = (date) => {
    return format(new Date(date), 'yyyy年MM月dd日', { locale: ja })
}

const getStatusLabel = (status) => ({
    not_started: '未着手',
    in_progress: '進行中',
    completed: '完了'
}[status])

const getPriorityLabel = (priority) => ({
    low: '低',
    medium: '中',
    high: '高'
}[priority])

const getStatusClass = (status) => ({
    not_started: 'text-gray-600',
    in_progress: 'text-blue-600',
    completed: 'text-green-600'
}[status])

const getPriorityClass = (priority) => ({
    low: 'text-gray-600',
    medium: 'text-yellow-600',
    high: 'text-red-600'
}[priority])
</script>