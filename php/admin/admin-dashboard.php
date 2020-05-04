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
    wp_enqueue_script('qsm_admin_script', plugins_url('../../js/admin.js', __FILE__), array('jquery'), $mlwQuizMasterNext->version);
    wp_enqueue_style('qsm_admin_dashboard_css', plugins_url('../../css/admin-dashboard.css', __FILE__));
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
                        <a class="button button-primary button-hero load-customize hide-if-no-customize" href="#"><?php _e('Create New Quiz/Survery', 'quiz-master-next'); ?></a>
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
    <?php
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


add_filter('qsm_dashboard_widget', 'simple_html_testing', 10, 1);
function simple_html_testing($array){
    $array['simple_html'] = array(
        'sidebar' => 'normal',
        'callback' => 'qsm_dashboard_simple_html',
        'title' => 'Simple Html'
    );
    update_option('qsm_dashboard_widget_arr', $array);
    return $array;
}
function qsm_dashboard_simple_html($widget_id){ ?>
    <div id="<?php echo $widget_id; ?>" class="postbox <?php qsm_check_close_hidden_box($widget_id); ?>">
        <button type="button" class="handlediv" aria-expanded="true">
            <span class="screen-reader-text">Toggle panel: <?php _e("Testing Stucture", 'quiz-master-next'); ?></span>
            <span class="toggle-indicator" aria-hidden="true"></span>
        </button>
        <h2 class="hndle ui-sortable-handle"><span><?php _e("Testing Stucture", 'quiz-master-next'); ?></span></h2>
        <div class="inside">
            <div class="main">
                Perfect testing of dashboard
            </div>
        </div>
    </div><?php
}