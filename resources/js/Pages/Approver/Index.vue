<template>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold">Overtime Dashboard</h1>
                <p class="text-xs text-base-content/40">Manage and track overtime requests</p>
            </div>

            <!-- Global period selector -->
            <div class="flex items-center bg-base-200 rounded-xl px-1 py-1 gap-0.5">
                <select v-model="selectedYear" @change="handleWeekSelection()"
                    class="select select-ghost select-sm bg-transparent font-medium focus:outline-none border-none min-w-28">
                    <option v-for="y in years" :key="y.value" :value="y.value">{{ y.label }}</option>
                </select>
                <div class="w-px h-5 bg-base-300"></div>
                <select v-model="selectedWeek" @change="handleWeekSelection()"
                    class="select select-ghost select-sm bg-transparent font-medium focus:outline-none border-none min-w-36">
                    <option v-for="w in weeks.filter(w => w.value)" :key="w.value" :value="w.value">{{ w.label }}
                    </option>
                </select>
            </div>
        </div>
        <div class="flex flex-col gap-6">
            <!-- Stat Cards -->
            <div class="stats stats-horizontal shadow-xs flex-wrap">
                <Card title="Total Filed" :value="card.total_filed" routename="overtime.filed"
                    :parameters="{ status: 'FILED', page: 'Approver/Filed', week: selectedWeek, year: selectedYear }" />
                <Card title="For Filing" :value="card.total_approved" routename="overtime.filing"
                    :parameters="{ status: 'APPROVED', page: 'Approver/Filing', week: selectedWeek, year: selectedYear }" />
                <Card title="Pending Approvals" :value="card.total_pending" routename="overtime.pending"
                    :parameters="{ status: 'PENDING', page: 'Approver/Pending', week: selectedWeek, year: selectedYear }"
                    :description="card.total_pending > 0 ? 'For approval waiting' : ''" />
                <Card title="Total Requests" :value="card.total_requests" />
                <Card title="Weekly Overtime Limit Left" :value="((card.required_hours - card.total_hours) % 1 === 0
                    ? (card.required_hours - card.total_hours).toFixed(0)
                    : (card.required_hours - card.total_hours).toFixed(2)) + ' hr(s)'"
                    :description="(card.required_hours - card.total_hours) <= 0
                        ? 'No overtime limit hours left' : (card.required_hours - card.total_hours) <= 10 ? `Only ${(card.required_hours - card.total_hours).toFixed(0)} hr(s) left` : ''" />
            </div>

            <!-- Weekly Overview -->
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1 card bg-base-100 shadow-xs">
                    <div class="card-body">
                        <h2 class="card-title">Week {{ selectedWeek }} Overtime Overview</h2>
                        <p>
                            In Week {{ selectedWeek }}, a total of {{ card.total_requests }} overtime requests were
                            submitted,
                            with {{ card.total_approved }} approved and {{ card.total_pending }} still pending review.
                            Employees filed for {{ card.total_filed }} hours, contributing {{ card.total_hours }} actual
                            hours of overtime.
                            The overtime limit for the week was {{ card.required_hours }} hours.
                        </p>
                    </div>
                </div>

                <!-- Weekly Hours Progress -->
                <div class="col-span-1 card bg-base-100 shadow-xs">
                    <div class="card-body">
                        <h2 class="card-title">Weekly Overtime Usage</h2>
                        <progress class="progress progress-primary w-full" :value="card.total_hours ?? 0"
                            :max="card.required_hours ?? 0">
                        </progress>

                        <p class="text-sm text-right mt-1">
                            {{ (card.total_hours ?? 0) }} / {{ card.required_hours }} hrs consumed
                        </p>

                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2 flex flex-col gap-4 p-4 bg-base-100 card shadow-xs">
                    <div ref="overtimeWeeklyBarGraph" class="h-[45vh] w-full"></div>
                </div>

                <div class="col-span-1 card bg-base-100 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-base-200 flex items-center justify-between">
                        <h2 class="text-sm font-semibold tracking-tight">
                            Recent Activities
                        </h2>
                    </div>
                    <div class="overflow-y-auto max-h-[45vh] p-2">
                        <div v-if="recentRequests.length === 0" class="flex items-center justify-center h-full py-8">
                            <p class="text-xs text-base-content/40">No Recent Activities</p>
                        </div>
                        <div v-for="request in recentRequests" :key="request.id" :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg border border-transparent',
                            request.status === 'CANCELED' ? 'opacity-50' : ''
                        ]">
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center overflow-hidden bg-base-200 border-2 flex-shrink-0 transition-colors duration-300 border-base-300">
                                <img v-if="request.avatar_url" :src="request.avatar_url" alt="Avatar"
                                    class="w-full h-full object-cover" />
                                <span v-else
                                    class="text-xs font-semibold text-base-content/60">{{ getInitials(request.user_name) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium truncate">{{ request.user_name }}</p>
                                    <span :class="[
                                        'badge badge-xs font-medium flex-shrink-0',
                                        request.status === 'APPROVED' ? 'badge-success' :
                                            request.status === 'PENDING' ? 'badge-warning' :
                                                request.status === 'FILED' ? 'badge-info' :
                                                    request.status === 'DECLINED' || request.status === 'DISAPPROVED' || request.status === 'CANCELED' ? 'badge-error' :
                                                        'badge-ghost'
                                    ]">{{ request.status }}</span>
                                </div>
                                <p class="text-xs text-base-content/50 mt-0.5">
                                    {{ request.date }} &middot; {{ request.shift_code }} &middot;
                                    {{ request.hours }}hrs
                                </p>
                                <p class="text-xs text-base-content/40 mt-0.5 truncate">
                                    {{ request.reason }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, watch, onMounted, computed } from 'vue'
import Card from '../Components/Card.vue'
import { weeks, years } from '../utils/dropdownOptions.js'
import { router } from '@inertiajs/vue3'
import * as echarts from 'echarts'
import { theme } from '../utils/themeStore.js'
import { getInitials } from '../utils/nameHelper.js'
import { Icon } from "@iconify/vue"

// ===== constant variables =====

const props = defineProps({
    info: Object,
    success: Boolean,
    message: String
})

const card = ref({
    total_requests: props.info?.result?.totals.TOTAL_REQUESTS ?? 0,
    total_approved: props.info?.result?.totals.APPROVED ?? 0,
    total_pending: props.info?.result?.totals.PENDING ?? 0,
    total_filed: props.info?.result?.totals.FILED ?? 0,
    required_hours: props?.info?.result?.totals?.REQUIRED_HOURS ?? 0,
    total_hours: props?.info?.result?.totals?.TOTAL_HOURS ?? 0,
})

const selectedWeek = ref(props?.info?.payload?.week)
const selectedYear = ref(props?.info?.payload?.year)
const barData = ref([...props?.info?.result?.breakdown] ?? [])
const recentRequests = ref([...props.info?.recentRequests] ?? [])

// ===== Watchers =====

watch(() => props?.info?.result, (updatedTotals) => {

    barData.value = updatedTotals.breakdown
    displayOvertimeWeeklyBarGraph()

    card.value = {
        total_requests: updatedTotals.totals.TOTAL_REQUESTS ?? 0,
        total_approved: updatedTotals.totals.APPROVED ?? 0,
        total_pending: updatedTotals.totals.PENDING ?? 0,
        total_filed: updatedTotals.totals.FILED ?? 0,
        required_hours: updatedTotals.totals?.REQUIRED_HOURS ?? 0,
        total_hours: updatedTotals.totals?.TOTAL_HOURS ?? 0,
    }

})

watch(() => props.info?.recentRequests, (updated) => {
    recentRequests.value = [...updated]
})

watch(() => props.info.payload.week, (newWeek) => {
    selectedWeek.value = newWeek
})

watch(() => props.info.payload.year, (newYear) => {
    selectedYear.value = newYear
})



const handleWeekSelection = () => {
    router.get(route('main'), {
        year: selectedYear.value,
        week: selectedWeek.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

function getTailwindColor(className) {
    const div = document.createElement('div')
    div.className = className
    div.style.display = 'none'
    document.body.appendChild(div)
    const color = getComputedStyle(div).backgroundColor
    document.body.removeChild(div)
    return color
}





const overtimeWeeklyBarGraph = ref(null);
let BarchartInstance = null;

function displayOvertimeWeeklyBarGraph(currTheme = theme.value) {
    if (!overtimeWeeklyBarGraph.value) return

    if (BarchartInstance) {
        BarchartInstance.dispose()
    }
    if (currTheme === 'dark') {
        BarchartInstance = echarts.init(overtimeWeeklyBarGraph.value, 'dark')
    } else {
        BarchartInstance = echarts.init(overtimeWeeklyBarGraph.value)
    }


    let bgColor = getTailwindColor('bg-base-100')
    const option = {
        backgroundColor: bgColor,
        title: {
            text: 'Daily Overtime Breakdown'
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
        },
        legend: {
            top: 'bottom',
            data: barData.value.filter(s => s.name !== 'Total').map(s => s.name)
        },
        xAxis: {
            type: 'category',
            data: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        },
        yAxis: {
            type: 'value',
            name: 'Overtime Hours',
            min: 0
        },
        series: barData.value
    }
    BarchartInstance.setOption(option);
}


watch(theme, (newTheme) => {
    if (!newTheme) return
    displayOvertimeWeeklyBarGraph(newTheme)
}, { immediate: true })


onMounted(() => {
    displayOvertimeWeeklyBarGraph(theme.value)
})
</script>
