
// Function to convert 12-hour time to 24-hour format
function convertTo24HourFormat(datetime) {
    // Split the datetime into date and time components
    let [date, time, period] = datetime.split(' ');

    // Split time into hour, minute, and second
    let [hour, minute, second] = time.split(':');

    // Convert hour to 24-hour format
    if (period === 'PM' && hour !== '12') {
        hour = parseInt(hour, 10) + 12;
    } else if (period === 'AM' && hour === '12') {
        hour = '00';
    }

    // Reconstruct the datetime in 24-hour format
    return `${date} ${hour}:${minute}:${second}`;
}