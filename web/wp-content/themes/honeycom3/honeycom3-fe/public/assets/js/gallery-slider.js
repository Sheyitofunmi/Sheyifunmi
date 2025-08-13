jQuery(document).ready(function() {
	jQuery('.gallery-slide').lightSlider({
		item: 3,
		loop: true,
		slideMove: 3,
		slideMargin: 15,
		easing: 'cubic-bezier(0.25, 0, 0.25, 1)',
		speed: 600,
		onSliderLoad: function(el) {
			el.lightGallery({
				selector: '.gallery-slide .gallery-item',
				autoplayControls: false,
				download: false,
				fullScreen: false,
				share: false,
				zoom: false,
				licenseKey: '2B3941E0-A4AE-4B38-91F9-5D80BF1406EC',
			});
		},
		responsive: [
			{
				breakpoint: 960,
				settings: {
					item: 2,
					slideMove: 2,
					slideMargin: 6,
				  }
			},
			{
				breakpoint: 560,
				settings: {
					item: 1,
					slideMove: 1
				  }
			}
		]
	});
  });
