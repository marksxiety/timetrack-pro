import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

describe('dropdownOptions', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date(2024, 5, 15));
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('exports 7 years starting from previous year', async () => {
        vi.resetModules();
        const { years } = await import('../../js/Pages/utils/dropdownOptions.js');

        expect(years).toHaveLength(7);
        expect(years[0]).toEqual({ label: '2023', value: '2023' });
        expect(years[1]).toEqual({ label: '2024 **', value: '2024' });
        expect(years[6]).toEqual({ label: '2029', value: '2029' });
    });

    it('marks current year with **', async () => {
        vi.resetModules();
        const { years } = await import('../../js/Pages/utils/dropdownOptions.js');

        const current = years.find((y) => y.value === '2024');
        expect(current.label).toContain('**');
    });

    it('exports 52 weeks', async () => {
        vi.resetModules();
        const { weeks } = await import('../../js/Pages/utils/dropdownOptions.js');

        expect(weeks).toHaveLength(52);
        expect(weeks[0]).toEqual({ label: 'Week 1', value: 1 });
        expect(weeks[51]).toEqual({ label: 'Week 52', value: 52 });
    });

    it('marks current week with **', async () => {
        vi.resetModules();
        const { currentWeek, weeks } = await import('../../js/Pages/utils/dropdownOptions.js');

        const cw = currentWeek();
        const current = weeks.find((w) => w.value === cw);
        expect(current.label).toContain('**');
    });

    it('exports 12 months', async () => {
        vi.resetModules();
        const { months } = await import('../../js/Pages/utils/dropdownOptions.js');

        expect(months).toHaveLength(12);
        expect(months[0]).toEqual({ label: 'January', value: 1 });
        expect(months[11]).toEqual({ label: 'December', value: 12 });
    });

    it('marks current month with **', async () => {
        vi.resetModules();
        const { months } = await import('../../js/Pages/utils/dropdownOptions.js');

        const current = months.find((m) => m.value === 6);
        expect(current.label).toContain('**');
    });

    it('exports statuses with expected values', async () => {
        vi.resetModules();
        const { statuses } = await import('../../js/Pages/utils/dropdownOptions.js');

        expect(statuses).toHaveLength(7);
        expect(statuses.map((s) => s.value)).toEqual(
            expect.arrayContaining(['ALL', 'FILED', 'PENDING', 'APPROVED', 'DECLINED', 'CANCELED', 'DISAPPROVED']),
        );
    });

    it('exports sort options', async () => {
        vi.resetModules();
        const { sortOptions } = await import('../../js/Pages/utils/dropdownOptions.js');

        expect(sortOptions).toHaveLength(4);
        expect(sortOptions[0]).toEqual({ label: 'Newest First', value: 'date_desc' });
    });
});
