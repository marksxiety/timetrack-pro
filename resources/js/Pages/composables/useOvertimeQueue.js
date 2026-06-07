import { ref } from 'vue'

/**
 * @typedef {Object} QueueItem
 * @property {number} _uid - internal unique id for Vue keys
 * @property {string} dateRaw - raw date string (Y-m-d)
 * @property {string} displayDate - formatted date for display
 * @property {string} week - week label (e.g. "Week 23")
 * @property {number} employee_schedule_id - FK to schedules table
 * @property {string} shift_code - shift code label
 * @property {string} shift_start_time - shift start (H:i)
 * @property {string} shift_end_time - shift end (H:i)
 * @property {string} start_time - overtime start time (H:i)
 * @property {string} end_time - overtime end time (H:i)
 * @property {string} reason - overtime reason
 * @property {'pending'|'submitting'|'success'|'error'} state - item lifecycle state
 * @property {Object|null} errors - field errors from submission failure
 */

const STORAGE_KEY = 'overtime_filing_queue'

let nextUid = 1

/** @type {import('vue').Ref<QueueItem[]>} */
const queue = ref([])

/** @returns {number} */
function generateUid() {
    return nextUid++
}

/**
 * Persist the queue to sessionStorage.
 * Filters out submitted items and strips internal fields (_uid, errors).
 */
function persist() {
    try {
        const items = queue.value
            .filter(item => item.state !== 'success')
            .map(({ _uid, errors, ...data }) => ({
                ...data,
                ...(errors ? { errors } : {}),
            }))
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items))
    } catch {}
}

/**
 * Restore the queue from sessionStorage.
 * Items interrupted during submission (state === 'submitting') reset to 'pending'.
 */
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
            errors: item.errors || null,
        }))
    } catch {}
}

hydrate()

export { queue, persist }

/**
 * Append a new item to the queue.
 * @param {Omit<QueueItem, '_uid'|'state'|'errors'>} data
 */
export function addToQueue(data) {
    queue.value.push({ _uid: generateUid(), ...data, state: 'pending', errors: null })
    persist()
}

/**
 * Replace an existing queue item at the given index.
 * @param {number} index
 * @param {Omit<QueueItem, '_uid'|'state'|'errors'>} data
 */
export function updateInQueue(index, data) {
    const item = queue.value[index]
    if (!item) return
    queue.value[index] = { ...item, ...data, state: 'pending', errors: null }
    persist()
}

/**
 * Remove a queue item by index.
 * @param {number} index
 */
export function removeFromQueue(index) {
    queue.value.splice(index, 1)
    persist()
}

/** Empty the queue and persist the empty state. */
export function clearQueue() {
    queue.value = []
    persist()
}

/** Nuclear clear — empty the queue and remove the sessionStorage key. Used on logout. */
export function clearAll() {
    queue.value = []
    try {
        sessionStorage.removeItem(STORAGE_KEY)
    } catch {}
}
