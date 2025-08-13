jQuery(document).ready(function() {

	var $memberCarousel = jQuery('.logo-carousel');

	$memberCarousel.slick({
		arrows: true,
		dots: true,
		infinite: true,
		speed: 300,
		slidesToShow: 5,
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
