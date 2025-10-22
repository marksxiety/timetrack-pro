<template>
    <div :class="margin">
        <label v-if="name" class="block mb-2" for="name">{{ name }}</label>
        <input :id="name" :type="type" v-model="model"
            :class="['w-full px-4 py-2 input', message ? 'border-red-500 focus:ring-red-200' : 'focus:ring-blue-200', textCase]"
            :placeholder="placeholder" :disabled="disabled" :readonly="readonly" />
        <p v-if="message" class="mt-1 text-sm text-red-600 px-2 py-1 text-center">
            {{ message }}
        </p>
    </div>
</template>


<script setup>

defineProps({
    name: {
        type: String
    },
    type: {
        type: String,
        default: 'text'
    },
    message: String,
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
    margin: {
        type: String,
        required: false,
        default: 'mb-4'
    }
})

const model = defineModel({
    type: null,
    required: true
})

</script>