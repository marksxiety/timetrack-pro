import { ref } from "vue";

// Detect stored theme or browser preference
const defaultTheme =
    localStorage.getItem("theme") ||
    (window.matchMedia &&
    window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light");

export const theme = ref(defaultTheme);

export function setTheme(newTheme) {
    theme.value = newTheme;
    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
}
