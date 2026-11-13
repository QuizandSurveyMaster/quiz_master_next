/**
 * Main admin file for functions to be used across many QSM admin pages.
 */

var QSMAdmin;
var QSMAdminResultsAndEmail;

function qsmShouldSuppressCreationAlerts() {
    let isActive = typeof window.qsmIsSetupWizardActive === 'function' && window.qsmIsSetupWizardActive();
    let isPending = typeof window.qsmIsSetupWizardPending === 'function' && window.qsmIsSetupWizardPending();
    return isActive || isPending;
}

(function ($) {

    QSMAdmin = {
        toasterTimeout: null,
        /**
         * Catches an error from a jQuery function (i.e. $.ajax())
         */
        displayjQueryError: function (jqXHR, textStatus, errorThrown) {
            QSMAdmin.displayAlert(qsm_admin_messages.error + ': ' + errorThrown + '! ' + qsm_admin_messages.try_again + '.', 'error');
        },
        /**
         * Catches an error from a BackBone function (i.e. model.save())
         */
        displayError: function (jqXHR, textStatus, errorThrown) {
            QSMAdmin.displayAlert(qsm_admin_messages.error + ': ' + errorThrown.errorThrown + '! ' + qsm_admin_messages.try_again + '.', 'error');
        },
        /**
         * Displays an alert within the "Quiz Settings" page
         *
         * @param string message The message of the alert
         * @param string type The type of alert. Choose from 'error', 'info', 'success', and 'warning'
         */
        displayAlert: function (message, type = "success") {
            QSMAdmin.clearAlerts();
            QSMAdmin.ensureAlertWrapper();
            const $wrapper = jQuery('.qsm-message-notice-wrap');

            const icons = {
                success: qsm_admin_messages.success_icon,
                info: qsm_admin_messages.info_icon,
                error: qsm_admin_messages.error_icon,
                warning: qsm_admin_messages.warning_icon
            };

            const iconSrc = icons[type] || icons.success;

            // Find any existing alert
            let $alert = $wrapper.find('.footer-bar-notice');
            // Store timer on wrapper to avoid multiple timers
            if ($wrapper.data('alertTimeout')) {
                clearTimeout($wrapper.data('alertTimeout'));
            }
            if ($wrapper.data('alertHideTimeout')) {
                clearTimeout($wrapper.data('alertHideTimeout'));
            }

            if ($alert.length > 0) {
                // Update existing alert
                $alert.removeClass('qsm-response-success qsm-response-info qsm-response-error qsm-response-warning hide');
                $alert.addClass(`qsm-response-${type}`);
                $alert.find('.qsm-alert-icon').attr('src', iconSrc).attr('alt', `${type} icon`);
                $alert.find('div').text(message);
                $alert.addClass('show');
            } else {
                // Create new alert
                $alert = jQuery(`
                    <div class="footer-bar-notice qsm-response-${type}">
                        <img src="${iconSrc}" alt="${type} icon" class="qsm-alert-icon">
                        <div>${message}</div>
                    </div>
                `);
                $wrapper.append($alert);
                setTimeout(() => {
                    $alert.addClass('show');
                }, 50);
            }

            // Auto-hide after 4 seconds with slide-out transition
            const alertTimeout = setTimeout(() => {
                $alert.addClass('hide'); // Add class to animate out
                const alertHideTimeout = setTimeout(() => {
                    $alert.remove();
                }, 500);
            }, 4000);
        },

        ensureAlertWrapper: function () {
            if (jQuery('.qsm-message-notice-wrap').length === 0) {
                jQuery('body').append('<div class="qsm-message-notice-wrap"></div>');
            }
        },

        clearAlerts: function () {},
        selectTab: function (tab) {
            $('.qsm-tab').removeClass('nav-tab-active');
            $('.qsm-tab-content').hide();
            tab.addClass('nav-tab-active');
            tabID = tab.data('tab');
            $('.tab-' + tabID).show();
        }
    };
    $(function () {
        let optionCount = jQuery('select.question_limit_category:last option').length-1;
        let selectCount = jQuery('select.question_limit_category').length;
        if(optionCount == selectCount) {
            jQuery(".add-more-link").hide();
        }
        $('.qsm-tab').on('click', function (event) {
            event.preventDefault();
            QSMAdmin.selectTab($(this));
        });

        //show set global default potion popup
        $(document).on('click', '#qsm-blobal-settings', function () {
            MicroModal.show('qsm-global-default-popup');
        });
        $(document).on('click', '#qsm-apply-global-settings', function () {
            MicroModal.show('qsm-global-apply-default-popup');
        });

        $('#qmn_check_all').change(function () {
            $('.qmn_delete_checkbox').prop('checked', jQuery('#qmn_check_all').prop('checked'));
        });

        $('.edit-quiz-name').click(function (e) {
            e.preventDefault();
            MicroModal.show('modal-3');
        });
        $('#edit-name-button').on('click', function (event) {
            event.preventDefault();
            $('#edit-name-form').submit();
        });
        $('#sendySignupForm').submit(function (e) {
            e.preventDefault();
            var $form = $(this),
                name = $form.find('input[name="name"]').val(),
                email = $form.find('input[name="email"]').val(),
                action = 'qsm_send_data_sendy';
            $form.find('#submit').attr('disabled', true);
            $.post(ajaxurl, { name: name, email: email, nonce: qsmAdminObject.saveNonce, action: action },
                function (data) {
                    if (data) {
                        $("#status").text('');
                        if (data == "Some fields are missing.") {
                            $("#status").text(qsm_admin_messages.sendy_signup_validation.required_message);
                            $("#status").css("color", "red");
                        } else if (data == "Invalid email address.") {
                            $("#status").text(qsm_admin_messages.sendy_signup_validation.email_validation);
                            $("#status").css("color", "red");
                        } else if (data == "Invalid list ID.") {
                            $("#status").text(qsm_admin_messages.sendy_signup_validation.list_validation);
                            $("#status").css("color", "red");
                        } else if (data == "Already subscribed.") {
                            $("#status").text(qsm_admin_messages.sendy_signup_validation.already_subscribed);
                            $("#status").css("color", "red");
                        } else {
                            $("#status").text(qsm_admin_messages.sendy_signup_validation.success_message);
                            $("#status").css("color", "green");
                        }
                        $form.find('#submit').attr('disabled', false);
                    } else {
                        alert(qsm_admin_messages.sendy_signup_validation.error_message);
                    }
                }
            );
        });
        jQuery('.category_selection_random').change(function () {
            var checked_data = jQuery(this).val().toString();
            jQuery('.catergory_comma_values').val(checked_data);
        });

        function qsmToggleHighlightCorrectAnswer() {
            jQuery('fieldset#highlight_correct_answer').toggle(jQuery('#enable_quick_result_mc-1').is(':checked'));
        }
        qsmToggleHighlightCorrectAnswer();
        $(document).on('change', '#enable_quick_result_mc-1', qsmToggleHighlightCorrectAnswer);

        $(document).on('change', '#limit_category_checkbox-1', function (event) {
            if (jQuery('#limit_category_checkbox-1:checked').length > 0) {
                jQuery('#question_per_category').hide();
                jQuery('div.select-category-question-limit-maindiv').parents("tr").show();
                jQuery('.category_selection_random').parents("tr").hide();
            } else {
                jQuery('div.select-category-question-limit-maindiv').parents("tr").hide();
                jQuery('#question_per_category').show();
                if ( 0 < jQuery('#question_per_category-input').val() ) {
                    jQuery('.category_selection_random').parents("tr").show();
                }
            }
        });
        $(document).on('change', '#question_per_category-input', function (event) {
            if ( 0 < jQuery('#question_per_category-input').val() ) {
                jQuery('.category_selection_random').parents("tr").show();
            } else {
                jQuery('.category_selection_random').parents("tr").hide();
            }
        });
        jQuery(document).on('change', '#preferred-date-format-custom', function() {
            let customValue = jQuery(this).val();
            let validDateFormat = /^[djDlmnMFYy\-\/\. ,]+$/;
            if (validDateFormat.test(customValue)) {
                jQuery(this).css("border-color", "");
                jQuery('#preferred_date_format label.qsm-option-label:last input[type="radio"]').val(customValue);
            } else {
                jQuery(this).css("border-color", "#e54444");
            }
        });
        if( jQuery('#qsm-select-quiz-apply').length ) {
            $('#qsm-select-quiz-apply').multiselect({
                columns: 1,
                placeholder: qsm_admin_messages.select,
                search: true,
                selectAll: true,
                dropdownAutoWidth: false,
            });
        }
        jQuery(document).on('click','.add-more-category', function () {
            let original = jQuery('div.select-category-question-limit-maindiv');
            let lastChild = original.children().last();
            if (lastChild.hasClass('add-more-link')) {
                lastChild = lastChild.prev();
            }
            let clonedChild = lastChild.clone();
            let optionCount = jQuery('select.question_limit_category:last option').length-1;
            let selectCount = jQuery('select.question_limit_category').length+1;
            clonedChild.appendTo(original);
            if(optionCount <= selectCount) {
                jQuery(".add-more-link").hide();
            }
        });
        jQuery(document).on('click', '.delete-category-button', function() {
            if((jQuery('div.select-category-question-limit-subdiv').length) > 1){
                jQuery(this).parent('.select-category-question-limit-subdiv').remove();
            }
            let nextDiv = jQuery('.select-category-question-limit-maindiv').next('div');
            if(nextDiv.next('div.add-more-link').length === 0 ) {
                jQuery(".add-more-link").show();
            }
        });
        jQuery('.category_selection_random').multiselect( {
            columns: 1,
            placeholder: qsm_admin_messages.select_category,
            search: true,
            selectAll: true
        } );
        jQuery('.row-actions-c > .rtq-delete-result').click(function (e) {
            e.preventDefault();
            var $this = jQuery(this);
            if (confirm(qsm_admin_messages.confirm_message)) {
                var action = 'qsm_dashboard_delete_result';
                var result_id = jQuery(this).data('result_id');
                $.post(ajaxurl, { result_id: result_id, action: action, nonce: wpApiSettings.nonce  },
                    function (response) {
                        if (response.success) {
                            $this.parents('li').remove();
                            $this.parents('li').slideUp();
                        } else {
                            alert(qsm_admin_messages.error_delete_result);
                        }
                    }
                );
            }
        });
        jQuery('.load-quiz-wizard').click(function (e) {
            e.preventDefault();
            MicroModal.show('model-wizard');
            if ( jQuery(this).attr('data-formType') == 1 ) {
                jQuery(document).find('.qsm-popup input[name="form_type"][value="1"]').prop('checked', true).change();
            }
            var height = jQuery(".qsm-wizard-template-section").css("height");
            jQuery(".qsm-wizard-setting-section").css("height", height);
            if (jQuery("#accordion").length > 0) {
                var icons = {
                    header: "iconClosed",    // custom icon class
                    activeHeader: "iconOpen" // custom icon class
                };
                jQuery("#accordion").accordion({
                    collapsible: true,
                    icons: icons,
                    heightStyle: "content"
                });
                jQuery('#accordion h3.ui-accordion-header').next().slideDown();
            }
        });

        //Get the message in text tab general
        jQuery(document).on('click', '.quiz_text_tab_message', function () {
            var text_id = jQuery(this).attr('data-id');
            var text_label = jQuery(this).attr('data-label');
            jQuery(".select_message").html(text_label);
            jQuery('.quiz_text_tab').removeClass('current_general');
            jQuery('.qsm-custom-label-left-menu').removeClass('currentli_general');
            jQuery(this).addClass('current_general');
            jQuery(this).parent().addClass('currentli_general');
            jQuery('#' + text_id).show();
            jQuery('.qsm-text-main-wrap .qsm-text-tab-message-loader').show();
            jQuery.post(ajaxurl, { text_id: text_id, 'quiz_id': qsmTextTabObject.quiz_id, action: 'qsm_get_question_text_message' }, function (response) {
                var data = jQuery.parseJSON(response);
                if (data.success === true) {
                    var text_msg = data.text_message;
                    if ($('#wp-qsm_question_text_message-wrap').hasClass('html-active')) {
                        jQuery("#qsm_question_text_message").val(text_msg);
                    } else {
                        text_msg = text_msg.replace(/\n/g, "<br>");
                        tinyMCE.get('qsm_question_text_message').setContent(text_msg);
                    }
                    jQuery('.qsm-text-allowed-variables > .qsm-text-variable-wrap').html('').html(data.allowed_variable_text);
                    jQuery('.qsm-text-main-wrap .qsm-text-tab-message-loader').hide();
                }
            });
        });
		//Get the message in text tab variable
		jQuery(document).on('click', '.quiz_text_tab_message_variable', function () {
			var text_id = jQuery(this).attr('data-id');
			var text_label = jQuery(this).attr('data-label');
			jQuery(".select_message_variable").html(text_label);
			jQuery('.quiz_style_tab').removeClass('current_variable');
			jQuery('.qsm-custom-label-left-menu').removeClass('currentli_variable');
			jQuery(this).addClass('current_variable');
			jQuery(this).parent().addClass('currentli_variable');
			jQuery('#' + text_id).show();
			jQuery('.qsm-text-main-wrap .qsm-text-tab-message-loader').show();
			jQuery.post(ajaxurl, { text_id: text_id, 'quiz_id': qsmTextTabObject.quiz_id, action: 'qsm_get_question_text_message' }, function (response) {
				var data = jQuery.parseJSON(response);
				if (data.success === true) {
					var text_msg = data.text_message;
					if ($('#wp-qsm_question_text_message-wrap').hasClass('html-active')) {
						jQuery("#qsm_question_text_message_variable").val(text_msg);
					} else {
						text_msg = text_msg.replace(/\n/g, "<br>");
						tinyMCE.get('qsm_question_text_message_variable').setContent(text_msg);
					}
					jQuery('.qsm-text-allowed-variables > .qsm-text-variable-wrap').html('').html(data.allowed_variable_text);
					jQuery('.qsm-text-main-wrap .qsm-text-tab-message-loader').hide();
				}
			});
		});
        //Save the message in text tab general text
        jQuery(document).on('click', '#qsm_save_text_message', function () {
            var $this = jQuery(this);
            $this.siblings('.spinner').addClass('is-active');
            var nonce   =  jQuery('#qsm_save_text_message_nonce').val();
            var text_id =  jQuery(".currentli_general .current_general").data('id');
            var message =  wp.editor.getContent('qsm_question_text_message');
            jQuery.post(ajaxurl, { text_id: text_id, 'message': message, 'quiz_id': qsmTextTabObject.quiz_id, action: 'qsm_update_text_message', nonce: nonce }, function (response) {
                var data = jQuery.parseJSON(response);
                if (data.success === true) {
                    //Do nothing
                }
                $this.siblings('.spinner').removeClass('is-active');
            });
        });
		//Save the message in text tab variable text
		jQuery(document).on('click', '#qsm_save_text_message_variable', function () {
			var $this = jQuery(this);
			$this.siblings('.spinner').addClass('is-active');
            var nonce   =  jQuery('#qsm_save_text_message_nonce').val();
			var text_id =  jQuery(".currentli_variable .current_variable").data('id');
			var message =  wp.editor.getContent('qsm_question_text_message_variable');
			jQuery.post(ajaxurl, { text_id: text_id, 'message': message, 'quiz_id': qsmTextTabObject.quiz_id, action: 'qsm_update_text_message', nonce: nonce }, function (response) {
				var data = jQuery.parseJSON(response);
				if (data.success === true) {
					//Do nothing
				}
				$this.siblings('.spinner').removeClass('is-active');
			});
		});
        //On click append on tiny mce
        jQuery(document).on('click', '.qsm-text-allowed-variables button.button', function () {
            var content = jQuery(this).text();
            if (jQuery('.qsm-question-text-tab .html-active').length > 0) {
                var $txt = jQuery("#qsm_question_text_message");
                var caretPos = $txt[0].selectionStart;
                var textAreaTxt = $txt.val();
                var txtToAdd = content;
                $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos));
            } else {
                tinyMCE.activeEditor.execCommand('mceInsertContent', false, content);
            }
        });
        //Show all the variable list
        jQuery('.qsm-show-all-variable-text').click(function (e) {
            e.preventDefault();
            MicroModal.show('show-all-variable');
        });
        if ( "" != jQuery('#scheduled_time_end-input').val() ) {
            jQuery('#not_allow_after_expired_time label').css('opacity', '1');
            jQuery('#not_allow_after_expired_time-1').attr('disabled', false);
        } else {
            jQuery('#not_allow_after_expired_time label').css('opacity', '0.7');
            jQuery('#not_allow_after_expired_time-1').attr('disabled', true);
        }
        jQuery(document).on('change', '#scheduled_time_end-input', function () {
            if ( "" != jQuery(this).val() ) {
                jQuery('#not_allow_after_expired_time label').css('opacity', '1');
                jQuery('#not_allow_after_expired_time-1').attr('disabled', false);
            } else {
                jQuery('#not_allow_after_expired_time label').css('opacity', '0.7');
                jQuery('#not_allow_after_expired_time-1').attr('disabled', true);
            }
        });
        jQuery(document).on('change', '#question_from_total-input', function () {
            if ( 0 != jQuery(this).val() ) {
                jQuery('#limit_category_checkbox label, #question_per_category').css('opacity', '1');
                jQuery('#limit_category_checkbox-1').attr('disabled', false);
            } else {
                jQuery('#limit_category_checkbox label,  #question_per_category').css('opacity', '0.7');
                jQuery('#limit_category_checkbox-1').attr('disabled', true);
                jQuery('#question_per_category-input').val(0);
                jQuery('.category_selection_random').parents("tr").hide();
            }
        });
        if ( 0 != jQuery('#question_from_total-input').val() ) {
            jQuery('#limit_category_checkbox label, #question_per_category').css('opacity', '1');
            jQuery('#limit_category_checkbox-1').attr('disabled', false);
        } else {
            jQuery('#limit_category_checkbox label,  #question_per_category').css('opacity', '0.7');
            jQuery('#limit_category_checkbox-1').attr('disabled', true);
            jQuery('#question_per_category-input').val(0);
            jQuery('.category_selection_random').parents("tr").hide();
        }
        if ( !jQuery('.category_selection_random').length ) {
            jQuery('#limit_category_checkbox,  #question_per_category').hide();
        }
        jQuery(document).on('change', '#qsm-quiz-options-form_type input', function () {
            if (0 == jQuery(this).val()) {
                jQuery('#qsm-quiz-options-system').show();
            } else {
                jQuery('#qsm-quiz-options-system').hide();
            }
        });
        $('.qsm_tab_content .qsm-opt-tr select').each(function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            if ($('.' + name + '_' + value).length > 0) {
                $('.' + name + '_' + value).show();
            }
        });

        $(document).ready(function () {
            if (jQuery('.qsm-date-picker').length) {
                jQuery('.qsm-date-picker').datetimepicker({ format: 'm/d/Y H:i', step: 1});
            }
        });
        if ($('.qsm-text-label-wrapper').length > 0) {
            var element_position = $('.qsm-text-label-wrapper').offset().top;
            $(window).scroll(function () {
                var y_scroll_pos = window.pageYOffset;
                var scroll_pos_test = element_position;
                if (y_scroll_pos > scroll_pos_test) {
                    $('.qsm_text_customize_label').fadeOut('slow');
                } else {
                    $('.qsm_text_customize_label').fadeIn('slow');
                }
            });
        }
        $(document).on('click', '.qsm_text_customize_label', function () {
            $('html, body').animate({
                scrollTop: $(".qsm-text-label-wrapper").offset().top - 30
            }, 2000);
        });
        //New template design hide show
        var new_template_result_detail = $('.new_template_result_detail:checked').val();
        if (new_template_result_detail == 1) {
            $('.new_template_result_detail:checked').parents('tr').next('tr').hide();
        }
        $(document).on('change', '.new_template_result_detail', function () {
            if ($(this).val() == 1) {
                $(this).parents('tr').next('tr').hide();
            } else {
                $(this).parents('tr').next('tr').show();
            }
        });
        $(document).on('click', '#show-all-variable .qsm-text-template-span:not(.qsm-upgrade-popup-variable)', function (e) {
            e.preventDefault();
            let templateSpan = jQuery(this);
            let templateVariable = jQuery(this).children('.template-variable');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(templateVariable.text()).select();
            document.execCommand("copy");
            $temp.remove();
            var button_width = templateSpan.width();
            var button_txt = templateSpan.html()
            templateSpan.css('width', button_width);
            templateSpan.text('').html('<span class="popup-copied-des"><span class="dashicons dashicons-yes"></span> ' + qsm_admin_messages.copied + '</span>');
            setTimeout(function () {
                templateSpan.css('width', 'auto');
                templateSpan.html(button_txt);
            }, 1000);
        });

        $(document).on('click', ' .qsm-active-addons .no_addons_installed a', function (e) {
            $('.qsm-addon-anchor-left .qsm-install-addon a').trigger('click');
        });
        $(document).on('click', '.qsm-addon-anchor-left .qsm-install-addon a', function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $('.qsm-addon-anchor-left .qsm-install-addon').find('a').removeClass('active');
            $(this).addClass('active');
            $('.qsm-addon-setting-wrap .qsm-primary-acnhor').hide();
            $(href).show();
            if (href == '#qsm_add_addons') {
                $('.qsm-add-addon').css('display', 'inline-block');
            } else {
                $('.qsm-add-addon').css('display', 'none');
            }
        });
        $(document).on('click', '.qsm-addon-anchor-left .qsm-add-addon a', function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $('.qsm-addon-anchor-left .qsm-add-addon').find('a').removeClass('active');
            $(this).addClass('active');
            $('.qsm-addon-setting-wrap .qsm_popular_addons').hide();
            $(href).show();
        });
        // opens media library o set featured image for quiz
        $(document).on('click', '#set_featured_image', function (e) {
            var button = $(this);
            e.preventDefault();
            custom_uploader = wp.media({
                title: qsm_admin_messages.set_feature_img,
                library: {
                    type: 'image'
                },
                button: {
                    text: qsm_admin_messages.use_img // button label text
                },
                multiple: false
            }).on('select', function () { // it also has "open" and "close" events
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev().val(attachment.url);
                button.nextAll('.qsm_featured_image_preview').attr('src', attachment.url);
            }).open();
        });

        $(document).on('click', '#qsm-set-theme-feature-image', function (e) {
            var button = $(this);
            e.preventDefault();
            custom_uploader = wp.media({
                title: qsm_admin_messages.set_feature_img,
                library: {
                    type: 'image'
                },
                button: {
                    text: qsm_admin_messages.use_img // button label text
                },
                multiple: false
            }).on('select', function () { // it also has "open" and "close" events
                let attachment = custom_uploader.state().get('selection').first().toJSON();
                button.parents('.quiz_style_tab_content').find('.quiz_featured_image').val(attachment.url).trigger('input');
            }).open();
        });

        $(document).on('click', '.qsm-image-btn', function (e) {
            let button = $(this);
            e.preventDefault();
            custom_uploader = wp.media({
                title: qsm_admin_messages.set_feature_img,
                library: {
                    type: 'image'
                },
                button: {
                    text: qsm_admin_messages.use_img // button label text
                },
                multiple: false
            }).on('select', function () { // it also has "open" and "close" events
                let attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('.qsm-image-input').val(attachment.url);
            }).open();
        });

        // opens media library o set background image for quiz
        $(document).on('click', '.set_background_image', function (e) {
            let button = $(this);
            e.preventDefault();
            custom_uploader = wp.media({
                title: qsm_admin_messages.set_bg_img,
                library: {
                    type: 'image'
                },
                button: {
                    text: qsm_admin_messages.use_img // button label text
                },
                multiple: false
            }).on('select', function () { // it also has "open" and "close" events
                let attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('.quiz_background_image').val(attachment.url);
            }).open();
        });

        //theme option setting image start
        $(document).on('click', '.quiz-theme-option-image-button', function (e) {
            let button = $(this);
            e.preventDefault();
            custom_uploader = wp.media({
                library: {
                    type: 'image'
                },
                button: {
                    text: qsm_admin_messages.use_img // button label text
                },
                multiple: false
            }).on('select', function () { // it also has "open" and "close" events
                let attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('.quiz-theme-option-image-input').val(attachment.url);
                button.next('.qsm-theme-option-image').fadeIn();
                button.hide();
                button.next('.qsm-theme-option-image').find('.quiz-theme-option-image-thumbnail').attr('src', attachment.url);
            }).open();
        });
        jQuery(document).on('click', '.qsm-theme-option-image-remove', function () {
            let button = $(this);
            button.parents('.qsm-theme-option-image').nextAll( ".qsm-theme-option-image-default" ).show();
            button.parents('.qsm-theme-option-image').hide();
            button.parents('.qsm-theme-option-image').prevAll('.quiz-theme-option-image-input').val("");
            button.parents('.qsm-theme-option-image').prevAll('.quiz-theme-option-image-button').fadeIn();

        });
        jQuery(document).on('click', '.qsm-theme-option-image-default', function () {
            let button = $(this);
            let default_img = $(this).data( "default" );
            button.prevAll('.qsm-theme-option-image').show();
            button.prevAll('.qsm-theme-option-image').find(".quiz-theme-option-image-thumbnail").attr( "src", default_img );
            button.prevAll('.quiz-theme-option-image-input').val(default_img);
            $(this).hide();

        });
        //theme option setting image end

        $(document).on('change', '.qsm_page_qmn_global_settings  input[name="qsm-quiz-settings[form_type]"]', function () {
            if ( 0 == $(this).val() ) {
                $('.global_setting_system').parents('tr').show();
                $("#qsm-correct-answer-logic").show();
            } else {
                $('.global_setting_system').parents('tr').hide();
                $("#qsm-correct-answer-logic").hide();
            }
        });
        $(document).on('change', '.global_setting_system input[name="qsm-quiz-settings[system]"]', function () {
            if ( 1 != $(this).val() && 0 == $('.qsm_page_qmn_global_settings  input[name="qsm-quiz-settings[form_type]"]:checked').val() ) {
                $("#qsm-correct-answer-logic").show();
            } else {
                $("#qsm-correct-answer-logic").hide();
            }
        });
        $('.qsm_page_qmn_global_settings  input[name="qsm-quiz-settings[form_type]"]:checked').trigger('change');
        $('.global_setting_system input[name="qsm-quiz-settings[system]"]:checked').trigger('change');
        jQuery(document).on('click', '.qsm-select-all-deleted-result', function () {
            $('.qsm-deleted-result-checkbox').prop('checked', $(this).prop('checked'));
        });
        jQuery(document).on('click', '.qsm-select-all-deleted-question', function () {
            $('.qsm-deleted-question-checkbox').prop('checked', $(this).prop('checked'));
        });

        jQuery(document).on('click', '.qsm-tools-delete-all-questions, .qsm-tools-delete-selected-questions', function (e) {
            e.preventDefault();
            MicroModal.show('qsm-delete-questions-tools-page-popup');
            $('.qsm-tools-page-delete-questions-message').html($(this).data('message'));
            $('.qsm-tools-delete-questions-action-name').val($(this).data('actiontype'));
        });

        jQuery(document).on('click', '.qsm-delete-questions-tools-page-btn', function () {
            let deleteForm = $("#qsm-tools-delete-questions-form");
            deleteForm.submit();
        });

        jQuery(document).on('click', '.qsm-tools-delete-all-results, .qsm-tools-delete-selected-results', function (e) {
            e.preventDefault();
            MicroModal.show('qsm-delete-results-tools-page-popup');
            $('.qsm-tools-page-delete-results-message').html($(this).data('message'));
            $('.qsm-tools-delete-results-action-name').val($(this).data('actiontype'));
        });

        jQuery(document).on('click', '.qsm-delete-results-tools-page-btn', function () {
            let deleteForm = $("#qsm-tools-delete-results-form");
            deleteForm.submit();
        });

    });

    $(document).on('click', '#the-list .delete_table_quiz_results_item', function (e) {
        e.preventDefault();
        var qid = $(this).data('quiz-id');
        var qname = $(this).data('quiz-name');
        deleteResults(qid, qname);
    });

    $(document).on('click', '#the-list .qsm-quiz-proctor-addon', function (e) {
        e.preventDefault();
        MicroModal.show('modal-proctor-quiz');
    });

    jQuery(document).on('click', '#btn_export', function (e) {
        e.preventDefault();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: "qsm_export_data",
                nonce: qsm_tools_page.nonce,
            },
            success: function (response) {
                /*
                 * Make CSV downloadable
                 */
                var d = new Date();

                var month = d.getMonth() + 1;
                var day = d.getDate();
                var output = d.getFullYear() + '-' + (('' + month).length < 2 ? '0' : '') + month + '-' + (('' + day).length < 2 ? '0' : '') + day;
                var downloadLink = document.createElement("a");
                var fileData = ['\ufeff' + response];

                var blobObject = new Blob(fileData, {
                    type: "text/csv;charset=utf-8;"
                });

                var url = URL.createObjectURL(blobObject);
                downloadLink.href = url;
                downloadLink.download = "export_" + output + ".csv";
                /*
                 * Actually download CSV
                 */
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            },
            error: function (errorThrown) {
                alert(errorThrown);
            }
        });
    });

    jQuery(document).on('click', '#btn_clear_logs', function (e) {
        e.preventDefault();
        var delete_logs = confirm(qsm_tools_page.qsm_delete_audit_logs);
        if (delete_logs) {
            // your deletion code
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: "qsm_clear_audit_data",
                    nonce: qsm_tools_page.nonce,
                },
                success: function (response) {
                    location.reload();
                },
                error: function (errorThrown) {
                    alert(errorThrown);
                }
            });
        }
    });

    jQuery('.qsm_audit_data').click(function (e) {
        e.preventDefault();
        MicroModal.show('qsm_fetch_audit_data');
        var qsm_get_setting_data = jQuery(this).attr('data-auditid');
        let qsm_audit_text = qsm_get_setting_data;
        try {
            qsm_audit_text = JSON.stringify(JSON.parse(qsm_get_setting_data), null, 2);
        } catch (err) {}
        // Audit data can hold visitor-submitted values: insert as text, never as HTML.
        jQuery('.qsm_setting__data').empty().append(jQuery('<p>').text(qsm_audit_text));
    });

    jQuery(document).on('click', '.qsm-toggle-box-handle', function (e) {
        e.preventDefault();
        var parent = jQuery(this).parent('.qsm-toggle-box');
        var content = parent.find('.qsm-toggle-box-content');
        if (content.is(":visible")) {
            content.hide();
            parent.removeClass('opened');
        } else {
            content.show();
            parent.addClass('opened');
        }
    });

    jQuery(document).on('click', '.custom-addon-upper li a', function (e) {
        jQuery(".custom-addon-upper li a").removeClass('current');
        jQuery(this).addClass('current');
        if(jQuery(this).data('section') == "all"){
            jQuery("#qsm_installed_addons").hide();
            jQuery("#qsm_add_addons").show();
        }else{
            jQuery("#qsm_add_addons").hide();
            jQuery("#qsm_installed_addons").show();
        }
    });

    jQuery(document).on('click', '.custom-explore-addon', function (e) {
        jQuery(".custom-addon-upper li a").removeClass('current');
        jQuery(".custom-addon-upper li a:first").addClass('current');
        jQuery("#qsm_installed_addons").hide();
        jQuery("#qsm_add_addons").show();
    });

    jQuery(document).on('click', '.qsm-help-tab-handle', function (e) {
        e.preventDefault();
        jQuery('.qsm-help-tab-dropdown-list').toggleClass('opened');
    });

    $(document).mouseup(function (e) {
        var link = $(".qsm-help-tab-handle");
        var container = $(".qsm-help-tab-dropdown-list");
        if (!link.is(e.target) && !container.is(e.target) && container.has(e.target).length === 0) {
            container.removeClass('opened');
        }
    });
}(jQuery));

