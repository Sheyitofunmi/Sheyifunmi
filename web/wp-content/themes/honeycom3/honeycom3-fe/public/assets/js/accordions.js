// Accordions
// Get all accordion triggers
const accordionTrigger = document.querySelectorAll('.accordion-trigger');

// Loop over accordion triggers
accordionTrigger.forEach(trigger => {
	// Add event listener to each one
	trigger.addEventListener('click', function(event) {
		const currentAccordion = trigger === event.target ? event.target : event.target.closest('.accordion-trigger');
		const currentPanel = currentAccordion.nextElementSibling;

		currentAccordion.classList.toggle('active');
		utils.slideToggle(currentPanel, '500');
		utils.toggleAria(currentPanel);
		utils.toggleAria(currentAccordion);
	})
})

// Setting filter inner Aria hidden to true on desktop
const filterInner = document.querySelector('.filter-inner');

if (filterInner) {
	var actual_width = window.innerWidth;

	if(actual_width < 700) {
		filterInner.setAttribute('aria-expanded', 'false');
	} else if (actual_width >= 700) {
		filterInner.removeAttribute('aria-expanded');
	}
}

// Setting sidebar inner Aria hidden to true on desktop
const sidebarInner = document.querySelectorAll('.sidebar-accordion-panel');

if (sidebarInner) {
	var actual_width = window.innerWidth;

	sidebarInner.forEach(sidebarInner => {
		if(actual_width < 700) {
			sidebarInner.setAttribute('aria-expanded', 'false');
		} else if (actual_width >= 700) {
			sidebarInner.removeAttribute('aria-expanded');
		}
	});
}
