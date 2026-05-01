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
                                        <div class="badge badge-sm gap-2" :class="getStatusBadgeClass(formFilledOvertime.current_status)">
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

                        <div class="card border border-base-300 shadow-sm overflow-hidden flex flex-col">
                            <div class="p-6 pb-0">
                                <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                    <Icon icon="material-symbols:schedule-outline" width="20" height="20" />
                                    Your Scheduled Shift
                                </h3>
                            </div>
                            <div class="grid grid-cols-3 gap-4 px-6 pb-6">
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
                                                <span class="font-semibold">{{ to12hr(formFilledOvertime.start_time)
                                                }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs opacity-60 mb-1">End Time</span>
                                                <span class="font-semibold">{{ to12hr(formFilledOvertime.end_time)
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
                                    <div class="tooltip tooltip-left tooltip-break"
                                                    data-tip="The better you describe, the better AI can enhance it!">
                                                    <span tabindex="0" class="inline-block">
                                                        <button type="button" class="btn btn-sm gap-2 btn-primary"
                                                            @click="handleEnhance(formFilledOvertime)" :disabled="isEnhancing">
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
            <Card title="Total Hours" :value="heatmapStats.total_hours + ' hrs'" />
            <Card title="Filed Requests" :value="heatmapStats.filed" />
            <Card title="Pending Requests" :value="heatmapStats.pending" />
            <Card title="Approved Requests" :value="heatmapStats.approved" />
            <Card title="Rejected Requests" :value="heatmapStats.rejected" />
        </div>

        <!-- Heatmap -->
        <Heatmap
            :data="heatmapDataObj"
            :years="heatmapYears"
            :stats="heatmapStats"
            :loading="heatmapLoading"
            @change-year="handleHeatmapYearChange"
            @change-filters="handleHeatmapFilterChange"
            @computed-stats="handleComputedStats"
        />

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
                                    <div v-if="req.reason && req.reason.length > 40"
                                        class="tooltip tooltip-left tooltip-break"
                                        :data-tip="req.reason">
                                        <p class="text-sm text-base-content/80 break-words">
                                            {{ truncateText(req.reason) }}
                                        </p>
                                    </div>
                                    <p v-else class="text-sm text-base-content/80 break-words">
                                        {{ req.reason }}
                                    </p>
                                </td>

                                <td>
                                    <span class="text-sm"
                                        :class="req.remarks ? 'text-base-content/80' : 'text-base-content/40 italic'">
                                        {{ req.remarks ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="badge badge-sm font-semibold gap-1" :class="getStatusBadgeClass(req.status)">
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
import { truncateText } from '../utils/helpers/format.js'
import { getStatusBadgeClass } from '../utils/helpers/status.js'
import { enhanceReason, submitCancelation as submitCancelationComposable } from '../composables/useOvertimeRequest.js'
import { to12hr, to24hr } from '../utils/helpers/date.js'
import { fetchHeatmapData } from '../api/heatmap.js'

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
    loadHeatmapData(null, heatmapActiveStatuses.value)
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

const heatmapStats = ref({
    total_hours: '0.00',
    filed: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
})

const heatmapLoading = ref(false)
const heatmapDataObj = ref({})
const heatmapYears = ref([])
const heatmapSelectedYear = ref(null)
const heatmapActiveStatuses = ref(['APPROVED', 'FILED', 'PENDING'])

function dateKey(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function snapToSunday(date) {
    const d = new Date(date)
    d.setDate(d.getDate() - d.getDay())
    return d
}

function computeHeatmapRange(year) {
    if (year === null) {
        const end = new Date()
        const start = new Date()
        start.setDate(start.getDate() - 365)
        return { start, end }
    }
    return { start: new Date(year, 0, 1), end: new Date(year, 11, 31) }
}

let heatmapAbortController = null

async function loadHeatmapData(year, statuses) {
    if (statuses.length === 0) {
        heatmapDataObj.value = {}
        heatmapStats.value = { total_hours: '0.00', filed: 0, pending: 0, approved: 0, rejected: 0 }
        return
    }

    heatmapAbortController?.abort()
    const controller = new AbortController()
    heatmapAbortController = controller
    heatmapLoading.value = true

    try {
        const { start, end } = computeHeatmapRange(year)
        const res = await fetchHeatmapData(dateKey(start), dateKey(end), statuses, controller.signal)
        const data = res.data || {}

        if (res.years && res.years.length > 0) {
            heatmapYears.value = [...res.years].sort((a, b) => b - a)
        }

        heatmapDataObj.value = data

        if (res.stats) {
            heatmapStats.value = res.stats
        }
    } catch (e) {
        if (e.name === 'AbortError') return
        heatmapDataObj.value = {}
    } finally {
        if (controller === heatmapAbortController) {
            heatmapLoading.value = false
        }
    }
}

function handleHeatmapYearChange(year) {
    heatmapSelectedYear.value = year
    loadHeatmapData(year, heatmapActiveStatuses.value)
}

function handleHeatmapFilterChange(statuses) {
    heatmapActiveStatuses.value = statuses
    loadHeatmapData(heatmapSelectedYear.value, statuses)
}

function handleComputedStats(stats) {
    heatmapStats.value = { ...heatmapStats.value, ...stats }
}

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

const openRequestModal = (data) => {
    formFilledOvertime.id = data.id
    formFilledOvertime.employee_schedule_id = data.employee_schedule_id
    formFilledOvertime.date = data.date
    formFilledOvertime.created_at = data.created_at
    formFilledOvertime.week = data.week
    formFilledOvertime.hours = data.hours
    formFilledOvertime.start_time = to24hr(data.start_time)
    formFilledOvertime.end_time = to24hr(data.end_time)
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

const handleEnhance = (form) => enhanceReason(form, isEnhancing)

const submitCancelation = () => {
    submitCancelationComposable(formFilledOvertime, modeUpdate, toast, () => {
        confirmingCancel.value = false
        modeUpdate.value = false
        formFilledOvertime.reset()
        closeRequestModal()
    })
}

</script>

<style scoped>
.tooltip-break::before {
    white-space: normal !important;
    word-break: break-word;
    width: 16rem;
    text-align: center;
    z-index: 50 !important;
}
</style>