<template>

    <Head title="Report Generator" />

    <div class="flex flex-col gap-6">
        <Breadcrumbs :items="[
            { label: 'Dashboard', route: 'main' },
            { label: 'Generate Report', route: 'approver.generate.report', active: true },
        ]" />

        <div v-if="reportLoaded" class="animate-in fade-in duration-500 flex flex-col gap-6">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight">Overtime Analysis</h1>
                    <p class="text-sm text-base-content/50 mt-0.5">Aggregated employee overtime data</p>
                </div>
                <div class="badge bg-success/30 border border-success text-success">
                    <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>
                    Live Data
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="card bg-base-100 border border-base-200 shadow-sm flex-1 min-w-0">
                    <div class="card-body px-4 py-2.5 flex-row items-center gap-3">
                        <Icon icon="lucide:calendar-range" class="w-4 h-4 text-base-content/40 shrink-0" />
                        <span class="text-sm text-base-content/70 truncate">{{ displayDateRange }}</span>
                        <span class="text-base-content/20">·</span>
                        <span class="text-sm text-base-content/70">{{ displayViewType }}</span>
                    </div>
                </div>
                <button class="btn btn-outline btn-sm gap-2" @click="configModal?.open()"
                    :disabled="isRegenerating">
                    <Icon icon="lucide:settings-2" class="w-4 h-4" />
                    Configure
                </button>
                <button class="btn btn-primary btn-sm gap-2" @click="handleRegenerateReport()"
                    :disabled="isRegenerating">
                    <span v-if="isRegenerating" class="loading loading-spinner loading-xs"></span>
                    <Icon v-else icon="lucide:refresh-cw" class="w-3.5 h-3.5" />
                    Regenerate
                </button>
            </div>

            <Modal ref="configModal" title="Report Configuration">
                <div class="flex flex-col gap-5">
                    <div v-if="props.userRole === 'admin'" class="form-control gap-1.5">
                        <label class="label py-0">
                            <span class="label-text text-xs font-semibold uppercase tracking-widest text-base-content/40">Organization Unit</span>
                        </label>
                        <SelectOption :options="orgUnitOptions" v-model="selectedOrgUnit"
                            class="select-bordered" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
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

                    <div class="form-control flex flex-col gap-1.5">
                        <label class="label py-0">
                            <span class="label-text text-xs font-semibold uppercase tracking-widest text-base-content/40">View</span>
                        </label>
                        <div class="join">
                            <input class="join-item btn btn-sm no-animation flex-1" type="radio" aria-label="Weekly"
                                value="weekly" v-model="selectedReportType" :disabled="isRegenerating" />
                            <input class="join-item btn btn-sm no-animation flex-1" type="radio" aria-label="Monthly"
                                value="monthly" v-model="selectedReportType" :disabled="isRegenerating" />
                            <input class="join-item btn btn-sm no-animation flex-1" type="radio" aria-label="Yearly"
                                value="yearly" v-model="selectedReportType" :disabled="isRegenerating" />
                        </div>
                    </div>

                    <div class="divider my-0"></div>

                    <button class="btn btn-primary gap-2" @click="handleModalApply" :disabled="isRegenerating">
                        <span v-if="isRegenerating" class="loading loading-spinner loading-sm"></span>
                        <Icon v-else icon="lucide:check" class="w-4 h-4" />
                        Apply &amp; Regenerate
                    </button>
                </div>
            </Modal>

            <div class="stats stats-horizontal shadow-xs flex-wrap">
                <Card title="Approved" :value="report?.cards?.filed + 'h'" description="Confirmed OT hours" />
                <Card title="Tentative" :value="report?.cards?.tentative + 'h'" description="Pending + Approved" />
                <Card title="Total Requests" :value="report?.cards?.requests"
                    description="Total filings received" />
                <Card title="Pending" :value="report?.cards?.pending" description="Awaiting action" />
            </div>

            <Heatmap :data="report?.heatmap?.data ?? {}" :years="report?.heatmap?.years ?? []"
                :loading="isRegenerating" :show-settings="false" />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
                    <div class="card-body p-0 !gap-0">
                        <div class="px-6 py-4 border-b border-base-200 flex justify-between items-center">
                            <h2
                                class="text-xs font-semibold uppercase tracking-widest text-base-content/40">
                                Overtime Trends
                            </h2>
                        </div>
                        <div class="p-4">
                            <div ref="trendsChartRef" class="min-h-[400px] w-full"></div>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
                    <div class="card-body p-0 !gap-0">
                        <div class="px-6 py-4 border-b border-base-200">
                            <h2
                                class="text-xs font-semibold uppercase tracking-widest text-base-content/40">
                                Employee Rankings
                            </h2>
                        </div>
                        <div class="p-4">
                            <div ref="rankingsChartRef" class="min-h-[400px] w-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
                    <div class="card-body p-0 !gap-0">
                        <div class="px-6 py-4 border-b border-base-200">
                            <h2
                                class="text-xs font-semibold uppercase tracking-widest text-base-content/40">
                                Cumulative Overtime
                            </h2>
                        </div>
                        <div class="p-4">
                            <div ref="cumulativeChartRef" class="min-h-[350px] w-full"></div>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
                    <div class="card-body p-0 !gap-0">
                        <div class="px-6 py-4 border-b border-base-200">
                            <h2
                                class="text-xs font-semibold uppercase tracking-widest text-base-content/40">
                                OT Utilization
                            </h2>
                        </div>
                        <div class="p-4">
                            <div ref="gaugeChartRef" class="min-h-[350px] w-full"></div>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
                    <div class="card-body p-0 !gap-0">
                        <div class="px-6 py-4 border-b border-base-200">
                            <h2
                                class="text-xs font-semibold uppercase tracking-widest text-base-content/40">
                                Status Breakdown
                            </h2>
                        </div>
                        <div class="p-4">
                            <div ref="statusPieRef" class="min-h-[350px] w-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <Icon icon="mingcute:ai-line" class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold tracking-tight italic">AI Insight Engine</h2>
                            <p class="text-xs text-base-content/50 mt-0.5">Powered by {{ config?.ai_model
                                || 'AI' }}</p>
                        </div>
                    </div>

                    <div ref="aiContainer" class="min-h-32">
                        <div v-if="AIresponse === ''"
                            class="flex flex-col items-center justify-center py-10 gap-4 rounded-xl border border-dashed border-base-300">
                            <p class="text-sm text-base-content/50 max-w-sm text-center leading-relaxed">
                                Let AI analyze the trends, identify outliers, and suggest resource
                                optimizations based on this period's data.
                            </p>
                            <button class="btn btn-primary btn-sm gap-2" @click="handleAnalyzeAI"
                                :disabled="analyzingAI">
                                <span v-if="analyzingAI" class="loading loading-dots loading-sm"></span>
                                <template v-else>
                                    Generate Insights
                                    <Icon icon="lucide:sparkles" class="w-3.5 h-3.5" />
                                </template>
                            </button>
                        </div>

                        <div v-else class="bg-base-200/40 rounded-xl p-6 border border-base-200">
                            <VueMarkdown :source="AIresponse"
                                class="prose prose-slate max-w-none prose-headings:text-primary prose-sm" />
                            <div class="flex justify-end mt-4 pt-4 border-t border-base-200">
                                <button class="btn btn-ghost btn-xs gap-2" @click="handleAnalyzeAI"
                                    :disabled="analyzingAI">
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

        <div v-else class="flex items-center justify-center min-h-[70vh] p-4">
            <div class="flex flex-col lg:flex-row bg-base-100 shadow-sm max-w-5xl border border-base-200 rounded-box overflow-hidden">
                <div class="lg:w-1/2 bg-base-200/50 flex items-center justify-center p-12">
                    <img :src="reportImage" alt="Report Illustration"
                        class="w-full h-auto max-h-[50vh] object-contain drop-shadow-xl animate-float" />
                </div>
                <div class="lg:w-1/2 flex flex-col justify-center p-8 lg:p-12 gap-6">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight mb-2">Generate Report</h2>
                        <p class="text-base-content/50 text-sm leading-relaxed">Select a date range and organization unit to generate a comprehensive overtime report.</p>
                    </div>

                    <div v-if="props.userRole === 'admin'" class="form-control gap-1.5">
                        <label class="label py-0">
                            <span
                                class="label-text text-xs font-semibold uppercase tracking-widest text-base-content/40">Organization
                                Unit</span>
                        </label>
                        <SelectOption name="Org Unit" :options="orgUnitOptions" v-model="selectedOrgUnit"
                            class="select-bordered" />
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="form-control gap-1.5">
                            <label class="label py-0">
                                <span
                                    class="label-text text-xs font-semibold uppercase tracking-widest text-base-content/40">Start
                                    Date</span>
                            </label>
                            <TextInput type="date" v-model="selectedDateRange.start_date"
                                :message="selectedDateRange.errors?.start_date" class="input-bordered" />
                        </div>
                        <div class="form-control gap-1.5">
                            <label class="label py-0">
                                <span
                                    class="label-text text-xs font-semibold uppercase tracking-widest text-base-content/40">End
                                    Date</span>
                            </label>
                            <TextInput type="date" v-model="selectedDateRange.end_date"
                                :message="selectedDateRange.errors?.end_date" class="input-bordered" />
                        </div>
                    </div>

                    <div class="mt-2">
                        <button class="btn btn-primary btn-block" @click="handleGenerateReport"
                            :disabled="isLoading">
                            <span v-if="isLoading" class="loading loading-spinner loading-sm"></span>
                            Generate Report
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
import { watch, ref, nextTick, computed, onMounted, onBeforeUnmount } from 'vue'
import Breadcrumbs from '../Components/Breadcrumbs.vue'
import Card from '../Components/Card.vue'
import Heatmap from '../Components/Heatmap.vue'
import Modal from '../Components/Modal.vue'
import SelectOption from '../Components/SelectOption.vue'
import { useForm } from '@inertiajs/vue3'
import reportImage from '../../images/generate-report.svg'
import TextInput from '../Components/TextInput.vue'
import { theme } from '../utils/themeStore.js'
import { getTailwindColor } from '../utils/helpers/color.js'
import * as echarts from 'echarts'
import { Icon } from "@iconify/vue"
import { analyzeWithAI } from "../services/ai.js"
import VueMarkdown from 'vue-markdown-render'
import { useConfig } from '../utils/configStore.js'

