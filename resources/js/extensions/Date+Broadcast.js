function formatTimeDifference(seconds) {
    const secondsInMinute = 60;
    const secondsInHour = 60 * 60;
    const secondsInDay = 24 * 60 * 60;
    const secondsInMonth = 30 * secondsInDay;
    const secondsInYear = 12 * secondsInMonth;

    let result;

    if (seconds >= secondsInYear) {
        const months = Math.floor(seconds / secondsInMonth);
        const days = Math.floor((seconds % secondsInMonth) / secondsInDay);
        result = `${months}M ${days}d`;
    } else if (seconds >= secondsInMonth) {
        const days = Math.floor(seconds / secondsInDay);
        const hours = Math.floor((seconds % secondsInDay) / secondsInHour);
        result = `${days}d ${hours}h`;
    } else if (seconds >= secondsInDay) {
        const days = Math.floor(seconds / secondsInDay);
        const hours = Math.floor((seconds % secondsInDay) / secondsInHour);
        const minutes = Math.floor((seconds % secondsInHour) / secondsInMinute);
        result = `${days}d ${hours}h ${minutes}m`;
    } else if (seconds >= secondsInHour) {
        const hours = Math.floor(seconds / secondsInHour);
        const minutes = Math.floor((seconds % secondsInHour) / secondsInMinute);
        const secs = seconds % secondsInMinute;
        result = `${hours}h ${minutes}m ${secs}s`;
    } else if (seconds >= secondsInMinute) {
        const minutes = Math.floor(seconds / secondsInMinute);
        const secs = seconds % secondsInMinute;
        result = `${minutes}m ${secs}s`;
    } else {
        result = `${seconds}s`;
    }

    return result;
}

export function getTimeUntilOrAgo(broadcastTimestamp, broadcastDuration) {
    const secondsInWeek = 7 * 24 * 60 * 60;
    const currentTimestamp = Date.now()
    let diffInSeconds = Math.floor((broadcastTimestamp - currentTimestamp) / 1000);

    if (diffInSeconds < -broadcastDuration) {
        // After the broadcast duration, switch to the next weekly broadcast
        diffInSeconds += Math.ceil((-diffInSeconds - broadcastDuration) / secondsInWeek) * secondsInWeek;
    }

    if (diffInSeconds > 0) {
        // Future broadcast
        return formatTimeDifference(diffInSeconds) + ' from now';
    }

    // Broadcast happening (within the duration)
    return formatTimeDifference(Math.abs(diffInSeconds)) + ' ago';
}
