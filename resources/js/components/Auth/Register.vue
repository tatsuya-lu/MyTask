<template>
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-6">アカウント登録</h1>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <label class="block mb-1">名前</label>
                <input type="text" v-model="name" required class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block mb-1">メールアドレス</label>
                <input type="email" v-model="email" required class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block mb-1">パスワード</label>
                <input type="password" v-model="password" required class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block mb-1">パスワード（確認）</label>
                <input type="password" v-model="password_confirmation" required class="w-full border rounded p-2">
            </div>

            <div v-if="error" class="text-red-500 text-sm">
                {{ error }}
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 transition"
                :disabled="isLoading">
                {{ isLoading ? '登録中...' : '登録する' }}
            </button>
        </form>

        <p class="mt-4 text-center">
            すでにアカウントをお持ちの方は
            <router-link :to="{ name: 'login' }" class="text-blue-500 hover:underline">
                こちら
            </router-link>
        </p>

        <p class="mt-4 text-center">
            すでにアカウントをお持ちの方は
            <router-link :to="{ name: 'login' }" class="text-blue-500 hover:underline">
                こちら
            </router-link>
        </p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useRouter } from 'vue-router';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');
const isLoading = ref(false);

const handleSubmit = async () => {
    error.value = '';
    isLoading.value = true;

    if (password.value !== password_confirmation.value) {
        error.value = 'パスワードが一致しません';
        isLoading.value = false;
        return;
    }

    try {
        const success = await authStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value
        });

        if (success) {
            router.push({ name: 'tasks' });
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        isLoading.value = false;
    }
};
</script>