// result page
jQuery('#results-screen-option-button').on('click', function (event) {
    event.preventDefault();
    MicroModal.show('modal-results-screen-option');
});
jQuery('#save-results-screen-option-button').on('click', function (event) {
    event.preventDefault();
    MicroModal.close('modal-results-screen-option');
    jQuery('#results-screen-option-form').submit();
});
function deleteResults(id, quizName) {
    MicroModal.show('qsm-delete-result-page-popup');
    var idHidden = document.getElementById("result_id");
    var idHiddenName = document.getElementById("delete_quiz_name");
    idHidden.value = id;
    idHiddenName.value = quizName;
}
//quiz options style tab
jQuery('.quiz_style_tab').click(function (e) {
    e.preventDefault();
    let current_id = jQuery(this).attr('data-id');
    jQuery('.quiz_style_tab').removeClass('current');
    jQuery('.qsm-custom-label-left-menu').removeClass('currentli');
    jQuery(this).addClass('current');
    jQuery('.quiz_style_tab_content').hide();
    jQuery('#' + current_id).show();
});
//quiz options text tab custom label
jQuery('.quiz_text_tab_custom').click(function (e) {
    e.preventDefault();
    let current_id = jQuery(this).attr('data-id');
    jQuery('.quiz_text_tab_custom').removeClass('current');
    jQuery('.qsm-custom-label-left-menu').removeClass('currentli');
    jQuery(this).addClass('current');
    jQuery(this).parent().addClass('currentli');
    jQuery('.quiz_style_tab_content').hide();
    jQuery('#' + current_id).show();
});
//quiz text tab
jQuery('.quiz_text_tab').click(function (e) {
    e.preventDefault();
    var current_id = jQuery(this).attr('data-id');
    jQuery('.quiz_text_tab').removeClass('current');
    jQuery(this).addClass('current');
    jQuery('.quiz_text_tab_content').hide();
    jQuery("#postbox-container-1").show();
    if(current_id == 'qsm_general_text'){ jQuery(".current_general")[0].click();}
    if(current_id == 'qsm_variable_text'){  jQuery(".current_variable")[0].click();}
    if(current_id == 'qsm_custom_label'){ jQuery("#postbox-container-1").css("display", "none");}
    jQuery('#' + current_id).show();
    jQuery('.qsm-text-tab-description').hide();
    jQuery('.qsm-text-tab-description[data-id="' + current_id + '"]').show();
    jQuery(document).trigger('qsm_quiz_text_tab_after', [current_id]);
});
if (jQuery('body').hasClass('admin_page_mlw_quiz_options') || jQuery('body').hasClass('qsm_page_qmn_global_settings')){
    let current_id = jQuery(this).attr('data-id'); 
    if(current_id == 'qsm_general_text'){ jQuery(".current_general")[0].click();}
    if(current_id == 'qsm_variable_text'){ jQuery(".current_variable")[0].click();}
    if (window.location.href.indexOf('tab=style') > 0) {
        function mlw_qmn_theme(theme) {
            document.getElementById('save_quiz_theme').value = theme;
            jQuery("div.mlw_qmn_themeBlockActive").toggleClass("mlw_qmn_themeBlockActive");
            jQuery("#mlw_qmn_theme_block_" + theme).toggleClass("mlw_qmn_themeBlockActive");
        }

        jQuery(document).ready(function () {
            jQuery(document).on('click', '.qsm-activate-theme', function () {
                jQuery(this).parents('.theme-wrapper').find('input[name=quiz_theme_id]').prop("checked", true);
            });
            jQuery(document).on('input', '.quiz_featured_image', function () {
                if(jQuery(this).val() == '') {
                    jQuery('.qsm_featured_image_preview').attr('src', qsm_admin_messages.feature_img_placeholder);
                } else {
                    jQuery('.qsm_featured_image_preview').attr('src', jQuery(this).val());
                }
            });
            jQuery(document).on('click', '.filter-links a', function () {
                let current_id = jQuery(this).attr('data-id');
                jQuery(this).parents('.filter-links').find('li a').each(function () {
                    jQuery(this).removeClass('current');
                });
                jQuery(this).addClass('current');
                jQuery(this).parents('#qsm_themes').find('.themes-container').children('div').each(function () {
                    if (jQuery(this).hasClass(current_id)) {
                        jQuery(this).show();
                    } else {
                        jQuery(this).hide();
                    }
                });
            })

            let $themeBrowser = jQuery(".quiz_style_tab_content#theme-browser");
            if ($themeBrowser.length) {
                let $themesContainer = $themeBrowser.find(".themes-container");
                if ($themesContainer.length) {
                    let $themesWrapper = $themesContainer.children();
                    let $themesTarget = $themeBrowser.find(".themes.wp-clearfix");
                    if ($themesTarget.length) {
                        $themesWrapper.appendTo($themesTarget); // Moves instead of appending
                    }
                    $themesContainer.remove();
                }
            }
        });
    }
    const processTemplates = function ( data, allowedTypes, updateOptions = false, updateType = '' ) {
        if ( !Array.isArray( data ) ) return;
        data.forEach( function ( filteredRow, key ) {
            if ( allowedTypes.includes( filteredRow.template_type ) ) {
                filteredRow.indexid = key;
                QSMAdminResultsAndEmail.addTemplateRow( filteredRow );
                if ( updateOptions ) {
                    QSMAdminResultsAndEmail.updateMyTemplateOptions( filteredRow, updateType );
                }
            }
        } );
    };
    if ( window.location.href.indexOf('tab=emails') > 0 || window.location.href.indexOf('tab=results-pages') > 0 || window.location.href.indexOf('tab=quiz-default-template') > 0 ) {
        QSMAdminResultsAndEmail = {
            insertTemplate: async function (button, data) {
                try {
                    button.prop('disabled', true);
                    const response = await jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: data,
                        beforeSend: function () {
                            button.text(qsm_admin_messages.add_process);
                        }
                    });
                    jQuery(document).find('.qsm-no-templates-row').remove();
                    if (response.success) {
                        return response;
                    } else {
                        return { success: false, message: response.message || 'Unknown error' };
                    }
                } catch (error) {
                    return { success: false, message: error.message };
                } finally {
                    button.prop('disabled', false);
                    button.text(qsm_admin_messages.add_more);
                }
            },
            headerLinks: function ( link, templateType ) {
                jQuery(document).find('.qsm-'+templateType+'-page-tmpl-header-links').removeClass('active');
                link.addClass("active");
                jQuery(document).find('.qsm-'+templateType+'-page-template-common').hide();
                jQuery(document).find('.qsm-'+templateType+'-'+link.data('tab')+'-template-container').show();
            },
            addTemplateRow: function ( data ) {
                let template = wp.template('qsm-my-template-rows');
                let templateType = data.template_type.replace('global_', '');
                let tableSelector = '.qsm-' + templateType + '-my-template-container .qsm-my-templates-table-body';
                jQuery(tableSelector).append(template(data));
            },
            loadMyTemplates: function ( type, isDefaultContext = false ) {
                if ( isDefaultContext ) {
                    if ( type === 'result' && typeof qsmDefaultResultsObject !== 'undefined' ) {
                        processTemplates( qsmDefaultResultsObject.my_tmpl_data, ['global_result'], true, 'global_result' );
                        return;
                    }
                    if ( type === 'email' && typeof qsmDefaultEmailsObject !== 'undefined' ) {
                        processTemplates( qsmDefaultEmailsObject.my_tmpl_data, ['global_email'], true, 'global_email' );
                        return;
                    }
                    return;
                }

                if ( type === 'result' && typeof qsmResultsObject !== 'undefined' ) {
                    processTemplates( qsmResultsObject.my_tmpl_data, ['result', 'global_result'] );
                    return;
                }
                if ( type === 'email' && typeof qsmEmailsObject !== 'undefined' ) {
                    processTemplates( qsmEmailsObject.my_tmpl_data, ['email', 'global_email'] );
                    return;
                }
            },
            updateMyTemplateOptions: function( newTemplate, type ) {
                let $selectBox;
                if ( type === 'email' ) {
                    $selectBox = jQuery('.qsm-email .qsm-to-replace-page-template');
                } else if ( type === 'result' ) {
                    $selectBox = jQuery('.results-page .qsm-to-replace-page-template');
                } else if ( type === 'global_email' ) {
                    $selectBox = jQuery('[data-template-type="global_email"] .qsm-to-replace-page-template');
                } else if ( type === 'global_result' ) {
                    $selectBox = jQuery('[data-template-type="global_result"] .qsm-to-replace-page-template');
                } else {
                    $selectBox = jQuery('.qsm-to-replace-page-template');
                }
                $selectBox.append(
                    jQuery('<option>', {
                        value: newTemplate.id,
                        text: newTemplate.template_name
                    })
                );
            },
            closePreviewModel: function ( popupId ){
                MicroModal.close(popupId);
                jQuery('#'+popupId).removeClass('is-open');
                jQuery('#'+popupId).attr('aria-hidden', true);
            },
            addDefaultTemplateOptions: function (type) {
                let objectName = type === 'email' ? 'qsmDefaultEmailsObject' : 'qsmDefaultResultsObject';
                let templateType = 'global_' + type;
                let $selectBox = jQuery('[data-template-type="' + templateType + '"] .qsm-to-replace-page-template');
                
                $selectBox.empty();
                $selectBox.append('<option value="">'+qsm_admin_messages.select_template+'</option>');
                
                if (window[objectName] !== undefined && Array.isArray(window[objectName].my_tmpl_data)) {
                    jQuery.each(window[objectName].my_tmpl_data, function(key, value) {
                        if (value.template_type === templateType) {
                            $selectBox.append(jQuery('<option>', {
                                value: value.id,
                                text: value.template_name
                            }));
                        }
                    });
                }
                
                $selectBox.select2({
                    placeholder: qsm_admin_messages.select_template,
                    allowClear: true,
                    initSelection: function(e, cb) { }
                });
            },

        };

        jQuery(document).ready(function () {

            jQuery(document).on('click', '.qsm-save-page-template-button', async function (e) {
                e.preventDefault();
                const button = jQuery(this);
                const templateWrap = button.parents('.qsm-insert-template-wrap');
                const templateName = templateWrap.find('.qsm-insert-page-template-title').val().trim();
                const selectedTemplateId = templateWrap.find('.qsm-to-replace-page-template').val();
                const uniqueId = button.data('id');
                const templateType = button.parents('.qsm-insert-page-template-anchor').data('template-type');
                const isDefaultContext = button.data('context') === 'default';
                let nonce;
                let editor;
                if (isDefaultContext) {
                    if (templateType == 'global_result') {
                        editor = tinymce.get('default_result_template');
                        nonce = qsmDefaultResultsObject.add_tmpl_nonce;
                    } else if (templateType == 'global_email') {
                        editor = tinymce.get('default_email_template');
                        nonce = qsmDefaultEmailsObject.add_tmpl_nonce;
                    }
                } else {
                    if (templateType == 'result') {
                        editor = tinymce.get('results-page-' + (uniqueId));
                        nonce = qsmResultsObject.add_tmpl_nonce;
                    } else if (templateType == 'email') {
                        editor = tinymce.get('email-template-' + (uniqueId));
                        nonce = qsmEmailsObject.add_tmpl_nonce;
                    }
                }
                
                if (!editor) {
                    QSMAdmin.displayAlert(qsm_admin_messages.confirm_message, 'error');
                    return;
                }

                const templateContent = editor.getContent().trim();
                const isReplace = jQuery('input[name="qsm-template-action"]:checked').val() === 'replace';

                // Validation
                if (isReplace && !selectedTemplateId) {
                    QSMAdmin.displayAlert(qsm_admin_messages.no_template_selected, 'error');
                    return;
                }

                if (!isReplace && !templateName) {
                    QSMAdmin.displayAlert(qsm_admin_messages.empty_template_name, 'error');
                    return;
                }

                if (!templateContent) {
                    console.log(qsm_admin_messages.empty_template_content);
                    QSMAdmin.displayAlert(qsm_admin_messages.empty_template_content, 'error');
                    return;
                }

                const data = {
                    action: 'qsm_insert_quiz_template',
                    template_name: isReplace ? '' : templateName,
                    template_id: isReplace ? selectedTemplateId : null,
                    template_type: templateType,
                    template_content: templateContent,
                    nonce: nonce,
                };

                try {
                    const response = await QSMAdminResultsAndEmail.insertTemplate(button, data);
                    if (response.success) {
                        if (isReplace) {
                            if (isDefaultContext) {
                                if (templateType == 'global_result' && typeof qsmDefaultResultsObject !== 'undefined') {
                                    qsmDefaultResultsObject.my_tmpl_data.forEach((tmpl, index) => {
                                        if (tmpl.id == selectedTemplateId) {
                                            qsmDefaultResultsObject.my_tmpl_data[index] = response.data;
                                        }
                                    });
                                } else if (templateType == 'global_email' && typeof qsmDefaultEmailsObject !== 'undefined') {
                                    qsmDefaultEmailsObject.my_tmpl_data.forEach((tmpl, index) => {
                                        if (tmpl.id == selectedTemplateId) {
                                            qsmDefaultEmailsObject.my_tmpl_data[index] = response.data;
                                        }
                                    });
                                }
                            } else {
                                if (templateType == 'result') {
                                    qsmResultsObject.my_tmpl_data.forEach((tmpl, index) => {
                                        if (tmpl.id == selectedTemplateId) {
                                            qsmResultsObject.my_tmpl_data[index] = response.data;
                                        }
                                    });
                                } else if (templateType == 'email') {
                                    qsmEmailsObject.my_tmpl_data.forEach((tmpl, index) => {
                                        if (tmpl.id == selectedTemplateId) {
                                            qsmEmailsObject.my_tmpl_data[index] = response.data;
                                        }
                                    });
                                }
                            }
                            QSMAdmin.displayAlert(qsm_admin_messages.template_updated, 'success');
                        } else {
                            let response_data = response.data;
                            if (isDefaultContext) {
                                if (templateType == 'global_result' && typeof qsmDefaultResultsObject !== 'undefined') {
                                    response_data.indexid = qsmDefaultResultsObject.my_tmpl_data.length == 0 ? 0 : qsmDefaultResultsObject.my_tmpl_data.length;
                                    qsmDefaultResultsObject.my_tmpl_data.push(response_data);
                                    QSMAdminResultsAndEmail.updateMyTemplateOptions(response_data, 'global_result');
                                    QSMAdminResultsAndEmail.addTemplateRow(response_data);
                                } else if (templateType == 'global_email' && typeof qsmDefaultEmailsObject !== 'undefined') {
                                    response_data.indexid = qsmDefaultEmailsObject.my_tmpl_data.length == 0 ? 0 : qsmDefaultEmailsObject.my_tmpl_data.length;
                                    qsmDefaultEmailsObject.my_tmpl_data.push(response_data);
                                    QSMAdminResultsAndEmail.updateMyTemplateOptions(response_data, 'global_email');
                                    QSMAdminResultsAndEmail.addTemplateRow(response_data);
                                }
                            } else {
                                if (templateType == 'result') {
                                    response_data.indexid = qsmResultsObject.my_tmpl_data.length == 0 ? 0 : qsmResultsObject.my_tmpl_data.length;
                                    qsmResultsObject.my_tmpl_data.push(response_data);
                                    QSMAdminResultsAndEmail.updateMyTemplateOptions(response_data, 'result');
                                    QSMAdminResultsAndEmail.addTemplateRow(response_data);
                                } else if (templateType == 'email') {
                                    response_data.indexid = qsmEmailsObject.my_tmpl_data.length == 0 ? 0 : qsmEmailsObject.my_tmpl_data.length;
                                    qsmEmailsObject.my_tmpl_data.push(response_data);
                                    QSMAdminResultsAndEmail.updateMyTemplateOptions(response_data, 'email');
                                    QSMAdminResultsAndEmail.addTemplateRow(response_data);
                                }
                            }
                            QSMAdmin.displayAlert(qsm_admin_messages.template_added, 'success');
                            templateWrap.find('.qsm-insert-page-template-title').val('');
                        }
                    }
                } catch (error) {
                    console.error('An error occurred during template saving:', error.message);
                    QSMAdmin.displayAlert(qsm_admin_messages.template_save_error, 'error');
                }
            });

            jQuery(document).on('change', '.qsm-insert-template-action', function () {
                const isReplace = jQuery(this).val() === 'replace';
                jQuery('.qsm-insert-page-template-title').toggle(!isReplace);
                jQuery('.qsm-to-replace-page-template-wrap').toggle(isReplace);
                jQuery('.qsm-insert-template-response').text('');
            });
            jQuery(document).on('click', '.qsm-insert-page-template-anchor', function (e) {
                let templateWrap = jQuery(this).find('.qsm-insert-template-wrap');
                let radioButtons = jQuery(this).find('input[type="radio"][name="qsm-template-action"]');
                if (!radioButtons.is(':checked')) {
                    radioButtons.filter('[value="new"]').prop('checked', true).trigger('change');
                }
                jQuery('.qsm-settings-box-details, .qsm-more-settings-box-details').hide();
                if (!templateWrap.is(':visible')) {
                    templateWrap.show();
                }
            });

            jQuery(document).on('click', 'a.qsm-view-templates-list', function (e) {
                e.preventDefault();
                let templateType = jQuery(this).data('type');
                if(templateType == 'result') {
                    let resultPageIndex = jQuery(this).parents('.qsm-template-btn-group').parents('.results-page').find('.results-page-show').data('result-page');
                    jQuery("#qsm-result-page-templates-content").attr('data-result-page', resultPageIndex);

                    if(typeof qsmDefaultResultsObject !== 'undefined'){
                        jQuery(document).find('.qsm-result-my-template-container').hide();
                    }
                } else if(templateType == 'email') {
                    let emailPageValue = jQuery(this).parents('.qsm-template-btn-group').parents('.qsm-email').find('.email-show').data('email-page');
                    jQuery("#qsm-email-page-templates-content").attr('data-email-page', emailPageValue);

                    if(typeof qsmDefaultEmailsObject !== 'undefined'){
                        jQuery(document).find('.qsm-email-my-template-container').hide();
                    }
                }
                // Strip 'global_' prefix if present to match modal ID
                let modalType = templateType.replace('global_', '');
                MicroModal.show('qsm-'+modalType+'-page-templates');
            });

            jQuery(document).on('click', '.qsm-preview-template-image-close', function (e) {
                let type = jQuery(this).data('type');
                jQuery('.qsm-preview-'+type+'-page-template-container').hide();
                jQuery('.qsm-'+type+'-page-template-container').show();
                jQuery(this).hide();
            });

            jQuery(document).on('click', 'a.qsm-result-page-template-remove-button, a.qsm-email-page-template-remove-button, a.qsm-global_email-page-template-remove-button, a.qsm-global_result-page-template-remove-button', async function (e) {
                e.preventDefault();
                if (!confirm(qsm_admin_messages.confirmDeleteTemplate)) {
                    return;
                }
                const button = jQuery(this);
                const templateId = button.data('id');
                let type = button.data('type');
                let nonce;
                if (type === 'global_email') { type = 'email'; }
                else if (type === 'global_result') { type = 'result'; }
                if (type === 'result') { 
                    if (typeof qsmDefaultResultsObject !== 'undefined') {
                        nonce = qsmDefaultResultsObject.remove_tmpl_nonce; 
                    } else if (typeof qsmResultsObject !== 'undefined') {
                        nonce = qsmResultsObject.remove_tmpl_nonce;
                    }
                } else if (type === 'email') { 
                    if (typeof qsmDefaultEmailsObject !== 'undefined') {
                        nonce = qsmDefaultEmailsObject.remove_tmpl_nonce;
                    } else if (typeof qsmEmailsObject !== 'undefined') {
                        nonce = qsmEmailsObject.remove_tmpl_nonce;
                    }
                } else {
                    console.error("Unknown template type.");
                    return;
                }
                button.prop('disabled', true);
                const loader = jQuery('<div class="qsm-spinner-loader"></div>');
                button.after(loader);
                try {
                    const response = await jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'qsm_remove_my_templates',
                            id: templateId,
                            nonce: nonce,
                        }
                    });
                    if (response.success) {
                        console.log(response.data.message);
                        setTimeout(function () {
                            button.closest('tr').fadeOut('slow', function () {
                                jQuery(this).remove();
                            });
                        }, 1000);
                    } else {
                        console.error(response.data.message || "Failed to remove the template.");
                    }
                } catch (error) {
                    console.error("AJAX request failed:", error);
                } finally {
                    loader.remove();
                    button.prop('disabled', false);
                }
            });
        });
    }
}

