
	var cards = document.querySelectorAll('.card, .item');
	Array.prototype.forEach.call(cards, card => {
	  var
	  down,
	  up,
	  link = card.querySelector('.card-link') || card.querySelector('.item-link');
	  // check if inside listing section

	  if (link) {

	  card.style.cursor = 'pointer';
	  card.addEventListener('mouseover', function(){
		card.classList.add('hovered');
	  });
	  card.addEventListener('mouseout', function(){
		card.classList.remove('hovered');
	  });
  	}

	  card.addEventListener('mousedown', function(){
		down = new Date();
		if (Event.button == 2) {
			 return;
		}
	  });
	  card.addEventListener('mouseup', function(e){
		if (e.button === 0) {
		 up = new Date();
		 if ((up - down) < 200 && e.target.tagName.toLowerCase() != 'a') {
		   link.click();
		 }
		}
		if (e.button == 2) {
			 return;
		}
	  });
	});
