<template>
    <div class="container mx-auto px-4 py-8">
        <!-- ヘッダー部分 -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">タスク一覧</h1>
            <router-link :to="{ name: 'task-create' }"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                新規タスク
            </router-link>
        </div>

        <!-- 検索フィールド -->
        <div class="mb-4">
            <input type="text" v-model="searchQuery" placeholder="タスクを検索..."
                class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                @input="handleSearch">
        </div>

        <!-- フィルター部分 -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ステータス
                    </label>
                    <select v-model="selectedStatus" class="w-full border rounded-md py-2 px-3 text-gray-700"
                        @change="handleFilterChange">
                        <option value="">すべて</option>
                        <option value="new">新規</option>
                        <option value="in_progress">進行中</option>
                        <option value="completed">完了</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        優先度
                    </label>
                    <select v-model="selectedPriority" class="w-full border rounded-md py-2 px-3 text-gray-700"
                        @change="handleFilterChange">
                        <option value="">すべて</option>
                        <option value="low">低</option>
                        <option value="medium">中</option>
                        <option value="high">高</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        タグ
                    </label>
                    <select v-model="selectedTagId" class="w-full border rounded-md py-2 px-3 text-gray-700"
                        @change="handleFilterChange">
                        <option value="">すべて</option>
                        <option v-for="tag in tagStore.tags" :key="tag.id" :value="tag.id">
                            {{ tag.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- タスク一覧 -->
        <div v-if="isLoading" class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
        </div>

        <div v-else-if="filteredTasks.length === 0" class="text-center py-8 text-gray-500">
            タスクが見つかりません
        </div>

        <div v-else class="grid gap-4">
            <div v-for="task in tasks" :key="task.id" class="bg-white p-4 rounded shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ task.title }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ task.description }}</p>
                        <!-- タグの表示 -->
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span v-for="tag in task.tags" :key="tag.id" class="px-2 py-1 rounded text-sm" :style="{
                                backgroundColor: tag.color + '20',
                                color: tag.color,
                                borderColor: tag.color,
                                borderWidth: '1px'
                            }">
                                {{ tag.name }}
                            </span>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <span class="px-2 py-1 text-xs rounded" :class="{
                                'bg-red-100 text-red-800': task.priority === 'high',
                                'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                                'bg-green-100 text-green-800': task.priority === 'low'
                            }">
                                {{ task.priority }}
                            </span>
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                {{ task.status }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <router-link :to="{ name: 'task-edit', params: { id: task.id } }"
                            class="text-blue-500 hover:text-blue-700">
                            編集
                        </router-link>
                        <button @click="deleteTask(task.id)" class="text-red-500 hover:text-red-700">
                            削除
                        </button>
                    </div>
            <TransitionGroup name="list">
                <div v-for="task in filteredTasks" :key="task.id"
                    class="bg-white p-4 rounded shadow hover:shadow-md transition-shadow duration-200">
                    <!-- 既存のタスクカード内容 -->
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useTaskStore } from '@/stores/task';
import { useTagStore } from '@/stores/tag';
import { useRouter } from 'vue-router';

const router = useRouter();
const taskStore = useTaskStore();
const tagStore = useTagStore();

const { isLoading, error } = storeToRefs(taskStore);
const filteredTasks = computed(() => taskStore.filteredTasks);

// フィルター状態
const selectedStatus = ref('');
const selectedPriority = ref('');
const selectedTagId = ref('');
const searchQuery = ref('');

// 検索とフィルターのハンドラー
const handleSearch = () => {
    taskStore.setFilter('searchQuery', searchQuery.value);
};

const handleFilterChange = () => {
    taskStore.setFilter('status', selectedStatus.value);
    taskStore.setFilter('priority', selectedPriority.value);
    taskStore.setFilter('tagId', selectedTagId.value);
};

onMounted(async () => {
    await Promise.all([
        taskStore.fetchTasks(),
        tagStore.fetchTags()
    ]);
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

<style>
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>