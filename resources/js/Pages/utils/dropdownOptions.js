const currentYear = new Date().getFullYear();
const currentWeekNum = currentWeek();
const currentMonth = new Date().getMonth() + 1;
export const years = [];
for (let i = 0; i < 7; i++) {
    const year = currentYear - 1 + i;
    const isCurrent = year === currentYear;
    years.push({
        label: isCurrent ? `${year} **` : String(year),
        value: String(year),
    });
}

export const weeks = [{ label: "All Weeks", value: "" }];
for (let w = 1; w <= 52; w++) {
    const isCurrent = w === currentWeekNum;
    weeks.push({
        label: isCurrent ? `Week ${w} **` : `Week ${w}`,
        value: w,
    });
}

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
export const months = monthNames.map((name, idx) => {
    const m = idx + 1;
    return {
        label: m === currentMonth ? `${name} **` : name,
        value: m,
    };
});

export function currentWeek(date = new Date()) {
    const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
    const pastDaysOfYear =
        (date -
            firstDayOfYear +
            (firstDayOfYear.getTimezoneOffset() - date.getTimezoneOffset()) *
                60 *
                1000) /
        86400000;

    // adjust if the starting of the week is sunday or monday
    // just add + 1 if starting on monday
    const weekNumber = Math.ceil(
        (pastDaysOfYear + firstDayOfYear.getDay()) / 7,
    );
    return weekNumber;
}

function formatShortDate(date) {
    const names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${names[date.getMonth()]} ${date.getDate()}`;
}

function getWeekNumberForYear(date, year) {
    const jan1 = new Date(year, 0, 1);
    const yearStartSunday = new Date(jan1);
    yearStartSunday.setDate(yearStartSunday.getDate() - jan1.getDay());
    return Math.floor(Math.round((date - yearStartSunday) / 86400000) / 7) + 1;
}

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

export function getTimeOptions() {
    const times = [];
    const pad = (n) => (n < 10 ? "0" + n : n);

    for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 15) {
            const hour12 = h % 12 || 12;
            const ampm = h < 12 ? "AM" : "PM";

            times.push({
                label: `${pad(hour12)}:${pad(m)} ${ampm}`,
                value: `${pad(h)}:${pad(m)}`,
            });
        }
    }
    return times;
}

export const statuses = [
    { label: "All", value: "ALL" },
    { label: "Filed", value: "FILED" },
    { label: "Pending", value: "PENDING" },
    { label: "Approved", value: "APPROVED" },
    { label: "Declined", value: "DECLINED" },
    { label: "Canceled", value: "CANCELED" },
    { label: "Disapproved", value: "DISAPPROVED" },
];

export const sortOptions = [
    { label: "Newest First", value: "date_desc" },
    { label: "Oldest First", value: "date_asc" },
    { label: "Status A-Z", value: "status_asc" },
    { label: "Status Z-A", value: "status_desc" },
];
