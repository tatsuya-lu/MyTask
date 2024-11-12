<template>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">
            {{ isEditing ? 'タスクの編集' : '新規タスク作成' }}
        </h1>

        <form @submit.prevent="handleSubmit" class="space-y-6 bg-white p-6 rounded shadow">
            <!-- タイトル -->
            <div>
                <label class="block mb-1">タイトル *</label>
                <input type="text" v-model="form.title" required class="w-full border rounded p-2"
                    :class="{ 'border-red-500': errors.title }">
                <span v-if="errors.title" class="text-red-500 text-sm">
                    {{ errors.title[0] }}
                </span>
            </div>

            <!-- 説明 -->
            <div>
                <label class="block mb-1">説明</label>
                <textarea v-model="form.description" rows="3" class="w-full border rounded p-2"
                    :class="{ 'border-red-500': errors.description }"></textarea>
                <span v-if="errors.description" class="text-red-500 text-sm">
                    {{ errors.description[0] }}
                </span>
            </div>

            <!-- 優先度 -->
            <div>
                <label class="block mb-1">優先度 *</label>
                <select v-model="form.priority" required class="w-full border rounded p-2"
                    :class="{ 'border-red-500': errors.priority }">
                    <option value="low">低</option>
                    <option value="medium">中</option>
                    <option value="high">高</option>
                </select>
                <span v-if="errors.priority" class="text-red-500 text-sm">
                    {{ errors.priority[0] }}
                </span>
            </div>

            <!-- ステータス -->
            <div>
                <label class="block mb-1">ステータス *</label>
                <select v-model="form.status" required class="w-full border rounded p-2"
                    :class="{ 'border-red-500': errors.status }">
                    <option value="not_started">未着手</option>
                    <option value="in_progress">進行中</option>
                    <option value="completed">完了</option>
                </select>
                <span v-if="errors.status" class="text-red-500 text-sm">
                    {{ errors.status[0] }}
                </span>
            </div>

            <!-- 進捗 -->
            <div>
                <label class="block mb-1">進捗 *</label>
                <div class="flex items-center space-x-2">
                    <input type="range" v-model.number="form.progress" min="0" max="100" step="10" class="flex-grow">
                    <span class="w-16 text-right">{{ form.progress }}%</span>
                </div>
                <span v-if="errors.progress" class="text-red-500 text-sm">
                    {{ errors.progress[0] }}
                </span>
            </div>

            <!-- 期限日 -->
            <div>
                <label class="block mb-1">期限日</label>
                <input type="date" v-model="form.due_date" class="w-full border rounded p-2"
                    :class="{ 'border-red-500': errors.due_date }">
                <span v-if="errors.due_date" class="text-red-500 text-sm">
                    {{ errors.due_date[0] }}
                </span>
            </div>

            <!-- 送信ボタン -->
            <div class="flex justify-end space-x-4">
                <button type="button" @click="$router.push({ name: 'home' })"
                    class="px-4 py-2 border rounded hover:bg-gray-100">
                    キャンセル
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    :disabled="isLoading">
                    {{ isLoading ? '保存中...' : (isEditing ? '更新する' : '作成する') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useTaskStore } from '../../stores/task'

const router = useRouter()
const route = useRoute()
const taskStore = useTaskStore()

const isEditing = computed(() => !!route.params.id)
const isLoading = ref(false)
const errors = ref({})

const form = ref({
    title: '',
    description: '',
    priority: 'medium',
    status: 'not_started',
    progress: 0,
    due_date: null
})

// タスクの読み込み（編集時）
onMounted(async () => {
    if (isEditing.value) {
        try {
            const task = await taskStore.fetchTask(route.params.id)
            form.value = {
                title: task.title,
                description: task.description || '',
                priority: task.priority,
                status: task.status,
                progress: task.progress,
                due_date: task.due_date
            }
        } catch (error) {
            console.error('タスクの読み込みに失敗しました:', error)
            router.push({ name: 'home' })
        }
    }
})

// フォームの送信
const handleSubmit = async () => {
    try {
        isLoading.value = true
        errors.value = {}

        if (isEditing.value) {
            await taskStore.updateTask(route.params.id, form.value)
        } else {
            await taskStore.createTask(form.value)
        }

        router.push({ name: 'home' })
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            console.error('エラーが発生しました:', error)
        }
    } finally {
        isLoading.value = false
    }
}
</script>