import { describe, it, expect } from 'vitest';
import {
    getStatusBadgeClass,
    identifyColorStatus,
    isRejectedStatus,
    isActiveStatus,
    getStatusBgClass,
} from '../../js/Pages/utils/helpers/status.js';

describe('getStatusBadgeClass', () => {
    it('maps PENDING to badge-warning', () => {
        expect(getStatusBadgeClass('PENDING')).toBe('badge-warning');
    });

    it('maps APPROVED to badge-success', () => {
        expect(getStatusBadgeClass('APPROVED')).toBe('badge-success');
    });

    it('maps FILED to badge-primary', () => {
        expect(getStatusBadgeClass('FILED')).toBe('badge-primary');
    });

    it('maps DECLINED to badge-error', () => {
        expect(getStatusBadgeClass('DECLINED')).toBe('badge-error');
    });

    it('maps DISAPPROVED to badge-error', () => {
        expect(getStatusBadgeClass('DISAPPROVED')).toBe('badge-error');
    });

    it('maps CANCELED to badge-error', () => {
        expect(getStatusBadgeClass('CANCELED')).toBe('badge-error');
    });

    it('returns badge-ghost for unknown status', () => {
        expect(getStatusBadgeClass('UNKNOWN')).toBe('badge-ghost');
    });

    it('returns badge-ghost for empty string', () => {
        expect(getStatusBadgeClass('')).toBe('badge-ghost');
    });
});

describe('identifyColorStatus', () => {
    it('maps pending to warning', () => {
        expect(identifyColorStatus('PENDING')).toBe('warning');
    });

    it('maps canceled to error', () => {
        expect(identifyColorStatus('CANCELED')).toBe('error');
    });

    it('maps disapproved to error', () => {
        expect(identifyColorStatus('DISAPPROVED')).toBe('error');
    });

    it('maps approved to success', () => {
        expect(identifyColorStatus('APPROVED')).toBe('success');
    });

    it('maps filed to primary', () => {
        expect(identifyColorStatus('FILED')).toBe('primary');
    });

    it('returns default for unknown status', () => {
        expect(identifyColorStatus('UNKNOWN')).toBe('default');
    });

    it('is case-insensitive', () => {
        expect(identifyColorStatus('pending')).toBe('warning');
        expect(identifyColorStatus('Approved')).toBe('success');
    });

    it('returns default for empty string', () => {
        expect(identifyColorStatus('')).toBe('default');
    });
});

describe('isRejectedStatus', () => {
    it('returns true for DISAPPROVED', () => {
        expect(isRejectedStatus('DISAPPROVED')).toBe(true);
    });

    it('returns true for CANCELED', () => {
        expect(isRejectedStatus('CANCELED')).toBe(true);
    });

    it('returns true for DECLINED', () => {
        expect(isRejectedStatus('DECLINED')).toBe(true);
    });

    it('returns false for APPROVED', () => {
        expect(isRejectedStatus('APPROVED')).toBe(false);
    });

    it('returns false for PENDING', () => {
        expect(isRejectedStatus('PENDING')).toBe(false);
    });
});

describe('isActiveStatus', () => {
    it('returns true for PENDING', () => {
        expect(isActiveStatus('PENDING')).toBe(true);
    });

    it('returns true for APPROVED', () => {
        expect(isActiveStatus('APPROVED')).toBe(true);
    });

    it('returns true for FILED', () => {
        expect(isActiveStatus('FILED')).toBe(true);
    });

    it('returns false for DECLINED', () => {
        expect(isActiveStatus('DECLINED')).toBe(false);
    });

    it('returns false for DISAPPROVED', () => {
        expect(isActiveStatus('DISAPPROVED')).toBe(false);
    });
});

describe('getStatusBgClass', () => {
    it('maps PENDING to bg-warning', () => {
        expect(getStatusBgClass('PENDING')).toBe('bg-warning');
    });

    it('maps APPROVED to bg-success', () => {
        expect(getStatusBgClass('APPROVED')).toBe('bg-success');
    });

    it('maps FILED to bg-primary', () => {
        expect(getStatusBgClass('FILED')).toBe('bg-primary');
    });

    it('maps DECLINED to bg-error', () => {
        expect(getStatusBgClass('DECLINED')).toBe('bg-error');
    });

    it('maps DISAPPROVED to bg-error', () => {
        expect(getStatusBgClass('DISAPPROVED')).toBe('bg-error');
    });

    it('maps CANCELED to bg-error', () => {
        expect(getStatusBgClass('CANCELED')).toBe('bg-error');
    });

    it('returns bg-ghost for unknown status', () => {
        expect(getStatusBgClass('UNKNOWN')).toBe('bg-ghost');
    });
});
