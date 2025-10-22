<template>
    <div class="flex flex-col gap-6 p-4 h-full">
        <!-- Stat Cards -->
        <div class="stats stats-horizontal shadow flex-wrap">
            <Card title="Total Filed" :value="card.total_filed" routename="overtime.filed"
                :parameters="{ status: 'FILED', page: 'Approver/Filed', week: selectedWeek, year: selectedYear }" />
            <Card title="For Filing" :value="card.total_approved" routename="overtime.filing"
                :parameters="{ status: 'APPROVED', page: 'Approver/Filing', week: selectedWeek, year: selectedYear }" />
            <Card title="Pending Approvals" :value="card.total_pending" routename="overtime.pending"
                :parameters="{ status: 'PENDING', page: 'Approver/Pending', week: selectedWeek, year: selectedYear }"
                :description="card.total_pending > 0 ? 'For approval waiting' : ''" />
            <Card title="Total Requests" :value="card.total_requests" />
            <Card title="ROA Hours Left" :value="((card.required_hours - card.total_hours) % 1 === 0
                ? (card.required_hours - card.total_hours).toFixed(0)
                : (card.required_hours - card.total_hours).toFixed(2)) + ' hr(s)'"
                :description="(card.required_hours - card.total_hours) <= 0
                    ? 'No ROA hours left' : (card.required_hours - card.total_hours) <= 10 ? `Only ${(card.required_hours - card.total_hours).toFixed(0)} hr(s) left` : ''" />
        </div>

        <!-- Weekly Overview -->
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-1 card bg-base-100 shadow">
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
            <div class="col-span-1 card bg-base-100 shadow">
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
        <div class="flex flex-col gap-4 p-4 bg-base-100 card shadow">
            <div class="flex justify-end w-full">
                <div class="flex flex-row gap-4 w-1/4">
                    <SelectOption :options="years" v-model="selectedYear" margin='' @change="handleWeekSelection()" />
                    <SelectOption :options="weeks" v-model="selectedWeek" margin='' @change="handleWeekSelection()" />
                </div>
            </div>
            <div class="w-full flex flex-row gap-4">
                <div ref="overtimeWeeklyBarGraph" class="min-h-[50vh] w-2/3"></div>
                <div ref="overtimeRequestStatus" class="min-h-[50vh] w-1/3"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, watch, onMounted, computed } from 'vue'
import Card from '../Components/Card.vue'
import SelectOption from '../Components/SelectOption.vue'
import { weeks, years } from '../utils/dropdownOptions.js'
import { router } from '@inertiajs/vue3'
import * as echarts from 'echarts'
import { theme } from '../utils/themeStore.js'

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
const pieData = ref([...props?.info?.result?.requests] ?? [])
const barData = ref([...props?.info?.result?.breakdown] ?? [])

// ===== Watchers =====

watch(() => props?.info?.result, (updatedTotals) => {

    // reinitialize the graph but update the pieData first
    pieData.value = updatedTotals.requests
    displayOvertimeRequestStatus()

    barData.value = updatedTotals.breakdown
    displayOvertimeWeeklyBarGraph()

    // update the value of each key in card (for reactivity)
    card.value = {
        total_requests: updatedTotals.totals.TOTAL_REQUESTS ?? 0,
        total_approved: updatedTotals.totals.APPROVED ?? 0,
        total_pending: updatedTotals.totals.PENDING ?? 0,
        total_filed: updatedTotals.totals.FILED ?? 0,
        required_hours: updatedTotals.totals?.REQUIRED_HOURS ?? 0,
        total_hours: updatedTotals.totals?.TOTAL_HOURS ?? 0,
    }

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


const overtimeRequestStatus = ref(null);
let PieChartInstance = null;

function displayOvertimeRequestStatus(currTheme = theme.value) {
    if (!overtimeRequestStatus.value) return

    if (PieChartInstance) {
        PieChartInstance.dispose()
    }

    if (currTheme === 'dark') {

        PieChartInstance = echarts.init(overtimeRequestStatus.value, 'dark')
    } else {

        PieChartInstance = echarts.init(overtimeRequestStatus.value)
    }


    let bgColor = getTailwindColor('bg-base-100')
    let option = {
        backgroundColor: bgColor,
        title: {
            text: 'Requests Status',
            left: 'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: function (params) {
                let extraInfo = ''
                if (Array.isArray(params.data.remarks)) {
                    extraInfo = params.data.remarks.map((item) => `• ${item}`).join('<br/>')
                } else {
                    extraInfo = params.data.remarks || ''
                }

                return `
        <strong>${params.name}</strong><br/>
        Count: ${params.value}<br/>
        Percentage: ${params.percent}%<br/>
        ${extraInfo ? '<br/><u>Remarks:</u><br/>' + extraInfo : ''}
      `
            }
        },
        legend: {
            top: 'bottom'
        },
        series: [
            {
                name: 'Status',
                type: 'pie',
                radius: '50%',
                data: pieData.value,
                label: {
                    show: true,
                    color: '#808080',
                    fontSize: 14
                }
            }
        ]
    }

    PieChartInstance.setOption(option);
}

watch(theme, (newTheme) => {
    if (!newTheme) return
    displayOvertimeWeeklyBarGraph(newTheme)
    displayOvertimeRequestStatus(newTheme)
}, { immediate: true })


onMounted(() => {
    displayOvertimeWeeklyBarGraph(theme.value)
    displayOvertimeRequestStatus(theme.value)

})
</script>