jQuery(document).ready(function () {
    jQuery(document).on('click', '.qsm-customize-color-settings', function (e) {
        e.preventDefault();
        let modalId = jQuery(this).data('modal-id');
        if ( modalId == undefined ) {
            MicroModal.show('qsm-theme-color-settings');
        } else {
            MicroModal.show('qsm-theme-color-settings-' + modalId);
        }
        if (jQuery('.qsm-color-field').length > 0) {
            jQuery('.qsm-color-field').wpColorPicker();
            jQuery('.qsm-color-field').each(function () {
                if (jQuery(this).attr('data-label')) {
                    jQuery(this).parents('.wp-picker-container').find('.wp-color-result-text').html( jQuery(this).attr('data-label') );
                }
            });

        }
    });
});

//QSM - Quizzes/Surveys Page

(function ($) {
    $('#qsm-upgrade-popup-opener').on( 'click', function (event) {
        event.preventDefault();
        let modalName = $(this).data('popup');
        MicroModal.show( modalName );
    });
    $("input[name='total_user_tries']").on("change", function() {
        $("input[name='limit_email_based_submission']").prop('disabled', +$(this).val() <= 0);
        $("#limit_email_based_submission").css('opacity', +$(this).val() <= 0 ? 0.5 : 1);
    }).trigger('change');
    $("input[name='limit_email_based_submission']").on("change", function() {
        if ( $(this).prop('checked') ) {
            $("#limit_email_based_submission").find(".qsm-opt-desc").show();
        } else {
            $("#limit_email_based_submission").find(".qsm-opt-desc").hide();
        }
    }).trigger('change');

    if (jQuery('body').hasClass('post-type-qsm_quiz')) {

        $('#new_quiz_button_two').on('click', function (event) {
            event.preventDefault();
            MicroModal.show('modal-2');
        });
        $(document).on('click', '.qsm-wizard-noquiz', function (event) {
            event.preventDefault();
            $('#new_quiz_button').trigger('click');
        });
        $(document).on('click', '#new_quiz_button', function (e) {
            e.preventDefault();
            MicroModal.show('model-wizard');
            var height = jQuery(".qsm-wizard-template-section").css("height");
            jQuery(".qsm-wizard-setting-section").css("height", height);
            if (jQuery("#accordion").length > 0) {
                var icons = {
                    header: "iconClosed", // custom icon class
                    activeHeader: "iconOpen" // custom icon class
                };
                jQuery("#accordion").accordion({
                    collapsible: true,
                    icons: icons,
                    heightStyle: "content"
                });
                jQuery('#accordion h3.ui-accordion-header').next().slideDown();
            }
        });

        $('#show_import_export_popup').on('click', function (event) {
            event.preventDefault();
            MicroModal.show('modal-export-import');
        });
        $(document).on('change', '.qsm_tab_content select:not(.qsm-woo-result-related-products, .qsm-woo-email-related-products), #quiz_settings_wrapper select', function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            $('.qsm_hidden_tr').hide();
            if ($('.' + name + '_' + value).length > 0) {
                $('.' + name + '_' + value).show();
            }
        });

        $(document).on('click', '#the-list .qsm-action-link-delete', function (event) {
            event.preventDefault();
            var dataid = $(this).data('id');
            var dataname = $(this).data('name');
            $('#delete_quiz_id').val(dataid + 'QID');
            $('#delete_quiz_name').val(dataname);
            MicroModal.show('modal-5');
        });
        $(document).on('click', '#the-list .qsm-action-link-duplicate', function (event) {
            event.preventDefault();
            var dataid = $(this).data('id');
            $('#duplicate_quiz_id').val(dataid + 'QID');
            MicroModal.show('modal-4');
        });
        $(document).on('click', '#the-list .qsm-action-link-reset', function (event) {
            event.preventDefault();
            var dataid = $(this).data('id');
            $('#reset_quiz_id').val(dataid);
            MicroModal.show('modal-1');
        });
        $('#reset-stats-button').on('click', function (event) {
            event.preventDefault();
            $('#reset_quiz_form').submit();
        });
        $('#duplicate-quiz-button').on('click', function (event) {
            event.preventDefault();
            $('#duplicate-quiz-form').submit();
        });
        $('#delete-quiz-button').on('click', function (event) {
            event.preventDefault();
            $('#delete-quiz-form').submit();
        });

        $(document).on('click', '.post-type-qsm_quiz #doaction, .post-type-qsm_quiz #doaction2', function (event) {
            event.preventDefault();
            if ($("#bulk-action-selector-top").val() == "delete_pr" || $("#bulk-action-selector-bottom").val() == "delete_pr") {
                MicroModal.show('modal-bulk-delete');
            } else {
                $('#posts-filter').submit();
            }
        });
        $(document).on('click', '.qsm-list-shortcode-view', function (e) {
            e.preventDefault();
            var embed_text = $(this).siblings('.sc-embed').text();
            var link_text = $(this).siblings('.sc-link').text();
            let embed_url = $(this).siblings('.sc-embed-iframe').text().trim();
            $('#sc-shortcode-model-text').val(embed_text);
            $('#sc-shortcode-model-text-link').val(link_text);
            $('#sc-embed-iframe-text').val(embed_url);
            MicroModal.show('modal-6');
        });
        $(document).on('click', '#sc-copy-shortcode', function () {
            var copyText = document.getElementById("sc-shortcode-model-text");
            copyText.select();
            document.execCommand("copy");
        });
        $(document).on('click', '#sc-copy-shortcode-link', function () {
            var copyText = document.getElementById("sc-shortcode-model-text-link");
            copyText.select();
            document.execCommand("copy");
        });
        $(document).on('click', '#sc-copy-embed-iframe', function () {
            let copyText = document.getElementById("sc-embed-iframe-text");
            copyText.select();
            document.execCommand("copy");
            let $btn = $(this);
            $btn.addClass('qsm-copied');
            setTimeout(function () { $btn.removeClass('qsm-copied'); }, 1500);
        });
        $('#bulk-delete-quiz-button').on('click', function (event) {
            event.preventDefault();
            if ($("#bult-delete-quiz-form input[name='qsm_delete_question_from_qb']").is(":checked")) {
                $("<input>", {
                    "type": "hidden",
                    "name": "qsm_delete_question_from_qb",
                    "value": "1"
                }).appendTo("#posts-filter");
            }
            if ($("#bult-delete-quiz-form input[name='qsm_delete_from_db']").is(":checked")) {
                $("<input>", {
                    "type": "hidden",
                    "name": "qsm_delete_from_db",
                    "value": "1"
                }).appendTo("#posts-filter");
            }
            $('#posts-filter').submit();
        });
    }
}(jQuery));

