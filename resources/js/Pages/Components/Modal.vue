<template>
    <dialog class="modal" ref="dialog">
        <div class="modal-box h-auto max-h-[85vh] min-w-lg w-auto max-w-xl p-0 flex flex-col" :class="width">
            <!-- Sticky header: never scrolls -->
            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-base-300 flex-shrink-0">
                <h3 class="text-lg font-bold">{{ title }}</h3>
                <button @click="close" class="btn btn-ghost btn-sm btn-circle hover:bg-base-200">
                    <Icon icon="material-symbols:close-rounded" width="20" height="20" />
                </button>
            </div>
            <!-- Scrollable content only -->
            <div class="overflow-y-auto overflow-x-hidden p-6">
                <slot></slot>
            </div>
        </div>
    </dialog>
</template>

<script setup>
import { ref } from 'vue'
import { Icon } from '@iconify/vue'

defineProps({
    title: { type: String, default: '' },
    width: { type: String, default: '' },
})

const dialog = ref(null)

const open = () => dialog.value?.showModal()
const close = () => dialog.value?.close()

defineExpose({ open, close })
</script>
