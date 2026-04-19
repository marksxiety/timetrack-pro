export async function fetchHeatmapData(startDate, endDate) {
    const res = await fetch(`/overtime/heatmap?start_date=${startDate}&end_date=${endDate}`);
    return res.json();
}
