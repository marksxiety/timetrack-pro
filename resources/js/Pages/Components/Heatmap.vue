<template>
    <div class="card bg-base-100 shadow-sm border border-base-300 w-full relative">
        <div v-if="isLoading" class="absolute inset-0 z-40 flex items-center justify-center bg-base-100/80 rounded-xl">
            <span class="loading loading-bars loading-xl text-primary"></span>
        </div>
        <div class="card-body px-12 py-4" :class="{ 'opacity-0': isLoading }">

            <div class="mt-4 grid gap-4 transition-opacity duration-300" style="grid-template-columns: 1fr 5.5rem;"
                ref="containerRef" :class="{ 'opacity-0': isLoading }">
                <div class="relative overflow-x-auto flex justify-center">
                    <div class="inline-flex flex-shrink-0 relative" style="min-width: 600px;">
                        <svg ref="svgRef" :width="svgWidth" :height="svgHeight" class="block">

                            <!-- Month labels -->
                            <text v-for="(label, i) in monthLabels" :key="'m' + i" :x="label.x" y="11"
                                class="fill-current text-base-content/40" font-size="10" font-family="inherit"
                                text-anchor="start">{{ label.text }}</text>

                            <!-- Day-of-week labels -->
                            <text x="0" :y="OFFSET_Y + 1 * CELL_H - 2" class="fill-current text-base-content/40"
                                font-size="9" font-family="inherit">Mon</text>
                            <text x="0" :y="OFFSET_Y + 3 * CELL_H - 2" class="fill-current text-base-content/40"
                                font-size="9" font-family="inherit">Wed</text>
                            <text x="0" :y="OFFSET_Y + 5 * CELL_H - 2" class="fill-current text-base-content/40"
                                font-size="9" font-family="inherit">Fri</text>

                            <!-- Cells -->
                            <rect v-for="cell in cells" :key="cell.i" :x="cell.x" :y="cell.y" :width="CELL_R_W"
                                :height="CELL_R_H" rx="3" :fill="cell.fill" :data-date="cell.date"
                                :data-hours="cell.hours" :data-tip="cell.tip"
                                class="cursor-default transition-opacity duration-75 hover:opacity-70" />
                        </svg>
                    </div>

                    <!--
                        Tooltip — DaisyUI's `tooltip` class works on HTML elements only and cannot
                        wrap SVG <rect> nodes. We keep the manual JS positioning but render the
                        bubble using DaisyUI's own `.tooltip-content` child element so it inherits
                        the theme's neutral bg, text color, border-radius, and shadow automatically.
                    -->
                    <div ref="tooltipRef"
                        class="tooltip tooltip-top tooltip-open absolute pointer-events-none z-50 hidden"
                        style="transform: translate(-50%, calc(-100% - 8px));">
                        <div class="tooltip-content text-[11px] font-medium whitespace-nowrap">
                            {{ tooltipText }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 content-start overflow-y-auto pr-4"
                    :style="{ maxHeight: svgHeight + 'px' }" :class="{ 'opacity-50 pointer-events-none': isLoading }">
                    <button v-for="y in yearPills" :key="y" type="button" @click="year = y"
                        class="btn btn-sm transition-all duration-150 w-full"
                        :class="year === y
                            ? 'btn-primary !bg-primary/50 hover:!bg-primary/60'
                            : 'btn-ghost text-base-content/70 hover:text-base-content'">
                        {{ y }}
                    </button>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center justify-end gap-1.5 mt-2">
                <span class="text-[10px] text-base-content/40 mr-0.5">Less</span>
                <div class="rounded-sm flex-none bg-base-300"
                    :style="{ width: CELL_R_W + 'px', height: CELL_R_H + 'px' }">
                </div>
                <div v-for="color in PRIMARY_COLORS_LIST" :key="color" class="rounded-sm flex-none"
                    :style="{ width: CELL_R_W + 'px', height: CELL_R_H + 'px', backgroundColor: color }"></div>
                <span class="text-[10px] text-base-content/40 ml-0.5">More</span>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, shallowRef, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { fetchHeatmapData } from '../api/overtime.js'

// ─── Constants ───────────────────────────────────────────────────────────────
const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const CELL_W = 22
const CELL_H = 20
const CELL_R_W = 20
const CELL_R_H = 18
const OFFSET_X = 30
const OFFSET_Y = 18

const PRIMARY_COLORS = {
    low: '#dfd0fe',
    mid: '#9b61fb',
    high: '#570df8',
}
const PRIMARY_COLORS_LIST = [PRIMARY_COLORS.low, PRIMARY_COLORS.mid, PRIMARY_COLORS.high]

let base300Color = '#e5e7eb'

function resolveBase300() {
    const probe = document.createElement('div')
    probe.className = 'bg-base-300'
    probe.style.cssText = 'position:absolute;width:1px;height:1px;visibility:hidden;pointer-events:none;'
    document.body.appendChild(probe)
    base300Color = getComputedStyle(probe).backgroundColor
    document.body.removeChild(probe)
}

// ─── State ───────────────────────────────────────────────────────────────────
const isLoading = ref(true)
const heatmapData = shallowRef({})
const yearPills = Array.from({ length: 4 }, (_, i) => new Date().getFullYear() + 1 - i)
const year = ref(new Date().getFullYear())
const totalHours = ref('0')
const cells = shallowRef([])
const monthLabels = shallowRef([])
const svgWidth = ref(0)
const svgHeight = ref(OFFSET_Y + 7 * CELL_H + 4)
const tooltipText = ref('')

const svgRef = ref(null)
const tooltipRef = ref(null)
const containerRef = ref(null)

// ─── Helpers ─────────────────────────────────────────────────────────────────
function dateKey(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function getCellFill(hours) {
    if (hours === 0) return base300Color
    if (hours <= 2) return PRIMARY_COLORS.low
    if (hours <= 5) return PRIMARY_COLORS.mid
    return PRIMARY_COLORS.high
}

function formatTip(dateStr, hours) {
    const d = new Date(dateStr + 'T00:00:00')
    const fmt = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })
    return hours > 0 ? `${fmt} · ${hours} hrs` : `${fmt} · No overtime`
}

