<template>
    <draggable v-model="draggedTasks" class="grid gap-4" @end="handleDragEnd" :animation="200" ghost-class="ghost"
        drag-class="drag" :disabled="false" item-key="id" :force-fallback="true" handle=".drag-handle">
        <template #item="{ element: task }">
            <div class="bg-white p-4 rounded shadow">
                <div class="drag-handle">
                    <div class="flex justify-between items-start">
                        <div class="flex-grow">
                            <TaskDisplayContent :task="task" />
                        </div>
                        <div class="flex gap-2 ml-4 flex-shrink-0">
                            <router-link :to="{ name: 'task-edit', params: { id: task.id } }"
                                class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </router-link>
                            <button @click.stop="$emit('delete-task', task.id)" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash-alt"></i>
                            </button>
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