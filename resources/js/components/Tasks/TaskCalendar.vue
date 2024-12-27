<template>
    <div class="space-y-6">
        <!-- ビュー切り替えタブ -->
        <div class="flex space-x-4 border-b">
            <button v-for="view in views" :key="view.id" @click="currentView = view.id" class="px-4 py-2 font-medium"
                :class="[
                    currentView === view.id
                        ? 'border-b-2 border-blue-500 text-blue-600'
                        : 'text-gray-500 hover:text-gray-700'
                ]">
                {{ view.name }}
            </button>
        </div>

        <!-- 月間ビュー -->
        <div v-if="currentView === 'month'" class="space-y-6">
            <div class="bg-white p-6 rounded shadow">
                <!-- カレンダーヘッダー -->
                <div class="flex justify-between items-center mb-4">
                    <button @click="previousMonth" class="p-2 hover:bg-gray-100 rounded">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h2 class="text-xl font-semibold">
                        {{ currentDate.getFullYear() }}年 {{ currentDate.getMonth() + 1 }}月
                    </h2>
                    <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- 曜日ヘッダー -->
                <div class="grid grid-cols-7 gap-2 mb-2">
                    <div v-for="day in ['日', '月', '火', '水', '木', '金', '土']" :key="day"
                        class="text-center font-medium p-2"
                        :class="{ 'text-red-500': day === '日', 'text-blue-500': day === '土' }">
                        {{ day }}
                    </div>
                </div>

                <!-- カレンダー本体 -->
                <div class="grid grid-cols-7 gap-2">
                    <div v-for="date in monthDates" :key="date.toISOString()"
                        class="min-h-[120px] border p-2 relative group" :class="{
                            'bg-gray-50': !isCurrentMonth(date),
                            'bg-blue-50': isToday(date)
                        }">
                        <div class="flex justify-between items-start mb-2">
                            <div :class="{ 'text-gray-400': !isCurrentMonth(date) }">
                                {{ date.getDate() }}
                            </div>
                            <!-- 追加ボタン -->
                            <button @click="openCreateTaskModal(date)"
                                class="invisible group-hover:visible p-1 hover:bg-gray-100 rounded-full"
                                :class="{ 'text-gray-400': !isCurrentMonth(date) }">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="space-y-1 max-h-[80px] overflow-y-auto">
                            <div v-for="task in getTasksForDate(date)" :key="task.id"
                                class="text-sm p-1 rounded group flex justify-between items-center"
                                :class="getTaskPriorityClass(task)">
                                <span @click="openTaskModal(task.id)" class="cursor-pointer flex-grow">
                                    {{ task.title }}
                                </span>
                                <button @click.stop="confirmDeleteTask(task)"
                                    class="invisible group-hover:visible text-gray-500 hover:text-red-500 px-1">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 年間ビュー -->
        <div v-else class="space-y-8">
            <div v-for="year in [currentYear]" :key="year" class="space-y-6">
                <div class="flex justify-between items-center">
                    <button @click="previousYear" class="p-2 hover:bg-gray-100 rounded">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h2 class="text-2xl font-bold">{{ year }}年</h2>
                    <button @click="nextYear" class="p-2 hover:bg-gray-100 rounded">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <div v-for="month in 12" :key="month"
                        class="bg-white p-4 rounded shadow hover:shadow-md transition-shadow cursor-pointer"
                        @click="switchToMonth(year, month - 1)">
                        <h3 class="text-lg font-semibold mb-4 text-center">{{ month }}月</h3>

                        <!-- 月のカレンダー -->
                        <div class="grid grid-cols-7 gap-1">
                            <div v-for="day in ['日', '月', '火', '水', '木', '金', '土']" :key="day"
                                class="text-center text-xs font-medium" :class="{
                                    'text-red-500': day === '日',
                                    'text-blue-500': day === '土'
                                }">
                                {{ day.charAt(0) }}
                            </div>

                            <div v-for="date in getMonthDates(year, month - 1)" :key="date.toISOString()"
                                class="relative text-center text-xs p-1" :class="{
                                    'text-gray-400': date.getMonth() !== month - 1,
                                    'bg-blue-50': isToday(date)
                                }">
                                {{ date.getDate() }}
                                <!-- タスクインジケーター（複数の場合は数を表示） -->
                                <div v-if="getTasksForDate(date).length"
                                    class="absolute bottom-0 left-1/2 transform -translate-x-1/2"
                                    :class="{ 'bg-blue-500 text-white rounded-full w-4 h-4 text-[10px] flex items-center justify-center': getTasksForDate(date).length > 1 }">
                                    <template v-if="getTasksForDate(date).length === 1">
                                        <div class="h-1 w-1 bg-blue-500 rounded-full"></div>
                                    </template>
                                    <template v-else>
                                        {{ getTasksForDate(date).length }}
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- タスク数のサマリー -->
                        <div class="mt-2 text-xs text-gray-600 text-center">
                            タスク: {{ getMonthTaskCount(year, month - 1) }}件
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 削除確認モーダル -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4">タスクの削除</h3>
                <p class="mb-6">「{{ taskToDelete?.title }}」を削除してもよろしいですか？</p>
                <div class="flex justify-end space-x-4">
                    <button @click="cancelDelete" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
                        キャンセル
                    </button>
                    <button @click="executeDelete" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">
                        削除
                    </button>
                </div>
            </div>
        </div>

        <!-- タスク編集/作成モーダル -->
        <div v-if="showTaskModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b flex justify-between items-center">
                    <h2 class="text-xl font-bold">{{ modalTitle }}</h2>
                    <button @click="closeTaskModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4">
                    <TaskForm v-if="showTaskModal" :task-id="selectedTaskId" :initial-date="selectedDate"
                        :is-modal="true" @saved="handleTaskSaved" @cancelled="closeTaskModal" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useTaskStore } from '@/stores/task'
