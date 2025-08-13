jQuery(document).ready(function() {

jQuery('.cards-outer').each(function() {
	jQuery(this).on('init', function (event, slick, direction) {
		// check to see if there are one or less slides
		if (!(jQuery(this).find('.slick-slide').length > 3)) {
			// remove arrows
			jQuery(this).find('.cards-slider-arrows-outer').hide();
		}
	});
});

var $cardCarousel = jQuery('.cards-slider .cards');

$cardCarousel.slick({
  dots: true,
  arrows: true,
  infinite: true,
  prevArrow: jQuery('.prev-arrow'),
  nextArrow: jQuery('.next-arrow'),
  speed: 300,
  slidesToShow: 3,
  slidesToScroll: 1,
  responsive: [
	  {
		  breakpoint: 960,
		  settings: {
			  slidesToShow: 2,
			  slidesToScroll: 2
		  }
	  },
	  {
		  breakpoint: 700,
		  settings: {
			  slidesToShow: 1,
			  slidesToScroll: 1
		  }
	  }
	 ]
	});

});
