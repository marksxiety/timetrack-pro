export async function fetchShiftList(auth) {
    const res = await fetch(`/${auth}/shift/list`);
    return res.json();
}
