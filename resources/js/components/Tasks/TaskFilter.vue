<template>
    <div class="bg-white p-4 rounded-lg shadow mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    ステータス
                </label>
                <select class="w-full border rounded-md py-2 px-3 text-gray-700" :value="status"
                    @change="$emit('update:status', $event.target.value)">
                    <option value="">すべて</option>
                    <option value="not_started">新規</option>
                    <option value="in_progress">進行中</option>
                    <option value="completed">完了</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    優先度
                </label>
                <select class="w-full border rounded-md py-2 px-3 text-gray-700" :value="priority"
                    @change="$emit('update:priority', $event.target.value)">
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
                <select class="w-full border rounded-md py-2 px-3 text-gray-700" :value="tag"
                    @change="$emit('update:tag', $event.target.value)">
                    <option value="">すべて</option>
                    <option v-for="tag in tagStore.tags" :key="tag.id" :value="tag.id">
                        {{ tag.name }}
                    </option>
                </select>
            </div>

            <div class="col-span-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    検索
                </label>
                <input type="text" :value="searchQuery" @input="$emit('update:search', $event.target.value)"
                    placeholder="タスクを検索..."
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>
</template>

<script setup>
import { useTagStore } from '@/stores/tag';

const tagStore = useTagStore();

defineProps({
    status: { type: String, default: '' },
    priority: { type: String, default: '' },
    tag: { type: String, default: '' },
    searchQuery: { type: String, default: '' }
});

defineEmits([
    'update:status',
    'update:priority',
    'update:tag',
    'update:search'
]);
</script>