function qsm_is_substring_in_array( text, array ) {
    return array.some(function(item) {
        return text.includes(item);
    });
}
(function ($) {
    if (jQuery('body').hasClass('post-type-qsm_quiz') || jQuery('body').hasClass('toplevel_page_qsm_dashboard') || jQuery('body').hasClass('qsm_page_qsm_create_quiz_page')) {
        $('#create-quiz-button').on('click', function (event) {
            event.preventDefault();
            if ($('#new-quiz-form').find('.quiz_name').val() === '') {
                $('#new-quiz-form').find('.quiz_name').addClass('qsm-required');
                $('.qsm-wizard-wrap[data-show="quiz_settings"]').trigger('click');
                $('#new-quiz-form').find('.quiz_name').focus();
                return;
            }
            $('#new-quiz-form').submit();
        });

        //Hide/show the wizard quiz options
        $(document).on('change', '#quiz_settings select', function () {
            var value = $(this).val();
            if (value == 0) {
                jQuery(this).closest('.input-group').next('.input-group').show();
            } else {
                jQuery(this).closest('.input-group').next('.input-group').hide();
            }
        });

        //Show the menus on widget click
        $(document).on('click', '.qsm-new_menu_tab_items li', function (e) {
            $('.qsm-new_menu_tab_items li').removeClass('active');
            $(this).addClass('active');
            $('.qsm-new-menu-elements').hide();
            var id = $(this).attr('data-show');
            $('#' + id).show();
            e.preventDefault();
        });

        $(document).on('click', '.qsm-wizard-wrap', function (e) {
            $('.qsm-wizard-menu .qsm-wizard-wrap').removeClass('active');
            $(this).addClass('active');
            $('.qsm-new-menu-elements').hide();
            var id = $(this).attr('data-show');
            $('#' + id).fadeIn()
            $('#modal-2-content').scrollTop(0);
            switch (id) {
                case 'select_themes':
                    $('#model-wizard .qsm-popup__footer #prev-theme-button').hide();
                    $('#model-wizard .qsm-popup__footer #prev-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #next-quiz-button').show();
                    $('#model-wizard .qsm-popup__footer #create-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #choose-addons-button').hide();
                    break;
                case 'quiz_settings':
                    $('#model-wizard .qsm-popup__footer #prev-theme-button').show();
                    $('#model-wizard .qsm-popup__footer #prev-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #next-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #create-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #choose-addons-button').show();
                    break;
                case 'addons_list':
                    $('#model-wizard .qsm-popup__footer #prev-theme-button').hide();
                    $('#model-wizard .qsm-popup__footer #prev-quiz-button').show();
                    $('#model-wizard .qsm-popup__footer #next-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #create-quiz-button').show();
                    $('#model-wizard .qsm-popup__footer #choose-addons-button').hide();
                    break;
                default:
                    $('#model-wizard .qsm-popup__footer #prev-theme-button').hide();
                    $('#model-wizard .qsm-popup__footer #prev-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #next-quiz-button').show();
                    $('#model-wizard .qsm-popup__footer #create-quiz-button').hide();
                    $('#model-wizard .qsm-popup__footer #choose-addons-button').hide();
                    break;
            }
            e.preventDefault();
        });
        $(document).on('click', '#model-wizard .qsm-popup__footer #prev-theme-button', function (e) {
            $('.qsm-wizard-wrap[data-show="select_themes"]').trigger('click');
            e.preventDefault();
        });
        $(document).on('click', '#model-wizard .qsm-popup__footer #prev-quiz-button', function (e) {
            $('.qsm-wizard-wrap[data-show="quiz_settings"]').trigger('click');
            e.preventDefault();
        });
        $(document).on('click', '#model-wizard .qsm-popup__footer #choose-addons-button', function (e) {
            $('.qsm-wizard-wrap[data-show="addons_list"]').trigger('click');
            e.preventDefault();
        });
        $(document).on('click', '#model-wizard .qsm-popup__footer #next-quiz-button', function (e) {
            $('.qsm-wizard-wrap[data-show="quiz_settings"]').trigger('click');
            e.preventDefault();
        });
        $(document).on('click', '.theme-sub-menu li', function (e) {
            e.preventDefault();
            var id = $(this).children('a').attr('data-show');
            $('.theme-sub-menu li').removeClass('active');
            $(this).addClass('active');
            $('.theme-wrap').hide();
            $('#' + id).show();
        });
        $(document).on('click', '#downloaded_theme .theme-wrapper:not(.market-theme)', function (e) {
            e.preventDefault();
            $('#downloaded_theme .theme-wrapper').removeClass('active');
            $('#downloaded_theme .theme-wrapper').find('.theme-name').stop().fadeTo('slow', 0);
            $(this).find('input[name="quiz_theme_id"]').prop("checked", true);
            $(this).addClass('active');
            $(this).find('.theme-name').stop().fadeTo('slow', 1);
            if ($(this).find('input[name="quiz_theme_id"]').val() == 0) {
                $('#model-wizard .featured_image').hide();
                $('#model-wizard .featured_image .quiz_featured_image').val('');
                $('#model-wizard #quiz_settings #pagination').val(0).parents('.input-group').hide();
                $('#model-wizard #quiz_settings #progress_bar-0').prop('checked', true).parents('.input-group').hide();
                $('#model-wizard #quiz_settings #enable_pagination_quiz-0').prop('checked', true).parents('.input-group').hide();
                $('#model-wizard #quiz_settings #disable_scroll_next_previous_click-0').prop('checked', true).parents('.input-group').hide();
            } else {
                $('#model-wizard .featured_image').show();
                $('#model-wizard #quiz_settings #pagination').val(1).parents('.input-group').show();
                $('#model-wizard #quiz_settings #progress_bar-1').prop('checked', true).parents('.input-group').show();
                $('#model-wizard #quiz_settings #enable_pagination_quiz-1').prop('checked', true).parents('.input-group').show();
                $('#model-wizard #quiz_settings #disable_scroll_next_previous_click-1').prop('checked', true).parents('.input-group').show();
            }
        });

        $(document).on('mouseover', '#downloaded_theme .theme-wrapper, #browse_themes .theme-wrapper', function (e) {
            e.preventDefault();
            if (!$(this).hasClass('active')) {
                $(this).find('.theme-name').stop().fadeTo('slow', 1);
            }
        });

        $(document).on('mouseout', '#downloaded_theme .theme-wrapper, #browse_themes .theme-wrapper', function (e) {
            e.preventDefault();
            if (!$(this).hasClass('active')) {
                $(this).find('.theme-name').stop().fadeTo('slow', 0);
            }
        });

        $(document).find('#select_themes .theme-actions').remove();

    }
}(jQuery));

// The "Database update required" notice for multiple categories lives in js/qsm-admin-notices.js:
// it shows on every admin page, and this file loads on QSM screens only (QSM_Admin_Assets).


/**
 * Pin a template-rendered <textarea>'s current value before wp.editor.initialize().
 *
 * With quicktags on, wp.editor.initialize() moves the textarea into a new wrapper. WordPress'
 * wp-emoji MutationObserver sees it added and runs twemoji over it, replacing each emoji in the
 * textarea's text node with an <img>. A textarea's value is its child TEXT only, so the emoji
 * vanish before TinyMCE reads them, and the next save writes them out of the DB. Setting the
 * value from script marks it dirty, which detaches it from later child-node changes.
 * Only bites when the browser fails WP's emoji support test (twemoji loaded), e.g. WP 7.1 / Emoji 17.
 */
function qsmPinTextareaValue( id ) {
    const $textarea = jQuery( '#' + id );
    $textarea.val( $textarea.val() );
}

function qsmConvertContentToShortcode( contentToConvert ){
    let updatedContent = contentToConvert
    .replace(/\[qsm([^\]]*)\](.*?)\[\/qsm([^\]]*)\]/gs, function(match, attributes, content, closingAttributes) {
        return `<qsmextrashortcodetag>[qsm${attributes}]</qsmextrashortcodetag>&nbsp;${content}<qsmextrashortcodetag>[\/qsm${closingAttributes}]</qsmextrashortcodetag>&nbsp;`;
    });
    return updatedContent;
}
//TinyMCE slash command auto suggest
(function ($) {
    if (jQuery('body').hasClass('admin_page_mlw_quiz_options') || jQuery('body').hasClass('qsm_page_qmn_global_settings')) {
        if ( window.location.href.indexOf('tab=emails') > 0 || window.location.href.indexOf('tab=results-pages') > 0 || window.location.href.indexOf('tab=contact') > 0 || window.location.href.indexOf('tab=quiz-default-template') > 0 ) {
            function addTinyMceAutoSuggestion() {
                if ( 'undefined' !== typeof tinymce && null !== tinymce && 'undefined' !== typeof qsm_admin_messages &&  null !== qsm_admin_messages ) {
                    tinymce.PluginManager.add('qsmslashcommands', function(editor) {
                        //Add stylesheet
                        editor.settings.extended_valid_elements = 'qsmvariabletag,qsmextrashortcodetag';
                        editor.settings.custom_elements = '~qsmvariabletag,~qsmextrashortcodetag';
                        editor.settings.content_style = 'qsmvariabletag, qsmextrashortcodetag { color: #1E1E1E; background: #DCEDFA; margin: 0 3px; padding: 5px 8px; font-family: Arial, Helvetica, sans-serif;font-size: 12px;border-top: 1px solid #fff; line-height: 2.3; } qsmvariabletag:hover, qsmextrashortcodetag:hover{ border: 1px solid #3858e9; background-color: #fff; box-shadow: 0px 4px 4px 0px #00000033; }';
                        //Auto complete commands
                        let commands = [];
                        for (let qsm_var_group in qsm_admin_messages.qsm_variables) {
                            if ( qsm_admin_messages.qsm_variables.hasOwnProperty( qsm_var_group ) ) {
                                for (let qsm_var_item in qsm_admin_messages.qsm_variables[qsm_var_group]) {
                                    if ( qsm_admin_messages.qsm_variables[qsm_var_group].hasOwnProperty( qsm_var_item ) ) {
                                        let cname = qsm_var_item.replace(/\W/g, '');
                                        cname = cname.replace(/_/g, ' ').toLowerCase();
                                        commands.push(
                                            {
                                                name:cname,
                                                value: qsm_var_item,
                                                description:qsm_admin_messages.qsm_variables[qsm_var_group][qsm_var_item],
                                                group: qsm_var_group,

                                            }
                                        )
                                    }
                                }
                            }
                         }

                        //Show autocomplete modal
                        function showAutocomplete( editor, clear =false ) {
                            removeAutocomplete( editor, clear );
                            let autocomplete = document.createElement('div');
                            autocomplete.className = 'qsm-autocomplete';
                            let newCommand = commands;
                            //Get search
                            let qsm_search =  editor.getContainer().getAttribute('qsm_search');
                            if ( 'undefined' !== typeof qsm_search && null !== qsm_search && '' !== qsm_search ) {
                                if ( false === clear ) {
                                    qsm_search = qsm_search.toLowerCase();
                                    newCommand = commands.filter(suggestion =>
                                        suggestion.name.toLowerCase().startsWith( qsm_search )
                                    );
                                }
                            } else {
                                qsm_search = '';
                            }

                            // Create a container div for the input and label
                            let inputContainer = document.createElement('div');
                            inputContainer.className = 'qsm-autocomplete-input-container'; // Add a class for styling if needed

                            // Create a label element
                            let inputLabel = document.createElement('label');
                            inputLabel.for = 'searchInput';
                            inputLabel.textContent = 'Search:';

                            // Create the input element
                            let searchInput = document.createElement('input');
                            searchInput.type = 'text';
                            searchInput.id = 'searchInput'; // Link this id with the label
                            searchInput.placeholder = 'Type to search...';
                            searchInput.className = 'qsm-autocomplete-search-input'; // Add a class for styling if needed

                            // Append the label and input to the container
                            inputContainer.appendChild(inputLabel);
                            inputContainer.appendChild(searchInput);

                            // Append the container to the autocomplete div
                            autocomplete.appendChild(inputContainer);

                            if ( 0 < newCommand.length ) {
                                let var_group = [];
                                newCommand.forEach(function (command, key) {
                                    //Add Group Name
                                    if ( -1 == var_group.indexOf( command.group ) ) {
                                        var_group.push( command.group );
                                        let item_group = document.createElement('div');
                                        item_group.className = 'qsm-autocomplete-item-group';
                                        item_group.textContent = command.group;
                                        autocomplete.appendChild(item_group);
                                    }
                                    //Add Item
                                    var item = document.createElement('div');
                                    if (0 == key) {
                                        item.classList.add('qsm-autocomplete-item-active');
                                    }
                                    item.classList.add('qsm-autocomplete-item');
                                    item.setAttribute('title', command.description);
                                    item.setAttribute('variable', command.name);
                                    item.innerHTML = "<span class='qsm-autocomplete-item-title'>%" + command.name + "%</span><span class='qsm-autocomplete-item-description'>" + command.description + "</span>";
                                    item.onclick = function() {
                                        for (let i = 0; i <= qsm_search.length; i++) {
                                            editor.execCommand('Delete');
                                        }
                                        editor.execCommand('mceInsertContent', false, command.value
                                            .replace(/%([^%]+)%/g, '&nbsp;<qsmvariabletag>$1</qsmvariabletag>&nbsp;')
                                            .replace(/\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs, '<qsmextrashortcodetag>$1</qsmextrashortcodetag>&nbsp;')
                                        );
                                        autocomplete.remove();
                                        editor.getContainer().setAttribute('qsm_search', '');
                                        editor.qsmShowAutocomplete = false;
                                    };
                                    autocomplete.appendChild(item);
                                });
                            } else {
                                //No Variable Found
                                let item_group = document.createElement('div');
                                item_group.className = 'qsm-autocomplete-no-item';
                                item_group.textContent = qsm_admin_messages.no_variables;
                                autocomplete.appendChild(item_group);
                            }
                            //Add autocomplete modal
                            editor.getContainer().appendChild(autocomplete);
                            editor.qsmShowAutocomplete = true;
                        }

                        //Remove autocomplete modal
                        function removeAutocomplete( editor, clear = true ) {
                            //Remove auto complete
                            let autocomplete =  editor.getContainer().querySelector('.qsm-autocomplete');
                            if ( 'undefined' !== typeof autocomplete && null !== autocomplete  ) {
                                autocomplete.remove();
                            }
                            if ( true === clear ) {
                            let qsm_search =  editor.getContainer().getAttribute('qsm_search');
                                if ( 'undefined' !== typeof qsm_search && null !== qsm_search && '' !== qsm_search ) {
                                    editor.getContainer().setAttribute('qsm_search', '');
                                }
                            }
                            editor.qsmShowAutocomplete = false;
                        }

                        //on keydowm inside editor
                        editor.on('keydown', function (e) {

                            if (e.keyCode === 13) {
                                let selection = editor.selection;
                                let range = selection.getRng();
                                let tagText = range.startContainer.parentNode.textContent;
                                let nodeName = range.startContainer.parentNode.nodeName.toLowerCase();
                                if ( (nodeName === 'qsmvariabletag' || nodeName === 'qsmextrashortcodetag') && range.startOffset === tagText.length ) {
                                  let newParagraph = editor.dom.create('p');
                                  editor.dom.insertAfter(newParagraph, range.startContainer.parentNode);
                                  range.setStartAfter(newParagraph);
                                  range.collapse(true);
                                  selection.setRng(range);
                                  e.preventDefault();
                                }
                            }

                            if (e.keyCode === 191 && e.ctrlKey === false && e.altKey === false && e.shiftKey === false) {
                              // "/" key pressed, trigger autocomplete
                              showAutocomplete( editor, true );
                            } else if ( 'undefined' !== typeof editor.qsmShowAutocomplete && null !== editor.qsmShowAutocomplete && true === editor.qsmShowAutocomplete ) {
                             //Prepare search word if autocomplete modal is visible
                              let keyCode = e.keyCode;
                              let isAlphanumeric = (keyCode >= 48 && keyCode <= 57) || (keyCode >= 65 && keyCode <= 90) || (keyCode >= 97 && keyCode <= 122);
                               if (  isAlphanumeric || 8 == keyCode ) {
                                    let qsm_search =  editor.getContainer().getAttribute('qsm_search');
                                    if ( 'undefined' === typeof qsm_search || null === qsm_search ) {
                                        qsm_search = '';
                                    }
                                    //Remove autocomplete modal if press backspace to remove slash
                                    if ( '' == qsm_search && 8 == keyCode ) {
                                        removeAutocomplete( editor );
                                    } else {
                                        if ( 8 == keyCode ) {
                                            //backspace remove last character
                                            qsm_search = qsm_search.slice(0, -1);
                                        } else {
                                            qsm_search += e.key;
                                        }
                                         editor.getContainer().setAttribute('qsm_search', qsm_search);

                                        showAutocomplete(editor);
                                    }
                                }
                                if (40 === e.keyCode) {
                                    let active_item = jQuery('.qsm-autocomplete-item-active');
                                    jQuery('.qsm-autocomplete-item').removeClass('qsm-autocomplete-item-active');
                                    if (active_item.length) {
                                        active_item.next('.qsm-autocomplete-item').addClass('qsm-autocomplete-item-active');
                                    } else {
                                        jQuery('.qsm-autocomplete-item:first').addClass('qsm-autocomplete-item-active');
                                    }
                                    e.preventDefault();
                                }

                                if (38 === e.keyCode) {
                                    let active_item = jQuery('.qsm-autocomplete-item-active');
                                    jQuery('.qsm-autocomplete-item').removeClass('qsm-autocomplete-item-active');
                                    if (active_item.length) {
                                        active_item.prev('.qsm-autocomplete-item').addClass('qsm-autocomplete-item-active');
                                    } else {
                                        jQuery('.qsm-autocomplete-item:last').addClass('qsm-autocomplete-item-active');
                                    }
                                    e.preventDefault();
                                }
                                if ( 13 == e.keyCode ) {
                                    if (jQuery('.qsm-autocomplete-item-active').length) {
                                        jQuery('.qsm-autocomplete-item-active').click();
                                        e.preventDefault();
                                    }
                                }
                            }

                        });

                        editor.on('paste', function (event) {
                            let clipboardData = (event.originalEvent || event).clipboardData;
                            let pastedValue = clipboardData.getData('text');
                            let variables = commands.map(function(item) {
                                return item.value;
                            });
                            if (variables.includes(pastedValue) || /\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs.test(pastedValue)) {
                                event.preventDefault();
                                // Replace the variable tags and qsm shortcodes correctly
                                let updatedContent = pastedValue.replace(/%([^%]+)%/g, '&nbsp;<qsmvariabletag>$1</qsmvariabletag>&nbsp;');  // Handle %variable%
                                updatedContent = qsmConvertContentToShortcode(updatedContent);
                                editor.execCommand('mceInsertContent', false, updatedContent);
                            }
                        });

                        // Stop multiple times registering click event
                        $(document).off('click', '.qsm-slashcommand-variables-button').on('click', '.qsm-slashcommand-variables-button', function(e) {
                            e.preventDefault();
                            let id = $(this).data('id');
                            let editor = tinymce.get(id);
                            let contentToInsert = '/';
                            editor.focus();
                            editor.selection.setContent(contentToInsert);
                            showAutocomplete(editor, true);
                        });

                        $(document).off('input', '.qsm-autocomplete-search-input').on('input', '.qsm-autocomplete-search-input', function () {
                            let query = $(this).val().toLowerCase(); // Get the current input value and convert to lowercase
                            let hasResults = false;

                            $('.qsm-autocomplete-item').each(function () {
                                let title = $(this).attr('variable').toLowerCase(); // Get the title attribute of the current item
                                if (title.includes(query)) {
                                    $(this).show(); // Show items that match the query
                                    hasResults = true; // At least one item matches
                                } else {
                                    $(this).hide(); // Hide items that don't match
                                }
                            });

                            // Check for the "no items" message
                            let noItemElement = $(this).parents('.qsm-autocomplete').find('.qsm-autocomplete-no-item');

                            if (hasResults) {
                                noItemElement.remove(); // Remove the "no items" message if results are found
                                $(this).parents('.qsm-autocomplete').find('.qsm-autocomplete-item-group').show();
                            } else if (noItemElement.length === 0) { // Append only if it doesn't already exist
                                let item_group = document.createElement('div');
                                item_group.className = 'qsm-autocomplete-no-item';
                                item_group.textContent = qsm_admin_messages.no_variables;
                                $(this).parents('.qsm-autocomplete').append(item_group);
                                $(this).parents('.qsm-autocomplete').find('.qsm-autocomplete-item-group').hide();
                            }
                        });
                    });
                }
            }
            addTinyMceAutoSuggestion();

            $( document ).on( 'click', '.qsm-extra-shortcode-popup', function( e ) {
                e.preventDefault();
                MicroModal.show('modal-extra-shortcodes');
            } );

            $( document ).on( 'click', '.qsm-webhooks-pricing-popup', function( e ) {
                if ( $(e.target).closest('.qsm-webhooks-pricing-popup-details').length ) {
                    return; // Allow default link behavior
                }
                e.preventDefault();
                MicroModal.show('modal-qsm-webhooks');
            } );

            jQuery(document).on('qsm_after_add_result_block', function(event, conditions, page, redirect, total) {
                let $matchingElement = $(`#results-page-${total}`);
                let $button = $matchingElement.parents('.results-page-show').find('.qsm-result-editor-custom-button');
                $button.attr('data-id', total - 1);
            });

            jQuery(document).on('qsm_after_add_email_block', function(event, conditions, to, subject, content, replyTo, total) {
                let $matchingElement = $(`#email-template-${total}`);
                let $button = $matchingElement.parents('.email-show').find('.qsm-email-editor-custom-button');
                $button.attr('data-id', total - 1);
            });
        }
    }
}(jQuery));

