/**
 * Fetch the list of shift codes for a given auth role.
 * @param {'approver'|'employee'} auth
 * @returns {Promise<Object>}
 */
export async function fetchShiftList(auth) {
    const res = await fetch(`/${auth}/shift/list`);
    return res.json();
}
