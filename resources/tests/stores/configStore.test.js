import { describe, it, expect, vi, beforeEach } from 'vitest';

const CONFIG_PATH = '../../js/Pages/utils/configStore.js';

describe('configStore', () => {
    beforeEach(() => {
        vi.resetModules();
        vi.restoreAllMocks();
    });

    async function importFresh() {
        return await import(CONFIG_PATH);
    }

    it('loadConfig fetches config and caches it', async () => {
        const fakeConfig = { app: 'test' };
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
            ok: true,
            json: () => Promise.resolve(fakeConfig),
        }));

        const { loadConfig, useConfig } = await importFresh();

        const result1 = await loadConfig();
        expect(result1).toEqual(fakeConfig);
        expect(useConfig().config.value).toEqual(fakeConfig);

        const result2 = await loadConfig();
        expect(result2).toEqual(fakeConfig);
        expect(fetch).toHaveBeenCalledOnce();
    });

    it('loadConfig sets empty object on fetch error', async () => {
        vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('Network error')));

        const { loadConfig, useConfig } = await importFresh();

        const result = await loadConfig();
        expect(result).toEqual({});
        expect(useConfig().config.value).toEqual({});
    });

    it('loadConfig sets empty object on non-ok response', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
            ok: false,
            status: 500,
        }));

        const { loadConfig } = await importFresh();

        const result = await loadConfig();
        expect(result).toEqual({});
    });

    it('useConfig returns readonly refs', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
            ok: true,
            json: () => Promise.resolve({ key: 'val' }),
        }));

        const { useConfig } = await importFresh();
        const { config, loading } = useConfig();

        config.value = {};
        expect(config.value).not.toEqual({});

        loading.value = true;
        expect(loading.value).toBe(false);
    });

    it('useConfig exposes loadConfig function', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
            ok: true,
            json: () => Promise.resolve({}),
        }));

        const { useConfig, loadConfig } = await importFresh();
        expect(useConfig().loadConfig).toBe(loadConfig);
    });
});
