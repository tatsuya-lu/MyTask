<template>
    <div class="bg-white p-4 rounded shadow">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="font-bold">{{ task.title }}</h3>
                <p class="text-gray-600">{{ task.description }}</p>
            </div>
            <div class="flex space-x-2">
                <button @click="toggleEdit" class="text-blue-500">
                    編集
                </button>
                <button @click="handleDelete" class="text-red-500">
                    削除
                </button>
            </div>
        </div>

        <div class="mt-4">
            <div class="flex items-center space-x-4">
                <span :class="priorityClass">{{ task.priority }}</span>
                <span :class="statusClass">{{ task.status }}</span>
                <span>進捗: {{ task.progress }}%</span>
            </div>
            <div v-if="task.due_date" class="mt-2 text-gray-600">
                期限: {{ formatDate(task.due_date) }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useTaskStore } from '../../stores/task'

const props = defineProps({
    task: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['update', 'delete'])

const taskStore = useTaskStore()

const handleDelete = async () => {
    if (confirm('このタスクを削除してもよろしいですか？')) {
        await taskStore.deleteTask(props.task.id)
        emit('delete')
    }
}

const priorityClass = computed(() => ({
    'text-red-500': props.task.priority === 'high',
    'text-yellow-500': props.task.priority === 'medium',
    'text-green-500': props.task.priority === 'low'
}))

const statusClass = computed(() => ({
    'text-gray-500': props.task.status === 'not_started',
    'text-blue-500': props.task.status === 'in_progress',
    'text-green-500': props.task.status === 'completed'
}))

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ja-JP')
}
</script>