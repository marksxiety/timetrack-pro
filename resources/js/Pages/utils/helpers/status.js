/**
 * @typedef {'PENDING'|'APPROVED'|'FILED'|'DECLINED'|'DISAPPROVED'|'CANCELED'|'ALL'} OvertimeStatus
 */

const BADGE_MAP = {
    PENDING: 'badge-warning',
    APPROVED: 'badge-success',
    FILED: 'badge-primary',
    DECLINED: 'badge-error',
    DISAPPROVED: 'badge-error',
    CANCELED: 'badge-error',
};

const DEFAULT_BADGE = 'badge-ghost';

/**
 * Map an overtime status string to a DaisyUI badge class.
 * @param {string} status
 * @returns {string}
 */
export function getStatusBadgeClass(status) {
    return BADGE_MAP[status] || DEFAULT_BADGE;
}

/**
 * Map an overtime status string to a color name used by the Stepper component.
 * @param {string} status
 * @returns {string}
 */
export function identifyColorStatus(status) {
    switch (status.toLowerCase()) {
        case 'pending':  return 'warning';
        case 'canceled': return 'error';
        case 'disapproved': return 'error';
        case 'approved': return 'success';
        case 'filed': return 'primary';
        default: return 'default';
    }
}

/** @type {string[]} */
export const REJECTED_STATUSES = ['DISAPPROVED', 'CANCELED', 'DECLINED'];

/** @type {string[]} */
export const ACTIVE_STATUSES = ['PENDING', 'APPROVED', 'FILED'];

/**
 * Check if a status is considered "rejected".
 * @param {string} status
 * @returns {boolean}
 */
export function isRejectedStatus(status) {
    return REJECTED_STATUSES.includes(status);
}

/**
 * Check if a status is considered "active" (still in progress).
 * @param {string} status
 * @returns {boolean}
 */
export function isActiveStatus(status) {
    return ACTIVE_STATUSES.includes(status);
}

const BG_MAP = {
    PENDING: 'bg-warning',
    APPROVED: 'bg-success',
    FILED: 'bg-primary',
    DECLINED: 'bg-error',
    DISAPPROVED: 'bg-error',
    CANCELED: 'bg-error',
};

const DEFAULT_BG = 'bg-ghost';

/**
 * Map an overtime status string to a DaisyUI bg-* class (for colored indicators, dots).
 * @param {string} status
 * @returns {string}
 */
export function getStatusBgClass(status) {
    return BG_MAP[status] || DEFAULT_BG;
}
