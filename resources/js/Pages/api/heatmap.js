/**
 * Fetch heatmap data for the overtime visualization.
 * @param {string} startDate - "YYYY-MM-DD"
 * @param {string} endDate - "YYYY-MM-DD"
 * @param {string[]} statuses - array of status filters
 * @param {AbortSignal} [signal] - optional abort controller signal
 * @returns {Promise<Object>}
 */
export async function fetchHeatmapData(startDate, endDate, statuses, signal) {
    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
    });
    statuses.forEach(s => params.append('statuses[]', s));
    const res = await fetch(`/overtime/heatmap?${params.toString()}`, { signal });
    return res.json();
}
