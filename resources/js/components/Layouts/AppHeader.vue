<template>
    <nav>
        <div>
            <div>
                <div>
                    <router-link :to="{ name: 'tasks' }">
                        <span>MyTask</span>
                    </router-link>
                </div>

                <div v-if="isLoggedIn">
                    <router-link :to="{ name: 'task-create' }">
                        新規タスク
                    </router-link>
                    <router-link :to="{ name: 'task-calendar' }">
                        カレンダー
                    </router-link>
                    <button @click="handleLogout">ログアウト</button>
                </div>
                <div v-else>
                    <router-link :to="{ name: 'login' }">
                        ログイン
                    </router-link>
                    <router-link :to="{ name: 'register' }">
                        新規登録
                    </router-link>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>

</script>