import TaskForm from './TaskForm.vue'

const views = [
    { id: 'month', name: '月間表示' },
    { id: 'year', name: '年間表示' }
]

const currentView = ref('month')
const currentDate = ref(new Date())
const currentYear = ref(new Date().getFullYear())
const showTaskModal = ref(false)
const selectedTaskId = ref(null)
const taskStore = useTaskStore()

const showDeleteModal = ref(false)
const taskToDelete = ref(null)

const confirmDeleteTask = (task) => {
    taskToDelete.value = task
    showDeleteModal.value = true
}

const cancelDelete = () => {
    showDeleteModal.value = false
    taskToDelete.value = null
}

const executeDelete = async () => {
    try {
        await taskStore.deleteTask(taskToDelete.value.id)
        await loadTasks()
    } catch (error) {
        console.error('タスクの削除に失敗しました:', error)
    } finally {
        showDeleteModal.value = false
        taskToDelete.value = null
    }
}

const selectedDate = ref(null)

const openCreateTaskModal = (date) => {
    selectedDate.value = date
    selectedTaskId.value = null
    showTaskModal.value = true
}

const modalTitle = computed(() => {
    return selectedTaskId.value ? 'タスクの編集' : '新規タスク作成'
})

const monthDates = computed(() => {
    const dates = []
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const firstDay = new Date(year, month, 1).getDay()

    for (let i = firstDay - 1; i >= 0; i--) {
        dates.push(new Date(year, month, -i))
    }

    const lastDate = new Date(year, month + 1, 0).getDate()
    for (let i = 1; i <= lastDate; i++) {
        dates.push(new Date(year, month, i))
    }

    const remainingDays = 42 - dates.length
    for (let i = 1; i <= remainingDays; i++) {
        dates.push(new Date(year, month + 1, i))
    }

    return dates
})

// 年間ビューの月ごとの日付生成
const getMonthDates = (year, month) => {
    const dates = []
    const firstDay = new Date(year, month, 1).getDay()

    for (let i = firstDay - 1; i >= 0; i--) {
        dates.push(new Date(year, month, -i))
    }

    const lastDate = new Date(year, month + 1, 0).getDate()
    for (let i = 1; i <= lastDate; i++) {
        dates.push(new Date(year, month, i))
    }

    const remainingDays = 42 - dates.length
    for (let i = 1; i <= remainingDays; i++) {
        dates.push(new Date(year, month + 1, i))
    }

    return dates
}

// モーダル関連の関数
const openTaskModal = (taskId) => {
    selectedTaskId.value = taskId
    showTaskModal.value = true
}

const closeTaskModal = () => {
    showTaskModal.value = false
    selectedTaskId.value = null
}

const handleTaskSaved = async () => {
    await loadTasks()
    closeTaskModal()
}

// ナビゲーション関連の関数
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

const previousYear = () => {
    currentYear.value--
    loadTasks()
}

const nextYear = () => {
    currentYear.value++
    loadTasks()
}

const switchToMonth = (year, month) => {
    currentDate.value = new Date(year, month)
    currentView.value = 'month'
}

// ユーティリティ関数
const isCurrentMonth = (date) => {
    return date.getMonth() === currentDate.value.getMonth()
}

const isToday = (date) => {
    const today = new Date()
    return date.toDateString() === today.toDateString()
}

const getTasksForDate = (date) => {
    return taskStore.tasks.filter(task => {
        const taskDate = new Date(task.due_date)
        return taskDate.toDateString() === date.toDateString()
    })
}

const getMonthTaskCount = (year, month) => {
    return taskStore.tasks.filter(task => {
        const taskDate = new Date(task.due_date)
        return taskDate.getFullYear() === year && taskDate.getMonth() === month
    }).length
}

const getTaskPriorityClass = (task) => ({
    'bg-red-100': task.priority === 'high',
    'bg-yellow-100': task.priority === 'medium',
    'bg-green-100': task.priority === 'low'
})

// タスクの読み込み
const loadTasks = async () => {
    // 表示している月の前後1ヶ月分も含めて取得
    const displayDate = currentDate.value
    const startDate = new Date(displayDate.getFullYear(), displayDate.getMonth() - 1, 1)
    const endDate = new Date(displayDate.getFullYear(), displayDate.getMonth() + 2, 0)

    const filters = {
        start_date: startDate.toISOString().split('T')[0],
        end_date: endDate.toISOString().split('T')[0]
    }

    await taskStore.fetchTasks(filters)
}

const loadYearTasks = async () => {
    const filters = {
        start_date: `${currentYear.value}-01-01`,
        end_date: `${currentYear.value}-12-31`
    }
    await taskStore.fetchTasks(filters)
}

// 表示切り替え時の処理を追加
watch(currentView, async (newView) => {
    if (newView === 'month') {
        await loadTasks()
    } else {
        await loadYearTasks()
    }
})

// 月変更時の処理
watch(currentDate, async () => {
    if (currentView.value === 'month') {
        await loadTasks()
    }
})

// 年変更時の処理
watch(currentYear, async () => {
    if (currentView.value === 'year') {
        await loadYearTasks()
    }
})

onMounted(() => {
    if (currentView.value === 'month') {
        loadTasks()
    } else {
        loadYearTasks()
    }
})
</script>