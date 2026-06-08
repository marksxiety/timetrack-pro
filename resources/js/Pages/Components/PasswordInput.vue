<template>
    <label class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all w-full">
        <Icon :icon="prefixIcon" width="16" height="16" class="text-base-content/40 shrink-0" />
        <input :type="type" class="grow bg-transparent outline-none text-sm"
            :placeholder="placeholder" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
            autocomplete="off" />
    </label>
</template>

<script setup>
import { Icon } from '@iconify/vue'

defineProps({
    modelValue: { type: String, default: '' },
    type: { type: String, default: 'password' },
    placeholder: { type: String, default: '' },
    prefixIcon: { type: String, default: 'material-symbols:lock-outline' },
})

defineEmits(['update:modelValue'])
</script>
