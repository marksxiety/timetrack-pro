import { getCsrfToken } from '../utils/helpers/csrf.js';

/**
 * Submit a single overtime request (JSON API, no Inertia redirect).
 * @param {Object} data
 * @param {string} data.employee_schedule_id
 * @param {string} data.date - Y-m-d format
 * @param {string} data.start_time - H:i format
 * @param {string} data.end_time - H:i format
 * @param {string} data.reason
 * @returns {Promise<{success: boolean, message?: string, errors?: string[]}>}
 */
export async function submitBulkOvertime(data) {
    const res = await fetch('/overtime/request/bulk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({
            employee_schedule_id: data.employee_schedule_id,
            date: data.date,
            start_time: data.start_time,
            end_time: data.end_time,
            reason: data.reason,
        }),
    });

    if (res.ok) {
        return await res.json();
    }

    const body = await res.json().catch(() => ({}));
    return {
        success: false,
        errors: body.errors || { _general: 'An unexpected error occurred.' },
    };
}
