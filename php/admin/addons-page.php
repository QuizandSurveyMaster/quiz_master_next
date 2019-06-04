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
                    $cateory_arr = array();
                    foreach ($xml->item as $key => $value) {
                        $category_addon = trim($value->category);
                        if (!array_key_exists($category_addon, $cateory_arr)) {
                            $cateory_arr[$category_addon] = array();
                        }
                        $cateory_arr[$category_addon][] = $value;
                    }
                }
            }
            if ($cateory_arr) {
                foreach ($cateory_arr as $cat_name => $cat_value) {
                    ?>
                    <h3 class="addon_category_name"><?php echo $cat_name; ?></h3>
                    <?php foreach ($cat_value as $value) { ?>
                        <div class="qsm-info-widget">
                            <h3><?php echo $value->name; ?></h3>
                            <p><?php echo $value->desc; ?></p>
                            <button class="button button-default"><?php echo '$' . $value->price; ?></button>
                            <a href="<?php echo $value->link . '&utm_medium=plugin&utm_content=' . $value->slug . '&utm_campaign=qsm_plugin' ?>" target="_blank" class="button button-primary">Get This Addon</a>
                        </div>                      
                    <?php } ?>
                    <?php
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

/**
 * @version 3.2.0
 * Display get a free addon page
 */
function qsm_display_optin_page() {
    ?>
    <div class="wrap about-wrap">

        <h1><?php esc_html_e('Get Your Free Addon!', 'quiz-master-next'); ?></h1>

        <div class="about-text"><?php esc_html_e('Wanna get more out of Awesome Support, but not yet ready to spend the cash? Get one free addon today!', 'quiz-master-next'); ?></div>

        <div class="changelog">

            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <div class="about-body">
                        <img src="" alt="Improved Custom Fields">
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <div class="about-body">
                        <h3><?php esc_attr_e('Custom Status', 'quiz-master-next'); ?></h3>
                        <p><?php esc_attr_e('Need more than the three default statuses?  Maybe you need to tag certain tickets for “Development” or  move them to certain support levels such as “Level 1” and “Level 2”.  With the Custom Status addon you can create the perfect set of ticket statuses for your organization.', 'quiz-master-next'); ?></p>
                        <p><a href="https://getawesomesupport.com/addons/custom-status/?utm_source=plugin&utm_medium=optin&utm_campaign=activation" target="_blank"><?php esc_attr_e('Read more about this addon on our site >', 'quiz-master-next'); ?></a></p>
                    </div>
                </div>
            </div>

            <h2><?php esc_html_e('How to Get Your Free Addon', 'quiz-master-next'); ?></h2>

            <p><?php echo sprintf(__('Getting your addon is dead simple: just subscribe to our newsletter and then you will get the free addon by e-mail. We will not spam you. We usually send out newsletters to talk about new features in Awesome Support, let you know when new or updated addons are being released and provide informative articles that show you how to use Awesome Support to its full potential. <a href="%s" %s>View our privacy policy</a>', 'quiz-master-next'), 'https://getawesomesupport.com/legal/privacy-policy/', 'target="_blank"'); ?></p>

            <div id="wpas-mailchimp-signup-form-wrapper">
                <form action="<?php echo add_query_arg(array('post_type' => 'ticket', 'page' => 'wpas-optin'), admin_url('edit.php')); ?>" method="post" id="wpas-mailchimp-signup-form" name="wpas-mailchimp-signup-form">
                    <table class="form-table">
                        <tr>
                            <td class="row-title"><label for="mce-FNAME">First Name</label> <input type="text" value="" name="FNAME" class="medium-text" id="mce-FNAME"></td>
                            <td class="row-title">
                                <label for="mce-EMAIL">Email Address</label>
                                <input type="email" value="" name="EMAIL" class="regular-text required email" id="mce-EMAIL">
                                <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button-secondary">
                            </td>
                        </tr>
                    </table>
                    <div style="position: absolute; left: -5000px;" aria-hidden="true">
                        <input type="text" name="b_46ccfe899f0d2648a8b74454a_ad9db57f69" tabindex="-1" value="">
                    </div>
                    <div id="mce-responses" class="clear">
                        <div class="wpas-alert-danger" id="wpas-mailchimp-signup-result-error" style="display:none;">Error</div>
                        <div class="wpas-alert-success" id="wpas-mailchimp-signup-result-success" style="display:none; color: green;"><?php esc_html_e('Thanks for your subscription! You will need to confirm the double opt-in e-mail that you will receive in a coupe of minutes. After you confirmed it, you will receive the free addon directly in your inbox.', 'quiz-master-next'); ?></div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <?php
}
?>
