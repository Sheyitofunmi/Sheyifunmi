// show hide main mobile navigation
var rnavButt = document.querySelector('.menu-button');
var rnavButtClose = document.querySelector('.menu-button-close');

var rnav = document.querySelector('.rnav-outer');
var rnavArrow = document.querySelectorAll('.rnav-arrow');

rnavButt.addEventListener('click', function() { rnav.classList.contains('show-nav') ? navUp(rnav, rnavButt) : navDown(rnav, rnavButt); });
rnavButtClose.addEventListener('click', function() { rnav.classList.contains('show-nav') ? navUp(rnav, rnavButt) : navDown(rnav, rnavButt); });

// Enter key aria controls

rnavButt.addEventListener('keydown', function(event) {
	if(event.keyCode != 13) { return; }
	 setAttributes([rnav, rnavButt], 'aria-expanded', 'true');
});
rnavButtClose.addEventListener('keydown', function(event) {
	if(event.keyCode != 13) { return; }
	 setAttributes([rnav, rnavButtClose], 'aria-expanded', 'true');
});

// Mobile navigation dropdown

rnavArrow.forEach(rnavArrow => {
	var rnavDropdown = rnavArrow.nextElementSibling;
	rnavArrow.addEventListener('click', function() { rnavDropdown.classList.contains('show-nav') ? navUp(rnavDropdown, rnavArrow) : navDown(rnavDropdown, rnavArrow); });
});

// Utility functions

function navUp(e1, e2) {
	e1.classList.remove('show-nav');
	e2.classList.remove('active');
	setAttributes([e1, e2], 'aria-expanded', 'false');
}
function navDown(e1, e2) {
	e1.classList.add('show-nav');
	e2.classList.add('active');
	setAttributes([e1, e2], 'aria-expanded', 'true');
}

function setAttributes(elements, label, value) {
	elements.forEach(element => {
		element.setAttribute(label, value);
	});
}

var rnavClose = document.querySelectorAll('.close-child');
var rnavChildren = document.querySelectorAll('.children');

if (rnavChildren) {
	rnavClose.forEach(rnavClose => {
		rnavClose.addEventListener('click', function() {
			rnavChildren.forEach(rnavChildren => {
				rnavChildren.classList.remove('show-nav');
			});
		});
	});
	rnavButtClose.addEventListener('click', function() {
		rnavChildren.forEach(rnavChildren => {
			rnavChildren.classList.remove('show-nav');
		});
	});
}

var rnavCloseGran = document.querySelectorAll('.close-gran');
var rnavGranchildren = document.querySelectorAll('.granchildren');

if (rnavGranchildren) {
	rnavCloseGran.forEach(rnavCloseGran => {
		rnavCloseGran.addEventListener('click', function() {
			rnavGranchildren.forEach(rnavGranchildren => {
				rnavGranchildren.classList.remove('show-nav');
			});
		});
	});
	rnavButtClose.addEventListener('click', function() {
		rnavGranchildren.forEach(rnavGranchildren => {
			rnavGranchildren.classList.remove('show-nav');
		});
	});
}
