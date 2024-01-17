var timerIntervalHandle: number;

document.addEventListener("DOMContentLoaded", () => {
	// Update the current time every second
	timerIntervalHandle = setInterval(updateCurrentTime, 1000);
	randomColorBackgroundAnimation(document.querySelector("html"), 15000);
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
			day: '2-digit',
		});

		const nthNumber = (number: number) => {
			if (number > 3 && number < 21) return "th";
			switch (number % 10) {
				case 1:
					return "st";
				case 2:
					return "nd";
				case 3:
					return "rd";
				default:
					return "th";
			}
		};
		const ordinalSuffix = nthNumber(now.getDate());

		// Combine the formatted time and date
		const formattedDateTime = `${formattedTime} - ${formattedDate}${ordinalSuffix}`;

		// Update the content of the element
		currentTimeElement.textContent = formattedDateTime;
	}
	else {
		clearInterval(timerIntervalHandle);
	}
}

// function start screensaver
function randomColorBackgroundAnimation(element: HTMLElement, timeout: number = 4000, iterations: number = null, child: boolean = false) {
	element.style.backgroundColor = getNewColor();
	if (!child) {
		element.style.transitionDuration = timeout * 1.5 + "ms";
	}

	if (iterations === null) {
		setTimeout(() => {
			randomColorBackgroundAnimation(element, timeout, null, true);
		}, timeout);
	} else {
		for (let iterator = 0; iterator < iterations; ++iterator) {
			setTimeout(() => {
				randomColorBackgroundAnimation(element, timeout, 1, true);
			}, timeout);
		}
	}

	// change color fuction
	function getNewColor() {
		let myColor = "rgb(" + randomNumber() + ", " + randomNumber() + ", " + randomNumber() + ")";
		return myColor;

		function randomNumber() {
			return Math.floor(Math.random() * 256);
		}
	}
}