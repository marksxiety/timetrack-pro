<template>
    <div class="card bg-base-100 shadow-sm border border-base-300 w-full relative heatmap-root">
        <div v-if="loading" class="absolute inset-0 z-40 flex items-center justify-center bg-base-100/80 rounded-xl">
            <span class="loading loading-bars loading-xl text-primary"></span>
        </div>
        <div class="card-body px-[2.5rem] py-4">
            <div class="mt-4 flex justify-center gap-4 overflow-x-auto transition-opacity duration-300 min-w-0"
                :class="{ 'opacity-0': loading }">

                <div ref="heatmapBox" class="flex justify-center border border-base-300 rounded-xl p-3">
                    <svg :width="svgWidth" :height="svgHeight" xmlns="http://www.w3.org/2000/svg">

                        <!-- Month labels -->
                        <text v-for="(label, i) in monthLabels" :key="'m' + i" :x="label.x" :y="MONTH_ROW_H - 4"
                            fill="var(--color-label)" font-size="11" font-family="inherit">{{ label.text }}</text>

                        <!-- Day labels -->
                        <text v-for="(d, idx) in DAY_LABELS" :key="'d' + idx" :x="DAY_LABEL_W - 4"
                            :y="MONTH_ROW_H + idx * (CELL_R_H + CELL_GAP) + CELL_R_H / 2 + 4" text-anchor="end"
                            fill="var(--color-label)" font-size="11" font-family="inherit">{{ d }}</text>

                        <!-- Cells -->
                        <g v-for="cell in cells" :key="cell.i">
                            <rect :x="cell.x" :y="cell.y" :width="CELL_R_W" :height="CELL_R_H" rx="3" ry="3"
                                :fill="cell.date ? (cell.fill || 'var(--color-empty-cell)') : 'transparent'" />
                            <rect v-if="cell.date" :x="cell.x" :y="cell.y" :width="CELL_R_W" :height="CELL_R_H"
                                fill="transparent" style="cursor: default;" @mouseenter="showTooltip($event, cell)"
                                @mouseleave="hideTooltip" />
                        </g>
                    </svg>
                </div>


                <!-- Year pills -->
                <div class="flex flex-col shrink-0 w-32" :class="{ 'opacity-50 pointer-events-none': loading }">
                    <div
                        class="flex flex-col gap-1 border border-base-300 rounded-xl p-1 overflow-y-auto justify-center"
                        :style="{ maxHeight: heatmapBoxHeight > 0 ? heatmapBoxHeight + 'px' : undefined }">
                        <button v-for="y in years" :key="y" type="button" @click="selectYear(y)"
                            class="btn btn-sm w-full"
                            :class="year === y ? 'btn-primary shadow-sm' : 'btn-ghost text-base-content/50'">{{ y
                            }}</button>
                    </div>
                </div>
            </div>

            <!-- Legend row -->
            <div class="flex items-center justify-end gap-2 mt-2">
                <!-- Color scale legend -->
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] text-base-content/40 mr-0.5">Less</span>
                    <svg :width="CELL_R_W" :height="CELL_R_H">
                        <rect :width="CELL_R_W" :height="CELL_R_H" rx="2" fill="var(--color-empty-cell)" />
                    </svg>
                    <svg v-for="color in PRIMARY_COLORS_LIST" :key="color" :width="CELL_R_W" :height="CELL_R_H">
                        <rect :width="CELL_R_W" :height="CELL_R_H" rx="2" :fill="color" />
                    </svg>
                    <span class="text-[10px] text-base-content/40 ml-0.5">More</span>
                </div>

                <!-- Status filter popover -->
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-xs btn-ghost btn-square">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path
                                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z">
                            </path>
                        </svg>
                    </label>
                    <ul tabindex="0"
                        class="dropdown-content z-30 menu p-2 shadow-lg bg-base-200 border border-base-300 rounded-xl w-48">
                        <li class="menu-title">
                            <span class="text-[11px] font-semibold uppercase tracking-wide opacity-60">Status
                                Filter</span>
                        </li>
                        <li v-for="status in availableStatuses" :key="status">
                            <label class="label cursor-pointer gap-3 justify-start">
                                <input type="checkbox" v-model="statusFilters[status]"
                                    class="checkbox checkbox-xs checkbox-primary" />
                                <span class="font-medium text-sm" :style="{ color: STATUS_COLORS[status] }">{{ status
                                }}</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tooltip -->
            <div v-if="tooltip.visible"
                class="fixed z-50 px-2 py-1 rounded text-xs bg-base-content text-base-100 shadow pointer-events-none whitespace-nowrap"
                :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px', transform: 'translate(-50%, -110%)' }">
                {{ tooltip.text }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, shallowRef, watch, onMounted, computed, reactive, nextTick } from 'vue'

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    },
    years: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    loading: {
        type: Boolean,
        default: false
    },
    availableStatuses: {
        type: Array,
        default: () => ['APPROVED', 'FILED', 'PENDING']
    }
})

const emit = defineEmits(['change-year', 'change-filters'])

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const DAY_LABELS = ['', 'Mon', '', 'Wed', '', 'Fri', '']
const CELL_R_W = 17
const CELL_R_H = 17
const CELL_GAP = 5
const MONTH_ROW_H = 20
const DAY_LABEL_W = 28

