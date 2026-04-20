<template>
    <Modal ref="overtimeRequestModal" title="Overtime Request Details">
        <div class="flex flex-col gap-2 w-full">
            <div class="max-w-2xl mx-auto w-full">
                <form @submit.prevent="submitCancelation()">
                    <div class="mb-8">
                        <Stepper :status="formFilledOvertime.current_status" />
                    </div>

                    <div class="space-y-8 text-sm">
                        <div class="card border border-base-300 shadow-sm">
                            <div class="card-body p-6">
                                <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                    <Icon icon="material-symbols:info-outline" width="20" height="20" />
                                    Meta Information
                                </h3>
                                <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs opacity-60 mb-1">Date Filed</span>
                                        <span class="font-semibold">{{ formFilledOvertime.created_at }}</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs opacity-60 mb-1">Week</span>
                                        <span class="font-semibold">{{ formFilledOvertime.week }}</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs opacity-60 mb-1">Status</span>
                                        <div class="badge badge-sm gap-2" :class="[
                                            formFilledOvertime.current_status === 'PENDING' ? 'badge-warning' :
                                                (formFilledOvertime.current_status === 'APPROVED' ? 'badge-success' :
                                                    (['DISAPPROVED', 'CANCELED'].includes(formFilledOvertime.current_status) ? 'badge-error' :
                                                        (formFilledOvertime.current_status === 'FILED' ? 'badge-primary' : 'badge-ghost')))
                                        ]">
                                            {{ formFilledOvertime.current_status }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs opacity-60 mb-1">Date</span>
                                        <span class="font-semibold">{{ formFilledOvertime.date }}</span>
                                    </div>
                                    <div class="flex flex-col col-span-2">
                                        <span class="text-xs opacity-60 mb-1">Total Hours</span>
                                        <span class="font-semibold text-lg">{{ formFilledOvertime.hours }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border border-base-300 shadow-sm">
                            <div class="card-body p-6">
                                <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                    <Icon icon="material-symbols:schedule-outline" width="20" height="20" />
                                    Your Scheduled Shift
                                </h3>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-1">
                                        <TextInput name="Shift Code:" type="text"
                                            v-model="formFilledOvertime.shift_code" :readonly="true"
                                            :placeholder="''" />
                                    </div>
                                    <div class="col-span-1">
                                        <TextInput name="Start:" type="text"
                                            v-model="formFilledOvertime.shift_start_time" :readonly="true"
                                            :placeholder="''" />
                                    </div>
                                    <div class="col-span-1">
                                        <TextInput name="End:" type="text" v-model="formFilledOvertime.shift_end_time"
                                            :readonly="true" :placeholder="''" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border border-base-300 shadow-sm">
                            <div class="card-body p-6">
                                <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                    <Icon icon="material-symbols:description-outline" width="20" height="20" />
                                    Filed Request
                                </h3>
                                <div class="flex flex-col gap-6">
                                    <div class="grid grid-cols-2 gap-4">
                                        <template v-if="formFilledOvertime.current_status === 'PENDING'">
                                            <div class="col-span-1">
                                                <TimePickerInput name="Start Time:"
                                                    :message="formFilledOvertime.errors?.start_time"
                                                    :minuteStep="minuteStep" v-model="formFilledOvertime.start_time" />
                                            </div>
                                            <div class="col-span-1">
                                                <TimePickerInput name="End Time:"
                                                    :message="formFilledOvertime.errors?.end_time"
                                                    :minuteStep="minuteStep" v-model="formFilledOvertime.end_time" />
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="flex flex-col">
                                                <span class="text-xs opacity-60 mb-1">Start Time</span>
                                                <span class="font-semibold">{{ formatTime(formFilledOvertime.start_time)
                                                }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs opacity-60 mb-1">End Time</span>
                                                <span class="font-semibold">{{ formatTime(formFilledOvertime.end_time)
                                                }}</span>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="divider my-0"></div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <label class="font-semibold text-sm flex items-center gap-2">
                                                <Icon icon="material-symbols:edit-note-outline" width="18"
                                                    height="18" />
                                                Reason
                                            </label>
                                            <div v-if="formFilledOvertime.current_status === 'PENDING'">
                                                <div class="tooltip tooltip-top tooltip-break"
                                                    data-tip="The better you describe, the better AI can enhance it!">
                                                    <span tabindex="0" class="inline-block">
                                                        <button type="button" class="btn btn-sm gap-2 btn-primary"
                                                            @click="enhanceReason()" :disabled="isEnhancing">
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
                                        </div>
                                        <TextArea type="text" v-model="formFilledOvertime.reason"
                                            :message="formFilledOvertime.errors?.reason"
                                            :readonly="formFilledOvertime.current_status !== 'PENDING'" />

                                        <p v-if="isEnhancing" class="text-sm text-primary flex items-center gap-2">
                                            <Icon icon="hugeicons:chat-gpt" width="16" height="16" />
                                            Currently Enhancing.. The longer the reason, the more time it will take
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border border-base-300 shadow-sm" v-if="formFilledOvertime.remarks">
                            <div class="card-body p-6">
                                <h3 class="card-title text-base mb-3 flex items-center gap-2">
                                    <Icon icon="material-symbols:comment-outline" width="20" height="20" />
                                    Remarks
                                </h3>
                                <p class="opacity-80 leading-relaxed">{{ formFilledOvertime.remarks }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="formFilledOvertime.current_status == 'PENDING'" class="mt-8">
                        <div v-if="!confirmingCancel" class="flex gap-3">
                            <button type="submit" class="btn btn-primary flex-1 gap-2"
                                :disabled="formFilledOvertime.processing" @click="modeUpdate = true">
                                <span v-if="formFilledOvertime.processing && modeUpdate"
                                    class="loading loading-spinner loading-sm"></span>
                                <Icon v-if="!formFilledOvertime.processing || !modeUpdate"
                                    icon="material-symbols:check-circle-outline" width="20" height="20" />
                                <span class="font-medium">Update Request</span>
                            </button>
                            <button type="button" class="btn btn-outline flex-1 gap-2" @click="confirmingCancel = true"
                                :disabled="formFilledOvertime.processing">
                                <Icon icon="material-symbols:cancel-outline" width="20" height="20" />
                                <span class="font-medium">Cancel Request</span>
                            </button>
                        </div>
                        <div v-else class="alert shadow-lg border border-base-300">
                            <Icon icon="material-symbols:warning-outline" width="24" height="24" class="text-warning" />
                            <div class="flex-1">
                                <h3 class="font-bold">Confirm Cancellation</h3>
                                <div class="text-xs opacity-70">Are you sure you want to cancel this overtime request?
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-sm btn-error gap-2" @click="modeUpdate = false"
                                    :disabled="formFilledOvertime.processing">
                                    <span v-if="formFilledOvertime.processing"
                                        class="loading loading-spinner loading-xs"></span>
                                    <span>Yes, Cancel</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline" @click="confirmingCancel = false"
                                    :disabled="formFilledOvertime.processing">
                                    No, Keep It
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </Modal>

    <div class="flex flex-col gap-6">
        <!-- Breadcrumbs -->
        <Breadcrumbs :items="[
            { label: 'Home', route: 'main' },
            { label: 'Overtime Request', active: true },
        ]" />

        <!-- Stats Cards -->
        <div class="stats shadow grid grid-cols-5">
            <Card title="Total Hours" :value="filteredTotalHours + ' hrs'" />
            <Card title="Filed Requests" :value="filteredFiled" />
            <Card title="Pending Requests" :value="filteredPending" />
            <Card title="Approved Requests" :value="filteredApproved" />
            <Card title="Rejected Requests" :value="filteredRejected" />
        </div>

        <!-- Heatmap -->
        <Heatmap />

        <!-- Main Card -->
        <div class="card bg-base-100 shadow-sm border border-base-300">
            <div class="card-body px-6 py-3">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                    <div>
                        <h1 class="text-xl font-bold text-base-content">Overtime Requests</h1>
                        <p class="text-base-content/60 text-sm mt-1">Track and manage your overtime submissions</p>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="mb-2">
                    <div class="flex flex-col lg:flex-row gap-3">
                        <div class="flex-1">
                            <TextInput name="Search" type="text" v-model="searchValue"
                                :placeholder="'Search by reason, remarks, date, week...'" margin=""
                                class="w-full input-bordered" />
                        </div>
                        <div class="flex-none w-full lg:w-48">
                            <SelectOption name="Week" :options="weeks" v-model="selectedWeek" margin=""
                                class="select-bordered w-full" />
                        </div>
                        <div class="flex-none w-full lg:w-48">
                            <SelectOption name="Status" :options="statuses" v-model="selectedStatus" margin=""
                                class="select-bordered w-full" />
                        </div>
                        <div class="flex-none w-full lg:w-44">
                            <SelectOption name="Sort" :options="sortOptions" v-model="selectedSort" margin=""
                                class="select-bordered w-full" />
                        </div>
                        <div class="flex-none self-end">
                            <button
                                class="btn btn-primary gap-2 w-full lg:w-auto shadow-md hover:shadow-lg transition-shadow"
                                @click="applyFilter">
                                <Icon icon="proicons:filter" width="20" height="20" />
                                <span>Apply</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="divider my-0"></div>

                <!-- Table Section -->
                <div class="overflow-auto max-h-[40vh] rounded-lg border border-base-300 bg-base-100">
                    <table class="table table-fixed w-full">
                        <thead class="bg-base-200 sticky top-0 z-10">
                            <tr>
                                <th class="font-bold text-sm">Date</th>
                                <th class="font-bold text-sm text-center">Shift Code</th>
                                <th class="font-bold text-sm text-center">Shift Time Range</th>
                                <th class="font-bold text-sm">Week</th>
                                <th class="font-bold text-sm">Hours</th>
                                <th class="font-bold text-sm">Reason</th>
                                <th class="font-bold text-sm">Remarks</th>
                                <th class="text-center font-bold text-sm">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!requests || requests.length === 0">
                                <td colspan="8" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-3">
                                        <Icon icon="tabler:inbox-off" width="48" height="48"
                                            class="text-base-content/30" />
                                        <p class="text-base-content/50 font-medium">No overtime requests found</p>
                                        <p class="text-base-content/40 text-sm">Try adjusting your filters</p>
                                    </div>
                                </td>
                            </tr>

                            <tr v-else v-for="req in requests" :key="req.id" class="hover:bg-base-200 cursor-pointer"
                                @click="openRequestModal(req)">
                                <td class="font-medium">{{ req.date }}</td>
                                <td class="font-medium text-center">{{ req.shift }}</td>
                                <td class="font-medium text-center">{{ req.shift_start_time }} - {{
                                    req.shift_end_time }}</td>
                                <td>
                                    <div class="badge badge-ghost badge-sm">{{ req.week }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <Icon icon="tabler:clock" width="16" height="16" class="text-base-content/60" />
                                        <span class="font-semibold">{{ req.hours }}</span>
                                    </div>
                                </td>
                                <td class="max-w-xs">
                                    <div class="tooltip tooltip-left tooltip-break"
                                        :data-tip="req.reason">
                                        <p class="text-sm text-base-content/80 break-words">
                                            {{ truncateText(req.reason) }}
                                        </p>
                                    </div>
                                </td>

                                <td>
                                    <span class="text-sm"
                                        :class="req.remarks ? 'text-base-content/80' : 'text-base-content/40 italic'">
                                        {{ req.remarks ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="badge badge-sm font-semibold gap-1" :class="{
                                        'badge-primary': req.status === 'FILED',
                                        'badge-warning': req.status === 'PENDING',
                                        'badge-success': req.status === 'APPROVED',
                                        'badge-error': req.status === 'DECLINED' || req.status === 'DISAPPROVED' || req.status === 'CANCELED',
                                    }">
                                        <Icon :icon="getStatusIcon(req.status)" width="14" height="14" />
                                        {{ req.status }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div>
                    <PaginationLinks :paginator="paginator" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import Modal from '../Components/Modal.vue'
import TextInput from '../Components/TextInput.vue'
import TextArea from '../Components/TextArea.vue'
import TimePickerInput from '../Components/TimePicker.vue'
import SelectOption from '../Components/SelectOption.vue'
import Stepper from '../Components/Stepper.vue'
import PaginationLinks from '../Components/PaginationLinks.vue'
import Card from '../Components/Card.vue'
import Heatmap from '../Components/Heatmap.vue'
import { Icon } from "@iconify/vue"
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch, inject, onMounted } from 'vue'
import { weeks, statuses, sortOptions } from '../utils/dropdownOptions.js'
import { truncateText } from '../utils/truncateText.js'
import { enhanceReasonWithAI } from "../services/ai.js"

const toast = inject('toast')
const appConfig = inject('appConfig')

const overtimeRequestModal = ref(null)
const confirmingCancel = ref(false)
const modeUpdate = ref(false)
const isEnhancing = ref(false)
const minuteStep = ref(15)

const formFilledOvertime = useForm({
    id: '',
    employee_schedule_id: '',
    date: '',
    created_at: '',
    week: '',
    hours: '',
    start_time: '',
    end_time: '',
    current_status: '',
    shift_start_time: '',
    shift_end_time: '',
    shift_code: '',
    update_status: 'CANCELED',
    reason: '',
    remarks: ''
})

onMounted(() => {
    const step = appConfig.value?.overtime_minute_step
    if ([1, 5, 10, 15, 30].includes(step)) {
        minuteStep.value = step
    }
})

const props = defineProps({
    info: Object,
    payload: Object,
    errors: Object,
    flash: Object,
    success: Boolean,
    message: String,
    auth: Object,
})

const selectedWeek = ref(props.payload?.week ?? '')
const selectedStatus = ref(props.payload?.status ?? '')
const selectedSort = ref(props.payload?.sort ?? 'date_desc')
const searchValue = ref(props.payload?.search ?? '')


const paginator = ref(props.info?.requests ?? { data: [], links: [] })
const requests = ref(paginator.value.data ?? [])

const filteredTotalHours = computed(() => {
    return requests.value.reduce((sum, r) => sum + parseFloat(r.hours || 0), 0).toFixed(2)
})

const filteredFiled = computed(() => {
    return requests.value.filter(r => r.status === 'FILED').length
})

const filteredPending = computed(() => {
    return requests.value.filter(r => r.status === 'PENDING').length
})

const filteredApproved = computed(() => {
    return requests.value.filter(r => r.status === 'APPROVED').length
})

const filteredRejected = computed(() => {
    return requests.value.filter(r => ['DECLINED', 'DISAPPROVED', 'CANCELED'].includes(r.status)).length
})

// Watch for props changes and update local data
watch(() => props.info?.requests, (newRequests) => {
    if (newRequests) {
        paginator.value = newRequests
        requests.value = newRequests.data || []
    }
}, { immediate: true })

const handleFilter = () => {
    paginator.value = {
        ...paginator.value,
        filters: {
            week: selectedWeek.value,
            status: selectedStatus.value,
            sort: selectedSort.value,
            search: searchValue.value
        }
    }
}

const fetchRequests = () => {
    router.get(route('overtime.requests.employee'), {
        week: selectedWeek.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
        search: searchValue.value
    }, {
        preserveState: true,
        onSuccess: (page) => {
            paginator.value = page.props.info.requests
            requests.value = paginator.value.data
        }
    })
}

const getStatusIcon = (status) => {
    const icons = {
        'FILED': 'tabler:file-text',
        'CANCELED': 'tabler:x',
        'PENDING': 'tabler:clock',
        'APPROVED': 'tabler:check',
        'DECLINED': 'tabler:x',
        'DISAPPROVED': 'tabler:x'
    };
    return icons[status] || 'tabler:circle';
};

const applyFilter = () => {
    handleFilter()
    fetchRequests()
}

const formatTimeStamp = (timestamp) => {
    if (!timestamp) return ''

    const match = timestamp.match(/(\d{1,2}):(\d{2})\s?(AM|PM)/i)
    if (!match) return ''

    let [, hour, minute, period] = match
    hour = parseInt(hour, 10)
    minute = parseInt(minute, 10)

    if (period.toUpperCase() === 'PM' && hour !== 12) hour += 12
    if (period.toUpperCase() === 'AM' && hour === 12) hour = 0

    return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`
}

const formatTime = (time) => {
    if (!time) return ''

    const [hours, minutes] = time.split(':').map(Number)
    const period = hours >= 12 ? 'PM' : 'AM'
    const formattedHours = hours % 12 || 12
    return `${formattedHours}:${minutes.toString().padStart(2, '0')} ${period}`
}

const openRequestModal = (data) => {
    formFilledOvertime.id = data.id
    formFilledOvertime.employee_schedule_id = data.employee_schedule_id
    formFilledOvertime.date = data.date
    formFilledOvertime.created_at = data.created_at
    formFilledOvertime.week = data.week
    formFilledOvertime.hours = data.hours
    formFilledOvertime.start_time = formatTimeStamp(data.start_time)
    formFilledOvertime.end_time = formatTimeStamp(data.end_time)
    formFilledOvertime.current_status = data.status
    formFilledOvertime.reason = data.reason
    formFilledOvertime.remarks = data.remarks
    formFilledOvertime.shift_code = data.shift
    formFilledOvertime.shift_start_time = data.shift_start_time
    formFilledOvertime.shift_end_time = data.shift_end_time
    confirmingCancel.value = false
    modeUpdate.value = false
    overtimeRequestModal.value?.open()
}

const closeRequestModal = () => {
    overtimeRequestModal.value?.close()
    formFilledOvertime.reset()
    confirmingCancel.value = false
    modeUpdate.value = false
}

const submitCancelation = () => {
    if (modeUpdate.value) {
        formFilledOvertime.update_status = 'PENDING'
    } else {
        formFilledOvertime.update_status = 'CANCELED'
    }

    formFilledOvertime.post(route('overtime.update.employee'), {
        onSuccess: () => {
            if (modeUpdate.value) {
                toast('Updating Successful', 'success')
            } else {
                toast('Cancelation Successful', 'success')
            }
            closeRequestModal()
        },
        onError: () => {
            toast('Request failed.', 'error')
        }
    })
}

const enhanceReason = async () => {
    const form = formFilledOvertime
    const originalReason = form.reason

    if (form.reason) {
        if (form.reason.trim().length === 0) {
            form.errors.reason = 'Please enter a reason to enhance.'
            return
        }

        let splitted_reason = form.reason?.trim().split(' ')
        if (splitted_reason.length < 3) {
            form.errors.reason = 'Please provide a more detailed reason (at least 3 words).'
            return
        }
        delete form.errors.reason
        isEnhancing.value = true

        const enhanced = await enhanceReasonWithAI(form.reason, (streamedText) => {
            form.reason = streamedText
        })

        if (enhanced.success) {
            form.reason = enhanced.data
            isEnhancing.value = false
        } else {
            form.reason = originalReason
            isEnhancing.value = false
            if (enhanced.status === 422) {
                form.errors.reason = { message: enhanced.data, type: 'warning' }
            } else {
                form.errors.reason = 'Failed to enhance reason. Please try again.'
            }
        }
    } else {
        form.errors.reason = 'Please enter a reason to enhance.'
    }
}

</script>

<style scoped>
.tooltip-break::before {
    white-space: normal !important;
    word-break: break-word;
    width: 16rem;
    text-align: center;
}
</style>