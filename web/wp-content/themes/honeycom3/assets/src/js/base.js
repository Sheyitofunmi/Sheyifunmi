jQuery(document).ready(function() {

	// Magnicfic Popup
	jQuery('.popup-video').magnificPopup({type:'iframe'});

	// Hide main share
	jQuery(".close").click(function(){
	  jQuery(".main-share-outer").addClass("hide-share");
	});

	// image caption
	jQuery('figcaption').each(function() {
	  let figure = jQuery(this).closest('figure');

	  figure.append("<a class='figcaption-icon'>i</a>");

	  let icon = figure.find('.figcaption-icon');

	  icon.click(function() {
		  figure.addClass('active');

		  setTimeout(function() {
			  figure.removeClass('active');
		  }, 5000);
	  });
	});

    // Moves inline components into the main content-outer
    if (jQuery('.sidebar-outer').length) {
        jQuery('.inline').appendTo('#main-wysiwyg');
    }

});

// Hovered class added to dropdown
var dropdownLink = document.querySelectorAll('.header-nav-list .nav-item');

dropdownLink.forEach(dropdownLink => {
	dropdownLink.addEventListener('mouseover', function() {
	   dropdownLink.classList.add('hovered');
	});
	dropdownLink.addEventListener('mouseout', function() {
	   dropdownLink.classList.remove('hovered');
	});
});

var alertBar = document.querySelector('.alert-outer');
var alertButton = document.querySelector('.alert-close');

if (alertBar) {
	alertButton.addEventListener('click', function () {
		alertBar.classList.add('close-alert');
	})
}
