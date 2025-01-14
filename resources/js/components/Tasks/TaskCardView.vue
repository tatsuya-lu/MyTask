<template>
    <draggable v-model="draggedTasks" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" @end="handleDragEnd"
        :animation="200" ghost-class="ghost" drag-class="drag" :disabled="false" item-key="id" :force-fallback="true"
        handle=".drag-handle">
        <template #item="{ element: task }">
            <div class="bg-white p-4 rounded shadow h-auto flex flex-col">
                <div class="drag-handle flex-1">
                    <div class="h-full flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <TaskDisplayContent :task="task" />
                            <div class="flex gap-2 ml-2">
                                <router-link :to="{ name: 'task-edit', params: { id: task.id } }"
                                    class="text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-edit"></i>
                                </router-link>
                                <button @click.stop="$emit('delete-task', task.id)"
                                    class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
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
import TaskDisplayContent from './TaskDisplayContent.vue';

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