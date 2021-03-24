<?php
/**
 * This file handles the contents on the "Quizzes/Surveys" page.
 *
 * @package QSM
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates the quizzes and surveys page
 *
 * @since 5.0
 */
function qsm_generate_quizzes_surveys_page() {

    // Only let admins and editors see this page.
    if (!current_user_can('edit_posts')) {
        return;
    }

    // Retrieve our globals.
    global $wpdb;
    global $mlwQuizMasterNext;

    // Enqueue our styles and scripts.
    wp_enqueue_script('micromodal_script', plugins_url('../../js/micromodal.min.js', __FILE__));
    wp_enqueue_style('qsm_admin_style', plugins_url('../../css/qsm-admin.css', __FILE__), array(), $mlwQuizMasterNext->version);
    wp_enqueue_script('qsm_admin_script', plugins_url('../../js/qsm-admin.js', __FILE__), array('wp-util', 'underscore', 'jquery', 'micromodal_script', 'jquery-ui-accordion'), $mlwQuizMasterNext->version);
    wp_enqueue_style('qsm_admin_dashboard_css', plugins_url('../../css/admin-dashboard.css', __FILE__));
    wp_enqueue_style('qsm_ui_css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');        

    // Delete quiz.
    if (isset($_POST['qsm_delete_quiz_nonce']) && wp_verify_nonce($_POST['qsm_delete_quiz_nonce'], 'qsm_delete_quiz')) {
        $quiz_id = intval($_POST['delete_quiz_id']);
        $quiz_name = sanitize_text_field($_POST['delete_quiz_name']);
        $mlwQuizMasterNext->quizCreator->delete_quiz($quiz_id, $quiz_name);
    }
    
    // Duplicate Quiz.
    if (isset($_POST['qsm_duplicate_quiz_nonce']) && wp_verify_nonce($_POST['qsm_duplicate_quiz_nonce'], 'qsm_duplicate_quiz')) {
        $quiz_id = intval($_POST['duplicate_quiz_id']);
        $quiz_name = sanitize_text_field(htmlspecialchars($_POST['duplicate_new_quiz_name'], ENT_QUOTES));
        $mlwQuizMasterNext->quizCreator->duplicate_quiz($quiz_id, $quiz_name, isset($_POST['duplicate_questions']) ? sanitize_text_field( $_POST['duplicate_questions'] ) : 0);
    }

    // Resets stats for a quiz.
    if (isset($_POST['qsm_reset_stats_nonce']) && wp_verify_nonce($_POST['qsm_reset_stats_nonce'], 'qsm_reset_stats')) {
        $quiz_id = intval($_POST['reset_quiz_id']);
        $results = $wpdb->update(
                $wpdb->prefix . 'mlw_quizzes', array(
            'quiz_views' => 0,
            'quiz_taken' => 0,
            'last_activity' => date('Y-m-d H:i:s'),
                ), array('quiz_id' => $quiz_id), array(
            '%d',
            '%d',
            '%s',
                ), array('%d')
        );
        if (false !== $results) {
            $mlwQuizMasterNext->alertManager->newAlert(__('The stats has been reset successfully.', 'quiz-master-next'), 'success');
            $mlwQuizMasterNext->audit_manager->new_audit("Quiz Stats Have Been Reset For Quiz Number $quiz_id");
        } else {
            $mlwQuizMasterNext->alertManager->newAlert(__('Error trying to reset stats. Please try again.', 'quiz-master-next'), 'error');
            $mlwQuizMasterNext->log_manager->add('Error resetting stats', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error');
        }
    }
    
    //Pagination.    
    $paged = filter_input(INPUT_GET, 'paged') ? absint(filter_input(INPUT_GET, 'paged')) : 1;
    /* //Not required already checked above as integer.(AA)
    if (!is_numeric($paged))
        $paged = 1;*/
    $limit = 10; // number of rows in page.
    
    $current_user = get_current_user_id();
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $limit = get_user_meta($current_user, $screen_option, true);
    
    if (empty($limit) || $limit < 1) {
        // get the default value if none is set
        $limit = $screen->get_option('per_page', 'default');
    }    
    $offset = ( $paged - 1 ) * $limit;
    $where = '';
    $search = '';    
    if (isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
        $search = htmlspecialchars($_REQUEST['s'],ENT_QUOTES) ;
        $where = " quiz_name LIKE '%$search%'";
    }
    
    /*if ( isset($_POST['btnSearchQuiz']) || isset($_POST['s']) && $_POST['s'] != '' ) {
        $delete_action = '';
        if (isset($_POST['take_action']) && isset($_POST['qsm-ql-action-top']) || isset($_POST['take_action']) && isset($_POST['qsm-ql-action-bottom'])) {
            $delete_action = 'multiple_delete';
        }
        ?>
        <script type="text/javascript">
            var paged = '<?php echo $paged; ?>';
            var s = ['<?php echo $search; ?>'];
            var action = ['<?php echo $delete_action; ?>'];
            window.location = "?page=mlw_quiz_list&paged=1&s=" + s + "&action=" + action;
        </script>
        <?php
    } */
    
    // Multiple Delete quiz.
    if (isset($_POST['qsm_search_multiple_delete_nonce']) && wp_verify_nonce($_POST['qsm_search_multiple_delete_nonce'], 'qsm_search_multiple_delete')) {
        if( ( isset($_POST[ 'qsm-ql-action-top' ]) && $_POST['qsm-ql-action-top'] == 'delete_pr' ) || ( isset($_POST[ 'qsm-ql-action-bottom' ]) && $_POST['qsm-ql-action-bottom'] == 'delete_pr' ) ){
            $quiz_ids_arr = $_POST['chk_remove_all'];            
            if($quiz_ids_arr){
                $_POST['qsm_delete_question_from_qb'] = 1;
                foreach ($quiz_ids_arr as $quiz_id) {
                    $mlwQuizMasterNext->quizCreator->delete_quiz($quiz_id, $quiz_id);
                }
            }
        }
    }
    /*Set Request To Post as form method is Post.(AA)*/
    if (isset($_POST['btnSearchQuiz']) && $_POST['s'] != '') {
        $search = htmlspecialchars($_POST['s'] ,ENT_QUOTES) ;
        $condition = " WHERE deleted=0 AND quiz_name LIKE '%$search%'";
        $qry = stripslashes( $wpdb->prepare( "SELECT COUNT('quiz_id') FROM {$wpdb->prefix}mlw_quizzes%1s", $condition ) );
        $total = $wpdb->get_var($qry);
        $num_of_pages = ceil($total / $limit);
    } else {
        $condition = " WHERE deleted=0";
        $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(`quiz_id`) FROM {$wpdb->prefix}mlw_quizzes %1s", $condition ) );
        $num_of_pages = ceil($total / $limit);
    }
    
    //Next and previous page.
    $next_page = (int) $paged + 1;

    if ($next_page > $num_of_pages){
        $next_page = $num_of_pages;
    }

    $prev_page = (int) $paged - 1;

    if ($prev_page < 1){
        $prev_page = 1;
    }

   
    //Check user role and fetch the quiz
    $user = wp_get_current_user();
    if (in_array('author', (array) $user->roles)) {
        $post_arr['author__in'] = [$user->ID];
    }
    if (isset($_GET['order']) && $_GET['order'] == 'asc') {
        $post_arr['orderby'] = isset($_GET['orderby']) && $_GET['orderby'] == 'title' ? 'title' : 'last_activity';
        $post_arr['order'] = 'ASC';
        // Load our quizzes.
        $quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes(false, $post_arr['orderby'], 'ASC', (array) $user->roles, $user->ID, $limit, $offset, $where);
    } else if (isset($_GET['order']) && $_GET['order'] == 'desc') {
        $post_arr['orderby'] = isset($_GET['orderby']) && $_GET['orderby'] == 'title' ? 'title' : 'last_activity';
        $post_arr['order'] = 'DESC';
        // Load our quizzes.
        $quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes(false, $post_arr['orderby'], 'DESC', (array) $user->roles, $user->ID, $limit, $offset, $where);
    } else {
        // Load our quizzes.
        $quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes(false, '', '', (array) $user->roles, $user->ID, $limit, $offset, $where);
    }
    /*Written to get results form search.(AA)*/
    if (isset($_POST['btnSearchQuiz']) && $_POST['s'] != '') {
        $search_quiz = htmlspecialchars($_POST['s'], ENT_QUOTES) ;
        $condition = " WHERE quiz_name LIKE '%$search_quiz%'";
        $qry = stripslashes( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes%1s", $condition) );
        $quizzes = $wpdb->get_results($qry );

    }

    // Load quiz posts.
    $post_to_quiz_array = array();
     //Query for post
    $post_arr = array(
        'post_type' => 'qsm_quiz',
        'paged' => $paged,
        'posts_per_page' => -1,
        'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private')
    );
    $my_query = new WP_Query($post_arr);

    if ($my_query->have_posts()) {
        while ($my_query->have_posts()) {
            $my_query->the_post();
            $post_to_quiz_array[get_post_meta(get_the_ID(), 'quiz_id', true)] = array(
                'link' => get_the_permalink(get_the_ID()),
                'id' => get_the_ID(),
                'post_status' => get_post_status(get_the_ID()),
            );
        }
    }
    wp_reset_postdata();
    $quiz_json_array = array();
    foreach ($quizzes as $quiz) {
        if (!isset($post_to_quiz_array[$quiz->quiz_id])) {
            $current_user = wp_get_current_user();
            $quiz_post = array(
                'post_title' => $quiz->quiz_name,
                'post_content' => "[qsm quiz={$quiz->quiz_id}]",
                //'post_status'  => 'publish',
                'post_author' => $current_user->ID,
                'post_type' => 'qsm_quiz',
            );
            $quiz_post_id = wp_insert_post($quiz_post);
            add_post_meta($quiz_post_id, 'quiz_id', $quiz->quiz_id);
            $post_to_quiz_array[$quiz->quiz_id] = array(
                'link' => get_permalink($quiz_post_id),
                'id' => $quiz_post_id,
                'post_status' => get_post_status($quiz_post_id),
            );
        }

		$quiz_results_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE `deleted`= 0 AND `quiz_id`= %d", $quiz->quiz_id ) );

        $activity_date = date_i18n(get_option('date_format'), strtotime($quiz->last_activity));
        $activity_time = date('h:i:s A', strtotime($quiz->last_activity));

        $quiz_json_array[] = array(
            'id' => $quiz->quiz_id,
            'name' => esc_html($quiz->quiz_name),
            'link' => $post_to_quiz_array[$quiz->quiz_id]['link'],
            'postID' => $post_to_quiz_array[$quiz->quiz_id]['id'],
            'views' => $quiz->quiz_views,
            /*'taken' => $quiz->quiz_taken,*/
            'taken' => $quiz_results_count,
            'lastActivity' => $activity_date,
            'lastActivityDateTime' => $activity_date . ' ' . $activity_time,
            'post_status' => $post_to_quiz_array[$quiz->quiz_id]['post_status'],
        );
    }
    $total_count = count($quiz_json_array);

    wp_localize_script('qsm_admin_script', 'qsmQuizObject', $quiz_json_array);
    ?>
    <div class="wrap qsm-quizes-page">
        <h1>
            <?php esc_html_e('Quizzes/Surveys', 'quiz-master-next'); ?>
            <a id="new_quiz_button" href="#" class="add-new-h2"><?php _e('Add New', 'quiz-master-next'); ?></a>
        </h1>
        <?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
        <?php
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            ?>
            <div class="qsm-info-box">
                <p><?php esc_html_e('Your site is using PHP version', 'quiz-master-next'); ?> <?php echo esc_html(PHP_VERSION); ?>! <?php esc_html_e('Starting in QSM 6.0, your version of PHP will no longer be supported.', 'quiz-master-next'); ?> <a href="http://bit.ly/2lyrrm8" target="_blank"><?php esc_html_e("Click here to learn more about QSM's minimum PHP version change.", 'quiz-master-next'); ?></a></p>
            </div>
            <?php
        }
        ?>
        <form method="POST" id="posts-filter">
            <?php wp_nonce_field('qsm_search_multiple_delete', 'qsm_search_multiple_delete_nonce'); ?>
            <div class="qsm-quizzes-page-content">
                <div class="<?php
                if ('false' != get_option('mlw_advert_shows')) {
                    echo 'qsm-quiz-page-wrapper-with-ads';
                } else {
                    echo 'qsm-quiz-page-wrapper';
                }
                ?>">
                    <p class="search-box">
                        <label class="screen-reader-text" for="quiz_search"><?php esc_html_e('Search', 'quiz-master-next'); ?></label>
                        <!-- Changed Request to Post -->
                        <input type="search" id="quiz_search" name="s" value="<?php echo isset($_POST['s']) && $_POST['s'] != '' ? htmlspecialchars($_POST['s'], ENT_QUOTES) : ''; ?>">
                        <input id="search-submit" class="button" type="submit" name="btnSearchQuiz" value="Search Quiz">
                        <?php if (class_exists('QSM_Export_Import')) { ?>
                            <a class="button button-primary" href="<?php echo admin_url() . 'admin.php?page=qmn_addons&tab=export-and-import'; ?>" target="_blank"><?php _e('Import & Export', 'quiz-master-next'); ?></a>
                        <?php } else { ?>
                            <a id="show_import_export_popup" href="#" style="position: relative;top: 0px;" class="add-new-h2 button-primary"><?php _e('Import & Export', 'quiz-master-next'); ?></a>
                        <?php } ?>
                    </p>
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <select name="qsm-ql-action-top">
                                <option selected="selected" value="none"><?php _e('Bulk Actions', 'quiz-master-next'); ?></option>
                                <option value="delete_pr"><?php _e('Delete Permanently', 'quiz-master-next'); ?></option>
                            </select>
                            <input id="take_action" name="take_action" class="button action" type="submit" value="<?php esc_attr_e('Apply', 'quiz-master-next'); ?>" >                    
                        </div>
                        <div class="tablenav-pages">                  
                            <span class="displaying-num"><?php echo number_format_i18n($total) . ' ' . sprintf(_n('item', 'items', $total), number_format_i18n($total)); ?></span>
                            <span class="pagination-links" <?php
                        if ((int) $num_of_pages <= 1) {
                            echo 'style="display:none;"';
                        }
                        ?>>
                                <?php if ($paged == '1') { ?>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                                <?php } else { ?>
                                    <a class="first-page button" href="<?php echo '?page=mlw_quiz_list&paged=1&s=' . $search; ?>" title="<?php esc_attr_e('Go to the first page', 'quiz-master-next'); ?>">&laquo;</a>
                                    <a class="prev-page button" href="<?php echo '?page=mlw_quiz_list&paged=' . $prev_page . '&s=' . $search; ?>" title="<?php esc_attr_e('Go to the previous page', 'quiz-master-next'); ?>">&lsaquo;</a>
                                <?php } ?>
                                <span class="paging-input">
                                    <span class="total-pages"><?php echo $paged; ?></span>
                                    <?php _e('of', 'quiz-master-next'); ?>
                                    <span class="total-pages"><?php echo $num_of_pages; ?></span>
                                </span>
                                <?php if ($paged == $num_of_pages) { ?>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                                <?php } else { ?>
                                    <a class="next-page button" href="<?php echo '?page=mlw_quiz_list&paged=' . $next_page . '&s=' . $search; ?>" title="<?php esc_attr_e('Go to the next page', 'quiz-master-next'); ?>">&rsaquo;</a>
                                    <a class="last-page button" href="<?php echo '?page=mlw_quiz_list&paged=' . $num_of_pages . '&s=' . $search; ?>" title="<?php esc_attr_e('Go to the last page', 'quiz-master-next'); ?>">&raquo;</a>
                                <?php } ?>
                            </span>
                        </div>
                    </div>
                    <table class="widefat">
                        <?php
                        $orderby_slug = '&orderby=title&order=asc';
                        $orderby_date_slug = '&orderby=date&order=asc';
                        $orderby_class = $orderby_date_class = 'sortable desc';
                        //Title order
                        if (isset($_GET['orderby']) && $_GET['orderby'] === 'title') {
                            if (isset($_GET['order']) && $_GET['order'] === 'asc') {
                                $orderby_slug = '&orderby=title&order=desc';
                                $orderby_class = 'sorted asc';
                            } else if (isset($_GET['order']) && $_GET['order'] === 'desc') {
                                $orderby_slug = '&orderby=title&order=asc';
                                $orderby_class = 'sorted desc';
                            }
                        } else if (isset($_GET['orderby']) && $_GET['orderby'] === 'date') {
                            if (isset($_GET['order']) && $_GET['order'] === 'asc') {
                                $orderby_date_slug = '&orderby=date&order=desc';
                                $orderby_date_class = 'sorted asc';
                            } else if (isset($_GET['order']) && $_GET['order'] === 'desc') {
                                $orderby_date_slug = '&orderby=date&order=asc';
                                $orderby_date_class = 'sorted desc';
                            }
                        }
                        ?>
                        <thead>
                            <tr>
                                <td class="manage-column column-cb check-column" id="cb"><input type="checkbox" name="delete-all-shortcodes-1" id="delete-all-shortcodes-1" value="0"></td>
                                <th class="<?php echo $orderby_class; ?>">
                                    <?php
                                    $paged_slug = isset($_GET['paged']) && $_GET['paged'] != '' ? '&paged='.esc_attr($_GET['paged']) : '';
                                    $searched_slug = isset($_GET['s']) && $_GET['s'] != ''? '&s='.esc_attr($_GET['s']) : '';
                                    $sorting_url = '?page=mlw_quiz_list' . $paged_slug . $searched_slug;
                                    ?>
                                    <a href="<?php echo $sorting_url . $orderby_slug; ?>">
                                        <span><?php esc_html_e('Title', 'quiz-master-next'); ?></span>
                                        <span class="sorting-indicator"></span>
                                    </a>
                                </th>
                                <th><?php esc_html_e('Shortcode', 'quiz-master-next'); ?></th>
                                <th><?php esc_html_e('Views', 'quiz-master-next'); ?></th>
                                <th><?php esc_html_e('Participants', 'quiz-master-next'); ?></th>
                                <th class="<?php echo $orderby_date_class; ?>">
                                    <a href="<?php echo $sorting_url . $orderby_date_slug; ?>">
                                        <span><?php esc_html_e('Last Modified', 'quiz-master-next'); ?></span>
                                        <span class="sorting-indicator"></span>
                                    </a>                                                            
                                </th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php
                            if($quiz_json_array){
                                foreach ($quiz_json_array as $key => $single_arr) { ?>
                                    <tr class="qsm-quiz-row" data-id="<?php echo $single_arr['id']; ?>">
                                        <th class="check-column">
                                            <input type="checkbox" class="chk_remove_all" name="chk_remove_all[]" id="chk_remove_all" value="<?php echo $single_arr['id']; ?>">
                                        </th>
                                        <td class="post-title column-title">
                                            <a class="row-title" href="admin.php?page=mlw_quiz_options&&quiz_id=<?php echo $single_arr['id']; ?>" aria-label="<?php echo $single_arr['name']; ?>">
                                                <?php echo $single_arr['name']; ?> <b style="color: #222; text-transform: capitalize;"><?php echo $single_arr['post_status'] != 'publish' ? '— ' . $single_arr['post_status'] : ''; ?></b>
                                            </a>
                                            <div class="row-actions">
                                                <a class="qsm-action-link" href="admin.php?page=mlw_quiz_options&&quiz_id=<?php echo $single_arr['id']; ?>"><?php _e('Edit', 'quiz-master-next'); ?></a> |                                                
                                                <a class="qsm-action-link qsm-action-link-duplicate" href="#"><?php _e('Duplicate', 'quiz-master-next'); ?></a> |
                                                <a class="qsm-action-link qsm-action-link-delete" href="#"><?php _e('Delete', 'quiz-master-next'); ?></a> |
                                                <a class="qsm-action-link" href="admin.php?page=mlw_quiz_results&quiz_id=<?php echo $single_arr['id']; ?>"><?php _e('View Results', 'quiz-master-next'); ?></a> |
                                                <a class="qsm-action-link" target="_blank" href="<?php echo $single_arr['link']; ?>"><?php _e('Preview', 'quiz-master-next'); ?></a>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="#" class="qsm-list-shortcode-view">
                                                <span class="dashicons dashicons-welcome-view-site"></span>
                                            </a>
                                            <div class="sc-content sc-embed">[qsm quiz=<?php echo $single_arr['id']; ?>]</div>                                            
                                            <div class="sc-content sc-link">[qsm_link id=<?php echo $single_arr['id']; ?>]<?php _e('Click here', 'quiz-master-next'); ?>[/qsm_link]</div>
                                        </td>
                                        <td>
                                            <?php echo $single_arr['views']; ?>
                                            <div class="row-actions">
                                                <a class="qsm-action-link qsm-action-link-reset" href="#"><?php _e('Reset', 'quiz-master-next'); ?></a>
                                            </div>
                                        </td>
                                        <td class="comments column-comments" style="text-align: left;">
                                            <span class="post-com-count post-com-count-approved">
                                                <span class="comment-count-approved" aria-hidden="true"><?php echo $single_arr['taken']; ?></span>
                                                <span class="screen-reader-text"><?php echo $single_arr['taken'] . __('Participants','quiz-master-next'); ?> </span>
                                            </span>                                        
                                        </td>
                                        <td>
                                            <abbr title="<?php echo $single_arr['lastActivityDateTime']; ?>"><?php echo $single_arr['lastActivity']; ?></abbr>
                                        </td>
                                    </tr>
                                <?php
                                }

                            }else{ ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">
                                        <?php _e('No Quiz found!', 'quiz-master-next'); ?>
                                    </td>
                                </tr>
                            <?php                         
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="manage-column column-cb check-column" id="cb"><input type="checkbox" name="delete-all-shortcodes-2" id="delete-all-shortcodes-2" value="0"></td>
                                <th class="<?php echo $orderby_class; ?>">
                                    <a href="<?php echo $sorting_url . $orderby_slug; ?>">
                                        <span><?php esc_html_e('Title', 'quiz-master-next'); ?></span>
                                        <span class="sorting-indicator"></span>
                                    </a>
                                </th>
                                <th><?php esc_html_e('Shortcode', 'quiz-master-next'); ?></th>
                                <th><?php esc_html_e('Views', 'quiz-master-next'); ?></th>
                                <th><?php esc_html_e('Participants', 'quiz-master-next'); ?></th>
                                <th class="<?php echo $orderby_date_class; ?>">
                                    <a href="<?php echo $sorting_url . $orderby_date_slug; ?>">
                                        <span><?php esc_html_e('Last Modified', 'quiz-master-next'); ?></span>
                                        <span class="sorting-indicator"></span>
                                    </a>                                                            
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="tablenav bottom">
                        <select name="qsm-ql-action-bottom">
                            <option selected="selected" value="none"><?php _e('Bulk Actions', 'quiz-master-next'); ?></option>
                            <option value="delete_pr"><?php _e('Delete Permanently', 'quiz-master-next'); ?></option>                        
                        </select>
                        <input id="take_action" name="take_action" class="button action" type="submit" value="<?php esc_attr_e('Apply', 'quiz-master-next'); ?>" >                    
                        <div class="tablenav-pages">                  
                            <span class="displaying-num"><?php echo number_format_i18n($total) . ' ' . sprintf(_n('item', 'items', $total), number_format_i18n($total)); ?></span>
                            <span class="pagination-links" <?php
                        if ((int) $num_of_pages <= 1) {
                            echo 'style="display:none;"';
                        }
                        ?>>
                                <?php if ($paged == '1') { ?>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                                <?php } else { ?>
                                    <a class="first-page button" href="<?php echo '?page=mlw_quiz_list&paged=1&s=' . $search; ?>" title="<?php esc_attr_e('Go to the first page', 'quiz-master-next'); ?>">&laquo;</a>
                                    <a class="prev-page button" href="<?php echo '?page=mlw_quiz_list&paged=' . $prev_page . '&s=' . $search; ?>" title="<?php esc_attr_e('Go to the previous page', 'quiz-master-next'); ?>">&lsaquo;</a>
                                <?php } ?>
                                <span class="paging-input">
                                    <span class="total-pages"><?php echo $paged; ?></span>
                                    <?php _e('of', 'quiz-master-next'); ?>
                                    <span class="total-pages"><?php echo $num_of_pages; ?></span>
                                </span>
                                <?php if ($paged == $num_of_pages) { ?>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                                <?php } else { ?>
                                    <a class="next-page button" href="<?php echo '?page=mlw_quiz_list&paged=' . $next_page . '&s=' . $search; ?>" title="<?php esc_attr_e('Go to the next page', 'quiz-master-next'); ?>">&rsaquo;</a>
                                    <a class="last-page button" href="<?php echo '?page=mlw_quiz_list&paged=' . $num_of_pages . '&s=' . $search; ?>" title="<?php esc_attr_e('Go to the last page', 'quiz-master-next'); ?>">&raquo;</a>
                                <?php } ?>
                            </span>
                        </div>
                    </div>
                </div>                
            </div>
        </form>        

        <!-- Popup for resetting stats -->
        <div class="qsm-popup qsm-popup-slide" id="modal-1" aria-hidden="true">
            <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
                <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                    <header class="qsm-popup__header">
                        <h2 class="qsm-popup__title" id="modal-1-title"><?php _e('Reset stats for this quiz?', 'quiz-master-next'); ?></h2>
                        <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                    </header>
                    <main class="qsm-popup__content" id="modal-1-content">
                        <p><?php _e('Are you sure you want to reset the stats to 0? All views and taken stats for this quiz will be reset. This is permanent and cannot be undone.', 'quiz-master-next'); ?></p>
                        <form action="" method="post" id="reset_quiz_form">
                            <?php wp_nonce_field('qsm_reset_stats', 'qsm_reset_stats_nonce'); ?>
                            <input type="hidden" id="reset_quiz_id" name="reset_quiz_id" value="0" />
                        </form>
                    </main>
                    <footer class="qsm-popup__footer">
                        <button id="reset-stats-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Reset All Stats For Quiz', 'quiz-master-next'); ?></button>
                        <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
                    </footer>
                </div>
            </div>
        </div>

        <!-- Popup for new quiz -->        
        <?php echo qsm_create_new_quiz_wizard(); ?>

        <!-- Popup for duplicate quiz -->
        <div class="qsm-popup qsm-popup-slide" id="modal-4" aria-hidden="true">
            <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
                <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-4-title">
                    <header class="qsm-popup__header">
                        <h2 class="qsm-popup__title" id="modal-4-title"><?php _e('Duplicate', 'quiz-master-next'); ?></h2>
                        <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                    </header>
                    <main class="qsm-popup__content" id="modal-4-content">
                        <form action='' method='post' id="duplicate-quiz-form">
                            <label for="duplicate_questions"><?php _e('Duplicate questions also?', 'quiz-master-next'); ?></label><input type="checkbox" name="duplicate_questions" id="duplicate_questions"/><br />
                            <br />
                            <label for="duplicate_new_quiz_name"><?php _e('Name Of New Quiz Or Survey:', 'quiz-master-next'); ?></label><input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" />
                            <input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
                            <?php wp_nonce_field('qsm_duplicate_quiz', 'qsm_duplicate_quiz_nonce'); ?>
                        </form>
                    </main>
                    <footer class="qsm-popup__footer">
                        <button id="duplicate-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Duplicate', 'quiz-master-next'); ?></button>
                        <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
                    </footer>
                </div>
            </div>
        </div>

        <!-- Popup for delete quiz -->
        <div class="qsm-popup qsm-popup-slide" id="modal-5" aria-hidden="true">
            <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
                <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
                    <header class="qsm-popup__header">
                        <h2 class="qsm-popup__title" id="modal-5-title"><?php _e('Delete', 'quiz-master-next'); ?></h2>
                        <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                    </header>
                    <main class="qsm-popup__content" id="modal-5-content">
                        <form action='' method='post' id="delete-quiz-form">
                            <h3><b><?php _e('Are you sure you want to delete this quiz or survey?', 'quiz-master-next'); ?></b></h3>
                            <label>
                                <input type="checkbox" value="1" name="qsm_delete_question_from_qb" checked="checked" /> <?php _e('Delete question from question bank?', 'quiz-master-next'); ?>
                            </label>
                            <?php wp_nonce_field('qsm_delete_quiz', 'qsm_delete_quiz_nonce'); ?>
                            <input type='hidden' id='delete_quiz_id' name='delete_quiz_id' value='' />
                            <input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />
                        </form>
                    </main>
                    <footer class="qsm-popup__footer">
                        <button id="delete-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Delete', 'quiz-master-next'); ?></button>
                        <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
                    </footer>
                </div>
            </div>
        </div>

        <!-- Popup for export import upsell -->
        <div class="qsm-popup qsm-popup-slide" id="modal-export-import" aria-hidden="true">
            <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
                <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
                    <header class="qsm-popup__header">
                        <h2 class="qsm-popup__title" id="modal-5-title"><?php _e('Extend QSM', 'quiz-master-next'); ?></h2>
                        <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                    </header>
                    <main class="qsm-popup__content" id="modal-5-content">                      
                        <h3><b><?php _e('Export functionality is provided as Premium addon.', 'quiz-master-next'); ?></b></h3>                                                  
                    </main>
                    <footer class="qsm-popup__footer">
                        <a style="color: white;    text-decoration: none;" href="https://quizandsurveymaster.com/downloads/export-import/" target="_blank" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Buy Now', 'quiz-master-next'); ?></a>
                        <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
                    </footer>
                </div>
            </div>
        </div>

        <!-- Popup for delete quiz -->
        <div class="qsm-popup qsm-popup-slide" id="modal-6" aria-hidden="true">
            <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
                <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
                    <header class="qsm-popup__header">
                        <h2 class="qsm-popup__title" id="modal-5-title"><?php _e('Shortcode', 'quiz-master-next'); ?></h2>
                        <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                    </header>
                    <main class="qsm-popup__content" id="modal-5-content">
                        <div class="qsm-row" style="margin-bottom: 30px;">
                            <lable><?php _e('Embed Shortcode','quiz-master-next'); ?></lable>
                            <input type="text" value="" id="sc-shortcode-model-text" style="width: 72%;padding: 5px;">
                            <button class="button button-primary" id="sc-copy-shortcode"><span class="dashicons dashicons-admin-page"></span></button>
                        </div>
                        <div class="qsm-row">
                            <lable><?php _e('Link Shortcode','quiz-master-next'); ?></lable>
                            <input type="text" value="" id="sc-shortcode-model-text-link" style="width: 72%;padding: 5px;">
                            <button class="button button-primary" id="sc-copy-shortcode-link"><span class="dashicons dashicons-admin-page"></span></button>
                        </div>                                                
                    </main>
                </div>
            </div>
        </div>

        <!-- Templates -->
        <script type="text/template" id="tmpl-no-quiz">
            <div class="qsm-no-quiz-wrapper">           
                <span class="dashicons dashicons-format-chat"></span>
                <h2><?php _e('You do not have any quizzes or surveys yet', 'quiz-master-next'); ?></h2>
                <div class="buttons">
                    <a class="button button-primary button-hero qsm-wizard-noquiz" href="#"><?php _e('Create New Quiz/Survey', 'quiz-master-next'); ?></a>
                    <a class="button button-secondary button-hero" href="https://quizandsurveymaster.com/docs/" target="_blank"><span class="dashicons dashicons-admin-page"></span> <?php _e('Read Documentation', 'quiz-master-next'); ?></a>
                </div>   
                <h3><?php _e('or watch the below video to get started', 'quiz-master-next'); ?></h3>
                <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/coE5W_WB-48" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </script>

        <script type="text/template" id="tmpl-quiz-row">
            <tr class="qsm-quiz-row" data-id="{{ data.id }}">
            <td class="post-title column-title">
            <a class="row-title" href="admin.php?page=mlw_quiz_options&&quiz_id={{ data.id }}" aria-label="{{ data.name }}">{{ data.name }} <b style="color: #222; text-transform: capitalize;">{{ data.post_status }}</b></a><a target="_blank" class="quiz-preview-link" href="{{ data.link }}"><span class="dashicons dashicons-external"></span></a>
            <div class="row-actions">
            <a class="qsm-action-link" href="admin.php?page=mlw_quiz_options&&quiz_id={{ data.id }}"><?php _e('Edit', 'quiz-master-next'); ?></a> |
            <a class="qsm-action-link" href="post.php?post={{ data.postID }}&action=edit"><?php _e('Post Settings', 'quiz-master-next'); ?></a> |
            <a class="qsm-action-link qsm-action-link-duplicate" href="#"><?php _e('Duplicate', 'quiz-master-next'); ?></a> |
            <a class="qsm-action-link qsm-action-link-delete" href="#"><?php _e('Delete', 'quiz-master-next'); ?></a> |
            <a class="qsm-action-link" target="_blank" href="{{ data.link }}"><?php _e('Preview', 'quiz-master-next'); ?></a>
            </div>
            </td>
            <td>
            <p class="sc-opener"><span class="dashicons dashicons-editor-contract"></span> Embed</p>
            <div class="sc-content">[qsm quiz={{ data.id }}]</div>
            <p class="sc-opener"><span class="dashicons dashicons-admin-links"></span> Link</p>
            <div class="sc-content">[qsm_link id={{ data.id }}]<?php _e('Click here', 'quiz-master-next'); ?>[/qsm_link]</div>
            </td>
            <td>
            {{ data.views }}/{{ data.taken }}
            <div class="row-actions">
            <a class="qsm-action-link qsm-action-link-reset" href="#"><?php _e('Reset', 'quiz-master-next'); ?></a> |
            <a class="qsm-action-link" href="admin.php?page=mlw_quiz_results&&quiz_id={{ data.id }}"><?php _e('Results', 'quiz-master-next'); ?></a>
            </div>
            </td>
            <td><abbr title="{{ data.lastActivityDateTime }}">{{ data.lastActivity }}</abbr></td>
            </tr>
        </script>
    </div>
    <?php
}

/**
* @since 7.0
* add per page option in screen option in Quiz list
* @global string $qsm_quiz_list_page
*/
function qsm_generate_quizzes_surveys_page_screen_options(){
    global $qsm_quiz_list_page;
    $screen = get_current_screen();

    // get out of here if we are not on our settings page
    if (!is_object($screen) || $screen->id != $qsm_quiz_list_page)
        return;

    $args = array(
        'label' => __('Number of items per page:', 'quiz-master-next'),
        'default' => 10,
        'option' => 'qsm_per_page'
    );
    add_screen_option('per_page', $args);
}

add_filter('set-screen-option', 'qsm_set_screen_option', 10, 3);
add_filter('set_screen_option_qsm_per_page', 'qsm_set_screen_option', 10, 3);
/**
 * @since 7.0
 * @param str $status
 * @param arr $option
 * @param str $value
 * @return str Save screen option value
 */
function qsm_set_screen_option( $status, $option, $value ){
    if ('qsm_per_page' == $option)
        return $value;
}
?>
