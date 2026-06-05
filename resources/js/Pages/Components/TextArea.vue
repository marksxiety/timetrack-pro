<template>
    <div class="mb-4 ta-wrap" :class="{ active: glowing }">
        <label class="block mb-2" :for="name">{{ name }}</label>
        <textarea ref="textareaEl" class="textarea break-words whitespace-normal" :id="name" :type="type"
            v-model="model" :class="[
                'w-full px-4 py-2 input',
                borderClass,
                textClass,
                { 'overflow-hidden': autoResize }
            ]" :placeholder="placeholder" :disabled="disabled" :readonly="readonly"></textarea>
        <p v-if="displayMessage" :class="['mt-1 text-sm px-2 py-1 text-center', textClass]">
            {{ displayMessage }}
        </p>
    </div>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue'

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
    autoResize: {
        type: Boolean,
        default: false
    },
    glowing: {
        type: Boolean,
        default: false
    },
})

const model = defineModel({
    type: null,
    required: true
})

const textareaEl = ref(null)

const isWarning = computed(() => typeof props.message === 'object')
const displayMessage = computed(() => isWarning.value ? props.message?.message : props.message)

const borderClass = computed(() => {
    if (!props.message) return 'focus:ring-blue-200'
    return isWarning.value ? 'border-yellow-500 focus:ring-yellow-200' : 'border-red-500 focus:ring-red-200'
})

const textClass = computed(() => {
    if (!props.message) return ''
    return isWarning.value ? 'text-yellow-600' : 'text-red-600'
})

const resizeTextarea = () => {
    if (!textareaEl.value) return
    textareaEl.value.style.height = 'auto'
    textareaEl.value.style.height = textareaEl.value.scrollHeight + 'px'
}

watch(() => model.value, () => {
    if (props.autoResize) {
        nextTick(resizeTextarea)
    }
})

watch(() => props.autoResize, (val) => {
    if (val) nextTick(resizeTextarea)
})
</script>

<style scoped>
.ta-wrap {
    --inner-radius: 0.45rem;
    position: relative;
    border-radius: 0.5rem;
}

.ta-wrap textarea {
    position: relative;
    z-index: 1;
    border-radius: var(--inner-radius);
}

.ta-wrap.active::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 0.6rem;
    z-index: 0;
    background: conic-gradient(from var(--angle, 0deg),
            var(--color-primary),
            var(--color-secondary),
            var(--color-accent),
            var(--color-primary));
    animation: spin-border 2s linear infinite;
}

.ta-wrap.active::after {
    content: '';
    position: absolute;
    inset: 2px;
    border-radius: var(--inner-radius);
    z-index: 0;
    background: var(--color-base-100);
}

@property --angle {
    syntax: '<angle>';
    initial-value: 0deg;
    inherits: false;
}

@keyframes spin-border {
    to {
        --angle: 360deg;
    }
}

.scan-bar {
    display: none;
    position: absolute;
    left: 4px;
    right: 4px;
    height: 2px;
    border-radius: 2px;
    background: linear-gradient(90deg,
            transparent 0%,
            var(--color-primary) 40%,
            var(--color-secondary) 60%,
            transparent 100%);
    pointer-events: none;
    z-index: 10;
    animation: scan 1.4s ease-in-out infinite;
}

.active .scan-bar {
    display: block;
}

@keyframes scan {
    0% {
        top: 4px;
        opacity: 0;
    }

    8% {
        opacity: 1;
    }

    92% {
        opacity: 1;
    }

    100% {
        top: calc(100% - 4px);
        opacity: 0;
    }
}
</style>