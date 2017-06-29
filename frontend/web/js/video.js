$(document).ready(function () {
	var
		modal = $('#videoModal'),
		errorModal = $('#errorModal'),
		modal_name = $('#videoModalLabel'),
		modal_description = $('#description'),
		modal_video = $('#modal-video'),
		modal_fave_btn = $('#fave'),
		player;

	$('.box').on('click', function (e) {
		e.preventDefault();
		var box = $(this),
			liked;

		modal_name.text(
			box.find('.info > .name').text()
		);

		modal_description.text(
			box.find('.info > .description').text()
		);

		modal_video.html(
			'<video class="video-js vjs-modal vjs-big-play-centered" controls autoplay preload="auto">' +
			'<source type="' + box.attr('data-type') + '" src="' + box.attr('data-src') + '">' +
			'</video>'
		);

		liked = !!parseInt(box.attr('data-fave'));

		function updFaveBtn() {
			modal_fave_btn
				.toggleClass('btn-primary', liked)
				.toggleClass('btn-default', !liked);

			modal_fave_btn.find('i')
				.toggleClass('glyphicon-star', liked)
				.toggleClass('glyphicon-star-empty', !liked);
		}

		updFaveBtn();

		modal_fave_btn.unbind('click').on('click', function () {
			modal_fave_btn.button('loading');
			$.ajax({
				type: 'PUT',
				url: box.attr('data-fave-url'),
				contentType: 'application/json',
				success: function (data) {
					liked = data.liked;
					modal_fave_btn.button('reset')

					updFaveBtn();
					box.attr('data-fave', +liked);
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					errorModal.find('.modal-body').html('<div class="error">' + XMLHttpRequest.responseText + '</div>');
					errorModal.modal('show');
				}
			});
		});

		player = videojs(modal_video.find('video')[0]);
		modal.modal('show');
	});

	modal.on('hide.bs.modal', function () {
		player.dispose();
	});
});