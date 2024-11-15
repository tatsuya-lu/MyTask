<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">タグ管理</h2>
            <button
                @click="showCreateModal = true"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            >
                新規タグ作成
            </button>
        </div>

        <!-- タグ一覧 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="tag in tags"
                :key="tag.id"
                class="border rounded-lg p-4 flex justify-between items-center"
                :style="{ borderLeftColor: tag.color, borderLeftWidth: '4px' }"
            >
                <div class="flex items-center">
                    <span
                        class="w-4 h-4 rounded-full mr-2"
                        :style="{ backgroundColor: tag.color }"
                    ></span>
                    <span>{{ tag.name }}</span>
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="editTag(tag)"
                        class="text-blue-500 hover:text-blue-700"
                    >
                        編集
                    </button>
                    <button
                        @click="confirmDelete(tag)"
                        class="text-red-500 hover:text-red-700"
                    >
                        削除
                    </button>
                </div>
            </div>
        </div>

        <!-- タグ作成/編集モーダル -->
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">
                    {{ showEditModal ? 'タグの編集' : '新規タグ作成' }}
                </h3>
                <form @submit.prevent="handleSubmit">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            タグ名
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required
                        >
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            色
                        </label>
                        <input
                            v-model="form.color"
                            type="color"
                            class="w-full h-10"
                        >
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                        >
                            キャンセル
                        </button>
                        <button
                            type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            :disabled="isLoading"
                        >
                            {{ isLoading ? '保存中...' : '保存' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useTagStore } from '../../stores/tag';

const tagStore = useTagStore();
const { tags, isLoading } = storeToRefs(tagStore);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const form = ref({
    name: '',
    color: '#000000'
});
const editingTagId = ref(null);

onMounted(async () => {
    await tagStore.fetchTags();
});

const editTag = (tag) => {
    form.value = {
        name: tag.name,
        color: tag.color
    };
    editingTagId.value = tag.id;
    showEditModal.value = true;
};

const confirmDelete = async (tag) => {
    if (confirm(`タグ「${tag.name}」を削除してもよろしいですか？`)) {
        await tagStore.deleteTag(tag.id);
    }
};

const handleSubmit = async () => {
    try {
        if (showEditModal.value) {
            await tagStore.updateTag(editingTagId.value, form.value);
        } else {
            await tagStore.createTag(form.value);
        }
        closeModal();
    } catch (error) {
        console.error('タグの保存に失敗しました:', error);
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    form.value = {
        name: '',
        color: '#000000'
    };
    editingTagId.value = null;
};
</script>