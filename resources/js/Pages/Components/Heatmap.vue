<template>
    <div class="card bg-base-100 shadow-sm border border-base-300 w-full relative">
        <div v-if="isLoading" class="absolute inset-0 z-40 flex items-center justify-center bg-base-100/80 rounded-xl">
            <span class="loading loading-bars loading-xl text-primary"></span>
        </div>
        <div class="card-body px-[2.5rem] py-4">

            <div class="mt-4 grid grid-cols-12 gap-4 transition-opacity duration-300"
                :class="{ 'opacity-0': isLoading }">

                <!-- Heatmap -->
                <div class="col-span-11 flex justify-center overflow-hidden lg:overflow-hidden overflow-x-auto p-2">
                    <div class="w-fit">
                        <!-- Month labels -->
                        <div class="relative" :style="{ height: MONTH_ROW_H + 'px' }">
                            <span v-for="(label, i) in monthLabels" :key="'m' + i"
                                class="absolute text-[11px] text-base-content/40" :style="{ left: label.x + 'px' }">{{
                                    label.text }}</span>
                        </div>

                        <div class="inline-flex w-fit">
                            <!-- Day labels -->
                            <div class="flex flex-col shrink-0" style="width: 30px;">
                                <span v-for="(d, idx) in DAY_LABELS" :key="idx" class="text-[11px] text-base-content/40"
                                    :style="{ height: CELL_R_H + 'px', marginBottom: idx < 6 ? CELL_GAP + 'px' : '0', lineHeight: CELL_R_H + 'px' }">{{
                                        d }}</span>
                            </div>

                            <!-- Grid -->
                            <div :style="gridStyle">
                                <div v-for="cell in cells" :key="cell.i"
                                    class="rounded-[3px] hover:brightness-90 hover:z-50" :class="[
                                        cell.date ? 'tooltip tooltip-top' : '',
                                        !cell.fill && cell.date ? 'bg-base-300' : ''
                                    ]" :data-tip="cell.tip || undefined"
                                    :style="cell.fill ? { backgroundColor: cell.fill } : {}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Year pills -->
                <div class="col-span-1 flex flex-col justify-center overflow-y-auto"
                    :style="{ maxHeight: gridHeight + 'px' }" :class="{ 'opacity-50 pointer-events-none': isLoading }">
                    <template v-for="(y, idx) in yearPills" :key="y">
                        <div v-if="idx > 0" class="divider my-0"></div>
                        <button type="button" @click="year = y" class="btn btn-sm transition-all duration-150 w-full"
                            :class="year === y
                                ? 'btn-primary !bg-primary/50 hover:!bg-primary/60'
                                : 'btn-ghost text-base-content/70 hover:text-base-content'">
                            {{ y }}
                        </button>
                    </template>
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
import { ref, shallowRef, watch, onMounted, computed } from 'vue'
import { fetchHeatmapData } from '../api/heatmap.js'

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const DAY_LABELS = ['', 'Mon', '', 'Wed', '', 'Fri', '']
const CELL_R_W = 20
const CELL_R_H = 18
const CELL_GAP = 2
const MONTH_ROW_H = 18

const PRIMARY_COLORS = {
    low: '#dfd0fe',
    mid: '#9b61fb',
    high: '#570df8',
}
const PRIMARY_COLORS_LIST = [PRIMARY_COLORS.low, PRIMARY_COLORS.mid, PRIMARY_COLORS.high]

const isLoading = ref(true)
const heatmapData = shallowRef({})
const yearPills = ref(Array.from({ length: 4 }, (_, i) => new Date().getFullYear() + 1 - i))
const year = ref(null)
const totalHours = ref('0')
const cells = shallowRef([])
const monthLabels = shallowRef([])
const gridHeight = ref(MONTH_ROW_H + 7 * CELL_R_H + 6 * CELL_GAP)

const gridStyle = computed(() => ({
    display: 'grid',
    gridTemplateRows: `repeat(7, ${CELL_R_H}px)`,
    gridAutoFlow: 'column',
    gridAutoColumns: `${CELL_R_W}px`,
    gap: `${CELL_GAP}px`,
}))

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

        if (inRange && dayIdx === 0) {
            const month = current.getMonth()
            if (month !== lastMonth) {
                lastMonth = month
                labels.push({ text: MONTHS[month], x: weekIdx * (CELL_R_W + CELL_GAP) })
            }
        }

        if (inRange) total += hours

        result.push({
            i,
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
    gridHeight.value = MONTH_ROW_H + 7 * CELL_R_H + 6 * CELL_GAP
}

async function loadData() {
    isLoading.value = true
    buildGrid({})
    try {
        const { start: rangeStart, end: rangeEnd } = computeRange()
        const res = await fetchHeatmapData(dateKey(rangeStart), dateKey(rangeEnd))
        const data = res.data || {}
        heatmapData.value = data
        buildGrid(data)
    } catch {
        heatmapData.value = {}
        buildGrid({})
    } finally {
        isLoading.value = false
    }
}

watch(year, loadData)
onMounted(() => {
    loadData()
})
</script>
