<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">期限日フィルターを作成</h2>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        フィルター名
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-3 py-2 border rounded-md"
                        placeholder="例: 2週間以内"
                        required
                    >
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            期間
                        </label>
                        <input
                            v-model.number="form.duration_value"
                            type="number"
                            min="1"
                            class="w-full px-3 py-2 border rounded-md"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            単位
                        </label>
                        <select
                            v-model="form.duration_unit"
                            class="w-full px-3 py-2 border rounded-md"
                            required
                        >
                            <option value="day">日</option>
                            <option value="week">週</option>
                            <option value="month">月</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        キャンセル
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? '作成中...' : '作成' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useDueDateFilterStore } from '@/stores/dueDateFilter';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close', 'created']);
const filterStore = useDueDateFilterStore();

const form = ref({
    name: '',
    duration_value: 1,
    duration_unit: 'day'
});

const isLoading = ref(false);

const handleSubmit = async () => {
    isLoading.value = true;
    try {
        await filterStore.createFilter(form.value);
        emit('created');
        emit('close');
    } catch (error) {
        console.error('フィルターの作成に失敗:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>