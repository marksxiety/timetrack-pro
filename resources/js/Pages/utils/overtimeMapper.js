export function getEmployeeOvertimeStats(requests) {
    let totalovertimehours = 0;
    let pendingrequests = 0;
    let rejectedrequests = 0;
    let approvedrequests = 0;

    requests.forEach((item) => {
        if (item.status.toUpperCase() === "PENDING") {
            pendingrequests++;
        }

        if (item.status.toUpperCase() === "DISAPPROVED") {
            rejectedrequests++;
        }

        if (item.status.toUpperCase() === "APPROVED" || item.status.toUpperCase() === "FILED" ) {
            approvedrequests++;
            totalovertimehours += item.hours;
        }
    });

    totalovertimehours = totalovertimehours.toFixed(2).replace(/\.00$/, "");

    return {
        totalovertimehours,
        approvedrequests,
        pendingrequests,
        rejectedrequests,
    };
}
