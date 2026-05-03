<template>
    <Head title="Request Overtime" />
    <div class="flex flex-col gap-6">
        <Breadcrumbs :items="[
            { label: 'Home', route: 'main' },
            { label: 'Request Overtime', route: 'overtime.file', active: true },
        ]" />

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-base-content">Request Overtime</h1>
                <p class="text-base-content/60 text-sm mt-1">File overtime requests and submit in bulk</p>
            </div>
        </div>

        <div class="gap-4 grid grid-cols-12">

            <div class="col-span-7">
                <form @submit.prevent="editingIndex === null ? addToQueue() : updateInQueue()">
                    <div class="card border border-base-300 shadow-sm bg-base-100">
                        <div class="card-body p-8 space-y-8">

                            <div class="space-y-4">
                                <h3 class="card-title text-base flex items-center gap-2">
                                    <Icon icon="material-symbols:calendar-month-outline" width="20" height="20" />
                                    Requested Overtime Date
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-1">
                                        <TextInput name="Date:" type="date" v-model="selectedDate" />
                                    </div>
                                    <div class="col-span-1">
                                        <TextInput name="Week:" type="text" v-model="form.week"
                                            :readonly="true" :placeholder="selectedDate ? '' : 'Select a date first'" />
                                    </div>
                                </div>
                            </div>

                            <div class="divider my-0"></div>

                            <div class="space-y-4">
                                <h3 class="card-title text-base flex items-center gap-2">
                                    <Icon icon="material-symbols:work-outline" width="20" height="20" />
                                    Your Scheduled Shift
                                </h3>

                                <div v-if="selectedDate && fetchingSchedule"
                                    class="flex items-center justify-center h-24 gap-4">
                                    <span class="loading loading-spinner loading-lg"></span>
                                    <span class="text-lg opacity-70">Loading Schedule...</span>
                                </div>

                                <template v-else-if="selectedDate && !withSchedule">
                                    <div class="alert alert-warning">
                                        <Icon icon="material-symbols:warning-outline" width="24" height="24" />
                                        <div>
                                            <h3 class="font-bold">No Registered Schedule</h3>
                                            <div class="text-xs opacity-70">You need to have a registered schedule
                                                before filing an overtime request.</div>
                                        </div>
                                        <Link :href="route('schedule')" class="btn btn-sm btn-ghost">
                                            Add Schedule
                                        </Link>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="grid grid-cols-3 gap-4" :class="{ 'opacity-50': !withSchedule }">
                                        <div class="col-span-1">
                                            <TextInput name="Shift Code:" type="text" v-model="form.shift_code"
                                                :readonly="true"
                                                :placeholder="!withSchedule ? 'Select a date first' : ''" />
                                        </div>
                                        <div class="col-span-1">
                                            <TextInput name="Start:" type="text"
                                                v-model="form.shift_start_time" :readonly="true"
                                                :placeholder="!withSchedule ? 'Select a date first' : ''" />
                                        </div>
                                        <div class="col-span-1">
                                            <TextInput name="End:" type="text" v-model="form.shift_end_time"
                                                :readonly="true"
                                                :placeholder="!withSchedule ? 'Select a date first' : ''" />
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="divider my-0"></div>

                            <div class="space-y-4" :class="{ 'opacity-50 pointer-events-none': fieldsDisabled }">
                                <h3 class="card-title text-base flex items-center gap-2">
                                    <Icon icon="material-symbols:timer-outline" width="20" height="20" />
                                    Overtime Duration and Reason
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-1">
                                        <TimePickerInput name="Start Time:"
                                            :message="form.errors?.start_time" :minuteStep="minuteStep"
                                            :disabled="fieldsDisabled" v-model="form.start_time" />
                                    </div>
                                    <div class="col-span-1">
                                        <TimePickerInput name="End Time:" :message="form.errors?.end_time"
                                            :minuteStep="minuteStep" :disabled="fieldsDisabled"
                                            v-model="form.end_time" />
                                    </div>
                                </div>

                                <div class="divider my-0"></div>

                                <div class="w-full space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="font-semibold text-sm flex items-center gap-2">
                                            <Icon icon="material-symbols:edit-note-outline" width="18" height="18" />
                                            Reason
                                        </label>
                                        <div v-if="withSchedule" class="tooltip tooltip-left tooltip-break"
                                            data-tip="The better you describe, the better AI can enhance it!">
                                            <span tabindex="0" class="inline-block">
                                                <button type="button" class="btn btn-sm gap-2 btn-primary"
                                                    @click="handleEnhance(form)" :disabled="isEnhancing">
                                                    <span v-if="isEnhancing"
                                                        class="loading loading-spinner loading-xs"></span>
                                                    <Icon v-if="!isEnhancing" icon="mingcute:ai-line" width="18"
                                                        height="18" />
                                                    <span class="font-medium">{{ isEnhancing ? 'Enhancing...' :
                                                        'Enhance with AI' }}</span>
                                                </button>
                                            </span>
                                        </div>
                                    </div>

                                    <TextArea type="text" v-model="form.reason"
                                        :message="form.errors?.reason" :disabled="fieldsDisabled" />

                                    <p v-if="form.errors?.reason && typeof form.errors?.reason === 'object'"
                                        class="text-sm text-warning flex items-center gap-2">
                                        <Icon icon="material-symbols:warning-outline" width="16" height="16" />
                                        {{ form.errors?.reason.message }}
                                    </p>

                                    <p v-if="isEnhancing" class="text-sm text-primary flex items-center gap-2">
                                        <Icon icon="hugeicons:chat-gpt" width="16" height="16" />
                                        Currently Enhancing.. The longer the reason, the more time it will take
                                    </p>
                                </div>
                            </div>

                            <div class="divider my-0"></div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button v-if="editingIndex !== null" type="button"
                                    class="btn btn-ghost gap-2" @click="cancelEdit()">
                                    <Icon icon="material-symbols:close-rounded" width="20" height="20" />
                                    <span class="font-medium">Cancel Edit</span>
                                </button>
                                <button type="submit" class="btn btn-primary gap-2"
                                    :disabled="fieldsDisabled || isSubmitting">
                                    <Icon
                                        :icon="editingIndex !== null ? 'material-symbols:edit-outline' : 'material-symbols:add-circle-outline'"
                                        width="20" height="20" />
                                    <span class="font-medium">{{ editingIndex !== null ? 'Update in List' :
                                        'Add to List' }}</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="col-span-5">
                <div
                    class="rounded-xl border border-base-300 bg-base-100 overflow-hidden flex flex-col h-full max-h-[85vh]">
                    <div class="px-4 py-3 border-b border-base-300 flex items-center justify-between">
                        <h2 class="text-sm font-semibold tracking-tight">
                            Queued Requests
                            <span v-if="queue.length > 0"
                                class="badge badge-sm badge-ghost ml-1">{{ queue.length }}</span>
                        </h2>
                        <div class="flex items-center gap-2">
                            <button v-if="pendingItems.length > 0" type="button"
                                class="btn btn-xs btn-primary gap-1" :disabled="isSubmitting"
                                @click="submitBulk()">
                                <span v-if="isSubmitting" class="loading loading-spinner loading-xs"></span>
                                <Icon icon="material-symbols:send-outline" width="16" height="16" />
                                Submit All
                            </button>
                            <button v-if="queue.length > 0 && !isSubmitting" type="button"
                                class="btn btn-xs btn-ghost" @click="clearQueue()">
                                Clear
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2">
                        <div v-if="queue.length === 0" class="flex items-center justify-center h-full">
                            <p class="text-xs text-base-content/40">No queued requests yet</p>
                        </div>
                        <div v-for="(item, index) in queue" :key="item._uid"
                            class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors mb-1"
                            :class="[
                                item.state === 'error' ? 'bg-error/10 hover:bg-error/20' : 'hover:bg-accent/50',
                                item.state === 'submitting' ? 'opacity-70' : '',
                                (item.state === 'pending' || item.state === 'error') ? 'cursor-pointer' : ''
                            ]" @click="(item.state === 'pending' || item.state === 'error') && editItem(index)">

                            <div class="w-1 h-8 rounded-full flex-shrink-0 mt-0.5"
                                :class="getItemStateColor(item.state)"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium truncate">{{ item.displayDate }}</p>
                                    <span
                                        :class="['badge badge-xs font-medium flex-shrink-0', getItemStateBadge(item.state)]">
                                        <span v-if="item.state === 'submitting'"
                                            class="loading loading-spinner loading-[10px]"></span>
                                        {{ getItemStateLabel(item.state) }}
                                    </span>
                                </div>
                                <p class="text-xs text-base-content/50 mt-0.5">
                                    {{ item.shift_code }} &middot; {{ to12hr(item.start_time) }} &rarr;
                                    {{ to12hr(item.end_time) }}
                                </p>
                                <p v-if="item.state === 'error' && item.error" class="text-xs text-error mt-1">
                                    {{ item.error }}
                                </p>
                                <p v-if="(item.state === 'pending' || item.state === 'error') && editingIndex === index"
                                    class="text-xs text-primary mt-1">
                                    Currently editing...
                                </p>
                            </div>
                            <button v-if="item.state !== 'submitting'" type="button"
                                class="btn btn-ghost btn-xs flex-shrink-0 opacity-40 hover:opacity-100 hover:text-error"
                                @click.stop="removeFromQueue(index)">
                                <Icon icon="material-symbols:close-rounded" width="16" height="16" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { onMounted, ref, inject, watch, computed } from 'vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import TextInput from '../Components/TextInput.vue'
