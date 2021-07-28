(function ($) {
	$(document).on('click', '.enable-multiple-category', function (e) {
		e.preventDefault();
		$('.category-action').html('Updating database<span></span>');
		$('.category-action').prev().hide();
		$('.category-action').prev().prev().hide();
		i = 0;
		category_interval = setInterval(() => {
			if (i % 3 == 0) {
				$('.category-action span').html(' .');
			} else {
				$('.category-action span').append(' .');
			}
			i++;
		}, 500);
		$.ajax({
			type: "POST",
			url: qsm_notices_ajax_object.ajax_url,
			data: {
				action: 'enable_multiple_categories',
				value: 'enable'
			},
			success: function (r) {
				response = JSON.parse(r);
				clearInterval(category_interval);
				if (response.status) {
					$('.category-action').parents('.multiple-category-notice').removeClass('notice-info').addClass('notice-success').html('<p>Database updated successfully.</p>');
				} else {
					$('.category-action').parents('.multiple-category-notice').removeClass('notice-info').addClass('notice-error').html('Error! Please try again');
				}

			}
		});
	});

	$(document).on('click', '.cancel-multiple-category', function (e) {
		e.preventDefault();
		$('.category-action').html('');
		$.ajax({
			type: "POST",
			url: qsm_notices_ajax_object.ajax_url,
			data: {
				action: 'enable_multiple_categories',
				value: 'cancel'
			},
			success: function (status) {
				if (status) {
					$('.multiple-category-notice').hide();
				}
			}
		});
	});
	$('.multiple-category-notice').show();
}(jQuery));