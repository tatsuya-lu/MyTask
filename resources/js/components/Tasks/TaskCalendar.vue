<template>
    <div>
        <h1 class="text-2xl font-bold mb-6">タスクカレンダー</h1>

        <div class="bg-white p-6 rounded shadow">
            <!-- カレンダーヘッダー -->
            <div class="flex justify-between items-center mb-4">
                <button @click="previousMonth" class="p-2">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="text-xl font-semibold">
                    {{ currentDate.getFullYear() }}年 {{ currentDate.getMonth() + 1 }}月
                </h2>
                <button @click="nextMonth" class="p-2">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- 曜日ヘッダー -->
            <div class="grid grid-cols-7 gap-2 mb-2">
                <div v-for="day in ['日', '月', '火', '水', '木', '金', '土']" :key="day" class="text-center font-medium p-2"
                    :class="{ 'text-red-500': day === '日', 'text-blue-500': day === '土' }">
                    {{ day }}
                </div>
            </div>

            <!-- カレンダー本体 -->
            <div class="grid grid-cols-7 gap-2">
                <div v-for="date in calendarDates" :key="date.toISOString()" class="min-h-[100px] border p-2" :class="{
                    'bg-gray-100': !isCurrentMonth(date),
                    'bg-blue-50': isToday(date)
                }">
                    <div class="text-right mb-2" :class="{ 'text-gray-400': !isCurrentMonth(date) }">
                        {{ date.getDate() }}
                    </div>

                    <!-- その日のタスク一覧 -->
                    <div class="space-y-1">
                        <div v-for="task in getTasksForDate(date)" :key="task.id" class="text-sm p-1 rounded" :class="{
                            'bg-red-100': task.priority === 'high',
                            'bg-yellow-100': task.priority === 'medium',
                            'bg-green-100': task.priority === 'low'
                        }">
                            {{ task.title }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTaskStore } from '@/stores/task'

const taskStore = useTaskStore()
const currentDate = ref(new Date())
const tasks = ref([])

// カレンダーの日付を生成
const calendarDates = computed(() => {
    const dates = []
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()

    // 月の最初の日の曜日を取得
    const firstDay = new Date(year, month, 1).getDay()

    // 前月の日を追加
    for (let i = firstDay - 1; i >= 0; i--) {
        dates.push(new Date(year, month, -i))
    }

    // 当月の日を追加
    const lastDate = new Date(year, month + 1, 0).getDate()
    for (let i = 1; i <= lastDate; i++) {
        dates.push(new Date(year, month, i))
    }

    // 次月の日を追加（6週間分になるまで）
    const remainingDays = 42 - dates.length
    for (let i = 1; i <= remainingDays; i++) {
        dates.push(new Date(year, month + 1, i))
    }

    return dates
})

// 日付に関するユーティリティ関数
const isCurrentMonth = (date) => {
    return date.getMonth() === currentDate.value.getMonth()
}

const isToday = (date) => {
    const today = new Date()
    return date.toDateString() === today.toDateString()
}

const getTasksForDate = (date) => {
    return tasks.value.filter(task => {
        const taskDate = new Date(task.due_date)
        return taskDate.toDateString() === date.toDateString()
    })
}

// 月の移動
const previousMonth = () => {
    currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() - 1
    )
    loadTasks()
}

const nextMonth = () => {
    currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() + 1
    )
    loadTasks()
}

// タスクの読み込み
const loadTasks = async () => {
    // filterを追加
    const filters = {
        month: currentDate.value.getMonth() + 1,
        year: currentDate.value.getFullYear()
    };
    await taskStore.fetchTasks(filters);
    // ストアから直接タスクを取得
    tasks.value = taskStore.tasks;
}

onMounted(() => {
    loadTasks()
})
</script>