<template>
    <div class="flex flex-col md:flex-row justify-between items-center mt-4 gap-2 p-4 w-full">
        <!-- Pagination summary -->
        <div>
            <p class="text-sm">
                Showing {{ paginator.data.length }} of {{ paginator.total }} entries
            </p>

        </div>

        <!-- Pagination buttons -->
        <div class="join">
            <template v-for="link in paginator.links" :key="link.label">
                <!-- Disabled buttons -->
                <button v-if="!link.url" class="join-item btn btn-disabled" v-html="formatLabel(link.label)" />

                <!-- Active + Inactive buttons -->
                <Link v-else :href="link.url" preserve-state preserve-scroll class="join-item btn"
                    :class="{ 'btn-primary': link.active }" v-html="formatLabel(link.label)" />
            </template>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    paginator: {
        type: Object,
        required: true
    }
})

/**
 * Format pagination labels
 * - Converts "Previous" → «
 * - Converts "Next" → »
 */
function formatLabel(label) {
    if (label.includes('Previous')) return '&laquo;'
    if (label.includes('Next')) return '&raquo;'
    return label
}
</script>
