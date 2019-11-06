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
            $.post(ajaxurl, {name: name, email: email, action: action},
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
        /**/
        jQuery('.buttonset').buttonset();
    });
}(jQuery));
