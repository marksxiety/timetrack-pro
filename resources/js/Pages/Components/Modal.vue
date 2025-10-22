<template>
    <dialog class="modal" ref="dialog">
        <div class="modal-box  h-auto max-h-[85vh] overflow-auto min-w-lg w-auto max-w-xl">
            <slot></slot>
        </div>
    </dialog>
</template>

<script setup>
import { ref, defineExpose } from 'vue'

const dialog = ref(null)

const open = () => dialog.value?.showModal()
const close = () => dialog.value?.close()

defineExpose({ open, close })
</script>