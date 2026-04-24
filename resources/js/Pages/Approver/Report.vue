<template>
    <Head title="Report Generator" />

    <div class="flex flex-col gap-6">
        <Breadcrumbs :items="[
            { label: 'Dashboard', route: 'main' },
            { label: 'Generate Report', route: 'approver.generate.report', active: true },
        ]" />

        <div v-if="reportLoaded" class="animate-in fade-in duration-500 flex flex-col gap-6">

            <!-- Page Header (title only, no filters) -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight">Overtime Analysis</h1>
                    <p class="text-sm text-base-content/50 mt-0.5">Aggregated employee overtime data</p>
                </div>
                <div class="badge badge-success badge-outline gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>
                    Live Data
                </div>
            </div>

            <!-- Sticky Filter Bar -->
            <div class="card bg-base-100 border border-base-200 shadow-sm sticky top-4 z-10">
                <div class="card-body px-5 py-3 flex-row flex-wrap items-center gap-4">

                    <!-- Date Range -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40">From</span>
                        <TextInput
                            type="date"
                            v-model="selectedDateRange.start_date"
                            margin=""
                            class="input-sm input-bordered w-36"
                            :disabled="isRegenerating"
                        />
                        <span class="text-base-content/30">→</span>
                        <TextInput
                            type="date"
                            v-model="selectedDateRange.end_date"
                            margin=""
                            class="input-sm input-bordered w-36"
                            :disabled="isRegenerating"
                        />
                    </div>

                    <div class="divider divider-horizontal mx-0 hidden md:flex"></div>

                    <!-- View Toggle -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-base-content/40">View</span>
                        <div class="join">
                            <input class="join-item btn btn-xs no-animation" type="radio"
                                aria-label="Weekly" value="weekly" v-model="selectedReportType" :disabled="isRegenerating" />
                            <input class="join-item btn btn-xs no-animation" type="radio"
                                aria-label="Monthly" value="monthly" v-model="selectedReportType" :disabled="isRegenerating" />
                            <input class="join-item btn btn-xs no-animation" type="radio"
                                aria-label="Yearly" value="yearly" v-model="selectedReportType" :disabled="isRegenerating" />
                        </div>
                    </div>

                    <!-- Spacer -->
                    <div class="flex-1"></div>

                    <!-- Regenerate -->
                    <button class="btn btn-primary btn-sm gap-2" @click="handleRegenerateReport()" :disabled="isRegenerating">
                        <span v-if="isRegenerating" class="loading loading-spinner loading-xs"></span>
                        <Icon v-else icon="lucide:refresh-cw" class="w-3.5 h-3.5" />
                        Regenerate
                    </button>

                </div>
            </div>

            <!-- Stat Cards -->
            <div class="stats stats-horizontal shadow-xs flex-wrap">
                <Card title="Approved" :value="card.filed + 'h'" description="Confirmed OT hours" />
                <Card title="Tentative" :value="card.tentative + 'h'" description="Pending + Approved" />
                <Card title="Total Requests" :value="card.requests" description="Total filings received" />
                <Card title="Pending" :value="card.pending" description="Awaiting action" />
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
                    <div class="card-body p-0 !gap-0">
                        <div class="px-6 py-4 border-b border-base-200 flex justify-between items-center">
                            <h2 class="text-xs font-semibold uppercase tracking-widest text-base-content/40">Consumed Overtime Trends</h2>
                        </div>
                        <div class="p-4">
                            <div ref="totalOvertimeViaTimeGraph" class="min-h-[400px] w-full"></div>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
                    <div class="card-body p-0 !gap-0">
                        <div class="px-6 py-4 border-b border-base-200">
                            <h2 class="text-xs font-semibold uppercase tracking-widest text-base-content/40">Employee Rankings</h2>
                        </div>
                        <div class="p-4">
                            <div ref="totalOvertimeViaEmployeeChart" class="min-h-[400px] w-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Insight Engine -->
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <Icon icon="mingcute:ai-line" class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold tracking-tight italic">AI Insight Engine</h2>
                            <p class="text-xs text-base-content/50 mt-0.5">Powered by Claude</p>
                        </div>
                    </div>

                    <div ref="aiContainer" class="min-h-32">
                        <div v-if="AIresponse === ''" class="flex flex-col items-center justify-center py-10 gap-4 rounded-xl border border-dashed border-base-300">
                            <p class="text-sm text-base-content/50 max-w-sm text-center leading-relaxed">
                                Let AI analyze the trends, identify outliers, and suggest resource optimizations based on this period's data.
                            </p>
                            <button class="btn btn-primary btn-sm gap-2" @click="handleAnalyzeAI" :disabled="analyzingAI">
                                <span v-if="analyzingAI" class="loading loading-dots loading-sm"></span>
                                <template v-else>
                                    Generate Insights
                                    <Icon icon="lucide:sparkles" class="w-3.5 h-3.5" />
                                </template>
                            </button>
                        </div>

                        <div v-else class="bg-base-200/40 rounded-xl p-6 border border-base-200">
                            <VueMarkdown :source="AIresponse" class="prose prose-slate max-w-none prose-headings:text-primary prose-sm" />
                            <div class="flex justify-end mt-4 pt-4 border-t border-base-200">
                                <button class="btn btn-ghost btn-xs gap-2" @click="handleAnalyzeAI" :disabled="analyzingAI">
                                    <span v-if="analyzingAI" class="loading loading-spinner loading-xs"></span>
                                    <template v-else>
                                        <Icon icon="mingcute:ai-line" class="w-3.5 h-3.5" />
                                        Regenerate Insights
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Empty State -->
        <div v-else class="flex items-center justify-center min-h-[70vh] p-4">
            <div class="card lg:card-side bg-base-100 shadow-sm max-w-5xl border border-base-200">
                <figure class="lg:w-1/2 bg-base-200/50 p-12">
                    <img :src="reportImage" alt="Report Illustration" class="w-full h-auto drop-shadow-xl animate-float" />
                </figure>
                <div class="card-body lg:w-1/2 justify-center p-8 lg:p-12 gap-6">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight mb-2">Ready to analyze?</h2>
                        <p class="text-base-content/50 text-sm leading-relaxed">Select a timeframe to aggregate employee overtime data and generate executive summaries.</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="form-control gap-1.5">
                            <label class="label py-0">
                                <span class="label-text text-xs font-semibold uppercase tracking-widest text-base-content/40">Start Date</span>
                            </label>
                            <TextInput type="date" v-model="selectedDateRange.start_date"
                                :message="selectedDateRange.errors?.start_date" class="input-bordered" />
                        </div>
                        <div class="form-control gap-1.5">
                            <label class="label py-0">
                                <span class="label-text text-xs font-semibold uppercase tracking-widest text-base-content/40">End Date</span>
                            </label>
                            <TextInput type="date" v-model="selectedDateRange.end_date"
                                :message="selectedDateRange.errors?.end_date" class="input-bordered" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 mt-2">
                        <button class="btn btn-primary btn-block" @click="handleGenerateReport" :disabled="isLoading">
                            <span v-if="isLoading" class="loading loading-spinner loading-sm"></span>
                            Generate Report
                        </button>
                        <button class="btn btn-ghost btn-block btn-sm" @click="handleClearState" :disabled="isLoading">
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<style scoped>
.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {

    0%,
    100% {
        transform: translateY(0px);
    }

    50% {
        transform: translateY(-20px);
    }
}
</style>