// QSM - Admin Stats Page
(function ($) {
    if (jQuery('body').hasClass('qsm_page_qmn_stats')) {
        if (window.stats_graph instanceof Chart) {
            window.stats_graph.destroy();
        }
        var graph_ctx = document.getElementById("graph_canvas").getContext("2d");
        window.stats_graph = new Chart(graph_ctx, {
            type: 'line',
            data: {
                labels: qsm_admin_stats.labels,
                datasets: [{
                    label: qsm_admin_messages.quiz_submissions, // Name the series
                    data: qsm_admin_stats.value, // Specify the data values array
                    fill: false,
                    borderColor: '#2196f3', // Add custom color border (Line)
                    backgroundColor: '#2196f3', // Add custom color background (Points and Fill)
                    borderWidth: 1 // Specify bar border width
                }]
            },
            options: {
                responsive: true, // Instruct chart js to respond nicely.
                maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height
            }
        });
    }
}(jQuery));


/**
 * QSM - Contact Form
 */

var QSMContact;
(function ($) {
    if (jQuery('body').hasClass('admin_page_mlw_quiz_options')) {
        if (window.location.href.indexOf('tab=contact') > 0) {

            QSMContact = {
                load: function () {
                    if ($.isArray(qsmContactObject.contactForm) && qsmContactObject.contactForm.length > 0) {
                        $.each(qsmContactObject.contactForm, function (i, val) {
                            QSMContact.addField(val);
                        });
                    }
                },
                addField: function (fieldArray) {
                    let template = wp.template('qsm-contact-form-field');

                    $('.contact-form').append(template(fieldArray));

                    $('.qsm-contact-form-field').each(function () {
                        QSMContact.hideShowSettings($(this));
                    });
                    setTimeout(QSMContact.removeNew, 250);
                },
                removeNew: function () {
                    $('.qsm-contact-form-field').removeClass('new');
                },
                duplicateField: function (linkClicked) {
                    let fieldArray = QSMContact.prepareFieldData(linkClicked.parents('.qsm-contact-form-field'));
                    QSMContact.addField(fieldArray);
                },
                deleteField: function (field) {
                    let parent = field.parents('.qsm-contact-form-field');
                    parent.addClass('deleting');
                    setTimeout(function () {
                        parent.remove();
                    }, 250);
                },
                newField: function () {
                    let fieldArray = {
                        label: '',
                        type: 'text',
                        answers: [],
                        required: false,
                        hide_label: false,
                        use_default_option: false,
                        use: '',
                        enable: true,
                        is_default: false
                    };
                    jQuery(document).trigger('qsm_add_contact_field', [fieldArray]);
                    QSMContact.addField(fieldArray);
                },
                prepareFieldData: function (field) {
                    var fieldArray = {
                        label: field.find('.label-control').val(),
                        type: field.find('.type-control').val(),
                        required: field.find('.qsm-required-control').prop('checked'),
                        hide_label: field.find('.qsm-hide-label-control').prop('checked'),
                        use_default_option: field.find('.qsm-use-default-control').prop('checked'),
                        use: field.find('.use-control').val() || field.find('.label-control').val().toLowerCase(),
                        enable: field.find('.enable-control').prop('checked'),
                    };
                    /**
                     * Store Other settings
                     */
                    field.find('.qsm-contact-form-field-settings :input').each(function () {
                        var inputName = $(this).attr('name');
                        var inputVal = $(this).val();
                        if ('checkbox' == $(this).attr('type')) {
                            inputVal = $(this).prop('checked');
                        }
                        fieldArray[inputName] = inputVal;
                    });
                    return fieldArray;
                },
                save: function () {
                    QSMAdmin.displayAlert(qsm_admin_messages.saving_contact_fields, 'info');
                    let contactFields = $('.qsm-contact-form-field');
                    var contactForm = [];
                    var contactEach;
                    $.each(contactFields, function (i, val) {
                        contactEach = QSMContact.prepareFieldData($(this));
                        contactForm.push(contactEach);
                    });

                    var settings = {};
                    $('#contactformsettings input').each(function () {
                        if ('checkbox' == $(this).attr('type')) {
                            settings[$(this).attr('name')] = ($(this).prop('checked') ? '1' : '0');
                        } else if ('radio' == $(this).attr('type') && $(this).prop('checked')) {
                            settings[$(this).attr('name')] = $(this).val();
                        } else if ('text' == $(this).attr('type')) {
                            settings[$(this).attr('name')] = $(this).val();
                        }
                    });

                    var data = {
                        action: 'qsm_save_contact',
                        contact_form: contactForm,
                        settings: settings,
                        quiz_id: qsmContactObject.quizID,
                        nonce: qsmContactObject.saveNonce,
                    };
                    jQuery(document).trigger('qsm_contact_field_save_settings_before', [data]);

                    jQuery.post(ajaxurl, data, function (response) {
                        QSMContact.saved(JSON.parse(response));
                    });
                },
                saved: function (response) {
                    if (response.status) {
                        QSMAdmin.displayAlert(qsm_admin_messages.contact_fields_saved, 'success');
                    } else {
                        QSMAdmin.displayAlert(qsm_admin_messages.contact_fields_save_error + ' ' + qsm_admin_messages.try_again, 'error');
                    }
                },
                displayAlert: function (message, type) {
                    QSMContact.clearAlerts();
                    $('.contact-message').addClass('notice');
                    switch (type) {
                        case 'info':
                            $('.contact-message').addClass('notice-info');
                            break;
                        case 'error':
                            $('.contact-message').addClass('notice-error');
                            break;
                        case 'success':
                            $('.contact-message').addClass('notice-success');
                            break;
                        default:
                    }
                    $('.contact-message').append('<p>' + message + '</p>');
                },
                clearAlerts: function () {
                    jQuery('#footer-bar-notice').removeClass('show');
                    $('.contact-message').empty().removeClass().addClass('contact-message');
                },
                hideShowSettings: function (field) {
                    var type = field.find('.type-control').val();
                    var useValue = field.find('.use-control').val();
                    if (field.find('.qsm-required-control').prop('checked')) {
                        field.find('.field-required-flag').show();
                    }
                    if (!field.find('.enable-control').prop('checked')) {
                        field.addClass('disabled-field');
                        if (!$('.show-disabled-fields').prop('checked')) {
                            field.addClass('hidden-field');
                        }
                    }
                    field.find('.qsm-contact-form-field-settings .qsm-contact-form-group:not(.qsm-required-option, .qsm-hide-label-option)').hide();
                    if (['text', 'number'].includes(type)) {
                        field.find('.qsm-contact-form-field-settings .qsm-min-max-option').show();
                    }
                    if ('email' == type) {
                        field.find('.qsm-contact-form-field-settings .qsm-email-option').show();
                    }
                    if ('phone' == useValue) {
                        field.find('.qsm-contact-form-field-settings .qsm-phone-option').show();
                    }
                    if (['radio', 'select'].includes(type)) {
                        field.find('.qsm-contact-form-field-settings .qsm-field-options').show();
                    }
                    if (['text', 'number', 'url', 'email'].includes(type)) {
                        field.find('.qsm-contact-form-field-settings .qsm-placeholder-option').show();
                    }
                    if (['checkbox', 'radio', 'select', 'date'].includes(type)) {
                        field.find('.qsm-contact-form-field-settings .qsm-hide-label-option').hide();
                    }
                    if ( 'select' == type ) {
                        field.find('.qsm-contact-form-field-settings .qsm-use-default-option').show();
                    }
                    jQuery(document).trigger('qsm_contact_field_hide_show_settings', [field, type]);
                }
            };
            $(function () {
                QSMContact.load();
                if ($('.contact-form > .qsm-contact-form-field').length === 0) {
                    $('.save-contact').hide();
                }
                $('.add-contact-field').on('click', function () {
                    QSMContact.newField();
                    if ($('.contact-form > .qsm-contact-form-field').length === 0) {
                        $('.save-contact').hide();
                    } else {
                        $('.save-contact').show();
                    }
                });
                $('.save-contact').on('click', function () {
                    QSMContact.save();
                });
                $('.contact-form').on('click', '.delete-field', function (event) {
                    event.preventDefault();
                    if (!$(this).hasClass('disabled')) {
                        QSMContact.deleteField($(this));
                    }
                    return false;
                });
                $('.contact-form').on('click', '.copy-field', function (event) {
                    event.preventDefault();
                    QSMContact.duplicateField($(this));
                });
                $('.contact-form').on('click', '.settings-field', function (event) {
                    event.preventDefault();
                    let target = $(this).parents('.qsm-contact-form-field').find('.qsm-contact-form-field-settings');
                    $('.qsm-contact-form-field-settings').not(target).hide();
                    target.toggle();
                });
                $('.contact-form').on('change', '.type-control', function (event) {
                    event.preventDefault();
                    QSMContact.hideShowSettings($(this).parents('.qsm-contact-form-field'));
                });
                $('.contact-form').on('change', '.qsm-required-control', function (event) {
                    event.preventDefault();
                    $(this).parents('.qsm-contact-form-field').find('.field-required-flag').hide();
                    if ($(this).is(':checked')) {
                        $(this).parents('.qsm-contact-form-field').find('.field-required-flag').show();
                    }
                });
                $('.contact-form').on('change', '.enable-control', function (event) {
                    event.preventDefault();
                    $(this).parents('.qsm-contact-form-field').addClass('disabled-field');
                    if ($(this).is(':checked')) {
                        $(this).parents('.qsm-contact-form-field').removeClass('disabled-field');
                    }
                    QSMContact.hideShowSettings($(this).parents('.qsm-contact-form-field'));
                });
                $(document).on('change', '.show-disabled-fields', function (event) {
                    event.preventDefault();
                    var is_show = $(this).prop('checked');
                    jQuery.post(ajaxurl, { action: 'qsm_show_disabled_contact_fields', show: is_show, 'nonce': qsmContactObject.saveNonce, 'quiz_id': qsmContactObject.quizID });
                    if (is_show) {
                        $('.qsm-contact-form-field').removeClass('hidden-field');
                    } else {
                        $('.qsm-contact-form-field.disabled-field').addClass('hidden-field');
                    }
                });
                $('.contact-form').sortable({
                    opacity: 70,
                    cursor: 'grabbing',
                    handle: 'span.dashicons-move'
                });
            });
        }
    }
}(jQuery));

/**
* QSM - Admin emails
*/


