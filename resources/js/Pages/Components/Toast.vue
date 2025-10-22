<template>
  <div class="toast toast-top toast-end fixed z-50 mt-6 mr-4">
    <div v-for="(toast, i) in toasts" :key="i" class="alert border-2 rounded-lg shadow-lg flex items-center gap-3"
      :class="[
        toast.type === 'success' ? 'alert-success border-success' :
          toast.type === 'error' ? 'alert-error border-error' :
            toast.type === 'info' ? 'alert-info border-info' :
              toast.type === 'warning' ? 'alert-warning border-warning' :
                ''
      ]">
      <!-- Icon based on toast type -->
      <Icon :icon="getToastIcon(toast.type)" width="24" height="24" class="flex-shrink-0" />

      <!-- Message -->
      <span class="flex-1">{{ toast.message }}</span>

      <!-- Close button -->
      <button @click="removeToast(i)" class="btn btn-ghost btn-sm btn-circle" aria-label="Close">
        <Icon icon="tabler:x" width="18" height="18" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Icon } from '@iconify/vue'

const toasts = ref([])

function getToastIcon(type) {
  const icons = {
    'success': 'tabler:circle-check',
    'error': 'tabler:circle-x',
    'info': 'tabler:info-circle',
    'warning': 'tabler:alert-triangle'
  }
  return icons[type] || 'tabler:bell'
}

function showToast(message, type = 'info') {
  toasts.value.push({ message, type })
  setTimeout(() => {
    toasts.value.shift()
  }, 5000)
}

function removeToast(index) {
  toasts.value.splice(index, 1)
}

defineExpose({ showToast })
</script>