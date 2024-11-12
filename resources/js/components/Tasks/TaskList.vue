<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">タスク一覧</h1>
            <router-link :to="{ name: 'task-create' }" class="button">
                新規タスク
            </router-link>
        </div>

        <div class="space-y-4">
            <TaskCard v-for="task in tasks" :key="task.id" :task="task" @update="loadTasks" @delete="loadTasks" />
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useTaskStore } from '../../stores/task'
import TaskCard from './TaskCard.vue'

const taskStore = useTaskStore()
const tasks = ref([])

const loadTasks = async () => {
    tasks.value = await taskStore.fetchTasks()
}

onMounted(() => {
    loadTasks()
})
</script>