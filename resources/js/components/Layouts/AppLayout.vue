<template>
    <div class="min-h-screen bg-gray-100">
        <AppHeader />
        <main class="container mx-auto px-4 py-6">
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue'
</script>