<script setup>
import { watch, ref, nextTick, reactive, computed } from 'vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import Card from '../Components/Card.vue'
import { useForm, Link } from '@inertiajs/vue3'
import reportImage from '../../images/generate-report.svg'
import TextInput from '../Components/TextInput.vue'
import { theme } from '../utils/themeStore.js'
import { getTailwindColor } from '../utils/helpers/color.js'
import * as echarts from 'echarts'
import { Icon } from "@iconify/vue"
import { analyzeWithAI } from "../services/ai.js"
import VueMarkdown from 'vue-markdown-render'

const isLoading = ref(false)
const loadingMessage = ref('Processing request...')
const reportLoaded = ref(false)
const selectedReportType = ref('weekly')
const apiResponseData = ref({})
const totalOvertimeViaTime = ref({})
const totalOvertimeViaEmployee = ref({})
const card = ref({
    filed: 0,
    pending: 0,
    tentative: 0,
    requests: 0
})

const analyzingAI = ref(false)
const AIresponse = ref("")
const isRegenerating = ref(false)


const aiContainer = ref(null)
const totalOvertimeViaTimeGraph = ref(null)
let totalOvertimeViaTimeGraphInstance = null

function rendertotalOvertimeViaTimeGraph(currTheme = theme.value) {
    if (!totalOvertimeViaTimeGraph.value) return

    if (totalOvertimeViaTimeGraphInstance) {
        totalOvertimeViaTimeGraphInstance.dispose()
    }

    if (currTheme === 'dark') {
        totalOvertimeViaTimeGraphInstance = echarts.init(totalOvertimeViaTimeGraph.value, 'dark')
    } else {
        totalOvertimeViaTimeGraphInstance = echarts.init(totalOvertimeViaTimeGraph.value)
    }

    let bgColor = getTailwindColor('bg-base-100')
    let type = selectedReportType.value
    let data = []

    if (type === 'weekly') {
        data = totalOvertimeViaTime.value.weeks.map((week, idx) => ({
            label: `Week ${week}`,
            sortKey: week,
            hours: totalOvertimeViaTime.value.totalHours[idx],
            roa: totalOvertimeViaTime.value.roa[idx],
            planned_roa: 108
        }))
        data.sort((a, b) => a.sortKey - b.sortKey)
    }
    else if (type === 'monthly') {
        data = totalOvertimeViaTime.value.months.map((month, idx) => ({
            label: month,
            sortKey: new Date(month).getTime(),
            hours: totalOvertimeViaTime.value.totalHours[idx],
            roa: totalOvertimeViaTime.value.roa[idx],
            planned_roa: 467.64
        }))
        data.sort((a, b) => a.sortKey - b.sortKey)
    }
    else if (type === 'yearly') {
        data = totalOvertimeViaTime.value.years.map((year, idx) => ({
            label: String(year),
            sortKey: year,
            hours: totalOvertimeViaTime.value.totalHours[idx],
            roa: totalOvertimeViaTime.value.roa[idx],
            planned_roa: 5600
        }))
        data.sort((a, b) => a.sortKey - b.sortKey)
    }

    const option = {
        backgroundColor: bgColor,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: [
            {
                type: 'category',
                data: data.map(d => d.label),
                axisTick: { alignWithLabel: true }
            }
        ],
        yAxis: [{ type: 'value' }],
        series: [
            {
                name: 'Total Hours',
                type: 'bar',
                barWidth: '60%',
                data: data.map(d => ({
                    value: d.hours,
                    itemStyle: {
                        color: d.hours > d.roa ? 'red' : '#5470c6'
                    }
                }))
            },
            {
                name: 'Weekly Limit',
                type: 'line',
                smooth: true,
                data: data.map(d => d.roa)
            },
            {
                name: 'Planned Limit',
                type: 'line',
                smooth: true,
                lineStyle: {
                    type: 'dashed'
                },
                data: data.map(d => d.planned_roa)
            }
        ]
    }
    totalOvertimeViaTimeGraphInstance.setOption(option)
}


