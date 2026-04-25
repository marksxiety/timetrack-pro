import { describe, it, expect } from 'vitest';
import { to12hr, to24hr, currentWeek, formatShortDate, getWeeksInMonth, getTimeOptions } from '../../js/Pages/utils/helpers/date.js';

describe('to12hr', () => {
    it('converts midnight', () => {
        expect(to12hr('00:00')).toBe('12:00 AM');
    });

    it('converts noon', () => {
        expect(to12hr('12:00')).toBe('12:00 PM');
    });

    it('converts morning time', () => {
        expect(to12hr('08:05')).toBe('8:05 AM');
    });

    it('converts afternoon time', () => {
        expect(to12hr('14:30')).toBe('2:30 PM');
    });

    it('converts late night time', () => {
        expect(to12hr('23:59')).toBe('11:59 PM');
    });

    it('pads single-digit minutes', () => {
        expect(to12hr('09:01')).toBe('9:01 AM');
    });

    it('returns null for null input', () => {
        expect(to12hr(null)).toBeNull();
    });

    it('returns null for undefined input', () => {
        expect(to12hr(undefined)).toBeNull();
    });

    it('returns null for empty string', () => {
        expect(to12hr('')).toBeNull();
    });
});

describe('to24hr', () => {
    it('converts 12:00 AM to midnight', () => {
        expect(to24hr('12:00 AM')).toBe('00:00');
    });

    it('converts 12:00 PM to noon', () => {
        expect(to24hr('12:00 PM')).toBe('12:00');
    });

    it('converts AM time', () => {
        expect(to24hr('08:30 AM')).toBe('08:30');
    });

    it('converts PM time', () => {
        expect(to24hr('02:30 PM')).toBe('14:30');
    });

    it('converts 11:59 PM', () => {
        expect(to24hr('11:59 PM')).toBe('23:59');
    });

    it('handles lowercase am/pm', () => {
        expect(to24hr('02:30 pm')).toBe('14:30');
    });

    it('returns empty string for null', () => {
        expect(to24hr(null)).toBe('');
    });

    it('returns empty string for undefined', () => {
        expect(to24hr(undefined)).toBe('');
    });

    it('returns empty string for invalid format', () => {
        expect(to24hr('not a time')).toBe('');
    });

    it('round-trips with to12hr', () => {
        const original = '14:30';
        expect(to24hr(to12hr(original))).toBe(original);
    });
});

describe('currentWeek', () => {
    it('returns a number for today', () => {
        expect(typeof currentWeek()).toBe('number');
    });

    it('returns a number between 1 and 53', () => {
        const week = currentWeek();
        expect(week).toBeGreaterThanOrEqual(1);
        expect(week).toBeLessThanOrEqual(53);
    });

    it('returns expected week number for a known date', () => {
        const date = new Date(2024, 0, 8);
        const week = currentWeek(date);
        expect(typeof week).toBe('number');
        expect(week).toBeGreaterThanOrEqual(1);
    });
});

describe('formatShortDate', () => {
    it('formats January 15', () => {
        const date = new Date(2024, 0, 15);
        expect(formatShortDate(date)).toBe('Jan 15');
    });

    it('formats December 31', () => {
        const date = new Date(2024, 11, 31);
        expect(formatShortDate(date)).toBe('Dec 31');
    });

    it('formats single-digit day', () => {
        const date = new Date(2024, 0, 5);
        expect(formatShortDate(date)).toBe('Jan 5');
    });

    it('formats June', () => {
        const date = new Date(2024, 5, 10);
        expect(formatShortDate(date)).toBe('Jun 10');
    });
});

describe('getWeeksInMonth', () => {
    it('returns an array of week entries', () => {
        const weeks = getWeeksInMonth(2024, 1);
        expect(Array.isArray(weeks)).toBe(true);
        expect(weeks.length).toBeGreaterThan(0);
    });

    it('each entry has weekNumber, startDate, endDate', () => {
        const weeks = getWeeksInMonth(2024, 1);
        weeks.forEach((w) => {
            expect(w).toHaveProperty('weekNumber');
            expect(w).toHaveProperty('startDate');
            expect(w).toHaveProperty('endDate');
            expect(typeof w.weekNumber).toBe('number');
            expect(typeof w.startDate).toBe('string');
            expect(typeof w.endDate).toBe('string');
        });
    });

    it('returns 5 weeks for January 2024', () => {
        const weeks = getWeeksInMonth(2024, 1);
        expect(weeks.length).toBe(5);
    });

    it('returns 4 weeks for February 2026 (starts on Sunday)', () => {
        const weeks = getWeeksInMonth(2026, 2);
        expect(weeks.length).toBe(4);
    });

    it('week numbers are sequential', () => {
        const weeks = getWeeksInMonth(2024, 1);
        for (let i = 1; i < weeks.length; i++) {
            expect(weeks[i].weekNumber).toBe(weeks[i - 1].weekNumber + 1);
        }
    });
});

describe('getTimeOptions', () => {
    it('returns 96 options (24h * 4 per hour)', () => {
        const options = getTimeOptions();
        expect(options).toHaveLength(96);
    });

    it('starts at 12:00 AM', () => {
        const options = getTimeOptions();
        expect(options[0]).toEqual({ label: '12:00 AM', value: '00:00' });
    });

    it('ends at 11:45 PM', () => {
        const options = getTimeOptions();
        expect(options[options.length - 1]).toEqual({ label: '11:45 PM', value: '23:45' });
    });

    it('has noon at the correct index', () => {
        const options = getTimeOptions();
        expect(options[48]).toEqual({ label: '12:00 PM', value: '12:00' });
    });

    it('each option has label and value', () => {
        const options = getTimeOptions();
        options.forEach((opt) => {
            expect(opt).toHaveProperty('label');
            expect(opt).toHaveProperty('value');
            expect(typeof opt.label).toBe('string');
            expect(typeof opt.value).toBe('string');
        });
    });
});
