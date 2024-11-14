<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">
            {{ isEditing ? 'タスクの編集' : '新規タスク作成' }}
        </h1>

        <form @submit.prevent="handleSubmit" class="max-w-2xl">
            <!-- タイトル -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                    タイトル*
                </label>
                <input id="title" v-model="form.title" type="text"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    :class="{ 'border-red-500': errors.title }" required>
                <p v-if="errors.title" class="text-red-500 text-xs italic">{{ errors.title[0] }}</p>
            </div>

            <!-- 説明 -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    説明
                </label>
                <textarea id="description" v-model="form.description"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    rows="4"></textarea>
            </div>

            <!-- 優先度 -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="priority">
                    優先度
                </label>
                <select id="priority" v-model="form.priority"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="low">低</option>
                    <option value="medium">中</option>
                    <option value="high">高</option>
                </select>
            </div>

            <!-- ステータス -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                    ステータス
                </label>
                <select id="status" v-model="form.status"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="not_started">未着手</option>
                    <option value="in_progress">進行中</option>
                    <option value="completed">完了</option>
                </select>
            </div>

            <!-- 進捗 -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="progress">
                    進捗 ({{ form.progress }}%)
                </label>
                <input id="progress" v-model="form.progress" type="range" min="0" max="100" step="10" class="w-full">
            </div>

            <!-- 期限日 -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="due_date">
                    期限日
                </label>
                <input id="due_date" v-model="form.due_date" type="date"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <!-- ボタン -->
            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    :disabled="isLoading">
                    {{ isLoading ? '保存中...' : (isEditing ? '更新' : '作成') }}
                </button>
                <button type="button" @click="router.push({ name: 'tasks' })"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    キャンセル
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTaskStore } from '../../stores/task';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const taskStore = useTaskStore();

const isEditing = computed(() => !!route.params.id);
const isLoading = ref(false);
const errors = ref({});

const form = ref({
    title: '',
    description: '',
    priority: 'medium',
    status: 'not_started',
    progress: 0,
    due_date: null
});

onMounted(async () => {
    if (isEditing.value) {
        try {
            const response = await api.get(`/tasks/${route.params.id}`);
            form.value = { ...response.data };
        } catch (error) {
            console.error('タスクの取得に失敗しました:', error);
            router.push({ name: 'tasks' });
        }
    }
});

const handleSubmit = async () => {
    isLoading.value = true;
    errors.value = {};

    try {
        if (isEditing.value) {
            await taskStore.updateTask(route.params.id, form.value);
        } else {
            await taskStore.createTask(form.value);
        }
        router.push({ name: 'tasks' });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    } finally {
        isLoading.value = false;
    }
};
</script>