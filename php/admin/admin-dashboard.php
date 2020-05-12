<?php
/**
 * @since 7.0
 * @param str $widget_id
 */
function qsm_check_close_hidden_box($widget_id){
    $current_screen = get_current_screen();
    $page_id = $current_screen->id;
    $user = wp_get_current_user();
    $closed_div = get_user_option("closedpostboxes_$page_id", $user->ID);
    if($closed_div && is_array($closed_div)){
        echo in_array($widget_id, $closed_div) ? 'closed' : '';
    }
    
    $hidden_box = get_user_option("metaboxhidden_$page_id", $user->ID);
    if($hidden_box && is_array($hidden_box)){
        echo in_array($widget_id, $hidden_box) ? ' hide-if-js' : '';
    }
}

/**
 * 
 * @param str $status
 * @param obj $args
 * @return type
 */
function qsm_dashboard_screen_options($status, $args){
    $screen = get_current_screen();
    if( is_object($screen) && trim($screen->id) == 'toplevel_page_qsm_dashboard' ){
        ob_start();
        $page_id = $screen->id;
        $user = wp_get_current_user();
        ?>
        <form id="adv-settings" method="post">
            <fieldset class="metabox-prefs">
                <legend>Boxes</legend>
                <?php
                $hidden_box = get_user_option( "metaboxhidden_$page_id", $user->ID);
                $hidden_box_arr = !empty($hidden_box) ? $hidden_box : array();
                $registered_widget = get_option('qsm_dashboard_widget_arr', array());
                if($registered_widget){
                    foreach ($registered_widget as $key => $value) { ?>
                        <label for="<?php echo $key; ?>-hide"><input class="hide-postbox-tog" name="<?php echo $key; ?>-hide" type="checkbox" id="<?php echo $key; ?>-hide" value="<?php echo $key; ?>" <?php if(!in_array($key, $hidden_box_arr)){ ?>checked="checked"<?php } ?>><?php echo $value['title']; ?></label>
                    <?php
                    }
                }
                ?>
            </fieldset>
            <?php wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false, false ); ?>
        </form>    
        <?php    
        return ob_get_clean();    
    }
    return $status;        
}

/**
 * @since 7.0
 * @return HTMl Dashboard for QSM
 */
