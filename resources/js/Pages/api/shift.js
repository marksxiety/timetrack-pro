import axios from "axios";

export function fetchShiftList(auth) {
    return axios.get(`/${auth}/shift/list`);
}
