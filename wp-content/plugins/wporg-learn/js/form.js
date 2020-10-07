(function($) {
	var checkOther = document.querySelectorAll('.checkbox-and-text');

	Array.from(checkOther).forEach(function(container) {
		var checkbox = container.querySelector('input[type="checkbox"]'),
			text     = container.querySelector('input[type="text"]');

		text.addEventListener('input', function(event) {
			checkbox.checked = !! event.target.value;
		});

		checkbox.addEventListener('change', function(event) {
			if (event.target.checked) {
				text.focus();
			} else {
				text.value = '';
			}
		});
	});

	$('.do-select2').select2();
})(jQuery);
