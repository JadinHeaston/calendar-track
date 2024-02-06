var timerIntervalHandle: number;

document.addEventListener("DOMContentLoaded", () => {
	// Update the current time every second
	timerIntervalHandle = setInterval(updateCurrentTime, 1000);
});

// TypeScript function to update current time
function updateCurrentTime() {
	const currentTimeElement = document.getElementById('current-time');
	if (currentTimeElement !== null) {
		const now = new Date();

		// Format the time
		const formattedTime = now.toLocaleTimeString('en-US', {
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
			hour12: true,
		});

		// Format the date
		const formattedDate = now.toLocaleDateString('en-US', {
			weekday: 'long',
			month: 'long',
			day: 'numeric',
			year: 'numeric',
		});

		// Combine the formatted time and date
		const formattedDateTime = `${formattedTime} - ${formattedDate}`;

		// Update the content of the element
		currentTimeElement.textContent = formattedDateTime;
	}
	else {
		clearInterval(timerIntervalHandle);
	}
}