const { config, loadConfig } = useConfig()

const props = defineProps({
    report: Object,
    organizationUnits: Array,
    userRole: String,
    errors: Object,
    flash: Object,
    auth: Object,
})

const isLoading = ref(false)
const reportLoaded = ref(false)
const selectedReportType = ref('weekly')
const selectedOrgUnit = ref('')
const analyzingAI = ref(false)
const AIresponse = ref("")
const isRegenerating = ref(false)
const configModal = ref(null)

const aiContainer = ref(null)

const trendsChartRef = ref(null)
const rankingsChartRef = ref(null)
const cumulativeChartRef = ref(null)
const gaugeChartRef = ref(null)
const statusPieRef = ref(null)

let trendsChartInstance = null
let rankingsChartInstance = null
let cumulativeChartInstance = null
let gaugeChartInstance = null
let statusPieChartInstance = null

onMounted(() => loadConfig())

onBeforeUnmount(() => {
    trendsChartInstance?.dispose()
    rankingsChartInstance?.dispose()
    cumulativeChartInstance?.dispose()
    gaugeChartInstance?.dispose()
    statusPieChartInstance?.dispose()
})

const orgUnitOptions = computed(() => {
    const options = [{ label: 'All Units', value: '' }]
    ;(props.organizationUnits ?? []).forEach(unit => {
        options.push({ label: unit.unit_path, value: unit.id })
    })
    return options
})

