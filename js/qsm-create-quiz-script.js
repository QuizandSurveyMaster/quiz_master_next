var QSMAdminDashboard;
jQuery(function ($) {

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
            if ($(document).find('.qsm-dashboard-error-content').length) {
                jQuery(document).find('.qsm-dashboard-header-pagination > a').hide();
                jQuery('.qsm-dashboard-journy-create-quiz').show();
            }
            QSMAdminDashboard.showDependentAddons();
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
            QSMAdminDashboard.nexPagePreviousPageAfter();
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
            QSMAdminDashboard.nexPagePreviousPageAfter();
        },

        nexPagePreviousPageAfter: function () {
            let $dashboardButton = jQuery('.qsm-dashboard-journy-previous-dashboard');
            let $upgradeButton = jQuery('.qsm-create-quiz-bottom-right-button');
            QSMAdminDashboard.currentPage == 2 || QSMAdminDashboard.currentPage == 3 ? $upgradeButton.show() : $upgradeButton.hide();
            QSMAdminDashboard.currentPage == 1 ? $dashboardButton.show() : $dashboardButton.hide();
        },

        showDependentAddons: function () {
            let $quiz_type = jQuery(document).find('.qsm-dashboard-page-item.qsm-dashboard-page-items-active');
            let $dependency = qsm_admin_new_quiz.quizoptions[$quiz_type.data('id') - 1];

            let $themeItemsParent = $('.qsm-quiz-theme-steps-container');
            let $themeItems = $themeItemsParent.children('.qsm-quiz-steps-card');

            let suggestedThemes = [];
            let remainingThemes = [];
            $themeItems.show();
            $themeItems.each(function () {
                let themeId = $(this).data('id');
                if ($dependency.themes.some(id => id == themeId)) {
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

            let $addonItemsParent = $('.qsm-quiz-addon-steps-grid');
            let $addonItems = $addonItemsParent.children('.qsm-quiz-addon-steps-card');

            let suggestedAddons = [];
            let remainingAddons = [];
            $addonItems.show();
            $addonItems.each(function () {
                let addonId = $(this).data('id');
                if ($dependency.addons.some(id => id == addonId)) {
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

        processPluginRequest: function ($element) {
            let isToggle = $element.hasClass('qsm-dashboard-addon-toggle');
            let isButton = $element.hasClass('qsm-theme-action-btn');
            if (isToggle) {
                var $parent = $element.parents('.qsm-quiz-addon-steps-card');
            }
            if (isButton) {
                var $parent = $element.parents('.qsm-quiz-steps-card');
            }
            if ($parent.hasClass('qsm-quiz-steps-default-theme')) {
                QSMAdminDashboard.processToSelectTheme($parent);
                return;
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
                    QSMAdminDashboard.processToSelectTheme($parent);
                    $parent.addClass('qsm-quiz-theme-activated');
                    return;
                }
            }

            // Disable the toggle to prevent multiple clicks
            if (isToggle || isButton) {
                $element.prop('disabled', true);
            }
            if (installedPlugins.includes(pluginPath)) {
                if (isToggle) {
                    $parent.find('.qsm-dashboard-addon-status').text(qsm_admin_new_quiz.activating);
                } else if (isButton) {
                    $element.text(qsm_admin_new_quiz.activating);
                }
                jQuery(document).trigger('qsm_activate_plugin_button_click_after', [pluginSlug, pluginPath, $parent, $element, installerActivated, isToggle, isButton]);
                if(0 == installerActivated) {
                    QSMAdminDashboard.activatePlugin(pluginSlug, pluginPath, $parent, $element, installerActivated, isToggle, isButton);
                }
            } else if( 1 == installerActivated ) {
                if (isToggle) {
                    $parent.find('.qsm-dashboard-addon-status').text(qsm_admin_new_quiz.installing);
                } else if (isButton) {
                    $element.text(qsm_admin_new_quiz.installing);
                }
                jQuery(document).trigger('qsm_install_plugin_button_click_after', [pluginSlug, pluginPath, $parent, $element, installerActivated, isToggle, isButton]);
            }
        },

        activatePlugin: async function (slug, path, $parent, $element, installerActivated, isToggle, isButton) {
            let response = await QSMAdminDashboard.ajaxRequest('qsm_activate_plugin', {
                nonce: qsm_admin_new_quiz.nonce,
                slug: slug,
                single: 'bundle',
                'plugin_path': path
            });
            if (response.data && (response.data.message.includes("Plugin activated successfully") || response.data.message.includes("Plugin is already activated."))) {
                // Ensure activatedPlugins is an array before pushing
                if (!Array.isArray(qsm_admin_new_quiz.activated)) {
                    qsm_admin_new_quiz.activated = [];
                }
                qsm_admin_new_quiz.activated.push(path);
                if (isButton) {
                    QSMAdminDashboard.afterInstall(slug, path, $parent, $element, installerActivated, isToggle, isButton);
                    QSMAdminDashboard.processToSelectTheme($parent);
                }
                if (isToggle) {
                    $parent.find('.qsm-dashboard-addon-status').text(qsm_admin_new_quiz.activated_text);
                    $element.prop('checked', true).prop('disabled', false);
                }
                $element.prop('disabled', false);
            } else {
                $parent.find('.qsm-dashboard-addon-status').text(response.data.message);
                if (isToggle) { $element.prop('checked', false).prop('disabled', false); }
                if (isButton) { $element.prop('disabled', false); $element.text(qsm_admin_new_quiz.retry); }
            }
        },

        afterInstall: async function (slug, path, $parent, $element, installerActivated, isToggle, isButton) {
            let response = await QSMAdminDashboard.ajaxRequest('qsm_get_activated_themes', {
                nonce: qsm_admin_new_quiz.nonce,
                slug: slug,
            });
            if (response.data) {
                $parent.find('input[name=quiz_theme_id]').prop("checked", true);
                $parent.find('input[name=quiz_theme_id]').val(response.data.id);
            }
        },

        processToSelectTheme: function ($parent) {
            jQuery(document).find('.qsm-quiz-steps-default-theme-active .qsm-theme-action-btn').text(qsm_admin_new_quiz.select);
            jQuery(document).find('.qsm-quiz-steps-default-theme-active .qsm-theme-action-btn').removeAttr('disabled');
            $parent.find('.qsm-theme-action-btn').text(qsm_admin_new_quiz.selected);
            jQuery('.qsm-quiz-steps-card').removeClass('qsm-quiz-steps-default-theme-active');
            $parent.addClass('qsm-quiz-steps-default-theme-active');
            $parent.find('.qsm-theme-action-btn').attr('disabled', 'disabled');
        },

        ajaxRequest: function (action, data) {
            return jQuery.post({ // Added return statement
                url: qsm_admin_new_quiz.ajaxurl,
                data: {
                    action: action,
                    ...data
                }
            }).then(function (response) { // Use .then to return the response
                return response; // Ensure the response is returned
            });
        },
    };
    jQuery(document).ready(function ($) {
        QSMAdminDashboard.init();
        jQuery(document).on('change', '.qsm-dashboard-addon-toggle', function () {
            QSMAdminDashboard.processPluginRequest($(this));
        });

        jQuery(document).on('click', '.qsm-theme-action-btn', function () {
            QSMAdminDashboard.processPluginRequest($(this));
        });
        jQuery(document).on('click', '.qsm-quiz-theme-steps-container .qsm-quiz-steps-image', function (e) {
            e.preventDefault();
            let $parent = $(this).parents('.qsm-quiz-steps-card');
            if ($parent.hasClass('qsm-quiz-theme-activated')) {
                QSMAdminDashboard.processToSelectTheme($parent);
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
            let $parent = $(this).parents('.qsm-dashboard-choose-quiz-type-wrap');
            let selectedType = $(this).data('type');
            $parent.find('.qsm-dashboard-page-item').removeClass('qsm-dashboard-page-items-active');
            $(this).addClass('qsm-dashboard-page-items-active');

            jQuery('.input-group input[name="form_type"]').parents('label').removeClass('qsm-dashboard-button-selected');
            // form_type
            let form_type = 0;
            if (selectedType === 'survey') {
                form_type = 1;
            } else if (selectedType === 'form') {
                form_type = 2;
            }
            0 == form_type ? jQuery('#qsm-quiz-options-system').show() : jQuery('#qsm-quiz-options-system').hide();
            jQuery(`.qsm-create-quiz-more-settings select[name="form_type"]`).val(form_type);

            $('.qsm-dashboard-theme-recommended, .qsm-dashboard-addon-recommended').hide();
            QSMAdminDashboard.showDependentAddons();
        });

        $('.qsm-create-quiz-show-more-settings').on('change', function () {
            let contentDiv = $('.qsm-create-quiz-more-settings');
            let toggleLabel = $('.qsm-create-quiz-toggle-label');

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
