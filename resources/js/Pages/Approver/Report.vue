<template>

    <Head title="Report Generator" />
    <div class="py-6 px-4">

        <div v-if="reportLoaded" class="space-y-6 w-full">

            <!-- Filters -->
            <div class="flex flex-row gap-4 justify-end">
                <TextInput type="date" v-model="selectedDateRange.start_date"
                    :message="selectedDateRange.errors?.start_date" :disabled="isRegenerating" />
                <TextInput type="date" v-model="selectedDateRange.end_date"
                    :message="selectedDateRange.errors?.end_date" :disabled="isRegenerating" />

                <div class="join w-full sm:w-auto">
                    <input class="join-item btn flex-1" type="radio" name="options" aria-label="Weekly" value="weekly"
                        v-model="selectedReportType" :disabled="isRegenerating" />
                    <input class="join-item btn flex-1" type="radio" name="options" aria-label="Monthly" value="monthly"
                        v-model="selectedReportType" :disabled="isRegenerating" />
                    <input class="join-item btn flex-1" type="radio" name="options" aria-label="Yearly" value="yearly"
                        v-model="selectedReportType" :disabled="isRegenerating" />
                </div>

                <button class="btn btn-primary btn-md w-full md:w-auto shadow-md" @click="handleRegenerateReport()"
                    :disabled="isRegenerating">
                    <span v-if="isRegenerating" class="loading loading-spinner loading-md"></span>
                    REGENERATE
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 w-full">

                <!-- Approved Overtime Hours -->
                <div class="stat bg-base-100 text-success-content rounded-lg shadow">
                    <div class="stat-title">Approved Overtime</div>
                    <div class="stat-value text-success">{{ card.filed }}h</div>
                    <div class="stat-desc">Filed Requests hours</div>
                </div>
                <!-- Tentative Overtime Hours (Approved + Pending) -->
                <div class="stat bg-base-100 text-info-content rounded-lg shadow">
                    <div class="stat-title">Tentative Overtime</div>
                    <div class="stat-value text-info">{{ card.tentative }}h</div>
                    <div class="stat-desc">Filed + Approved + Pending hours</div>
                </div>

                <!-- Total Filed Requests -->
                <div class="stat bg-base-100 text-primary-content rounded-lg shadow">
                    <div class="stat-title">Total Overtime Requests</div>
                    <div class="stat-value text-primary">{{ card.requests }}</div>
                    <div class="stat-desc">All requests from employees</div>
                </div>

                <!-- Pending Requests -->
                <div class="stat bg-base-100 text-warning-content rounded-lg shadow">
                    <div class="stat-title">Pending Requests</div>
                    <div class="stat-value text-warning">{{ card.pending }}</div>
                    <div class="stat-desc">Waiting for approval</div>
                </div>
            </div>


            <!-- Graphs -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                <div class="col-span-2 card bg-base-100 shadow p-4 h-full">
                    <h2 class="font-bold mb-2">Consumed Overtime</h2>
                    <div class="h-full flex items-center justify-center text-gray-400">
                        <div ref="totalOvertimeViaTimeGraph" class="min-h-[50vh] w-full"></div>
                    </div>
                </div>
                <div class="col-span-1 card bg-base-100 shadow p-4 h-full">
                    <h2 class="font-bold mb-2">Employee Rankings</h2>
                    <div class="h-full flex items-center justify-center text-gray-400">
                        <div ref="totalOvertimeViaEmployeeChart" class="min-h-[50vh] w-full"></div>
                    </div>
                </div>
            </div>

            <!-- AI Summary -->
            <div class="card bg-base-100 shadow p-4 min-h-40 max-h-full">
                <h2 class="font-bold mb-2">AI-Generated Summary</h2>

                <div ref="aiContainer" class="h-full overflow-y-visible">
                    <div v-if="AIresponse === ''" class="flex items-center justify-center h-full text-gray-400">
                        <button class="btn btn-primary" @click="handleAnalyzeAI" :disabled="analyzingAI">
                            <template v-if="analyzingAI">
                                Generating
                                <span class="loading loading-dots loading-xl ml-2"></span>
                            </template>
                            <template v-else>
                                Generate
                                <Icon icon="mingcute:ai-line" width="24" height="24" />
                            </template>
                        </button>

                    </div>
                    <VueMarkdown v-else :source="AIresponse" class="prose prose-slate max-w-none text-sm" />
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center min-h-[80vh] bg-base-300 px-6 py-10">
            <div class="card bg-base-100 shadow-sm rounded-md w-full max-w-5xl overflow-visible border border-base-200">
                <div class="grid md:grid-cols-2 gap-8">
                    <figure class="flex items-center justify-center bg-base-200 p-8 rounded-l-3xl">
                        <img :src="reportImage" alt="report"
                            class="object-contain w-full h-full max-h-[15rem] drop-shadow-md" />
                    </figure>
                    <div class="card-body flex flex-col justify-center">
                        <h2 class="text-3xl font-extrabold text-primary tracking-tight">Generate Report</h2>
                        <p class="text-base text-gray-500 mt-2">Choose a date range to generate your report.</p>

                        <div v-if="isLoading" class="flex items-center gap-3 mt-4 text-primary">
                            <span class="loading loading-spinner loading-md"></span>
                            <span class="font-medium">{{ loadingMessage }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            <TextInput name="Start Date:" type="date" v-model="selectedDateRange.start_date"
                                :message="selectedDateRange.errors?.start_date" />
                            <TextInput name="End Date:" type="date" v-model="selectedDateRange.end_date"
                                :message="selectedDateRange.errors?.end_date" />
                        </div>

                        <div class="card-actions justify-end mt-8 gap-3 flex-wrap">
                            <button class="btn btn-neutral w-full md:w-auto shadow-sm" @click="handleClearState"
                                :disabled="isLoading">
                                RESET
                            </button>
                            <button class="btn btn-primary w-full md:w-auto shadow-md" @click="handleGenerateReport"
                                :disabled="isLoading">
                                GENERATE
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>


<script setup>
import { watch, ref, nextTick, reactive, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import reportImage from '../../images/generate-report.svg'
import TextInput from '../Components/TextInput.vue'
import { theme } from '../utils/themeStore.js'
import { getTailwindColor } from '../utils/tailwindColorIdentifier.js'
import * as echarts from 'echarts'
import { Icon } from "@iconify/vue"
import { analyzeWithAI } from "../services/openai.js"
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
                name: 'ROA',
                type: 'line',
                smooth: true,
                data: data.map(d => d.roa)
            },
            {
                name: 'Planned ROA',
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

        // ---- ROA by week ----
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

        // ---- ROA by date (month-year) ----
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

        // ---- ROA by date (year) ----
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

    if (AIresponse.value) {
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: "smooth"
        })
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