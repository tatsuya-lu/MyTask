<template>
    <div class="max-w-md mx-auto mt-8">
        <h1 class="text-2xl font-bold mb-6">ログイン</h1>
        
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <label class="block mb-1">メールアドレス</label>
                <input
                    type="email"
                    v-model="email"
                    required
                    class="w-full border rounded p-2"
                >
            </div>

            <div>
                <label class="block mb-1">パスワード</label>
                <input
                    type="password"
                    v-model="password"
                    required
                    class="w-full border rounded p-2"
                >
            </div>

            <div v-if="errors.error" class="text-red-500">
                {{ errors.error }}
            </div>

            <button
                type="submit"
                class="w-full bg-blue-500 text-white p-2 rounded"
                :disabled="isLoading"
            >
                {{ isLoading ? 'ログイン中...' : 'ログイン' }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useRouter } from 'vue-router';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const errors = ref({});
const isLoading = ref(false);

const handleSubmit = async () => {
    errors.value = {};
    isLoading.value = true;
    
    try {
        const success = await authStore.login({
            email: email.value,
            password: password.value
        });
        
        if (success) {
            router.push({ name: 'tasks' });
        }
    } catch (error) {
        errors.value.error = error.message;
    } finally {
        isLoading.value = false;
    }
};
</script>