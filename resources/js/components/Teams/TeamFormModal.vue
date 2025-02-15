<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">
                {{ isEditing ? 'チームを編集' : '新規チーム作成' }}
            </h3>

            <form @submit.prevent="handleSubmit" novalidate>
                <!-- チーム名 -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        チーム名*
                    </label>
                    <input id="name" v-model="form.name" type="text"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        :class="{ 'border-red-500': errors.name }" required>
                    <p v-if="errors.name" class="text-red-500 text-xs italic">{{ errors.name[0] }}</p>
                </div>

                <!-- 説明 -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        説明
                    </label>
                    <textarea id="description" v-model="form.description"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        rows="4"></textarea>
                </div>

                <!-- ボタン -->
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="$emit('close')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        キャンセル
                    </button>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        :disabled="isLoading">
                        {{ isLoading ? '保存中...' : '保存' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="mb-4" v-if="showMemberLimitInfo">
        <p class="text-sm text-gray-600">
            メンバー上限: {{ memberLimit }}人
            <span v-if="!authStore.user?.is_premium">
                （プレミアムプランなら50人まで追加可能）
            </span>
        </p>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const props = defineProps({
    team: {
        type: Object,
        default: null
    },
    isEditing: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    name: '',
    description: ''
});

const errors = ref({});
const isLoading = ref(false);

const memberLimit = computed(() => {
    return authStore.user?.is_premium ? 50 : 10;
});

const showMemberLimitInfo = computed(() => {
    return !props.isEditing;
});

onMounted(() => {
    if (props.team) {
        form.value = {
            name: props.team.name,
            description: props.team.description
        };
    }
});

const handleSubmit = async () => {
    isLoading.value = true;
    errors.value = {};

    try {
        emit('submit', form.value);
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    } finally {
        isLoading.value = false;
    }
};
</script>