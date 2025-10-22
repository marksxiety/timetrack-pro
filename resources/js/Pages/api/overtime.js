import axios from "axios";

export function fetchFilledOvertime() {
    return axios.get("/overtime/list");
}