(function ($) {
    if (jQuery('body').hasClass('admin_page_mlw_quiz_options')) {
        if (window.location.href.indexOf('tab=emails') > 0) {
            var QSMAdminEmails;
            QSMAdminEmails = {
                total: 0,
                saveEmails: function () {
                    QSMAdmin.displayAlert(qsm_admin_messages.saving_emails, 'info');
                    var emails = [];
                    var email = {};
                    $('.qsm-email').each(function () {
                        var email_content = '';
                        if ($(this).find('.email-template').parent('.wp-editor-container').length > 0) {
                            email_content = wp.editor.getContent($(this).find('.email-template').attr('id'));
                        } else {
                            email_content = $(this).find('.email-template').val()
                        }
                        let default_mark = $(this).find('.qsm-mark-as-default:checked').length ? $(this).find('.qsm-mark-as-default:checked').val() : false;
                        email = {
                            'conditions': [],
                            'to': $(this).find('.qsm-to-email').val(),
                            'subject': $(this).find('.qsm-email-subject').val(),
                            'content': email_content,
                            'replyTo': $(this).find('.reply-to').prop('checked'),
                            'default_mark': default_mark,
                            'is_updated': 1,
                        };
                        $(this).find('.email-condition').each(function () {
                            email.conditions.push({
                                'category': $(this).find('.email-condition-category').val(),
                                'extra_condition': $(this).find('.email-extra-condition-category').val(),
                                'criteria': $(this).find('.email-condition-criteria').val(),
                                'operator': $(this).find('.email-condition-operator').val(),
                                'value': $(this).find('.email-condition-value').val()
                            });
                        });
                        emails.push(email);
                    });
                    let _X_validation = false;
                    _.each(emails, function( email ) {
                        if( email.content.indexOf('_X%') != -1 || email.subject.indexOf('_X%') != -1 ) {
                            _X_validation = true;
                        }
                    });
                    if( _X_validation ) {
                        QSMAdmin.displayAlert( qsm_admin_messages._X_validation_fails, 'error');
                        return false;
                    }
                    var data = {
                        'emails': emails,
                        'rest_nonce': qsmEmailsObject.rest_user_nonce
                    }
                    $.ajax({
                        url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmEmailsObject.quizID + '/emails',
                        method: 'POST',
                        data: data,
                        headers: { 'X-WP-Nonce': qsmEmailsObject.nonce },
                    })
                        .done(function (results) {
                            if (results.status) {
                                jQuery(document).trigger('qsm_after_save_email');
                                QSMAdmin.displayAlert(qsm_admin_messages.emails_saved, 'success');
                            } else {
                                QSMAdmin.displayAlert(qsm_admin_messages.emails_save_error + ' ' + qsm_admin_messages.try_again, 'error');
                            }
                        })
                        .fail(QSMAdmin.displayjQueryError);

                },
                loadEmails: function () {
                    $.ajax({
                        url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmEmailsObject.quizID + '/emails',
                        headers: { 'X-WP-Nonce': qsmEmailsObject.nonce },
                    })
                        .done(function (emails) {
                            $('#qsm_emails').find('.qsm-spinner-loader').remove();
                            emails.forEach(function (email, i, emails) {
                                QSMAdminEmails.addEmail(email.conditions, email.to, email.subject, email.content, email.replyTo, email.default_mark);
                            });
                            QSMAdmin.clearAlerts();
                            jQuery(document).find('.qsm-email-my-template-container').hide();
                        })
                        .fail(QSMAdmin.displayjQueryError);
                },
                addCondition: function ($email, category, extra_condition, criteria, operator, value) {
                    var template = wp.template('email-condition');
                    $email.find('.qsm-email-when-conditions').append(template({
                        'category': category,
                        'extra_condition': extra_condition,
                        'criteria': criteria,
                        'operator': operator,
                        'value': value
                    }));
                    $email.find('.email-condition').each(function () {
                        let extraCategory = jQuery(this).find('.email-extra-condition-category');
                        if ('quiz' == jQuery(this).find('.email-condition-category').val() || '' == jQuery(this).find('.email-condition-category').val()) {
                            extraCategory.closest('.email-extra-condition-category-container').hide();
                            jQuery(this).find('.email-condition-operator-container').show();
                            jQuery(this).find('option.qsm-questions-criteria-container').show();
                            jQuery(this).find('option.qsm-score-criteria-container').show()
                        } else if ('category' == jQuery(this).find('.email-condition-category').val()) {
                            jQuery(this).find('.option.qsm-questions-criteria').hide();
                            extraCategory.find('option').hide();
                            extraCategory.find('.qsm-condition-category-container').show();
                            jQuery(this).find('option.qsm-score-criteria-container').show()
                            jQuery(this).find('.email-condition-operator-container').show();
                        }
                        jQuery(this).find('.email-extra-condition-category-container .qsm-extra-condition-label').html( jQuery(this).find('.email-condition-category option:selected' ).text());
                    });
                    jQuery(document).trigger('qsm_after_add_email_condition', [$email, category, extra_condition, criteria, operator, value]);
                },
                newCondition: function ($email) {
                    QSMAdminEmails.addCondition($email, 'quiz', '', 'score', 'equal', 0);
                },
                updateEmailConditonCount: function ( $parent ) {
                    $parent.find('.email-condition').each(function (index) {
                        jQuery(this).find('.qsm-condition-collection-count').text(index + 1);
                    });
                },
                addEmail: function (conditions, to, subject, content, replyTo, default_mark = false) {
                    QSMAdminEmails.total += 1;
                    var template = wp.template('email');
                    $('#qsm_emails').append(template({ id: QSMAdminEmails.total, to: to, subject: subject, content: content, replyTo: replyTo, default_mark: default_mark }));
                    conditions.forEach(function (condition, i, conditions) {
                        QSMAdminEmails.addCondition(
                            $('.qsm-email:last-child'),
                            condition.category,
                            condition.extra_condition,
                            condition.criteria,
                            condition.operator,
                            condition.value
                        );
                    });
                    if (qsmEmailsObject.qsm_user_ve === 'true') {
                        var settings = {
                            mediaButtons: true,
                            tinymce: {
                                plugins: "qsmslashcommands link image lists charmap colorpicker textcolor hr fullscreen wordpress",
                                forced_root_block: '',
                                toolbar1: 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,fullscreen,wp_adv',
                                toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help,wp_code',
                                height: 200,
                            },
                            quicktags: true,
                        };
                        jQuery(document).trigger('qsm_tinyMCE_settings_after', [settings]);
                        qsmPinTextareaValue('email-template-' + QSMAdminEmails.total);
                        wp.editor.initialize('email-template-' + QSMAdminEmails.total, settings);
                    }
                    const $emailBlock = jQuery(`#email-template-${QSMAdminEmails.total}`).closest('.email-show');
                    const $conditionalButton = $emailBlock.find('.qsm-extra-shortcode-conditional-button');
                    $emailBlock.find('.wp-media-buttons .insert-media').after($conditionalButton);
                    QSMAdminEmails.displayEmailEditor( $emailBlock );
                    QSMAdminEmails.addMyTemplateOptions($emailBlock);
                    jQuery(document).trigger('qsm_after_add_email_block', [conditions, to, subject, content, replyTo, QSMAdminEmails.total]);
                },
                addMyTemplateOptions: function ($emailBlock) {
                    var $selectBox = $emailBlock.parents('.qsm-email').find('.qsm-to-replace-page-template');
                    $selectBox.empty();
                    $selectBox.append('<option value="">'+qsm_admin_messages.select_template+'</option>');
                    $.each(qsmEmailsObject.my_tmpl_data, function(key, value) {
                        $selectBox.append($('<option>', {
                            value: value.id,
                            text: value.template_name
                        }));
                    });
                    $selectBox.select2({
                        placeholder: qsm_admin_messages.select_template,
                        allowClear: true,
                        initSelection: function(e, cb) { }
                    });
                },
                newEmail: function () {
                    var conditions = [{
                        'category': '',
                        'extra_condition': '',
                        'criteria': 'score',
                        'operator': 'greater',
                        'value': '0'
                    }];
                    var to = '%USER_EMAIL%';
                    var subject = 'Quiz Results For %QUIZ_NAME%';
                    let content = '';
                    var replyTo = false;
                    QSMAdminEmails.addEmail(conditions, to, subject, content, replyTo);
                },
                displayEmailEditor: function ($emailBlock) {
                    let email_page = $emailBlock.data('email-page');
                    let editor = tinymce.get('email-template-' + email_page);
					let content = wp.editor.getContent('email-template-' + email_page);
                    if (content === '') {
                        $emailBlock.find('.qsm-email-page-then-box-styles-wrap').hide();
                        $emailBlock.find('.qsm-email-page-template-options').show();
                    } else {
                        $emailBlock.find('.qsm-email-page-template-options').hide();
                        $emailBlock.find('.qsm-email-page-then-box-styles-wrap').show();
                        $emailBlock.find('.qsm-email-page-common-section').each(function () {
                            let section = $(this);
                            if (section.children().length > 0) {
                                if (!section.is(':visible')) {
                                    section.show();
                                }
                            } else {
                                section.hide();
                            }
                        });
                    }
                }
            };
            $(function () {
                QSMAdminEmails.loadEmails();
                QSMAdminResultsAndEmail.loadMyTemplates( 'email', false );
                jQuery(document).on('click', '.qsm-start-with-template', function (e) {
                    e.preventDefault();
                    let emailPageValue = jQuery(this).parents('.email-show').data('email-page');
                    jQuery("#qsm-email-page-templates-content").attr('data-email-page', emailPageValue);
                    MicroModal.show('qsm-email-page-templates');
                });

                jQuery(document).on('click', '.qsm-start-with-canvas', function (e) {
                    e.preventDefault();
                    const $emailBlock = jQuery(this).parents('.email-show');
                    let email_page = $emailBlock.data('email-page');
                    let editor = tinymce.get('email-template-' + (email_page));
                    let updatedContent = qsmEmailsObject.default_email_template.replaceAll(/%([^%]+)%/g, '&nbsp;<qsmvariabletag>$1</qsmvariabletag>&nbsp;');
                    updatedContent = qsmConvertContentToShortcode(updatedContent).replace(/\\/g, '');
                    editor.execCommand('mceInsertContent', false, updatedContent);
                    QSMAdminEmails.displayEmailEditor( $emailBlock );
                });

                jQuery(document).on('click', '.qsm-email-page-template-preview-button, .qsm-email-page-template-card-content', function (e) {
                    e.preventDefault();
                    let indexId = jQuery(this).data('indexid');
                    jQuery('.qsm-email-page-template-container').hide();
                    jQuery('.qsm-preview-email-page-template-container').show();
                    jQuery('.qsm-preview-template-image-close').show();
                    let backgroundImage = jQuery(this).parents('.qsm-email-page-template-card').data('url');
                    jQuery('.qsm-preview-template-image').attr('src', backgroundImage);
                    let scriptTemplate = qsmEmailsObject.script_tmpl[indexId];
                    let all_dependency = qsmEmailsObject.dependency;
                    let $container = $('.qsm-email-template-dependency-addons');
                    $container.empty();
                    if (scriptTemplate?.dependency) {
                        let templateDependency = scriptTemplate.dependency;
                        if (templateDependency.trim() !== '') {
                            let dependencyIds = new Set(
                                templateDependency.split(',').map(id => Number.parseInt(id.trim()))
                            );
                            let $usedAddonsDiv = $('<div>').addClass('qsm-used-addons');
                            $usedAddonsDiv.append($('<h3>').text(qsmEmailsObject.used_addons));
                            let hasUsedAddons = false;
                            $.each(all_dependency, function(_, dependency) {
                                if (dependencyIds.has(dependency.id)) {
                                    let $anchor = $('<a>').addClass('qsm-email-template-dependency-addon').attr('href', dependency.link).attr('target', '_blank').text(dependency.name);
                                    hasUsedAddons = true;
                                    if (dependency.status === 'activated' || dependency.status === 'installed') {
                                        $anchor.addClass('qsm-email-template-dependency-addon-purple');
                                    } else {
                                        $anchor.addClass('qsm-email-template-dependency-addon-orange');
                                    }
                                    $usedAddonsDiv.append($anchor);
                                }
                            });
                            if (hasUsedAddons) {
                                $container.append($usedAddonsDiv);
                            }
                        }
                    }
                    $container.children().length > 0 ? $container.show() : $container.hide();
                });

                jQuery(document).on('click', '.qsm-email-page-template-header .qsm-email-page-tmpl-header-links', function (e) {
                    QSMAdminResultsAndEmail.headerLinks( jQuery(this), 'email' );
                });

                jQuery(document).on('click', '.qsm-email-page-template-use-button', function (e) {
                    let structure = jQuery(this).data('structure');
                    let email_index = jQuery("#qsm-email-page-templates-content").attr('data-email-page');
                    let editor = tinymce.get('email-template-' + (email_index));
                    let templateValue;
                    if (structure == 'default') {
                        templateValue = qsmEmailsObject.script_tmpl[jQuery(this).attr('data-indexid')].template_content;
                    } else if (structure == 'custom') {
                        templateValue = qsmEmailsObject.my_tmpl_data[jQuery(this).attr('data-indexid')].template_content;
                    }
                    let updatedContent = templateValue.replace(/%([^%]+)%/g, '<qsmvariabletag>$1</qsmvariabletag>&nbsp;');
                    updatedContent = qsmConvertContentToShortcode(updatedContent).replace(/\\/g, '');
                    editor.setContent('');
                    updatedContent = updatedContent + "<p></p>";
                    editor.execCommand('mceInsertContent', false, updatedContent);
                    const $emailBlock = jQuery(`#email-template-${email_index}`).closest('.email-show');
                    QSMAdminEmails.displayEmailEditor( $emailBlock );
                    MicroModal.close('qsm-email-page-templates');
                });

                $('.add-new-email').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminEmails.newEmail();
                    let $lastEmailPage = jQuery(document).find('#qsm_emails').children().last();
                    $lastEmailPage.find(".email-show > div:not(.email-content-header):not(.qsm-email-page-template-options)").hide();
                    $lastEmailPage.find('.qsm-email-page-template-options').show();
                });
                jQuery(document).on('click', '.qsm-duplicate-email-template-button', function () {
                    let template = jQuery(this).closest("header").next("main");
                    let email_content = '';
                    if (template.find('.email-template').parent('.wp-editor-container').length > 0) {
                        email_content = wp.editor.getContent(template.find('.email-template').attr('id'));
                    } else {
                        email_content = template.find('.email-template').val()
                    }
                    let conditions = [];
                    template.find('.email-condition').each(function () {
                        conditions.push({
                            'category': jQuery(this).find('.email-condition-category').val(),
                            'extra_condition': jQuery(this).find('.email-extra-condition-category').val(),
                            'criteria': jQuery(this).find('.email-condition-criteria').val(),
                            'operator': jQuery(this).find('.email-condition-operator').val(),
                            'value': jQuery(this).find('.email-condition-value').val()
                        });
                    });
                    let to = template.find('.qsm-to-email').val();
                    let subject = template.find('.qsm-email-subject').val();
                    let content = email_content;
                    let replyTo = template.find('.reply-to').prop('checked');

                    QSMAdminEmails.addEmail(conditions, to, subject, content, replyTo);
                    jQuery('html, body').animate({ scrollTop: jQuery('.qsm-email:last-child').offset().top - 150 }, 1000);
                });
                $('.save-emails').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminEmails.saveEmails();
                });
                $('#qsm_emails').on('click', '.qsm-new-condition', function (event) {
                    event.preventDefault();
                    $page = $(this).closest('.qsm-email');
                    QSMAdminEmails.newCondition($page);
                    QSMAdminEmails.updateEmailConditonCount($(this).parents('.qsm-email-when'));
                });
                $('#qsm_emails').on('click', '.qsm-delete-email-button', function (event) {
                    event.preventDefault();
                    if (!confirm(qsm_admin_messages.confirmRemovePage)) {
                        return;
                    }
                    $(this).closest('.qsm-email').remove();
                });
                $('#qsm_emails').on('click', '.delete-condition-button', function (event) {
                    event.preventDefault();
                    let $parent = $(this).parents('.qsm-email-when');
                    $(this).closest('.email-condition').remove();
                    QSMAdminEmails.updateEmailConditonCount($parent);
                });
            });
        }
    }  else if (jQuery('body').hasClass('qsm_page_qmn_global_settings')) {
        if (window.location.href.indexOf('tab=quiz-default-template') > 0) {
            // Load saved templates for default template context
            QSMAdminResultsAndEmail.loadMyTemplates( 'email', true );
            QSMAdminResultsAndEmail.loadMyTemplates( 'result', true );

            QSMAdminResultsAndEmail.addDefaultTemplateOptions('email');
            QSMAdminResultsAndEmail.addDefaultTemplateOptions('result');

            // Click handler for .qsm-view-templates-list
            $(document).on('click', '.qsm-view-templates-list', function() {
                let type = $(this).data('type');
                // Strip 'global_' prefix if present to match modal ID
                let modalType = type.replace('global_', '');
                MicroModal.show('qsm-' + modalType + '-page-templates');
                // Initialize tab state - show QSM Templates, hide My Templates
                $('.qsm-' + modalType + '-page-tmpl-header-links').removeClass('active');
                $('.qsm-' + modalType + '-page-tmpl-header-links[data-tab="page"]').addClass('active');
                $('.qsm-' + modalType + '-page-template-container').show();
                $('.qsm-' + modalType + '-my-template-container').hide();
                $('.qsm-preview-' + modalType + '-page-template-container').hide();
            });
 
            // Preview button
                jQuery(document).on('click', '.qsm-email-page-template-preview-button, .qsm-email-page-template-card-content', function (e) {
                    e.preventDefault();
                    let indexId = jQuery(this).data('indexid');
                    jQuery('.qsm-email-page-template-container').hide();
                    jQuery('.qsm-preview-email-page-template-container').show();
                    jQuery('.qsm-preview-template-image-close').show();
                    let backgroundImage = jQuery(this).parents('.qsm-email-page-template-card').data('url');
                    jQuery('.qsm-preview-template-image').attr('src', backgroundImage);
                    let scriptTemplate = (typeof qsmDefaultEmailsObject === 'undefined') ? null : qsmDefaultEmailsObject.script_tmpl[indexId];
                    let all_dependency = (typeof qsmDefaultEmailsObject === 'undefined') ? [] : qsmDefaultEmailsObject.dependency;
                    let $container = $('.qsm-email-template-dependency-addons');
                    $container.empty();
                    if (scriptTemplate?.dependency) {
                        let templateDependency = scriptTemplate.dependency;
                        if (templateDependency.trim() !== '') {
                            let dependencyIds = new Set(
                                templateDependency.split(',').map(id => Number.parseInt(id.trim()))
                            );
                            let $usedAddonsDiv = $('<div>').addClass('qsm-used-addons');
                            $usedAddonsDiv.append($('<h3>').text((typeof qsmDefaultEmailsObject === 'undefined') ? '' : qsmDefaultEmailsObject.used_addons));
                            let hasUsedAddons = false;
                            $.each(all_dependency, function(_, dependency) {
                                if (dependencyIds.has(dependency.id)) {
                                    let $anchor = $('<a>').addClass('qsm-email-template-dependency-addon').attr('href', dependency.link).attr('target', '_blank').text(dependency.name);
                                    hasUsedAddons = true;
                                    if (dependency.status === 'activated' || dependency.status === 'installed') {
                                        $anchor.addClass('qsm-email-template-dependency-addon-purple');
                                    } else {
                                        $anchor.addClass('qsm-email-template-dependency-addon-orange');
                                    }
                                    $usedAddonsDiv.append($anchor);
                                }
                            });
                            if (hasUsedAddons) {
                                $container.append($usedAddonsDiv);
                            }
                        }
                    }
                    $container.children().length > 0 ? $container.show() : $container.hide();
                });
                jQuery(document).on('click', '.qsm-email-page-template-header .qsm-email-page-tmpl-header-links', function (e) {
                    QSMAdminResultsAndEmail.headerLinks( jQuery(this), 'email' );
                });
            // Use button for templates - Only global_email handler for Quiz Default Template
                jQuery(document).on('click', '.qsm-email-page-template-use-button, .qsm-global_email-page-template-use-button', function (e) {
                    e.preventDefault();
                    let structure = jQuery(this).data('structure');
                    let indexid = jQuery(this).data('indexid');
                    let type = jQuery(this).closest('.qsm-popup').data('type');
                    let editor_id = 'default_email_template';
                    let templateValue;
                    if (structure == 'default') {
                        templateValue = (typeof qsmDefaultEmailsObject === 'undefined') ? '' : qsmDefaultEmailsObject.script_tmpl[indexid].template_content;
                    } else if (structure == 'custom') {
                        templateValue = (typeof qsmDefaultEmailsObject === 'undefined') ? '' : qsmDefaultEmailsObject.my_tmpl_data[indexid].template_content;
                    }
                    let updatedContent = templateValue.replace(/%([^%]+)%/g, '<qsmvariabletag>$1</qsmvariabletag>&nbsp;');
                    updatedContent = qsmConvertContentToShortcode(updatedContent).replace(/\\/g, '');
                    let editor = tinymce.get(editor_id);
                    if (editor) {
                        editor.setContent(updatedContent);
                    } else {
                        $('#' + editor_id).val(updatedContent);
                    }
                    MicroModal.close('qsm-' + type + '-page-templates');
                });
 
            // Tab switching
            $(document).on('click', '.qsm-page-tmpl-header-links', function() {
                let tab = $(this).data('tab');
                let type = $(this).closest('.qsm-popup').data('type');
                $('.qsm-' + type + '-page-tmpl-header-links').removeClass('active');
                $(this).addClass('active');
                if (tab === 'page') {
                    $('.qsm-' + type + '-page-template-container').show();
                    $('.qsm-' + type + '-my-template-container').hide();
                } else {
                    $('.qsm-' + type + '-page-template-container').hide();
                    $('.qsm-' + type + '-my-template-container').show();
                }
            });
 
            // Back button from preview
            $(document).on('click', '.qsm-preview-template-image-close', function() {
                let type = $(this).data('type');
                $('.qsm-preview-' + type + '-page-template-container').hide();
                $('.qsm-' + type + '-page-template-container').show();
            });
        }
    }
}(jQuery));