const selectedDateRange = useForm({
    start_date: null,
    end_date: null,
    organization_unit_id: '',
})

const handleModalApply = () => {
    configModal.value?.close()
    handleRegenerateReport()
}

const displayDateRange = computed(() => {
    const start = selectedDateRange.start_date
    const end = selectedDateRange.end_date
    if (start && end) return `${start} → ${end}`
    if (start) return `From ${start}`
    return 'No date range set'
})

const displayViewType = computed(() => {
    const map = { weekly: 'Weekly', monthly: 'Monthly', yearly: 'Yearly' }
    return map[selectedReportType.value] ?? 'Weekly'
})

function initChart(dom, instanceRef, key) {
    if (instanceRef) instanceRef.dispose()
    if (!dom) return null
    return echarts.init(dom, theme.value === 'dark' ? 'dark' : undefined)
}

function renderAllCharts() {
    renderTrendsChart()
    renderRankingsChart()
    renderCumulativeChart()
    renderGaugeChart()
    renderStatusPieChart()
}

function renderTrendsChart() {
    const dom = trendsChartRef.value
    if (!dom || !props.report?.trends) return

    trendsChartInstance = initChart(dom, trendsChartInstance)
    if (!trendsChartInstance) return

    const bgColor = getTailwindColor('bg-base-100')
    const view = props.report.trends[selectedReportType.value]
    if (!view) return

    const barColor = (val, roaIdx) => {
        const roa = view.roa[roaIdx]
        if (!roa) return '#5470c6'
        return val > roa ? '#ef4444' : '#5470c6'
    }

    const option = {
        backgroundColor: bgColor,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        legend: {
            data: ['Total Hours', 'Required Hours'],
            bottom: 0,
            textStyle: { fontSize: 11 },
        },
        xAxis: {
            type: 'category',
            data: view.labels,
            axisTick: { alignWithLabel: true },
        },
        yAxis: [{ type: 'value' }],
        series: [
            {
                name: 'Total Hours',
                type: 'bar',
                barWidth: '60%',
                data: view.hours.map((h, i) => ({
                    value: h,
                    itemStyle: { color: barColor(h, i) },
                })),
            },
            {
                name: 'Required Hours',
                type: 'line',
                smooth: true,
                data: view.roa,
            },
        ],
    }

    trendsChartInstance.setOption(option, true)
}

