const value = document.querySelector("#amount");
const input = document.querySelector("#impact-range");
const Impactnumber = document.querySelectorAll(".impact-slide-number");

if (input) {
	value.textContent = input.value;
	input.addEventListener("input", (event) => {
	  value.textContent = event.target.value;

		Impactnumber.forEach(Impactnumber => {
			Impactnumber.textContent = Math.round(input.value / Impactnumber.dataset.value);
		});
	});
}


