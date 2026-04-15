export async function fetchFilledOvertime() {
    const res = await fetch("/overtime/list");
    return res.json();
}
