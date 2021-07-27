(function ($) {
	$(document).on('click', '.enable-multiple-category', function (e) {
				e.preventDefault();
				$('.category-action').html('<b>Migrating categories<span></span></b>');
				i = 0;
				category_interval = setInterval(() => {
					if (i % 3 == 0) {
						$('.category-action b span').html(' .');
					} else {
						$('.category-action b span').append(' .');
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
									if (response.status) {
										clearInterval(category_interval);
										$('.category-action b').parents('.multiple-category-notice').removeClass('notice-info').addClass('notice-success').html('<p><b>' + response.count + '</b> records migrated succesfully!</p>');
									}

			}
			});
			});

	$(document).on('click', '.enable-cancelled-multiple-category', function (e) {
				e.preventDefault();
		// if ($(this).hasClass('disabled')) {
		//     return true;
		// }
		// $('.enable-cancelled-multiple-category').addClass('disabled').html('<b>Migrating categories<span></span></b>');
		$('.enable-cancelled-multiple-category').hide();
		$('.enable-category-notice .message').html('Migrating Categories');
		$('.enable-category-notice').show();
		i = 0;
		category_interval = setInterval(() => {
					if (i % 3 == 0) {
				$('.enable-category-notice .trail').html(' .');
				}
				else {
					$('.enable-category-notice .trail').append(' .');
				}
				i++;
				}, 500);
				$.ajax({
							type: "POST",
							url: qsm_notices_ajax_object.ajax_url,
							data: {
								action: 'enable_multiple_categories',
								value: 'update'
							},
							success: function (r) {
									response = JSON.parse(r);
									if (response.status) {
										clearInterval(category_interval);
										$('.enable-cancelled-multiple-category').hide();
										$('.enable-category-notice').removeClass('notice-info').addClass('notice-success');
										$('.enable-category-notice .message').html('<b>' + response.count + '</b> records updated');
										$('.enable-category-notice .trail').html('');
										}
										else {
											$('.enable-category-notice').removeClass('notice-info').addClass('notice-error');
											$('.enable-category-notice .message').html('Error! Please try again');
											$('.enable-category-notice .trail').html('');
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
}(jQuery));