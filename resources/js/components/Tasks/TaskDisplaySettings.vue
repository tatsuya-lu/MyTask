<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">表示設定</h2>
                <button @click="close" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- 基本項目の表示設定 -->
                <div class="space-y-2">
                    <div v-for="(label, key) in basicSettings" :key="key" class="flex items-center">
                        <input type="checkbox" :id="key" v-model="tempSettings[key]"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <label :for="key" class="ml-2 text-gray-700">{{ label }}</label>
                    </div>
                </div>

                <!-- 進捗表示の設定 -->
                <div class="border-t pt-4">
                    <h3 class="font-semibold mb-2">進捗表示</h3>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="progress-show" v-model="tempSettings.progress.show"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <label for="progress-show" class="ml-2 text-gray-700">進捗を表示</label>
                    </div>
                    <div v-if="tempSettings.progress.show" class="ml-6">
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="tempSettings.progress.type" value="percentage"
                                    class="text-blue-600">
                                <span class="ml-2">パーセンテージ</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="tempSettings.progress.type" value="bar"
                                    class="text-blue-600">
                                <span class="ml-2">プログレスバー</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="close"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    キャンセル
                </button>
                <button @click="save" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    保存
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useTaskStore } from '@/stores/task';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close']);
const taskStore = useTaskStore();

const basicSettings = {
    title: 'タイトル',
    description: '説明',
    priority: '優先度',
    status: 'ステータス',
    dueDate: '期限日',
    tags: 'タグ'
};

const tempSettings = ref({
    ...taskStore.displaySettings
});

const close = () => {
    emit('close');
};

const save = () => {
    taskStore.updateDisplaySettings(tempSettings.value);
    close();
};
</script>