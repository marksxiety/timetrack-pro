import { ref, watch } from "vue";

const getInitialTheme = () => {
    const stored = localStorage.getItem("theme");
    if (stored) return stored;
    return window.matchMedia?.("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light";
};

const initialTheme = getInitialTheme();
document.documentElement.setAttribute("data-theme", initialTheme);

export const theme = ref(initialTheme);

watch(theme, (value) => {
    document.documentElement.setAttribute("data-theme", value);
    localStorage.setItem("theme", value);
});

export function setTheme(newTheme) {
    theme.value = newTheme;
}
