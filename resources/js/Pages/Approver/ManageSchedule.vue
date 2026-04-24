<template>

    <Head title="Manage Schedules" />
    <Modal ref="confirmSubmitModal" title="Confirm Submission">
        <div class="flex flex-col gap-2">
            <p class="text-md">
                Are you sure you want to submit the employee's schedule?
                This action cannot be undone.
            </p>
            <div class="alert alert-warning px-3 py-2 rounded text-sm flex justify-center text-center gap-2">
                <Icon icon="ph:warning" width="24" height="24" />
                <span>Updating an employee's schedule may cause disalignment in their filed overtime.</span>
            </div>

            <div class="flex justify-end gap-2 mt-2">
                <button class="btn btn-sm btn-neutral" @click="closeConfirmModal()" :disabled="isSubmitting">
                    Cancel
                </button>
                <button class="btn btn-sm btn-primary" @click="hanldesubmitSchedule()" :disabled="isSubmitting">
                    <span v-if="isSubmitting" class="loading loading-spinner loading-xs"></span>
                    Yes, Submit
                </button>
            </div>
        </div>
    </Modal>


    <div class="flex flex-col gap-6">
        <Breadcrumbs :items="[
            { label: 'Home', route: 'main' },
            { label: 'Manage Schedule', route: 'schedule.manage', active: true },
        ]" />

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-base-content">Manage Schedule</h1>
                <p class="text-base-content/60 text-sm mt-1">Assign and manage employee shift schedules</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-base-200 rounded-xl px-1 py-1 gap-0.5">
                    <select v-model="selectedYear"
                        class="select select-ghost select-sm bg-transparent font-medium focus:outline-none border-none min-w-28">
                        <option v-for="y in years" :key="y.value" :value="y.value">{{ y.label }}</option>
                    </select>
                    <div class="w-px h-5 bg-base-300"></div>
                    <select v-model="selectedWeek"
                        class="select select-ghost select-sm bg-transparent font-medium focus:outline-none border-none min-w-36">
                        <option v-for="w in weeks" :key="w.value" :value="w.value">{{ w.label }}</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm gap-1" @click="handleAddWeek()"
                    :disabled="isLoading || addingWeek">
                    <span v-if="addingWeek" class="loading loading-spinner loading-xs"></span>
                    <Icon v-if="!addingWeek" icon="material-symbols:add-rounded" width="18" height="18" />
                    <span>Add Week</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:h-[70vh]">

            <div class="card bg-base-100 lg:col-span-4 flex flex-col min-h-0 h-full">
        <div class="card-body flex flex-col p-4 min-h-0 h-full">
            <div class="overflow-auto flex-1 min-h-0 p-4">
                        <div v-if="isLoading" class="flex w-full items-center justify-center py-12">
                            <span class="loading loading-spinner mr-2"></span>
                            Loading schedules...
                        </div>

                        <div v-else-if="employeeSchedules.length === 0" class="min-h-96 flex items-center justify-center">
                            <p class="text-base-content/40 italic text-sm">Select a year and week, then click Add Week
                            </p>
                        </div>

                        <template v-else>
                            <div v-for="(sched, index) in employeeSchedules" :key="index" class="mb-6 last:mb-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm">W{{ sched.week }} — {{ sched.year }}</span>
                                        <span class="text-xs text-base-content/50">{{ sched.week_start }} — {{
                                            sched.week_end }}</span>
                                    </div>
                                    <div class="tooltip tooltip-left tooltip-error" data-tip="Remove Week">
                                        <Icon icon="gg:remove" width="18" height="18" @click="removeSchedule(index)"
                                            class="hover:bg-error hover:cursor-pointer rounded-full" />
                                    </div>
                                </div>
                                <table class="table w-full text-sm">
                                    <thead class="bg-base-200 rounded">
                                        <tr>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider">
                                                Employee</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center"
                                                v-if="defaultShiftCodes.length > 0">Default</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                                Sun</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                                Mon</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                                Tue</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                                Wed</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                                Thu</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                                Fri</th>
                                            <th
                                                class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                                Sat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(sch, index_sched) in sched.week_schedule"
                                            :key="index_sched" class="text-sm">
                                            <td class="font-medium whitespace-nowrap">{{ sch.name }}</td>
                                            <td v-if="defaultShiftCodes.length > 0" class="text-center">
                                                <div class="flex justify-center">
                                                    <label
                                                        class="label tooltip tooltip-left"
                                                        data-tip="Default Shift">
                                                        <input type="checkbox"
                                                            class="checkbox checkbox-primary checkbox-xs"
                                                            :checked="isDefaultShift(sch.schedule)"
                                                            :disabled="isLoading || isSubmitting"
                                                            @change="handleDefaultShiftFill($event, index, index_sched)" />
                                                    </label>
                                                </div>
                                            </td>
                                            <td v-for="day in sch.schedule" :key="day.date">
                                                <SelectOption :options="shifts" v-model="day.shift_id" margin=""
                                                    size="select-xs" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-if="index < employeeSchedules.length - 1" class="divider my-4"></div>
                            </div>
                        </template>
                    </div>

                    <div class="divider my-2"></div>
                    <div class="flex justify-between items-center">
                        <span v-if="defaultShiftCodes.length > 0" class="text-xs text-base-content/40 italic">
                            *Check "Default" to auto-fill shifts. Uncheck to erase.
                        </span>
                        <span v-else></span>
                        <button type="submit" class="btn btn-neutral btn-sm rounded-lg"
                            @click="openConfirmModal()"
                            :disabled="isSubmitting || isLoading || employeeSchedules.length === 0">
                            <span v-if="isSubmitting" class="loading loading-spinner"></span>
                            <span>Submit Schedule</span>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="shiftReference.length > 0" class="card bg-base-100 overflow-hidden flex flex-col h-full">
                <div class="p-4 pb-0">
                    <h3 class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-3">
                        Shift Reference
                    </h3>
                </div>
                <div class="overflow-y-auto px-4 pb-4 flex-1">
                    <table class="table text-sm">
                        <thead class="sticky top-0 bg-base-200 rounded z-10">
                            <tr>
                                <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Code
                                </th>
                                <th
                                    class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">
                                    Time Range
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ref in shiftReference" :key="ref.id">
                                <td class="font-bold text-base-content">{{ ref.code }}</td>
                                <td class="text-base-content/60 text-center">{{ ref.timeRange }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</template>
<script setup>
import { onMounted, ref, inject } from 'vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from "@iconify/vue"

import { years, weeks, currentWeek } from '../utils/dropdownOptions.js'
import { to12hr } from '../utils/helpers/date.js'
import { fetchShiftList } from '../api/shift.js'
import { fetchEmployeeSchedule, submitEmployeeSchedule } from '../api/schedule.js'
import { buildShiftReference, isDefaultShift as checkDefaultShift, applyDefaultShiftFill } from '../composables/useScheduleManager.js'
import SelectOption from '../Components/SelectOption.vue'
import Modal from '../Components/Modal.vue'

const toast = inject('toast')
const appConfig = inject('appConfig')

const props = defineProps({
    flash: Object,
    success: Boolean,
    auth: Object,
})


// flgs
const isSubmitting = ref(false)
const isLoading = ref(false)
const alreadyLoaded = ref(false)
const addingWeek = ref(false)
const confirmSubmitModal = ref(null)

// Default selected from props or get manually for year and week
const selectedYear = ref(new Date().getFullYear())
const selectedWeek = ref(currentWeek())

const employeeSchedules = ref([])
const shifts = ref([])
const shiftData = ref([])
const defaultShiftCodes = ref([])

const shiftReference = buildShiftReference(shiftData)


onMounted(async () => {
    isLoading.value = true


    // fetch first the list of registered shift codes
    // this data will be the content of the select option that allows the user
    // to change the current shift code of the emploployee on specific day

    const shiftsResponse = await fetchShiftList('approver')

    const shiftDataResponse = shiftsResponse?.data ?? []
    shiftData.value = shiftDataResponse
    shifts.value = shiftDataResponse.map(element => ({
        label: element.code,
        value: element.id
    }))

    const scheduleResponse = await fetchEmployeeSchedule(selectedYear.value, selectedWeek.value)

    if (scheduleResponse.success) {
        employeeSchedules.value = [{
            week_schedule: scheduleResponse.info?.schedules,
            week: scheduleResponse.info?.week,
            year: scheduleResponse.info?.year,
            week_start: scheduleResponse.info?.week_start,
            week_end: scheduleResponse.info?.week_end,
        }]

    } else {
        toast("Loading Employee(s) schedule failed. Please try again", 'error')
    }

    defaultShiftCodes.value = Array.isArray(appConfig.value?.default_shift_codes)
        ? appConfig.value.default_shift_codes.map(entry => entry.code.trim()).filter(code => code !== '')
        : []

    isLoading.value = false
})


const handleAddWeek = async () => {

    let exists = employeeSchedules.value.some(sched =>
        parseInt(sched.year) === selectedYear.value &&
        parseInt(sched.week) === selectedWeek.value
    )

    if (exists) {
        toast('Schedule for this week is already loaded.', 'warning')
        alreadyLoaded.value = true
        return
    }

    alreadyLoaded.value = false
    addingWeek.value = true
    const scheduleResponse = await fetchEmployeeSchedule(selectedYear.value, selectedWeek.value)
    if (scheduleResponse.success) {
        employeeSchedules.value.push({
            week_schedule: scheduleResponse.info?.schedules,
            week: scheduleResponse.info?.week,
            year: scheduleResponse.info?.year,
            week_start: scheduleResponse.info?.week_start,
            week_end: scheduleResponse.info?.week_end,
        })

        employeeSchedules.value.sort((a, b) => {
            if (a.year !== b.year) return a.year - b.year
            return a.week - b.week
        })

    } else {
        toast("Loading Employee(s) schedule failed. Please try again", 'error')
    }
    addingWeek.value = false
}

const handleDefaultShiftFill = (event, schedIndex, rowIndex) => {
    const targetSchedule = employeeSchedules.value[schedIndex].week_schedule[rowIndex].schedule
    applyDefaultShiftFill(event.target.checked, targetSchedule, defaultShiftCodes.value, shifts.value, toast, 'shift_id')
}

const isDefaultShift = (schedule) => checkDefaultShift(schedule, defaultShiftCodes.value, shiftData.value, 'shift_id')



const removeSchedule = (index) => {
    employeeSchedules.value.splice(index, 1)
}

const openConfirmModal = () => {
    confirmSubmitModal.value?.open()
}

const closeConfirmModal = () => {
    confirmSubmitModal.value?.close()
}

const hanldesubmitSchedule = async () => {

    if (employeeSchedules.value.length === 0) {
        confirmSubmitModal.value?.close()
        toast('Please load a schedule before submitting.', 'warning')
        return
    }

    isSubmitting.value = true
    let submitResponse = await submitEmployeeSchedule(employeeSchedules.value)
    if (submitResponse.success) {
        toast('Schedule submitted successfully', 'success')
    } else {
        toast('Schedule submission failed. Please try again.', 'error')
    }

    isSubmitting.value = false
    confirmSubmitModal.value?.close()
}

</script>