function renderRankingsChart() {
    const dom = rankingsChartRef.value
    if (!dom || !props.report?.rankings) return

    rankingsChartInstance = initChart(dom, rankingsChartInstance)
    if (!rankingsChartInstance) return

    const bgColor = getTailwindColor('bg-base-100')
    const { names, totalHours } = props.report.rankings

    const option = {
        backgroundColor: bgColor,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: { type: 'value' },
        yAxis: {
            type: 'category',
            data: names,
            axisTick: { alignWithLabel: true },
            axisLabel: {
                formatter: function (value) {
                    return value.length > 4 ? value.slice(0, 4) + '\u2026' : value
                },
            },
            inverse: true,
        },
        series: [
            {
                name: 'Total Hours',
                type: 'bar',
                barWidth: '60%',
                data: totalHours,
                label: {
                    show: true,
                    position: 'inside',
                    formatter: '{c}h',
                    color: '#fff',
                },
            },
        ],
    }

    rankingsChartInstance.setOption(option, true)
}

function renderCumulativeChart() {
    const dom = cumulativeChartRef.value
    if (!dom || !props.report?.cumulative) return

    cumulativeChartInstance = initChart(dom, cumulativeChartInstance)
    if (!cumulativeChartInstance) return

    const bgColor = getTailwindColor('bg-base-100')
    const { dates, values } = props.report.cumulative

    const option = {
        backgroundColor: bgColor,
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'cross' },
            formatter: function (params) {
                const d = params[0]
                return `${d.axisValue}<br/><strong>${d.value}h</strong> cumulative`
            },
        },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: {
            type: 'category',
            data: dates,
            axisLabel: {
                rotate: 45,
                fontSize: 10,
                formatter: function (val) {
                    return val.slice(5)
                },
            },
        },
        yAxis: { type: 'value', name: 'Hours' },
        series: [
            {
                name: 'Cumulative OT',
                type: 'line',
                smooth: true,
                symbol: 'none',
                lineStyle: { width: 2.5 },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(84, 112, 198, 0.35)' },
                        { offset: 1, color: 'rgba(84, 112, 198, 0.05)' },
                    ]),
                },
                data: values,
            },
        ],
    }

    cumulativeChartInstance.setOption(option, true)
}

