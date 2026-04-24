<template>
    <div :class="[margin, 'w-full']">
        <label v-if="name" for="role" class="block mb-2">{{ name }}</label>
        <select id="role" v-model="model" :disabled="disabled"
            :class="[warning ? 'border-orange-400 focus:ring-orange-200' : message ? 'border-red-500 focus:ring-red-200' : 'focus:ring-blue-200', 'w-full select text-center', size, minwidth]">
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <p v-if="message" class="mt-1 text-sm text-red-600 px-2 py-1 text-center">
            {{ message == '-' ? '' : message }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    name: {
        type: String
    },
    message: String,
    options: Array,
    margin: {
        type: String,
        default: 'mb-4'
    },
    minwidth: {
        type: String,
        default: 'min-w-24 w-full'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    size: {
        type: String,
        default: 'select-md'
    },
    warning: {
        type: Boolean,
        default: false
    }
})

const model = defineModel({
    type: null,
    required: true
})
</script>