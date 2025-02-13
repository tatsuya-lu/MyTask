<template>
    <div class="container mx-auto px-4 py-8">
        <h1 v-if="!isModal" class="text-2xl font-bold mb-6">
            {{ isEditing ? 'タスクの編集' : '新規タスク作成' }}
        </h1>

        <form @submit.prevent="handleSubmit" class="max-w-2xl" novalidate>
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
                    進捗 ({{ Number(form.progress) }}%)
                </label>
                <input id="progress" v-model.number="form.progress" type="range" min="0" max="100" step="10"
                    class="w-full">
            </div>

            <!-- 期限日 -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="due_date">
                    期限日
                </label>
                <Datepicker v-model="form.due_date" locale="ja" weekStart="0" :enable-time-picker="false"
                    :clearable="true" :auto-apply="true" :format="(date) => formatDateForDisplay(date)"
                    input-class-name="w-full px-4 py-2 border rounded-lg" placeholder="期限日を選択"
                    :day-class="getDayClass" />
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
                <button type="button" @click="handleCancel"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    キャンセル
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
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

const isEditing = computed(() => {
    if (props.isModal) {
        return !!props.taskId;
    }
    return !!route.params.id;
});
const isLoading = ref(false);
const errors = ref({});

const getDayClass = (date) => {
    const dayOfWeek = new Date(date).getDay();
    if (dayOfWeek === 0) return 'dp-weekend-sunday';
    if (dayOfWeek === 6) return 'dp-weekend-saturday';
    return '';
};

const form = ref({
    title: '',
    description: '',
    priority: 'low',
    status: 'not_started',
    progress: 0,
    due_date: null,
    tags: []
});

const props = defineProps({
    taskId: {
        type: [Number, String],
        default: null
    },
    isModal: {
        type: Boolean,
        default: false
    },
    initialDate: {
        type: Date,
        default: null
    }
});

const emit = defineEmits(['saved', 'cancelled'])

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

const formatDateForDisplay = (date) => {
    if (!date) return '';
    const d = new Date(date);
    if (isNaN(d.getTime())) return '';
    return `${d.getFullYear()}年${String(d.getMonth() + 1).padStart(2, '0')}月${String(d.getDate()).padStart(2, '0')}日`;
};

const handleSubmit = async () => {
    isLoading.value = true;
    errors.value = {};

    try {
        const submitData = { ...form.value };

        if (submitData.due_date) {
            const d = new Date(submitData.due_date);
            if (!isNaN(d.getTime())) {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                submitData.due_date = `${year}-${month}-${day}`;
            }
        }

        await axios.get('/sanctum/csrf-cookie');

        const newTagPromises = dynamicTags.value.map(tag => {
            return tagStore.createTag({
                name: tag.name,
                color: tag.color
            });
        });

        const createdTags = await Promise.all(newTagPromises);
        const newTagIds = createdTags.map(tag => tag.id);
        submitData.tags = [...(submitData.tags || []), ...newTagIds];

        if (isEditing.value) {
            const taskId = props.isModal ? props.taskId : route.params.id;
            await taskStore.updateTask(taskId, submitData);
        } else {
            const newTask = await taskStore.createTask(submitData);
            if (!newTask || !newTask.id) {
                throw new Error('タスクの作成に失敗しました');
            }
        }

        if (props.isModal) {
            emit('saved');
        } else {
            router.push({ name: 'tasks' });
        }
    } catch (error) {
        console.error('エラーの詳細:', error);
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    } finally {
        isLoading.value = false;
    }
};

const handleCancel = () => {
    if (props.isModal) {
        emit('cancelled')
    } else {
        router.push({ name: 'tasks' })
    }
}

onMounted(async () => {
    try {
        await tagStore.fetchTags();
    } catch (error) {
        console.error('タグの取得に失敗しました:', error);
    }

    const targetTaskId = props.isModal ? props.taskId : route.params.id;

    if (targetTaskId) {
        try {
            const response = await axios.get(`/api/tasks/${targetTaskId}`);
            const task = response.data.data;

            // 日付の処理を修正
            let dueDate = null;
            if (task.due_date) {
                const [year, month, day] = task.due_date.split('-');
                dueDate = new Date(year, month - 1, day);
            }

            form.value = {
                ...task,
                progress: Number(task.progress || 0),
                due_date: dueDate,
                priority: task.priority || 'low',
                status: task.status || 'not_started',
                tags: task.tags ? task.tags.map(tag => tag.id) : []
            };
        } catch (error) {
            console.error('タスクの取得に失敗しました:', error);
            if (!props.isModal) {
                router.push({ name: 'tasks' });
            }
        }
    } else if (props.initialDate) {
        form.value.due_date = new Date(props.initialDate);
    }
});

// 日付の変更を監視して正しい形式かチェックするwatcherを追加
watch(() => form.value.due_date, (newValue) => {
    if (newValue && !(newValue instanceof Date)) {
        // 日付形式ではない値が設定された場合、Dateオブジェクトに変換を試みる
        const d = new Date(newValue);
        if (!isNaN(d.getTime())) {
            form.value.due_date = d;
        } else {
            console.error('Invalid date value:', newValue);
        }
    }
});
</script>
<style>
.dp-weekend-saturday {
    color: #3b82f6;
}

.dp-weekend-sunday {
    color: #ef4444;
}
</style>