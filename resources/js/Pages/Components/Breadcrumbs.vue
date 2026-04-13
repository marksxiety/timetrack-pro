<template>
    <div class="flex items-center gap-1 text-sm py-3">
        <template v-for="(item, index) in items" :key="index">
            <div class="flex items-center gap-1">
                <component :is="item.route ? Link : 'span'"
                    :href="item.route ? route(item.route, item.params) : undefined" :class="[
                        'px-2 py-1 rounded-lg transition-all duration-150',
                        item.active
                            ? 'bg-primary/10 text-primary font-semibold'
                            : item.route
                                ? 'text-base-content/50 hover:text-base-content hover:bg-base-200 cursor-pointer'
                                : 'text-base-content/30 cursor-default'
                    ]">
                    {{ item.label }}
                </component>
            </div>

            <span v-if="index < items.length - 1" class="text-base-content/25 select-none text-xs">
                /
            </span>
        </template>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    items: {
        type: Array,
        required: true,
    },
})
</script>