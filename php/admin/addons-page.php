<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Creates the add on page that is displayed in the add on settings page
 *
 * @return void
 * @since 4.4.0
 */
function qmn_addons_page() {
    if (!current_user_can('moderate_comments')) {
        return;
    }

    global $mlwQuizMasterNext;
    $active_tab = strtolower(str_replace(" ", "-", isset($_GET['tab']) ? $_GET['tab'] : __('Featured Addons', 'quiz-master-next')));
    $tab_array = $mlwQuizMasterNext->pluginHelper->get_addon_tabs();
    ?>
    <div class="wrap">
        <h2>Quiz And Survey Master Addon Settings</h2>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ($tab_array as $tab) {
                $active_class = '';
                if ($active_tab == $tab['slug']) {
                    $active_class = 'nav-tab-active';
                }
                echo "<a href=\"?page=qmn_addons&tab={$tab['slug']}\" class=\"nav-tab $active_class\">{$tab['title']}</a>";
            }
            ?>
        </h2>
        <div>
            <?php
            foreach ($tab_array as $tab) {
                if ($active_tab == $tab['slug']) {
                    call_user_func($tab['function']);
                }
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Displays the contents of the featured add ons page.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_generate_featured_addons() {
    wp_enqueue_style('qsm_addons_style', plugins_url('../../css/qsm-admin.css', __FILE__));
    ?>
    <p><?php esc_html_e('These addons extend the functionality of Quiz And Survey Master', 'quiz-master-next'); ?></p>
    <div class="qsm-quiz-page-addon">
        <a href="http://quizandsurveymaster.com/addons/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin" target="_blank" class="button-primary"><?php _e('Browse All Addons', 'quiz-master-next'); ?></a>
        <div class="qsm-addons">
            <?php
            $file = esc_url('http://localhost/work/et/wp-dev/addon_list.xml');
            $response = wp_remote_post($file, array('sslverify' => false));
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo "<p>" . __('Something went wrong', BLOGDESIGNERPRO_TEXTDOMAIN) . " : $error_message" . "</p>";
            } else {
                $body = wp_remote_retrieve_body($response);
                $xml = simplexml_load_string($body);
                if ($xml->item) {
                    foreach ($xml->item as $key => $value) {
                        ?>
                        <div class="qsm-info-widget">
                            <h3><?php echo $value->name; ?></h3>
                            <p><?php echo $value->desc; ?></p>
                            <a href="<?php echo $value->link . '&utm_medium=plugin&utm_content=' . $value->slug . '&utm_campaign=qsm_plugin' ?>" target="_blank" class="button">Get This Addon</a>
                        </div>
                        <?php
                    }
                }
            }
            ?>		
        </div>
        <a href="http://quizandsurveymaster.com/addons/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-bottom&utm_campaign=qsm_plugin" target="_blank" class="button-primary"><?php _e('Browse All Addons', 'quiz-master-next'); ?></a>
    </div>
    <div class="qsm-news-ads">
        <h3 class="qsm-news-ads-title">QSM Bundle</h3>
        <div class="qsm-info-widget">
            <h3>Starter Bundle</h3>
            <p>Join our mailing list to learn about our newest features, receive email-only promotions, receive tips and guides, and more!</p>
            <a target="_blank" href="http://quizandsurveymaster.com/subscribe-to-our-newsletter/?utm_source=qsm-quizzes-page&amp;utm_medium=plugin&amp;utm_campaign=qsm_plugin&amp;utm_content=subscribe-to-newsletter" class="button-primary">Get Now</a>
        </div>
        <div class="qsm-info-widget">
            <h3>Premium Bundle</h3>
            <p>Join our mailing list to learn about our newest features, receive email-only promotions, receive tips and guides, and more!</p>
            <a target="_blank" href="http://quizandsurveymaster.com/subscribe-to-our-newsletter/?utm_source=qsm-quizzes-page&amp;utm_medium=plugin&amp;utm_campaign=qsm_plugin&amp;utm_content=subscribe-to-newsletter" class="button-primary">Get Now</a>
        </div>
        <div class="remove-ads-adv-link">
            <a target="_blank" href="https://quizandsurveymaster.com/downloads/advertisement-gone/"><span class="dashicons dashicons-no-alt"></span> Remove Ads</a>
        </div>
    </div>
    <?php
}

/**
 * This function registers the feature add ons tab.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_featured_addons_tab() {
    global $mlwQuizMasterNext;
    $mlwQuizMasterNext->pluginHelper->register_addon_settings_tab(__('Featured Addons', 'quiz-master-next'), 'qsm_generate_featured_addons');
}

add_action('plugins_loaded', 'qsm_featured_addons_tab');
?>
