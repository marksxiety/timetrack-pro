/**
 * Convert 24-hour "HH:mm" to 12-hour "h:mm AM/PM" format.
 * @param {string|null|undefined} time - e.g. "14:30"
 * @returns {string|null}
 */
export function to12hr(time) {
    if (!time) return null;
    const [h, m] = time.split(':').map(Number);
    const period = h >= 12 ? 'PM' : 'AM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${period}`;
}

/**
 * Convert 12-hour "h:mm AM/PM" to 24-hour "HH:mm" format.
 * @param {string|null|undefined} timestamp - e.g. "08:00 PM"
 * @returns {string}
 */
export function to24hr(timestamp) {
    if (!timestamp) return '';
    const match = timestamp.match(/(\d{1,2}):(\d{2})\s?(AM|PM)/i);
    if (!match) return '';
    let [, hour, minute, period] = match;
    hour = parseInt(hour, 10);
    minute = parseInt(minute, 10);
    if (period.toUpperCase() === 'PM' && hour !== 12) hour += 12;
    if (period.toUpperCase() === 'AM' && hour === 12) hour = 0;
    return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
}

/**
 * Get the current week number (Sunday start).
 * @param {Date} [date=new Date()]
 * @returns {number}
 */
export function currentWeek(date = new Date()) {
    const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
    const pastDaysOfYear =
        (date -
            firstDayOfYear +
            (firstDayOfYear.getTimezoneOffset() - date.getTimezoneOffset()) *
                60 *
                1000) /
        86400000;
    return Math.ceil(
        (pastDaysOfYear + firstDayOfYear.getDay()) / 7,
    );
}

/**
 * Format date as short string "Jan 15".
 * @param {Date} date
 * @returns {string}
 */
export function formatShortDate(date) {
    const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${names[date.getMonth()]} ${date.getDate()}`;
}

/**
 * @param {Date} date
 * @param {number} year
 * @returns {number}
 */
function getWeekNumberForYear(date, year) {
    const jan1 = new Date(year, 0, 1);
    const yearStartSunday = new Date(jan1);
    yearStartSunday.setDate(yearStartSunday.getDate() - jan1.getDay());
    return Math.floor(Math.round((date - yearStartSunday) / 86400000) / 7) + 1;
}

/**
 * @typedef {Object} WeekEntry
 * @property {number} weekNumber
 * @property {string} startDate
 * @property {string} endDate
 */

/**
 * Get all Sunday-Saturday weeks that fall within a given month.
 * @param {number} year
 * @param {number} month - 1-based (1 = January)
 * @returns {WeekEntry[]}
 */
export function getWeeksInMonth(year, month) {
    const weeks = [];
    const firstOfMonth = new Date(year, month - 1, 1);
    const lastOfMonth = new Date(year, month, 0);
    let sunday = new Date(firstOfMonth);
    sunday.setDate(sunday.getDate() - sunday.getDay());
    while (sunday <= lastOfMonth) {
        const saturday = new Date(sunday);
        saturday.setDate(saturday.getDate() + 6);
        weeks.push({
            weekNumber: getWeekNumberForYear(sunday, year),
            startDate: formatShortDate(sunday),
            endDate: formatShortDate(saturday),
        });
        sunday.setDate(sunday.getDate() + 7);
    }
    return weeks;
}

/**
 * @typedef {Object} TimeOption
 * @property {string} label - e.g. "2:30 PM"
 * @property {string} value - e.g. "14:30"
 */

/**
 * Generate 15-minute interval time options for a full day.
 * @returns {TimeOption[]}
 */
export function getTimeOptions() {
    const times = [];
    const pad = (n) => (n < 10 ? '0' + n : n);
    for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 15) {
            const hour12 = h % 12 || 12;
            const ampm = h < 12 ? 'AM' : 'PM';
            times.push({
                label: `${pad(hour12)}:${pad(m)} ${ampm}`,
                value: `${pad(h)}:${pad(m)}`,
            });
        }
    }
    return times;
}
