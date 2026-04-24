import { ref, watch } from "vue";

/**
 * Resolve the initial theme from localStorage or OS preference.
 * @returns {'dark'|'light'}
 */
const getInitialTheme = () => {
    const stored = localStorage.getItem("theme");
    if (stored) return stored;
    return window.matchMedia?.("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light";
};

const initialTheme = getInitialTheme();
document.documentElement.setAttribute("data-theme", initialTheme);

/** @type {import('vue').Ref<'dark'|'light'>} */
export const theme = ref(initialTheme);

watch(theme, (value) => {
    document.documentElement.setAttribute("data-theme", value);
    localStorage.setItem("theme", value);
});

/**
 * Set the application theme.
 * @param {'dark'|'light'} newTheme
 */
export function setTheme(newTheme) {
    theme.value = newTheme;
}