const totalOvertimeViaEmployeeChart = ref(null)
let totalOvertimeViaEmployeeInstance = null

function rendertotalOvertimeViaEmployeeGraph(currTheme = theme.value) {
    if (!totalOvertimeViaEmployeeChart.value) return

    if (totalOvertimeViaEmployeeInstance) {
        totalOvertimeViaEmployeeInstance.dispose()
    }

    if (currTheme === 'dark') {
        totalOvertimeViaEmployeeInstance = echarts.init(totalOvertimeViaEmployeeChart.value, 'dark')
    } else {
        totalOvertimeViaEmployeeInstance = echarts.init(totalOvertimeViaEmployeeChart.value)
    }

    let bgColor = getTailwindColor('bg-base-100')

    // --- Sort employees by total hours (descending) ---
    let employees = totalOvertimeViaEmployee.value.employees.map((id, idx) => ({
        id,
        name: totalOvertimeViaEmployee.value.names[idx],
        hours: totalOvertimeViaEmployee.value.totalHours[idx]
    }))

    employees.sort((a, b) => b.hours - a.hours)

    const option = {
        backgroundColor: bgColor,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: { type: 'value' },
        yAxis: {
            type: 'category',
            data: employees.map(e => e.name),
            axisTick: { alignWithLabel: true },
            axisLabel: {
                formatter: function (value) {
                    return value.length > 4 ? value.slice(0, 4) + '…' : value
                }
            },
            inverse: true
        },
        series: [
            {
                name: 'Total Hours',
                type: 'bar',
                barWidth: '60%',
                data: employees.map(e => e.hours),
                label: {
                    show: true,
                    position: 'inside',
                    formatter: '{c}h',
                    color: '#fff',
                }
            }
        ]
    }

    totalOvertimeViaEmployeeInstance.setOption(option)
}

