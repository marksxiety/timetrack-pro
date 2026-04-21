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

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            <!-- Schedule Card -->
            <div class="card bg-base-100 lg:col-span-4 h-full">
                <div class="card-body flex flex-col p-4 h-full">
                    <div class="overflow-auto flex-1">
                        <table class="table min-h-96 w-full text-lg">
                            <thead class="bg-base-200 rounded">
                                <tr>
                                    <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Week
                                    </th>
                                    <th
                                        class="text-xs font-medium text-base-content/50 uppercase tracking-wider whitespace-nowrap">
                                        Date Range</th>
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
                                    <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="isLoading">
                                    <td colspan="10" class="text-center italic text-base-content/40 py-4">
                                        <span class="loading loading-spinner"></span> Loading Schedules...
                                    </td>
                                </tr>

                                <template v-else-if="weeklySchedules.length > 0">
                                    <tr v-for="(week, weekIndex) in weeklySchedules" :key="week.weekNumber"
                                        class="text-sm">
                                        <td class="font-semibold text-center whitespace-nowrap">W{{ week.weekNumber }}
                                        </td>
                                        <td class="whitespace-nowrap text-xs text-base-content/60">{{ week.startDate }}
                                            — {{ week.endDate }}</td>
                                        <td v-for="day in week.schedules" :key="day.date">
                                            <SelectOption :options="shifts" v-model="day.shift_code" margin=""
                                                size="select-xs" />
                                        </td>
                                        <td>
                                            <div class="flex flex-col items-center gap-2">
                                                <label v-if="defaultShiftCodes.length > 0"
                                                    class="label tooltip tooltip-left" data-tip="Default Shift">
                                                    <input type="checkbox" class="checkbox checkbox-primary checkbox-xs"
                                                        :checked="isDefaultShift(week.schedules)"
                                                        :disabled="isLoading || isSubmitting"
                                                        @change="handleDefaultShiftFill($event, weekIndex)" />
                                                </label>
                                                <div class="tooltip tooltip-left tooltip-error" data-tip="Remove Week">
                                                    <Icon icon="gg:remove" width="20" height="20"
                                                        @click="removeWeek(weekIndex)"
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
                    </div>
                    <div class="divider my-2"></div>
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

            <!-- Shift Reference Card -->
            <div v-if="shiftReference.length > 0" class="card bg-base-100 h-full">
                <div class="card-body p-4 h-full">
                    <h3 class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-3">
                        Shift Reference
                    </h3>
                    <table class="table text-sm">
                        <thead class="bg-base-200 rounded">
                            <tr>
                                <th class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Code</th>
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
import SelectOption from '../Components/SelectOption.vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import { onMounted, ref, inject, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { years, months, getWeeksInMonth } from '../utils/dropdownOptions.js'
import { fetchSchedule, submitSchedule } from '../api/schedule.js'
import { useConfig } from '../utils/configStore.js'

const page = usePage()
const toast = inject('toast')
const { config, loadConfig } = useConfig()

const props = defineProps({
    shifts: {
        type: Array,
        default: () => []
    }
})

const selectedYear = ref(new Date().getFullYear())
const selectedMonth = ref(new Date().getMonth() + 1)

const isLoading = ref(false)
const isSubmitting = ref(false)
const shifts = ref([])
const weeklySchedules = ref([])
const tableText = ref('No registered Schedule.')
const defaultShiftCodes = ref([])

const to12hr = (t) => {
    if (!t) return null
    const [h, m] = t.split(':').map(Number)
    const period = h >= 12 ? 'PM' : 'AM'
    const hour = h % 12 || 12
    return `${hour}:${String(m).padStart(2, '0')} ${period}`
}

const shiftReference = computed(() => {
    return props.shifts.map(s => ({
        id: s.id,
        code: s.code,
        timeRange: s.start_time && s.end_time
            ? `${to12hr(s.start_time)} - ${to12hr(s.end_time)}`
            : 'N/A'
    }))
})


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

onMounted(async () => {
    isLoading.value = true
    await loadConfig()
    await loadMonthData()
})

async function loadMonthData() {
    isLoading.value = true

    const weeksInMonth = getWeeksInMonth(selectedYear.value, selectedMonth.value)

    shifts.value = props.shifts.map(element => ({
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

    defaultShiftCodes.value = Array.isArray(config.value?.default_shift_codes)
        ? config.value.default_shift_codes.map(entry => entry.code.trim()).filter(code => code !== '')
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

    if (props.shifts.length > 0) {
        return schedule.every((day, idx) => {
            let match = props.shifts.find(shift => shift.id === day.shift_code)
            return match ? match.code === defaultShiftCodes.value[idx] : false
        })
    }

    return false
}

</script>
