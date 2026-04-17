import { ref, readonly } from 'vue'

const config = ref(null)
const loading = ref(false)

export async function loadConfig() {
    if (config.value !== null) return config.value

    loading.value = true
    try {
        const res = await fetch('/setup/config')
        if (res.ok) {
            config.value = await res.json()
        } else {
            config.value = {}
        }
    } catch {
        config.value = {}
    }
    loading.value = false
    return config.value
}

export function useConfig() {
    return {
        config: readonly(config),
        loading: readonly(loading),
        loadConfig,
    }
}
