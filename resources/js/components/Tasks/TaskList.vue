// TaskList.vue
<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">タスク一覧</h1>
            <router-link 
                :to="{ name: 'task-create' }" 
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
                新規タスク
            </router-link>
        </div>

        <div v-if="isLoading" class="text-center py-4">
            読み込み中...
        </div>
        
        <div v-else-if="error" class="text-red-500 text-center py-4">
            {{ error }}
        </div>

        <div v-else-if="tasks.length === 0" class="text-center py-4">
            タスクがありません
        </div>

        <div v-else class="grid gap-4">
            <div v-for="task in tasks" 
                :key="task.id" 
                class="bg-white p-4 rounded shadow"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ task.title }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ task.description }}</p>
                        <div class="mt-2 flex gap-2">
                            <span 
                                class="px-2 py-1 text-xs rounded"
                                :class="{
                                    'bg-red-100 text-red-800': task.priority === 'high',
                                    'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                                    'bg-green-100 text-green-800': task.priority === 'low'
                                }"
                            >
                                {{ task.priority }}
                            </span>
                            <span 
                                class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800"
                            >
                                {{ task.status }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <router-link 
                            :to="{ name: 'task-edit', params: { id: task.id }}"
                            class="text-blue-500 hover:text-blue-700"
                        >
                            編集
                        </router-link>
                        <button 
                            @click="deleteTask(task.id)"
                            class="text-red-500 hover:text-red-700"
                        >
                            削除
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useTaskStore } from '../../stores/task';
import { useRouter } from 'vue-router';

const taskStore = useTaskStore();
const router = useRouter();

const { tasks, isLoading, error } = storeToRefs(taskStore);

onMounted(async () => {
    await taskStore.fetchTasks();
});

const deleteTask = async (taskId) => {
    if (confirm('このタスクを削除してもよろしいですか？')) {
        try {
            await taskStore.deleteTask(taskId);
        } catch (error) {
            console.error('タスクの削除に失敗しました:', error);
        }
    }
};
</script>