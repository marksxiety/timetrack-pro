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
                <p class="text-base-content/60 text-sm mt-1">File a new overtime request by selecting a date</p>
            </div>
        </div>

        <form @submit.prevent="submitOvertime()" class="max-w-2xl mx-auto w-full">
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

                        <div v-if="selectedDate && fetchingSchedule" class="flex items-center justify-center h-24 gap-4">
                            <span class="loading loading-spinner loading-lg"></span>
                            <span class="text-lg opacity-70">Loading Schedule...</span>
                        </div>

                        <template v-else-if="selectedDate && !withSchedule">
                            <div class="alert alert-warning">
                                <Icon icon="material-symbols:warning-outline" width="24" height="24" />
                                <div>
                                    <h3 class="font-bold">No Registered Schedule</h3>
                                    <div class="text-xs opacity-70">You need to have a registered schedule before
                                        filing an overtime request.</div>
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
                                        :readonly="true" :placeholder="!withSchedule ? 'Select a date first' : ''" />
                                </div>
                                <div class="col-span-1">
                                    <TextInput name="Start:" type="text" v-model="form.shift_start_time"
                                        :readonly="true" :placeholder="!withSchedule ? 'Select a date first' : ''" />
                                </div>
                                <div class="col-span-1">
                                    <TextInput name="End:" type="text" v-model="form.shift_end_time"
                                        :readonly="true" :placeholder="!withSchedule ? 'Select a date first' : ''" />
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
                                            <span class="font-medium">{{ isEnhancing ?
                                                'Enhancing...' :
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
                        <Link :href="route('main')" class="btn btn-outline gap-2" :disabled="form.processing">
                            <Icon icon="material-symbols:close-rounded" width="20" height="20" />
                            <span class="font-medium">Cancel</span>
                        </Link>
                        <button type="submit" class="btn btn-primary gap-2"
                            :disabled="form.processing || fieldsDisabled">
                            <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                            <Icon v-if="!form.processing" icon="material-symbols:check-circle-outline"
                                width="20" height="20" />
                            <span class="font-medium">Submit Request</span>
                        </button>
                    </div>

                </div>
            </div>
        </form>
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
import { enhanceReason } from '../composables/useOvertimeRequest.js'
import { currentWeek } from '../utils/helpers/date.js'
import { Icon } from "@iconify/vue"

const toast = inject('toast')
const appConfig = inject('appConfig')
const isEnhancing = ref(false)
const fetchingSchedule = ref(false)
const withSchedule = ref(false)
const selectedDate = ref('')
const minuteStep = ref(15)

const fieldsDisabled = computed(() => !withSchedule.value)

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

const submitOvertime = () => {
    form.post(route('overtime.request'), {
        onSuccess: () => {
            toast('Overtime Request Filing successful!', 'success')
            form.reset()
            selectedDate.value = ''
            withSchedule.value = false
        },
        onError: () => {
            toast('Overtime Request failed.', 'error')
        }
    })
}

watch(selectedDate, async (newDate) => {
    if (!newDate) {
        withSchedule.value = false
        form.reset()
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
