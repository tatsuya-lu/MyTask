<template>
    <div>
        <!-- タイトル -->
        <h3 v-if="store.displaySettings.title" class="font-semibold text-lg">
            {{ task.title }}
        </h3>

        <!-- 説明 -->
        <p v-if="store.displaySettings.description" class="text-gray-600 text-sm mt-1">
            {{ task.description }}
        </p>

        <div class="mt-2 space-y-2">
            <!-- ステータスと優先度 -->
            <div class="flex flex-wrap gap-2">
                <span v-if="store.displaySettings.status" class="px-2 py-1 rounded-full text-sm"
                    :class="statusClasses[task.status]">
                    {{ statusLabels[task.status] }}
                </span>

                <span v-if="store.displaySettings.priority" class="px-2 py-1 rounded-full text-sm"
                    :class="priorityClasses[task.priority]">
                    {{ priorityLabels[task.priority] }}
                </span>
            </div>

            <!-- 進捗 -->
            <div v-if="store.displaySettings.progress.show" class="w-full">
                <template v-if="store.displaySettings.progress.type === 'percentage'">
                    <span class="text-sm text-gray-600">
                        進捗: {{ task.progress }}%
                    </span>
                </template>
                <template v-else>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${task.progress}%` }">
                        </div>
                    </div>
                </template>
            </div>

            <!-- 期限日 -->
            <div v-if="store.displaySettings.dueDate && task.due_date" class="text-sm text-gray-600">
                期限日: {{ formatDate(task.due_date) }}
            </div>

            <!-- タグ -->
            <div v-if="store.displaySettings.tags" class="flex flex-wrap gap-1">
                <span v-for="tag in task.tags" :key="tag.id" class="px-2 py-1 rounded text-sm" :style="{
                    backgroundColor: tag.color + '20',
                    color: tag.color,
                    borderColor: tag.color,
                    borderWidth: '1px'
                }">
                    {{ tag.name }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useTaskStore } from '@/stores/task';

const store = useTaskStore();

const props = defineProps({
    task: {
        type: Object,
        required: true
    }
});

const statusLabels = {
    not_started: '未着手',
    in_progress: '進行中',
    completed: '完了'
};

const statusClasses = {
    not_started: 'bg-gray-100 text-gray-700',
    in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700'
};

const priorityLabels = {
    low: '低',
    medium: '中',
    high: '高'
};

const priorityClasses = {
    low: 'bg-green-100 text-green-700',
    medium: 'bg-yellow-100 text-yellow-700',
    high: 'bg-red-100 text-red-700'
};

const formatDate = (date) => {
    const d = new Date(date);
    return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`;
};
</script>