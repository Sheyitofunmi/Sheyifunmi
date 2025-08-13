// Accordions
// Get all accordion triggers
const accordionSidebarTrigger = document.querySelectorAll('.sidebar-accordion-trigger');

// Loop over accordion triggers
accordionSidebarTrigger.forEach(trigger => {
	// Add event listener to each one
	trigger.addEventListener('click', function(event) {
		const currentSidebarAccordion = trigger === event.target ? event.target : event.target.closest('.sidebar-accordion-trigger');
		const currentSidebarPanel = currentSidebarAccordion.nextElementSibling;

		currentSidebarAccordion.classList.toggle('active');
		utils.slideToggle(currentSidebarPanel, '500');
		utils.toggleAria(currentSidebarPanel);
		utils.toggleAria(currentSidebarAccordion);
	})
});