/**
 * QSM - Admin results pages
 */

(function ($) {
    if (jQuery('body').hasClass('admin_page_mlw_quiz_options')) {
        if (window.location.href.indexOf('tab=results-pages') > 0) {
            var QSMAdminResults;
            QSMAdminResults = {
                total: 0,
                saveResults: function () {
                    QSMAdmin.displayAlert(qsm_admin_messages.saving_results_page, 'info');
                    var pages = [];
                    var page = {};
                    var redirect_value = '';
                    let default_mark = false;
                    $('.results-page').each(function () {
                        default_mark = $(this).find('.qsm-mark-as-default:checked').length ? $(this).find('.qsm-mark-as-default:checked').val() : false;


                        page = {
                            'conditions': [],
                            'page': wp.editor.getContent($(this).find('.results-page-template').attr('id')),
                            'redirect': false,
                            default_mark: default_mark,
                            'is_updated': 1,
                        };
                        redirect_value = $(this).find('.results-page-redirect').val();
                        if ('' != redirect_value) {
                            page.redirect = redirect_value;
                        }

                        $(this).find('.results-page-condition').each(function () {
                            page.conditions.push({
                                'category': $(this).find('.results-page-condition-category').val(),
                                'extra_condition': $(this).find('.results-page-extra-condition-category').val(),
                                'criteria': $(this).find('.results-page-condition-criteria').val(),
                                'operator': $(this).find('.results-page-condition-operator').val(),
                                'value': $(this).find('.results-page-condition-value').val()
                            });
                        });
                        jQuery(document).trigger('qsm_save_result_page_before', [this, page]);
                        pages.push(page);
                    });
                    let _X_validation = false;
                    _.each(pages, function( page ) {
                        if( page.page.indexOf('_X%') != -1 ) {
                            _X_validation = true;
                        }
                    });
                    if( _X_validation ) {
                        QSMAdmin.displayAlert( qsm_admin_messages._X_validation_fails, 'error');
                        return false;
                    }
                    var data = {
                        'pages': pages,
                        'rest_nonce': qsmResultsObject.rest_user_nonce
                    }
                    $.ajax({
                        url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmResultsObject.quizID + '/results',
                        method: 'POST',
                        data: data,
                        headers: { 'X-WP-Nonce': qsmResultsObject.nonce },
                    })
                        .done(function (results) {
                            if (results.status) {
                                jQuery(document).trigger('qsm_after_save_results');
                                QSMAdmin.displayAlert(qsm_admin_messages.results_page_saved, 'success');
                            } else {
                                QSMAdmin.displayAlert( qsm_admin_messages.results_page_save_error + ' ' + qsm_admin_messages.results_page_saved, 'error');
                            }
                        })
                        .fail(QSMAdmin.displayjQueryError);
                },
                loadResults: function () {
                    $.ajax({
                        url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmResultsObject.quizID + '/results',
                        headers: { 'X-WP-Nonce': qsmResultsObject.nonce },
                    })
                        .done(function (pages) {
                            $('#results-pages').find('.qsm-spinner-loader').remove();
                            pages.forEach(function (page, i, pages) {
                                QSMAdminResults.addResultsPage(page.conditions, page.page, page.redirect, page.default_mark, page);
                                QSMAdminResults.updateResultConditonCount(jQuery('.results-page-when').eq(i));
                            });
                            QSMAdmin.clearAlerts();
                            jQuery(document).find('.qsm-result-my-template-container').hide();
                        })
                        .fail(QSMAdmin.displayjQueryError);
                },
                addCondition: function ($page, category, extra_condition, criteria, operator, value) {
                    var template = wp.template('results-page-condition');
                    $page.find('.results-page-when-conditions').append(template({
                        'category': category,
                        'extra_condition': extra_condition,
                        'criteria': criteria,
                        'operator': operator,
                        'value': value
                    }));
                    $page.find('.results-page-condition').each(function () {
                        let extraCategory = jQuery(this).find('.results-page-extra-condition-category');
                        if ('quiz' == jQuery(this).find('.results-page-condition-category').val() || '' == jQuery(this).find('.results-page-condition-category').val()) {
                            extraCategory.closest('.results-page-extra-condition-category-container').hide();
                            jQuery(this).find('.results-page-condition-operator').show();
                            jQuery(this).find('option.qsm-questions-criteria').show();
                            jQuery(this).find('option.qsm-score-criteria').show()
                        } else if ('category' == jQuery(this).find('.results-page-condition-category').val()) {
                            jQuery(this).find('.option.qsm-questions-criteria').hide();
                            extraCategory.find('option').hide();
                            extraCategory.find('.qsm-condition-category-container').show();
                            jQuery(this).find('option.qsm-score-criteria-container').show()
                            jQuery(this).find('.results-page-condition-operator-container').show();
                        }
                        jQuery(this).find('.results-page-extra-condition-category-container .qsm-extra-condition-label').html( jQuery(this).find('.results-page-condition-category option:selected' ).text());
                    });
                    jQuery(document).trigger('qsm_after_add_result_condition', [$page, category, extra_condition, criteria, operator, value]);
                },
                newCondition: function ($page) {
                    QSMAdminResults.addCondition($page, 'quiz', '', 'score', 'equal', 0);
                },
                updateResultConditonCount: function ( $parent ) {
                    $parent.find('.results-page-condition').each(function (index) {
                        jQuery(this).find('.qsm-condition-collection-count').text(index + 1);
                    });
                },
                addResultsPage: function (conditions, page, redirect, default_mark = false, singlePage = {}) {
                    const parser = new DOMParser();
                    let parseRedirect = parser.parseFromString(redirect, 'text/html');
                    redirect = parseRedirect.documentElement.textContent;
                    QSMAdminResults.total += 1;
                    var template = wp.template('results-page');
                    $('#results-pages').append(template({ id: QSMAdminResults.total, page: page, redirect: redirect, default_mark: default_mark }));
                    conditions.forEach(function (condition, i, conditions) {
                        QSMAdminResults.addCondition(
                            $('.results-page:last-child'),
                            condition.category,
                            condition.extra_condition,
                            condition.criteria,
                            condition.operator,
                            condition.value
                        );
                    });
                    var settings = {
                        mediaButtons: true,
                        tinymce: {
                            plugins: "qsmslashcommands link image lists charmap colorpicker textcolor hr fullscreen wordpress",
                            forced_root_block: '',
                            toolbar1: 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,fullscreen,wp_adv',
                            toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help,wp_code',
                            height: 200,
                        },
                        quicktags: true,
                    };
                    jQuery(document).trigger('qsm_tinyMCE_settings_after', [settings]);
                    qsmPinTextareaValue('results-page-' + QSMAdminResults.total);
                    wp.editor.initialize('results-page-' + QSMAdminResults.total, settings);
                    jQuery(document).trigger('qsm_after_add_result_block', [conditions, page, redirect, QSMAdminResults.total, singlePage]);
                    const $resultsPage = jQuery(`#results-page-${QSMAdminResults.total}`).closest('.results-page-show');
                    const $conditionalButton = $resultsPage.find('.qsm-extra-shortcode-conditional-button');
                    $resultsPage.find('.wp-media-buttons .insert-media').after($conditionalButton);

                    if (
                        singlePage &&
                        typeof singlePage === 'object' &&
                        singlePage.hasOwnProperty('redirect') &&
                        singlePage.redirect !== false &&
                        singlePage.redirect !== ''
                    ){
                        $resultsPage.find(".qsm-result-page-then-box-styles-wrap > div:not(.qsm-edit-result-view-options):not(.qsm-result-page-redirect-options)").hide();
                        $resultsPage.find('.qsm-then-redirect-to-url').prop('checked', true);
                        $resultsPage.find('.qsm-result-page-template-options').hide();
                        QSMAdminResults.hideShowResultPageSection($resultsPage);
                    } else {
                        if((typeof singlePage.redirect === 'undefined' || page == '' || !singlePage.hasOwnProperty('default_mark')) && singlePage.hasOwnProperty('is_updated') ) {
                            // New Result Page
                            $resultsPage.find(".qsm-result-page-then-box-styles-wrap > div").hide();
                            $resultsPage.find('.qsm-result-page-template-options').show();
                        } else {
                            // Default Loading Result Page
                            $resultsPage.find('.qsm-result-page-redirect-options').hide();
                            $resultsPage.find('.qsm-result-page-template-options').hide();
                        }
                    }
                    $resultsPage.find(".qsm-edit-result-input-option").removeClass("active");
                    $resultsPage.find('input[name="qsm_then_show_result_option_'+QSMAdminResults.total+'"]:checked').closest(".qsm-edit-result-input-option").addClass("active");
                    QSMAdminResults.addMyTemplateOptions($resultsPage);
                },
                addMyTemplateOptions: function ($resultsPage) {
                    var $selectBox = $resultsPage.parents('.results-page').find('.qsm-to-replace-page-template');
                    $selectBox.empty();
                    $selectBox.append('<option value="">'+qsm_admin_messages.select_template+'</option>');
                    $.each(qsmResultsObject.my_tmpl_data, function(key, value) {
                        $selectBox.append($('<option>', {
                            value: value.id,
                            text: value.template_name
                        }));
                    });
                    $selectBox.select2({
                        placeholder: qsm_admin_messages.select_template,
                        allowClear: true,
                        initSelection: function(e, cb) { }
                    });
                },
                newResultsPage: function () {
                    QSMAdminResults.addResultsPage([], '', '', false, {is_updated: 1});
                },
                displayResultEditor: function ( $resultsPage ){
                    $resultsPage.find(".qsm-result-page-editor-options").show();
                    $resultsPage.find('.qsm-result-page-redirect-options').hide();
                    $resultsPage.find('.qsm-result-page-template-options').hide();
                    QSMAdminResults.hideShowResultPageSection($resultsPage);
                },
                hideShowResultPageSection: function ( $resultsPage ) {
                    $resultsPage.find('.qsm-result-page-common-section').each(function () {
                        let section = $(this);
                        if (section.children().length > 0) {
                            if (!section.is(':visible')) {
                                section.show();
                            }
                        }
                    });
                    if($resultsPage.find('.qsm-result-page-template-options').is(":visible")) {
                        $resultsPage.find('.qsm-result-page-common-section').hide();
                    }
                }
            };
            $(function () {
                QSMAdminResults.loadResults();
                QSMAdminResultsAndEmail.loadMyTemplates( 'result', false );
                jQuery(document).on('click', '.qsm-start-with-template', function (e) {
                    e.preventDefault();
                    let resultPageIndex = jQuery(this).parents('.results-page-show').data('result-page');
                    jQuery("#qsm-result-page-templates-content").attr('data-result-page', resultPageIndex);
                    MicroModal.show('qsm-result-page-templates',{
                        onClose: function () {
                            jQuery("#qsm-result-page-templates-content").attr('data-result-page', '');
                        }
                    });
                });

                jQuery(document).on('click', '.qsm-start-with-canvas', function (e) {
                    e.preventDefault();
                    let $resultsPage = jQuery(this).parents('.results-page-show');
                    let resultPageIndex = $resultsPage.data('result-page');
                    let editor = tinymce.get('results-page-' + (resultPageIndex));
                    let updatedContent = qsmResultsObject.default_result_template.replaceAll(/%([^%]+)%/g, '&nbsp;<qsmvariabletag>$1</qsmvariabletag>&nbsp;');
                    editor.setContent('');
                    editor.execCommand('mceInsertContent', false, updatedContent);
                    QSMAdminResults.displayResultEditor( $resultsPage );
                });

                jQuery(document).on('click', '.qsm-result-page-template-preview-button, .qsm-result-page-template-card-content', function (e) {
                    e.preventDefault();
                    let indexId = jQuery(this).data('indexid');
                    jQuery('.qsm-result-page-template-container').hide();
                    jQuery('.qsm-preview-result-page-template-container').show();
                    jQuery('.qsm-preview-template-image-close').show();
                    let backgroundImage = jQuery(this).parents('.qsm-result-page-template-card').data('url');
                    jQuery('.qsm-preview-template-image').attr('src', backgroundImage);
                    let scriptTemplate = qsmResultsObject.script_tmpl[indexId];
                    let all_dependency = qsmResultsObject.dependency;
                    let $container = $('.qsm-result-template-dependency-addons');
                    $container.empty();
                    if (scriptTemplate?.dependency) {
                        let templateDependency = scriptTemplate.dependency;
                        if (templateDependency.trim() !== '') {
                            let dependencyIds = new Set(
                                templateDependency.split(',').map(id => Number.parseInt(id.trim()))
                            );
                            let $usedAddonsDiv = $('<div>').addClass('qsm-used-addons');
                            $usedAddonsDiv.append($('<h3>').text(qsmResultsObject.used_addons));
                            let hasUsedAddons = false;
                            $.each(all_dependency, function(_, dependency) {
                                if (dependencyIds.has(dependency.id)) {
                                    let $anchor = $('<a>').addClass('qsm-result-template-dependency-addon').attr('href', dependency.link).attr('target', '_blank').text(dependency.name);
                                    hasUsedAddons = true;
                                    if (dependency.status === 'activated' || dependency.status === 'installed') {
                                        $anchor.addClass('qsm-result-template-dependency-addon-purple');
                                    } else {
                                        $anchor.addClass('qsm-result-template-dependency-addon-orange');
                                    }
                                    $usedAddonsDiv.append($anchor);
                                }
                            });
                            if (hasUsedAddons) {
                                $container.append($usedAddonsDiv);
                            }
                        }
                    }
                    $container.children().length > 0 ? $container.show() : $container.hide();
                });

                jQuery(document).on('click', '.qsm-result-page-template-header .qsm-result-page-tmpl-header-links', function (e) {
                    QSMAdminResultsAndEmail.headerLinks( jQuery(this), 'result' );
                });

                jQuery(document).on('click', '.qsm-result-page-template-use-button', function (e) {
                    let structure = jQuery(this).data('structure');
                    let result_index = jQuery("#qsm-result-page-templates-content").attr('data-result-page');
                    let editor = tinymce.get('results-page-' + (result_index));
                    let templateValue;
                    if (structure == 'default') {
                        templateValue = qsmResultsObject.script_tmpl[jQuery(this).attr('data-indexid')].template_content;
                    } else if (structure == 'custom') {
                        templateValue = qsmResultsObject.my_tmpl_data[jQuery(this).attr('data-indexid')].template_content;
                    }
                    let updatedContent = templateValue.replace(/%([^%]+)%/g, '<qsmvariabletag>$1</qsmvariabletag>&nbsp;');
                    updatedContent = qsmConvertContentToShortcode(updatedContent).replace(/\\/g, '');
                    updatedContent = updatedContent + "<p></p>";
                    editor.setContent('');
                    editor.execCommand('mceInsertContent', false, updatedContent);
                    const $resultsPage = jQuery(`#results-page-${result_index}`).closest('.results-page-show');
                    QSMAdminResults.displayResultEditor( $resultsPage );
                    QSMAdminResultsAndEmail.closePreviewModel('qsm-result-page-templates');
                });

                jQuery(document).on('change', '.results-page-show .qsm-then-show-result, .results-page-show .qsm-then-redirect-to-url', function () {
                    // Show/Hide content
                    let $this = jQuery(this);
                    let $parent = $this.parents('.results-page-show');

                    if ($this.val() === "1") {
                        let editor = tinymce.get('results-page-' + ($parent.data('result-page')));
                        let content = editor.getContent().trim();
                        if (content === "") {
                            console.log("Content is empty.");
                            $parent.find(".qsm-result-page-template-options").show();
                        } else {
                            $parent.find(".qsm-result-page-template-options").hide();
                            $parent.find(".qsm-result-page-editor-options").show();
                        }

                        $parent.find(".qsm-result-page-redirect-options").hide();
                    } else if ($this.val() === "2") {
                        $parent.find(".qsm-result-page-template-options").hide();
                        $parent.find(".qsm-result-page-editor-options").hide();
                        $parent.find(".qsm-result-page-redirect-options").show();
                    }

                    QSMAdminResults.hideShowResultPageSection($parent);

                    $parent.find(".qsm-edit-result-input-option").removeClass("active");
                    $this.closest(".qsm-edit-result-input-option").addClass("active");
                });
                $('.add-new-page').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminResults.newResultsPage();
                    QSMAdminResults.updateResultConditonCount(jQuery('.results-page-when').last());
                });
                jQuery(document).on('click', '.qsm-duplicate-result-page-button', function () {
                    let result_page = jQuery(this).closest("header").next("main");
                    let conditions = [];
                    let redirect_value = result_page.find('.results-page-redirect').val();
                    let page = wp.editor.getContent(result_page.find('.results-page-template').attr('id'));
                    result_page.find('.results-page-condition').each(function () {
                        conditions.push({
                            'category': $(this).find('.results-page-condition-category').val(),
                            'extra_condition': $(this).find('.results-page-extra-condition-category').val(),
                            'criteria': $(this).find('.results-page-condition-criteria').val(),
                            'operator': $(this).find('.results-page-condition-operator').val(),
                            'value': $(this).find('.results-page-condition-value').val()
                        });
                    });
                    QSMAdminResults.addResultsPage(conditions, page, redirect_value);
                    jQuery('html, body').animate({ scrollTop: jQuery('.results-page:last-child').offset().top - 150 }, 1000);
                    QSMAdminResults.updateResultConditonCount(jQuery('.results-page-when').last());
                });
                $('.save-pages').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminResults.saveResults();
                });
                $('#results-pages').on('click', '.qsm-new-condition', function (event) {
                    event.preventDefault();
                    $page = $(this).closest('.results-page');
                    QSMAdminResults.newCondition($page);
                    QSMAdminResults.updateResultConditonCount($(this).parents('.results-page-when'));
                });
                $('#results-pages').on('click', '.qsm-delete-result-button', function (event) {
                    event.preventDefault();
                    if (!confirm(qsm_admin_messages.confirmRemovePage)) {
                        return;
                    }
                    $(this).closest('.results-page').remove();
                });
                $('#results-pages').on('click', '.delete-condition-button', function (event) {
                    event.preventDefault();
                    let $parent = $(this).parents('.results-page-when');
                    console.log($parent);
                    $(this).closest('.results-page-condition').remove();
                    QSMAdminResults.updateResultConditonCount($parent);
                });
            });
        }
    } else if (jQuery('body').hasClass('qsm_page_qmn_global_settings')) {
        if (window.location.href.indexOf('tab=quiz-default-template') > 0) {
            // Click handler for .qsm-view-templates-list
            $(document).on('click', '.qsm-view-templates-list', function() {
                let type = $(this).data('type');
                // Strip 'global_' prefix if present to match modal ID
                let modalType = type.replace('global_', '');
                MicroModal.show('qsm-' + modalType + '-page-templates');
                // Initialize tab state - show QSM Templates, hide My Templates
                $('.qsm-' + modalType + '-page-tmpl-header-links').removeClass('active');
                $('.qsm-' + modalType + '-page-tmpl-header-links[data-tab="page"]').addClass('active');
                $('.qsm-' + modalType + '-page-template-container').show();
                $('.qsm-' + modalType + '-my-template-container').hide();
                $('.qsm-preview-' + modalType + '-page-template-container').hide();
            });
 
            // Preview button
                jQuery(document).on('click', '.qsm-result-page-template-preview-button, .qsm-result-page-template-card-content', function (e) {
                    e.preventDefault();
                    let indexId = jQuery(this).data('indexid');
                    jQuery('.qsm-result-page-template-container').hide();
                    jQuery('.qsm-preview-result-page-template-container').show();
                    jQuery('.qsm-preview-template-image-close').show();
                    let backgroundImage = jQuery(this).parents('.qsm-result-page-template-card').data('url');
                    jQuery('.qsm-preview-template-image').attr('src', backgroundImage);
                    let scriptTemplate = (typeof qsmDefaultResultsObject === 'undefined') ? null : qsmDefaultResultsObject.script_tmpl[indexId];
                    let all_dependency = (typeof qsmDefaultResultsObject === 'undefined') ? [] : qsmDefaultResultsObject.dependency;
                    let $container = $('.qsm-result-template-dependency-addons');
                    $container.empty();
                    if (scriptTemplate?.dependency) {
                        let templateDependency = scriptTemplate.dependency;
                        if (templateDependency.trim() !== '') {
                            let dependencyIds = new Set(
                                templateDependency.split(',').map(id => Number.parseInt(id.trim()))
                            );
                            let $usedAddonsDiv = $('<div>').addClass('qsm-used-addons');
                            $usedAddonsDiv.append($('<h3>').text((typeof qsmDefaultResultsObject === 'undefined') ? '' : qsmDefaultResultsObject.used_addons));
                            let hasUsedAddons = false;
                            $.each(all_dependency, function(_, dependency) {
                                if (dependencyIds.has(dependency.id)) {
                                    let $anchor = $('<a>').addClass('qsm-result-template-dependency-addon').attr('href', dependency.link).attr('target', '_blank').text(dependency.name);
                                    hasUsedAddons = true;
                                    if (dependency.status === 'activated' || dependency.status === 'installed') {
                                        $anchor.addClass('qsm-result-template-dependency-addon-purple');
                                    } else {
                                        $anchor.addClass('qsm-result-template-dependency-addon-orange');
                                    }
                                    $usedAddonsDiv.append($anchor);
                                }
                            });
                            if (hasUsedAddons) {
                                $container.append($usedAddonsDiv);
                            }
                        }
                    }
                    $container.children().length > 0 ? $container.show() : $container.hide();
                });
                jQuery(document).on('click', '.qsm-result-page-template-header .qsm-result-page-tmpl-header-links', function (e) {
                    QSMAdminResultsAndEmail.headerLinks( jQuery(this), 'result' );
                });
            // Use button for templates - Only global_result handler for Quiz Default Template
                jQuery(document).on('click', '.qsm-result-page-template-use-button, .qsm-global_result-page-template-use-button', function (e) {
                    e.preventDefault();
                    let structure = jQuery(this).data('structure');
                    let indexid = jQuery(this).data('indexid');
                    let type = jQuery(this).closest('.qsm-popup').data('type');
                    let editor_id = 'default_result_template';
                    let templateValue;
                    if (structure == 'default') {
                        templateValue = (typeof qsmDefaultResultsObject !== 'undefined') ? qsmDefaultResultsObject.script_tmpl[indexid].template_content : '';
                    } else if (structure == 'custom') {
                        templateValue = (typeof qsmDefaultResultsObject !== 'undefined') ? qsmDefaultResultsObject.my_tmpl_data[indexid].template_content : '';
                    }
                    let updatedContent = templateValue.replace(/%([^%]+)%/g, '<qsmvariabletag>$1</qsmvariabletag>&nbsp;');
                    updatedContent = qsmConvertContentToShortcode(updatedContent).replace(/\\/g, '');
                    let editor = tinymce.get(editor_id);
                    if (editor) {
                        editor.setContent(updatedContent);
                    } else {
                        $('#' + editor_id).val(updatedContent);
                    }
                    MicroModal.close('qsm-' + type + '-page-templates');
                });
 
            // Tab switching
            $(document).on('click', '.qsm-page-tmpl-header-links', function() {
                let tab = $(this).data('tab');
                let type = $(this).closest('.qsm-popup').data('type');
                $('.qsm-' + type + '-page-tmpl-header-links').removeClass('active');
                $(this).addClass('active');
                if (tab === 'page') {
                    $('.qsm-' + type + '-page-template-container').show();
                    $('.qsm-' + type + '-my-template-container').hide();
                } else {
                    $('.qsm-' + type + '-page-template-container').hide();
                    $('.qsm-' + type + '-my-template-container').show();
                }
            });
 
            // Back button from preview
            $(document).on('click', '.qsm-preview-template-image-close', function() {
                let type = $(this).data('type');
                $('.qsm-preview-' + type + '-page-template-container').hide();
                $('.qsm-' + type + '-page-template-container').show();
            });
        }
    } 

    jQuery(document).on('click', '.qsm-toggle-result-page-button, .qsm-toggle-email-template-button', function () {
        jQuery(this).closest("header").next("main").slideToggle();
        let $img = jQuery(this).find('img');
        let currentSrc = $img.attr('src');
        let arrowUpImage = qsm_admin_messages.arrow_up_image;
        let arrowDownImage = qsm_admin_messages.arrow_down_image;

        // Toggle the image
        if (currentSrc === arrowUpImage) {
            $img.attr('src', arrowDownImage);
        } else {
            $img.attr('src', arrowUpImage);
        }
    });
    jQuery(document).on('click', '.qsm-settings-box-result-button, .qsm-settings-box-email-button, .qsm-settings-box-default-template-button', function () {
        jQuery('.qsm-more-settings-box-details, .qsm-insert-template-wrap').hide();
        jQuery('.qsm-settings-box-details').not(jQuery(this).parents('.qsm-template-btn-group').find('.qsm-settings-box-details')).hide();
        jQuery(this).parents('.qsm-template-btn-group').find('.qsm-settings-box-details').toggle();
    });

    jQuery(document).on('click', '.qsm-more-settings-box-result-button, .qsm-more-settings-box-email-button, .qsm-more-settings-box-default-template-button', function () {
        jQuery('.qsm-settings-box-details, .qsm-insert-template-wrap').hide();
        jQuery('.qsm-more-settings-box-details').not(jQuery(this).parents('.qsm-template-btn-group').find('.qsm-more-settings-box-details')).hide();
        jQuery(this).parents('.qsm-template-btn-group').find('.qsm-more-settings-box-details').toggle();
    });

    jQuery(document).on('click', function (e) {
        // Check if the click was outside .qsm-template-btn-group
        if (!jQuery(e.target).closest('.qsm-template-btn-group').length) {
            jQuery('.qsm-settings-box-details, .qsm-more-settings-box-details, .qsm-insert-template-wrap').hide();
        }
        if (!$(e.target).closest('.email-show').length) {
            let autocomplete =  jQuery(document).find('.qsm-autocomplete');
            autocomplete.remove();
        }
    });

    $(document).on('click', '.qsm_global_settings .qsm-generate-api-key', function (event) {
        event.preventDefault();
        if (!$(this).hasClass('confirmation') || confirm(qsm_api_object.confirmation_message)) {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'regenerate_api_key',
                    nonce: qsm_api_object.nonce
                },
                success: function (response) {
                    $(".qsm_global_settings .qsm-api-key-input").val(response.data);
                },
                error: function (xhr, status, error) {
                    alert("Error: " + error);
                }
            });
        }
    });

    jQuery(document).on('click', '.qsm-mark-as-default', function () {
        jQuery('.qsm-mark-as-default').not(jQuery(this)).prop('checked', false);
    })
    jQuery(document).on('click', '.qsm-upgrade-popup-advanced-assessment-variable', function () {
        MicroModal.show('modal-advanced-assessment');
    });
    function qsmHandleConditionChange(containerClass, extraCategoryClass, operatorClass, criteriaClass, defaultValueClass) {
        jQuery(document).on('change', '.' + containerClass + '-category', function () {
            let container = jQuery(this).closest('.' + containerClass);
            let extraCategory = container.find('.' + extraCategoryClass);
            container.find('.' + extraCategoryClass + '-container .qsm-extra-condition-label').html( container.find('.' + containerClass + '-category option:selected' ).text());
            if ('quiz' == jQuery(this).val() || '' == jQuery(this).val()) {
                extraCategory.closest('.' + extraCategoryClass + '-container').hide();
                container.find('.' + operatorClass).closest('.' + operatorClass + '-container').show();
                container.find('.' + operatorClass).prev('label').text(qsm_admin_messages.condition);
                container.find('.' + criteriaClass).closest('.' + criteriaClass + '-container').show();
                container.find('.' + defaultValueClass).closest('.' + defaultValueClass + '-container').show();
                container.find('.' + operatorClass + ' option').hide().prop("selected", false);
                container.find('.' + operatorClass + ' option.default_operator').show().prop("selected", true);
                container.find('option.qsm-score-criteria').show();
                container.find('.' + criteriaClass + ' option.qsm-points-criteria').prop("selected", true);
            } else if ('category' == jQuery(this).val()) {
                extraCategory.closest('.' + extraCategoryClass + '-container').show();
                container.find('.' + criteriaClass).closest('.' + criteriaClass + '-container').show();
                container.find('.' + operatorClass).prev('label').text(qsm_admin_messages.condition);
                container.find('.' + operatorClass).closest('.' + operatorClass + '-container').show();
                extraCategory.find('option').prop("selected", false).hide();
                extraCategory.find('.qsm-condition-category').show();
                container.find('.' + defaultValueClass).closest('.' + defaultValueClass + '-container').show();
                container.find('.' + criteriaClass + ' option.qsm-points-criteria').prop("selected", true);
                extraCategory.find('option:visible:first').prop("selected", true);
                container.find('.' + operatorClass + ' option').hide().prop("selected", true);
                container.find('.' + operatorClass + ' option.default_operator').show().prop("selected", true);
                container.find('option.qsm-score-criteria').show();
            } else if ('option-pro' == jQuery(this).val() || 'label-pro' == jQuery(this).val()) {
                jQuery(this).val('quiz');
                MicroModal.show('modal-advanced-assessment');
            }
        });
    }

    function qsmHandleOperatorChange(containerClass, defaultValueClass) {
        jQuery(document).on('change', '.' + containerClass + '-operator', function () {
            let selectedOption = jQuery(this).find('option:selected');
            if (selectedOption.hasClass('default_operator')) {
                jQuery(this).closest('.' + containerClass).find('.' + defaultValueClass + '-container').show();
            }
        });
    }

    // Usage
    qsmHandleConditionChange('results-page-condition', 'results-page-extra-condition-category', 'results-page-condition-operator', 'results-page-condition-criteria', 'condition-default-value');
    qsmHandleOperatorChange('results-page-condition', 'condition-default-value');
    qsmHandleConditionChange('email-condition', 'email-extra-condition-category', 'email-condition-operator', 'email-condition-criteria', 'condition-default-value');
    qsmHandleOperatorChange('email-condition', 'condition-default-value');

}(jQuery));

