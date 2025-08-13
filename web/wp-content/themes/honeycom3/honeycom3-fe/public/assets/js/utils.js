const utils = {
	removeClass(items, removeClass){
		items.forEach(item => item.classList.remove(removeClass));
	},
	addClass(items, addClass){
		items.forEach(item => item.classList.add(addClass));
	},
	setAttributes(items, attribute, value){
		items.forEach(item => item.setAttribute(attribute, value));
	},
	toggleAria(element){
		if (element.getAttribute('aria-expanded') == 'true') {
			element.setAttribute('aria-expanded', 'false');
		} else {
			element.setAttribute('aria-expanded', 'true');
		}
	},
	addHandlers(item, handlers, callBackFunc){
		handlers.forEach(handler => item.addEventListener(handler, callBackFunc));
	},
	getParent(item, selector){
		// Get matching parent elements
		while (item && item !== document) {
			// If current item is selected item return it straight away
			if (item.matches(selector)) {
				return item;
			}
			// Jump to the next parent node if not selected item
			item = item.parentNode;
		}
	},
	// vanilla JS slide up and down functions, from here https://dev.to/bmsvieira/vanilla-js-slidedown-up-4dkn
	slideUp(target, duration=500){
		target.style.transitionProperty = 'height, margin, padding';
		target.style.transitionDuration = duration + 'ms';
		target.style.boxSizing = 'border-box';
		target.style.height = target.offsetHeight + 'px';
		target.offsetHeight;
		target.style.overflow = 'hidden';
		target.style.height = 0;
		target.style.paddingTop = 0;
		target.style.paddingBottom = 0;
		target.style.marginTop = 0;
		target.style.marginBottom = 0;
		window.setTimeout( () => {
			  target.style.display = 'none';
			  target.style.removeProperty('height');
			  target.style.removeProperty('padding-top');
			  target.style.removeProperty('padding-bottom');
			  target.style.removeProperty('margin-top');
			  target.style.removeProperty('margin-bottom');
			  target.style.removeProperty('overflow');
			  target.style.removeProperty('transition-duration');
			  target.style.removeProperty('transition-property');
			  //alert("!");
		}, duration);
	},
	slideDown(target, duration=500){
		target.style.removeProperty('display');
		let display = window.getComputedStyle(target).display;
		if (display === 'none') display = 'block';
		target.style.display = display;
		let height = target.offsetHeight;
		target.style.overflow = 'hidden';
		target.style.height = 0;
		target.style.paddingTop = 0;
		target.style.paddingBottom = 0;
		target.style.marginTop = 0;
		target.style.marginBottom = 0;
		target.offsetHeight;
		target.style.boxSizing = 'border-box';
		target.style.transitionProperty = "height, margin, padding";
		target.style.transitionDuration = duration + 'ms';
		target.style.height = height + 'px';
		target.style.removeProperty('padding-top');
		target.style.removeProperty('padding-bottom');
		target.style.removeProperty('margin-top');
		target.style.removeProperty('margin-bottom');
		window.setTimeout( () => {
		  target.style.removeProperty('height');
		  target.style.removeProperty('overflow');
		  target.style.removeProperty('transition-duration');
		  target.style.removeProperty('transition-property');
		}, duration);
	},
	slideToggle(target, duration = 500){
		if (window.getComputedStyle(target).display === 'none') {
		  return utils.slideDown(target, duration);
		} else {
		  return utils.slideUp(target, duration);
		}
	}
}