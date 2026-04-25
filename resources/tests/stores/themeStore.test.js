import { describe, it, expect, vi, beforeEach } from 'vitest';
import { nextTick } from 'vue';

const THEME_PATH = '../../js/Pages/utils/themeStore.js';

describe('themeStore', () => {
    beforeEach(() => {
        vi.resetModules();
        vi.restoreAllMocks();
        localStorage.clear();
        document.documentElement.removeAttribute('data-theme');
        vi.spyOn(Storage.prototype, 'setItem');
        vi.spyOn(Storage.prototype, 'getItem');
        vi.spyOn(document.documentElement, 'setAttribute');
    });

    async function importFresh() {
        return await import(THEME_PATH);
    }

    it('defaults to OS preference when no stored theme', async () => {
        vi.stubGlobal('matchMedia', vi.fn().mockReturnValue({ matches: true }));

        const { theme } = await importFresh();

        expect(theme.value).toBe('dark');
        expect(localStorage.getItem).toHaveBeenCalledWith('theme');
    });

    it('uses stored theme from localStorage', async () => {
        localStorage.setItem('theme', 'light');

        const { theme } = await importFresh();

        expect(theme.value).toBe('light');
    });

    it('setTheme updates theme ref and persists to localStorage', async () => {
        localStorage.setItem('theme', 'dark');

        const { setTheme, theme } = await importFresh();

        setTheme('light');
        await nextTick();

        expect(theme.value).toBe('light');
        expect(localStorage.setItem).toHaveBeenCalledWith('theme', 'light');
    });

    it('setTheme updates document data-theme attribute', async () => {
        localStorage.setItem('theme', 'dark');

        const { setTheme } = await importFresh();

        setTheme('light');
        await nextTick();

        expect(document.documentElement.setAttribute).toHaveBeenCalledWith('data-theme', 'light');
    });
});
