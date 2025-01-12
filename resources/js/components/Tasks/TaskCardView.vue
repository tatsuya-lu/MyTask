<template>
    <draggable v-model="draggedTasks" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" @end="handleDragEnd"
        :animation="200" ghost-class="ghost" drag-class="drag" :disabled="false" item-key="id" :force-fallback="true"
        handle=".drag-handle">
        <template #item="{ element: task }">
            <div class="bg-white p-4 rounded shadow h-48 flex flex-col">
                <div class="drag-handle flex-1">
                    <div class="h-full flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-lg truncate">{{ task.title }}</h3>
                            <div class="flex gap-2">
                                <router-link :to="{ name: 'task-edit', params: { id: task.id } }"
                                    class="text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-edit mr-1"></i>編集
                                </router-link>
                                <button @click.stop="$emit('delete-task', task.id)"
                                    class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash-alt mr-1"></i>削除
                                </button>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm flex-1 overflow-hidden">{{ task.description }}</p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span v-for="tag in task.tags" :key="tag.id" class="px-2 py-1 rounded text-sm" :style="{
                                backgroundColor: tag.color + '20',
                                color: tag.color,
                                borderColor: tag.color,
                                borderWidth: '1px'
                            }">
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </draggable>
</template>

<script setup>
import { computed } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    tasks: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:tasks', 'drag-end', 'delete-task']);

const draggedTasks = computed({
    get: () => props.tasks,
    set: (value) => {
        emit('update:tasks', value);
    }
});

const handleDragEnd = (event) => {
    emit('drag-end', event);
};
</script>