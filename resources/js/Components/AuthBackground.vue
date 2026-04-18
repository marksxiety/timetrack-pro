<template>
    <div
        ref="container"
        class="absolute inset-0 opacity-50 mix-blend-screen pointer-events-none"
    >
        <div
            ref="blob1"
            class="absolute h-64 w-64 rounded-full bg-primary blur-3xl"
            :style="blob1Style"
        ></div>
        <div
            ref="blob2"
            class="absolute h-64 w-64 rounded-full bg-indigo-500 blur-3xl"
            :style="blob2Style"
        ></div>
        <div
            ref="blob3"
            class="absolute h-64 w-64 rounded-full bg-secondary blur-3xl"
            :style="blob3Style"
        ></div>
    </div>
</template>
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const container = ref(null)

// Current rendered positions (lerped)
const pos = [
    { x: 30, y: 40 },
    { x: 60, y: 50 },
    { x: 50, y: 60 },
]

// Target positions (where cursor is)
const targets = [
    { x: 30, y: 40 },
    { x: 60, y: 50 },
    { x: 50, y: 60 },
]

// Offsets so blobs don't stack on each other
const offsets = [
    { x: -5, y: -5 },
    { x:  8, y:  6 },
    { x:  2, y: -8 },
]

// Each blob has a different lerp speed — blob1 fastest, blob3 slowest
const speeds = [0.08, 0.05, 0.03]

const blob1Style = ref({ left: '30%', top: '40%', transform: 'translate(-50%, -50%)' })
const blob2Style = ref({ left: '60%', top: '50%', transform: 'translate(-50%, -50%)' })
const blob3Style = ref({ left: '50%', top: '60%', transform: 'translate(-50%, -50%)' })

const styles = [blob1Style, blob2Style, blob3Style]

let rafId = null
let cursorX = 50
let cursorY = 50

const lerp = (a, b, t) => a + (b - a) * t

const animate = () => {
    // Blob 1 follows cursor directly
    targets[0].x = cursorX + offsets[0].x
    targets[0].y = cursorY + offsets[0].y

    // Blob 2 follows blob 1's current position (chained)
    targets[1].x = pos[0].x + offsets[1].x
    targets[1].y = pos[0].y + offsets[1].y

    // Blob 3 follows blob 2's current position (chained)
    targets[2].x = pos[1].x + offsets[2].x
    targets[2].y = pos[1].y + offsets[2].y

    for (let i = 0; i < 3; i++) {
        pos[i].x = lerp(pos[i].x, targets[i].x, speeds[i])
        pos[i].y = lerp(pos[i].y, targets[i].y, speeds[i])

        styles[i].value = {
            left: `${pos[i].x}%`,
            top: `${pos[i].y}%`,
            transform: 'translate(-50%, -50%)',
        }
    }

    rafId = requestAnimationFrame(animate)
}

const handleMouseMove = (e) => {
    const rect = container.value?.parentElement?.getBoundingClientRect()
    if (!rect) return
    cursorX = (e.clientX - rect.left) / rect.width * 100
    cursorY = (e.clientY - rect.top) / rect.height * 100
}

onMounted(() => {
    const parent = container.value?.parentElement
    if (parent) {
        parent.addEventListener('mousemove', handleMouseMove)
    }
    rafId = requestAnimationFrame(animate)
})

onUnmounted(() => {
    const parent = container.value?.parentElement
    if (parent) {
        parent.removeEventListener('mousemove', handleMouseMove)
    }
    if (rafId) cancelAnimationFrame(rafId)
})
</script>