// ─── Grid builder ─────────────────────────────────────────────────────────────
function buildGrid(data) {
    const jan1 = new Date(year.value, 0, 1)
    const startOfWeek = new Date(jan1)
    startOfWeek.setDate(jan1.getDate() - ((jan1.getDay() + 6) % 7))

    const result = []
    const labels = []
    let lastMonth = -1
    let total = 0
    let weekIdx = 0
    let dayIdx = 0
    let i = 0

    const current = new Date(startOfWeek)
    const dec31 = new Date(year.value, 11, 31)

    while (current <= dec31 || dayIdx > 0) {
        const inYear = current.getFullYear() === year.value
        const key = dateKey(current)
        const hours = inYear ? (data[key] || 0) : 0
        const dateStr = inYear ? key : null

        if (inYear && dayIdx === 0) {
            const month = current.getMonth()
            if (month !== lastMonth) {
                lastMonth = month
                labels.push({ text: MONTHS[month], x: OFFSET_X + weekIdx * CELL_W })
            }
        }

        if (inYear) total += hours

        result.push({
            i,
            x: OFFSET_X + weekIdx * CELL_W,
            y: OFFSET_Y + dayIdx * CELL_H,
            fill: dateStr ? getCellFill(hours) : 'transparent',
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
    svgWidth.value = OFFSET_X + weekIdx * CELL_W + 4
    svgHeight.value = OFFSET_Y + 7 * CELL_H + 4
}

// ─── Tooltip ──────────────────────────────────────────────────────────────────
function handleMouseMove(e) {
    if (isLoading.value) return
    const rect = e.target.closest('rect[data-date]')
    const tip = tooltipRef.value
    if (!rect?.dataset.date) { tip.classList.add('hidden'); return }

    tooltipText.value = rect.dataset.tip || ''
    tip.classList.remove('hidden')

    tip.style.left = (parseFloat(rect.getAttribute('x')) + CELL_R_W / 2) + 'px'
    tip.style.top = parseFloat(rect.getAttribute('y')) + 'px'
}

function handleMouseLeave() {
    tooltipRef.value?.classList.add('hidden')
}

// ─── Data loading ─────────────────────────────────────────────────────────────
async function loadData() {
    isLoading.value = true
    buildGrid({})
    removeListeners()
    try {
        const res = await fetchHeatmapData(year.value)
        heatmapData.value = res.data || {}
        if (!res.data) {
            heatmapData.value = {}
            buildGrid({})
        } else {
            buildGrid(heatmapData.value)
        }
    } catch {
        heatmapData.value = {}
        buildGrid({})
    } finally {
        isLoading.value = false
        await nextTick()
        addListeners()
    }
}

function addListeners() {
    svgRef.value?.addEventListener('mousemove', handleMouseMove)
    svgRef.value?.addEventListener('mouseleave', handleMouseLeave)
}

function removeListeners() {
    svgRef.value?.removeEventListener('mousemove', handleMouseMove)
    svgRef.value?.removeEventListener('mouseleave', handleMouseLeave)
}

watch(year, loadData)
onMounted(() => {
    resolveBase300()
    loadData()
})
onBeforeUnmount(removeListeners)
</script>