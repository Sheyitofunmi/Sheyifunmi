jQuery(document).ready(function() {

function mobileOnlySlider() {
		jQuery('.mob-slider .cards').slick({
			dots: true,
			arrows: false,
			autoplay: false,
			speed: 300,
			autoplaySpeed: 5000
		});
	}
	if(window.innerWidth < 768) {
		mobileOnlySlider();
	}

jQuery(window).resize(function(e){
	if(window.innerWidth < 560) {
		if(!jQuery('.mob-slider .cards').hasClass('slick-initialized')){
			mobileOnlySlider();
		}

	}else{
		if(jQuery('.mob-slider .cards').hasClass('slick-initialized')){
			jQuery('.mob-slider .cards').slick('unslick');
		}
	}
});

});
