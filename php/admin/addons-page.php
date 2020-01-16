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
    wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
    ?>
    <div class="wrap qsm-addon-setting-wrap">
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
            $xml = qsm_fetch_data_from_xml();
            $cateory_arr = array();
            if (isset($xml->item) && $xml->item) {
                foreach ($xml->item as $key => $value) {
                    $category_addon = trim($value->category);
                    if (!array_key_exists($category_addon, $cateory_arr)) {
                        $cateory_arr[$category_addon] = array();
                    }
                    $cateory_arr[$category_addon][] = $value;
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
        <?php
        if(isset($xml->qsm_bundle)){
        ?>
        <div class="qsm-info-widget">
            <h3><?php echo $xml->qsm_bundle->starter_bundle->name; ?></h3>
            <p><?php echo $xml->qsm_bundle->starter_bundle->desc; ?></p>
            <button class="button button-default">$<?php echo $xml->qsm_bundle->starter_bundle->price; ?></button>
            <a target="_blank" href="<?php echo $xml->qsm_bundle->starter_bundle->link; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin" class="button-primary">Get Now</a>
        </div>
        <div class="qsm-info-widget">
            <h3><?php echo $xml->qsm_bundle->premium_bundle->name; ?></h3>
            <p><?php echo $xml->qsm_bundle->premium_bundle->desc; ?></p>
            <button class="button button-default">$<?php echo $xml->qsm_bundle->premium_bundle->price; ?></button>
            <a target="_blank" href="<?php echo $xml->qsm_bundle->premium_bundle->link; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin" class="button-primary">Get Now</a>
        </div>
        <?php } ?>
       <!--  <div class="remove-ads-adv-link">
            <a target="_blank" href="https://quizandsurveymaster.com/downloads/advertisement-gone/"><span class="dashicons dashicons-no-alt"></span> Remove Ads</a>
        </div> -->
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
    global $mlwQuizMasterNext;
    wp_enqueue_script( 'qsm_admin_script', plugins_url( '../../js/admin.js', __FILE__ ), array( 'jquery' ), $mlwQuizMasterNext->version );
    ?>
    <div class="wrap about-wrap">

        <h1><?php esc_html_e('Get Your Free Addon!', 'quiz-master-next'); ?></h1>

        <div class="about-text"><?php esc_html_e('Wanna get more out of Quiz and Survey Master, but not yet ready to spend the cash? Get one free addon today!', 'quiz-master-next'); ?></div>

        <div class="changelog">

            <div class="row">
                <!-- <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <div class="about-body">
                        <img src="" alt="Improved Custom Fields">
                    </div>
                </div> -->
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <div class="about-body">
                        <h3><?php esc_attr_e('Advertisment Be Gone', 'quiz-master-next'); ?></h3>
                        <p><?php esc_attr_e('The Quiz And Survey Master News widgets from the Quizzes/Surveys page as well as all the green bordered ads at the top of pages will disappear when you activate this addon!', 'quiz-master-next'); ?></p>
                        <p><a href="https://quizandsurveymaster.com/downloads/advertisement-gone/" target="_blank"><?php esc_attr_e('Read more about this addon on our site >', 'quiz-master-next'); ?></a></p>
                    </div>
                </div>
            </div>

            <h2><?php esc_html_e('How to Get Your Free Addon', 'quiz-master-next'); ?></h2>

            <p><?php echo sprintf(__('Getting your addon is dead simple: just subscribe to our newsletter and then you will get the free addon by e-mail. We will not spam you. We usually send out newsletters to talk about new features in Awesome Support, let you know when new or updated addons are being released and provide informative articles that show you how to use Awesome Support to its full potential. <a href="%s" %s>View our privacy policy</a>', 'quiz-master-next'), 'https://quizandsurveymaster.com/privacy-policy/', 'target="_blank"'); ?></p>

            <div id="wpas-mailchimp-signup-form-wrapper">
                <div id="status"></div>
                <form id="sendySignupForm" action="http://sendy.expresstech.io/subscribe" method="POST" accept-charset="utf-8">
                    <table class="form-table">
                        <tr>
                            <td class="row-title"><label for="name">First Name</label> <input type="text" name="name" id="name"/></td>
                            <td class="row-title">
                                <label for="email">Email Address</label>
                                <input type="email" name="email" id="email"/>
                                <div style="display:none;">
                                    <label for="hp">HP</label><br/>
                                    <input type="text" name="hp" id="hp"/>
                                </div>
                                <input type="hidden" name="list" value="4v8zvoyXyTHSS80jeavOpg"/>
                                <input type="hidden" name="subform" value="yes"/>
                                <input type="submit" name="submit" id="submit" value="Subscribe" class="button-secondary"/>                                
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

    </div>
    <?php
}
?>
