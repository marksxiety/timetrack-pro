export async function fetchHeatmapData(startDate, endDate, signal) {
    const res = await fetch(`/overtime/heatmap?start_date=${startDate}&end_date=${endDate}`, { signal });
    return res.json();
}
