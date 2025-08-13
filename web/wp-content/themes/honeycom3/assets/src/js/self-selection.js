var selfSelections = document.querySelectorAll('.self-selection-inner');

if (selfSelections) {
	selfSelections.forEach(selfSelect => {
		selfSelect.querySelector('.audience_selector_button').addEventListener('click', function() {
			window.location = selfSelect.querySelector('.audience-selector').value;
		});
	});
}