const STATUS_COLORS = {
    APPROVED: '#570df8',
    FILED: '#2563eb',
    PENDING: '#f59e0b',
}

const PRIMARY_COLORS = {
    low: '#dfd0fe',
    mid: '#9b61fb',
    high: '#570df8',
}
const PRIMARY_COLORS_LIST = [PRIMARY_COLORS.low, PRIMARY_COLORS.mid, PRIMARY_COLORS.high]

const year = ref(null)
const cells = shallowRef([])
const monthLabels = shallowRef([])
const tooltip = ref({ visible: false, text: '', x: 0, y: 0 })
const statusFilters = reactive({})
const heatmapBox = ref(null)
const heatmapBoxHeight = ref(0)

function initStatusFilters() {
    props.availableStatuses.forEach(s => {
        if (!(s in statusFilters)) {
            statusFilters[s] = true
        }
    })
    Object.keys(statusFilters).forEach(s => {
        if (!props.availableStatuses.includes(s)) {
            delete statusFilters[s]
        }
    })
}

initStatusFilters()
watch(() => props.availableStatuses, initStatusFilters)

let skipYearEmit = true

onMounted(() => {
    nextTick(() => { skipYearEmit = false })
})

async function syncBoxHeight() {
    await nextTick()
    if (heatmapBox.value) {
        heatmapBoxHeight.value = heatmapBox.value.offsetHeight
    }
}

function activeStatuses() {
    return props.availableStatuses.filter(s => statusFilters[s])
}

const NUM_WEEKS = computed(() => {
    const { gridStart, end: rangeEnd } = computeRange()
    const ms = rangeEnd - gridStart
    return Math.ceil(ms / (7 * 24 * 60 * 60 * 1000)) + 1
})

const svgWidth = computed(() => DAY_LABEL_W + NUM_WEEKS.value * (CELL_R_W + CELL_GAP))
const svgHeight = computed(() => MONTH_ROW_H + 7 * (CELL_R_H + CELL_GAP))

function dateKey(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function snapToSunday(date) {
    const d = new Date(date)
    d.setDate(d.getDate() - d.getDay())
    return d
}

function getCellFill(hours) {
    if (hours === 0) return ''
    if (hours <= 2) return PRIMARY_COLORS.low
    if (hours <= 5) return PRIMARY_COLORS.mid
    return PRIMARY_COLORS.high
}

function formatTip(dateStr, hours) {
    const d = new Date(dateStr + 'T00:00:00')
    const fmt = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })
    return hours > 0 ? `${fmt} · ${hours} hrs` : `${fmt} · No overtime`
}

function computeRange() {
    if (year.value === null) {
        const end = new Date()
        const start = new Date()
        start.setDate(start.getDate() - 365)
        return { start, end, gridStart: snapToSunday(start) }
    }
    const y = year.value
    const start = new Date(y, 0, 1)
    const end = new Date(y, 11, 31)
    return { start, end, gridStart: snapToSunday(start) }
}

function buildGrid(data) {
    const { start: rangeStart, end: rangeEnd, gridStart } = computeRange()
    const rangeStartStr = dateKey(rangeStart)
    const rangeEndStr = dateKey(rangeEnd)

    const result = []
    const labels = []
    let lastMonth = -1
    let total = 0
    let weekIdx = 0
    let dayIdx = 0
    let i = 0

    const current = new Date(gridStart)

    while (current <= rangeEnd) {
        const key = dateKey(current)
        const inRange = key >= rangeStartStr && key <= rangeEndStr
        const hours = inRange ? (data[key] || 0) : 0
        const dateStr = inRange ? key : null

        const x = DAY_LABEL_W + weekIdx * (CELL_R_W + CELL_GAP)
        const y = MONTH_ROW_H + dayIdx * (CELL_R_H + CELL_GAP)

        if (inRange) {
            const month = current.getMonth()
            if (month !== lastMonth) {
                lastMonth = month
                labels.push({ text: MONTHS[month], x })
            }
            total += hours
        }

        result.push({ i, x, y, fill: dateStr ? getCellFill(hours) : '', date: dateStr, hours, tip: dateStr ? formatTip(dateStr, hours) : '' })

        i++
        dayIdx++
        if (dayIdx === 7) { dayIdx = 0; weekIdx++ }
        current.setDate(current.getDate() + 1)
    }

    cells.value = result
    monthLabels.value = labels
    emit('computed-stats', { total_hours: total.toFixed(2) })
    syncBoxHeight()
}

watch(() => props.data, (newData) => {
    buildGrid(newData)
}, { immediate: true, deep: true })

watch(year, (newYear) => {
    buildGrid(props.data)
    if (!skipYearEmit) {
        emit('change-year', newYear)
    }
})

watch(statusFilters, () => {
    emit('change-filters', activeStatuses())
}, { deep: true })

function selectYear(y) {
    year.value = y
}

function showTooltip(event, cell) {
    tooltip.value = { visible: true, text: cell.tip, x: event.clientX, y: event.clientY }
}

function hideTooltip() {
    tooltip.value.visible = false
}
</script>

<style scoped>
.heatmap-root {
    --color-empty-cell: var(--color-base-300);
    --color-label: color-mix(in oklch, var(--color-base-content) 40%, transparent);
}
</style>
