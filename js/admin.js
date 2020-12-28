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
            QSMAdmin.displayAlert('Error: ' + errorThrown + '! Please try again.', 'error');
        },
        /**
         * Catches an error from a BackBone function (i.e. model.save())
         */
        displayError: function (jqXHR, textStatus, errorThrown) {
            QSMAdmin.displayAlert('Error: ' + errorThrown.errorThrown + '! Please try again.', 'error');
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
                    $.post(ajaxurl, {name: name, email: email, nonce: qsmAdminObject.saveNonce, action: action},
                    function (data) {
                        if (data)
                        {
                            $("#status").text('');
                            if (data == "Some fields are missing.")
                            {
                                $("#status").text("Please fill in your name and email.");
                                $("#status").css("color", "red");
                            } else if (data == "Invalid email address.")
                            {
                                $("#status").text("Your email address is invalid.");
                                $("#status").css("color", "red");
                            } else if (data == "Invalid list ID.")
                            {
                                $("#status").text("Your list ID is invalid.");
                                $("#status").css("color", "red");
                            } else if (data == "Already subscribed.")
                            {
                                $("#status").text("You're already subscribed!");
                                $("#status").css("color", "red");
                            } else
                            {
                                $("#status").text("Thanks, you are now subscribed to our mailing list!");
                                $("#status").css("color", "green");
                            }                            
                            $form.find('#submit').attr('disabled', false);
                        } else
                        {
                            alert("Sorry, unable to subscribe. Please try again later!");
                        }
                    }
            );
        });        
        jQuery('.category_selection_random').change(function(){
                var checked_data = jQuery(this).val().toString();                
                jQuery('.catergory_comma_values').val(checked_data);
        });
        jQuery('.row-actions-c > .rtq-delete-result').click(function(e){
            e.preventDefault();
            var $this = jQuery(this);
            if(confirm('are you sure?')){   
                var action = 'qsm_dashboard_delete_result';
                var result_id = jQuery(this).data('result_id');
                $.post(ajaxurl, {result_id: result_id, action: action },
                    function (data) {
                        if(data == 'failed'){
                            alert('Error to delete the result!');
                        }else{
                            $this.parents('li').slideUp();
                            $this.parents('li').remove();
                        }                 
                    }
                );
            }
        });
        jQuery('.load-quiz-wizard').click(function(e){
            e.preventDefault();            
            MicroModal.show('model-wizard');
            var height = jQuery(".qsm-wizard-template-section").css("height");
            jQuery(".qsm-wizard-setting-section").css("height", height);
            if(jQuery( "#accordion" ).length > 0){
                var icons = {
                    header: "iconClosed",    // custom icon class
                    activeHeader: "iconOpen" // custom icon class
                };
                jQuery( "#accordion" ).accordion({
                        collapsible: true,
                        icons: icons,
                        heightStyle: "content"
                });
                jQuery('#accordion h3.ui-accordion-header').next().slideDown();
                jQuery('.template-list .template-list-inner:first-child').trigger('click');                
            }
        });
        //Get quiz options
        jQuery('.template-list-inner').click(function(){
            var action = 'qsm_wizard_template_quiz_options';
            var settings = jQuery(this).data('settings');
            var addons = jQuery(this).data('addons');
            jQuery('.template-list .template-list-inner').removeClass('selected-quiz-template');
            jQuery(this).addClass('selected-quiz-template');
            jQuery('#quiz_settings_wrapper').html('').html('<div class="qsm-spinner-loader"></div>');
            jQuery('#recomm_addons_wrapper').html('').html('<div class="qsm-spinner-loader"></div>');
            $.post(ajaxurl, {settings: settings, addons: addons, action: action },
                function (data) {
                    var diff_html = data.split('=====');                    
                    jQuery('#quiz_settings_wrapper').html('');
                    jQuery('#quiz_settings_wrapper').html(diff_html[0]);
                    jQuery('#recomm_addons_wrapper').html('');
                    jQuery('#recomm_addons_wrapper').html(diff_html[1]);
                    jQuery( "#accordion" ).accordion();
                    jQuery('#accordion h3.ui-accordion-header').next().slideDown();
                    $( '#quiz_settings_wrapper select' ).each(function(){
                        var name = $(this).attr('name');
                        var value = $(this).val();                        
                        if( $( '.' + name + '_' + value ).length > 0 ){                
                            $( '.' + name + '_' + value ).show();
                        }
                    });
                }
            );
        });
        
        jQuery( '#create-quiz-button' ).on( 'click', function( event ) {
            event.preventDefault();
            if( jQuery('#new-quiz-form').find('.quiz_name').val() === ''){
                jQuery('#new-quiz-form').find('.quiz_name').addClass('qsm-required');
                jQuery('#new-quiz-form').find('.quiz_name').focus();
                return;
            }
            jQuery( '#new-quiz-form' ).submit();
        });
        
        //Hide/Show legacy option
        jQuery('#legacy_options').parents('tr').nextAll('tr').hide();
        jQuery(document).on('click','#legacy_options', function(e){
            e.preventDefault();
            if( jQuery('#legacy_options').parents('tr').next('tr').is(':visible') ){
                jQuery(this).text('').text('Show Legacy options');
                jQuery('#legacy_options').parents('tr').nextAll('tr').hide();                
            }else{
                jQuery(this).text('').text('Hide Legacy options');
                jQuery('#legacy_options').parents('tr').nextAll('tr').show();
            }
        });
        
        //Dismiss the welcome panel
        jQuery('.qsm-welcome-panel-dismiss').click(function(e){
            e.preventDefault();
            jQuery( '#welcome_panel' ).addClass('hidden');            
            jQuery('#screen-options-wrap').find('#welcome_panel-hide').prop('checked', false);
            postboxes.save_state( 'toplevel_page_qsm_dashboard' );
        });
        //Get the message in text tab
        jQuery( document ).on( 'change', '#qsm_question_text_message_id' , function(){            
            var text_id = jQuery(this).val();
            jQuery('.qsm-text-main-wrap .qsm-text-tab-message-loader').show();
            jQuery.post(ajaxurl, {text_id: text_id, 'quiz_id': qsmTextTabObject.quiz_id, action: 'qsm_get_question_text_message'},function (response) {
                var data = jQuery.parseJSON( response );
                if( data.success === true ){
                    var text_msg = data.text_message;                    
                    if ($('#wp-qsm_question_text_message-wrap').hasClass('html-active')) {
                        jQuery( "#qsm_question_text_message" ).val( text_msg );
                    } else {
                        text_msg = text_msg.replace(/\n/g,"<br>");
                        tinyMCE.get( 'qsm_question_text_message' ).setContent( text_msg );
                    }                    
                    //tinyMCE.get( 'qsm_question_text_message' ).setContent( text_msg );
                    jQuery( '.qsm-text-allowed-variables > .qsm-text-variable-wrap' ).html('').html( data.allowed_variable_text );
                    jQuery('.qsm-text-main-wrap .qsm-text-tab-message-loader').hide();
                } else {
                    console.log( data.message );
                }
            });
        });
        //Save the message in text tab
        jQuery( document ).on( 'click', '#qsm_save_text_message' , function(){
            var $this = jQuery(this);
            $this.siblings('.spinner').addClass('is-active');
            var text_id = jQuery( '#qsm_question_text_message_id' ).val();
            var message = wp.editor.getContent( 'qsm_question_text_message' );
            jQuery.post(ajaxurl, {text_id: text_id, 'message': message, 'quiz_id': qsmTextTabObject.quiz_id, action: 'qsm_update_text_message'},function (response) {
                var data = jQuery.parseJSON( response );
                if( data.success === true ){
                    //Do nothing
                } else {
                    console.log( data.message );
                }
                $this.siblings('.spinner').removeClass('is-active');
            });
        });
        //On click append on tiny mce
        jQuery( document ).on( 'click', '.qsm-text-allowed-variables button.button' , function(){
            var content = jQuery(this).text();
            if( jQuery('.qsm-question-text-tab .html-active').length > 0 ){
                var $txt = jQuery("#qsm_question_text_message");
                var caretPos = $txt[0].selectionStart;
                var textAreaTxt = $txt.val();
                var txtToAdd = content;
                $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
            }else{
                tinyMCE.activeEditor.execCommand('mceInsertContent', false, content);
            }            
        });
        //Show all the variable list
        jQuery('.qsm-show-all-variable-text').click(function (e) {
            e.preventDefault();
            MicroModal.show('show-all-variable');
        });
        //Hide/show tr based on selection
        $( '.qsm_tab_content select' ).each(function(){
            var name = $(this).attr('name');
            var value = $(this).val();                        
            if( $( '.' + name + '_' + value ).length > 0 ){                
                $( '.' + name + '_' + value ).show();
            }
        });
        $(document).on('change', '.qsm_tab_content select, #quiz_settings_wrapper select', function(){
            var name = $(this).attr('name');
            var value = $(this).val();            
            $( '.qsm_hidden_tr' ).hide();
            if( $( '.' + name + '_' + value ).length > 0 ){                
                $( '.' + name + '_' + value ).show();
            }
        });
		$(document).on('click', '.qsm_tab_content input[name="system"]', function(){
             var name = $(this).attr('name');
             var value = $(this).val(); 
			 $( '.qsm_hidden_tr_gradingsystem' ).hide();
             if(value == 0 || value == 3){				 
                $( '.qsm_hidden_tr_gradingsystem').show();
             }
        });
		$( document ).ready(function() {
			var system_option = $("input[type=radio][name='system']:checked").val();
			 $( '.qsm_hidden_tr_gradingsystem' ).hide();
             if(system_option == 0 || system_option == 3){				 
                $( '.qsm_hidden_tr_gradingsystem').show();
             }
		});
        if( $('.qsm-text-label-wrapper').length > 0 ){
            var element_position = $('.qsm-text-label-wrapper').offset().top;
            $(window).scroll(function() {
                var y_scroll_pos = window.pageYOffset;
                var scroll_pos_test = element_position;
                if(y_scroll_pos > scroll_pos_test) {
                    $('.qsm_text_customize_label').fadeOut('slow');                
                }else{
                    $('.qsm_text_customize_label').fadeIn('slow');
                }
            });
        }        
        $(document).on('click','.qsm_text_customize_label', function(){               
            $('html, body').animate({
                scrollTop: $(".qsm-text-label-wrapper").offset().top - 30
            }, 2000);
        });
        //New template design hide show
        var new_template_result_detail = $('.new_template_result_detail:checked').val();
        if( new_template_result_detail == 1 ){
            $('.new_template_result_detail:checked').parents('tr').next('tr').hide();
        }
        $(document).on('change','.new_template_result_detail', function(){
            if( $(this).val() == 1 ){
                $(this).parents('tr').next('tr').hide();
            }else{
                $(this).parents('tr').next('tr').show();
            }
        });
        $(document).on('click', '#show-all-variable .qsm-text-template-span > .button', function(e){
            e.preventDefault();
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val( jQuery(this).text() ).select();
            document.execCommand("copy");
            $temp.remove();
            var button_width = $(this).width();
            var button_txt = $(this).text();
            $(this).css('width', button_width);
            $(this).text('').html('<span class="popup-copied-des"><span class="dashicons dashicons-yes"></span> Copied!</span>');
            var this_par = $(this);
            setTimeout(function(){
                this_par.css('width', 'auto');
                this_par.text('').text(button_txt);
            }, 1000);
        });
        $(document).on('click', '.qsm-addon-anchor-left .qsm-install-addon a', function(e){
            e.preventDefault();
            var href = $(this).attr('href');
            $('.qsm-addon-anchor-left .qsm-install-addon').find('a').removeClass('active');
            $(this).addClass('active');
            $('.qsm-addon-setting-wrap .qsm-primary-acnhor').hide();
            $( href ).show();
            if( href == '#qsm_add_addons' ){
                $('.qsm-add-addon').css('display', 'inline-block');
            }else{
                $('.qsm-add-addon').css('display', 'none');
            }
        });
        $(document).on('click', '.qsm-addon-anchor-left .qsm-add-addon a', function(e){
            e.preventDefault();
            var href = $(this).attr('href');
            $('.qsm-addon-anchor-left .qsm-add-addon').find('a').removeClass('active');
            $(this).addClass('active');
            $('.qsm-addon-setting-wrap .qsm_popular_addons').hide();
            $( href ).show();
        });
    });    
}(jQuery));