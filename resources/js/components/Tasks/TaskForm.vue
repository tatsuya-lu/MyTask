<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">
            {{ isEditing ? 'タスクの編集' : '新規タスク作成' }}
        </h1>

        <form @submit.prevent="handleSubmit" class="max-w-2xl">
            <!-- タイトル -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                    タイトル*
                </label>
                <input id="title" v-model="form.title" type="text"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    :class="{ 'border-red-500': errors.title }" required>
                <p v-if="errors.title" class="text-red-500 text-xs italic">{{ errors.title[0] }}</p>
            </div>

            <!-- 説明 -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    説明
                </label>
                <textarea id="description" v-model="form.description"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    rows="4"></textarea>
            </div>

            <!-- 優先度 -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="priority">
                    優先度
                </label>
                <select id="priority" v-model="form.priority"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="low">低</option>
                    <option value="medium">中</option>
                    <option value="high">高</option>
                </select>
            </div>

            <!-- ステータス -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                    ステータス
                </label>
                <select id="status" v-model="form.status"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="not_started">新規</option>
                    <option value="in_progress">進行中</option>
                    <option value="completed">完了</option>
                </select>
            </div>

            <!-- 進捗 -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="progress">
                    進捗 ({{ form.progress }}%)
                </label>
                <input id="progress" v-model="form.progress" type="range" min="0" max="100" step="10" class="w-full">
            </div>

            <!-- 期限日 -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="due_date">
                    期限日
                </label>
                <Datepicker v-model="form.due_date" format="yyyy年MM月dd日" model-type="yyyy-MM-dd" locale="ja" cancel-text="キャンセル"
                    select-text="選択" :enable-time-picker="false" :month-picker="false" :year-picker="false"
                    placeholder="期限日を選択" position="left" :dark="false" class="w-full" />
            </div>

            <!-- タグ選択フィールド -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">タグ</label>
                <div class="flex items-center space-x-2 mb-2">
                    <input v-model="newTag.name" type="text" placeholder="タグ名"
                        class="flex-grow px-2 py-1 border rounded">
                    <input v-model="newTag.color" type="color" class="w-12 h-10 border rounded">
                    <button @click="addTag" class="bg-blue-500 text-white px-3 py-1 rounded" :disabled="!newTag.name">
                        追加
                    </button>
                </div>

                <!-- 追加されたタグのリスト -->
                <div class="flex flex-wrap gap-2 mb-2">
                    <span v-for="(tag, index) in dynamicTags" :key="index"
                        class="flex items-center px-2 py-1 rounded text-sm" :style="{
                            backgroundColor: tag.color + '20',
                            color: tag.color,
                            borderColor: tag.color,
                            borderWidth: '1px'
                        }">
                        {{ tag.name }}
                        <button @click="removeTag(index)" class="ml-2 text-red-500 text-xs">
                            ×
                        </button>
                    </span>
                </div>

                <!-- 既存のタグ選択 -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">タグ</label>
                    <div class="flex flex-wrap gap-2">
                        <p v-if="tagStore.tags.length === 0" class="text-gray-500">
                            タグが見つかりません
                        </p>
                        <label v-else v-for="tag in tagStore.tags" :key="tag.id"
                            class="inline-flex items-center cursor-pointer p-2 rounded border"
                            :style="{ borderColor: tag.color }">
                            <input type="checkbox" :value="tag.id" v-model="form.tags" class="mr-2">
                            <span class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: tag.color }"></span>
                            {{ tag.name }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- ボタン -->
            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    :disabled="isLoading">
                    {{ isLoading ? '保存中...' : (isEditing ? '更新' : '作成') }}
                </button>
                <button type="button" @click="router.push({ name: 'tasks' })"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    キャンセル
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTaskStore } from '../../stores/task';
import { useTagStore } from '../../stores/tag';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const route = useRoute();
const router = useRouter();
const taskStore = useTaskStore();
const tagStore = useTagStore();

const isEditing = computed(() => !!route.params.id);
const isLoading = ref(false);
const errors = ref({});

const form = ref({
    title: '',
    description: '',
    priority: 'medium',
    status: 'not_started',
    progress: 0,
    due_date: null,
    tags: []
});

const dynamicTags = ref([]);
const newTag = ref({
    name: '',
    color: '#000000'
});

const addTag = () => {
    if (newTag.value.name.trim()) {
        dynamicTags.value.push({
            name: newTag.value.name,
            color: newTag.value.color
        });
        newTag.value.name = '';
        newTag.value.color = '#000000';
    }
};

const removeTag = (index) => {
    dynamicTags.value.splice(index, 1);
};


onMounted(async () => {
    try {
        await tagStore.fetchTags();
    } catch (error) {
        console.error('タグの取得に失敗しました:', error);
    }

    if (isEditing.value) {
        try {
            const response = await axios.get(`/api/tasks/${route.params.id}`);
            const task = response.data.data;

            form.value = {
                ...task,
                due_date: task.due_date
                    ? new Date(task.due_date)
                    : null,
                tags: task.tags
                    ? task.tags.map(tag => tag.id)
                    : [] // tagsがnullの場合は空配列にする
            };
        } catch (error) {
            console.error('タスクの取得に失敗しました:', error);
            router.push({ name: 'tasks' });
        }
    }
});

const handleSubmit = async () => {

    isLoading.value = true;
    errors.value = {};

    try {
        // フォームデータの複製を作成
        const submitData = { ...form.value };

        // due_dateが存在する場合、フォーマットを変換
        if (submitData.due_date) {
            submitData.due_date = new Date(submitData.due_date)
                .toISOString()
                .split('T')[0]; // 'YYYY-MM-DD'形式に変換
        }

        await axios.get('/sanctum/csrf-cookie');
        // 動的タグの作成
        const newTagPromises = dynamicTags.value.map(tag => {
            console.log('タグ作成:', tag);
            return tagStore.createTag({
                name: tag.name,
                color: tag.color
            });
        });

        const createdTags = await Promise.all(newTagPromises);
        console.log('作成されたタグ:', createdTags);

        // 新しく作成されたタグのIDを追加
        const newTagIds = createdTags.map(tag => tag.id);
        form.value.tags = [...form.value.tags, ...newTagIds];

        console.log('最終的なタグID:', form.value.tags);

        // タスクの作成または更新
        if (isEditing.value) {
            await taskStore.updateTask(route.params.id, form.value);
        } else {
            await taskStore.createTask(form.value);
        }

        router.push({ name: 'tasks' });
    } catch (error) {
        console.error('エラーの詳細:', error);

        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    } finally {
        isLoading.value = false;
    }
};
</script>