const props = defineProps({
    requests: Object,
    errors: Object,
    flash: Object,
    auth: Object,
})

const selectedDateRange = useForm({
    start_date: null,
    end_date: null,
    unit: props.auth?.user?.organization_unit_id ?? 0
})

const handleClearState = () => {
    selectedDateRange.start_date = ''
    selectedDateRange.end_date = ''
}

function handleDataManipulationViaReportType(data) {
    // ---- Cards computation ----
    card.value.filed = data.list.filter(req => req.status === 'FILED').reduce((sum, req) => sum + req.hours, 0)
    card.value.pending = data.list.filter(req => req.status === 'PENDING').length
    card.value.tentative = data.list.filter(req => ['PENDING', 'APPROVED', 'FILED'].includes(req.status)).reduce((sum, req) => sum + req.hours, 0)
    card.value.requests = data.list.filter(req => req.status !== 'CANCELED' && req.status !== 'DECLINED').length

    let type = selectedReportType.value

    let computedConsumedOvertime = {}
    let computedEmployeeRankings = {
        employees: [],
        names: [],
        totalHours: []
    }

    if (type === 'weekly') {
        // ---------------- Weekly ----------------
        computedConsumedOvertime.weeks = []
        computedConsumedOvertime.totalHours = []

        const overtimeRequestList = data.list.filter(req => req.status === 'FILED')

        overtimeRequestList.forEach(element => {
            if (!computedConsumedOvertime.weeks.includes(element.week)) {
                computedConsumedOvertime.weeks.push(element.week)
            }
            let weekIndex = computedConsumedOvertime.weeks.indexOf(element.week)
            if (computedConsumedOvertime.totalHours[weekIndex] === undefined) {
                computedConsumedOvertime.totalHours[weekIndex] = 0
            }
            computedConsumedOvertime.totalHours[weekIndex] += element.hours

            // employee ranking
            let idx = computedEmployeeRankings.employees.indexOf(element.user_id)
            if (idx === -1) {
                computedEmployeeRankings.employees.push(element.user_id)
                computedEmployeeRankings.names.push(element.user_name)
                computedEmployeeRankings.totalHours.push(0)
                idx = computedEmployeeRankings.employees.length - 1
            }
            computedEmployeeRankings.totalHours[idx] += element.hours
        })

        // ---- Weekly Limit by week ----
        computedConsumedOvertime.roa = computedConsumedOvertime.weeks.map(week => {
            let match = data.required_hours.find(e => e.week === week)
            return match ? match.required_hours : 0
        })
    }
    else if (type === 'monthly') {
        // ---------------- Monthly ----------------
        computedConsumedOvertime.months = []
        computedConsumedOvertime.totalHours = []

        const overtimeRequestList = data.list.filter(req => req.status === 'FILED')

        overtimeRequestList.forEach(element => {
            let month = new Date(element.date).toLocaleString('default', { month: 'short', year: 'numeric' })
            if (!computedConsumedOvertime.months.includes(month)) {
                computedConsumedOvertime.months.push(month)
            }
            let monthIndex = computedConsumedOvertime.months.indexOf(month)
            if (computedConsumedOvertime.totalHours[monthIndex] === undefined) {
                computedConsumedOvertime.totalHours[monthIndex] = 0
            }
            computedConsumedOvertime.totalHours[monthIndex] += element.hours

            // employee ranking
            let idx = computedEmployeeRankings.employees.indexOf(element.user_id)
            if (idx === -1) {
                computedEmployeeRankings.employees.push(element.user_id)
                computedEmployeeRankings.names.push(element.user_name)
                computedEmployeeRankings.totalHours.push(0)
                idx = computedEmployeeRankings.employees.length - 1
            }
            computedEmployeeRankings.totalHours[idx] += element.hours
        })

        // ---- Weekly Limit by date (month-year) ----
        computedConsumedOvertime.roa = computedConsumedOvertime.months.map(month => {
            let total = data.required_hours.reduce((sum, e) => {
                if (!e.date) return sum
                let d = new Date(e.date)
                if (isNaN(d)) return sum
                let monthLabel = d.toLocaleString('default', { month: 'short', year: 'numeric' })
                return monthLabel === month ? sum + e.required_hours : sum
            }, 0)
            return total
        })
    }
    else if (type === 'yearly') {
        // ---------------- Yearly ----------------
        computedConsumedOvertime.years = []
        computedConsumedOvertime.totalHours = []

        const overtimeRequestList = data.list.filter(req => req.status === 'FILED')

        overtimeRequestList.forEach(element => {
            let year = new Date(element.date).getFullYear()
            if (!computedConsumedOvertime.years.includes(year)) {
                computedConsumedOvertime.years.push(year)
            }
            let yearIndex = computedConsumedOvertime.years.indexOf(year)
            if (computedConsumedOvertime.totalHours[yearIndex] === undefined) {
                computedConsumedOvertime.totalHours[yearIndex] = 0
            }
            computedConsumedOvertime.totalHours[yearIndex] += element.hours

            // employee ranking
            let idx = computedEmployeeRankings.employees.indexOf(element.user_id)
            if (idx === -1) {
                computedEmployeeRankings.employees.push(element.user_id)
                computedEmployeeRankings.names.push(element.user_name)
                computedEmployeeRankings.totalHours.push(0)
                idx = computedEmployeeRankings.employees.length - 1
            }
            computedEmployeeRankings.totalHours[idx] += element.hours
        })

        // ---- Weekly Limit by date (year) ----
        computedConsumedOvertime.roa = computedConsumedOvertime.years.map(year => {
            let total = data.required_hours.reduce((sum, e) => {
                if (!e.date) return sum
                let d = new Date(e.date)
                if (isNaN(d)) return sum
                return d.getFullYear() === year ? sum + e.required_hours : sum
            }, 0)
            return total
        })
    }

    // ---- Assign final values ----
    totalOvertimeViaTime.value = computedConsumedOvertime
    totalOvertimeViaEmployee.value = computedEmployeeRankings

    nextTick(() => {
        rendertotalOvertimeViaTimeGraph()
        rendertotalOvertimeViaEmployeeGraph()
    })
}


