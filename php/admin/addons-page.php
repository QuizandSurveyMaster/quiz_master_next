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
    wp_localize_script( 'qsm_admin_script', 'qsmAdminObject', array( 'saveNonce' => wp_create_nonce('ajax-nonce-sendy-save') ) );
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

            <p><?php echo sprintf(__('Getting your addon is dead simple: just subscribe to our newsletter and then you will get the free addon by e-mail. We will not spam you. We usually send out newsletters to talk about new features in <b>Quiz and Survey Master</b>, let you know when new or updated addons are being released and provide informative articles that show you how to use <b>Quiz and Survey Master</b> to its full potential. <a href="%s" %s>View our privacy policy</a>', 'quiz-master-next'), 'https://quizandsurveymaster.com/privacy-policy/', 'target="_blank"'); ?></p>

            <div id="wpas-mailchimp-signup-form-wrapper">
                <div id="status"></div>
                <!-- Begin Sendinblue Form -->
<!-- START - We recommend to place the below code in head tag of your website html  -->
<style>
  @font-face {
    font-display: block;
    font-family: Roboto;
    src: url(https://assets.sendinblue.com/font/Roboto/Latin/normal/normal/7529907e9eaf8ebb5220c5f9850e3811.woff2) format("woff2"), url(https://assets.sendinblue.com/font/Roboto/Latin/normal/normal/25c678feafdc175a70922a116c9be3e7.woff) format("woff")
  }

  @font-face {
    font-display: fallback;
    font-family: Roboto;
    font-weight: 600;
    src: url(https://assets.sendinblue.com/font/Roboto/Latin/medium/normal/6e9caeeafb1f3491be3e32744bc30440.woff2) format("woff2"), url(https://assets.sendinblue.com/font/Roboto/Latin/medium/normal/71501f0d8d5aa95960f6475d5487d4c2.woff) format("woff")
  }

  @font-face {
    font-display: fallback;
    font-family: Roboto;
    font-weight: 700;
    src: url(https://assets.sendinblue.com/font/Roboto/Latin/bold/normal/3ef7cf158f310cf752d5ad08cd0e7e60.woff2) format("woff2"), url(https://assets.sendinblue.com/font/Roboto/Latin/bold/normal/ece3a1d82f18b60bcce0211725c476aa.woff) format("woff")
  }

  #sib-container input:-ms-input-placeholder {
    text-align: left;
    font-family: "Helvetica", sans-serif;
    color: #c0ccda;
    border-width: px;
  }

  #sib-container input::placeholder {
    text-align: left;
    font-family: "Helvetica", sans-serif;
    color: #c0ccda;
    border-width: px;
  }
</style>
<link rel="stylesheet" href="https://assets.sendinblue.com/component/form/2ef8d8058c0694a305b0.css">
<link rel="stylesheet" href="https://assets.sendinblue.com/component/clickable/b056d6397f4ba3108595.css">
<link rel="stylesheet" href="https://assets.sendinblue.com/component/progress-indicator/f86d65a4a9331c5e2851.css">
<link rel="stylesheet" href="https://sibforms.com/forms/end-form/build/sib-styles.css">
<!--  END - We recommend to place the above code in head tag of your website html -->

<!-- START - We recommend to place the below code where you want the form in your website html  -->
<div class="sib-form" style="text-align: center;">
            <div id="sib-form-container" class="sib-form-container">
              <div id="error-message" class="sib-form-message-panel" style="font-size:16px; text-align:left; font-family:&quot;Helvetica&quot;, sans-serif; color:#661d1d; background-color:#ffeded; border-radius:3px; border-width:px; border-color:#ff4949;max-width:540px; border-width:px;">
                <div class="sib-form-message-panel__text sib-form-message-panel__text--center">
                  <svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
                    <path d="M256 40c118.621 0 216 96.075 216 216 0 119.291-96.61 216-216 216-119.244 0-216-96.562-216-216 0-119.203 96.602-216 216-216m0-32C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm-11.49 120h22.979c6.823 0 12.274 5.682 11.99 12.5l-7 168c-.268 6.428-5.556 11.5-11.99 11.5h-8.979c-6.433 0-11.722-5.073-11.99-11.5l-7-168c-.283-6.818 5.167-12.5 11.99-12.5zM256 340c-15.464 0-28 12.536-28 28s12.536 28 28 28 28-12.536 28-28-12.536-28-28-28z"
                    />
                  </svg>
                  <span class="sib-form-message-panel__inner-text">
                                    Your subscription could not be saved. Please try again.
                                </span>
                </div>
              </div>
              <div></div>
              <div id="success-message" class="sib-form-message-panel" style="font-size:16px; text-align:left; font-family:&quot;Helvetica&quot;, sans-serif; color:#085229; background-color:#e7faf0; border-radius:3px; border-width:px; border-color:#13ce66;max-width:540px; border-width:px;">
                <div class="sib-form-message-panel__text sib-form-message-panel__text--center">
                  <svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
                    <path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 464c-118.664 0-216-96.055-216-216 0-118.663 96.055-216 216-216 118.664 0 216 96.055 216 216 0 118.663-96.055 216-216 216zm141.63-274.961L217.15 376.071c-4.705 4.667-12.303 4.637-16.97-.068l-85.878-86.572c-4.667-4.705-4.637-12.303.068-16.97l8.52-8.451c4.705-4.667 12.303-4.637 16.97.068l68.976 69.533 163.441-162.13c4.705-4.667 12.303-4.637 16.97.068l8.451 8.52c4.668 4.705 4.637 12.303-.068 16.97z"
                    />
                  </svg>
                  <span class="sib-form-message-panel__inner-text">
                                    Your subscription has been successful.
                                </span>
                </div>
              </div>
              <div></div>
              <div id="sib-container" class="sib-container--large sib-container--vertical" style="text-align:center; background-color:rgba(255,255,255,1); max-width:540px; border-radius:3px; border-width:1px; border-color:#C0CCD9; border-style:solid;">
                <form id="sib-form" method="POST" action="https://cddf18fd.sibforms.com/serve/MUIEAO9t8eOB2GOqY73EWqFatPi328RiosfYMKieZ_8IxVL2jyEazmQ9LlkDj6pYrTlvB7JBsx3su8WdK5A4l445X0P-0r0Qf82LWXLSFa3yK0YZuypiIxy8hZfBXClZMANBeEVpBkswLw0RxDt2uWrN7B7zHTFXWY0W4mftpWo3Nqen7SQW1L9DYnXrex6lyw5EfHvZ3ZwsU6Xp"
                  data-type="subscription">                                   
                  <div style="padding: 16px 0;">
                    <div class="sib-input sib-form-block">
                      <div class="form__entry entry_block">
                        <div class="form__label-row ">
                          <label class="entry__label" style="font-size:16px; text-align:left; font-weight:700; font-family:&quot;Helvetica&quot;, sans-serif; color:#3c4858; border-width:px;" for="EMAIL" data-required="*">
                            Enter your email address to subscribe
                          </label>

                          <div class="entry__field">
                            <input class="input" type="text" id="EMAIL" name="EMAIL" autocomplete="off" placeholder="EMAIL" data-required="true" required />
                          </div>
                        </div>

                        <label class="entry__error entry__error--primary" style="font-size:16px; text-align:left; font-family:&quot;Helvetica&quot;, sans-serif; color:#661d1d; background-color:#ffeded; border-radius:3px; border-width:px; border-color:#ff4949;">
                        </label>
                        <label class="entry__specification" style="font-size:12px; text-align:left; font-family:&quot;Helvetica&quot;, sans-serif; color:#8390A4; border-width:px;">
                          Provide your email address to subscribe. For e.g abc@xyz.com
                        </label>
                      </div>
                    </div>
                  </div>
                  <div style="padding: 16px 0;">
                    <div class="sib-form-block" style="text-align: left">
                      <button class="sib-form-block__button sib-form-block__button-with-loader" style="font-size:16px; text-align:left; font-weight:700; font-family:&quot;Helvetica&quot;, sans-serif; color:#FFFFFF; background-color:#3E4857; border-radius:3px; border-width:0px;"
                        form="sib-form" type="submit">
                        <svg class="icon clickable__icon progress-indicator__icon sib-hide-loader-icon" viewBox="0 0 512 512">
                          <path d="M460.116 373.846l-20.823-12.022c-5.541-3.199-7.54-10.159-4.663-15.874 30.137-59.886 28.343-131.652-5.386-189.946-33.641-58.394-94.896-95.833-161.827-99.676C261.028 55.961 256 50.751 256 44.352V20.309c0-6.904 5.808-12.337 12.703-11.982 83.556 4.306 160.163 50.864 202.11 123.677 42.063 72.696 44.079 162.316 6.031 236.832-3.14 6.148-10.75 8.461-16.728 5.01z"
                          />
                        </svg>
                        SUBSCRIBE
                      </button>
                    </div>
                  </div>
                  <div style="padding: 16px 0;">
                    <div class="sib-form-block" style="font-size:14px; text-align:center; font-family:&quot;Helvetica&quot;, sans-serif; color:#333; background-color:transparent; border-width:px;">
                      <div class="sib-text-form-block">
                        <p>
                          <a href="https://sendinblue.com" target="_blank">Terms &amp; Privacy policy</a>
                        </p>
                      </div>
                    </div>
                  </div>

                  <input type="text" name="email_address_check" value="" class="input--hidden">
                  <input type="hidden" name="locale" value="en">
                </form>
              </div>
            </div>
          </div>
          <!-- END - We recommend to place the below code where you want the form in your website html  -->

          <!-- START - We recommend to place the below code in footer or bottom of your website html  -->
          <script>
            window.REQUIRED_CODE_ERROR_MESSAGE = 'Please choose a country code';

            window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE = "The information provided is invalid. Please review the field format and try again.";

            window.REQUIRED_ERROR_MESSAGE = "This field cannot be left blank. ";

            window.GENERIC_INVALID_MESSAGE = "The information provided is invalid. Please review the field format and try again.";




            window.translation = {
              common: {
                selectedList: '{quantity} list selected',
                selectedLists: '{quantity} lists selected'
              }
            };

            var AUTOHIDE = Boolean(0);
          </script>
          <script src="https://sibforms.com/forms/end-form/build/main.js">
          </script>
          <script src="https://www.google.com/recaptcha/api.js?hl=en"></script>
          <!-- END - We recommend to place the above code in footer or bottom of your website html  -->
          <!-- End Sendinblue Form -->                
            </div>
        </div>

    </div>
    <?php
}
?>