import TextArea from '../Components/TextArea.vue'
import TimePickerInput from '../Components/TimePicker.vue'
import { fetchUserSchedule } from '../api/schedule.js'
import { submitBulkOvertime } from '../api/overtime.js'
import { enhanceReason } from '../composables/useOvertimeRequest.js'
import { currentWeek, to12hr } from '../utils/helpers/date.js'
import { Icon } from "@iconify/vue"

const toast = inject('toast')
const appConfig = inject('appConfig')
const isEnhancing = ref(false)
const fetchingSchedule = ref(false)
const withSchedule = ref(false)
const selectedDate = ref('')
const minuteStep = ref(15)
const isSubmitting = ref(false)
const editingIndex = ref(null)
let uid = 0

const queue = ref([])

const fieldsDisabled = computed(() => !withSchedule.value)

const pendingItems = computed(() => queue.value.filter(i => i.state === 'pending' || i.state === 'error'))

const form = useForm({
    date: '',
    week: '',
    employee_schedule_id: '',
    shift_code: '',
    shift_start_time: '',
    shift_end_time: '',
    start_time: '',
    end_time: '',
    reason: ''
})

onMounted(async () => {
    const step = appConfig.value?.overtime_minute_step
    if ([1, 5, 10, 15, 30].includes(step)) {
        minuteStep.value = step
    }
})

