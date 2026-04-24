import { ref, readonly } from 'vue';

/** @type {import('vue').Ref<Object|null>} */
const config = ref(null);
/** @type {import('vue').Ref<boolean>} */
const loading = ref(false);

/**
 * Fetch and cache the application configuration from /setup/config.
 * Returns cached value on subsequent calls.
 * @returns {Promise<Object>}
 */
export async function loadConfig() {
    if (config.value !== null) return config.value;

    loading.value = true;
    try {
        const res = await fetch('/setup/config');
        if (res.ok) {
            config.value = await res.json();
        } else {
            config.value = {};
        }
    } catch {
        config.value = {};
    }
    loading.value = false;
    return config.value;
}

/**
 * Composable for accessing the application configuration.
 * @returns {{
 *   config: import('vue').DeepReadonly<import('vue').Ref<Object|null>>,
 *   loading: import('vue').DeepReadonly<import('vue').Ref<boolean>>,
 *   loadConfig: () => Promise<Object>
 * }}
 */
export function useConfig() {
    return {
        config: readonly(config),
        loading: readonly(loading),
        loadConfig,
    };
}
