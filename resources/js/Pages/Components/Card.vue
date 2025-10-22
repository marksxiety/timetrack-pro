<template>
    <component :is="routename ? Link : 'div'" :href="routename ? route(routename, parameters) : undefined" :class="[
        'stat bg-base-100 shadow',
        routename ? 'hover:bg-primary hover:cursor-pointer' : ''
    ]">
        <div class="stat-title">{{ title }}</div>
        <div class="stat-value">{{ value }}</div>
        <div v-if="description" class="stat-desc">{{ description }}</div>
    </component>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    title: String | Number,
    value: String | Number,
    description: String,
    routename: String,
    parameters: Object
})
</script>