const currentYear = new Date().getFullYear();
const currentDate = new Date().toLocaleDateString("en-CA", {
    timeZone: "Asia/Manila",
});

/**
 * Format a date string into a human-readable holiday format.
 * @param {string} date - ISO date string, e.g. "2026-01-01"
 * @returns {string} - e.g. "January 1, 2026"
 */
function formatHolidayDate(date) {
    const dateInstance = new Date(date);
    return dateInstance.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
}

/** @type {string} */
const url = `https://date.nager.at/api/v3/publicholidays/${currentYear}/PH`;

/**
 * @typedef {Object} Holiday
 * @property {string} date - formatted date string
 * @property {string} rawDate - ISO date string
 * @property {string} localName - local Philippine name
 * @property {string} name - English name
 */

/**
 * Fetch upcoming Philippine public holidays from the current date onward.
 * @returns {Promise<{ success: boolean, holidays: Holiday[] }>}
 */
export default async function fetchUpcomingHolidays() {
    /** @type {Holiday[]} */
    let upcoming_holidays = [];
    try {
        const res = await fetch(url);
        const response = await res.json();

        if (Array.isArray(response) && response.length > 0) {
            upcoming_holidays = response
                .filter((holiday) => new Date(holiday.date) >= new Date(currentDate))
                .map((holiday) => ({
                    date: formatHolidayDate(holiday.date),
                    rawDate: holiday.date,
                    localName: holiday.localName,
                    name: holiday.name,
                }));
        }

        return { success: true, holidays: upcoming_holidays };
    } catch (error) {
        return { success: false, holidays: upcoming_holidays };
    }
}