function qsm_generate_dashboard_page() {
    // Only let admins and editors see this page.
    if (!current_user_can('edit_posts')) {
        return;
    }
    global $mlwQuizMasterNext;
    wp_enqueue_script( 'micromodal_script', plugins_url( '../../js/micromodal.min.js', __FILE__ ) );
    wp_enqueue_script('qsm_admin_script', plugins_url('../../js/admin.js', __FILE__), array('jquery','micromodal_script','jquery-ui-accordion'), $mlwQuizMasterNext->version);
    wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ) );
    wp_enqueue_style('qsm_admin_dashboard_css', plugins_url('../../css/admin-dashboard.css', __FILE__));
    wp_enqueue_style('qsm_ui_css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script( 'dashboard' );
    if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
    }
    ?>
    <div class="wrap">
        <h1><?php _e('QSM Dashboard', 'quiz-master-next'); ?></h1>
        <div id="welcome-panel" class="welcome-panel">
            <div class="welcome-panel-close">
                <img src="<?php echo QSM_PLUGIN_URL . '/assets/icon-128x128.png'; ?>">
                <p class="current_version"><?php echo $mlwQuizMasterNext->version; ?></p>
            </div>
            <div class="welcome-panel-content">
                <h2><?php _e('Welcome to Quiz And Survey Master!', 'quiz-master-next'); ?></h2>
                <p class="about-description"><?php _e('Formerly Quiz Master Next', 'quiz-master-next'); ?></p>
                <div class="welcome-panel-column-container">
                    <div class="welcome-panel-column">
                        <h3><?php _e('Get Started', 'quiz-master-next'); ?></h3>
                        <a class="button button-primary button-hero load-quiz-wizard hide-if-no-customize" href="#"><?php _e('Create New Quiz/Survery', 'quiz-master-next'); ?></a>
                        <p class="hide-if-no-customize">
                            or, <a href="admin.php?page=mlw_quiz_list"><?php _e('Edit previously created quizzes', 'quiz-master-next'); ?></a>
                        </p>
                    </div>
                    <div class="welcome-panel-column">
                        <h3><?php _e('Next Steps', 'quiz-master-next'); ?></h3>
                        <ul>
                            <li><a target="_blank" href="https://quizandsurveymaster.com/docs/" class="welcome-icon"><span class="dashicons dashicons-media-document"></span>&nbsp;&nbsp;<?php _e('Read Documentation', 'quiz-master-next'); ?></a></li>
                            <li><a target="_blank" href="https://quizandsurveymaster.com/" class="welcome-icon"><span class="dashicons dashicons-format-video"></span>&nbsp;&nbsp;<?php _e('See demos', 'quiz-master-next'); ?></a></li>
                            <li><a target="_blank" href="https://quizandsurveymaster.com/addons/" class="welcome-icon"><span class="dashicons dashicons-plugins-checked"></span>&nbsp;&nbsp;<?php _e('Extend QSM with PRO Addons', 'quiz-master-next'); ?></a></li>
                        </ul>
                    </div>
                    <div class="welcome-panel-column welcome-panel-last">
                        <h3><?php _e('Usefull Links', 'quiz-master-next'); ?></h3>
                        <ul>
                            <li><a target="_blank" href="https://support.quizandsurveymaster.com/" class="welcome-icon"><span class="dashicons dashicons-admin-users"></span>&nbsp;&nbsp;<?php _e('Support Forum', 'quiz-master-next'); ?></a></li>
                            <li><a target="_blank" href="https://github.com/QuizandSurveyMaster/quiz_master_next" class="welcome-icon"><span class="dashicons dashicons-editor-code"></span>&nbsp;&nbsp;<?php _e('Github Repository', 'quiz-master-next'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php do_action('qsm_welcome_panel'); ?>
        </div>
        <?php
        $qsm_dashboard_widget = array(
            'dashboard_popular_addon' => array(
                'sidebar' => 'normal',
                'callback' => 'qsm_dashboard_popular_addon',
                'title' => 'Popular Addons'
            ),
            'dashboard_recent_taken_quiz' => array(
                'sidebar' => 'normal',
                'callback' => 'qsm_dashboard_recent_taken_quiz',
                'title' => 'Recent Taken Quiz'
            ),
            'dashboard_what_new' => array(
                'sidebar' => 'side',
                'callback' => 'qsm_dashboard_what_new',
                'title' => 'Latest news'
            ),
            'dashboard_chagelog' => array(
                'sidebar' => 'side',
                'callback' => 'qsm_dashboard_chagelog',
                'title' => 'Changelog'
            )
        );
        $qsm_dashboard_widget = apply_filters('qsm_dashboard_widget', $qsm_dashboard_widget);
        update_option('qsm_dashboard_widget_arr', $qsm_dashboard_widget);
        
        //Get the metabox positions
        $current_screen = get_current_screen();
        $page_id = $current_screen->id;
        $user = wp_get_current_user();
        $box_positions = get_user_option("meta-box-order_$page_id", $user->ID);
        ?>
        <div id="dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <?php
                        $normal_widgets = $side_widgets = array();
                        if($box_positions && is_array($box_positions) && isset($box_positions['normal']) && $box_positions['normal'] != ''){
                            $normal_widgets = explode(',', $box_positions['normal']);
                            foreach ($normal_widgets as $value) {
                                if(isset($qsm_dashboard_widget[$value])){
                                    call_user_func($qsm_dashboard_widget[$value]['callback'], $value);
                                }
                            }
                        }
                        if($box_positions && is_array($box_positions) && isset($box_positions['side']) && $box_positions['side'] != ''){
                            $side_widgets = explode(',', $box_positions['side']);
                        }
                        $all_widgets = array_merge($normal_widgets,$side_widgets);
                        if ($qsm_dashboard_widget) {
                            foreach ($qsm_dashboard_widget as $widgte_id => $normal_widget) {
                                if (!in_array($widgte_id, $all_widgets) && $normal_widget['sidebar'] == 'normal') {
                                    call_user_func($normal_widget['callback'], $widgte_id);
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div id="postbox-container-2" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables ui-sortable">
                        <?php
                        $normal_widgets = array();
                        if($box_positions && is_array($box_positions) && isset($box_positions['side']) && $box_positions['side'] != ''){
                            $normal_widgets = explode(',', $box_positions['side']);
                            foreach ($normal_widgets as $value) {
                                if(isset($qsm_dashboard_widget[$value])){
                                    call_user_func($qsm_dashboard_widget[$value]['callback'], $value);
                                }
                            }
                        }
                        if ($qsm_dashboard_widget) {
                            foreach ($qsm_dashboard_widget as $widgte_id => $normal_widget) {
                                if (!in_array($widgte_id, $all_widgets) && $normal_widget['sidebar'] == 'side') {
                                    call_user_func($normal_widget['callback'], $widgte_id);
                                }
                            }
                        }                        
                        ?>
                    </div>
                </div>
            </div>
            <?php
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
            ?>
        </div><!-- dashboard-widgets-wrap -->
    </div>
    <!-- Popup for new wizard -->
    <div class="qsm-popup qsm-popup-slide" id="model-wizard" aria-hidden="true">
        <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
            <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
                <header class="qsm-popup__header">
                    <h2 class="qsm-popup__title" id="modal-2-title"><?php _e('Create New Quiz Or Survey', 'quiz-master-next'); ?></h2>
                    <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                </header>
                <main class="qsm-popup__content" id="modal-2-content">
                    <?php
                    $qsm_quiz_templates = array(
                        array(
                            'template_name' => __('Start from scratch', 'quiz-master-next'),
                            'priority' => '1',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                )
                            ),
                            'recommended_addon' => array(
                                array(
                                    'name' => 'Payment Integration',
                                    'link' => 'https://quizandsurveymaster.com/downloads/paypal-and-stripe-payment-integration/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Paypal-and-Stripe-Payment-Integration-300x170.jpg',
                                    'attribute' => 'recommended'
                                ),
                                array(
                                    'name' => 'Google Sheet',
                                    'link' => 'https://quizandsurveymaster.com/downloads/sync-with-google-sheets/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/03/first-1-300x170.jpg',
                                    'attribute' => 'required'
                                ),
                            )
                        ),
                        array(
                            'template_name' => __('Sample Template', 'quiz-master-next'),
                            'priority' => '2',
                            'template_img' => 'http://localhost/work/et/qsm/wp-content/uploads/2020/05/sample-quiz.png',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                ),
                                'enable_result_after_timer_end' => array(
                                    'option_name' => 'Force Submit',
                                    'value' => 0
                                )
                            ),
                        ),
                        array(
                            'template_name' => __('Customer Feedback', 'quiz-master-next'),
                            'priority' => '3',
                            'template_img' => 'http://localhost/work/et/qsm/wp-content/uploads/2020/05/sample-quiz.png',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                ),
                                'enable_result_after_timer_end' => array(
                                    'option_name' => 'Force Submit',
                                    'value' => 0
                                ),
                                'progress_bar' => array(
                                    'option_name' => 'Progress Bar',
                                    'value' => 0
                                )
                            ),
                        ),
                        array(
                            'template_name' => __('Event Planning', 'quiz-master-next'),
                            'priority' => '4',
                            'template_img' => 'http://localhost/work/et/qsm/wp-content/uploads/2020/05/sample-quiz.png',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                ),
                                'enable_result_after_timer_end' => array(
                                    'option_name' => 'Force Submit',
                                    'value' => 0
                                ),
                                'progress_bar' => array(
                                    'option_name' => 'Progress Bar',
                                    'value' => 0
                                )
                            ),
                        ),
                    );
                    $qsm_quiz_templates = apply_filters('qsm_wizard_quiz_templates', $qsm_quiz_templates);
                    $keys = array_column($qsm_quiz_templates, 'priority');
                    array_multisort($keys, SORT_ASC, $qsm_quiz_templates);
                    ?>
                    <form action="" method="post" id="new-quiz-form">
                        <div class="qsm-wizard-template-section">
                            <?php wp_nonce_field('qsm_new_quiz', 'qsm_new_quiz_nonce'); ?>
                            <input type="text" class="quiz_name" name="quiz_name" value="" placeholder="<?php _e('Enter quiz name here', 'quiz-master-next'); ?>" />
                            <div class="template-inner-wrap">
                                <h6><?php _e('Select Template', 'quiz-master-next'); ?></h6>
                                <div class="template-list">
                                    <?php 
                                    if( $qsm_quiz_templates ){
                                        foreach ($qsm_quiz_templates as $key => $single_template) {
                                            if(isset($single_template['priority']) && $single_template['priority'] == 1){ ?>
                                                <div class="template-list-inner" data-settings='<?php echo isset($single_template['options']) ? json_encode($single_template['options']) : ''; ?>' data-addons='<?php echo isset($single_template['recommended_addon']) ? json_encode($single_template['recommended_addon']) : ''; ?>'>
                                                    <div class="template-center-vertical">
                                                        <span class="dashicons dashicons-plus-alt2"></span>
                                                        <p class="start_scratch"><?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?></p>
                                                    </div>                                        
                                                </div>
                                            <?php                                
                                            }else{ ?>
                                                <div class="template-list-inner inner-json" data-settings='<?php echo isset($single_template['options']) ? json_encode($single_template['options']) : ''; ?>' data-addons='<?php echo isset($single_template['recommended_addon']) ? json_encode($single_template['recommended_addon']) : ''; ?>'>
                                                    <h3><?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?></h3>
                                                    <div class="template-center-vertical">
                                                        <?php
                                                        if( isset($single_template['template_img']) ){ ?>
                                                            <img src="<?php echo $single_template['template_img']; ?>" title="<?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?>">
                                                        <?php                                                        
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php                                            
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="qsm-wizard-setting-section">
                            <div id="accordion">
                                <h3><?php _e('Quiz Settings', 'quiz-master-next'); ?></h3>
                                <div id="quiz_settings_wrapper"></div>
                                <h3><?php _e('Recommended Addons', 'quiz-master-next'); ?></h3>
                                <div id="recomm_addons_wrapper"></div>
                              </div>
                        </div>
                    </form>
                </main>
                <footer class="qsm-popup__footer">
                    <button id="create-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Create Quiz', 'quiz-master-next'); ?></button>
                    <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
                </footer>
            </div>
        </div>
    </div>
    <?php
}

add_action( 'wp_ajax_qsm_wizard_template_quiz_options', 'qsm_wizard_template_quiz_options' );
function qsm_wizard_template_quiz_options(){
    global $mlwQuizMasterNext;
    $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
    $addons = isset($_POST['addons']) ? $_POST['addons'] : array();
    $all_settings = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_options' );
    $recommended_addon_str = '';
    if($settings){
        foreach ($settings as $key => $single_setting) {
            $key = array_search($key, array_column($all_settings, 'id'));
            $field = $all_settings[$key];
            $field['label'] = $single_setting['option_name'];
            $field['default'] = $single_setting['value'];            
            QSM_Fields::generate_field( $field, $single_setting['value'] );
        }
    }else{
        echo __('No settings are found!', 'quiz-master-next');
    }
    echo '=====';
    if($addons){
        $recommended_addon_str .= '<ul>';
        foreach ($addons as $single_addon) {
            $recommended_addon_str .= '<li>';
            if(isset($single_addon['attribute']) && $single_addon['attribute'] != '' ){
                $attr = $single_addon['attribute'];
                $recommended_addon_str .= '<span class="ra-attr qra-att-'. $attr .'">' . $attr .'</span>';
            }
            $link = isset($single_addon['link']) ? $single_addon['link'] : '';
            $recommended_addon_str .= '<a href="' . $link . '">';
            if(isset($single_addon['img']) && $single_addon['img'] != '' ){
                $img = $single_addon['img'];
                $recommended_addon_str .= '<img src="' . $img . '"/>';
            }
            $recommended_addon_str .= '</a>';
            $recommended_addon_str .= '</li>';
        }
        $recommended_addon_str .= '</ul>';
    }else{
        $recommended_addon_str .= __('No addons are found!', 'quiz-master-next');
    }
    echo $recommended_addon_str;
    exit;
}

function qsm_dashboard_popular_addon($widget_id) {
    $file = esc_url('http://localhost/work/et/qsm/qsm_dashboard.json');
    $response = wp_remote_get($file, array('sslverify' => false));
    $body = wp_remote_retrieve_body($response);
    $addon_array = json_decode($body, TRUE);
    ?>
    <div id="<?php echo $widget_id; ?>" class="postbox <?php qsm_check_close_hidden_box($widget_id); ?>">
        <button type="button" class="handlediv" aria-expanded="true">
            <span class="screen-reader-text">Toggle panel: <?php _e('Most Popular Addon this Week', 'quiz-master-next'); ?></span>
            <span class="toggle-indicator" aria-hidden="true"></span>
        </button>
        <h2 class="hndle ui-sortable-handle"><span><?php _e('Most Popular Addon this Week', 'quiz-master-next'); ?></span></h2>
        <div class="inside">
            <div class="main">
                <ul class="popuar-addon-ul">
                    <?php
                    if (isset($addon_array['most_popular_addon'])) {
                        foreach ($addon_array['most_popular_addon'] as $key => $single_arr) {
                            ?>
                            <li>
                                <a href="<?php echo $single_arr['link'] ?>" target="_blank">
                                    <img src="<?php echo $single_arr['image']; ?>" title="<?php echo $single_arr['name']; ?>">
                                </a>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
                <div class="pa-all-addon">
                    <a href="https://quizandsurveymaster.com/addons/" target="_blank"><?php _e('SEE ALL ADDONS', 'quiz-master-next'); ?></a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function qsm_dashboard_recent_taken_quiz($widget_id) {
    global $wpdb;
    ?>
    <div id="<?php echo $widget_id; ?>" class="postbox <?php qsm_check_close_hidden_box($widget_id); ?>">
        <button type="button" class="handlediv" aria-expanded="true">
            <span class="screen-reader-text">Toggle panel: <?php _e('Recently Taken Quizzes', 'quiz-master-next'); ?></span>
            <span class="toggle-indicator" aria-hidden="true"></span>
        </button>
        <h2 class="hndle ui-sortable-handle"><span><?php _e('Recently Taken Quizzes', 'quiz-master-next'); ?></span></h2>
        <div class="inside">
            <div class="main">
                <ul class="recently-taken-quiz-ul">
                    <?php
                    $mlw_resutl_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted='0' ORDER BY result_id DESC LIMIT 2", ARRAY_A);
                    if ($mlw_resutl_data) {
                        foreach ($mlw_resutl_data as $key => $single_result_arr) {
                            ?>
                            <li>
                                <?php
                                if (isset($single_result_arr['user']) && $single_result_arr['user'] != '') {
                                    echo '<img src="' . get_avatar_url($single_result_arr['user']) . '" class="avatar avatar-50 photo">';
                                } else {
                                    echo '<img src="' . QSM_PLUGIN_URL . '/assets/default_image.png" class="avatar avatar-50 photo">';
                                }
                                ?>
                                <div class="rtq-main-wrapper">
                                    <span class="rtq_user_info">
                                        <?php
                                        if (isset($single_result_arr['user']) && $single_result_arr['user'] != '') {
                                            $edit_link = get_edit_profile_url($single_result_arr['user']);
                                            echo '<a href="' . $edit_link . '">' . $single_result_arr['name'] . '</a>';
                                        } else {
                                            echo $single_result_arr['name'];
                                        }
                                        _e(' took quiz ', 'quiz-master-next');
                                        echo '<a href="admin.php?page=mlw_quiz_options&quiz_id=' . $single_result_arr['quiz_id'] . '">' . $single_result_arr['quiz_name'] . '</a>';
                                        ?>
                                    </span>
                                    <span class="rtq-result-info">
                                        <?php
                                        $quotes_list = '';
                                        if ($single_result_arr['quiz_system'] == 0) {
                                            $quotes_list .= $single_result_arr['correct'] . " out of " . $single_result_arr['total'] . " or " . $single_result_arr['correct_score'] . "%";
                                        }
                                        if ($single_result_arr['quiz_system'] == 1) {
                                            $quotes_list .= $single_result_arr['point_score'] . " Points";
                                        }
                                        if ($single_result_arr['quiz_system'] == 2) {
                                            $quotes_list .= __('Not Graded', 'quiz-master-next');
                                        }
                                        echo $quotes_list;
                                        ?>
                                        |
                                        <?php
                                        $mlw_complete_time = '';
                                        $mlw_qmn_results_array = @unserialize($single_result_arr['quiz_results']);
                                        if (is_array($mlw_qmn_results_array)) {
                                            $mlw_complete_hours = floor($mlw_qmn_results_array[0] / 3600);
                                            if ($mlw_complete_hours > 0) {
                                                $mlw_complete_time .= "$mlw_complete_hours hours ";
                                            }
                                            $mlw_complete_minutes = floor(($mlw_qmn_results_array[0] % 3600) / 60);
                                            if ($mlw_complete_minutes > 0) {
                                                $mlw_complete_time .= "$mlw_complete_minutes minutes ";
                                            }
                                            $mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
                                            $mlw_complete_time .= "$mlw_complete_seconds seconds";
                                        }
                                        _e(' Time to complete ', 'quiz-master-next');
                                        echo $mlw_complete_time;
                                        ?>
                                    </span>
                                    <span class="rtq-time-taken"><?php echo date_i18n(get_option('date_format'), strtotime($single_result_arr['time_taken'])); ?></span>
                                    <p class="row-actions-c">
                                        <a href="admin.php?page=qsm_quiz_result_details&result_id=<?php echo $single_result_arr['result_id']; ?>">View</a> | <a href="#" data-result_id="<?php echo $single_result_arr['result_id']; ?>" class="trash rtq-delete-result">Delete</a>
                                    </p>
                                </div>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
                <p>
                    <a href="admin.php?page=mlw_quiz_results">
                        <?php
                        $mlw_resutl_data = $wpdb->get_row("SELECT DISTINCT COUNT(result_id) as total_result FROM {$wpdb->prefix}mlw_results WHERE deleted='0'", ARRAY_A);
                        echo isset($mlw_resutl_data['total_result']) ? __('See All Results ', 'quiz-master-next') : '';
                        ?>
                    </a>
                    <?php
                    echo isset($mlw_resutl_data['total_result']) ? '(' . $mlw_resutl_data['total_result'] . ')' : '';
                    ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}

function qsm_dashboard_what_new($widget_id) {
    ?>
    <div id="<?php echo $widget_id; ?>" class="postbox <?php qsm_check_close_hidden_box($widget_id); ?>">
        <button type="button" class="handlediv" aria-expanded="true">
            <span class="screen-reader-text">Toggle panel: <?php _e("'what's New", 'quiz-master-next'); ?></span>
            <span class="toggle-indicator" aria-hidden="true"></span>
        </button>
        <h2 class="hndle ui-sortable-handle"><span><?php _e("What's New", 'quiz-master-next'); ?></span></h2>
        <div class="inside">
            <div class="main">
                <ul class="what-new-ul">
                    <?php
                    $feeds = esc_url('https://quizandsurveymaster.com/wp-json/wp/v2/posts?per_page=2');
                    $feed_posts = wp_remote_get($feeds, array('sslverify' => false));
                    $feed_posts_body = wp_remote_retrieve_body($feed_posts);
                    $feed_posts_array = json_decode($feed_posts_body, TRUE);
                    if (!empty($feed_posts_array)) {
                        foreach ($feed_posts_array as $key => $single_feed_arr) {
                            ?>
                            <li>
                                <a href="<?php echo $single_feed_arr['link']; ?>" target="_blank">
                                    <?php echo $single_feed_arr['title']['rendered']; ?>
                                </a>
                                <div class="post-description">
                                    <?php echo $single_feed_arr['excerpt']['rendered']; ?>
                                </div>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

function qsm_dashboard_chagelog($widget_id) {
    ?>
    <div id="<?php echo $widget_id; ?>" class="postbox <?php qsm_check_close_hidden_box($widget_id); ?>">
        <button type="button" class="handlediv" aria-expanded="true">
            <span class="screen-reader-text">Toggle panel: <?php _e("Changelog", 'quiz-master-next'); ?></span>
            <span class="toggle-indicator" aria-hidden="true"></span>
        </button>
        <h2 class="hndle ui-sortable-handle"><span><?php _e("Changelog (6.4.8)", 'quiz-master-next'); ?></span></h2>
        <div class="inside">
            <div class="main">
                <ul class="changelog-ul">
                    <li><span class="enhancement">Enhancement</span> JavaScript error messages will show up only for WordPress admins - <a href="#">Issue#754</a></li>
                    <li><span class="bug">Bug</span> Changed the quiz post type slug to solve the conflict with LMS plugin - <a href="#">Issue#768</a></li>
                    <li><span class="user_request">User Request</span> Added the button to remove the result data permanent - <a href="#">Issue#778</a></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}


add_action('admin_init','qsm_create_new_quiz_from_wizard');
/**
 * @since 7.0
 * 
 * Create new quiz and redirect to newly created quiz
 */

function qsm_create_new_quiz_from_wizard(){
    // Create new quiz.
    if (isset($_POST['qsm_new_quiz_nonce']) && wp_verify_nonce($_POST['qsm_new_quiz_nonce'], 'qsm_new_quiz')) {
        global $mlwQuizMasterNext;
        $quiz_name = sanitize_textarea_field(htmlspecialchars(stripslashes($_POST['quiz_name']), ENT_QUOTES));
        unset($_POST['qsm_new_quiz_nonce']);
        unset($_POST['_wp_http_referer']);
        $setting_arr = array(
            'quiz_options' => serialize($_POST)
        );
        $mlwQuizMasterNext->quizCreator->create_quiz($quiz_name, serialize($setting_arr));
    }
}