const handleGenerateReport = () => {
    isLoading.value = true

    setTimeout(() => {
        loadingMessage.value = 'This may take a while depending on date range. Kindly wait...'
    }, 5000);

    selectedDateRange.get(route('approver.generate.report.daterange'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            reportLoaded.value = true
            apiResponseData.value = props?.requests
            handleDataManipulationViaReportType(props?.requests)
        },
        onError: (errors) => {
            console.log("Validation errors:", errors)
        },
        onFinish: () => {
            isLoading.value = false
        }
    })
}

const handleRegenerateReport = () => {
    isRegenerating.value = true
    AIresponse.value = ""

    selectedDateRange.get(route('approver.generate.report.daterange'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            apiResponseData.value = props?.requests
        },
        onError: (errors) => console.log(errors),
        onFinish: async () => {

            isRegenerating.value = false
            await nextTick()
            handleDataManipulationViaReportType(props?.requests)
        }
    })
}



const handleAnalyzeAI = async () => {
    analyzingAI.value = true
    AIresponse.value = ""

    let firstChunk = true

    const result = await analyzeWithAI(apiResponseData.value.list, (chunk) => {
        AIresponse.value += chunk
        if (firstChunk) {
            analyzingAI.value = false
            firstChunk = false
        }
    })

    if (!result.success) {
        AIresponse.value = "Error: " + result.data
        analyzingAI.value = false
    }
}


watch(AIresponse, async () => {
    await nextTick()

    if (AIresponse.value && aiContainer.value) {
        aiContainer.value.scrollIntoView({ behavior: "smooth", block: "end" })
    }

})

watch(theme, (newTheme) => {
    if (!newTheme) return
    rendertotalOvertimeViaTimeGraph(newTheme)
    rendertotalOvertimeViaEmployeeGraph(newTheme)
}, { immediate: true })


watch(selectedReportType, (newVal) => {
    // re-compute your data
    handleDataManipulationViaReportType(apiResponseData.value)

    // re-render the graph
    nextTick(() => {
        rendertotalOvertimeViaTimeGraph()
        rendertotalOvertimeViaEmployeeGraph()
    })
})
</script>

<style scoped>
.prose {
    font-family: ui-sans-serif, system-ui, sans-serif;
    line-height: 1;
}

.prose h1,
.prose h2,
.prose h3 {
    font-weight: 600;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.3em;
}

.prose ul {
    list-style-type: disc;
    padding-left: 1.5rem;
}

.prose ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
}

.prose blockquote {
    border-left: 4px solid #d1d5db;
    padding-left: 1rem;
    color: #6b7280;
    font-style: italic;
}
</style>