const handleEnhance = (f) => enhanceReason(f, isEnhancing)

const getFormData = () => ({
    dateRaw: selectedDate.value,
    displayDate: form.date,
    week: form.week,
    employee_schedule_id: form.employee_schedule_id,
    shift_code: form.shift_code,
    shift_start_time: form.shift_start_time,
    shift_end_time: form.shift_end_time,
    start_time: form.start_time,
    end_time: form.end_time,
    reason: form.reason
})

const isDuplicate = (data, excludeIndex = null) => {
    return queue.value.some((item, i) =>
        i !== excludeIndex &&
        item.dateRaw === data.dateRaw &&
        item.start_time === data.start_time &&
        item.end_time === data.end_time
    )
}

const validateBeforeAdd = () => {
    if (!form.date || !form.start_time || !form.end_time || !form.reason?.trim()) {
        toast('Please fill in all required fields.', 'error')
        return false
    }
    return true
}

const addToQueue = () => {
    if (!validateBeforeAdd()) return

    const data = getFormData()

    if (isDuplicate(data)) {
        toast('This date, start time, and end time already exists in the queue.', 'error')
        return
    }

    queue.value.push({
        _uid: ++uid,
        ...data,
        state: 'pending',
        error: null
    })

    toast('Request added to queue.', 'success')
    resetForm()
}

const updateInQueue = () => {
    if (editingIndex.value === null) return
    if (!validateBeforeAdd()) return

    const data = getFormData()

    if (isDuplicate(data, editingIndex.value)) {
        toast('This date, start time, and end time already exists in the queue.', 'error')
        return
    }

    const item = queue.value[editingIndex.value]
    Object.assign(item, data)
    item.state = 'pending'
    item.error = null

    toast('Request updated in queue.', 'success')
    editingIndex.value = null
    resetForm()
}

const editItem = (index) => {
    if (isSubmitting.value) return
    const item = queue.value[index]
    editingIndex.value = index
    selectedDate.value = item.dateRaw || ''
    form.date = item.displayDate
    form.week = item.week
    form.employee_schedule_id = item.employee_schedule_id
    form.shift_code = item.shift_code
    form.shift_start_time = item.shift_start_time
    form.shift_end_time = item.shift_end_time
    form.start_time = item.start_time
    form.end_time = item.end_time
    form.reason = item.reason
    form.clearErrors()
    withSchedule.value = true
}

