$(document).ready(function () {
	var
		name = $('input[name*="[name]"]'),
		slug = $('input[name*="[slug]"]'),
		slug_changed = false;

	name.on('change keyup', function () {
		if (!slug_changed || slug.val() == '') {
			slug.val(
				this.value.toLowerCase().replace(/[^a-z\d]/g, '-').replace(/-{2,}/g, '-')
			);
		}
	});

	slug.on('change', function () {
		slug_changed = true;
	});
});