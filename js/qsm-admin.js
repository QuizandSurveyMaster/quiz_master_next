/**
 * Main admin file for functions to be used across many QSM admin pages.
 */

var QSMAdmin;
(function ($) {

    QSMAdmin = {
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
        displayAlert: function (message, type) {
            QSMAdmin.clearAlerts();
            var template = wp.template('notice');
            var data = {
                message: message,
                type: type
            };
            $('.qsm-alerts').append(template(data));
        },
        clearAlerts: function () {
            $('.qsm-alerts').empty();
        },
        selectTab: function (tab) {
            $('.qsm-tab').removeClass('nav-tab-active');
            $('.qsm-tab-content').hide();
            tab.addClass('nav-tab-active');
            tabID = tab.data('tab');
            $('.tab-' + tabID).show();
        }
    };
    $(function () {
        $('.qsm-tab').on('click', function (event) {
            event.preventDefault();
            QSMAdmin.selectTab($(this));
        });

        //show set global default potion popup
        $(document).on('click', '#qsm-blobal-settings', function () {
            MicroModal.show('qsm-global-default-popup');
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
        jQuery('.row-actions-c > .rtq-delete-result').click(function (e) {
            e.preventDefault();
            var $this = jQuery(this);
            if (confirm(qsm_admin_messages.confirm_message)) {
                var action = 'qsm_dashboard_delete_result';
                var result_id = jQuery(this).data('result_id');
                $.post(ajaxurl, { result_id: result_id, action: action },
                    function (data) {
                        if (data == 'failed') {
                            alert(qsm_admin_messages.error_delete_result);
                        } else {
                            $this.parents('li').slideUp();
                            $this.parents('li').remove();
                        }
                    }
                );
            }
        });
        jQuery('.load-quiz-wizard').click(function (e) {
            e.preventDefault();
            MicroModal.show('model-wizard');
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
                jQuery('.template-list .template-list-inner:first-child').trigger('click');
            }
        });
        //Get quiz options
        jQuery('.template-list-inner').click(function () {
            var action = 'qsm_wizard_template_quiz_options';
            var settings = jQuery(this).data('settings');
            var addons = jQuery(this).data('addons');
            jQuery('.template-list .template-list-inner').removeClass('selected-quiz-template');
            jQuery(this).addClass('selected-quiz-template');
            jQuery('#quiz_settings_wrapper').html('').html('<div class="qsm-spinner-loader"></div>');
            jQuery('#recomm_addons_wrapper').html('').html('<div class="qsm-spinner-loader"></div>');
            $.post(ajaxurl, { settings: settings, addons: addons, action: action },
                function (data) {
                    var diff_html = data.split('=====');
                    jQuery('#quiz_settings_wrapper').html('');
                    jQuery('#quiz_settings_wrapper').html(diff_html[0]);
                    jQuery('#recomm_addons_wrapper').html('');
                    jQuery('#recomm_addons_wrapper').html(diff_html[1]);
                    jQuery("#accordion").accordion();
                    jQuery('#accordion h3.ui-accordion-header').next().slideDown();
                    $('#quiz_settings_wrapper select').each(function () {
                        var name = $(this).attr('name');
                        var value = $(this).val();
                        if ($('.' + name + '_' + value).length > 0) {
                            $('.' + name + '_' + value).show();
                        }
                    });
                }
            );
        });

        //Dismiss the welcome panel
        jQuery('.qsm-welcome-panel-dismiss').click(function (e) {
            e.preventDefault();
            jQuery('#welcome_panel').addClass('hidden');
            jQuery('#screen-options-wrap').find('#welcome_panel-hide').prop('checked', false);
            postboxes.save_state('toplevel_page_qsm_dashboard');
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
        if ( "" != jQuery('#scheduled_time_end').val() ) {
            jQuery('.not_allow_after_expired_time label').css('opacity', '1');
            jQuery('#not_allow_after_expired_time-1').attr('disabled', false);
        } else {
            jQuery('.not_allow_after_expired_time label').css('opacity', '0.7');
            jQuery('#not_allow_after_expired_time-1').attr('disabled', true);
        }
        jQuery(document).on('change', '#scheduled_time_end', function () {
            if ( "" != jQuery(this).val() ) {
                jQuery('.not_allow_after_expired_time label').css('opacity', '1');
                jQuery('#not_allow_after_expired_time-1').attr('disabled', false);
            } else {
                jQuery('.not_allow_after_expired_time label').css('opacity', '0.7');
                jQuery('#not_allow_after_expired_time-1').attr('disabled', true);
            }
        });
        //Hide/show tr based on selection
        $('.qsm_tab_content select').each(function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            if ($('.' + name + '_' + value).length > 0) {
                $('.' + name + '_' + value).show();
            }
        });
        $(document).on('change', '.qsm_tab_content select, #quiz_settings_wrapper select', function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            $('.qsm_hidden_tr').hide();
            if ($('.' + name + '_' + value).length > 0) {
                $('.' + name + '_' + value).show();
            }
        });
        $(document).on('click', '.qsm_tab_content input[name="system"]', function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            $('.qsm_hidden_tr_gradingsystem').hide();
            if (value == 0 || value == 3) {
                $('.qsm_hidden_tr_gradingsystem').show();
            }
        });
        $(document).ready(function () {
            var system_option = $("input[type=radio][name='system']:checked").val();
            $('.qsm_hidden_tr_gradingsystem').hide();
            if (system_option == 0 || system_option == 3) {
                $('.qsm_hidden_tr_gradingsystem').show();
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
        $(document).on('click', '#show-all-variable .qsm-text-template-span ', function (e) {
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
            button.parents('.qsm-theme-option-image').hide();
            button.parents('.qsm-theme-option-image').prevAll('.quiz-theme-option-image-input').val("");
            button.parents('.qsm-theme-option-image').prevAll('.quiz-theme-option-image-button').fadeIn();

        });
        //theme option setting image end

        $(document).on('change', '.global_form_type_settiong  select[name="qsm-quiz-settings[form_type]"]', function () {
            var value = $(this).val();
            if (value == '0') {
                $('.global_setting_system').parents('tr').show();
                $('.global_setting_score_roundoff').parents('tr').show();
            } else {
                $('.global_setting_system').parents('tr').hide();
                $('.global_setting_score_roundoff').parents('tr').hide();

            }
        });
        $(document).on('change', '.global_setting_system input[name="qsm-quiz-settings[system]"]', function () {
            var value = $('input[name="qsm-quiz-settings[system]"]:checked').val();
            var value1 = $('.global_form_type_settiong  select[name="qsm-quiz-settings[form_type]"]').val();
            if (value != '1' && value1 == '0') {
                $('.global_setting_score_roundoff').parents('tr').show();
            } else {
                $('.global_setting_score_roundoff').parents('tr').hide();
            }
        });
        $('.global_form_type_settiong  select[name="qsm-quiz-settings[form_type]"]').trigger('change');
        $('.global_setting_system input[name="qsm-quiz-settings[system]"]').trigger('change');

    });
    $(document).on('change', '.global_setting_system input[name="qsm-quiz-settings[system]"]', function () {
        var value = $('input[name="qsm-quiz-settings[system]"]:checked').val();
        var value1 = $('.global_form_type_settiong  select[name="qsm-quiz-settings[form_type]"]').val();
        if (value != '1' && value1 == '0') {
            $('.global_setting_score_roundoff').parents('tr').show();
        } else {
            $('.global_setting_score_roundoff').parents('tr').hide();
        }
    });
    $('.global_form_type_settiong  select[name="qsm-quiz-settings[form_type]"]').trigger('change');
    $('.global_setting_system input[name="qsm-quiz-settings[system]"]').trigger('change');

    $(document).on('click', '#the-list .delete_table_quiz_results_item', function (e) {
        e.preventDefault();
        var qid = $(this).data('quiz-id');
        var qname = $(this).data('quiz-name');
        deleteResults(qid, qname);
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
        jQuery('.qsm_setting__data').html('<p>' + JSON.stringify(JSON.parse(qsm_get_setting_data), null, 2) + '</p>');
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
    jQuery("#delete_dialog").dialog({
        autoOpen: false,
        buttons: {
            Cancel: function () {
                $jQuery(this).dialog('close');
            }
        }
    });
    jQuery("#delete_dialog").dialog('open');
    var idHidden = document.getElementById("result_id");
    var idHiddenName = document.getElementById("delete_quiz_name");
    idHidden.value = id;
    idHiddenName.value = quizName;
}
//quiz options style tab
jQuery('.quiz_style_tab').click(function (e) {
    e.preventDefault();
    var current_id = jQuery(this).attr('data-id');
    jQuery('.quiz_style_tab').removeClass('current');
    jQuery('.qsm-custom-label-left-menu').removeClass('currentli');
    jQuery(this).addClass('current');
    jQuery('.quiz_style_tab_content').hide();
    jQuery('#' + current_id).show();
});
//quiz options text tab custom label
jQuery('.quiz_text_tab_custom').click(function (e) {
    e.preventDefault();
    var current_id = jQuery(this).attr('data-id');
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
});
if (jQuery('body').hasClass('admin_page_mlw_quiz_options')) { var current_id = jQuery(this).attr('data-id'); if(current_id == 'qsm_general_text'){ jQuery(".current_general")[0].click();}
if(current_id == 'qsm_variable_text'){  jQuery(".current_variable")[0].click();}
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
                jQuery('.qsm_featured_image_preview').attr('src', jQuery(this).val());
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
        });

        jQuery(document).ready(function () {
            jQuery(document).on('click', '.qsm-customize-color-settings', function (e) {
                e.preventDefault();
                MicroModal.show('qsm-theme-color-settings');
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
    }
}


//QSM - Quizzes/Surveys Page

(function ($) {
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
                jQuery('.template-list .template-list-inner:first-child').trigger('click');
            }
        });
        //Get quiz options
        $('.template-list-inner').click(function () {
            var action = 'qsm_wizard_template_quiz_options';
            var settings = $(this).data('settings');
            var addons = $(this).data('addons');
            $('.template-list .template-list-inner').removeClass('selected-quiz-template');
            $(this).addClass('selected-quiz-template');
            $('#quiz_settings_wrapper').html('').html('<div class="qsm-spinner-loader"></div>');
            $('#recomm_addons_wrapper').html('').html('<div class="qsm-spinner-loader"></div>');
            $.post(ajaxurl, {
                settings: settings,
                addons: addons,
                action: action
            },
                function (data) {
                    var diff_html = data.split('=====');
                    $('#quiz_settings_wrapper').html('');
                    $('#quiz_settings_wrapper').html(diff_html[0]);
                    $('#recomm_addons_wrapper').html('');
                    $('#recomm_addons_wrapper').html(diff_html[1]);
                    $("#accordion").accordion();
                    $('#accordion h3.ui-accordion-header').next().slideDown();
                    $('#quiz_settings_wrapper select').each(function () {
                        var name = $(this).attr('name');
                        var value = $(this).val();
                        if ($('.' + name + '_' + value).length > 0) {
                            $('.' + name + '_' + value).show();
                        }
                    });
                }
            );
        });
        $('#show_import_export_popup').on('click', function (event) {
            event.preventDefault();
            MicroModal.show('modal-export-import');
        });
        $(document).on('change', '.qsm_tab_content select, #quiz_settings_wrapper select', function () {
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
            $('#sc-shortcode-model-text').val(embed_text);
            $('#sc-shortcode-model-text-link').val(link_text);
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

// QSM - Quiz Wizard

(function ($) {
    if (jQuery('body').hasClass('post-type-qsm_quiz') || jQuery('body').hasClass('toplevel_page_qsm_dashboard')) {
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

        $(document).on('click', '#new_quiz_button', function () {
            $('#quiz_settings').find('.qsm-opt-desc').each(function () {
                if ($(this)) {
                    desc = $(this);
                    desc.parents('.input-group').find('label:first-child').append(desc);
                }
            })
        });
    }
}(jQuery));

//QSM - Admin Notices for enabling multiple categories in QSM 7.3+

(function ($) {
    $(document).on('click', '.enable-multiple-category', function (e) {
        e.preventDefault();
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
                value: 'enable'
            },
            success: function (r) {
                response = JSON.parse(r);
                clearInterval(category_interval);
                if (response.status) {
                    $('.category-action').parents('.multiple-category-notice').removeClass('notice-info').addClass('notice-success').html('<p>' + qsm_admin_messages.update_db_success + '</p>');
                } else {
                    $('.category-action').parents('.multiple-category-notice').removeClass('notice-info').addClass('notice-error').html(qsm_admin_messages.error + '! ' + qsm_admin_messages.try_again);
                }

            }
        });
    });

    $(document).on('click', '.cancel-multiple-category', function (e) {
        e.preventDefault();
        $('.category-action').html('');
        $.ajax({
            type: "POST",
            url: ajaxurl,
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
                    var template = wp.template('contact-form-field');

                    $('.contact-form').append(template(fieldArray));

                    $('.contact-form-field').each(function () {
                        QSMContact.hideShowSettings($(this));
                    });
                    setTimeout(QSMContact.removeNew, 250);
                },
                removeNew: function () {
                    $('.contact-form-field').removeClass('new');
                },
                duplicateField: function (linkClicked) {
                    var fieldArray = QSMContact.prepareFieldData(linkClicked.parents('.contact-form-field'));
                    QSMContact.addField(fieldArray);
                },
                deleteField: function (field) {
                    var parent = field.parents('.contact-form-field');
                    parent.addClass('deleting');
                    setTimeout(function () {
                        parent.remove();
                    }, 250);
                },
                newField: function () {
                    var fieldArray = {
                        label: '',
                        type: 'text',
                        answers: [],
                        required: false,
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
                        required: field.find('.required-control').prop('checked'),
                        use: field.find('.use-control').val(),
                        enable: field.find('.enable-control').prop('checked'),
                    };
                    /**
                     * Store Other settings
                     */
                    field.find('.contact-form-field-settings :input').each(function () {
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
                    QSMContact.displayAlert(qsm_admin_messages.saving_contact_fields, 'info');
                    var contactFields = $('.contact-form-field');
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
                        } else {
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

                    jQuery.post(ajaxurl, data, function (response) {
                        QSMContact.saved(JSON.parse(response));
                    });
                },
                saved: function (response) {
                    if (response.status) {
                        QSMContact.displayAlert('<strong>' + qsm_admin_messages.success + '</strong> ' + qsm_admin_messages.contact_fields_saved, 'success');
                    } else {
                        QSMContact.displayAlert('<strong>' + qsm_admin_messages.error + '</strong> ' + qsm_admin_messages.contact_fields_save_error + ' ' + qsm_admin_messages.try_again, 'error');
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
                    $('.contact-message').empty().removeClass().addClass('contact-message');
                },
                hideShowSettings: function (field) {
                    var type = field.find('.type-control').val();
                    if (field.find('.required-control').prop('checked')) {
                        field.find('.field-required-flag').show();
                    }
                    if (!field.find('.enable-control').prop('checked')) {
                        field.addClass('disabled-field');
                        if (!$('.show-disabled-fields').prop('checked')) {
                            field.addClass('hidden-field');
                        }
                    }
                    field.find('.contact-form-field-settings .contact-form-group:not(.required-option)').hide();
                    if ('text' == type || 'number' == type) {
                        field.find('.contact-form-field-settings .min-max-option').show();
                    }
                    if ('email' == type) {
                        field.find('.contact-form-field-settings .email-option').show();
                    }
                    jQuery(document).trigger('qsm_contact_field_hide_show_settings', [field, type]);
                }
            };
            $(function () {
                QSMContact.load();
                if ($('.contact-form > .contact-form-field').length === 0) {
                    $('.save-contact').hide();
                }
                $('.add-contact-field').on('click', function () {
                    QSMContact.newField();
                    if ($('.contact-form > .contact-form-field').length === 0) {
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
                    var target = $(this).parents('.contact-form-field').find('.contact-form-field-settings');
                    $('.contact-form-field-settings').not(target).hide();
                    target.toggle();
                });
                $('.contact-form').on('change', '.type-control', function (event) {
                    event.preventDefault();
                    QSMContact.hideShowSettings($(this).parents('.contact-form-field'));
                });
                $('.contact-form').on('change', '.required-control', function (event) {
                    event.preventDefault();
                    $(this).parents('.contact-form-field').find('.field-required-flag').hide();
                    if ($(this).is(':checked')) {
                        $(this).parents('.contact-form-field').find('.field-required-flag').show();
                    }
                });
                $('.contact-form').on('change', '.enable-control', function (event) {
                    event.preventDefault();
                    $(this).parents('.contact-form-field').addClass('disabled-field');
                    if ($(this).is(':checked')) {
                        $(this).parents('.contact-form-field').removeClass('disabled-field');
                    }
                    QSMContact.hideShowSettings($(this).parents('.contact-form-field'));
                });
                $(document).on('change', '.show-disabled-fields', function (event) {
                    event.preventDefault();
                    var is_show = $(this).prop('checked');
                    jQuery.post(ajaxurl, { action: 'qsm_show_disabled_contact_fields', show: is_show });
                    if (is_show) {
                        $('.contact-form-field').removeClass('hidden-field');
                    } else {
                        $('.contact-form-field.disabled-field').addClass('hidden-field');
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
                        email = {
                            'conditions': [],
                            'to': $(this).find('.to-email').val(),
                            'subject': $(this).find('.subject').val(),
                            'content': email_content,
                            'replyTo': $(this).find('.reply-to').prop('checked'),
                        };
                        $(this).find('.email-condition').each(function () {
                            email.conditions.push({
                                'category': $(this).children('.email-condition-category').val(),
                                'criteria': $(this).children('.email-condition-criteria').val(),
                                'operator': $(this).children('.email-condition-operator').val(),
                                'value': $(this).children('.email-condition-value').val()
                            });
                        });
                        emails.push(email);
                    });
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
                                QSMAdminEmails.addEmail(email.conditions, email.to, email.subject, email.content, email.replyTo);
                            });
                            QSMAdmin.clearAlerts();
                        })
                        .fail(QSMAdmin.displayjQueryError);
                },
                addCondition: function ($email, category, criteria, operator, value) {
                    var template = wp.template('email-condition');
                    $email.find('.email-when-conditions').append(template({
                        'category': category,
                        'criteria': criteria,
                        'operator': operator,
                        'value': value
                    }));
                },
                newCondition: function ($email) {
                    QSMAdminEmails.addCondition($email, '', 'score', 'equal', 0);
                },
                addEmail: function (conditions, to, subject, content, replyTo) {
                    QSMAdminEmails.total += 1;
                    var template = wp.template('email');
                    $('#qsm_emails').append(template({ id: QSMAdminEmails.total, to: to, subject: subject, content: content, replyTo: replyTo }));
                    conditions.forEach(function (condition, i, conditions) {
                        QSMAdminEmails.addCondition(
                            $('.qsm-email:last-child'),
                            condition.category,
                            condition.criteria,
                            condition.operator,
                            condition.value
                        );
                    });
                    if (qsmEmailsObject.qsm_user_ve === 'true') {
                        var settings = {
                            mediaButtons: true,
                            tinymce: {
                                forced_root_block: '',
                                toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
                            },
                            quicktags: true,
                        };
                        wp.editor.initialize('email-template-' + QSMAdminEmails.total, settings);
                    }
                    jQuery(document).trigger('qsm_after_add_email_block', [conditions, to, subject, content, replyTo]);
                },
                newEmail: function () {
                    var conditions = [{
                        'category': '',
                        'criteria': 'score',
                        'operator': 'greater',
                        'value': '0'
                    }];
                    var to = '%USER_EMAIL%';
                    var subject = 'Quiz Results For %QUIZ_NAME%';
                    var content = '%QUESTIONS_ANSWERS_EMAIL%';
                    var replyTo = false;
                    QSMAdminEmails.addEmail(conditions, to, subject, content, replyTo);
                }
            };
            $(function () {
                QSMAdminEmails.loadEmails();
                $('.add-new-email').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminEmails.newEmail();
                });
                $('.save-emails').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminEmails.saveEmails();
                });
                $('#qsm_emails').on('click', '.new-condition', function (event) {
                    event.preventDefault();
                    $page = $(this).closest('.qsm-email');
                    QSMAdminEmails.newCondition($page);
                });
                $('#qsm_emails').on('click', '.delete-email-button', function (event) {
                    event.preventDefault();
                    $(this).closest('.qsm-email').remove();
                });
                $('#qsm_emails').on('click', '.delete-condition-button', function (event) {
                    event.preventDefault();
                    $(this).closest('.email-condition').remove();
                });
            });
        }
    }
}(jQuery));

/**
 * QSM Question Tab
 */
var QSMQuestion;
var import_button;
(function ($) {
    if (jQuery('body').hasClass('admin_page_mlw_quiz_options')) {
        if (window.location.href.indexOf('&tab') == -1 || window.location.href.indexOf('tab=questions') > 0) {

            $.QSMSanitize = function (input) {
                return input.replace(/<(|\/|[^>\/bi]|\/[^>bi]|[^\/>][^>]+|\/[^>][^>]+)>/g, '');
            };
            QSMQuestion = {
                question: Backbone.Model.extend({
                    defaults: {
                        id: null,
                        quizID: 1,
                        type: '0',
                        name: '',
                        question_title: '',
                        answerInfo: '',
                        comments: '1',
                        hint: '',
                        category: '',
                        required: 1,
                        answers: [],
                        page: 0
                    }
                }),
                questions: null,
                page: Backbone.Model.extend({
                    defaults: {
                        id: null,
                        quizID: 1,
                        pagekey: qsmRandomID(8),
                        hide_prevbtn: 0,
                        questions: null,
                    }
                }),
                qpages: null,
                questionCollection: null,
                pageCollection: null,
                categories: [],
                /**
                 * Counts the total number of questions and then updates #total-questions span.
                 */
                countTotal: function () {
                    var total = 0;

                    // Cycles through each page.
                    _.each(jQuery('.page'), function (page) {

                        // If page is empty, continue to the next.
                        if (0 == jQuery(page).children('.question').length) {
                            return;
                        }
                        // Cycle through each question and add to our total.
                        _.each(jQuery(page).children('.question'), function (question) {
                            total += 1;
                        });
                    });
                    $('#total-questions').text(total);
                },
                openQuestionBank: function (pageID) {
                    QSMQuestion.loadQuestionBank();
                    $('#add-question-bank-page').val(pageID);
                    MicroModal.show('modal-2', {
                        onClose: function () {
                            $('.save-page-button').trigger('click');
                        }
                    });
                },
                loadQuestionBank: function (action = '') {
                    if (action == 'change') {
                        $('.qb-load-more-wrapper').remove();
                        $('#question-bank').find('.question-bank-question').remove();
                        $('#question-bank').append('<div style="top: 70px;position: relative;left: calc(50% - 20px);" class="qsm-spinner-loader"></div>');
                    } else if ($('.qb-load-more-wrapper').length > 0) {
                        $('.qb-load-more-question').hide();
                        $('.qb-load-more-wrapper').append('<div style="top: 70px;position: relative;left: calc(50% - 20px); class="qsm-spinner-loader"></div>');
                    } else {
                        $('#question-bank').empty();
                        $('#question-bank').append('<div style="top: 70px;position: relative;left: calc(50% - 20px); class="qsm-spinner-loader"></div>');
                    }
                    $.ajax({
                        url: wpApiSettings.root + 'quiz-survey-master/v1/bank_questions/0/',
                        method: 'GET',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', qsmQuestionSettings.nonce);
                        },
                        data: {
                            'quizID': $('#question-bank-quiz').val(),
                            'page': $('#question_back_page_number').length > 0 ? parseInt($('#question_back_page_number').val()) + 1 : 1,
                            'category': $('#question-bank-cat').val(),
                            'search': $('#question-bank-search-input').val()
                        },
                        success: QSMQuestion.questionBankLoadSuccess
                    });
                },
                questionBankLoadSuccess: function (response) {
                    var pagination = response.pagination;
                    var questions = response.questions;
                    if ($('.qb-load-more-wrapper').length > 0) {
                        $('.qb-load-more-wrapper').remove();
                    } else {
                        $('#question-bank').empty();
                    }
                    for (var i = 0; i < questions.length; i++) {
                        QSMQuestion.addQuestionToQuestionBank(questions[i]);
                    }
                    if (pagination.total_pages > pagination.current_page) {
                        var pagination_html = '<div class="qb-load-more-wrapper" style="text-align: center;margin: 20px 0 10px 0;"><input type="hidden" id="question_back_page_number" value="' + pagination.current_page + '"/>';
                        pagination_html += '<input type="hidden" id="question_back_total_pages" value="' + pagination.total_pages + '"/>';
                        pagination_html += '<a href="javascript:void(0)" class="qb-load-more-question">' + qsm_admin_messages.load_more_quetions + '</a></div>';
                        $('#question-bank').append(pagination_html);
                    }
                    if (pagination.current_page == 1) {
                        if (qsmQuestionSettings.categories.length > 0) {
                            var category_arr = qsmQuestionSettings.categories;
                            let $cat_html = '<option value="">' + qsm_admin_messages.all_categories + '</option>';
                            $.each(category_arr, function (index, value) {
                                if (value.category !== '') {
                                    if (typeof value.cat_id !== 'undefined' && value.cat_id !== '') {
                                        $cat_html += '<option value="' + value.cat_id + '">' + value.category + '</option>';
                                    } else {
                                        $cat_html += '<option value="' + value.category + '">' + value.category + '</option>';
                                    }
                                }
                            });
                            $('#question-bank-cat').html($cat_html);
                            $('#question-bank-cat').val(pagination.category);
                        }
                    }
                },
                addQuestionToQuestionBank: function (question) {
                    var questionText = QSMQuestion.prepareQuestionText(question.name);
                    var template = wp.template('single-question-bank-question');
                    if (question.question_title !== "undefined" && question.question_title !== "") {
                        questionText = question.question_title;
                    }
                    $('#question-bank').append(template({
                        id: question.id,
                        type: question.type,
                        question: questionText,
                        category: question.category,
                        quiz_name: question.quiz_name
                    }));
                },
                addQuestionFromQuestionBank: function (questionID) {
                    QSMAdmin.displayAlert(qsm_admin_messages.adding_question, 'info');
                    var model = new QSMQuestion.question({
                        id: questionID
                    });
                    model.fetch({
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        url: wpApiSettings.root + 'quiz-survey-master/v1/questions/' + questionID,
                        success: QSMQuestion.questionBankSuccess,
                        error: QSMAdmin.displayError
                    });
                },
                questionBankSuccess: function (model) {
                    var newModel = _.clone(model.attributes);
                    newModel.id = null;
                    QSMQuestion.questions.create(
                        newModel, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.addNewQuestionFromQuestionBank,
                        error: QSMAdmin.displayError
                    }
                    );
                },
                prepareCategories: function () {
                    QSMQuestion.categories = [];
                    QSMQuestion.questions.each(function (question) {
                        var category = question.get('category');
                        if (0 !== category.length && !_.contains(QSMQuestion.categories, category)) {
                            QSMQuestion.categories.push(category);
                        }
                    });
                },
                processCategories: function () {
                    $('.category').remove();
                    _.each(QSMQuestion.categories, function (category) {
                        QSMQuestion.addCategory(category);
                    });
                },
                addCategory: function (category) {
                    var template = wp.template('single-category');
                    $('#categories').prepend(template({
                        category: category
                    }));
                },
                loadQuestions: function () {
                    QSMAdmin.displayAlert(qsm_admin_messages.loading_question, 'info');
                    QSMQuestion.questions.fetch({
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        data: {
                            quizID: qsmQuestionSettings.quizID
                        },
                        success: QSMQuestion.loadSuccess,
                        error: QSMAdmin.displayError
                    });
                },
                loadSuccess: function () {
                    QSMAdmin.clearAlerts();
                    $('.qsm-showing-loader').remove();
                    var question;
                    _.each(qsmQuestionSettings.qpages, function (page) {
                        QSMQuestion.qpages.add(page);
                    });
                    if (qsmQuestionSettings.pages.length > 0) {
                        for (var i = 0; i < qsmQuestionSettings.pages.length; i++) {
                            for (var j = 0; j < qsmQuestionSettings.pages[i].length; j++) {
                                question = QSMQuestion.questions.get(qsmQuestionSettings.pages[i][j]);
                                if ('undefined' !== typeof question) {
                                    QSMQuestion.addQuestionToPage(question);
                                }
                            }
                        }
                    } else {
                        //We have removed this code in  7.0.0 because not allow to delete the single page.
                        QSMQuestion.questions.each(QSMQuestion.addQuestionToPage);
                    }
                    //Create Default pages and one question.
                    if (qsmQuestionSettings.pages.length == 0 && QSMQuestion.questions.length == 0) {
                        $('.new-page-button').trigger('click');
                        $('.questions .new-question-button').trigger('click');
                    }
                    QSMQuestion.countTotal();
                },
                updateQPage: function (pageID) {
                    QSMAdmin.displayAlert(qsm_admin_messages.saving_page_info, 'info');
                    var pageInfo = QSMQuestion.qpages.get(pageID);
                    jQuery('#page-options').find(':input, select, textarea').each(function (i, field) {
                        pageInfo.set(field.name, field.value);
                    });
                },
                savePages: function () {
                    QSMAdmin.displayAlert(qsm_admin_messages.saving_page_questions, 'info');
                    var pages = [];
                    var qpages = [];
                    var pageInfo = null;
                    var post_id = jQuery('#edit_quiz_post_id').val();

                    // Cycles through each page and add page + questions to pages variable
                    _.each(jQuery('.page'), function (page) {

                        // If page is empty, do not add it.
                        if (0 == jQuery(page).children('.question').length) {
                            return;
                        }
                        var singlePage = [];
                        // Cycle through each question and add to the page.
                        _.each(jQuery(page).children('.question'), function (question) {
                            singlePage.push(jQuery(question).data('question-id'))
                        });
                        pages.push(singlePage);
                        /**
                         * Prepare qpages Object
                         */
                        pageInfo = QSMQuestion.qpages.get(jQuery(page).data('page-id'));
                        pageInfo.set('questions', singlePage);
                        qpages.push(pageInfo.attributes);
                    });
                    var data = {
                        action: 'qsm_save_pages',
                        quiz_id: qsmQuestionSettings.quizID,
                        nonce: qsmQuestionSettings.saveNonce,
                        pages: pages,
                        qpages: qpages,
                        post_id: post_id,
                    };

                    jQuery.ajax(ajaxurl, {
                        data: data,
                        method: 'POST',
                        success: QSMQuestion.savePagesSuccess,
                        error: QSMAdmin.displayjQueryError
                    });
                },
                savePagesSuccess: function () {
                    QSMAdmin.displayAlert(qsm_admin_messages.saved_page_questions, 'success');
                    $('#save-edit-quiz-pages').removeClass('is-active');
                },
                addNewPage: function (pageID) {
                    var template = wp.template('page');
                    if (typeof pageID == 'undefined' || pageID == '') {
                        var newPageID = QSMQuestion.qpages.length + 1;
                        var pageID = newPageID;
                        var pageInfo = QSMQuestion.qpages.add({
                            id: newPageID,
                            quizID: qsmQuestionSettings.quizID,
                            pagekey: qsmRandomID(8),
                            hide_prevbtn: 0
                        });
                    }
                    var pageInfo = QSMQuestion.qpages.get(pageID);
                    $('.questions').append(template(pageInfo));
                    var page = $('.questions').find('.page').length;
                    $('.page:nth-child(' + page + ')').find('.page-number').text('Page ' + page);
                    $('.page').sortable({
                        items: '.question',
                        opacity: 70,
                        cursor: 'move',
                        handle: 'span.dashicons-move',
                        placeholder: "ui-state-highlight",
                        connectWith: '.page',
                        stop: function (evt, ui) {
                            setTimeout(
                                function () {
                                    $('.save-page-button').trigger('click');
                                },
                                200
                            )
                        }
                    });
                    setTimeout(QSMQuestion.removeNew, 250);
                },
                addNewQuestionFromQuestionBank: function (model) {
                    var page = parseInt($('#add-question-bank-page').val(), 10);
                    model.set('page', page);
                    QSMQuestion.questions.add(model);
                    QSMQuestion.addQuestionToPage(model);
                    $('.import-button').removeClass('disable_import');
                    QSMQuestion.countTotal();
                    import_button.html('').html(qsm_admin_messages.add_question);
                    import_button.attr("onclick", "return confirm(" + qsm_admin_messages.confirm_message + "' '" + qsm_admin_messages.import_question_again + ")");
                    QSMQuestion.openEditPopup(model.id, $('.question[data-question-id=' + model.id + ']').find('.edit-question-button'));
                    $('#save-popup-button').trigger('click');
                },
                addNewQuestion: function (model) {
                    var default_answers = parseInt(qsmQuestionSettings.default_answers);
                    var count = 0;
                    QSMAdmin.displayAlert(qsm_admin_messages.question_created, 'success');
                    QSMQuestion.addQuestionToPage(model);
                    QSMQuestion.openEditPopup(model.id, $('.question[data-question-id=' + model.id + ']').find('.edit-question-button'));
                    QSMQuestion.countTotal();
                    if ($('#answers').find('.answers-single').length < default_answers) {
                        while (count < default_answers) {
                            $('#new-answer-button').trigger('click');
                            count++;
                        }
                    }
                    $('.save-page-button').trigger('click');
                },
                addQuestionToPage: function (model) {
                    var page = model.get('page') + 1;
                    var template = wp.template('question');
                    var page_exists = $('.page:nth-child(' + page + ')').length;
                    var count = 0;
                    while (!page_exists) {
                        QSMQuestion.addNewPage(page);
                        page_exists = $('.page:nth-child(' + page + ')').length;
                        count++;
                        if (count > 5) {
                            page_exists = true;
                            console.log('count reached');
                        }
                    }
                    var questionName = QSMQuestion.prepareQuestionText(model.get('name'));
                    var new_question_title = model.get('question_title');
                    if (new_question_title === null || typeof new_question_title === "undefined" || new_question_title === "") {
                        //Do nothing
                    } else {
                        questionName = new_question_title;
                    }
                    if (questionName == '') {
                        questionName = qsm_admin_messages.new_question;
                    }
                    $('.page:nth-child(' + page + ')').append(template({
                        id: model.id,
                        type: model.get('type'),
                        category: model.get('category'),
                        question: questionName
                    }));
                    setTimeout(QSMQuestion.removeNew, 250);
                },
                createQuestion: function (page) {
                    QSMAdmin.displayAlert(qsm_admin_messages.creating_question, 'info');
                    QSMQuestion.questions.create({
                        quizID: qsmQuestionSettings.quizID,
                        page: page
                    }, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.addNewQuestion,
                        error: QSMAdmin.displayError
                    });
                },
                duplicateQuestion: function (questionID) {
                    QSMAdmin.displayAlert(qsm_admin_messages.duplicating_question, 'info');
                    var model = QSMQuestion.questions.get(questionID);
                    var newModel = _.clone(model.attributes);
                    newModel.id = null;
                    QSMQuestion.questions.create(
                        newModel, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.addNewQuestion,
                        error: QSMAdmin.displayError
                    }
                    );
                },
                saveQuestion: function (questionID, CurrentElement) {
                    QSMAdmin.displayAlert(qsm_admin_messages.saving_question, 'info');
                    var model = QSMQuestion.questions.get(questionID);
                    var hint = $('#hint').val();
                    var name = wp.editor.getContent('question-text');
                    //Save new question title
                    var question_title = $('#question_title').val();
                    if (name == '' && question_title == '') {
                        alert(qsm_admin_messages.enter_question_title);
                        setTimeout(function () {
                            $('#save-edit-question-spinner').removeClass('is-active');
                        }, 250);
                        return false;
                    }
                    var advanced_option = {};
                    var answerInfo = wp.editor.getContent('correct_answer_info');
                    var quizID = parseInt(qsmTextTabObject.quiz_id);
                    var type = $("#question_type").val();
                    var comments = $("#comments").val();
                    advanced_option['required'] = $(".questionElements input[name='required']").is(":checked") ? 0 : 1;
                    var category = $(".category-radio:checked").val();
                    var type_arr = [];
                    $.each($("input[name='file_upload_type[]']:checked"), function () {
                        type_value = $(this).val().replace(/,/g, '');
                        type_arr.push(type_value);
                    });
                    if ('new_category' == category) {
                        category = $('#new_category').val();
                    }
                    if (!category) {
                        category = '';
                    }

                    //polar question validation
                    if (13 == type) {
                        let polar_error = 0;
                        let polar_required_error = 0;
                        let old_value = "";
                        $('.answers-single .answer-points').each(function () {
                            $(this).css('border-color', '');
                            if ("" != old_value && $(this).val() == old_value) {
                                alert(qsm_admin_messages.polar_q_range_error);
                                polar_error++;
                            }
                            if ("" == $(this).val()) {
                                $(this).css('border-color', 'red');
                                polar_error++;
                                polar_required_error++;
                            }
                            old_value = $(this).val();
                        });
                        if (0 < polar_required_error) {
                            alert(qsm_admin_messages.range_fields_required);
                        }
                        if (0 < polar_error) {
                            setTimeout(function () {
                                $('#save-edit-question-spinner').removeClass('is-active');
                            }, 250);
                            return false;
                        }
                    }

                    var multicategories = [];
                    $.each($("input[name='tax_input[qsm_category][]']:checked"), function () {
                        multicategories.push($(this).val());
                    });
                    var featureImageID = $('.qsm-feature-image-id').val();
                    var featureImageSrc = $('.qsm-feature-image-src').val();
                    var answerType = $('#change-answer-editor').val();
                    var matchAnswer = $('#match-answer').val();

                    var intcnt = 1;
					var answers = [];
                    var $answersElement = jQuery('.answers-single');
                    _.each($answersElement, function (answer) {
                        var $answer = jQuery(answer);
                        var answer = '';
                        var caption = '';
                        if (answerType == 'rich') {
                            var ta_id = $answer.find('textarea').attr('id')
                            answer = wp.editor.getContent(ta_id);
                        } else if (answerType == 'image') {
                            answer = $answer.find('.answer-text').val().trim();
                            answer = $.QSMSanitize(answer);
                            caption = $answer.find('.answer-caption').val().trim();
                            caption = $.QSMSanitize(caption);
                        } else {
                            answer = $answer.find('.answer-text').val().trim();
                            answer = $.QSMSanitize(answer);
                        }

                        var points = $answer.find('.answer-points').val();
                        var correct = 0;
                        if ($answer.find('.answer-correct').prop('checked')) {
                            correct = 1;
                        }

						var ansData = [answer, points, correct];
                        if (answerType == 'image') {
							ansData.push(caption);
                        }
						ansData = QSMQuestion.answerFilter(ansData, $answer, answerType);
						answers.push(ansData);
						intcnt++
                    });
					model.set('answers', answers);

                    $('.questionElements .advanced-content > .qsm-row:not(.core-option)').each(function () {
                        if ($(this).find('input[type="text"]').length > 0) {
                            $($(this).find('input[type="text"]')).each(function () {
                                let element_id = $(this).attr('id');
                                advanced_option[element_id] = $(this).val();
                            });
                        } else if ($(this).find('input[type="number"]').length > 0) {
                            let element_id = $(this).find('input[type="number"]').attr('id');
                            advanced_option[element_id] = $(this).find('input[type="number"]').val();
                        } else if ($(this).find('select').length > 0) {
                            let element_id = $(this).find('select').attr('id');
                            advanced_option[element_id] = $(this).find('select').val();
                        } else if ($(this).find('input[type="checkbox"]').length > 0) {
                            let element_id = $(this).find('input[type="checkbox"]').attr('name');
                            let multi_value = $(this).find('input[type="checkbox"]:checked').map(function () {
                                return this.value;
                            }).get().join(',');
                            element_id = element_id.replace('[]', '');
                            advanced_option[element_id] = multi_value;
                        }
                    });

                    model.save({
                        quizID: quizID,
                        type: type,
                        name: name,
                        question_title: question_title,
                        answerInfo: answerInfo,
                        comments: comments,
                        hint: hint,
                        category: category,
                        multicategories: multicategories,
                        featureImageID: featureImageID,
                        featureImageSrc: featureImageSrc,
                        answers: answers,
                        answerEditor: answerType,
                        matchAnswer: matchAnswer,
                        other_settings: advanced_option,
                        rest_nonce: qsmQuestionSettings.rest_user_nonce
                    }, {
                        headers: {
                            'X-WP-Nonce': qsmQuestionSettings.nonce
                        },
                        success: QSMQuestion.saveSuccess,
                        error: QSMAdmin.displayError,
                        type: 'POST'
                    });
                    jQuery(document).trigger('qsm_save_question', [questionID, CurrentElement]);
                },
                answerFilter: function (ansData, $answer, answerType) {
					return ansData;
				},
                saveSuccess: function (model) {
                    QSMAdmin.displayAlert(qsm_admin_messages.question_saved, 'success');
                    var template = wp.template('question');
                    var page = model.get('page') + 1;
                    var questionName = model.get('name');
                    var new_question_title = model.get('question_title');
                    if (new_question_title !== '') {
                        questionName = $.QSMSanitize(new_question_title);
                    }
                    var category = [];
                    var multicategories = model.get('multicategories');
                    if (multicategories === null || typeof multicategories === "undefined") {
                        //No Action Require
                    } else {
                        $.each(multicategories, function (i, val) {
                            category.push($(".qsm-popup__content #qsm_category-" + val + " label:first-child")[0].textContent);
                        });
                        category = category.filter(item => item);
                    }
                    $('.question[data-question-id=' + model.id + ']').replaceWith(template({
                        id: model.id,
                        type: model.get('type'),
                        category: category.join(', '),
                        question: questionName
                    }));
                    setTimeout(function () {
                        $('#save-edit-question-spinner').removeClass('is-active');
                    }, 250);
                    setTimeout(QSMQuestion.removeNew, 250);
                },
                addNewAnswer: function (answer, questionType = false) {
                    if (!questionType) {
                        questionType = $('#question_type').val();
                    }
                    var answerTemplate = wp.template('single-answer');
					var ansTemp = {
						answer: decodeEntities(answer[0]),
						points: answer[1],
						correct: answer[2],
						count: answer['index'],
						question_id: answer['question_id'],
						answerType: answer['answerType'],
						form_type: qsmQuestionSettings.form_type,
						quiz_system: qsmQuestionSettings.quiz_system
					};
                    if (answer['answerType'] == 'image') {
						ansTemp = {
                            answer: decodeEntities(answer[0]),
                            points: answer[1],
                            correct: answer[2],
                            caption: answer[3],
                            count: answer['index'],
							question_id: answer['question_id'],
							answerType: answer['answerType'],
                            form_type: qsmQuestionSettings.form_type,
                            quiz_system: qsmQuestionSettings.quiz_system
                        };
                    }
					jQuery(document).trigger('qsm_new_answer_template', [ansTemp, answer, questionType]);
					$('#answers').append(answerTemplate(ansTemp));

                    // show points field only for polar in survey and simple form
                    if (qsmQuestionSettings.form_type != 0) {
                        if (questionType == 13) {
                            $('#answers .answer-points').show();
                        } else {
                            $('#answers .answer-points').val('').hide();
                        }
                    }
                    if (qsmQuestionSettings.form_type == 0) {
                        if (questionType == 14) {
                            $('.correct-answer').hide();
                        } else {
                            $('.correct-answer').show();
                        }
                    }

                    if (answer['answerType'] == 'rich' && qsmQuestionSettings.qsm_user_ve === 'true') {
                        var textarea_id = 'answer-' + answer['question_id'] + '-' + answer['index'];
                        wp.editor.remove(textarea_id);
                        var settings = {
                            mediaButtons: true,
                            tinymce: {
                                forced_root_block: '',
                                toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
                            },
                            quicktags: true,
                        };
                        wp.editor.initialize(textarea_id, settings);
                        var anser = QSMQuestion.prepareQuestionText(answer[0]);
                        $(textarea_id).val(anser);
                        tinyMCE.get(textarea_id).setContent(anser);
                    }
                },
                openEditPopup: function (questionID, CurrentElement) {
                    jQuery('.qsm_tab_content').find('.question').removeClass('opened');
                    if (CurrentElement.parents('.question').next('.questionElements').length > 0) {
                        if (CurrentElement.parents('.question').next('.questionElements').is(":visible")) {
                            CurrentElement.parents('.question').next('.questionElements').slideUp('slow');
                            $('.questions').sortable('enable');
                            $('.page').sortable('enable');
                        } else {
                            CurrentElement.parents('.question').addClass('opened');
                            CurrentElement.parents('.question').next('.questionElements').slideDown('slow');
                        }
                        return;
                    } else {
                        CurrentElement.parents('.question').addClass('opened');
                        $('.questions .questionElements').slideDown('slow');
                        $('.questions .questionElements').remove();
                    }
                    //Copy and remove popup div
                    var questionElements = $('#modal-1-content').html();
                    $('#modal-1-content').children().remove();
                    CurrentElement.parents('.question').after("<div style='display: none;' class='questionElements'>" + questionElements + "</div>");

                    //Show question id on question edit screen
                    $('#qsm-question-id').text('ID: ' + questionID);
                    QSMQuestion.prepareCategories();
                    QSMQuestion.processCategories();
                    var question = QSMQuestion.questions.get(questionID);
                    var questionText = QSMQuestion.prepareQuestionText(question.get('name'));
                    $('#edit_question_id').val(questionID);
                    var answerInfo = question.get('answerInfo');
                    var CAI_editor = '';
                    var question_editor = ''
                    if (qsmQuestionSettings.qsm_user_ve === 'true') {
                        wp.editor.remove('question-text');
                        wp.editor.remove('correct_answer_info');
                        QSMQuestion.prepareEditor();
                        question_editor = tinyMCE.get('question-text');
                        CAI_editor = tinyMCE.get('correct_answer_info');
                    }
                    if ($('#wp-question-text-wrap').hasClass('html-active')) {
                        jQuery("#question-text").val(questionText);
                    } else if (question_editor) {
                        tinyMCE.get('question-text').setContent(questionText);
                    } else {
                        jQuery("#question-text").val(questionText);
                    }
                    if ('' != questionText) {
                        jQuery('.qsm-show-question-desc-box').trigger('click');
                    }

                    if ($('#wp-correct_answer_info-wrap').hasClass('html-active')) {
                        jQuery("#correct_answer_info").val(answerInfo);
                    } else if (CAI_editor) {
                        tinyMCE.get('correct_answer_info').setContent(answerInfo);
                    } else {
                        jQuery("#correct_answer_info").val(answerInfo);
                    }

                    $('#answers').empty();
                    var answers = question.get('answers');
                    var answerEditor = question.get('answerEditor');
                    if (answerEditor === null || typeof answerEditor === "undefined") {
                        answerEditor = 'text';
                    }
                    //Get text limit value
                    var get_limit_text = question.get('limit_text');
                    if (get_limit_text === null || typeof get_limit_text === "undefined") {
                        get_limit_text = '0';
                    }
                    //Get limit multiple response value
                    var get_limit_mr = question.get('limit_multiple_response');
                    if (get_limit_mr === null || typeof get_limit_mr === "undefined") {
                        get_limit_mr = '0';
                    }
                    //Get image width value
                    let image_width = question.get('img_width');
                    if (image_width === null || typeof image_width === "undefined") {
                        image_width = '';
                    }
                    //Get image height value
                    let image_height = question.get('img_height');
                    if (image_height === null || typeof image_height === "undefined") {
                        image_height = '';
                    }
                    //Get file upload limit
                    var get_limit_fu = question.get('file_upload_limit');
                    if (get_limit_fu === null || typeof get_limit_fu === "undefined") {
                        get_limit_fu = '0';
                    }
                    //Get checked question type
                    var multicategories = question.get('multicategories');
                    $("input[name='tax_input[qsm_category][]']:checkbox").attr("checked", false);
                    if (multicategories === null || typeof multicategories === "undefined") {
                        //No Action Require
                    } else {
                        $.each(multicategories, function (i, val) {
                            $("input[name='tax_input[qsm_category][]']:checkbox[value='" + val + "']").attr("checked", "true");
                        });
                    }
                    //Get featured image
                    var get_featureImageSrc = question.get('featureImageSrc');
                    var get_featureImageID = question.get('featureImageID');
                    if (get_featureImageSrc === null || typeof get_featureImageSrc === "undefined") {
                        get_featureImageSrc = get_featureImageID = '';
                    }
                    //Get checked question type
                    var get_file_upload_type = question.get('file_upload_type');
                    $("input[name='file_upload_type[]']:checkbox").attr("checked", false);
                    if (get_file_upload_type === null || typeof get_file_upload_type === "undefined") { } else {
                        var fut_arr = get_file_upload_type.split(",");
                        $.each(fut_arr, function (i) {
                            $("input[name='file_upload_type[]']:checkbox[value='" + fut_arr[i] + "']").attr("checked", "true");
                        });
                    }
                    var al = 1;
					_.each(answers, function (answer) {
                        answer['index'] = al;
                        answer['question_id'] = questionID;
                        answer['answerType'] = answerEditor;
                        QSMQuestion.addNewAnswer(answer, question.get('type'));
                        al++;
                    });
                    //get new question type
                    var get_question_title = question.get('question_title');
                    if (get_question_title === null || typeof get_question_title === "undefined") {
                        get_question_title = '';
                    }

                    //Hide the question settings based on question type
                    $('.qsm_hide_for_other').hide();
                    if ($('.qsm_show_question_type_' + question.get('type')).length > 0) {
                        $('.qsm_show_question_type_' + question.get('type')).show();
                    }

                    qsm_hide_show_question_desc(question.get('type'));
                    $('#hint').val(question.get('hint'));
                    $('#image_size-width').val(image_width);
                    $('#image_size-height').val(image_height);
                    $("#question_type").val(question.get('type'));
                    $("#comments").val(question.get('comments'));
                    //Changed checked logic based on new structure for required.
                    $("input#required[value='" + question.get('required') + "']").prop('checked', true);

                    $("#limit_text").val(get_limit_text);
                    $("#limit_multiple_response").val(get_limit_mr);
                    $("#file_upload_limit").val(get_limit_fu);
                    $("#change-answer-editor").val(answerEditor);
                    $(".category-radio").removeAttr('checked');
                    $("#edit-question-id").text('').text(questionID);
                    $("#question_title").val(get_question_title);
                    if (0 !== question.get('category').length) {
                        $(".category-radio").val([question.get('category')]);
                    }
                    //Append feature image
                    if (get_featureImageSrc) {
                        var button = $('.qsm-feature-image-upl');
                        button.html('<img src="' + get_featureImageSrc + '" style="width:150px">');
                        button.next('.qsm-feature-image-rmv').show();
                        button.next().next('.qsm-feature-image-id').val(get_featureImageID);
                        button.next().next().next('.qsm-feature-image-src').val(get_featureImageSrc);
                    }
                    //Append extra settings
                    var all_setting = question.get('settings');
                    if (all_setting === null || typeof all_setting === "undefined") { } else {
                        $.each(all_setting, function (index, value) {
                            if ($('#' + index + '_area').length > 0) {
                                if (1 == $('#' + index + '_area').find('input[type=checkbox]').length) {
                                    $(".questionElements input[name='" + index + "'][value='" + value + "']").attr("checked", "true").prop('checked', true);
                                } else if ($('#' + index + '_area').find('input[type=checkbox]').length > 1) {
                                    var fut_arr = value.split(",");
                                    $.each(fut_arr, function (i) {
                                        $(".questionElements input[name='" + index + "[]']:checkbox[value='" + fut_arr[i] + "']").attr("checked", "true").prop('checked', true);
                                    });
                                } else {
                                    if (value != null) {
                                        $('#' + index).val(value);
                                    }
                                }
                            }
                            if (index == 'matchAnswer') {
                                $('#match-answer').val(value);
                            }
                        });
                    }
                    CurrentElement.parents('.question').next('.questionElements').slideDown('slow');
                    $('#modal-1-content').html(questionElements);
                    //MicroModal.show( 'modal-1' );
                    $('.questions').sortable('disable');
                    $('.page').sortable('disable');

                    if (13 == question.get('type')) {
                        QSMQuestion.prepareEditPolarQuestion(question.get('type'));
                    }
                    QSMQuestion.sync_child_parent_category(questionID);

                    $('#image_size_area').hide();
                    if ('image' === answerEditor) {
                        $('#image_size_area').show();
                    }

                    jQuery(document).trigger('qsm_open_edit_popup', [questionID, CurrentElement]);
                },
                openEditPagePopup: function (pageID) {
                    var page = QSMQuestion.qpages.get(pageID);
                    $('#edit_page_id').val(pageID);
                    $("#edit-page-id").text('').text(pageID);
                    jQuery('#page-options').find(':input, select, textarea').each(function (i, field) {
                        field.value = page.get(field.name);
                        if ('undefined' == field.value) {
                            field.value = "";
                        }
                    });
                    MicroModal.show('modal-page-1');
                },
                removeNew: function () {
                    $('.page-new').removeClass('page-new');
                    $('.question-new').removeClass('question-new');
                },
                prepareQuestionText: function (question) {
                    return jQuery('<textarea />').html(question).text();
                },
                prepareEditor: function () {
                    var settings = {
                        mediaButtons: true,
                        tinymce: {
                            forced_root_block: '',
                            toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
                        },
                        quicktags: true,
                    };
                    wp.editor.initialize('question-text', settings);
                    wp.editor.initialize('correct_answer_info', settings);
                },
                sync_child_parent_category: function (questionID) {
                    $('.qsm_category_checklist').find('input').each(function (index, input) {
                        $(input).bind('change', function () {
                            var checkbox = $(this);
                            var is_checked = $(checkbox).is(':checked');
                            if (is_checked) {
                                $(checkbox).parents('li').children('label').children('input').prop("checked", true);
                            } else {
                                $(checkbox).parentsUntil('ul').find('input').prop("checked", false);
                            }
                            jQuery(document).trigger('qsm_sync_child_parent_category', [checkbox, questionID]);
                        });
                    });
                },
                question_type_change: function (previous_question_val, questionID) {
                    //you can override this object
                    var ans_type = $('#change-answer-editor').val();
                    if (2 == questionID && 'text' !== ans_type) {
                        $('#change-answer-editor').val('text');
                        $('.answers-single').remove();
                    }
                    if (13 != questionID) {
                        $('.new-answer-button').show();
                        $('.remove-answer-icon').show();
                        $('.answer-points').css('border-color', '');
                        let ans_placeholder = qsm_admin_messages.your_answer;
                        "image" == ans_type && (ans_placeholder = qsm_admin_messages.insert_image_url), $("#answers").find(".answers-single input.answer-text").attr("placeholder", ans_placeholder), $("#answers").find(".answers-single input.answer-points").attr("placeholder", qsm_admin_messages.points);
                    }
                },
                prepareEditPolarQuestion: function (question_val) {
                    var answerType = $('#change-answer-editor').val();
                    if (13 == question_val) {
                        if ($('#answers').find('.answers-single').length < 2) {
                            $('#new-answer-button').trigger('click');
                            if ($('#answers').find('.answers-single').length < 2) {
                                $('#new-answer-button').trigger('click');
                            }
                        }
                        if ($('#answers').find('.answers-single').length > 2) {
                            jQuery('#answers').find('.answers-single').slice(2).remove();
                        }
                        $('.new-answer-button').hide();
                        $('#answers').find('.answers-single .remove-answer-icon').hide();

                        let ans_l_placeholder = qsm_admin_messages.left_label;
                        "image" == answerType && (ans_l_placeholder = "Insert left image URL"), $("#answers").find(".answers-single:first-child input.answer-text").attr("placeholder", ans_l_placeholder);
                        let ans_r_placeholder = qsm_admin_messages.right_label;
                        "image" == answerType && (ans_r_placeholder = "Insert right image URL"), $("#answers").find(".answers-single:last-child input.answer-text").attr("placeholder", ans_r_placeholder), $("#answers").find(".answers-single:first-child input.answer-points").attr("placeholder", qsm_admin_messages.left_range), $("#answers").find(".answers-single:last-child input.answer-points").attr("placeholder", qsm_admin_messages.right_range);
                        if ( "" == $("#answers").find(".answers-single:first-child input.answer-points").val() ) {
                            $("#answers").find(".answers-single:first-child input.answer-points").val(0);
                        }
                        if ( "" == $("#answers").find(".answers-single:last-child input.answer-points").val() ) {
                            $("#answers").find(".answers-single:last-child input.answer-points").val(5);
                        }
                    } else {
                        $('.new-answer-button').show();
                        $('.remove-answer-icon').show();
                        let ans_placeholder = qsm_admin_messages.your_answer;
                        "image" == answerType && (ans_placeholder = qsm_admin_messages.insert_image_url), $("#answers").find(".answers-single input.answer-text").attr("placeholder", ans_placeholder), $("#answers").find(".answers-single input.answer-points").attr("placeholder", qsm_admin_messages.points);
                    }
                }
            };

            $(function () {
                QSMQuestion.pageCollection = Backbone.Collection.extend({
                    model: QSMQuestion.page
                });
                QSMQuestion.qpages = new QSMQuestion.pageCollection();
                QSMQuestion.questionCollection = Backbone.Collection.extend({
                    url: wpApiSettings.root + 'quiz-survey-master/v1/questions',
                    model: QSMQuestion.question
                });
                QSMQuestion.questions = new QSMQuestion.questionCollection();
                $('.new-page-button').on('click', function (event) {
                    event.preventDefault();
                    QSMQuestion.addNewPage();
                });

                $('.questions').on('click', '.new-question-button', function (event) {
                    event.preventDefault();
                    if (jQuery('.questionElements').is(':visible')) {
                        $('#save-popup-button').trigger('click');
                    }
                    QSMQuestion.createQuestion($(this).parents('.page').index());
                });

                $('.questions').on('click', '.add-question-bank-button', function (event) {
                    event.preventDefault();
                    if (jQuery('.questionElements').is(':visible')) {
                        $('#save-popup-button').trigger('click');
                    }
                    QSMQuestion.openQuestionBank($(this).parents('.page').index());
                });

                //Show more question on load more
                $(document).on('click', '.qb-load-more-question', function (event) {
                    event.preventDefault();
                    QSMQuestion.loadQuestionBank();
                });

                //Show category related question
                $(document).on('change', '#question-bank-cat, #question-bank-quiz', function (event) {
                    event.preventDefault();
                    QSMQuestion.loadQuestionBank('change');
                });

                //Show searched question
                $(document).on('submit', '#question-bank-search-form', function (event) {
                    event.preventDefault();
                    QSMQuestion.loadQuestionBank('change');
                });

                $('.questions').on('click', '.edit-question-button', function (event) {
                    event.preventDefault();
                    $('.qsm-category-filter').trigger('keyup');
                    QSMQuestion.openEditPopup($(this).parents('.question').data('question-id'), $(this));
                });
                $('.questions').on('click', '.edit-page-button', function (event) {
                    event.preventDefault();
                    QSMQuestion.openEditPagePopup($(this).parents('.page').data('page-id'));
                });

                $(document).on('click', '.questions .duplicate-question-button', function (event) {
                    event.preventDefault();
                    QSMQuestion.duplicateQuestion($(this).parents('.question').data('question-id'));
                });
                $('.questions').on('click', '.delete-question-button', function (event) {
                    event.preventDefault();
                    remove = $(this);
                    $(this).parents('.question').next('.questionElements').slideUp('slow');
                    // opens-up question-delete modal
                    MicroModal.show('modal-7');
                    $('#unlink-question-button').attr('data-question-iid', $(this).data('question-iid'));
                    $('#delete-question-button').attr('data-question-iid', $(this).data('question-iid'));
                });
                // removes question from database
                $('#delete-question-button').click(function (event) {
                    event.preventDefault();
                    var question_id = $(this).data('question-iid');
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            'action': 'qsm_delete_question_from_database',
                            'question_id': question_id,
                            'nonce': qsmQuestionSettings.single_question_nonce
                        },
                        success: function (response) {
                            // do nothing
                        }
                    });
                    remove.parents('.question').remove();
                    QSMQuestion.countTotal();
                    $('.save-page-button').trigger('click');
                    MicroModal.close('modal-7');
                });

                // unlink question from  a particular quiz.
                $('#unlink-question-button').click(function (event) {
                    event.preventDefault();
                    var question_id = $(this).data('question-iid');
                    remove.parents('.question').remove();
                    QSMQuestion.countTotal();
                    $('.save-page-button').trigger('click');
                    MicroModal.close('modal-7');
                });

                $(document).on('click', '.delete-page-button', function (event) {
                    event.preventDefault();
                    if (confirm(qsm_admin_messages.confirm_message)) {
                        var pageID = $(this).parent().siblings('main').children('#edit_page_id').val();
                        $('.page[data-page-id=' + pageID + ']').remove();
                        $('.save-page-button').trigger('click');
                        QSMQuestion.countTotal();
                        MicroModal.close('modal-page-1');
                    }
                });
                $(document).on('click', '#answers .delete-answer-button', function (event) {
                    event.preventDefault();
                    $(this).parents('.answers-single').remove();
                });
                $(document).on('click', '#delete-action .deletion', function (event) {
                    event.preventDefault();
                    $(this).parents('.questionElements').slideUp('slow');
                });
                $(document).on('click', '#save-popup-button', function (event) {
                    event.preventDefault();
                    questionElements = $(this).parents('.questionElements');
                    if (6 == questionElements.find('#question_type').val()) {
                        question_description = wp.editor.getContent('question-text').trim();
                        if (question_description == '' || question_description == null) {
                            alert(qsm_admin_messages.html_section_empty);
                            return false;
                        }
                    }
                    if (14 == questionElements.find('#question_type').val()) {
                        question_description = wp.editor.getContent('question-text').trim();
                        blanks = question_description.match(/%BLANK%/g);
                        options_length = $('.answer-text-div').length
                        if ($('#match-answer').val() == 'sequence') {
                            if (blanks == null || blanks.length != options_length) {
                                $('.modal-8-table').html(qsm_admin_messages.blank_number_validation);
                                MicroModal.show('modal-8');
                                return false;
                            }
                        } else {
                            if (blanks == null || options_length === 0) {
                                $('.modal-8-table').html(qsm_admin_messages.blank_required_validation);
                                MicroModal.show('modal-8');
                                return false;
                            }
                        }


                    }
                    $('#save-edit-question-spinner').addClass('is-active');
                    var model_html = $('#modal-1-content').html();
                    $('#modal-1-content').children().remove();

                    QSMQuestion.saveQuestion($(this).parents('.questionElements').children('#edit_question_id').val(), $(this));
                    $('.save-page-button').trigger('click');
                    $('#modal-1-content').html(model_html);
                });
                $(document).on('click', '#new-answer-button', function (event) {
                    event.preventDefault();
                    var question_id = $('#edit_question_id').val();
                    var questionType = $('#question_type').val();
                    var answer_length = $('#answers').find('.answers-single').length;
                    var answerType = $('#change-answer-editor').val();
                    if (answer_length > 1 && $('#question_type').val() == 13) {
                        alert(qsm_admin_messages.polar_options_validation);
                        return;
                    }
                    var answer = ['', '', 0];
					answer['index'] = answer_length + 1;
					answer['question_id'] = question_id;
					answer['answerType'] = answerType;
                    QSMQuestion.addNewAnswer(answer, questionType);
                });

                $(document).on('click', '.qsm-popup-bank .import-button', function (event) {
                    event.preventDefault();
                    $(this).text('').text(qsm_admin_messages.adding_question);
                    import_button = $(this);
                    $('.import-button').addClass('disable_import');
                    QSMQuestion.addQuestionFromQuestionBank($(this).data('question-id'));
                    MicroModal.close('modal-2');
                });

                //Click on selected question button.
                $('.qsm-popup-bank').on('click', '#qsm-import-selected-question', function (event) {
                    var $total_selction = $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').length;
                    if ($total_selction === 0) {
                        alert(qsm_admin_messages.no_question_selected);
                    } else {
                        $.fn.reverse = [].reverse;
                        $($('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').reverse()).each(function () {
                            $(this).find('.import-button').text('').text(qsm_admin_messages.adding_question);
                            import_button = $(this).find('.import-button');
                            QSMQuestion.countTotal();
                            QSMQuestion.addQuestionFromQuestionBank($(this).data('question-id'));
                            $(this).find('.import-button').text('').text(qsm_admin_messages.add_question);
                        });
                        $('.import-button').addClass('disable_import');
                        $('#question-bank').find('[name="qsm-question-checkbox[]"]').attr('checked', false);
                        MicroModal.close('modal-2');
                    }
                });
                //Delete question from question bank
                $('.qsm-popup-bank').on('click', '#qsm-delete-selected-question', function (event) {
                    if (confirm(qsm_admin_messages.confirm_message)) {
                        var $total_selction = $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').length;
                        if ($total_selction === 0) {
                            alert(qsm_admin_messages.no_question_selected);
                        } else {
                            $.fn.reverse = [].reverse;
                            var question_ids = $($('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').reverse()).map(function () {
                                return $(this).data('question-id');
                            }).get().join(',');
                            if (question_ids) {
                                $.ajax({
                                    url: ajaxurl,
                                    method: 'POST',
                                    data: {
                                        'action': 'qsm_delete_question_question_bank',
                                        'question_ids': question_ids,
                                        'nonce': qsmQuestionSettings.question_bank_nonce
                                    },
                                    success: function (response) {
                                        var data = jQuery.parseJSON(response);
                                        if (data.success === true) {
                                            $('#question-bank').find('[name="qsm-question-checkbox[]"]:checked').parents('.question-bank-question').slideUp('slow');
                                            alert(data.message);
                                        } else {
                                            alert(data.message);
                                        }
                                    }
                                });
                            }
                        }
                    }
                });

                //Select all button.
                $(document).on('change', '.qsm-question-checkbox', function (event) {
                    event.preventDefault();
                    if ($('.qsm-question-checkbox:checked').length > 0) {
                        $('.qsm-question-bank-footer').addClass('opened');
                    } else {
                        $('.qsm-question-bank-footer').removeClass('opened');
                    }
                });
                $(document).on('change', '#qsm_select_all_question', function (event) {
                    event.preventDefault();
                    $('.qsm-question-checkbox').prop('checked', jQuery('#qsm_select_all_question').prop('checked'));
                    if ($('.qsm-question-checkbox:checked').length > 0) {
                        $('.qsm-question-bank-footer').addClass('opened');
                    } else {
                        $('.qsm-question-bank-footer').removeClass('opened');
                    }
                });

                $('.save-page-button').on('click', function (event) {
                    event.preventDefault();
                    $('#save-edit-quiz-pages').addClass('is-active');
                    QSMQuestion.savePages();
                });
                $('#save-page-popup-button').on('click', function (event) {
                    event.preventDefault();
                    var pageID = $(this).parent().siblings('main').children('#edit_page_id').val();
                    var pageKey = jQuery('#pagekey').val();
                    if (pageKey.replace(/^\s+|\s+$/g, "").length == 0) {
                        alert(qsm_admin_messages.page_name_required);
                        return false;
                    } else if (null == pageKey.match(/^[A-Za-z0-9\-\s]+$/)) {
                        alert(qsm_admin_messages.page_name_validation);
                        return false;
                    } else {
                        QSMQuestion.updateQPage(pageID);
                        QSMQuestion.savePages();
                        MicroModal.close('modal-page-1');
                    }
                });

                $(document).on('change', '#change-answer-editor', function (event) {
                    var newVal = $(this).val();
                    if (confirm(qsm_admin_messages.question_reset_message)) {
                        $('#answers').find('.answers-single').remove();
                        $('#image_size_area').hide();
                        if ('image' === newVal) {
                            $('#image_size_area').show();
                        }
                    } else {
                        if (newVal == 'rich') {
                            $(this).val('text');
                        } else {
                            $(this).val('rich');
                        }
                        return false;
                    }
                    var question_val = $('#question_type').val();
                    if (13 == question_val) {
                        QSMQuestion.prepareEditPolarQuestion(question_val);
                    }
                    if (18 == question_val) {
                        jQuery(document).trigger('qsm-change-answer-editor-after');
                    }
                });

                // Adds event handlers for searching questions
                $('#question_search').on('keyup', function () {
                    $('.question').each(function () {
                        if ($(this).text().toLowerCase().indexOf($('#question_search').val().toLowerCase()) === -1) {
                            $(this).hide();
                        } else {
                            $(this).show();
                            $(this).parent('.page').show();

                        }
                    });
                    $('.page').each(function () {
                        if (0 === $(this).children('.question:visible').length) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    });
                    if (0 === $('#question_search').val().length) {
                        $('.page').show();
                        $('.question').show();
                    }
                });

                qsm_init_sortable();

                if (qsmQuestionSettings.qsm_user_ve === 'true') {
                    QSMQuestion.prepareEditor();
                }
                QSMQuestion.loadQuestions();

                /**
                 * Hide/show advanced option
                 */
                $(document).on('click', '#show-advanced-option', function () {
                    var $this = $(this);
                    $(this).next('div.advanced-content').slideToggle('slow', function () {
                        if ($(this).is(':visible')) {
                            $this.text('').html(qsm_admin_messages.hide_advance_options);
                        } else {
                            $this.text('').html(qsm_admin_messages.show_advance_options);
                        }
                    });
                });

                //Hide the question settings based on question type
                var previous_question_val;
                $(document).on('focus', '#question_type', function () {
                    previous_question_val = this.value;
                })
                $(document).on('change', '#question_type', function () {
                    var question_val = $('#question_type').val();
                    QSMQuestion.question_type_change(previous_question_val, question_val);
                    if (6 == question_val) {
                        var question_description = wp.editor.getContent('question-text');
                        if (question_description == 'Add description here!') {
                            tinyMCE.get('question-text').setContent('');
                        }
                    }
                    if (14 == question_val) {
                        $('.correct-answer').hide();
                    } else {
                        $('.correct-answer').show();
                    }
                    if (15 == question_val || 16 == question_val || 17 == question_val) {
                        MicroModal.show('modal-advanced-question-type');
                        $('#question_type').val(previous_question_val);
                        return false;
                    }
                    // show points field only for polar in survey and simple form
                    if (qsmQuestionSettings.form_type != 0) {
                        if (13 == question_val) {
                            $('.answer-points').show();
                        } else {
                            $('.answer-points').val('').hide();
                        }
                    }

                    $('.qsm_hide_for_other').hide();
                    if ($('.qsm_show_question_type_' + question_val).length > 0) {
                        $('.qsm_show_question_type_' + question_val).show();
                    }
                    qsm_hide_show_question_desc(question_val);
                    if (13 == question_val) {
                        QSMQuestion.prepareEditPolarQuestion(question_val);
                    }
                });



                //Add new category
                $(document).on('click', '#qsm-category-add-toggle', function () {
                    if ($('#qsm-category-add').is(":visible")) {
                        $('.questionElements #new_category_new').attr('checked', false);
                        $('#qsm-category-add').slideUp('slow');
                    } else {
                        $('.questionElements #new_category_new').attr('checked', true).prop('checked', 'checked');
                        $('#qsm-category-add').slideDown('slow');
                    }
                });

                //Hide/show quesion description
                $(document).on('click', '.qsm-show-question-desc-box', function (e) {
                    e.preventDefault();
                    $(this).hide();
                    var question_description = wp.editor.getContent('question-text');
                    if (question_description == '' || question_description == null) {
                        var questionElements = $(this).parents('.questionElements');
                        if (6 == questionElements.find('#question_type').val()) {
                            tinyMCE.get('question-text').setContent('');
                        }
                    }
                    $(this).next('.qsm-row').show();
                });
                $(document).on('click', '.qsm-hide-question-desc-box', function (e) {
                    e.preventDefault();
                    $(this).parents('.qsm-row.qsm-editor-wrap').hide();
                    $('.qsm-show-question-desc-box').show();
                });

                //Open file upload on feature image
                $('body').on('click', '.qsm-feature-image-upl', function (e) {
                    e.preventDefault();
                    var button = $(this),
                        custom_uploader = wp.media({
                            title: qsm_admin_messages.insert_img,
                            library: {
                                // uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
                                type: 'image'
                            },
                            button: {
                                text: qsm_admin_messages.use_img // button label text
                            },
                            multiple: false
                        }).on('select', function () { // it also has "open" and "close" events
                            var attachment = custom_uploader.state().get('selection').first().toJSON();
                            button.html('<img src="' + attachment.url + '" style="width:150px">');
                            button.next('.qsm-feature-image-rmv').show();
                            button.next().next('.qsm-feature-image-id').val(attachment.id);
                            button.next().next().next('.qsm-feature-image-src').val(attachment.url);
                        }).open();
                });

                // on remove button click
                $('body').on('click', '.qsm-feature-image-rmv', function (e) {
                    e.preventDefault();
                    var button = $(this);
                    button.next().val(''); // emptying the hidden field
                    button.next().next().val(''); // emptying the hidden field
                    button.hide().prev().html(qsm_admin_messages.upload_img);
                });

            });
            var decodeEntities = (function () {
                //create a new html document (doesn't execute script tags in child elements)
                var doc = document.implementation.createHTMLDocument("");
                var element = doc.createElement('div');

                function getText(str) {
                    element.innerHTML = str;
                    str = element.textContent;
                    element.textContent = '';
                    return str;
                }

                function decodeHTMLEntities(str) {
                    if (str && typeof str === 'string') {
                        var x = getText(str);
                        while (str !== x) {
                            str = x;
                            x = getText(x);
                        }
                        return x;
                    }
                }
                return decodeHTMLEntities;
            })();

            function qsm_hide_show_question_desc(question_type) {
                $('.question-type-description').hide();
                if ($('#question_type_' + question_type + '_description').length > 0) {
                    $('#question_type_' + question_type + '_description').show();
                }
            }

            function qsm_init_sortable() {
                $('.questions').sortable({
                    opacity: 70,
                    cursor: 'move',
                    handle: 'span.dashicons-move',
                    placeholder: "ui-state-highlight",
                    stop: function (evt, ui) {
                        $('.questions > .page').each(function () {
                            var page = parseInt($(this).index()) + 1;
                            $(this).find('.page-number').text('Page ' + page);
                        });
                        setTimeout(
                            function () {
                                $('.save-page-button').trigger('click');
                            },
                            200
                        )
                    }
                });
                $('.page').sortable({
                    items: '.question',
                    handle: 'span.dashicons-move',
                    opacity: 70,
                    cursor: 'move',
                    placeholder: "ui-state-highlight",
                    connectWith: '.page',
                    stop: function (evt, ui) {
                        setTimeout(
                            function () {
                                $('.save-page-button').trigger('click');
                            },
                            200
                        )
                    }
                });
            }

            function qsmRandomID(length) {
                var result = '';
                var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                var charactersLength = characters.length;
                for (var i = 0; i < length; i++) {
                    result += characters.charAt(Math.floor(Math.random() * charactersLength));
                }
                return result;
            }
            $(document).on('keyup', '.qsm-category-filter', function () {
                search_term = $.trim($(this).val());
                if (search_term == '') {
                    $('.qsm_category_checklist li').each(function () {
                        $(this).show()
                    });
                } else {
                    search_term = new RegExp(search_term, 'i');
                    $('.qsm_category_checklist li').each(function () {
                        search_string = $(this).children('label').text();
                        result = search_string.search(search_term);
                        if (result > -1) {
                            $(this).show();
                            if ($(this).parent('ul').hasClass('children')) {
                                $(this).parents('li').show();
                            }
                        } else {
                            $(this).hide();
                        }
                    });

                }
            });

            $(document).on('click', '.add-multiple-category', function (e) {
                e.preventDefault();
                MicroModal.show('modal-9', {
                    onShow: function () {
                        $('#new-category-name').val($('.qsm-category-filter').val());
                        $('.qsm-category-filter').val('').trigger('keyup');
                    },
                    onClose: function () {
                        $('#modal-9-content .info').html('');
                        $('#new-category-name').val('');
                        $('#qsm-parent-category').val(-1);
                    }
                });
            });

            $(document).on('click', '#save-multi-category-button', function (e) {
                e.preventDefault();
                duplicate = false;
                new_category = $('#new-category-name').val().trim();
                parent_category = $('#qsm-parent-category option:selected').val();
                if (new_category == '') {
                    $('#modal-9-content .info').html(qsm_admin_messages.category_not_empty);
                    return false;
                } else {
                    $('#qsm-parent-category option').each(function () {
                        if ($(this).text().toLowerCase() == new_category.toLowerCase()) {
                            duplicate = true;
                            $('#modal-9-content .info').html(qsm_admin_messages.category + ' ' + new_category + ' ' + qsm_admin_messages.already_exists_in_database);
                            return false;
                        }
                    });

                    if (!duplicate) {
                        var new_category_data = {
                            action: 'save_new_category',
                            name: new_category,
                            parent: parent_category
                        };
                        $('#modal-9-content .info').html('');
                        jQuery.ajax(ajaxurl, {
                            data: new_category_data,
                            method: 'POST',
                            success: function (response) {
                                result = JSON.parse(response);
                                if (result.term_id > 0) {
                                    $('#qsm-parent-category').append('<option class="level-0" value="' + result.term_id + '">' + new_category + '</option>');
                                    if (parent_category == -1) {
                                        $('.qsm_category_checklist').prepend('<li id="qsm_category-' + result.term_id + '"><label class="selectit"><input value="' + result.term_id + '" type="checkbox" checked="checked" name="tax_input[qsm_category][]"  id="in-qsm_category-' + result.term_id + '"> ' + new_category + '</label></li>');
                                    } else {
                                        if ($('.qsm_category_checklist li#qsm_category-' + parent_category).children('ul').length > 0) {
                                            $('.qsm_category_checklist li#qsm_category-' + parent_category).children('ul').append('<li id="qsm_category-' + result.term_id + '"><label class="selectit"><input value="' + result.term_id + '" type="checkbox" name="tax_input[qsm_category][]"  id="in-qsm_category-' + result.term_id + '"> ' + new_category + '</label></li>');
                                        } else {
                                            $('.qsm_category_checklist li#qsm_category-' + parent_category).append('<ul class="children"><li id="qsm_category-' + result.term_id + '"><label class="selectit"><input value="' + result.term_id + '" type="checkbox" name="tax_input[qsm_category][]"  id="in-qsm_category-' + result.term_id + '"> ' + new_category + '</label></li></ul>')
                                        }
                                        $('.qsm_category_checklist li#qsm_category-' + result.term_id).children('label').children('input').prop('checked', true);
                                        $('.qsm_category_checklist li#qsm_category-' + result.term_id).parents('li').each(function () {
                                            $(this).children('label').children('input').prop('checked', true);
                                        });
                                    }
                                    MicroModal.close('modal-9')
                                }
                            }
                        });
                    }
                }
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
                    $('.results-page').each(function () {
                        page = {
                            'conditions': [],
                            'page': wp.editor.getContent($(this).find('.results-page-template').attr('id')),
                            'redirect': false,
                        };
                        redirect_value = $(this).find('.results-page-redirect').val();
                        if ('' != redirect_value) {
                            page.redirect = redirect_value;
                        }
                        $(this).find('.results-page-condition').each(function () {
                            page.conditions.push({
                                'category': $(this).children('.results-page-condition-category').val(),
                                'criteria': $(this).children('.results-page-condition-criteria').val(),
                                'operator': $(this).children('.results-page-condition-operator').val(),
                                'value': $(this).children('.results-page-condition-value').val()
                            });
                        });
                        pages.push(page);
                    });
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
                                QSMAdmin.displayAlert(qsm_admin_messages.results_page_saved, 'success');
                            } else {
                                QSMAdmin.displayAlert( qsm_admin_messages.results_page_save_error + ' ' + qsm_admin_messages.results_page_saved, 'error');
                            }
                        })
                        .fail(QSMAdmin.displayjQueryError);
                },
                loadResults: function () {
                    //QSMAdmin.displayAlert( 'Loading results pages...', 'info' );
                    $.ajax({
                        url: wpApiSettings.root + 'quiz-survey-master/v1/quizzes/' + qsmResultsObject.quizID + '/results',
                        headers: { 'X-WP-Nonce': qsmResultsObject.nonce },
                    })
                        .done(function (pages) {
                            $('#results-pages').find('.qsm-spinner-loader').remove();
                            pages.forEach(function (page, i, pages) {
                                QSMAdminResults.addResultsPage(page.conditions, page.page, page.redirect);
                            });
                            QSMAdmin.clearAlerts();
                        })
                        .fail(QSMAdmin.displayjQueryError);
                },
                addCondition: function ($page, category, criteria, operator, value) {
                    var template = wp.template('results-page-condition');
                    $page.find('.results-page-when-conditions').append(template({
                        'category': category,
                        'criteria': criteria,
                        'operator': operator,
                        'value': value
                    }));
                },
                newCondition: function ($page) {
                    QSMAdminResults.addCondition($page, '', 'score', 'equal', 0);
                },
                addResultsPage: function (conditions, page, redirect) {
                    QSMAdminResults.total += 1;
                    var template = wp.template('results-page');
                    $('#results-pages').append(template({ id: QSMAdminResults.total, page: page, redirect: redirect }));
                    conditions.forEach(function (condition, i, conditions) {
                        QSMAdminResults.addCondition(
                            $('.results-page:last-child'),
                            condition.category,
                            condition.criteria,
                            condition.operator,
                            condition.value
                        );
                    });
                    var settings = {
                        mediaButtons: true,
                        tinymce: {
                            forced_root_block: '',
                            toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,strikethrough,hr,forecolor,pastetext,removeformat,codeformat,charmap,undo,redo'
                        },
                        quicktags: true,
                    };
                    wp.editor.initialize('results-page-' + QSMAdminResults.total, settings);
                    jQuery(document).trigger('qsm_after_add_result_block', [conditions, page, redirect]);
                },
                newResultsPage: function () {
                    var conditions = [{
                        'category': '',
                        'criteria': 'score',
                        'operator': 'greater',
                        'value': '0'
                    }];
                    var page = '%QUESTIONS_ANSWERS%';
                    QSMAdminResults.addResultsPage(conditions, page);
                }
            };
            $(function () {
                QSMAdminResults.loadResults();

                $('.add-new-page').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminResults.newResultsPage();
                });
                $('.save-pages').on('click', function (event) {
                    event.preventDefault();
                    QSMAdminResults.saveResults();
                });
                $('#results-pages').on('click', '.new-condition', function (event) {
                    event.preventDefault();
                    $page = $(this).closest('.results-page');
                    QSMAdminResults.newCondition($page);
                });
                $('#results-pages').on('click', '.delete-page-button', function (event) {
                    event.preventDefault();
                    $(this).closest('.results-page').remove();
                });
                $('#results-pages').on('click', '.delete-condition-button', function (event) {
                    event.preventDefault();
                    $(this).closest('.results-page-condition').remove();
                });
            });
        }
    }
}(jQuery));

