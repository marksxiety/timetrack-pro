import { currentWeek, getWeeksInMonth, getTimeOptions } from './helpers/date.js';

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

export const weeks = [];
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

export { currentWeek, getWeeksInMonth, getTimeOptions };

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
