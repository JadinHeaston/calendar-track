var timerIntervalHandle: number;

document.addEventListener("DOMContentLoaded", () => {
	initSelect2Inputs();
	// Update the current time every second
	timerIntervalHandle = setInterval(updateCurrentTime, 1000);

	// //Confetti!!!!
	// document.querySelectorAll("canvas.confetti").forEach((connfettiCanvas) => {
	// 	if (!(connfettiCanvas instanceof HTMLCanvasElement))
	// 		return false;
	// 	showConfetti(connfettiCanvas);
	// });

});

document.addEventListener('htmx:afterRequest', function (evt) {
	initSelect2Inputs();
});

//Initializes all select2 question inputs.
async function initSelect2Inputs() {
	var select2Inputs = document.querySelectorAll('select.select2');
	select2Inputs.forEach((element) => {
		jQuery(element).select2();

		//Manually focusing the search field when opened.
		jQuery(element).on('select2:open', () => {
			let select = document.querySelector('.select2-search__field') as HTMLSelectElement;
			select.focus();
		});
	});
}

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
