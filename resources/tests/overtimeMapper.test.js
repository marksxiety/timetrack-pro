import { describe, it, expect } from 'vitest';
import { getEmployeeOvertimeStats } from '../js/Pages/utils/overtimeMapper.js';

describe('getEmployeeOvertimeStats', () => {
    it('returns zeros for empty array', () => {
        const result = getEmployeeOvertimeStats([]);
        expect(result).toEqual({
            totalovertimehours: '0',
            approvedrequests: 0,
            pendingrequests: 0,
            rejectedrequests: 0,
        });
    });

    it('counts pending requests', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'PENDING', hours: 2 },
            { status: 'PENDING', hours: 3 },
        ]);
        expect(result.pendingrequests).toBe(2);
    });

    it('counts rejected requests (DISAPPROVED)', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'DISAPPROVED', hours: 1 },
        ]);
        expect(result.rejectedrequests).toBe(1);
    });

    it('counts approved requests and sums hours', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'APPROVED', hours: 2.5 },
            { status: 'APPROVED', hours: 1.5 },
        ]);
        expect(result.approvedrequests).toBe(2);
        expect(result.totalovertimehours).toBe('4');
    });

    it('counts filed requests as approved and sums hours', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'FILED', hours: 3 },
        ]);
        expect(result.approvedrequests).toBe(1);
        expect(result.totalovertimehours).toBe('3');
    });

    it('handles mixed statuses', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'APPROVED', hours: 2 },
            { status: 'PENDING', hours: 1 },
            { status: 'DISAPPROVED', hours: 3 },
            { status: 'FILED', hours: 4 },
            { status: 'PENDING', hours: 2 },
        ]);
        expect(result.approvedrequests).toBe(2);
        expect(result.pendingrequests).toBe(2);
        expect(result.rejectedrequests).toBe(1);
        expect(result.totalovertimehours).toBe('6');
    });

    it('is case-insensitive for status', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'pending', hours: 1 },
            { status: 'approved', hours: 2 },
            { status: 'disapproved', hours: 3 },
        ]);
        expect(result.pendingrequests).toBe(1);
        expect(result.approvedrequests).toBe(1);
        expect(result.rejectedrequests).toBe(1);
    });

    it('strips .00 from total hours', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'APPROVED', hours: 5 },
        ]);
        expect(result.totalovertimehours).toBe('5');
    });

    it('keeps decimal places for non-whole hours', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'APPROVED', hours: 2.5 },
        ]);
        expect(result.totalovertimehours).toBe('2.50');
    });

    it('does not sum hours for pending or rejected requests', () => {
        const result = getEmployeeOvertimeStats([
            { status: 'PENDING', hours: 10 },
            { status: 'DISAPPROVED', hours: 20 },
        ]);
        expect(result.totalovertimehours).toBe('0');
    });
});
