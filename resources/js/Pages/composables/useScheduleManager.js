import { ref, computed } from 'vue';
import { to12hr } from '../utils/helpers/date.js';

/**
 * @typedef {Object} ShiftItem
 * @property {number} id
 * @property {string} code
 * @property {string|null} start_time
 * @property {string|null} end_time
 */

/**
 * @typedef {Object} ShiftRefItem
 * @property {number} id
 * @property {string} code
 * @property {string} timeRange
 */

/**
 * @param {import('vue').Ref<ShiftItem[]>} shiftData
 * @returns {import('vue').ComputedRef<ShiftRefItem[]>}
 */
export function buildShiftReference(shiftData) {
    return computed(() => {
        return shiftData.value.map(s => ({
            id: s.id,
            code: s.code,
            timeRange: s.start_time && s.end_time
                ? `${to12hr(s.start_time)} - ${to12hr(s.end_time)}`
                : 'N/A',
        }));
    });
}

/**
 * Check if a schedule row matches the default shift codes.
 * @param {Object[]} schedule - array of schedule day objects
 * @param {string[]} defaultCodes - array of default shift code strings
 * @param {ShiftItem[]} shiftData - full shift data for lookup
 * @param {string} fieldKey - "shift_id" (approver) or "shift_code" (employee)
 * @returns {boolean}
 */
export function isDefaultShift(schedule, defaultCodes, shiftData, fieldKey) {
    if (defaultCodes.length === 0 || defaultCodes.length !== schedule.length) {
        return false;
    }
    return schedule.every((day, idx) => {
        const match = shiftData.find(shift => shift.id === day[fieldKey]);
        return match ? match.code === defaultCodes[idx] : false;
    });
}

/**
 * Fill or clear a schedule row with default shift codes.
 * @param {boolean} checked - whether the "default" checkbox is checked
 * @param {Object[]} targetSchedule - array of schedule day objects
 * @param {string[]} defaultCodes - array of default shift code strings
 * @param {Object[]} shiftOptions - shift dropdown options [{ label, value }]
 * @param {Function} toast - toast notification function
 * @param {string} fieldKey - "shift_id" (approver) or "shift_code" (employee)
 */
export function applyDefaultShiftFill(checked, targetSchedule, defaultCodes, shiftOptions, toast, fieldKey) {
    if (defaultCodes.length === 0) {
        toast('Default shift codes are not configured. Please contact your administrator.', 'error');
        return false;
    }
    if (defaultCodes.length !== targetSchedule.length) {
        toast(`Default shift codes (${defaultCodes.length}) do not match schedule days (${targetSchedule.length}). Please contact your administrator.`, 'error');
        return false;
    }

    if (checked) {
        const defaultIds = defaultCodes.map(code => {
            const match = shiftOptions.find(shift => shift.label.includes(code));
            if (!match) {
                toast(`Shift code "${code}" not found in available shifts.`, 'warning');
            }
            return match ? match.value : null;
        });
        for (let j = 0; j < targetSchedule.length; j++) {
            targetSchedule[j][fieldKey] = defaultIds[j];
        }
    } else {
        for (let j = 0; j < targetSchedule.length; j++) {
            targetSchedule[j][fieldKey] = null;
        }
    }
    return true;
}