function qsm_check_email_sent_option() {
    if (jQuery('input[name="send_email"]').prop('checked') === true) {
        jQuery('#check_already_sent_email input[name="check_already_sent_email"]').prop( 'disabled', false );
        jQuery('#check_already_sent_email').show();
    } else {
        jQuery('#check_already_sent_email input[name="check_already_sent_email"]').prop( 'disabled', true );
        jQuery('#check_already_sent_email').hide();
    }
}
/**
 * QSM - failed submission data table action
 */
(function ($) {
    qsm_check_email_sent_option();
    $(document).on('change', 'input[name="send_email"]', function (event) {
        event.preventDefault();
        qsm_check_email_sent_option();
    });
    function submit_failed_submission_action_notice( res ) {
        if ( 'object' !== typeof res || null === res || undefined === res.message  ) {
            return false;
        }
       let noticeEl = $( '#qmn-failed-submission-table-message' );
       if ( 0 < noticeEl.length ) {
            let remove_notice_type_class = 'success' === res.status ? 'notice-error' : 'notice-success';
            noticeEl.removeClass( remove_notice_type_class );
            noticeEl.addClass( 'notice-'+res.status );
            noticeEl.find( '.notice-message' ).text(
                res.message
            );
            noticeEl.removeClass( 'display-none-notice' );
       }
    }

    function submit_failed_submission_action_form( formData, ) {

        // check for required data
        if ( undefined === formData || null === formData || -1 == formData.quiz_action || 0 === formData.post_ids.length ) {
            submit_failed_submission_action_notice( {
                status:"error",
                message:"Missing form action or data"
            } );
            return false;
        }

        // quiz action
        formData.action = 'qsm_action_failed_submission_table';

        // Disable conatiner for further any action
        let containerDiv = $("#qmn-failed-submission-conatiner");
            containerDiv.toggleClass('qsm-pointer-events-none');

        // Actiion one by one
        formData.post_ids.forEach( post_id => {
            formData.post_id = post_id;
            let action_link_wrap = $( '#action-link-'+post_id );
            let action_link_html = action_link_wrap.html();
            action_link_wrap.html( 'processing...');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: formData,
                success: function (response) {
                    // notice.
                    submit_failed_submission_action_notice( response.data );

                    // enable click pointer
                    containerDiv.removeClass('qsm-pointer-events-none');

                    // add success icon
                    if ( response.success ) {
                        action_link_wrap.html( '<span class="dashicons dashicons-yes-alt"></span>' );
                    } else {
                        action_link_wrap.html( 'Failed' );
                    }

                    // Remove row if trashed
                    if ( 'trash' === formData.quiz_action ) {
                        $( '#qsm-submission-row-'+post_id ).remove();
                    }

                },
                error: function ( jqXHR, textStatus, errorThrown ) {
                    // undo action link
                    action_link_wrap.html( action_link_html );

                    // enable click pointer
                    containerDiv.removeClass('qsm-pointer-events-none');

                    // error notice
                    submit_failed_submission_action_notice( {
                        status:"error",
                        message:errorThrown
                    } );
                }
            });
        });

    }

    // Submit Form.
    $( document ).on( 'submit', '#failed-submission-action-form', function( e ) {
        e.preventDefault();
        let formData = {
            qmnnonce: $('#failed-submission-action-form input[name="qmnnonce"]').val(),
            post_ids: [],
            quiz_action: $('#failed-submission-action-form #bulk-action-selector-top').val()
        };
         // Select all checkboxes with the name attribute 'post_id[]'
         let checkedCheckboxes = $('#failed-submission-action-form input[type="checkbox"][name="post_id[]"]:checked');

         // Iterate over each checked checkbox
         checkedCheckboxes.each(function() {
            formData.post_ids.push( $(this).val() );
         });

         submit_failed_submission_action_form( formData );
    } );

    // Dismiss notification
    $( document ).on( 'click', '#qmn-failed-submission-table-message .notice-dismiss', function( e ) {
        e.preventDefault();
        $(this).parent().addClass( 'display-none-notice' );
    });

    // On click retrieve link
    $( document ).on( 'click', '.qmn-retrieve-failed-submission-link', function( e ) {
        e.preventDefault();

        submit_failed_submission_action_form( {
            qmnnonce: $('#failed-submission-action-form input[name="qmnnonce"]').val(),
            post_ids: [ $(this).attr('post-id') ],
            quiz_action:'retrieve'
        } );
    } );

    // Run failed ALTER TABLE query via ajax on notification button click
    $( document ).on( 'click', '.qsm-check-db-fix-btn', function( e ) {
        e.preventDefault();
        let dbFixBtn = $( this );
        let formData = {
            action: 'qsm_check_fix_db',
            qmnnonce: $( this ).data( 'nonce' ),
            query: $( this ).data( 'query' ),
        };
        dbFixBtn.attr( 'disabled', true );
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: formData,
            success: function (response) {
                if ( response.success ) {
                    QSMAdmin.displayAlert(response.data.message, 'success');
                    dbFixBtn.parents('tr').remove();
                } else {
                    QSMAdmin.displayAlert(response.data.message, 'error');
                }
                dbFixBtn.attr( 'disabled', false );
            },
            error: function (jqXHR, textStatus, errorThrown) {
                QSMAdmin.displayAlert(jqXHR.responseText, 'error');
                dbFixBtn.attr( 'disabled', false );
            }
        });
    } );

}(jQuery));

(function ($) {
    $(document).ready(function() {
        let $settingsFields = $('.settings-field');
        let $popups = $('.qsm-contact-form-field-settings');

        // Function to hide all popups
        function qsmHideAllPopups() {
            $popups.hide();
        }

        // Close popup on document click if popup is open and clicking outside
        $(document).on('click', function(event) {
            if (!$settingsFields.is(event.target) && $settingsFields.has(event.target).length === 0) {
                qsmHideAllPopups();
            }
        });

        // Prevent the click event from propagating to the document when clicking inside the popup
        $popups.on('click', function(event) {
            event.stopPropagation();
        });
    });
}(jQuery));

jQuery(document).ready(function() {
    jQuery(document).on('input', '.question-title', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    const bulkActionSelect = jQuery('#qsm_bulk_action_select');
    const applyBulkActionButton = jQuery('#qsm_apply_bulk_action');

    applyBulkActionButton.on('click', function() {
        const action = bulkActionSelect.val();

        // Handle bulk delete action
        if (action === 'bulk_delete') {
            if (confirm(qsm_admin_messages.delete_confirm)) {
                jQuery('form[name="bulk_delete_form"]').submit();
            }
        }
        // Handle permanent delete action
        else if (action === 'bulk_permanent_delete') {
            if (confirm(qsm_admin_messages.delete_confirm)) {
                jQuery('#bulk_permanent_delete').val('1');
                jQuery('form[name="bulk_delete_form"]').submit();
            }
        }
        else {
            alert(qsm_admin_messages.delete_alert);
        }
    });
});
