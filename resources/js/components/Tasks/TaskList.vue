<template>
    <div class="container mx-auto px-4 py-8">
        <!-- ヘッダー部分 -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">タスク一覧</h1>
            <div class="flex gap-2">
                <!-- 保存済み並び順の選択 -->
                <!-- <select v-model="selectedOrderId" @change="handleSavedOrderSelect"
                    class="border rounded-md py-2 px-3 text-gray-700">
                    <option value="">保存済みの並び順</option>
                    <option v-for="order in taskStore.savedOrders" :key="order.id" :value="order.id">
                        {{ order.name || order.created_at }}
                        {{ order.description ? `(${order.description})` : '' }}
                    </option>
                </select> -->

                <!-- 保存済み並び順の選択（カスタムドロップダウン） -->
                <div class="relative">
                    <button @click="toggleOrderDropdown"
                        class="border rounded-md py-2 px-3 text-gray-700 bg-white flex items-center justify-between min-w-[200px]">
                        <span>{{ selectedOrderName || '保存済みの並び順' }}</span>
                        <span class="ml-2">▼</span>
                    </button>

                    <!-- ドロップダウンメニュー -->
                    <div v-if="isOrderDropdownOpen"
                        class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                        <div class="py-1">
                            <div v-if="taskStore.savedOrders.length === 0" class="px-4 py-2 text-gray-500">
                                保存された並び順はありません
                            </div>
                            <div v-else>
                                <div v-for="order in taskStore.savedOrders" :key="order.id"
                                    class="flex items-center justify-between px-4 py-2 hover:bg-gray-100">
                                    <button @click="selectOrder(order)" class="flex-grow text-left">
                                        {{ order.name || order.created_at }}
                                        <span v-if="order.description" class="text-sm text-gray-500 block">
                                            {{ order.description }}
                                        </span>
                                    </button>
                                    <button @click.stop="confirmDeleteOrder(order)"
                                        class="text-red-500 hover:text-red-700 px-2">
                                        <span class="sr-only">削除</span>
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 既存のソート選択 -->
                <select v-model="sortType" @change="handleSort" class="border rounded-md py-2 px-3 text-gray-700">
                    <option value="">並び順を選択</option>
                    <option value="created_desc">作成日（新しい順）</option>
                    <option value="due_date">期限日順</option>
                </select>

                <!-- 並び順の保存ボタン -->
                <button v-if="taskStore.isCustomOrder" @click="handleSaveOrder"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    現在の並び順を保存
                </button>

                <router-link :to="{ name: 'task-create' }"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    新規タスク
                </router-link>
            </div>
        </div>

        <!-- 検索フィールド -->
        <div class="mb-4">
            <input type="text" v-model="searchQuery" placeholder="タスクを検索..."
                class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                @input="handleSearch">
        </div>

        <!-- フィルター部分 -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ステータス
                    </label>
                    <select v-model="selectedStatus" class="w-full border rounded-md py-2 px-3 text-gray-700"
                        @change="handleFilterChange">
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
                    <select v-model="selectedPriority" class="w-full border rounded-md py-2 px-3 text-gray-700"
                        @change="handleFilterChange">
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
                    <select v-model="selectedTagId" class="w-full border rounded-md py-2 px-3 text-gray-700"
                        @change="handleFilterChange">
                        <option value="">すべて</option>
                        <option v-for="tag in tagStore.tags" :key="tag.id" :value="tag.id">
                            {{ tag.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- タスク一覧 -->
        <div v-if="isLoading" class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
        </div>

        <div v-else-if="filteredTasks.length === 0" class="text-center py-8 text-gray-500">
            タスクが見つかりません
        </div>

        <draggable v-model="draggedTasks" class="grid gap-4" @end="handleDragEnd" :animation="200" ghost-class="ghost"
            drag-class="drag" :disabled="false" item-key="id" :force-fallback="true" handle=".drag-handle">
            <template #item="{ element: task }">
                <div class="bg-white p-4 rounded shadow">
                    <div class="drag-handle">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold">{{ task.title }}</h3>
                                <p class="text-gray-600 text-sm mt-1">{{ task.description }}</p>
                                <!-- タグの表示 -->
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <span v-for="tag in task.tags" :key="tag.id" class="px-2 py-1 rounded text-sm"
                                        :style="{
                                            backgroundColor: tag.color + '20',
                                            color: tag.color,
                                            borderColor: tag.color,
                                            borderWidth: '1px'
                                        }">
                                        {{ tag.name }}
                                    </span>
                                </div>
                            </div>
                            <!-- 操作ボタン -->
                            <div class="flex gap-2">
                                <router-link :to="{ name: 'task-edit', params: { id: task.id } }"
                                    class="text-blue-500 hover:text-blue-700">
                                    編集
                                </router-link>
                                <button @click.stop="deleteTask(task.id)" class="text-red-500 hover:text-red-700">
                                    削除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </draggable>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useTaskStore } from '@/stores/task';
