<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">
                メンバーを追加
            </h3>

            <form @submit.prevent="handleSubmit" novalidate>
                <!-- メールアドレスまたはユーザー検索 -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="user">
                        ユーザーを検索
                    </label>
                    <input id="user" v-model="searchQuery" type="text"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="メールアドレスまたは名前" @input="searchUsers">

                    <!-- 検索結果 -->
                    <div v-if="searchResults.length > 0" class="mt-2 border rounded">
                        <div v-for="user in searchResults" :key="user.id" class="p-2 hover:bg-gray-100 cursor-pointer"
                            @click="selectUser(user)">
                            {{ user.name }} ({{ user.email }})
                        </div>
                    </div>
                </div>

                <!-- 役割選択 -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                        役割
                    </label>
                    <select id="role" v-model="form.role_id"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                        <option value="">役割を選択</option>
                        <option v-for="role in roles" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </option>
                    </select>
                </div>

                <!-- ボタン -->
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="$emit('close')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        キャンセル
                    </button>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        :disabled="isLoading || !form.user_id || !form.role_id">
                        {{ isLoading ? '追加中...' : '追加' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div v-if="showMemberLimitWarning" class="text-red-500 text-sm mb-4">
        メンバー数が上限に達しています（{{ currentMemberCount }}/{{ team.member_limit }}人）
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    team: {
        type: Object,
        required: true
    }
});

const currentMemberCount = computed(() => {
    return props.team.members?.length || 0;
});

const showMemberLimitWarning = computed(() => {
    return currentMemberCount.value >= props.team.member_limit;
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    user_id: null,
    role_id: ''
});

const searchQuery = ref('');
const searchResults = ref([]);
const roles = ref([]);
const isLoading = ref(false);

onMounted(async () => {
    try {
        const response = await axios.get('/api/roles');
        roles.value = response.data;
    } catch (error) {
        console.error('役割の取得に失敗しました:', error);
    }
});

const searchUsers = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    try {
        const response = await axios.get(`/api/users/search?q=${searchQuery.value}`);
        searchResults.value = response.data;
    } catch (error) {
        console.error('ユーザー検索に失敗しました:', error);
    }
};

const selectUser = (user) => {
    form.value.user_id = user.id;
    searchQuery.value = user.name;
    searchResults.value = [];
};

const handleSubmit = async () => {
    if (!form.value.user_id || !form.value.role_id) return;

    try {
        isLoading.value = true;
        // awaitを追加し、submitイベントの結果を待つ
        await emit('submit', {
            user_id: form.value.user_id,
            role_id: parseInt(form.value.role_id) // 数値型に変換
        });
        // 成功した場合のみcloseを実行
        emit('close');
    } catch (error) {
        console.error('メンバーの追加に失敗しました:', error);
    } finally {
        isLoading.value = false;
    }
};
</script>