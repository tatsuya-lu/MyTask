<template>
    <button type="button" :class="[
        'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
        modelValue ? 'bg-indigo-600' : 'bg-gray-200'
    ]" @click="$emit('update:modelValue', !modelValue)">
        <span :class="[
            'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
            modelValue ? 'translate-x-5' : 'translate-x-0'
        ]" />
    </button>
</template>

<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        required: true
    }
})

defineEmits(['update:modelValue'])
</script>