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

        <!-- Submit Confirmation Modal -->
        <dialog ref="modalSubmitConfirm" class="modal">
            <div class="modal-box max-w-md">
                <h3 class="font-bold text-base flex items-center gap-2 mb-1">
                    <Icon icon="material-symbols:send-outline" width="18" height="18" />
                    Confirm Submission
                </h3>
                <p class="text-sm text-base-content/60 mb-4">
                    You are about to submit {{ pendingItems.length }} overtime request{{ pendingItems.length > 1 ? 's' :
                        '' }}. Please review before proceeding.
                </p>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <div v-for="item in pendingItems" :key="item._uid"
                        class="flex items-center justify-between bg-base-200 rounded-lg px-3 py-2 gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-base-content truncate">{{ item.displayDate }}</p>
                            <p class="text-xs text-base-content/50 mt-0.5">
                                <span class="font-mono">{{ item.shift_code }}</span>
                                &nbsp;·&nbsp;{{ to12hr(item.start_time) }} → {{ to12hr(item.end_time) }}
                            </p>
                        </div>
                        <span class="badge badge-xs badge-warning flex-shrink-0">Pending</span>
                    </div>
                </div>
                <div class="modal-action mt-5">
                    <form method="dialog">
                        <button class="btn btn-ghost btn-sm">Cancel</button>
                    </form>
                    <button class="btn btn-primary btn-sm gap-1.5" @click="confirmSubmitBulk()">
                        <Icon icon="material-symbols:send-outline" width="14" height="14" />
                        Submit All
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>

        <!-- Clear Queue Confirmation Modal -->
        <dialog ref="modalClearConfirm" class="modal">
            <div class="modal-box max-w-sm">
                <h3 class="font-bold text-base flex items-center gap-2 mb-1">
                    <Icon icon="material-symbols:delete-outline" width="18" height="18" />
                    Clear Queue?
                </h3>
                <p class="text-sm text-base-content/60">
                    This will remove all <span class="font-semibold text-base-content">{{ queue.length }}</span> queued
                    request{{ queue.length > 1 ? 's' : '' }} from the list. This action cannot be undone.
                </p>
                <div class="modal-action mt-5">
                    <form method="dialog">
                        <button class="btn btn-ghost btn-sm">Cancel</button>
                    </form>
                    <button class="btn btn-error btn-sm gap-1.5" @click="confirmClearQueue()">
                        <Icon icon="material-symbols:delete-outline" width="14" height="14" />
                        Clear All
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>

        <div class="gap-6 grid grid-cols-12 max-w-7xl mx-auto w-full">

            <!-- ── LEFT: Form ── -->
            <div class="col-span-7">
                <form @submit.prevent="editingIndex === null ? addToQueue() : updateInQueue()">
                    <div class="card bg-base-100 border border-base-300 shadow-sm">
                        <div class="card-body p-0 gap-0">

                            <!-- Section 1: Date -->
                            <div class="px-6 pt-6 pb-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="bg-base-200 rounded-lg p-1.5">
                                        <Icon icon="material-symbols:calendar-month-outline" width="16" height="16" />
                                    </div>
                                    <h3 class="text-sm font-semibold text-base-content tracking-wide uppercase">
                                        Overtime Date
                                    </h3>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <TextInput name="Date:" type="date" v-model="selectedDate" />
                                    </div>
                                    <div>
                                        <TextInput name="Week:" type="text" v-model="form.week" :readonly="true"
                                            :placeholder="selectedDate ? '' : 'Select a date first'" />
                                    </div>
                                </div>
                            </div>

                            <div class="divider mx-6 my-0"></div>

                            <!-- Section 2: Shift -->
                            <div class="px-6 py-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="bg-base-200 rounded-lg p-1.5">
                                        <Icon icon="material-symbols:work-outline" width="16" height="16" />
                                    </div>
                                    <h3 class="text-sm font-semibold text-base-content tracking-wide uppercase">
                                        Scheduled Shift
                                    </h3>
                                </div>

                                <!-- Loading -->
                                <div v-if="selectedDate && fetchingSchedule"
                                    class="flex items-center gap-3 py-5 px-4 rounded-xl bg-base-200/60">
                                    <span class="loading loading-spinner loading-sm text-primary"></span>
                                    <span class="text-sm text-base-content/60">Fetching your schedule...</span>
                                </div>

                                <!-- No schedule -->
                                <template v-else-if="selectedDate && !withSchedule">
                                    <div class="alert alert-warning rounded-xl">
                                        <Icon icon="material-symbols:warning-outline" width="20" height="20" />
                                        <div>
                                            <h3 class="font-semibold text-sm">No Schedule Found</h3>
                                            <div class="text-xs opacity-70 mt-0.5">
                                                You need a registered schedule before filing an overtime request.
                                            </div>
                                        </div>
                                        <Link :href="route('schedule')" class="btn btn-sm btn-ghost">
                                            Add Schedule
                                        </Link>
                                    </div>
                                </template>

                                <!-- Shift details -->
                                <template v-else>
                                    <div class="grid grid-cols-3 gap-3"
                                        :class="{ 'opacity-40 pointer-events-none': !withSchedule }">
                                        <TextInput name="Shift Code:" type="text" v-model="form.shift_code"
                                            :readonly="true" :placeholder="!withSchedule ? '—' : ''" />
                                        <TextInput name="Start:" type="text" v-model="form.shift_start_time"
                                            :readonly="true" :placeholder="!withSchedule ? '—' : ''" />
                                        <TextInput name="End:" type="text" v-model="form.shift_end_time"
                                            :readonly="true" :placeholder="!withSchedule ? '—' : ''" />
                                    </div>
                                </template>
                            </div>

                            <div class="divider mx-6 my-0"></div>

                            <!-- Section 3: Duration & Reason -->
                            <div class="px-6 py-5" :class="{ 'opacity-40 pointer-events-none': fieldsDisabled }">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="bg-base-200 rounded-lg p-1.5">
                                        <Icon icon="material-symbols:timer-outline" width="16" height="16" />
                                    </div>
                                    <h3 class="text-sm font-semibold text-base-content tracking-wide uppercase">
                                        Duration &amp; Reason
                                    </h3>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <TimePickerInput name="Start Time:" :message="form.errors?.start_time"
                                        :minuteStep="minuteStep" :disabled="fieldsDisabled" v-model="form.start_time" />
                                    <TimePickerInput name="End Time:" :message="form.errors?.end_time"
                                        :minuteStep="minuteStep" :disabled="fieldsDisabled" v-model="form.end_time" />
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label
                                            class="text-sm font-medium flex items-center gap-1.5 text-base-content/80">
                                            <Icon icon="material-symbols:edit-note-outline" width="16" height="16" />
                                            Reason
                                        </label>
                                        <div v-if="withSchedule" class="tooltip tooltip-left tooltip-break"
                                            data-tip="The better you describe, the better AI can enhance it!">
                                            <span tabindex="0" class="inline-block">
                                                <button type="button" class="btn btn-sm btn-primary gap-1.5"
                                                    @click="handleEnhance(form)" :disabled="isEnhancing">
                                                    <span v-if="isEnhancing"
                                                        class="loading loading-spinner loading-xs"></span>
                                                    <Icon v-if="!isEnhancing" icon="mingcute:ai-line" width="16"
                                                        height="16" />
                                                    {{ isEnhancing ? 'Enhancing...' : 'Enhance with AI' }}
                                                </button>
                                            </span>
                                        </div>
                                    </div>

                                    <TextArea type="text" v-model="form.reason" :message="form.errors?.reason"
                                        :disabled="fieldsDisabled" />

                                    <p v-if="form.errors?.reason && typeof form.errors?.reason === 'object'"
                                        class="text-xs text-warning flex items-center gap-1.5">
                                        <Icon icon="material-symbols:warning-outline" width="14" height="14" />
                                        {{ form.errors?.reason.message }}
                                    </p>

                                    <p v-if="isEnhancing" class="text-xs text-primary flex items-center gap-1.5">
                                        <Icon icon="hugeicons:chat-gpt" width="14" height="14" />
                                        Enhancing your reason — longer text takes more time.
                                    </p>
                                </div>
                            </div>

                            <!-- Footer actions -->
                            <div class="px-6 pb-6 flex justify-end gap-2">
                                <button v-if="editingIndex !== null" type="button" class="btn btn-ghost btn-sm gap-1.5"
                                    @click="cancelEdit()">
                                    <Icon icon="material-symbols:close-rounded" width="16" height="16" />
                                    Cancel Edit
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm gap-1.5"
                                    :disabled="fieldsDisabled || isSubmitting">
                                    <Icon :icon="editingIndex !== null
                                        ? 'material-symbols:edit-outline'
                                        : 'material-symbols:add-circle-outline'" width="16" height="16" />
                                    {{ editingIndex !== null ? 'Update in List' : 'Add to List' }}
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <!-- ── RIGHT: Queue ── -->
            <div class="col-span-5">
                <div class="card bg-base-100 border border-base-300 shadow-sm flex flex-col h-full max-h-[85vh]">

                    <!-- Queue header -->
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-base-300">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-base-content">Queued Requests</span>
                            <span v-if="queue.length > 0" class="badge badge-sm badge-neutral">
                                {{ queue.length }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button v-if="pendingItems.length > 0" type="button" class="btn btn-xs btn-primary gap-1"
                                :disabled="isSubmitting"
                                @click="modalSubmitConfirm.showModal()">
                                <span v-if="isSubmitting" class="loading loading-spinner loading-xs"></span>
                                <Icon icon="material-symbols:send-outline" width="14" height="14" />
                                Submit All
                            </button>
                            <button v-if="queue.length > 0 && !isSubmitting" type="button"
                                class="btn btn-xs btn-ghost text-base-content/40 hover:text-error"
                                @click="modalClearConfirm.showModal()">
                                Clear all
                            </button>
                        </div>
                    </div>

                    <!-- Queue body -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-2">

                        <!-- Empty state -->
                        <div v-if="queue.length === 0"
                            class="flex flex-col items-center justify-center h-full gap-3 py-12">
                            <div class="bg-base-200 rounded-full p-4">
                                <Icon icon="material-symbols:inbox-outline" width="28" height="28"
                                    class="text-base-content/30" />
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-medium text-base-content/40">No requests queued</p>
                                <p class="text-xs text-base-content/30 mt-0.5">Fill the form and click "Add to List"</p>
                            </div>
                        </div>

                        <!-- Queue items -->
                        <div v-for="(item, index) in queue" :key="item._uid"
                            class="group flex items-start gap-3 p-3 rounded-xl border transition-all duration-150"
                            :class="[
                                item.state === 'error'
                                    ? 'bg-error/5 border-error/30 hover:bg-error/10'
                                    : item.state === 'success'
                                        ? 'bg-success/5 border-success/20'
                                        : item.state === 'submitting'
                                            ? 'bg-base-200/60 border-base-300 opacity-70'
                                            : 'bg-base-100 border-base-200 hover:border-primary/30 hover:bg-primary/5',
                                (item.state === 'pending' || item.state === 'error') ? 'cursor-pointer' : ''
                            ]" @click="(item.state === 'pending' || item.state === 'error') && editItem(index)">
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-base-content truncate leading-tight">
                                        {{ item.displayDate }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="badge badge-xs badge-ghost font-mono">{{ item.shift_code }}</span>
                                    <span class="text-xs text-base-content/50">
                                        {{ to12hr(item.start_time) }} → {{ to12hr(item.end_time) }}
                                    </span>
                                </div>

                                <p v-if="item.state === 'error' && item.error"
                                    class="text-xs text-error mt-1.5 leading-snug">
                                    {{ item.error }}
                                </p>
                                <p v-if="(item.state === 'pending' || item.state === 'error') && editingIndex === index"
                                    class="text-xs text-primary mt-1 flex items-center gap-1">
                                    <Icon icon="material-symbols:edit-outline" width="12" height="12" />
                                    Currently editing…
                                </p>

                                <!-- Status badge row -->
                                <div class="mt-2">
                                    <span class="badge badge-sm font-medium gap-1"
                                        :class="getItemStateBadge(item.state)">
                                        <span v-if="item.state === 'submitting'" class="loading loading-spinner"
                                            style="width:8px;height:8px"></span>
                                        {{ getItemStateLabel(item.state) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Remove button -->
                            <button v-if="item.state !== 'submitting'" type="button"
                                class="btn btn-ghost btn-xs flex-shrink-0 opacity-0 group-hover:opacity-60 hover:!opacity-100 hover:text-error transition-opacity"
                                @click.stop="removeFromQueue(index)">
                                <Icon icon="material-symbols:close-rounded" width="14" height="14" />
                            </button>
                        </div>

                    </div>

                    <!-- Queue footer summary -->
                    <div v-if="queue.length > 0"
                        class="px-4 py-2.5 border-t border-base-300 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span v-if="pendingItems.length > 0" class="badge badge-sm badge-warning gap-1">
                                {{ pendingItems.length }} Pending
                            </span>
                            <span v-if="queue.filter(i => i.state === 'error').length > 0"
                                class="badge badge-sm badge-error gap-1">
                                {{queue.filter(i => i.state === 'error').length}} Failed
                            </span>
                            <span v-if="queue.filter(i => i.state === 'success').length > 0"
                                class="badge badge-sm badge-success gap-1">
                                {{queue.filter(i => i.state === 'success').length}} Done
                            </span>
                        </div>
                        <span class="text-xs text-base-content/30 flex-shrink-0">Click an item to edit</span>
                    </div>

                </div>
            </div>

        </div><!-- end grid -->
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
const modalSubmitConfirm = ref(null)
const modalClearConfirm = ref(null)
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

onMounted(() => {
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
    queue.value.push({ _uid: ++uid, ...data, state: 'pending', error: null })
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
    queue.value[editingIndex.value] = { ...item, ...data, state: 'pending', error: null }
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

    try {
        for (const item of items) {
            item.state = 'submitting'
            item.error = null

            try {
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
            } catch (e) {
                item.state = 'error'
                item.error = 'Submission failed.'
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
    } finally {
        isSubmitting.value = false
        editingIndex.value = null
    }
}

const confirmSubmitBulk = () => {
    modalSubmitConfirm.value.close()
    submitBulk()
}

const confirmClearQueue = () => {
    modalClearConfirm.value.close()
    clearQueue()
}


const getItemStateBadge = (state) => {
    switch (state) {
        case 'pending': return 'badge-warning'
        case 'submitting': return 'badge-ghost border border-base-300'
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

    try {
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
        } else {
            toast('Failed to load schedule. Please try again', 'error')
        }
    } catch (e) {
        toast('Failed to load schedule. Please try again', 'error')
    } finally {
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