const cancelEdit = () => {
    editingIndex.value = null
    resetForm()
}

const removeFromQueue = (index) => {
    if (isSubmitting.value) return
    if (editingIndex.value === index) {
        editingIndex.value = null
        resetForm()
    } else if (editingIndex.value !== null && editingIndex.value > index) {
        editingIndex.value--
    }
    queue.value.splice(index, 1)
}

const clearQueue = () => {
    if (isSubmitting.value) return
    queue.value = []
    editingIndex.value = null
    resetForm()
    toast('Queue cleared.', 'info')
}

const resetForm = () => {
    form.reset()
    form.clearErrors()
    selectedDate.value = ''
    withSchedule.value = false
}

const submitBulk = async () => {
    if (isSubmitting.value) return
    isSubmitting.value = true

    const items = queue.value.filter(i => i.state === 'pending' || i.state === 'error')
    let successCount = 0

    for (const item of items) {
        item.state = 'submitting'
        item.error = null

        const result = await submitBulkOvertime({
            employee_schedule_id: item.employee_schedule_id,
            date: item.dateRaw,
            start_time: item.start_time,
            end_time: item.end_time,
            reason: item.reason
        })

        if (result.success) {
            item.state = 'success'
            successCount++
        } else {
            item.state = 'error'
            item.error = result.errors?.join(', ') || 'Submission failed.'
        }
    }

    queue.value = queue.value.filter(i => i.state !== 'success')

    if (successCount > 0 && queue.value.length === 0) {
        toast(`${successCount} request${successCount > 1 ? 's' : ''} submitted successfully!`, 'success')
    } else if (successCount > 0 && queue.value.length > 0) {
        toast(`${successCount} submitted, ${queue.value.length} failed.`, 'warning')
    } else {
        toast('All requests failed. Please review the errors.', 'error')
    }

    isSubmitting.value = false
    editingIndex.value = null
}

const getItemStateColor = (state) => {
    switch (state) {
        case 'pending': return 'bg-base-content/20'
        case 'submitting': return 'bg-warning'
        case 'success': return 'bg-success'
        case 'error': return 'bg-error'
        default: return 'bg-base-content/20'
    }
}

const getItemStateBadge = (state) => {
    switch (state) {
        case 'pending': return 'badge-ghost border border-base-300'
        case 'submitting': return 'badge-warning'
        case 'success': return 'badge-success'
        case 'error': return 'badge-error'
        default: return 'badge-ghost'
    }
}

const getItemStateLabel = (state) => {
    switch (state) {
        case 'pending': return 'Pending'
        case 'submitting': return 'Submitting'
        case 'success': return 'Done'
        case 'error': return 'Failed'
        default: return ''
    }
}

watch(selectedDate, async (newDate) => {
    if (!newDate) {
        withSchedule.value = false
        form.reset()
        form.clearErrors()
        return
    }

    const parsed = new Date(newDate + 'T00:00:00')
    const year = parsed.getFullYear()
    const month = parsed.getMonth()
    const day = parsed.getDate()

    form.date = parsed.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
    form.week = 'Week ' + currentWeek(parsed)

    fetchingSchedule.value = true
    withSchedule.value = false
    form.shift_code = ''
    form.employee_schedule_id = ''
    form.shift_start_time = ''
    form.shift_end_time = ''
    form.start_time = ''
    form.end_time = ''
    form.reason = ''

    let scheduleResponse = await fetchUserSchedule(year, month + 1, day)

    if (scheduleResponse?.success) {
        let scheduledata = scheduleResponse?.schedule

        if (Object.keys(scheduledata).length > 0) {
            withSchedule.value = true
            form.date = scheduledata.date
            form.week = scheduledata.week
            form.shift_code = scheduledata.shift_code
            form.employee_schedule_id = scheduledata.id
            form.shift_start_time = scheduledata.shift_start_time
            form.shift_end_time = scheduledata.shift_end_time
        } else {
            withSchedule.value = false
        }

        fetchingSchedule.value = false
    } else {
        toast('Failed to load schedule. Please try again', 'error')
        fetchingSchedule.value = false
    }
})
</script>

<style scoped>
.tooltip-break::before {
    white-space: normal !important;
    word-break: break-word;
    width: 16rem;
    text-align: center;
}
</style>
