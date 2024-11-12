<template>
    <div>
        <AppHeader />
        <main>
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue'
</script>