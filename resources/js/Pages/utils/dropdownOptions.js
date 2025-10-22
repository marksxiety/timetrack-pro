const currentYear = new Date().getFullYear();
export const years = [];
for (let i = 0; i < 7; i++) {
    const year = currentYear - 1 + i;
    years.push({
        label: String(year),
        value: String(year),
    });
}

export const weeks = [{ label: "All Weeks", value: "" }];
for (let w = 1; w <= 52; w++) {
    weeks.push({
        label: `Week ${w}`,
        value: w,
    });
}

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
        (pastDaysOfYear + firstDayOfYear.getDay()) / 7
    );
    return weekNumber;
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
