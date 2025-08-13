(function() {
	const scrollEffects = {
		fadein: function(element){
			element.setAttribute("data-visible", "true");
		},
		fade: function(element){
		  element.setAttribute("data-visible", "true");
		},
		fadeinright: function(element){
			element.setAttribute("data-visible", "true");
		},
		fadeinleft: function(element){
			element.setAttribute("data-visible", "true");
		}
	};
	var scrollElements = [];
	var setupElements = function() {
	  scrollElements = document.querySelectorAll("[data-scroll-effect]");
	};
	var cleanElements = function() {
	  scrollElements = Array.prototype.filter.call(scrollElements, function(el) {
		return el.hasAttribute('data-scroll-effect');
	  });
	};
	var isVisible = function(el) {
	   var rect = el.getBoundingClientRect();
	   // Only completely visible elements return true:
	   var _isVisible = (rect.top >= 0) && (rect.bottom <= window.innerHeight);
	   // Partially visible elements return true:
	   _isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
	   return _isVisible;
	};
	var checkEffects = function () {
		var element;
		for(var i = 0, j = scrollElements.length; i < j; i++) {
		  element = scrollElements[i];
		  if(isVisible(element)) {
			scrollEffects[element.getAttribute("data-scroll-effect")](element, element.getAttribute("data-scroll-effect-speed"));
			element.removeAttribute("data-scroll-effect");
		  }
		}
		cleanElements();
	};
	window.addEventListener('scroll', checkEffects);
	window.addEventListener('load', function() {
	  setupElements();
	  checkEffects();
	});
}());
