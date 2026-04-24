import { getCsrfToken } from '../utils/helpers/csrf.js';

/**
 * Fetch the approver's employee schedule for a given year and week.
 * @param {number} year
 * @param {number} week
 * @returns {Promise<Object>}
 */
export async function fetchEmployeeSchedule(year, week) {
    const res = await fetch(`/schedule/employee/list?year=${year}&week=${week}`);
    return res.json();
}

/**
 * Fetch the employee's own schedule for a given year and week.
 * @param {number} year
 * @param {number} week
 * @returns {Promise<Object>}
 */
export async function fetchSchedule(year, week) {
    const res = await fetch(`/schedule/list?year=${year}&week=${week}`);
    return res.json();
}

/**
 * Submit an employee's schedule entries.
 * @param {Object[]} info - array of schedule day objects
 * @returns {Promise<Object>}
 */
export async function submitSchedule(info) {
    const res = await fetch("/schedule/submit", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-XSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({ schedule: info }),
    });
    return res.json();
}

/**
 * Submit an employee schedule from the approver view.
 * @param {Object[]} info - array of weekly schedule objects
 * @returns {Promise<Object>}
 */
export async function submitEmployeeSchedule(info) {
    const res = await fetch("/schedule/employee/submit", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-XSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({ schedule: info }),
    });
    return res.json();
}

/**
 * Fetch the schedule for a specific user on a specific date.
 * @param {number} year
 * @param {number} month - 1-based
 * @param {number} day
 * @returns {Promise<Object>}
 */
export async function fetchUserSchedule(year, month, day) {
    const res = await fetch(`/schedule/user?year=${year}&month=${month}&day=${day}`);
    return res.json();
}
