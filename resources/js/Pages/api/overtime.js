export async function fetchHeatmapData(year) {
    const res = await fetch(`/overtime/heatmap?year=${year}`);
    return res.json();
}
