/**
 * Create Quiz page.
 *
 * Two views on one page: "custom" (name, type, grading, theme, additional
 * settings) and "template" (the QSM Installer's demo browser). The dashboard
 * links to each; the view is kept in the URL (?view=) so Back / reload land on it.
 *
 * QSMAdminDashboard keeps the methods the QSM Installer calls
 * (processToSelectTheme, afterInstall, ajaxRequest).
 */
var QSMAdminDashboard;
jQuery(function ($) {

    QSMAdminDashboard = {
        view: 'custom',
        themesExpanded: false,

        init: function () {
            let initial = (qsm_admin_new_quiz && qsm_admin_new_quiz.view) ? qsm_admin_new_quiz.view : 'custom';
            QSMAdminDashboard.showView(initial, false);
            QSMAdminDashboard.applyQuizType();
            if (jQuery('.qsm-quiz-steps-default-theme-active').length === 0) {
                jQuery('.qsm-quiz-theme-steps-container').children('.qsm-quiz-steps-card').first().addClass('qsm-quiz-steps-default-theme-active');
            }
        },

        showView: function (view, push) {
            if (!jQuery('[data-qsm-cq-view="' + view + '"]').length) {
                view = 'custom';
            }
            QSMAdminDashboard.view = view;
            jQuery('[data-qsm-cq-view]').prop('hidden', true).filter('[data-qsm-cq-view="' + view + '"]').prop('hidden', false);
            jQuery('[data-qsm-cq-title]').prop('hidden', true).filter('[data-qsm-cq-title="' + view + '"]').prop('hidden', false);
            jQuery('[data-qsm-cq-only]').each(function () {
                jQuery(this).prop('hidden', jQuery(this).attr('data-qsm-cq-only') !== view);
            });
            jQuery('.qsm-cq').attr('data-qsm-cq-current', view);
            if (push && window.history && window.history.pushState) {
                let url = new URL(window.location.href);
                url.searchParams.set('view', view);
                window.history.pushState({ qsmCqView: view }, '', url.toString());
            }
            if ('custom' === view) {
                jQuery('#qsm-cq-quiz-name').trigger('focus');
            }
            jQuery(document).trigger('qsm_create_quiz_view_change', [view]);
            window.scrollTo(0, 0);
        },

        // Selected quiz type: sets form_type, shows grading only for graded types, reorders the themes.
        applyQuizType: function () {
            let id = parseInt(jQuery('input[name="qsm_quiz_type"]:checked').val(), 10);
            let box = QSMAdminDashboard.getQuizOption(id);
            let formType = 0;
            if (box && 'survey' === box.type) {
                formType = 1;
            } else if (box && 'form' === box.type) {
                formType = 2;
            }
            jQuery('#new-quiz-form input[name="form_type"]').val(formType);
            jQuery('.qsm-cq-grading').prop('hidden', 0 !== formType);
            jQuery('.qsm-dashboard-theme-recommended').hide();
            QSMAdminDashboard.showDependentAddons(box);
        },

        getQuizOption: function (id) {
            let options = Array.isArray(qsm_admin_new_quiz.quizoptions) ? qsm_admin_new_quiz.quizoptions : [];
            for (let i = 0; i < options.length; i++) {
                if (parseInt(options[i].id, 10) === id) {
                    return options[i];
                }
            }
            return options.length ? options[0] : null;
        },

        // Recommended themes first, then activated ones, then the rest; the default theme stays first.
        showDependentAddons: function (box) {
            let recommended = (box && Array.isArray(box.themes)) ? box.themes : [];
            let $parent = $('.qsm-quiz-theme-steps-container');
            let $items = $parent.children('.qsm-quiz-steps-card');
            if (!$items.length) {
                return;
            }
            let suggested = [], activated = [], rest = [];
            $items.show();
            $items.each(function () {
                let themeId = $(this).data('id');
                if (recommended.some(id => id == themeId)) {
                    suggested.push(this);
                    $(this).find('.qsm-dashboard-theme-recommended').show();
                } else if ($(this).hasClass('qsm-quiz-theme-activated')) {
                    activated.push(this);
                } else {
                    rest.push(this);
                }
            });
            $parent.append(suggested).append(activated).append(rest);
            $parent.prepend($parent.children('.qsm-quiz-steps-default-theme'));

            // Until "See more themes" is clicked show the first four, plus every activated theme.
            // Track this with a flag: the view can be hidden on load, so :visible is not reliable.
            if (!QSMAdminDashboard.themesExpanded) {
                $parent.children(':gt(3)').not('.qsm-quiz-theme-activated').hide();
            }
            // Offer "See more" only while some themes are still hidden.
            let hasHidden = $parent.children('.qsm-quiz-steps-card').filter(function () {
                return 'none' === this.style.display;
            }).length > 0;
            jQuery('.qsm-dashboard-see-more-types-wrap').toggle(!QSMAdminDashboard.themesExpanded && hasHidden);
        },

        processPluginRequest: function ($element) {
            let $parent = $element.parents('.qsm-quiz-steps-card');
            if (!$parent.length) {
                return;
            }
            if ($parent.hasClass('qsm-quiz-steps-default-theme')) {
                QSMAdminDashboard.processToSelectTheme($parent);
                return;
            }
            let pluginPath = $parent.attr('data-path');
            let pluginSlug = $parent.data('slug');
            let installerActivated = qsm_admin_new_quiz.installer_activated;
            let activatedPlugins = Array.isArray(qsm_admin_new_quiz.activated) ? qsm_admin_new_quiz.activated : Object.values(qsm_admin_new_quiz.activated || {});
            let installedPlugins = Array.isArray(qsm_admin_new_quiz.installed) ? qsm_admin_new_quiz.installed : Object.values(qsm_admin_new_quiz.installed || {});

            if (activatedPlugins.includes(pluginPath)) {
                QSMAdminDashboard.processToSelectTheme($parent);
                $parent.addClass('qsm-quiz-theme-activated');
                return;
            }
            $element.prop('disabled', true);
            if (installedPlugins.includes(pluginPath)) {
                $element.text(qsm_admin_new_quiz.activating);
                jQuery(document).trigger('qsm_activate_plugin_button_click_after', [pluginSlug, pluginPath, $parent, $element, installerActivated, false, true]);
                if (0 == installerActivated) {
                    QSMAdminDashboard.activatePlugin(pluginSlug, pluginPath, $parent, $element, installerActivated, false, true);
                }
            } else if (1 == installerActivated) {
                $element.text(qsm_admin_new_quiz.installing);
                jQuery(document).trigger('qsm_install_plugin_button_click_after', [pluginSlug, pluginPath, $parent, $element, installerActivated, false, true]);
            }
        },

        activatePlugin: async function (slug, path, $parent, $element, installerActivated, isToggle, isButton) {
            let response = await QSMAdminDashboard.ajaxRequest('qsm_activate_plugin', {
                nonce: qsm_admin_new_quiz.nonce,
                slug: slug,
                single: 'bundle',
                'plugin_path': path
            });
            if (response.data && response.data.message && (response.data.message.includes('Plugin activated successfully') || response.data.message.includes('Plugin is already activated.'))) {
                if (!Array.isArray(qsm_admin_new_quiz.activated)) {
                    qsm_admin_new_quiz.activated = Object.values(qsm_admin_new_quiz.activated || {});
                }
                qsm_admin_new_quiz.activated.push(path);
                $parent.addClass('qsm-quiz-theme-activated');
                QSMAdminDashboard.afterInstall(slug, path, $parent, $element, installerActivated, isToggle, isButton);
                QSMAdminDashboard.processToSelectTheme($parent);
            } else {
                $parent.find('.qsm-dashboard-addon-status').text(response.data && response.data.message ? response.data.message : '');
                $element.prop('disabled', false).text(qsm_admin_new_quiz.retry);
            }
        },

        afterInstall: async function (slug, path, $parent, $element, installerActivated, isToggle, isButton) {
            let response = await QSMAdminDashboard.ajaxRequest('qsm_get_activated_themes', {
                nonce: qsm_admin_new_quiz.nonce,
                slug: slug,
            });
            if (response.data) {
                $parent.find('input[name=quiz_theme_id]').val(response.data.id).prop('checked', true);
            }
        },

        processToSelectTheme: function ($parent) {
            let $current = jQuery('.qsm-quiz-steps-default-theme-active');
            $current.find('.qsm-theme-action-btn').text(qsm_admin_new_quiz.select).removeAttr('disabled');
            jQuery('.qsm-quiz-steps-card').removeClass('qsm-quiz-steps-default-theme-active');
            $parent.addClass('qsm-quiz-steps-default-theme-active');
            $parent.find('.qsm-theme-action-btn').text(qsm_admin_new_quiz.selected).attr('disabled', 'disabled');
            $parent.find('input[name=quiz_theme_id]').prop('checked', true);
            if ($parent.hasClass('qsm-quiz-steps-default-theme')) {
                jQuery('input[name=quiz_theme_id]').prop('checked', false);
            }
        },

        validateName: function () {
            let $name = jQuery('#qsm-cq-quiz-name');
            let empty = '' === String($name.val() || '').trim();
            $name.toggleClass('is-invalid', empty).attr('aria-invalid', empty ? 'true' : null);
            jQuery('#qsm-cq-quiz-name-msg').prop('hidden', !empty);
            return !empty;
        },

        ajaxRequest: function (action, data) {
            return jQuery.post({
                url: qsm_admin_new_quiz.ajaxurl,
                data: {
                    action: action,
                    ...data
                }
            });
        },
    };

    QSMAdminDashboard.init();

    $(document).on('click', '[data-qsm-cq-go]', function (e) {
        e.preventDefault();
        QSMAdminDashboard.showView($(this).attr('data-qsm-cq-go'), true);
    });

    window.addEventListener('popstate', function () {
        let view = new URL(window.location.href).searchParams.get('view') || 'custom';
        QSMAdminDashboard.showView(view, false);
    });

    $(document).on('change', 'input[name="qsm_quiz_type"]', function () {
        QSMAdminDashboard.applyQuizType();
    });

    $(document).on('input', '#qsm-cq-quiz-name', function () {
        if ($(this).hasClass('is-invalid')) {
            QSMAdminDashboard.validateName();
        }
    });

    // qsm-admin.js submits the form from this button; this only shows the inline message.
    $(document).on('click', '#create-quiz-button', function () {
        if (!QSMAdminDashboard.validateName()) {
            jQuery('#qsm-cq-quiz-name').trigger('focus');
        }
    });

    $(document).on('keydown', '#qsm-cq-quiz-name', function (e) {
        if ('Enter' === e.key) {
            e.preventDefault();
            jQuery('#create-quiz-button').trigger('click');
        }
    });

    $(document).on('change', '#qsm-cq-more-toggle', function () {
        $(this).closest('.qsm-cq-more-card').toggleClass('is-open', $(this).is(':checked'));
    });

    $(document).on('click', '.qsm-theme-action-btn', function (e) {
        e.preventDefault();
        QSMAdminDashboard.processPluginRequest($(this));
    });

    $(document).on('click', '.qsm-quiz-theme-steps-container .qsm-quiz-steps-image', function (e) {
        e.preventDefault();
        let $parent = $(this).parents('.qsm-quiz-steps-card');
        if ($parent.hasClass('qsm-quiz-theme-activated')) {
            QSMAdminDashboard.processToSelectTheme($parent);
        }
    });

    $(document).on('click', '.qsm-dashboard-see-more-themes', function () {
        QSMAdminDashboard.themesExpanded = true;
        $('.qsm-quiz-theme-steps-container').children('.qsm-quiz-steps-card').show();
        $('.qsm-dashboard-see-more-types-wrap').hide();
    });
});
