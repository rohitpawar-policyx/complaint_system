'use strict';

document.documentElement.classList.add('js');

document.addEventListener('submit', (event) => {
	const message = event.target.dataset.confirm;

	if (message && !window.confirm(message)) {
		event.preventDefault();
	}
});
