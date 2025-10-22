<template>

    <Head title="For Approval" />
    <Modal ref="manageRequestModal" width="w-md">
        <div class="py-4 mt-2">
            <div class="flex flex-col gap-2 w-full">
                <!-- Stepper -->
                <div class="mb-6">
                    <Stepper :status="overtime.status" />
                </div>

                <div class="space-y-6">
                    <!-- Employee Information -->
                    <div class="card border border-base-300 shadow-sm">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:person-outline" width="20" height="20" />
                                Employee Information
                            </h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:badge-outline" width="14" height="14" />
                                        Name
                                    </span>
                                    <span class="font-semibold">{{ user.name }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:mail-outline" width="14" height="14" />
                                        Email
                                    </span>
                                    <span class="font-semibold">{{ user.email }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:user-attributes-rounded" width="14" height="14" />
                                        Employee ID
                                    </span>
                                    <span class="font-semibold">{{ user.employee_id }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:work-outline" width="14" height="14" />
                                        Role
                                    </span>
                                    <span class="font-semibold capitalize">{{ user.role }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Schedule -->
                    <div class="card border border-base-300 shadow-sm">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:schedule-outline" width="20" height="20" />
                                Registered Schedule
                            </h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:calendar-today-outline" width="14" height="14" />
                                        Date
                                    </span>
                                    <span class="font-semibold">{{ schedule.date }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:date-range-outline" width="14" height="14" />
                                        Week
                                    </span>
                                    <span class="font-semibold">{{ schedule.week }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:code" width="14" height="14" />
                                        Shift Code
                                    </span>
                                    <span class="font-semibold">{{ schedule.shift_code }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="clarity:employee-line" width="14" height="14" />
                                        Schedule
                                    </span>
                                    <span class="font-semibold">
                                        {{ schedule.shift_start === '--' || schedule.shift_end === '--'
                                            ? 'N/A'
                                            : schedule.shift_start + ' → ' + schedule.shift_end }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overtime Request -->
                    <div class="card border border-base-300 shadow-sm">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:timer-outline" width="20" height="20" />
                                Overtime Request
                            </h3>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:event-available-outline" width="14" height="14" />
                                        Filing Date
                                    </span>
                                    <span class="font-semibold">{{ overtime.created_at }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:schedule" width="14" height="14" />
                                        Time
                                    </span>
                                    <span class="font-semibold">{{ overtime.start_time }} → {{ overtime.end_time
                                        }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:hourglass-empty" width="14" height="14" />
                                        Hours
                                    </span>
                                    <span class="font-semibold text-lg">{{ overtime.hours }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs opacity-60 mb-1 flex items-center gap-1">
                                        <Icon icon="material-symbols:info-outline" width="14" height="14" />
                                        Status
                                    </span>
                                    <div class="badge badge-lg gap-2" :class="[
                                        overtime.status === 'PENDING' ? 'badge-warning' :
                                            (overtime.status === 'APPROVED' ? 'badge-success' :
                                                (['DISAPPROVED', 'CANCELED', 'DECLINED'].includes(overtime.status) ? 'badge-error' :
                                                    (overtime.status === 'FILED' ? 'badge-primary' : 'badge-ghost')))
                                    ]">
                                        {{ overtime.status }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="card border border-base-300 shadow-sm">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-3 flex items-center gap-2">
                                <Icon icon="material-symbols:description-outline" width="20" height="20" />
                                Reason
                            </h3>
                            <p class="opacity-80 leading-relaxed">{{ overtime.reason }}</p>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="card border border-base-300 shadow-sm">
                        <div class="card-body p-6">
                            <h3 class="card-title text-base mb-4 flex items-center gap-2">
                                <Icon icon="material-symbols:comment-outline" width="20" height="20" />
                                Remarks
                            </h3>
                            <TextArea type="text" v-model="overtimeRequestForm.remarks"
                                :message="overtimeRequestForm.errors?.remarks"
                                :disabled="['FILED', 'DECLINED', 'CANCELED'].includes(overtime.status)"
                                :placeholder="['FILED', 'DECLINED', 'APPROVED'].includes(overtime.status) ? '' : 'Enter any remarks regarding this request...'" />
                        </div>
                    </div>

                    <!-- Close Button -->
                    <div class="divider"></div>
                    <div class="flex justify-between gap-4">
                        <div>
                            <button class="btn btn-neutral" :disabled="overtimeRequestForm.processing"
                                @click="closeManageRequestModal()">CLOSE</button>
                        </div>
                        <div>
                            <div v-if="overtime.status === 'PENDING'" class="flex flex-end gap-2">
                                <button class="btn btn-secondary" :disabled="overtimeRequestForm.processing"
                                    @click="updateOvertiemRequestStatus('DISAPPROVED')">
                                    <span
                                        v-if="overtimeRequestForm.processing && overtimeRequestForm.update_status === 'DISAPPROVED'"
                                        class="loading loading-spinner"></span>
                                    <span>DISAPPROVE</span>
                                </button>
                                <button class="btn btn-primary" :disabled="overtimeRequestForm.processing"
                                    @click="updateOvertiemRequestStatus('APPROVED')"> <span
                                        v-if="overtimeRequestForm.processing && overtimeRequestForm.update_status === 'APPROVED'"
                                        class="
                                    loading loading-spinner"></span>
                                    <span>APPROVE</span></button>
                            </div>
                            <div v-if="overtime.status === 'APPROVED'" class="flex flex-end gap-2">
                                <button class="btn btn-secondary" :disabled="overtimeRequestForm.processing"
                                    @click="updateOvertiemRequestStatus('DECLINED')"><span
                                        v-if="overtimeRequestForm.processing && overtimeRequestForm.update_status === 'DECLINED'"
                                        class="
                                    loading loading-spinner"></span>
                                    <span>DECLINE</span></button>
                                <button class="btn btn-primary" :disabled="overtimeRequestForm.processing"
                                    @click="updateOvertiemRequestStatus('FILED')"><span
                                        v-if="overtimeRequestForm.processing && overtimeRequestForm.update_status === 'FILED'"
                                        class="
                                    loading loading-spinner"></span>
                                    <span>FILE</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>

    <div class="flex flex-col gap-6 p-4 h-full">
        <!-- Breadcrumbs -->
        <div class="breadcrumbs text-sm">
            <ul>
                <li>
                    <Link :href="route('main', { week: selectedWeek, year: selectedYear })">Dashboard</Link>
                </li>
                <li>
                    <Link
                        :href="route('overtime.pending', { status: 'PENDING', page: 'Approver/Pending', week: selectedWeek, year: selectedYear })"
                        class="text-primary font-semibold underline">
                    Pending
                    </Link>
                </li>
                <li>
                    <Link
                        :href="route('overtime.filing', { status: 'APPROVED', page: 'Approver/Filing', week: selectedWeek, year: selectedYear })">
                    Filing
                    </Link>
                </li>
                <li>
                    <Link
                        :href="route('overtime.filing', { status: 'FILED', page: 'Approver/Filed', week: selectedWeek, year: selectedYear })">
                    Filed
                    </Link>
                </li>
            </ul>
        </div>

        <!-- Page Title -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-base-content">Pending Overtime Requests</h1>
        </div>

        <div class="stats stats-horizontal shadow flex-wrap">
            <Card title="Requests to File" :value="total_requests" description="For Approval" />
            <Card title="Total Overtime Hours" :value="total_requests_hours" description="Awaiting confirmation" />
            <Card title="Registered ROA" :value="roa_hours"
                :description="roa_hours <= 0 ? 'No ROA hours remaining — please file additional hours.' : 'You still have ROA hours available.'" />
            <Card title="Remaining Hours" :value="remaining_hours" :description="remaining_hours === 0
                ? 'No remaining hours'
                : remaining_hours < 0
                    ? 'ROA consumed exceeded — please file additional hours'
                    : 'Remaining hours available'" />
        </div>

        <!-- Filing Table -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between mb-4">
                    <h2 class="card-title">Approved Requests Awaiting Filing</h2>
                    <div class="flex flex-row flex-end gap-4 w-1/4">
                        <SelectOption :options="years" v-model="selectedYear" margin=''
                            @change="handleWeekSelection()" />
                        <SelectOption :options="weeks" v-model="selectedWeek" margin=''
                            @change="handleWeekSelection()" />
                    </div>
                </div>

                <div class="overflow-x-auto min-h-[10vh] max-h-[50vh]">
                    <table class="table table-zebra w-full">
                        <thead class="sticky top-0 bg-base-300 z-10 rounded">
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Week</th>
                                <th>Hours</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="requests.length === 0">
                                <td colspan="6" class="text-center h-48 italic text-gray-400 py-4">
                                    No pending request(s)
                                </td>
                            </tr>
                            <tr v-for="request in requests" :key="request.id">
                                <td>{{ request.user.employee_id }}</td>
                                <td>{{ request.user.name }}</td>
                                <td>{{ request.schedule.date }}</td>
                                <td>{{ request.schedule.week }}</td>
                                <td>{{ request.overtime.hours }}</td>
                                <td class="flex gap-2 justify-center">
                                    <button class="btn btn-xs text-sm btn-primary"
                                        @click="openManageRequestModal(request)">Manage</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, watch, computed } from 'vue'
import Card from '../Components/Card.vue'
import SelectOption from '../Components/SelectOption.vue'
import TextArea from '../Components/TextArea.vue'
import Stepper from '../Components/Stepper.vue'
import { weeks, years, currentWeek } from '../utils/dropdownOptions.js'
import Modal from '../Components/Modal.vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const toast = inject('toast')

const props = defineProps({
    info: Object,
    success: Boolean,
    message: String
})

const selectedWeek = ref(props?.info?.payload?.week)
const selectedYear = ref(props?.info?.payload?.year)
const requests = ref([...props?.info?.requests ?? []])
const roa_hours = ref(props?.info?.hours?.limit ?? 0)
const remaining_hours = ref(props?.info?.hours?.remaining ?? 0)

const total_requests = computed(() => {
    return props?.info?.requests.length
})

const total_requests_hours = computed(() => {
    return props.info.requests.reduce((sum, r) => sum + (r.overtime?.hours ?? 0), 0).toFixed(2)
})

const user = ref({
    name: '',
    employee_id: '',
    role: '',
    email: ''
})

const schedule = ref({
    date: '',
    shift_code: '',
    shift_start: '',
    shift_end: '',
    week: '',
})

const overtime = ref({
    start_time: '',
    end_time: '',
    hours: '',
    status: '',
    created_at: '',
    reason: '',
    remarks: '',
})

const overtimeRequestForm = useForm({
    id: '',
    current_status: '',
    update_status: '',
    remarks: ''
})

// ===== Watchers =====

watch(() => props?.info?.requests, (updatedRequest) => {
    requests.value = [...updatedRequest]
})

watch(() => props.info.payload.week, (newWeek) => {
    selectedWeek.value = newWeek
})

watch(() => props.info.payload.year, (newYear) => {
    selectedYear.value = newYear
})

watch(() => props?.info?.hours?.remaining, (newRemaining) => {
    remaining_hours.value = newRemaining
})

const manageRequestModal = ref(null)

const openManageRequestModal = (data) => {
    manageRequestModal.value?.open()

    // id of the overtime request itself (will use for update status)
    // for events like approval, disapproval, declining, or filing
    user.value = {
        name: data?.user?.name,
        employee_id: data?.user?.employee_id,
        role: data?.user?.role,
        email: data?.user?.email
    }

    schedule.value = {
        date: data?.schedule?.date,
        shift_code: data?.schedule?.shift_code,
        shift_start: data?.schedule?.shift_start,
        shift_end: data?.schedule?.shift_end,
        week: data?.schedule?.week,
    }

    overtime.value = {
        start_time: data?.overtime?.start_time,
        end_time: data?.overtime?.end_time,
        hours: data?.overtime?.hours,
        status: data?.overtime?.status,
        created_at: data?.overtime?.created_at,
        reason: data?.overtime?.reason,
        remarks: data?.overtime?.remarks,
    }

    // populate the data for form (to use in updating the request's status)
    overtimeRequestForm.id = data?.id
    overtimeRequestForm.current_status = data?.overtime?.status
    overtimeRequestForm.remarks = data?.overtime?.remarks
}

const closeManageRequestModal = () => {
    manageRequestModal.value?.close()
}


// === Requests ===

const updateOvertiemRequestStatus = (status) => {
    if (roa_hours.value === 0 && status === 'APPROVED') {
        toast('No registered ROA yet.', 'error')
        return
    }

    if (status && overtimeRequestForm.id) {
        overtimeRequestForm.update_status = status
        overtimeRequestForm.post(route('overtime.update.approver'), {
            onSuccess: () => {
                overtimeRequestForm.reset()
                closeManageRequestModal()
                setTimeout(() => {
                    toast(`Overtime request has been ${status}`, 'success')
                }, 200);
            },
            onError: (errors) => {
                toast('Failed to update schedule. Please try again', 'error')
                console.log(errors)
            }
        })
    } else {
        toast('Failed to update schedule. Please try again', 'error')
    }
}


const handleWeekSelection = () => {
    router.get(route('overtime.pending'), {
        year: selectedYear.value,
        week: selectedWeek.value,
        status: 'PENDING',
        page: 'Approver/Pending'
    }, {
        preserveState: true
    })
}
</script>
