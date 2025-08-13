var sidebarNav = document.querySelectorAll('.sidebar-nav-item.current-section');
var rnavArrow = document.querySelectorAll('.sidebar-nav-item.current-section > .rnav-arrow');

sidebarNav.forEach(sidebarNav => {
	var subList = document.querySelectorAll('.sidebar-nav-item.current-section .sub-list');
	subList.forEach(subList => {
		if (sidebarNav) {
			subList.classList.add('current-nav');
			subList.setAttribute('aria-expanded', 'true');
		}
	});

	var subListPrevious = document.querySelector('.sidebar-nav-item.current-section').closest('ul');
	if (sidebarNav) {
		subListPrevious.classList.add('current-nav');
		subListPrevious.setAttribute('aria-expanded', 'true');
	}

	rnavArrow.forEach(rnavArrow => {
		if (sidebarNav) {
			rnavArrow.classList.add('active');
			rnavArrow.setAttribute('aria-expanded', 'true');
			rnavArrow.addEventListener('click', function() {
				if (rnavArrow.classList.contains('active')) {
					rnavArrow.classList.remove('active');
					rnavArrow.setAttribute('aria-expanded', 'false');
				} else {
					rnavArrow.classList.add('active');
					rnavArrow.setAttribute('aria-expanded', 'true');
				}
			});
		}
	});
});
