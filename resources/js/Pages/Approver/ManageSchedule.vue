<template>

    <Head title="Manage Schedules" />
    <Modal ref="confirmSubmitModal">
        <div class="flex flex-col gap-2 p-2">
            <div class="flex flex-row justify-start items-center gap-2">
                <h3 class="text-lg font-bold">Confirm Submission</h3>
                <Icon icon="stash:question" width="28" height="28" />
            </div>

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


    <div class="flex flex-col gap-6 h-full pb-12">
        <div class="breadcrumbs text-sm">
            <ul>
                <li>
                    <Link :href="route('main')">Home</Link>
                </li>
                <li>
                    <Link :href="route('schedule.manage')">Manage Schedule</Link>
                </li>
            </ul>
        </div>
        <!-- Page Heading -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Employee Schedule</h1>
            <div class="flex space-x-4 w-2/4">
                <div class="flex-1">
                    <SelectOption :options="years" v-model="selectedYear" :message="alreadyLoaded ? '-' : ''" />
                </div>
                <div class="flex-1">
                    <SelectOption :options="weeks" v-model="selectedWeek" :message="alreadyLoaded ? '-' : ''" />
                </div>
                <div>
                    <button class="btn btn-primary flex items-center gap-2 px-4" @click="handleAddWeek()"
                        :disabled="isLoading || addingWeek">
                        <Icon icon="material-symbols:add-rounded" width="24" height="24" v-if="!addingWeek" />
                        <span v-if="addingWeek"><span class="loading loading-spinner loading-xs"></span>
                            Add Week</span>
                        <span v-else>Add Week</span>
                    </button>
                </div>
                <div>
                    <button class="btn btn-primary flex items-center gap-2 px-4" :disabled="isLoading || addingWeek"
                        @click="openConfirmModal()">
                        <Icon icon="mingcute:schedule-line" width="24" height="24" /> Submit
                        Schedule
                    </button>
                </div>
            </div>

        </div>

        <div v-if="isLoading" class="flex w-full items-center justify-center rounded-md bg-base-100 py-4">
            <span class="loading loading-spinner mr-2"></span>
            Loading current schedule...
        </div>

        <div v-else v-for="(sched, index) in employeeSchedules" :key="index"
            class="bg-base-100 p-6 rounded-lg shadow-sm">

            <!-- Top Section (fixed, does not scroll) -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h2
                    class="text-xl font-semibold tracking-tight leading-tight text-base-content flex items-center gap-2">
                    <span>Schedule for</span>
                    <span class="font-bold">Week {{ sched.week }} — {{ sched.year }}</span>
                </h2>

                <div class="flex flex-row gap-6">
                    <p class="mt-2 sm:mt-0 text-base-content opacity-70 font-medium">
                        {{ sched.week_start }} — {{ sched.week_end }}
                    </p>
                    <div class="tooltip tooltip-right tooltip-error" data-tip="REMOVE">
                        <Icon icon="gg:remove" width="28" height="28" @click="removeSchedule(index)"
                            class="hover:bg-error hover:cursor-pointer rounded-full" />
                    </div>
                </div>
            </div>

            <!-- Scrollable Table -->
            <div class="max-h-80 overflow-y-auto overflow-x-auto">
                <table class="table w-full h-auto">
                    <thead class="sticky top-0 bg-base-200 z-10">
                        <tr class="text-center">
                            <th>Employee</th>
                            <th>Default Shift</th>
                            <th>Sunday</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(sch, index_sched) in sched.week_schedule" :key="index_sched">
                            <td class="text-center">{{ sch.name }}</td>
                            <td class="text-center">
                                <div class="flex justify-center">
                                    <label class="label">
                                        <input type="checkbox" :checked="isDefaultShift(sch.schedule)"
                                            class="checkbox checkbox-primary"
                                            @change="handleDefaultShiftFill($event, index, index_sched)" />
                                    </label>
                                </div>
                            </td>
                            <td v-for="day in sch.schedule" :key="day.date">
                                <div class="w-full flex justify-center items-center">
                                    <span class="loading loading-spinner" v-if="isLoading"></span>
                                    <span v-else class="w-full">
                                        <SelectOption :options="shifts" v-model="day.shift_id" margin="" />
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
<script setup>
import { onMounted, ref, inject } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from "@iconify/vue"

import { years, weeks, currentWeek } from '../utils/dropdownOptions.js'
import { fetchShiftList } from '../api/shift.js'
import { fetchEmployeeSchedule, submitEmployeeSchedule } from '../api/schedule.js'

import SelectOption from '../Components/SelectOption.vue'
import Modal from '../Components/Modal.vue'

const toast = inject('toast')

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


onMounted(async () => {
    isLoading.value = true


    // fetch first the list of registered shift codes
    // this data will be the content of the select option that allows the user
    // to change the current shift code of the emploployee on specific day

    const shiftsResponse = await fetchShiftList('approver')

    // Format the shift data into { label, value } structure
    // so it can be used directly in <SelectOption>
    const shiftData = shiftsResponse?.data?.data ?? []
    shifts.value = shiftData.map(element => ({
        label: (element.start_time && element.end_time)
            ? element.code
            : element.code,
        value: element.id
    }))

    const scheduleResponse = await fetchEmployeeSchedule(selectedYear.value, selectedWeek.value)

    if (scheduleResponse.data.success) {
        employeeSchedules.value = [{
            week_schedule: scheduleResponse.data.info?.schedules,
            week: scheduleResponse.data.info?.week,
            year: scheduleResponse.data.info?.year,
            week_start: scheduleResponse.data.info?.week_start,
            week_end: scheduleResponse.data.info?.week_end,
        }]

    } else {
        toast("Loading Employee(s) schedule failed. Please try again", 'error')
    }

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
    if (scheduleResponse.data.success) {
        employeeSchedules.value.push({
            week_schedule: scheduleResponse.data.info?.schedules,
            week: scheduleResponse.data.info?.week,
            year: scheduleResponse.data.info?.year,
            week_start: scheduleResponse.data.info?.week_start,
            week_end: scheduleResponse.data.info?.week_end,
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
    let targetSchedule = employeeSchedules.value[schedIndex].week_schedule[rowIndex].schedule

    if (event.target.checked) {
        // Apply default shifts
        let default_shiftcodes = ['SX', 'S7', 'S7', 'S7', 'S7', 'C9', 'SY']

        let default_shiftcodes_id = default_shiftcodes.map(code => {
            let match = shifts.value.find(shift => shift.label === code)
            return match ? match.value : null
        })

        for (let j = 0; j < targetSchedule.length; j++) {
            targetSchedule[j].shift_id = default_shiftcodes_id[j]
        }
    } else {
        // Clear all shifts for this row
        for (let j = 0; j < targetSchedule.length; j++) {
            targetSchedule[j].shift_id = null
        }
    }
}

const isDefaultShift = (schedule) => {
    let default_shiftcodes = ['SX', 'S7', 'S7', 'S7', 'S7', 'C9', 'SY']

    // Compare schedule's shift labels with default codes
    return schedule.every((day, idx) => {
        let match = shifts.value.find(shift => shift.value === day.shift_id)
        return match ? match.label === default_shiftcodes[idx] : false
    })
}



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
    if (submitResponse.data.success) {
        toast('Schedule submitted successfully', 'success')
    } else {
        toast('Schedule submission failed. Please try again.', 'error')
    }

    isSubmitting.value = false
    confirmSubmitModal.value?.close()
}

</script>