import { useTagStore } from '@/stores/tag';
import draggable from 'vuedraggable';
import { useRouter } from 'vue-router';

const router = useRouter();
const taskStore = useTaskStore();
const tagStore = useTagStore();

const { isLoading, error } = storeToRefs(taskStore);
const tasks = computed(() => taskStore.tasks);
const filteredTasks = computed(() => taskStore.filteredTasks);

const draggedTasks = computed({
    get: () => filteredTasks.value,
    set: (value) => {
        // handleDragEndで処理するため、ここでは何もしない
    }
});

const selectedOrderId = ref('');

const sortType = ref('');

// ドラッグ&ドロップ終了時の処理
const handleDragEnd = async (event) => {
    if (event.oldIndex === event.newIndex) return;

    try {
        const newOrder = [...draggedTasks.value];
        const movedItem = newOrder.splice(event.oldIndex, 1)[0];
        newOrder.splice(event.newIndex, 0, movedItem);

        const taskIds = newOrder.map(task => task.id);
        await taskStore.updateTaskOrder(taskIds);
    } catch (error) {
        console.error('並び順の更新に失敗:', error);
    }
};

// ソート処理
const handleSort = async () => {
    if (sortType.value) {
        await taskStore.applySort(sortType.value);
    }
};

// フィルター状態
const selectedStatus = ref('');
const selectedPriority = ref('');
const selectedTagId = ref('');
const searchQuery = ref('');

// 検索とフィルターのハンドラー
const handleSearch = () => {
    taskStore.setFilter('searchQuery', searchQuery.value);
};

const handleFilterChange = () => {
    taskStore.setFilter('status', selectedStatus.value);
    taskStore.setFilter('priority', selectedPriority.value);
    taskStore.setFilter('tagId', selectedTagId.value);
};

// 保存済み並び順の選択
const handleSavedOrderSelect = async () => {
    if (!selectedOrderId.value) return;

    const order = taskStore.savedOrders.find(o => o.id === parseInt(selectedOrderId.value));
    if (order) {
        try {
            await taskStore.applySavedOrder(order);
        } catch (error) {
            console.error('並び順の適用に失敗:', error);
        }
    }
};

// 現在の並び順を保存
const handleSaveOrder = async () => {
    const name = prompt('並び順の名前を入力してください（省略可）:');
    if (name === null) return; // キャンセル時

    const description = prompt('説明を入力してください（省略可）:');
    if (description === null) return; // キャンセル時

    try {
        await taskStore.saveTaskOrder(
            draggedTasks.value.map(task => task.id),
            name || null,
            description || null
        );
    } catch (error) {
        console.error('並び順の保存に失敗:', error);
    }
};

const isOrderDropdownOpen = ref(false);
const selectedOrderName = computed(() => {
    if (!selectedOrderId.value) return '';
    const order = taskStore.savedOrders.find(o => o.id === parseInt(selectedOrderId.value));
    return order ? (order.name || order.created_at) : '';
});

// ドロップダウンの表示/非表示を切り替え
const toggleOrderDropdown = () => {
    isOrderDropdownOpen.value = !isOrderDropdownOpen.value;
};

// 並び順の選択
const selectOrder = async (order) => {
    selectedOrderId.value = order.id;
    await handleSavedOrderSelect();
    isOrderDropdownOpen.value = false;
};

// 削除の確認と実行
const confirmDeleteOrder = async (order) => {
    const message = order.name
        ? `並び順「${order.name}」を削除してもよろしいですか？`
        : '選択した並び順を削除してもよろしいですか？';

    if (confirm(message)) {
        try {
            await taskStore.deleteSavedOrder(order.id);
            if (selectedOrderId.value === order.id) {
                selectedOrderId.value = '';
            }
            isOrderDropdownOpen.value = false;
        } catch (error) {
            console.error('並び順の削除に失敗しました:', error);
        }
    }
};

// クリックイベントのハンドラーを追加
onMounted(() => {
    document.addEventListener('click', (event) => {
        const dropdown = document.querySelector('.relative');
        if (dropdown && !dropdown.contains(event.target)) {
            isOrderDropdownOpen.value = false;
        }
    });
});

onMounted(async () => {
    await Promise.all([
        taskStore.fetchTasks(),
        taskStore.fetchSavedOrders(),
        tagStore.fetchTags()
    ]);
});

watch(() => taskStore.isCustomOrder, (newValue) => {
    if (!newValue) {
        selectedOrderId.value = '';
    }
});

const deleteTask = async (taskId) => {
    if (confirm('このタスクを削除してもよろしいですか？')) {
        try {
            await taskStore.deleteTask(taskId);
        } catch (error) {
            console.error('タスクの削除に失敗しました:', error);
        }
    }
};
</script>

<style>
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.drag {
    opacity: 0.9;
}

.drag-handle {
    cursor: move;
    touch-action: none;
    width: 100%;
    height: 100%;
}

.relative {
    position: relative;
}

.absolute {
    position: absolute;
}

.z-10 {
    z-index: 10;
}
</style>