/**
 * QSM admin notices shown on any admin page (not only QSM screens).
 *
 * "Database update required" for multiple categories (QSM 7.3+). Loaded by
 * QSM_Admin_Assets only when that notice is printed.
 */

(function ($) {
    // Strings: qsmAdminNotices (this file's own), else qsm_admin_messages on QSM screens.
    var qsm_admin_messages = $.extend({}, window.qsm_admin_messages || {}, window.qsmAdminNotices || {});
    var i, category_interval;
    $(document).on('click', '.enable-multiple-category', function (e) {
        e.preventDefault();
        const nonce = $(this).data('qsm-mc-nonce');
        if (!nonce) {
            return;
        }
        $('.category-action').html('<span>' + qsm_admin_messages.updating_db + '</span>');
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
            url: ajaxurl,
            data: {
                action: 'enable_multiple_categories',
                value: 'enable',
                nonce: nonce
            },
            success: function (response) {
                clearInterval(category_interval);
                if (response.success) {
                    $('.category-action').parents('.multiple-category-notice').removeClass('notice-info').addClass('notice-success').html('<p>' + qsm_admin_messages.update_db_success + '</p>');
                } else {
                    $('.category-action').parents('.multiple-category-notice').removeClass('notice-info').addClass('notice-error').html(qsm_admin_messages.error + '! ' + qsm_admin_messages.try_again);
                }

            }
        });
    });

    $(document).on('click', '.cancel-multiple-category', function (e) {
        e.preventDefault();
        const nonce = $(this).data('qsm-mc-nonce');
        if (!nonce) {
            return;
        }
        $('.category-action').html('');
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: 'enable_multiple_categories',
                value: 'cancel',
                nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    $('.multiple-category-notice').hide();
                }
            }
        });
    });
    $('.multiple-category-notice').show();
}(jQuery));
