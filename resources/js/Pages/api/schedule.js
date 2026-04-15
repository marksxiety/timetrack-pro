export async function fetchSchedule(year, week) {
    const res = await fetch(`/schedule/list?year=${year}&week=${week}`);
    return res.json();
}

export async function fetchEmployeeSchedule(year, week) {
    const res = await fetch(`/schedule/employee/list?year=${year}&week=${week}`);
    return res.json();
}

export async function submitSchedule(info) {
    const res = await fetch("/schedule/submit", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ schedule: info }),
    });
    return res.json();
}

export async function submitEmployeeSchedule(info) {
    const res = await fetch("/schedule/employee/submit", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ schedule: info }),
    });
    return res.json();
}

export async function fetchUserSchedule(year, month, day) {
    const res = await fetch(`/schedule/user?year=${year}&month=${month}&day=${day}`);
    return res.json();
}