function renderGaugeChart() {
    const dom = gaugeChartRef.value
    if (!dom || !props.report?.gauge) return

    gaugeChartInstance = initChart(dom, gaugeChartInstance)
    if (!gaugeChartInstance) return

    const bgColor = getTailwindColor('bg-base-100')
    const { filed_hours, required_hours } = props.report.gauge

    const maxVal = Math.max(required_hours, filed_hours * 1.2, 1)
    const percentage = required_hours > 0
        ? Math.round((filed_hours / required_hours) * 100)
        : 0
    const isOver = percentage > 100

    const option = {
        backgroundColor: bgColor,
        series: [
            {
                type: 'gauge',
                startAngle: 200,
                endAngle: -20,
                center: ['50%', '60%'],
                min: 0,
                max: maxVal,
                progress: {
                    show: true,
                    width: 16,
                    itemStyle: {
                        color: isOver
                            ? '#ef4444'
                            : percentage > 80
                                ? '#f59e0b'
                                : '#5470c6',
                    },
                },
                pointer: {
                    length: '60%',
                    width: 5,
                    itemStyle: { color: '#5470c6' },
                },
                axisLine: {
                    lineStyle: {
                        width: 16,
                        color: [[1, 'rgba(0,0,0,0.06)']],
                    },
                },
                axisTick: { show: false },
                splitLine: { show: false },
                axisLabel: { show: false },
                anchor: { show: true, size: 14, showAbove: true, itemStyle: { color: '#5470c6' } },
                detail: {
                    valueAnimation: true,
                    fontSize: 28,
                    fontWeight: 'bold',
                    offsetCenter: [0, '70%'],
                    formatter: function () {
                        return required_hours > 0 ? `${percentage}%` : `${filed_hours}h`
                    },
                },
                title: {
                    show: true,
                    offsetCenter: [0, '92%'],
                    fontSize: 12,
                    color: isOver ? '#ef4444' : 'rgba(0,0,0,0.35)',
                },
                data: [
                    {
                        value: filed_hours,
                        name: required_hours > 0
                            ? `of ${required_hours}h required`
                            : 'No limit set',
                    },
                ],
            },
        ],
    }

    gaugeChartInstance.setOption(option, true)
}

function renderStatusPieChart() {
    const dom = statusPieRef.value
    if (!dom || !props.report?.status_pie) return

    statusPieChartInstance = initChart(dom, statusPieChartInstance)
    if (!statusPieChartInstance) return

    const bgColor = getTailwindColor('bg-base-100')
    const { labels, counts, colors } = props.report.status_pie

    const option = {
        backgroundColor: bgColor,
        tooltip: {
            trigger: 'item',
            formatter: '{b}: {c} ({d}%)',
        },
        legend: {
            orient: 'vertical',
            right: 10,
            top: 'center',
            textStyle: { fontSize: 12 },
        },
        series: [
            {
                type: 'pie',
                radius: ['45%', '70%'],
                center: ['40%', '50%'],
                avoidLabelOverlap: false,
                label: {
                    show: true,
                    position: 'center',
                    formatter: function () {
                        const total = counts.reduce((a, b) => a + b, 0)
                        return total.toString()
                    },
                    fontSize: 28,
                    fontWeight: 'bold',
                },
                emphasis: {
                    label: { show: true, fontSize: 28, fontWeight: 'bold' },
                },
                labelLine: { show: false },
                data: labels.map((label, i) => ({
                    name: label,
                    value: counts[i],
                    itemStyle: { color: colors[i] },
                })),
            },
        ],
    }

    statusPieChartInstance.setOption(option, true)
}

const handleGenerateReport = () => {
    isLoading.value = true

    selectedDateRange.organization_unit_id = selectedOrgUnit.value

    selectedDateRange.get(route('approver.generate.report.daterange'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            reportLoaded.value = true
            nextTick(() => renderAllCharts())
        },
        onError: (errors) => {
            console.log("Validation errors:", errors)
        },
        onFinish: () => {
            isLoading.value = false
        },
    })
}

const handleRegenerateReport = () => {
    isRegenerating.value = true
    AIresponse.value = ""

    selectedDateRange.organization_unit_id = selectedOrgUnit.value

    selectedDateRange.get(route('approver.generate.report.daterange'), {
        preserveState: true,
        preserveScroll: true,
        onError: (errors) => console.log(errors),
        onFinish: async () => {
            isRegenerating.value = false
            await nextTick()
            renderAllCharts()
        },
    })
}

const handleAnalyzeAI = async () => {
    analyzingAI.value = true
    AIresponse.value = ""

    let firstChunk = true

    const result = await analyzeWithAI(props.report?.list ?? [], (chunk) => {
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
    if (!newTheme || !props.report) return
    renderAllCharts()
}, { immediate: true })

watch(selectedReportType, () => {
    renderTrendsChart()
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
