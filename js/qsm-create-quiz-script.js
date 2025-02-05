var QSMAdminDashboard;
jQuery(function ($) {

    console.log('load')
    // Install Plugin - Working
    QSMAdminDashboard = {
        currentPage: 1,
        totalPages: 0,
        init: function () {
            this.totalPages = jQuery('.qsm-dashboard-container-pages').length;
            this.showPage(this.currentPage, true);

            // Hide Form Type on load
            jQuery('.input-group#qsm-quiz-options-form_type').hide();

            // Find all unique radio groups by name within the parent
            const parentElement = jQuery("#new-quiz-form");
            const uniqueRadioGroups = parentElement.find('input[type="radio"]').map(function () {
                return this.name;
            }).get().filter((name, index, self) => self.indexOf(name) === index); // Remove duplicates
            // Log the selected value for each radio group
            uniqueRadioGroups.forEach(name => {
                const selectedRadio = parentElement.find(`input[type="radio"][name="${name}"]:checked`);
                selectedRadio.parents('label').addClass('qsm-dashboard-button-selected')
            });
        },

        showPage: function (pageNo, onload = false) {
            const $pages = jQuery('.qsm-dashboard-container-pages');
            $pages.hide();
            $pages.filter(`[data-page-no="${pageNo}"]`).show();
            if (pageNo === 1) {
                jQuery('.qsm-dashboard-journy-previous-step, .qsm-dashboard-journy-next-step').hide();
            } else {
                jQuery('.qsm-dashboard-journy-previous-step').show();
            }
            if (pageNo === this.totalPages) {
                jQuery('.qsm-dashboard-journy-next-step, .qsm-dashboard-journy-next-step-proceed').hide();
                jQuery('.qsm-dashboard-journy-create-quiz').show();
            } else {
                jQuery('.qsm-dashboard-journy-next-step-proceed').show();
                jQuery('.qsm-dashboard-journy-create-quiz').hide();
            }
        },

        nextPage: function () {
            if (QSMAdminDashboard.currentPage < QSMAdminDashboard.totalPages) {
                QSMAdminDashboard.currentPage++;
                QSMAdminDashboard.showPage(QSMAdminDashboard.currentPage);
            }
            QSMAdminDashboard.showDependentAddons();
            // Always at last
            if (jQuery('.qsm-quiz-steps-default-theme-active').length === 0) {
                jQuery('.qsm-quiz-theme-steps-container').children('.qsm-quiz-steps-card').first().addClass('qsm-quiz-steps-default-theme-active'); // Add the class
            }
        },

        previousPage: function () {
            if (QSMAdminDashboard.currentPage > 1) {
                QSMAdminDashboard.currentPage--;
                QSMAdminDashboard.showPage(QSMAdminDashboard.currentPage);
            }
        },

        showDependentAddons: function () {
            let $quiz_type = jQuery(document).find('.qsm-dashboard-page-item.qsm-dashboard-page-items-active');
            var $dependency = qsm_admin_new_quiz.quizoptions[$quiz_type.data('id')];

            var $themeItemsParent = $('.qsm-quiz-theme-steps-container');
            var $themeItems = $themeItemsParent.children('.qsm-quiz-steps-card');

            var suggestedThemes = [];
            var remainingThemes = [];
            $themeItems.show();
            $themeItems.each(function () {
                var themeId = $(this).data('id');
                if ($dependency.themes.includes(themeId)) {
                    suggestedThemes.push($(this));
                    $(this).find('.qsm-dashboard-theme-recommended').show();
                } else {
                    remainingThemes.push($(this));
                }
            });

            $themeItemsParent.empty();
            suggestedThemes.forEach(function ($item) {
                $themeItemsParent.append($item);
            });
            remainingThemes.forEach(function ($item) {
                $themeItemsParent.append($item);
            });
            $themeItemsParent.prepend(jQuery(document).find('.qsm-quiz-steps-default-theme'));

            // show only first four
            if (jQuery('.qsm-dashboard-see-more-themes').is(":visible")) {
                $themeItemsParent.children(":gt(3)").hide();
            }

            var $addonItemsParent = $('.qsm-quiz-addon-steps-grid');
            var $addonItems = $addonItemsParent.children('.qsm-quiz-addon-steps-card');

            var suggestedAddons = [];
            var remainingAddons = [];
            $addonItems.show();
            $addonItems.each(function () {
                var addonId = $(this).data('id');
                if ($dependency.addons.includes(addonId)) {
                    suggestedAddons.push($(this));
                    $(this).find('.qsm-dashboard-addon-recommended').show();
                } else {
                    remainingAddons.push($(this));
                }
            });

            $addonItemsParent.empty();
            suggestedAddons.forEach(function ($item) {
                $addonItemsParent.append($item);
            });
            remainingAddons.forEach(function ($item) {
                $addonItemsParent.append($item);
            });

            // show only first four
            if (jQuery('.qsm-dashboard-see-more-addons').is(":visible")) {
                $addonItemsParent.children(":gt(3)").hide();
            }
        },

        installPlugin: function (slug, path, $parent, $element, installerActivated, isToggle, isButton) {
            $.ajax({
                type: "POST",
                url: qsm_admin_new_quiz.ajaxurl,
                data: {
                    action: 'qsm_handle_ajax_install',
                    nonce: qsm_admin_new_quiz.nonce,
                    slug: slug,
                },
                success: function (response) {
                    response = QSMAdminDashboard.parseResponse(response);

                    if (response.data && response.data.message.includes("Plugin installed successfully")) {
                        // Ensure installedPlugins is an array before pushing
                        if (!Array.isArray(qsm_admin_new_quiz.installed)) {
                            qsm_admin_new_quiz.installed = [];
                        }
                        qsm_admin_new_quiz.installed.push(path);
                        if (isButton) { QSMAdminDashboard.afterInstall(slug, path, $parent, $element, installerActivated, isToggle, isButton); }
                        jQuery('.qsm-quiz-steps-card').removeClass('qsm-quiz-steps-default-theme-active');
                        $parent.addClass('qsm-quiz-steps-default-theme-active');
                        if ($element.hasClass('qsm-theme-action-btn')) {
                            $element.remove();
                        }
                    } else {
                        $parent.find('.qsm-dashboard-addon-status').text(qsm_admin_new_quiz.retry);
                        if (isToggle) { $element.prop('checked', false).prop('disabled', false); }
                        if (isButton) { $element.prop('disabled', false); }
                    }
                }
            });
        },

        activatePlugin: function (slug, path, $parent, $element, installerActivated, isToggle, isButton) {
            let action = installerActivated == 1 ? 'qsm_handle_ajax_activate' : 'qsm_activate_plugin';

            $.ajax({
                type: "POST",
                url: qsm_admin_new_quiz.ajaxurl,
                data: {
                    action: action,
                    nonce: qsm_admin_new_quiz.nonce,
                    slug: slug,
                    single: 'bundle',
                    'plugin_path': path
                },
                success: function (response) {
                    response = QSMAdminDashboard.parseResponse(response);
                    if (response.data && (response.data.message.includes("Plugin activated successfully") || response.data.message.includes("Plugin is already activated."))) {
                        // Ensure activatedPlugins is an array before pushing
                        if (!Array.isArray(qsm_admin_new_quiz.activated)) {
                            qsm_admin_new_quiz.activated = [];
                        }
                        qsm_admin_new_quiz.activated.push(path);
                        if (isButton) {
                            $parent.addClass('qsm-quiz-theme-activated');
                            QSMAdminDashboard.afterInstall(slug, path, $parent, $element, installerActivated, isToggle, isButton);
                        }
                        $parent.find('.qsm-dashboard-addon-status').text(qsm_admin_new_quiz.available);
                        if (isToggle) {
                            $element.prop('checked', true).prop('disabled', false);
                        }
                        $element.prop('disabled', false);
                        if ($element.hasClass('qsm-theme-action-btn')) {
                            $element.remove();
                        }
                    } else {
                        $parent.find('.qsm-dashboard-addon-status').text(response.data.message);
                        if (isToggle) { $element.prop('checked', false).prop('disabled', false); }
                        if (isButton) { $element.prop('disabled', false); }
                    }
                }
            });
        },

        afterInstall: function (slug, path, $parent, $element, installerActivated, isToggle, isButton) {
            let action = 'qsm_get_activated_themes';

            $.ajax({
                type: "POST",
                url: qsm_admin_new_quiz.ajaxurl,
                data: {
                    action: action,
                    nonce: qsm_admin_new_quiz.nonce,
                    slug: slug,
                },
                success: function (response) {
                    console.log(response);
                    response = QSMAdminDashboard.parseResponse(response);
                    if (response.data) {
                        console.log(response);
                        $parent.find('input[name=quiz_theme_id]').prop("checked", true);
                        $parent.find('input[name=quiz_theme_id]').val(response.data.id);
                    }
                }
            });
        },

        parseResponse: function (response) {
            if (typeof response !== 'object') {
                const jsonRegex = /\{.*\}/;
                let match = response.match(jsonRegex);
                if (match) {
                    response = JSON.parse(match[0]);
                } else {
                    response = { success: false };
                }
            }
            return response;
        },
    };
    jQuery(document).ready(function ($) {
        QSMAdminDashboard.init();
        console.log('ready')
        jQuery(document).on('click change', '.qsm-theme-action-btn, .qsm-dashboard-addon-toggle', function () {
            let $element = $(this);
            let isToggle = $element.hasClass('qsm-dashboard-addon-toggle');
            let isButton = $element.hasClass('qsm-theme-action-btn');
            if (isToggle) {
                var $parent = $element.parents('.qsm-quiz-addon-steps-card');
            }
            if (isButton) {
                var $parent = $element.parents('.qsm-quiz-steps-card');
            }

            let pluginPath = $parent.attr('data-path');
            let pluginSlug = $parent.data('slug');
            let activatedPlugins = qsm_admin_new_quiz.activated;
            let installedPlugins = qsm_admin_new_quiz.installed || [];
            let installerActivated = qsm_admin_new_quiz.installer_activated;

            // Ensure activatedPlugins is an array
            if (!Array.isArray(activatedPlugins)) {
                activatedPlugins = Object.values(activatedPlugins);
            }

            // Ensure installedPlugins is an array
            if (!Array.isArray(installedPlugins)) {
                installedPlugins = Object.values(installedPlugins);
            }

            // If plugin is already activated, do nothing and keep toggle checked
            if (activatedPlugins.includes(pluginPath)) {
                if (isToggle) {
                    $element.prop('checked', true);
                    return;
                }
                if (isButton) {
                    $parent.addClass('qsm-quiz-theme-activated');
                    return;
                }
            }
            $parent.find('.qsm-dashboard-addon-status').text(qsm_admin_new_quiz.process).prepend('<span class="dashicons dashicons-update"></span>');
            // Disable the toggle to prevent multiple clicks
            if (isToggle || isButton) {
                $element.prop('disabled', true);
            }

            if (installedPlugins.includes(pluginPath)) {
                QSMAdminDashboard.activatePlugin(pluginSlug, pluginPath, $parent, $element, installerActivated, isToggle, isButton);
            } else {
                QSMAdminDashboard.installPlugin(pluginSlug, pluginPath, $parent, $element, installerActivated, isToggle, isButton);
            }
        });
        jQuery(document).on('click', '.qsm-quiz-theme-steps-container .qsm-quiz-steps-image', function (e) {
            e.preventDefault();
            let $parent = $(this).parents('.qsm-quiz-steps-card');
            if($parent.hasClass('qsm-quiz-theme-activated')) {
                jQuery('.qsm-quiz-steps-card').removeClass('qsm-quiz-steps-default-theme-active');
                $parent.addClass('qsm-quiz-steps-default-theme-active');
                $parent.find('input[name=quiz_theme_id]').prop("checked", true);
            }
        });

        $(document).on('click', '.qsm-dashboard-journy-previous-step', function (e) {
            QSMAdminDashboard.previousPage();
        });

        $(document).on('click', '.qsm-dashboard-journy-next-step, .qsm-dashboard-journy-next-step-proceed', function (e) {
            QSMAdminDashboard.nextPage();
        });

        $(document).on('click', '.qsm-dashboard-quiz-form .input-group input[type="radio"]', function (e) {
            jQuery(this).parents('.input-group').find('fieldset label').removeClass('qsm-dashboard-button-selected');
            jQuery(this).parents('label').addClass('qsm-dashboard-button-selected');
        });

        $(document).on('click', '.qsm-dashboard-quiz-form .input-group input[type="checkbox"]', function (e) {
            jQuery(this).parents('.input-group').find('fieldset label').removeClass('qsm-dashboard-button-selected');
            if (jQuery(this).prop('checked')) {
                jQuery(this).parents('label').addClass('qsm-dashboard-button-selected');
            }
        });

        $(document).on('click', '.qsm-dashboard-page-item', function (e) {
            var $parent = $(this).parents('.qsm-dashboard-choose-quiz-type-wrap');
            var selectedType = $(this).data('type');
            $parent.find('.qsm-dashboard-page-item').removeClass('qsm-dashboard-page-items-active');
            $(this).addClass('qsm-dashboard-page-items-active');

            jQuery('.input-group input[name="form_type"]').parents('label').removeClass('qsm-dashboard-button-selected');
            // form_type
            if (selectedType === 'survey') {
                jQuery(`#new-quiz-form input[name="form_type"][value="1"]`).parents('label')[0].click();
            } else if (selectedType === 'form') {
                jQuery(`#new-quiz-form input[name="form_type"][value="2"]`).parents('label')[0].click();
            } else {
                jQuery(`#new-quiz-form input[name="form_type"][value="0"]`).parents('label')[0].click();
            }

            // Hide recommendetions
            $('.qsm-dashboard-theme-recommended, .qsm-dashboard-addon-recommended').hide();

            QSMAdminDashboard.showDependentAddons();
        });

        $('.qsm-create-quiz-show-more-settings').on('change', function () {
            var contentDiv = $('.qsm-create-quiz-more-settings');
            var toggleLabel = $('.qsm-create-quiz-toggle-label');

            if ($(this).is(':checked')) {
                contentDiv.slideDown(); // Show the content
                toggleLabel.text(qsm_admin_new_quiz.less_settings); // Update label text
            } else {
                contentDiv.slideUp(); // Hide the content
                toggleLabel.text(qsm_admin_new_quiz.more_settings); // Update label text
            }

            // Smooth scroll to the content
            $('html, body').animate({
                scrollTop: contentDiv.offset().top
            }, 500); // 500ms duration for the scroll
        });

        $(document).on('click', '.qsm-dashboard-see-more-themes', function (e) {
            $('.qsm-quiz-theme-steps-container').children('.qsm-quiz-steps-card').show();
            $(this).hide();
        });

        $(document).on('click', '.qsm-dashboard-see-more-addons', function (e) {
            $(this).hide();
            $('.qsm-quiz-addon-steps-grid').children('.qsm-quiz-addon-steps-card').show();
        });
    });

});
