import { ref } from 'vue'

const STORAGE_KEY = 'overtime_filing_queue'

let nextUid = 1

const queue = ref([])

function generateUid() {
    return nextUid++
}

function persist() {
    try {
        const items = queue.value
            .filter(item => item.state !== 'success')
            .map(({ _uid, errors, ...data }) => data)
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items))
    } catch {}
}

function hydrate() {
    try {
        const raw = sessionStorage.getItem(STORAGE_KEY)
        if (!raw) return
        const items = JSON.parse(raw)
        if (!Array.isArray(items)) return
        queue.value = items.map(item => ({
            ...item,
            _uid: generateUid(),
            state: item.state === 'submitting' ? 'pending' : (item.state || 'pending'),
            errors: null,
        }))
    } catch {}
}

hydrate()

export { queue, persist }

export function addToQueue(data) {
    queue.value.push({ _uid: generateUid(), ...data, state: 'pending', errors: null })
    persist()
}

export function updateInQueue(index, data) {
    const item = queue.value[index]
    if (!item) return
    queue.value[index] = { ...item, ...data, state: 'pending', errors: null }
    persist()
}

export function removeFromQueue(index) {
    queue.value.splice(index, 1)
    persist()
}

export function clearQueue() {
    queue.value = []
    persist()
}

export function clearAll() {
    queue.value = []
    try {
        sessionStorage.removeItem(STORAGE_KEY)
    } catch {}
}
