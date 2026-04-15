const currentYear = new Date().getFullYear()
const currentDate = new Date().toLocaleDateString("en-CA", {
    timeZone: "Asia/Manila",
})

function formatHolidayDate(date) {
    const dateInstance = new Date(date)
    return dateInstance.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    })
}

const url = `https://date.nager.at/api/v3/publicholidays/${currentYear}/PH`

export default async function fetchUpcomingHolidays() {
    let upcoming_holidays = []
    try {
        const res = await fetch(url)
        const response = await res.json()

        if (Array.isArray(response) && response.length > 0) {
            upcoming_holidays = response
                .filter((holiday) => new Date(holiday.date) >= new Date(currentDate))
                .map((holiday) => ({
                    date: formatHolidayDate(holiday.date),
                    rawDate: holiday.date,
                    localName: holiday.localName,
                    name: holiday.name,
                }))
        }

        return { success: true, holidays: upcoming_holidays }
    } catch (error) {
        return { success: false, holidays: upcoming_holidays }
    }
}
