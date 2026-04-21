export async function fetchHeatmapData(startDate, endDate, statuses, signal) {
    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
    });
    statuses.forEach(s => params.append('statuses[]', s));
    const res = await fetch(`/overtime/heatmap?${params.toString()}`, { signal });
    return res.json();
}
