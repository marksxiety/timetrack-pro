<template>
    <div class="mb-4">
        <label class="block mb-2" for="name">{{ name }}</label>
        <textarea class="textarea break-words whitespace-normal" :id="name" :type="type" v-model="model"
            :class="['w-full px-4 py-2 input', borderClass, textCase]"
            :placeholder="placeholder" :disabled="disabled" :readonly="readonly"></textarea>
        <p v-if="displayMessage" :class="['mt-1 text-sm px-2 py-1 text-center', textClass]">
            {{ displayMessage }}
        </p>
    </div>
</template>



<script setup>
import { computed } from 'vue'

const props = defineProps({
    name: {
        type: String
    },
    type: {
        type: String,
        default: 'text'
    },
    message: [String, Object],
    placeholder: {
        type: String,
        default: 'Enter your input'
    },
    textCase: {
        type: String,
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    },
    readonly: {
        type: Boolean,
        default: false
    },
})

const model = defineModel({
    type: null,
    required: true
})

const isWarning = computed(() => typeof props.message === 'object')
const displayMessage = computed(() => isWarning.value ? props.message?.message : props.message)

const borderClass = computed(() => {
    if (!props.message) return 'focus:ring-blue-200'
    return isWarning.value ? 'border-yellow-500 focus:ring-yellow-200' : 'border-red-500 focus:ring-red-200'
})

const textClass = computed(() => {
    return isWarning.value ? 'text-yellow-600' : 'text-red-600'
})
</script>
