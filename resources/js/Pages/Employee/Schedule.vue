<template>

    <Head title="Manage Schedule" />
    <div class="flex flex-col gap-6">
        <div class="breadcrumbs text-sm">
            <ul>
                <li>
                    <Link :href="route('main')">Home</Link>
                </li>
                <li>
                    <Link :href="route('schedule')">Manage Schedule</Link>
                </li>
            </ul>
        </div>
        <!-- Page Heading -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Manage Schedule</h1>
            <div class="grid grid-cols-2 gap-4 w-1/3">
                <div class="col-span-1 w-full">
                    <SelectOption name="Year" :options="years" v-model="selectedYear" @change="loadScheduleData()" />
                </div>
                <div class="col-span-1 w-full">
                    <SelectOption name="Week" :options="weeks" v-model="selectedWeek" @change="loadScheduleData()" />
                </div>
            </div>
        </div>
        <div class="overflow-x-auto bg-base-100 p-4 rounded max-w-7xl mx-auto w-full">
            <table class="table min-h-96 w-full text-lg">
                <thead class="bg-base-200 rounded">
                    <tr>
                        <th>Date</th>
                        <th>Week</th>
                        <th>Day</th>
                        <th class="w-1/3 text-center">Shift Code</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="isLoading">
                        <td colspan="4" class="text-center italic text-gray-400 py-4">
                            <span class="loading loading-spinner"></span> Loading Schedules...
                        </td>
                    </tr>

                    <template v-else-if="schedules.length > 0">
                        <tr v-for="schedule in schedules" :key="schedule.id">
                            <td>{{ schedule.date }}</td>
                            <td>{{ schedule.week }}</td>
                            <td>{{ schedule.day }}</td>
                            <td class="flex justify-center items-center">
                                <span class="w-full">
                                    <SelectOption :options="shifts" v-model="schedule.shift_code" margin="" />
                                </span>
                            </td>
                        </tr>
                    </template>

                    <tr v-else>
                        <td colspan="4" class="text-center italic text-gray-400 py-4">
                            {{ tableText }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="divider"></div>
            <div class="flex justify-between">
                <div v-if="defaultShiftCodes.length > 0" class="flex flex-col gap-2">
                    <label class="label text-sm font-semibold">
                        <input type="checkbox" class="checkbox checkbox-primary"
                            @change="handleDefaultShiftFill($event, schedules)" :checked="isDefaultShift(schedules)"
                            :disabled="isLoading || isSubmitting" />
                        DEFAULT SHIFT
                    </label>
                    <span class="text-xs text-gray-500 italic">*If unchecked, the selected schedule will be
                        erased</span>
                </div>
                <div v-else></div>
                <button type="submit" class="btn btn-neutral mt-4" @click="submitForm()"
                    :disabled="isSubmitting || isLoading">
                    <span v-if="isSubmitting" class="loading loading-spinner"></span>
                    <span>Submit Schedule</span>
                </button>
            </div>
        </div>

    </div>
</template>
<script setup>
import SelectOption from '../Components/SelectOption.vue'
import { onMounted, ref, inject } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { years, weeks, currentWeek } from '../utils/dropdownOptions.js'
import { fetchShiftList } from '../api/shift.js'
import { fetchSchedule, submitSchedule } from '../api/schedule.js'

// Get user ID from page props (Inertia auth session)
const page = usePage()
const user_id = ref(page?.props?.auth?.user?.id)

const toast = inject('toast')

// Default selected year and week
const selectedYear = ref(new Date().getFullYear())
const selectedWeek = ref(currentWeek())

const isLoading = ref(false)
const updateSelectedSchedule = ref(false)
const isSubmitting = ref(false)
const initshifts = ref([]) // raw shift data from API
const shifts = ref([])     // formatted shift data for <SelectOption>
const schedules = ref([])
const tableText = ref('No registered Schedule.')
const defaultShiftCodes = ref([])


const submitForm = async () => {
    isSubmitting.value = true
    const submitResponse = await submitSchedule(schedules.value)
    if (submitResponse?.success) {
        schedules.value = submitResponse.schedules
        toast(submitResponse?.message, 'success')
    } else {
        toast(submitResponse?.message, 'error')
    }
    isSubmitting.value = false
}

onMounted(() => {
    isLoading.value = true
    loadScheduleData()
})

async function loadScheduleData() {
    isLoading.value = true
    // Fetch schedule for the logged-in user and selected week/year
    const scheduleResponse = await fetchSchedule(selectedYear.value, selectedWeek.value)

    if (scheduleResponse?.success) {
        // Fetch all available shift codes
        const shiftsResponse = await fetchShiftList('employee')
        initshifts.value = shiftsResponse

        // Format the shift data into { label, value } structure
        // so it can be used directly in <SelectOption>
        const shiftData = shiftsResponse?.data ?? []
        shifts.value = shiftData.map(element => ({
            label: (element.start_time && element.end_time) ? `${element.code}: ${element.start_time} - ${element.end_time}` : `${element.code === 'SY' ? 'NO WORK SCHEDULE' : 'RESTDAY/HOLIDAY'}`,
            value: element.id
        }))

        // Store fetched schedules
        schedules.value = scheduleResponse.schedules
    } else {
        tableText.value = 'Failed to load schedules.'
    }

    try {
        const configResponse = await fetch('/setup/config')
        const configData = await configResponse.json()
        defaultShiftCodes.value = Array.isArray(configData?.default_shift_codes)
            ? configData.default_shift_codes.map(entry => entry.code.trim()).filter(code => code !== '')
            : []
    } catch {
        defaultShiftCodes.value = []
    }

    isLoading.value = false
}


const handleDefaultShiftFill = (event, schedule) => {
    if (defaultShiftCodes.value.length === 0) {
        toast('Default shift codes are not configured. Please contact your administrator.', 'error')
        event.target.checked = false
        return
    }

    if (defaultShiftCodes.value.length !== schedule.length) {
        toast(`Default shift codes (${defaultShiftCodes.value.length}) do not match schedule days (${schedule.length}). Please contact your administrator.`, 'error')
        event.target.checked = false
        return
    }

    if (event.target.checked) {
        let default_shiftcodes_id = defaultShiftCodes.value.map(code => {
            let match = shifts.value.find(shift => (shift.label).includes(code))
            if (!match) {
                toast(`Shift code "${code}" not found in available shifts.`, 'warning')
            }
            return match ? match.value : null
        })

        for (let j = 0; j < schedule.length; j++) {
            schedule[j].shift_code = default_shiftcodes_id[j]
        }

    } else {
        for (let j = 0; j < schedule.length; j++) {
            schedule[j].shift_code = null
        }
    }
}

const isDefaultShift = (schedule) => {
    if (defaultShiftCodes.value.length === 0 || defaultShiftCodes.value.length !== schedule.length) {
        return false
    }

    if (initshifts.value?.data?.length > 0) {
        // Compare schedule's shift labels with default codes
        return schedule.every((day, idx) => {
            let match = initshifts.value.data.find(shift => shift.id === day.shift_code)
            return match ? match.code === defaultShiftCodes.value[idx] : false
        })
    }

    return false
}

</script>