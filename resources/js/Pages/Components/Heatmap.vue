<template>
    <div class="card bg-base-100 shadow-sm border border-base-300 w-full relative">
        <div v-if="isLoading" class="absolute inset-0 z-40 flex items-center justify-center bg-base-100/80 rounded-xl">
            <span class="loading loading-bars loading-xl text-primary"></span>
        </div>
        <div class="card-body px-[2.5rem] py-4">
            <div class="mt-4 flex gap-4 transition-opacity duration-300" :class="{ 'opacity-0': isLoading }">

                <!-- SVG Heatmap -->
                <div class="flex-1 overflow-x-auto">
                    <svg :width="svgWidth" :height="svgHeight" xmlns="http://www.w3.org/2000/svg">

                        <!-- Month labels -->
                        <text
                            v-for="(label, i) in monthLabels"
                            :key="'m' + i"
                            :x="label.x"
                            :y="MONTH_ROW_H - 4"
                            class="fill-base-content/40"
                            font-size="11"
                            font-family="inherit"
                        >{{ label.text }}</text>

                        <!-- Day labels -->
                        <text
                            v-for="(d, idx) in DAY_LABELS"
                            :key="'d' + idx"
                            :x="DAY_LABEL_W - 4"
                            :y="MONTH_ROW_H + idx * (CELL_R_H + CELL_GAP) + CELL_R_H / 2 + 4"
                            text-anchor="end"
                            class="fill-base-content/40"
                            font-size="11"
                            font-family="inherit"
                        >{{ d }}</text>

                        <!-- Cells -->
                        <g v-for="cell in cells" :key="cell.i">
                            <rect
                                :x="cell.x"
                                :y="cell.y"
                                :width="CELL_R_W"
                                :height="CELL_R_H"
                                :rx="3"
                                :ry="3"
                                :fill="cell.fill || 'var(--fallback-b3, oklch(var(--b3)))'"
                                :opacity="cell.date ? 1 : 0"
                                class="cursor-default"
                            />
                            <!-- Tooltip trigger overlay -->
                            <rect
                                v-if="cell.date"
                                :x="cell.x"
                                :y="cell.y"
                                :width="CELL_R_W"
                                :height="CELL_R_H"
                                fill="transparent"
                                class="tooltip-cell"
                                @mouseenter="showTooltip($event, cell)"
                                @mouseleave="hideTooltip"
                            />
                        </g>
                    </svg>
                </div>

                <!-- Year pills -->
                <div class="flex flex-col justify-center gap-1" :class="{ 'opacity-50 pointer-events-none': isLoading }">
                    <template v-for="(y, idx) in yearPills" :key="y">
                        <div v-if="idx > 0" class="divider my-0"></div>
                        <button
                            type="button"
                            @click="year = y"
                            class="btn btn-sm transition-all duration-150 w-full"
                            :class="year === y
                                ? 'btn-primary !bg-primary/50 hover:!bg-primary/60'
                                : 'btn-ghost text-base-content/70 hover:text-base-content'"
                        >{{ y }}</button>
                    </template>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center justify-end gap-1.5 mt-2">
                <span class="text-[10px] text-base-content/40 mr-0.5">Less</span>
                <svg :width="CELL_R_W" :height="CELL_R_H">
                    <rect :width="CELL_R_W" :height="CELL_R_H" rx="2" fill="var(--fallback-b3, oklch(var(--b3)))" />
                </svg>
                <svg v-for="color in PRIMARY_COLORS_LIST" :key="color" :width="CELL_R_W" :height="CELL_R_H">
                    <rect :width="CELL_R_W" :height="CELL_R_H" rx="2" :fill="color" />
                </svg>
                <span class="text-[10px] text-base-content/40 ml-0.5">More</span>
            </div>

            <!-- SVG Tooltip (portal-style, fixed position) -->
            <div
                v-if="tooltip.visible"
                class="fixed z-50 px-2 py-1 rounded text-xs bg-base-content text-base-100 shadow pointer-events-none whitespace-nowrap"
                :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px', transform: 'translate(-50%, -110%)' }"
            >
                {{ tooltip.text }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, shallowRef, watch, onMounted, computed } from 'vue'
import { fetchHeatmapData } from '../api/heatmap.js'

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const DAY_LABELS = ['', 'Mon', '', 'Wed', '', 'Fri', '']
const CELL_R_W = 14
const CELL_R_H = 14
const CELL_GAP = 3
const MONTH_ROW_H = 20
const DAY_LABEL_W = 28

const PRIMARY_COLORS = {
    low: '#dfd0fe',
    mid: '#9b61fb',
    high: '#570df8',
}
const PRIMARY_COLORS_LIST = [PRIMARY_COLORS.low, PRIMARY_COLORS.mid, PRIMARY_COLORS.high]

const isLoading = ref(true)
const heatmapData = shallowRef({})
const yearPills = ref([])
const year = ref(null)
const totalHours = ref('0')
const cells = shallowRef([])
const monthLabels = shallowRef([])

const tooltip = ref({ visible: false, text: '', x: 0, y: 0 })

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

        // Compute pixel positions directly — same coordinate system as SVG
        const x = DAY_LABEL_W + weekIdx * (CELL_R_W + CELL_GAP)
        const y = MONTH_ROW_H + dayIdx * (CELL_R_H + CELL_GAP)

        // Month label: place at the column x where the month first appears
        // If month starts mid-week (dayIdx > 0), label goes on this column still —
        // the x is already correct since we use the same formula
        if (inRange) {
            const month = current.getMonth()
            if (month !== lastMonth) {
                lastMonth = month
                labels.push({ text: MONTHS[month], x })
            }
        }

        if (inRange) total += hours

        result.push({
            i,
            x,
            y,
            fill: dateStr ? getCellFill(hours) : '',
            date: dateStr,
            hours,
            tip: dateStr ? formatTip(dateStr, hours) : '',
        })

        i++
        dayIdx++
        if (dayIdx === 7) { dayIdx = 0; weekIdx++ }
        current.setDate(current.getDate() + 1)
    }

    cells.value = result
    monthLabels.value = labels
    totalHours.value = total.toFixed(1)
}

function showTooltip(event, cell) {
    tooltip.value = {
        visible: true,
        text: cell.tip,
        x: event.clientX,
        y: event.clientY,
    }
}

function hideTooltip() {
    tooltip.value.visible = false
}

let abortController = null

async function loadData() {
    abortController?.abort()
    const controller = new AbortController()
    abortController = controller
    isLoading.value = true
    buildGrid({})
    try {
        const { start: rangeStart, end: rangeEnd } = computeRange()
        const res = await fetchHeatmapData(dateKey(rangeStart), dateKey(rangeEnd), controller.signal)
        const data = res.data || {}

        if (res.years && res.years.length > 0) {
            yearPills.value = [...res.years].sort((a, b) => b - a)
        }

        heatmapData.value = data
        buildGrid(data)
    } catch (e) {
        if (e.name === 'AbortError') return
        heatmapData.value = {}
        buildGrid({})
    } finally {
        if (controller === abortController) {
            isLoading.value = false
        }
    }
}

watch(year, loadData)
onMounted(() => {
    loadData()
})
</script>