<template>

    <Head title="Manage Schedule" />
    <div class="flex flex-col gap-6">
        <Breadcrumbs :items="[
            { label: 'Home', route: 'main' },
            { label: 'Manage Schedule', route: 'schedule', active: true },
        ]" />
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-base-content">Manage Schedule</h1>
                <p class="text-base-content/60 text-sm mt-1">Set your weekly shift assignments</p>
            </div>
            <div class="flex items-center bg-base-200 rounded-xl px-1 py-1 gap-0.5">
                <select v-model="selectedYear" @change="loadMonthData()"
                    class="select select-ghost select-sm bg-transparent font-medium focus:outline-none border-none min-w-28">
                    <option v-for="y in years" :key="y.value" :value="y.value">{{ y.label }}</option>
                </select>
                <div class="w-px h-5 bg-base-300"></div>
                <select v-model="selectedMonth" @change="loadMonthData()"
                    class="select select-ghost select-sm bg-transparent font-medium focus:outline-none border-none min-w-36">
                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto bg-base-100 p-4 rounded max-w-7xl mx-auto w-full">
            <table class="table min-h-96 w-full text-lg">
                <thead class="bg-base-200 rounded">
                    <tr>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Week</th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider whitespace-nowrap">
                            Date Range</th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">Sun
                        </th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">Mon
                        </th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">Tue
                        </th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">Wed
                        </th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">Thu
                        </th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">Fri
                        </th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider text-center">Sat
                        </th>
                        <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="isLoading">
                        <td colspan="10" class="text-center italic text-base-content/40 py-4">
                            <span class="loading loading-spinner"></span> Loading Schedules...
                        </td>
                    </tr>

                    <template v-else-if="weeklySchedules.length > 0">
                        <tr v-for="(week, weekIndex) in weeklySchedules" :key="week.weekNumber" class="text-sm">
                            <td class="font-semibold text-center whitespace-nowrap">W{{ week.weekNumber }}</td>
                            <td class="whitespace-nowrap text-xs text-base-content/60">{{ week.startDate }} — {{
                                week.endDate }}</td>
                            <td v-for="day in week.schedules" :key="day.date">
                                <SelectOption :options="shifts" v-model="day.shift_code" margin="" size="select-xs" />
                            </td>
                            <td>
                                <div class="flex flex-col items-center gap-2">
                                    <label v-if="defaultShiftCodes.length > 0" class="label tooltip tooltip-left"
                                        data-tip="Default Shift">
                                        <input type="checkbox" class="checkbox checkbox-primary checkbox-xs"
                                            :checked="isDefaultShift(week.schedules)"
                                            :disabled="isLoading || isSubmitting"
                                            @change="handleDefaultShiftFill($event, weekIndex)" />
                                    </label>
                                    <div class="tooltip tooltip-left tooltip-error" data-tip="Remove Week">
                                        <Icon icon="gg:remove" width="20" height="20" @click="removeWeek(weekIndex)"
                                            class="hover:bg-error hover:cursor-pointer rounded-full" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr v-else>
                        <td colspan="10" class="text-center italic text-base-content/40 py-4">
                            {{ tableText }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="divider"></div>
            <div class="flex justify-between items-center">
                <span v-if="defaultShiftCodes.length > 0" class="text-xs text-base-content/40 italic">
                    *Check "Default" to auto-fill shifts. Uncheck to erase.
                </span>
                <span v-else></span>
                <button type="submit" class="btn btn-neutral btn-sm rounded-lg" @click="submitForm()"
                    :disabled="isSubmitting || isLoading || weeklySchedules.length === 0">
                    <span v-if="isSubmitting" class="loading loading-spinner"></span>
                    <span>Submit Schedule</span>
                </button>
            </div>
        </div>

    </div>
</template>
<script setup>
import SelectOption from '../Components/SelectOption.vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import { onMounted, ref, inject } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { years, months, getWeeksInMonth } from '../utils/dropdownOptions.js'
import { fetchShiftList } from '../api/shift.js'
import { fetchSchedule, submitSchedule } from '../api/schedule.js'

const page = usePage()
const toast = inject('toast')
const appConfig = inject('appConfig')

const selectedYear = ref(new Date().getFullYear())
const selectedMonth = ref(new Date().getMonth() + 1)

const isLoading = ref(false)
const isSubmitting = ref(false)
const initshifts = ref([])
const shifts = ref([])
const weeklySchedules = ref([])
const tableText = ref('No registered Schedule.')
const defaultShiftCodes = ref([])


const submitForm = async () => {
    isSubmitting.value = true
    const allSchedules = weeklySchedules.value.flatMap(week => week.schedules)
    const submitResponse = await submitSchedule(allSchedules)
    if (submitResponse?.success) {
        toast(submitResponse?.message, 'success')
    } else {
        toast(submitResponse?.message, 'error')
    }
    isSubmitting.value = false
}

onMounted(() => {
    isLoading.value = true
    loadMonthData()
})

async function loadMonthData() {
    isLoading.value = true

    const weeksInMonth = getWeeksInMonth(selectedYear.value, selectedMonth.value)

    const shiftsResponse = await fetchShiftList('employee')
    initshifts.value = shiftsResponse

    const shiftData = shiftsResponse?.data ?? []
    const to12hr = (t) => {
        const [h, m] = t.split(':').map(Number)
        const period = h >= 12 ? 'PM' : 'AM'
        const hour = h % 12 || 12
        return `${hour}:${String(m).padStart(2, '0')} ${period}`
    }
    shifts.value = shiftData.map(element => ({
        label: element.code,
        value: element.id
    }))

    const schedulePromises = weeksInMonth.map(w =>
        fetchSchedule(selectedYear.value, w.weekNumber)
    )
    const results = await Promise.all(schedulePromises)

    weeklySchedules.value = weeksInMonth.map((weekInfo, idx) => ({
        ...weekInfo,
        schedules: results[idx]?.success ? results[idx].schedules : []
    }))

    defaultShiftCodes.value = Array.isArray(appConfig.value?.default_shift_codes)
        ? appConfig.value.default_shift_codes.map(entry => entry.code.trim()).filter(code => code !== '')
        : []

    isLoading.value = false
}


function removeWeek(index) {
    weeklySchedules.value.splice(index, 1)
}

const handleDefaultShiftFill = (event, weekIndex) => {
    const schedule = weeklySchedules.value[weekIndex].schedules

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
        return schedule.every((day, idx) => {
            let match = initshifts.value.data.find(shift => shift.id === day.shift_code)
            return match ? match.code === defaultShiftCodes.value[idx] : false
        })
    }

    return false
}

</script>
