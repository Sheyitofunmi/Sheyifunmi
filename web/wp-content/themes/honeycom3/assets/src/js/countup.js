document.addEventListener("DOMContentLoaded", function(event) {
  // modified version of https://codepen.io/Denzy/pen/LazpQQ

  var stats = document.querySelectorAll('.statistic');

  var options = {
	useEasing: true,
	useGrouping: true,
	separator: ',',
	decimal: '.',
  };

  if (stats) {
	stats.forEach((stat) => {
	  var children = stat.querySelectorAll('.statistic-item');
	  function countStart() {
		stat.classList.add('active');
		children.forEach((child) => {
		  var item = child.querySelector('.counter');
		  var countValue = item.dataset.value;

		  var countUP = new CountUp(item, 0, countValue, 0, 3, options);
		  if (!countUP.error) {
			countUP.start();
		  }
		});
	  }

	  ScrollReveal().reveal(stat, {
		beforeReveal: countStart
	  });
	});
  }
});
