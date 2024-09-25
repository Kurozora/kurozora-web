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
    const currentTimestamp = Date.now()
    const diffInSeconds = Math.floor((broadcastTimestamp - currentTimestamp) / 1000);

    if (diffInSeconds > 0) {
        // Future broadcast
        return formatTimeDifference(diffInSeconds) + ' from now';
    } else if (diffInSeconds <= 0 && Math.abs(diffInSeconds) <= broadcastDuration) {
        // Broadcast happening (within the duration)
        return formatTimeDifference(Math.abs(diffInSeconds)) + ' ago';
    } else {
        // After the broadcast duration, switch back to counting until next broadcast
        const nextBroadcastInSeconds = broadcastDuration + diffInSeconds;
        return formatTimeDifference(nextBroadcastInSeconds) + ' from now';
    }
}
