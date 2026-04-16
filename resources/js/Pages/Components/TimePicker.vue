<template>
    <div :class="[margin, 'w-full']">
        <label v-if="name" class="block mb-2 text-sm font-medium">{{ name }}</label>

        <div class="relative" ref="wrapperRef">
            <!-- Trigger -->
            <button type="button" @click="togglePicker" :class="[
                'flex items-center gap-2 w-full px-3 h-10 rounded-lg border bg-base-100 transition-all text-left',
                isOpen ? 'border-primary ring-2 ring-primary/20' : 'border-base-300 hover:border-base-content/40',
                message ? 'border-error' : ''
            ]">
                <Icon icon="material-symbols:schedule-outline" width="16" height="16"
                    class="opacity-50 flex-shrink-0" />
                <span v-if="model" class="flex-1 text-sm font-medium">{{ displayValue }}</span>
                <span v-else class="flex-1 text-sm opacity-40">Select time</span>
                <Icon icon="material-symbols:expand-more-rounded" width="16" height="16" class="opacity-40"
                    :class="{ 'rotate-180': isOpen }" />
            </button>

            <!-- Popover -->
            <div v-if="isOpen"
                class="absolute top-[calc(100%+6px)] left-0 z-50 bg-base-100 border border-base-300 rounded-xl shadow-lg w-52 overflow-hidden">
                <!-- Columns -->
                <div class="flex relative">
                    <!-- Hour column -->
                    <div ref="hourColRef" class="flex-1 h-48 overflow-y-auto scrollbar-none scroll-smooth relative">
                        <div
                            class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-0 right-0 h-10 bg-primary/5 border-y border-primary/20 z-10" />
                        <div class="py-[80px]">
                            <div v-for="h in hours" :key="h" @click="selectHour(h)" :class="[
                                'h-10 flex items-center justify-center text-sm cursor-pointer transition-colors relative z-20',
                                selectedHour === h ? 'text-primary font-semibold' : 'text-base-content/50 hover:text-base-content'
                            ]">
                                {{ String(h).padStart(2, '0') }}
                            </div>
                        </div>
                    </div>

                    <div class="w-px bg-base-200" />

                    <!-- Minute column -->
                    <div ref="minColRef" class="flex-1 h-48 overflow-y-auto scrollbar-none scroll-smooth relative">
                        <div
                            class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-0 right-0 h-10 bg-primary/5 border-y border-primary/20 z-10" />
                        <div class="py-[80px]">
                            <div v-for="m in minutes" :key="m" @click="selectMinute(m)" :class="[
                                'h-10 flex items-center justify-center text-sm cursor-pointer transition-colors relative z-20',
                                selectedMinute === m ? 'text-primary font-semibold' : 'text-base-content/50 hover:text-base-content'
                            ]">
                                {{ String(m).padStart(2, '0') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AM/PM -->
                <div class="flex border-t border-base-200">
                    <button type="button" @click="setAmPm('AM')"
                        :class="['flex-1 py-2 text-sm font-medium transition-colors border-r border-base-200', selectedAmPm === 'AM' ? 'bg-primary text-primary-content' : 'hover:bg-base-200 text-base-content/60']">AM</button>
                    <button type="button" @click="setAmPm('PM')"
                        :class="['flex-1 py-2 text-sm font-medium transition-colors', selectedAmPm === 'PM' ? 'bg-primary text-primary-content' : 'hover:bg-base-200 text-base-content/60']">PM</button>
                </div>


            </div>
        </div>

        <p v-if="message" class="mt-1 text-sm text-error px-1">
            {{ message === '-' ? '' : message }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    name: String,
    message: String,
    margin: { type: String, default: 'mb-4' },
    disabled: { type: Boolean, default: false },
    minuteStep: { type: Number, default: 15 } // 15 or 30 or 1
})

const model = defineModel({ type: String, required: true })

const isOpen = ref(false)
const wrapperRef = ref(null)
const hourColRef = ref(null)
const minColRef = ref(null)

const selectedHour = ref(null)
const selectedMinute = ref(null)
const selectedAmPm = ref('AM')

const hours = Array.from({ length: 12 }, (_, i) => i + 1)
const minutes = computed(() => {
    const result = []
    for (let m = 0; m < 60; m += props.minuteStep) result.push(m)
    return result
})

// Parse incoming model value (24hr format "HH:mm" from your backend)
const parseModel = (val) => {
    if (!val) return
    const [h, m] = val.split(':').map(Number)
    selectedAmPm.value = h >= 12 ? 'PM' : 'AM'
    selectedHour.value = h % 12 || 12
    selectedMinute.value = m
}

onMounted(() => parseModel(model.value))
watch(() => model.value, parseModel)

// Display in 12hr format
const displayValue = computed(() => {
    if (selectedHour.value === null || selectedMinute.value === null) return ''
    return `${String(selectedHour.value).padStart(2, '0')}:${String(selectedMinute.value).padStart(2, '0')} ${selectedAmPm.value}`
})

const to24hr = () => {
    let h = selectedHour.value
    if (selectedAmPm.value === 'PM' && h !== 12) h += 12
    if (selectedAmPm.value === 'AM' && h === 12) h = 0
    return `${String(h).padStart(2, '0')}:${String(selectedMinute.value).padStart(2, '0')}`
}

const scrollToSelected = () => {
    nextTick(() => {
        if (hourColRef.value && selectedHour.value) {
            const idx = hours.indexOf(selectedHour.value)
            hourColRef.value.scrollTop = idx * 40
        }
        if (minColRef.value && selectedMinute.value !== null) {
            const idx = minutes.value.indexOf(selectedMinute.value)
            minColRef.value.scrollTop = idx * 40
        }
    })
}

const togglePicker = () => {
    if (props.disabled) return
    isOpen.value = !isOpen.value
    if (isOpen.value) scrollToSelected()
}

watch([selectedHour, selectedMinute, selectedAmPm], () => {
    if (selectedHour.value !== null && selectedMinute.value !== null) {
        model.value = to24hr()
    }
})

const selectHour = (h) => {
    selectedHour.value = h
    scrollToSelected()
}
const selectMinute = (m) => {
    selectedMinute.value = m
}
const setAmPm = (v) => {
    selectedAmPm.value = v
}

// Close on outside click
const handleOutsideClick = (e) => {
    if (wrapperRef.value && !wrapperRef.value.contains(e.target) && isOpen.value) {
        isOpen.value = false
        if (selectedHour.value !== null && selectedMinute.value !== null) {
            model.value = to24hr()
        }
    }
}
onMounted(() => document.addEventListener('click', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('click', handleOutsideClick))
</script>

<style scoped>
.scrollbar-none {
    scrollbar-width: none;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>