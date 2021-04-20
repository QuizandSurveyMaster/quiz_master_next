<?php
/**
 * File for the QMNQuizManager class
 *
 * @package QSM
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * This class generates the contents of the quiz shortcode
 *
 * @since 4.0.0
 */
class QMNQuizManager {

    protected $qsm_background_email;
    /**
     * Main Construct Function
     *
     * Call functions within class
     *
     * @since 4.0.0
     * @uses QMNQuizManager::add_hooks() Adds actions to hooks and filters
     * @return void
     */
    public function __construct() {
        $this->add_hooks();
    }

    /**
     * Add Hooks
     *
     * Adds functions to relavent hooks and filters
     *
     * @since 4.0.0
     * @return void
     */
    public function add_hooks() {
        add_shortcode('mlw_quizmaster', array($this, 'display_shortcode'));
        add_shortcode('qsm', array($this, 'display_shortcode'));
        add_shortcode('qsm_result', array($this, 'shortcode_display_result'));
        add_action('wp_ajax_qmn_process_quiz', array($this, 'ajax_submit_results'));
        add_action('wp_ajax_nopriv_qmn_process_quiz', array($this, 'ajax_submit_results'));
        add_action('wp_ajax_qsm_get_quiz_to_reload', array($this, 'qsm_get_quiz_to_reload'));
        add_action('wp_ajax_nopriv_qsm_get_quiz_to_reload', array($this, 'qsm_get_quiz_to_reload'));
        add_action('wp_ajax_qsm_get_question_quick_result', array($this, 'qsm_get_question_quick_result'));
        add_action('wp_ajax_nopriv_qsm_get_question_quick_result', array($this, 'qsm_get_question_quick_result'));
        //Upload file of file upload question type
        add_action('wp_ajax_qsm_upload_image_fd_question', array($this, 'qsm_upload_image_fd_question'));
        add_action('wp_ajax_nopriv_qsm_upload_image_fd_question', array($this, 'qsm_upload_image_fd_question'));

        //remove file of file upload question type
        add_action('wp_ajax_qsm_remove_file_fd_question', array($this, 'qsm_remove_file_fd_question'));
        add_action('wp_ajax_nopriv_qsm_remove_file_fd_question', array($this, 'qsm_remove_file_fd_question'));

        add_action('init', array($this, 'qsm_process_background_email'));
    }

    /**
     * @version 6.3.7
     * Upload file to server
     */
    public function qsm_upload_image_fd_question(){
        global $mlwQuizMasterNext;
        $question_id = isset($_POST['question_id']) ? sanitize_text_field($_POST['question_id']) : 0;
        $file_upload_type = $mlwQuizMasterNext->pluginHelper->get_question_setting($question_id, 'file_upload_type');
        $file_upload_limit = $mlwQuizMasterNext->pluginHelper->get_question_setting($question_id, 'file_upload_limit');
        $mimes = array();
        if($file_upload_type){
            $file_type_exp = explode(',', $file_upload_type);
            foreach ($file_type_exp as $value) {
                if($value == 'image'){
                    $mimes[] = 'image/jpeg';
                    $mimes[] = 'image/png';
                    $mimes[] = 'image/x-icon';
                    $mimes[] = 'image/gif';
                }else if($value == 'doc'){
                    $mimes[] = 'application/msword';
                    $mimes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                }else if($value == 'excel'){
                    $mimes[] = 'application/excel, application/vnd.ms-excel, application/x-excel, application/x-msexcel';
                    $mimes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                }else{
                    $mimes[] = $value;
                }
            }
        }
        $json = array();        
        $file_name = sanitize_file_name( $_FILES["file"]["name"] );
        $validate_file = wp_check_filetype( $file_name );        
        if ( isset( $validate_file['type'] ) && in_array($validate_file['type'], $mimes)) {
            if($_FILES["file"]['size'] >= $file_upload_limit * 1024 * 1024){
                $json['type']= 'error';
                $json['message'] = __('File is too large. File must be less than ', 'quiz-master-next') . $file_upload_limit . ' MB';
                echo json_encode($json);
                exit;
            }
            $upload_dir = wp_upload_dir();
            $datafile = $_FILES["file"]["tmp_name"];
            //$file_name = $_FILES["file"]["name"];
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            //remove white space between file name
            $file_name = str_replace(' ', '-', $file_name);
            $rawBaseName = 'qsmfileupload_' . md5( date('Y-m-d H:i:s') ) . '_' . pathinfo($file_name, PATHINFO_FILENAME);
            $new_fname = $rawBaseName . '.' . $extension;
            $file = $upload_dir['path'] . '/' . $new_fname;
            $file_url = $upload_dir['url'] . '/' . $new_fname;
            $counter = 1;                        
            if(file_exists($file)){
                while (file_exists($file)) {
                    $new_fname = $rawBaseName . '-' . $counter . '.' . $extension;
                    $file = $upload_dir['path'] . '/' . $new_fname;
                    $file_url = $upload_dir['url'] . '/' . $new_fname;
                    $counter++;
                }
            }
            if (!move_uploaded_file($datafile, $file)) {
                $json['type']= 'error';
                $json['message'] = __('File not uploaded', 'quiz-master-next');
                echo json_encode($json);
            }else{
                // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $upload_dir['url'] . '/' . basename( $file ), 
                    'post_mime_type' => $validate_file['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $file, 0 );
                if( $attach_id ){
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );                    
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                }
                $json['type']= 'success';
                $json['message'] = __( 'File uploaded successfully', 'quiz-master-next' );
                $json['file_url'] = $file_url;
                $json['file_path'] = $new_fname;
                echo json_encode($json);
            }
        }else{
            $json['type']= 'error';
            $json['message'] = __('File type is not supported', 'quiz-master-next');
            echo json_encode($json);
        }
        exit;
    }

    /**
     * @since 6.3.7
     * Remove the uploaded image
     */
    public function qsm_remove_file_fd_question(){
        $file_url = isset($_POST['file_url']) ? sanitize_text_field($_POST['file_url']) : '';
        $upload_dir = wp_upload_dir();
        $uploaded_path = $upload_dir['path'];
        if($file_url && stristr( $file_url, 'qsmfileupload_' ) && file_exists( $uploaded_path . '/' . $file_url ) ){
            $attachment_url = $upload_dir['url'] . '/' . $file_url;
            $attachment_id = $this->qsm_get_attachment_id_from_url($attachment_url);
            wp_delete_file( $uploaded_path . '/' . $file_url );
            wp_delete_attachment( $attachment_id );
            $json['type']= 'success';
            $json['message'] = __( 'File removed successfully', 'quiz-master-next' );
            echo json_encode($json);
            exit;
        }
        $json['type']= 'error';
        $json['message'] = __( 'File not removed', 'quiz-master-next' );
        echo json_encode($json);
        exit;
    }


    /**
     * @version 6.3.2
     * Get question quick result
     */
    public function qsm_get_question_quick_result(){
        global $wpdb;
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $answer = isset( $_POST['answer'] ) ? stripslashes_deep( $_POST['answer'] ) : '';
        $question_array = $wpdb->get_row( $wpdb->prepare( "SELECT answer_array, question_answer_info FROM {$wpdb->prefix}mlw_questions WHERE question_id = (%d)", $question_id ), 'ARRAY_A' );
        $answer_array = unserialize($question_array['answer_array']);
        $correct_info_text = isset( $question_array['question_answer_info'] ) ? html_entity_decode( $question_array['question_answer_info'] ) : '';
        $show_correct_info = isset( $_POST['show_correct_info'] ) ? sanitize_text_field( $_POST['show_correct_info'] ) : 0;
        $got_ans = false;
        $correct_answer = false;
        if($answer_array && $got_ans === false){
            foreach ($answer_array as $key => $value) {             
                if( esc_html( $value[0]) == esc_html($answer) && $value[2] == 1 ){
                    $got_ans = true;
                    $correct_answer = true;
                    break;
                }
            }
        }
        if( $show_correct_info == 2 ){
            $got_ans = true;
        }
        echo wp_json_encode(
                array(
                    'success' => $correct_answer ? 'correct' : 'incorrect',
                    'message' => $show_correct_info && $got_ans ?  '<b>'. __('Correct Info: ', 'quiz-master-next') .'</b>' . do_shortcode($correct_info_text) : ''
                )
        );
	wp_die();    
    }

    /**
     * Generates Content For Quiz Shortcode
     *
     * Generates the content for the [mlw_quizmaster] shortcode
     *
     * @since 4.0.0
     * @param array $atts The attributes passed from the shortcode.
     * @uses QMNQuizManager:load_questions() Loads questions
     * @uses QMNQuizManager:create_answer_array() Prepares answers
     * @uses QMNQuizManager:display_quiz() Generates and prepares quiz page
     * @uses QMNQuizManager:display_results() Generates and prepares results page
     * @return string The content for the shortcode
     */
    public function display_shortcode($atts) {
        extract(shortcode_atts(array(
            'quiz' => 0,
            'question_amount' => 0,
                        ), $atts));

        ob_start();
        if(isset($_GET['result_id']) && $_GET['result_id'] != ''){
            global $wpdb;
            global $mlwQuizMasterNext;
            wp_enqueue_style('qmn_quiz_common_style', plugins_url('../../css/common.css', __FILE__));
            wp_enqueue_style('dashicons');
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script('qsm_quiz', plugins_url('../../js/qsm-quiz.js', __FILE__), array('wp-util', 'underscore', 'jquery', 'jquery-ui-tooltip'), $mlwQuizMasterNext->version);
            wp_enqueue_script( 'math_jax', '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML' );
            $result_unique_id =sanitize_text_field($_GET['result_id']);
            $query = $wpdb->prepare("SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE unique_id = %s",$result_unique_id);
            $result = $wpdb->get_row($query,ARRAY_A);
            if( !empty($result) && isset($result['result_id']) ){
                $result_id = $result['result_id'];
                $return_display = do_shortcode( '[qsm_result id="'. $result_id .'"]' );
                $return_display = str_replace('%FB_RESULT_ID%', $result_unique_id, $return_display);
            }else{
                $return_display = 'Result id is wrong!';
            }
            $return_display .= ob_get_clean();
        }else{
            global $wpdb;
            global $mlwQuizMasterNext;
            global $qmn_allowed_visit;
            global $qmn_json_data;
            $qmn_json_data = array();
            $qmn_allowed_visit = true;
            $success = $mlwQuizMasterNext->pluginHelper->prepare_quiz($quiz);
            if (false === $success) {
                return __('It appears that this quiz is not set up correctly', 'quiz-master-next');
            }
            $question_amount = intval($question_amount);

            // Legacy variable.
            global $mlw_qmn_quiz;
            $mlw_qmn_quiz = $quiz;

            $return_display = '';
            $qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();

            // If quiz options isn't found, stop function.
            if (is_null($qmn_quiz_options) || empty($qmn_quiz_options->quiz_name)) {
                return __('It appears that this quiz is not set up correctly', 'quiz-master-next');
            }

            // Loads Quiz Template.
            // The quiz_stye is misspelled because it has always been misspelled and fixing it would break many sites :(.
            if ('default' == $qmn_quiz_options->theme_selected) {
                $return_display .= '<style type="text/css">' . htmlspecialchars_decode($qmn_quiz_options->quiz_stye) . '</style>';
                wp_enqueue_style('qmn_quiz_style', plugins_url('../../css/qmn_quiz.css', __FILE__));
            } else {
                $registered_template = $mlwQuizMasterNext->pluginHelper->get_quiz_templates($qmn_quiz_options->theme_selected);
                // Check direct file first, then check templates folder in plugin, then check templates file in theme.
                // If all fails, then load custom styling instead.
                if ($registered_template && file_exists($registered_template['path'])) {
                    wp_enqueue_style('qmn_quiz_template', $registered_template['path'], array(), $mlwQuizMasterNext->version);
                } elseif ($registered_template && file_exists(plugin_dir_path(__FILE__) . '../../templates/' . $registered_template['path'])) {
                    wp_enqueue_style('qmn_quiz_template', plugins_url('../../templates/' . $registered_template['path'], __FILE__), array(), $mlwQuizMasterNext->version);
                } elseif ($registered_template && file_exists(get_stylesheet_directory_uri() . '/templates/' . $registered_template['path'])) {
                    wp_enqueue_style('qmn_quiz_template', get_stylesheet_directory_uri() . '/templates/' . $registered_template['path'], array(), $mlwQuizMasterNext->version);
                } else {
                    echo "<style type='text/css'>{$qmn_quiz_options->quiz_stye}</style>";
                }
            }
            wp_enqueue_style('qmn_quiz_animation_style', plugins_url('../../css/animate.css', __FILE__));
            wp_enqueue_style('qmn_quiz_common_style', plugins_url('../../css/common.css', __FILE__));
            wp_enqueue_style('dashicons');

            // Starts to prepare variable array for filters.
            $qmn_array_for_variables = array(
                'quiz_id' => $qmn_quiz_options->quiz_id,
                'quiz_name' => $qmn_quiz_options->quiz_name,
                'quiz_system' => $qmn_quiz_options->system,
                'user_ip' => $this->get_user_ip(),
            );

            $return_display .= "<script>
                            if (window.qmn_quiz_data === undefined) {
                                    window.qmn_quiz_data = new Object();
                            }
                    </script>";
			$qpages = array();
			$qpages_arr = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('qpages', array());
			if (!empty($qpages_arr)) {
				foreach ($qpages_arr as $key => $qpage) {
					unset($qpage['questions']);
					$qpages[$qpage['id']] = $qpage;
				}
			}
            $qmn_json_data = array(
				'quiz_id' => $qmn_array_for_variables['quiz_id'],
				'quiz_name' => $qmn_array_for_variables['quiz_name'],
				'disable_answer' => $qmn_quiz_options->disable_answer_onselect,
				'ajax_show_correct' => $qmn_quiz_options->ajax_show_correct,
				'progress_bar' => $qmn_quiz_options->progress_bar,
				'contact_info_location' => $qmn_quiz_options->contact_info_location,
				'qpages' => $qpages,
				'skip_validation_time_expire' => $qmn_quiz_options->skip_validation_time_expire,
				'timer_limit_val' => $qmn_quiz_options->timer_limit,
				'disable_scroll_next_previous_click' => $qmn_quiz_options->disable_scroll_next_previous_click,
				'enable_result_after_timer_end' => isset($qmn_quiz_options->enable_result_after_timer_end) ? $qmn_quiz_options->enable_result_after_timer_end : '',
				'enable_quick_result_mc' => isset($qmn_quiz_options->enable_quick_result_mc) ? $qmn_quiz_options->enable_quick_result_mc : '',
                'end_quiz_if_wrong' => isset($qmn_quiz_options->end_quiz_if_wrong) ? $qmn_quiz_options->end_quiz_if_wrong : '',
                'form_disable_autofill' => isset($qmn_quiz_options->form_disable_autofill) ? $qmn_quiz_options->form_disable_autofill : '',
				'enable_quick_correct_answer_info' => isset($qmn_quiz_options->enable_quick_correct_answer_info) ? $qmn_quiz_options->enable_quick_correct_answer_info : 0,
				'quick_result_correct_answer_text' => $qmn_quiz_options->quick_result_correct_answer_text,
				'quick_result_wrong_answer_text' => $qmn_quiz_options->quick_result_wrong_answer_text,
			);

            $return_display = apply_filters('qmn_begin_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables);

            // Checks if we should be showing quiz or results page.
            if ($qmn_allowed_visit && !isset($_POST["complete_quiz"]) && !empty($qmn_quiz_options->quiz_name)) {
                $return_display .= $this->display_quiz($qmn_quiz_options, $qmn_array_for_variables, $question_amount);
            } elseif (isset($_POST["complete_quiz"]) && 'confirmation' == $_POST["complete_quiz"] && $_POST["qmn_quiz_id"] == $qmn_array_for_variables["quiz_id"]) {
                $return_display .= $this->display_results($qmn_quiz_options, $qmn_array_for_variables);
            }

            $qmn_filtered_json = apply_filters('qmn_json_data', $qmn_json_data, $qmn_quiz_options, $qmn_array_for_variables);

            $return_display .= '<script>
                            window.qmn_quiz_data["' . $qmn_json_data["quiz_id"] . '"] = ' . json_encode($qmn_filtered_json) . '
                    </script>';

            $return_display .= ob_get_clean();
            $return_display = apply_filters('qmn_end_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables);
        }
        return $return_display;
    }

    public function shortcode_display_result($atts){
        extract(shortcode_atts(array(
            'id' => 0,
                        ), $atts));
        ob_start();
        if($id == 0){
            $id = (int) isset($_GET['result_id']) ? sanitize_text_field( $_GET['result_id'] ) : 0;
        }
        if( $id && is_numeric($id) ){
            global $wpdb;
            $result_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $id), ARRAY_A);
            if( $result_data ){
                wp_enqueue_style('qmn_quiz_common_style', plugins_url('../../css/common.css', __FILE__));
                wp_enqueue_style('dashicons');
                wp_enqueue_script( 'math_jax', '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML' );
                $quiz_result = unserialize($result_data['quiz_results']);
                $response_data = array(
                    'quiz_id' => $result_data['quiz_id'],
                    'quiz_name' => $result_data['quiz_name'],
                    'quiz_system' => $result_data['quiz_system'],
                    'quiz_payment_id' => '',
                    'user_ip' => $result_data['user_ip'],
                    'user_name' => $result_data['name'],
                    'user_business' => $result_data['business'],
                    'user_email' => $result_data['email'],
                    'user_phone' => $result_data['phone'],
                    'user_id' => $result_data['user'],
                    'timer' => 0,
                    'time_taken' => $result_data['time_taken'],
                    'contact' => $quiz_result['contact'],
                    'total_points' => $result_data['point_score'],
                    'total_score' => $result_data['correct_score'],
                    'total_correct' => $result_data['correct'],
                    'total_questions' => $result_data['total'],
                    'question_answers_array' => $quiz_result[1],
                    'comments' => ''
                );
                $data = QSM_Results_Pages::generate_pages($response_data);
                echo htmlspecialchars_decode($data['display']);
            } else {
                echo _e('Invalid result id!', 'quiz-master-next');
            }
        }else{
            echo _e('Invalid result id!', 'quiz-master-next');
        }
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Loads Questions
     *
     * Retrieves the questions from the database
     *
     * @since 4.0.0
     * @param int   $quiz_id The id for the quiz.
     * @param array $quiz_options The database row for the quiz.
     * @param bool  $is_quiz_page If the page being loaded is the quiz page or not.
     * @param int   $question_amount The amount of questions entered using the shortcode attribute.
     * @return array The questions for the quiz
     * @deprecated 5.2.0 Use new class: QSM_Questions instead
     */
    public function load_questions($quiz_id, $quiz_options, $is_quiz_page, $question_amount = 0) {

        // Prepare variables.
        global $wpdb;
        global $mlwQuizMasterNext;
        $questions = array();
        $order_by_sql = 'ORDER BY question_order ASC';
        $limit_sql = '';

        // Checks if the questions should be randomized.
		$cat_query = '';
        if (1 == $quiz_options->randomness_order || 2 == $quiz_options->randomness_order) {
            $order_by_sql = 'ORDER BY rand()';
			$categories = isset($quiz_options->randon_category) ? $quiz_options->randon_category : '';
			if($categories){
				$exploded_arr = explode(',', $quiz_options->randon_category);
				$cat_str = "'" . implode ( "', '", $exploded_arr ) . "'";
				$cat_query = " AND category IN ( $cat_str ) ";
			}
        }

        // Check if we should load all questions or only a selcted amount.
        if ($is_quiz_page && ( 0 != $quiz_options->question_from_total || 0 !== $question_amount )) {
            if (0 !== $question_amount) {
                $limit_sql = " LIMIT $question_amount";
            } else {
                $limit_sql = ' LIMIT ' . intval($quiz_options->question_from_total);
            }
        }

        // If using newer pages system from 5.2.
        $pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('pages', array());
        // Get all question IDs needed.
        $total_pages = count($pages);
        if ($total_pages > 0) {
            for ($i = 0; $i < $total_pages; $i++) {
                foreach ($pages[$i] as $question) {
                    $question_ids[] = intval($question);
                }
            }
	    $question_ids = apply_filters('qsm_load_questions_ids', $question_ids, $quiz_id, $quiz_options);
            $question_sql = implode(', ', $question_ids);
            $query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE question_id IN (%1s) %2s %3s %4s", $question_sql, $cat_query, $order_by_sql, $limit_sql );
            $questions = $wpdb->get_results( stripslashes($query));

            // If we are not using randomization, we need to put the questions in the order of the new question editor.
            // If a user has saved the pages in the question editor but still uses the older pagination options
            // Then they will make it here. So, we need to order the questions based on the new editor.
            if (1 != $quiz_options->randomness_order && 2 != $quiz_options->randomness_order) {
                $ordered_questions = array();
                foreach ($questions as $question) {
                    $key = array_search($question->question_id, $question_ids);
                    if (false !== $key) {
                        $ordered_questions[$key] = $question;
                    }
                }
                ksort($ordered_questions);
                $questions = $ordered_questions;
            }
        } else {
		$question_ids = apply_filters('qsm_load_questions_ids', array(), $quiz_id, $quiz_options);
		$question_sql = '';
		if (!empty($question_ids)) {
			$qids = implode(', ', $question_ids);
			$question_sql = " AND question_id IN ($qids) ";
		}
		$questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mlw_questions WHERE quiz_id=%d AND deleted=0 %1s %2s %3s", $quiz_id, $question_sql, $order_by_sql, $limit_sql));
        }
	$questions = apply_filters('qsm_load_questions_filter', $questions, $quiz_id, $quiz_options);
        // Returns an array of all the loaded questions.
        return $questions;
    }

    /**
     * Prepares Answers
     *
     * Prepares or creates the answer array for the quiz
     *
     * @since 4.0.0
     * @param array $questions The questions for the quiz.
     * @param bool  $is_ajax Pass true if this is an ajax call.
     * @return array The answers for the quiz
     * @deprecated 5.2.0 Use new class: QSM_Questions instead
     */
    public function create_answer_array($questions, $is_ajax = false) {

        // Load and prepare answer arrays.
        $mlw_qmn_answer_arrays = array();
        $question_list = array();
        foreach ($questions as $mlw_question_info) {
            $question_list[$mlw_question_info->question_id] = get_object_vars($mlw_question_info);
            if (is_serialized($mlw_question_info->answer_array) && is_array(@unserialize($mlw_question_info->answer_array))) {
                $mlw_qmn_answer_array_each = @unserialize($mlw_question_info->answer_array);
                $mlw_qmn_answer_arrays[$mlw_question_info->question_id] = $mlw_qmn_answer_array_each;
                $question_list[$mlw_question_info->question_id]["answers"] = $mlw_qmn_answer_array_each;
            } else {
                $mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
                $mlw_answer_array_correct[$mlw_question_info->correct_answer - 1] = 1;
                $mlw_qmn_answer_arrays[$mlw_question_info->question_id] = array(
                    array($mlw_question_info->answer_one, $mlw_question_info->answer_one_points, $mlw_answer_array_correct[0]),
                    array($mlw_question_info->answer_two, $mlw_question_info->answer_two_points, $mlw_answer_array_correct[1]),
                    array($mlw_question_info->answer_three, $mlw_question_info->answer_three_points, $mlw_answer_array_correct[2]),
                    array($mlw_question_info->answer_four, $mlw_question_info->answer_four_points, $mlw_answer_array_correct[3]),
                    array($mlw_question_info->answer_five, $mlw_question_info->answer_five_points, $mlw_answer_array_correct[4]),
                    array($mlw_question_info->answer_six, $mlw_question_info->answer_six_points, $mlw_answer_array_correct[5]));
                $question_list[$mlw_question_info->question_id]["answers"] = $mlw_qmn_answer_arrays[$mlw_question_info->question_id];
            }
        }
        if (!$is_ajax) {
            global $qmn_json_data;
            $qmn_json_data["question_list"] = $question_list;
        }
        return $mlw_qmn_answer_arrays;
    }

    /**
     * Generates Content Quiz Page
     *
     * Generates the content for the quiz page part of the shortcode
     *
     * @since 4.0.0
     * @param array $options The database row of the quiz.
     * @param array $quiz_data The array of results for the quiz.
     * @param int $question_amount The number of questions to load for quiz.
     * @uses QMNQuizManager:display_begin_section() Creates display for beginning section
     * @uses QMNQuizManager:display_questions() Creates display for questions
     * @uses QMNQuizManager:display_comment_section() Creates display for comment section
     * @uses QMNQuizManager:display_end_section() Creates display for end section
     * @return string The content for the quiz page section
     */
    public function display_quiz($options, $quiz_data, $question_amount) {

        global $qmn_allowed_visit;
        global $mlwQuizMasterNext;
        $quiz_display = '';
        $quiz_display = apply_filters('qmn_begin_quiz', $quiz_display, $options, $quiz_data);
        if (!$qmn_allowed_visit) {
            return $quiz_display;
        }
        wp_enqueue_script('json2');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_style('jquery-redmond-theme', plugins_url('../../css/jquery-ui.css', __FILE__));

        global $qmn_json_data;
        $qmn_json_data['error_messages'] = array(
            'email' => $options->email_error_text,
            'number' => $options->number_error_text,
            'incorrect' => $options->incorrect_error_text,
            'empty' => $options->empty_error_text,
        );

        wp_enqueue_script('progress-bar', plugins_url('../../js/progressbar.min.js', __FILE__));
        wp_enqueue_script( 'jquery-ui-slider-js', plugins_url('../../js/jquery-ui.js', __FILE__));
        wp_enqueue_script( 'jquery-ui-slider-rtl-js', plugins_url('../../js/jquery.ui.slider-rtl.js', __FILE__) );
        wp_enqueue_style( 'jquery-ui-slider-rtl-css', plugins_url('../../css/jquery.ui.slider-rtl.css', __FILE__) );
        wp_enqueue_script( 'jqueryui-touch-js', '//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js' );
        wp_enqueue_style('qsm_model_css', plugins_url('../../css/qsm-admin.css', __FILE__));
        wp_enqueue_script('qsm_model_js', plugins_url('../../js/micromodal.min.js', __FILE__));
        wp_enqueue_script('qsm_quiz', plugins_url('../../js/qsm-quiz.js', __FILE__), array('wp-util', 'underscore', 'jquery', 'jquery-ui-tooltip', 'progress-bar'), $mlwQuizMasterNext->version);
        wp_localize_script('qsm_quiz', 'qmn_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php'), 'multicheckbox_limit_reach' => __('Limit of choice is reached.', 'quiz-master-next'), 'out_of_text' => __(' out of ', 'quiz-master-next')));
        wp_enqueue_script( 'math_jax', '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML' );
        global $qmn_total_questions;
        $qmn_total_questions = 0;
        global $mlw_qmn_section_count;
        $mlw_qmn_section_count = 0;
        $auto_pagination_class = $options->pagination > 0 ? 'qsm_auto_pagination_enabled' : '';
        $quiz_display .= "<div class='qsm-quiz-container qmn_quiz_container mlw_qmn_quiz {$auto_pagination_class}'>";
        // Get quiz post based on quiz id
        $args = array(
            'posts_per_page' => 1,
            'post_type' => 'qsm_quiz',
            'meta_query' => array(
                array(
                    'key' => 'quiz_id',
                    'value' => $quiz_data['quiz_id'],
                    'compare' => '=',
                ),
            ),
        );
        $the_query = new WP_Query($args);

        // The Loop
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $quiz_display .= get_the_post_thumbnail(get_the_ID(),'full');
            }
            /* Restore original Post Data */
            wp_reset_postdata();
        }
        $quiz_display = apply_filters('qsm_display_before_form', $quiz_display, $options, $quiz_data);
        $quiz_display .= "<form name='quizForm{$quiz_data['quiz_id']}' id='quizForm{$quiz_data['quiz_id']}' action='".$_SERVER['REQUEST_URI']."' method='POST' class='qsm-quiz-form qmn_quiz_form mlw_quiz_form' novalidate  enctype='multipart/form-data'>";
        $quiz_display .= "<input type='hidden' name='qsm_hidden_questions' id='qsm_hidden_questions' value=''>";
        $quiz_display .= "<div id='mlw_error_message' class='qsm-error-message qmn_error_message_section'></div>";
        $quiz_display .= "<span id='mlw_top_of_quiz'></span>";
        $quiz_display = apply_filters('qmn_begin_quiz_form', $quiz_display, $options, $quiz_data);

        // If deprecated pagination setting is not used, use new system...
        $pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('pages', array());
        if (0 == $options->randomness_order && 0 == $options->question_from_total && 0 == $options->pagination && 0 !== count($pages)) {
            $quiz_display .= $this->display_pages($options, $quiz_data);
        } else {
            // ... else, use older system.
            $questions = $this->load_questions($quiz_data['quiz_id'], $options, true, $question_amount);
            $answers = $this->create_answer_array($questions);
            $quiz_display .= $this->display_begin_section($options, $quiz_data);
            $quiz_display = apply_filters('qmn_begin_quiz_questions', $quiz_display, $options, $quiz_data);
            $quiz_display .= $this->display_questions($options, $questions, $answers);
            $quiz_display = apply_filters('qmn_before_comment_section', $quiz_display, $options, $quiz_data);
            $quiz_display .= $this->display_comment_section($options, $quiz_data);
            $quiz_display = apply_filters('qmn_after_comment_section', $quiz_display, $options, $quiz_data);
            $quiz_display .= $this->display_end_section($options, $quiz_data);
        }
        $quiz_display = apply_filters('qmn_before_error_message', $quiz_display, $options, $quiz_data);
        $quiz_display .= "<div id='mlw_error_message_bottom' class='qsm-error-message qmn_error_message_section'></div>";
        $quiz_display .= "<input type='hidden' name='total_questions' id='total_questions' value='$qmn_total_questions'/>";
        $quiz_display .= "<input type='hidden' name='timer' id='timer' value='0'/>";
        $quiz_display .= "<input type='hidden' name='timer_ms' id='timer_ms' value='0'/>";
        $quiz_display .= "<input type='hidden' class='qmn_quiz_id' name='qmn_quiz_id' id='qmn_quiz_id' value='{$quiz_data['quiz_id']}'/>";
        $quiz_display .= "<input type='hidden' name='complete_quiz' value='confirmation' />";
        if (isset($_GET['payment_id']) && $_GET['payment_id'] != '') {
            $quiz_display .= "<input type='hidden' name='main_payment_id' value='" . $_GET['payment_id'] . "' />";
        }
        $quiz_display = apply_filters('qmn_end_quiz_form', $quiz_display, $options, $quiz_data);
        $quiz_display .= '</form>';
        $quiz_display .= '</div>';

        $quiz_display = apply_filters('qmn_end_quiz', $quiz_display, $options, $quiz_data);
        return $quiz_display;
    }

    /**
     * Creates the pages of content for the quiz/survey
     *
     * @since 5.2.0
     * @param array $options The settings for the quiz.
     * @param array $quiz_data The array of quiz data.
     * @return string The HTML for the pages
     */
    public function display_pages($options, $quiz_data) {
        global $mlwQuizMasterNext;
        global $qmn_json_data;
        ob_start();
        $pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('pages', array());
		$qpages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('qpages', array());
        $questions = QSM_Questions::load_questions_by_pages($options->quiz_id);
        $question_list = '';
        $contact_fields = QSM_Contact_Manager::load_fields();
        $animation_effect = isset($options->quiz_animation) && $options->quiz_animation != '' ? ' animated ' . $options->quiz_animation : '';
        $enable_pagination_quiz = isset($options->enable_pagination_quiz) && $options->enable_pagination_quiz == 1 ? true : false;
        if (count($pages) > 1 && (!empty($options->message_before) || ( 0 == $options->contact_info_location && $contact_fields ) )) {
            $qmn_json_data['first_page'] = true;
            $message_before = wpautop(htmlspecialchars_decode($options->message_before, ENT_QUOTES));
            $message_before = apply_filters('mlw_qmn_template_variable_quiz_page', $message_before, $quiz_data);
            ?>
            <section class="qsm-page <?php echo $animation_effect; ?>">
                <div class="quiz_section quiz_begin">
                    <div class='qsm-before-message mlw_qmn_message_before'>
                        <?php 
                            echo $this->qsm_convert_editor_text_to_shortcode( $message_before );
                        ?>
                    </div>
                    <?php
                    if (0 == $options->contact_info_location) {
                        echo QSM_Contact_Manager::display_fields($options);
                    }
                    ?>
                </div>
            </section>
            <?php
        }

        // If there is only one page.
		$pages = apply_filters('qsm_display_pages', $pages, $options->quiz_id, $options);
        if (1 == count($pages)) {
            ?>
            <section class="qsm-page <?php echo $animation_effect; ?>">
                <?php
                if (!empty($options->message_before) || ( 0 == $options->contact_info_location && $contact_fields )) {
                    $qmn_json_data['first_page'] = false;
                    $message_before = wpautop(htmlspecialchars_decode($options->message_before, ENT_QUOTES));
                    $message_before = apply_filters('mlw_qmn_template_variable_quiz_page', $message_before, $quiz_data);
                    ?>
                    <div class="quiz_section quiz_begin">
                        <div class='qsm-before-message mlw_qmn_message_before'>
                            <?php 
                            echo $this->qsm_convert_editor_text_to_shortcode( $message_before );
                            ?>
                        </div>
                        <?php                        
                        if (0 == $options->contact_info_location) {                            
                            echo QSM_Contact_Manager::display_fields($options);
                        }
                        ?>
                    </div>
                    <?php
                }
                foreach ($pages[0] as $question_id) {
                    $question_list .= $question_id . 'Q';
                    $question = $questions[$question_id];
                    ?>
                    <div class='quiz_section question-section-id-<?php echo esc_attr($question_id); ?>'>
                        <?php
                        echo $mlwQuizMasterNext->pluginHelper->display_question($question['question_type_new'], $question_id, $options);
                        if (0 == $question['comments']) {
                            echo "<input type='text' class='qsm-question-comment qsm-question-comment-small mlw_qmn_question_comment' id='mlwComment$question_id' name='mlwComment$question_id' placeholder='" . esc_attr(htmlspecialchars_decode($options->comment_field_text, ENT_QUOTES)) . "' onclick='qmnClearField(this)'/>";
                        }
                        if (2 == $question['comments']) {
                            echo "<textarea class='qsm-question-comment qsm-question-comment-large mlw_qmn_question_comment' id='mlwComment$question_id' name='mlwComment$question_id' placeholder='" . esc_attr(htmlspecialchars_decode($options->comment_field_text, ENT_QUOTES)) ."' onclick='qmnClearField(this)' ></textarea>";
                        }
                        // Checks if a hint is entered.
                        if (!empty($question['hints'])) {
                            echo '<div class="qsm-hint qsm_hint mlw_qmn_hint_link qsm_tooltip">'.$options->hint_text . '<span class="qsm_tooltiptext">'.htmlspecialchars_decode($question['hints'], ENT_QUOTES).'</span></div>';
                        }
                        ?>
                    </div>
                    <?php
                }
                if (0 == $options->comment_section) {
                    $message_comments = wpautop(htmlspecialchars_decode($options->message_comment, ENT_QUOTES));
                    $message_comments = apply_filters('mlw_qmn_template_variable_quiz_page', $message_comments, $quiz_data);
                    ?>
                    <div class="quiz_section quiz_begin">
                        <label for='mlwQuizComments' class='qsm-comments-label mlw_qmn_comment_section_text'><?php echo $message_comments; ?></label>
                        <textarea id='mlwQuizComments' name='mlwQuizComments' class='qsm-comments qmn_comment_section'></textarea>
                    </div>
                    <?php
                }
                if (!empty($options->message_end_template) || ( 1 == $options->contact_info_location && $contact_fields )) {
                    $message_after = wpautop(htmlspecialchars_decode($options->message_end_template, ENT_QUOTES));
                    $message_after = apply_filters('mlw_qmn_template_variable_quiz_page', $message_after, $quiz_data);
                    ?>
                    <div class="quiz_section">
                        <div class='qsm-after-message mlw_qmn_message_end'><?php echo $message_after; ?></div>
                        <?php
                        if (1 == $options->contact_info_location) {
                            echo QSM_Contact_Manager::display_fields($options);
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </section>
            <?php
        } else {
            $total_pages_count = count($pages);
            $pages_count = 1;
            foreach ($pages as $key => $page) {
				$qpage = (isset($qpages[$key]) ? $qpages[$key] : array());
				$qpage_id = (isset($qpage['id']) ? $qpage['id'] : $key);
				$page_key = (isset($qpage['pagekey']) ? $qpage['pagekey'] : $key);
				$hide_prevbtn = (isset($qpage['hide_prevbtn']) ? $qpage['hide_prevbtn'] : 0);                                
                                $style = "style='display: none;'";                                
                ?>
                <section class="qsm-page <?php echo $animation_effect; ?> qsm-page-<?php echo $qpage_id;?>" data-pid="<?php echo $qpage_id;?>" data-prevbtn="<?php echo $hide_prevbtn;?>" <?php echo $style; ?>>
					<?php do_action('qsm_action_before_page', $qpage_id, $qpage);?>
                    <?php
                    foreach ($page as $question_id) {
                        $question_list .= $question_id . 'Q';
                        $question = $questions[$question_id];
                        ?>
                        <div class='quiz_section question-section-id-<?php echo esc_attr($question_id); ?>'>
                            <?php
                            echo $mlwQuizMasterNext->pluginHelper->display_question($question['question_type_new'], $question_id, $options);
                            if (0 == $question['comments']) {
                                echo "<input type='text' class='qsm-question-comment qsm-question-comment-small mlw_qmn_question_comment' id='mlwComment$question_id' name='mlwComment$question_id' placeholder='" . esc_attr(htmlspecialchars_decode($options->comment_field_text, ENT_QUOTES)) . "' onclick='qmnClearField(this)'/>";
                            }
                            if (2 == $question['comments']) {                                
                                echo "<textarea class='qsm-question-comment qsm-question-comment-large mlw_qmn_question_comment' id='mlwComment$question_id' name='mlwComment$question_id' placeholder='" . esc_attr(htmlspecialchars_decode($options->comment_field_text, ENT_QUOTES)) ."' onclick='qmnClearField(this)' ></textarea>";
                            }
                            // Checks if a hint is entered.
                            if (!empty($question['hints'])) { 
                               echo '<div class="qsm-hint qsm_hint mlw_qmn_hint_link qsm_tooltip">'.$options->hint_text . '<span class="qsm_tooltiptext">'.htmlspecialchars_decode($question['hints'], ENT_QUOTES).'</span></div>';
                            }
                            ?>
                        </div>
                        <?php
                    }
                    if($enable_pagination_quiz){
                    ?>
                        <span class="pages_count">
                            <?php
                            $text_c = $pages_count . __(' out of ', 'quiz-master-next') .$total_pages_count;
                            echo apply_filters('qsm_total_pages_count',$text_c,$pages_count,$total_pages_count);
                            ?>
                        </span>
                    <?php } ?>
                </section>
                <?php
                $pages_count++;
            }
        }

        if (count($pages) > 1 && 0 == $options->comment_section) {
            $message_comments = wpautop(htmlspecialchars_decode($options->message_comment, ENT_QUOTES));
            $message_comments = apply_filters('mlw_qmn_template_variable_quiz_page', $message_comments, $quiz_data);
            ?>
            <section class="qsm-page">
                <div class="quiz_section quiz_begin">
                    <label for='mlwQuizComments' class='qsm-comments-label mlw_qmn_comment_section_text'><?php echo $message_comments; ?></label>
                    <textarea id='mlwQuizComments' name='mlwQuizComments' class='qsm-comments qmn_comment_section'></textarea>
                </div>
            </section>
            <?php
        }
        if (count($pages) > 1 && (!empty($options->message_end_template) || ( 1 == $options->contact_info_location && $contact_fields ) )) {
            $message_after = wpautop(htmlspecialchars_decode($options->message_end_template, ENT_QUOTES));
            $message_after = apply_filters('mlw_qmn_template_variable_quiz_page', $message_after, $quiz_data);
            ?>
<section class="qsm-page" style="display: none;">
                <div class="quiz_section">
                    <div class='qsm-after-message mlw_qmn_message_end'><?php echo $message_after; ?></div>
                    <?php
                    if (1 == $options->contact_info_location) {
                        echo QSM_Contact_Manager::display_fields($options);
                    }
                    ?>
                </div>
                <?php
                // Legacy code.
                do_action('mlw_qmn_end_quiz_section');
                ?>
            </section>
            <?php
        }
        do_action('qsm_after_all_section');
        ?>
        <!-- View for pagination -->
        <script type="text/template" id="tmpl-qsm-pagination-<?php echo $options->quiz_id;?>">
            <div class="qsm-pagination qmn_pagination border margin-bottom">
            <a class="qsm-btn qsm-previous qmn_btn mlw_qmn_quiz_link mlw_previous" href="#"><?php echo esc_html($options->previous_button_text); ?></a>
            <span class="qmn_page_message"></span>
            <div class="qmn_page_counter_message"></div>
            <div class="qsm-progress-bar" style="display:none;"><div class="progressbar-text"></div></div>
            <a class="qsm-btn qsm-next qmn_btn mlw_qmn_quiz_link mlw_next" href="#"><?php echo esc_html($options->next_button_text); ?></a>
            <input type='submit' class='qsm-btn qsm-submit-btn qmn_btn' value='<?php echo esc_attr(htmlspecialchars_decode($options->submit_button_text, ENT_QUOTES)); ?>' />
            </div>
        </script>
        <input type='hidden' name='qmn_question_list' value='<?php echo esc_attr($question_list); ?>' />
        <?php
        return ob_get_clean();
    }

    /**
     * Creates Display For Beginning Section
     *
     * Generates the content for the beginning section of the quiz page
     *
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz.
     * @param array $qmn_array_for_variables The array of results for the quiz.
     * @return string The content for the beginning section
     * @deprecated 5.2.0 Use new page system instead
     */
    public function display_begin_section($qmn_quiz_options, $qmn_array_for_variables) {
        $section_display = '';
        global $qmn_json_data;
        $contact_fields = QSM_Contact_Manager::load_fields();
        if (!empty($qmn_quiz_options->message_before) || ( 0 == $qmn_quiz_options->contact_info_location && $contact_fields )) {
            $qmn_json_data["first_page"] = true;
            global $mlw_qmn_section_count;
            $mlw_qmn_section_count += 1;
            $animation_effect = isset($qmn_quiz_options->quiz_animation) && $qmn_quiz_options->quiz_animation != '' ? ' animated ' . $qmn_quiz_options->quiz_animation : '';
            $section_display .= "<div class='qsm-auto-page-row quiz_section $animation_effect quiz_begin'>";

            $message_before = wpautop(htmlspecialchars_decode($qmn_quiz_options->message_before, ENT_QUOTES));
            $message_before = apply_filters('mlw_qmn_template_variable_quiz_page', $message_before, $qmn_array_for_variables);

            $section_display .= "<div class='mlw_qmn_message_before'>". $this->qsm_convert_editor_text_to_shortcode( $message_before ) ."</div>";
            if (0 == $qmn_quiz_options->contact_info_location) {
                $section_display .= QSM_Contact_Manager::display_fields($qmn_quiz_options);
            }
            $section_display .= "</div>";
        } else {
            $qmn_json_data["first_page"] = false;
        }
        return $section_display;
    }

    /**
     * Creates Display For Questions
     *
     * Generates the content for the questions part of the quiz page
     *
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz.
     * @param array $qmn_quiz_questions The questions of the quiz.
     * @param array $qmn_quiz_answers The answers of the quiz.
     * @uses QMNPluginHelper:display_question() Displays a question
     * @return string The content for the questions section
     * @deprecated 5.2.0 Use new page system instead
     */
    public function display_questions($qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers) {
        $question_display = '';
        global $mlwQuizMasterNext;
        global $qmn_total_questions;
        global $mlw_qmn_section_count;
        $question_id_list = '';
        $animation_effect = isset($qmn_quiz_options->quiz_animation) && $qmn_quiz_options->quiz_animation != '' ? ' animated ' . $qmn_quiz_options->quiz_animation : '';
        $enable_pagination_quiz = isset($qmn_quiz_options->enable_pagination_quiz) && $qmn_quiz_options->enable_pagination_quiz ? $qmn_quiz_options->enable_pagination_quiz : 0;
        $pagination_optoin = $qmn_quiz_options->pagination;        
        if($enable_pagination_quiz && $pagination_optoin){
            $total_pages_count = count($qmn_quiz_questions);
            $total_pagination = ceil($total_pages_count / $pagination_optoin);
        }
        $pages_count = 1;
        $current_page_number = 1;
        foreach ($qmn_quiz_questions as $mlw_question) {
            if( $pagination_optoin != 0 ){
                if( $pagination_optoin == 1 ){
                    $question_display .='<div class="qsm-auto-page-row qsm-apc-' . $current_page_number . '" style="display: none;">';
                    $current_page_number++;
                }else{
                    if ($pages_count % $pagination_optoin == 1 || $pages_count == 1) { // beginning of the row or first.
                        $question_display .='<div class="qsm-auto-page-row qsm-apc-' . $current_page_number . '" style="display: none;">';
                        $current_page_number++;
                    }
                }
				$question_display .= apply_filters('qsm_auto_page_begin_row', '', ($current_page_number - 1), $qmn_quiz_options, $qmn_quiz_questions);
			}
            $question_id_list .= $mlw_question->question_id . "Q";
			$question_display .= "<div class='quiz_section {$animation_effect} question-section-id-{$mlw_question->question_id} slide{$mlw_qmn_section_count}'>";
            $question_display .= $mlwQuizMasterNext->pluginHelper->display_question($mlw_question->question_type_new, $mlw_question->question_id, $qmn_quiz_options);

            if (0 == $mlw_question->comments) {
                $question_display .= "<input type='text' class='mlw_qmn_question_comment' id='mlwComment" . $mlw_question->question_id . "' name='mlwComment" . $mlw_question->question_id . "' placeholder='" . esc_attr(htmlspecialchars_decode($qmn_quiz_options->comment_field_text, ENT_QUOTES)) . "' onclick='qmnClearField(this)'/>";
                $question_display .= "<br />";
            }
            if (2 == $mlw_question->comments) {
                $question_display .= "<textarea cols='70' rows='5' class='mlw_qmn_question_comment' id='mlwComment" . $mlw_question->question_id . "' name='mlwComment" . $mlw_question->question_id . "' placeholder='" . htmlspecialchars_decode($qmn_quiz_options->comment_field_text, ENT_QUOTES) . "' onclick='qmnClearField(this)'></textarea>";
                $question_display .= "<br />";
            }

            // Checks if a hint is entered.
            if (!empty($mlw_question->hints)) {
                $question_display .= "<div title=\"" . esc_attr(htmlspecialchars_decode($mlw_question->hints, ENT_QUOTES)) . "\" class='qsm_hint mlw_qmn_hint_link'>{$qmn_quiz_options->hint_text}</div>";
                $question_display .= "<br /><br />";
            }
			$question_display .= '</div><!-- .quiz_section -->';
            if( $pagination_optoin == 0 ){
				
            } else if( $pagination_optoin == 1 ){
                $question_display .= '</div><!-- .qsm-auto-page-row -->';
            }else if ($pages_count % $pagination_optoin == 0 || $pages_count == count($qmn_quiz_questions)) { // end of the row or last
                $question_display .= '</div><!-- .qsm-auto-page-row -->';
            }
            $mlw_qmn_section_count = $mlw_qmn_section_count + 1;
            $pages_count++;
        }
        if($enable_pagination_quiz){
            $question_display .=  "<span class='pages_count' style='display: none;'>";
            $text_c = $current_page_number . __(' out of ', 'quiz-master-next') .$total_pagination;
            $question_display .= apply_filters('qsm_total_pages_count',$text_c,$pages_count,$total_pages_count);
            $question_display .=  "</span>";
        }
        $question_display .= "<input type='hidden' name='qmn_question_list' value='$question_id_list' />";
        return $question_display;
    }

    /**
     * Creates Display For Comment Section
     *
     * Generates the content for the comment section part of the quiz page
     *
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz.
     * @param array $qmn_array_for_variables The array of results for the quiz.
     * @return string The content for the comment section
     * @deprecated 5.2.0 Use new page system instead
     */
    public function display_comment_section($qmn_quiz_options, $qmn_array_for_variables) {
        global $mlw_qmn_section_count;
        $comment_display = '';
        if (0 == $qmn_quiz_options->comment_section) {
            $mlw_qmn_section_count = $mlw_qmn_section_count + 1;
            $comment_display .= "<div class='quiz_section quiz_end qsm-auto-page-row qsm-quiz-comment-section slide" . $mlw_qmn_section_count . "' style='display: none;'>";
            $message_comments = wpautop(htmlspecialchars_decode($qmn_quiz_options->message_comment, ENT_QUOTES));
            $message_comments = apply_filters('mlw_qmn_template_variable_quiz_page', $message_comments, $qmn_array_for_variables);
            $comment_display .= "<label for='mlwQuizComments' class='mlw_qmn_comment_section_text'>$message_comments</label><br />";
            $comment_display .= "<textarea cols='60' rows='10' id='mlwQuizComments' name='mlwQuizComments' class='qmn_comment_section'></textarea>";
            $comment_display .= "</div>";
        }
        return $comment_display;
    }

    /**
     * Creates Display For End Section Of Quiz Page
     *
     * Generates the content for the end section of the quiz page
     *
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz.
     * @param array $qmn_array_for_variables The array of results for the quiz.
     * @return string The content for the end section
     * @deprecated 5.2.0 Use new page system instead
     */
    public function display_end_section($qmn_quiz_options, $qmn_array_for_variables) {
        global $mlw_qmn_section_count;
        $section_display = '';
        $section_display .= '<br />';
        $mlw_qmn_section_count = $mlw_qmn_section_count + 1;
        $pagination_optoin = $qmn_quiz_options->pagination;
        $style = '';
        if( $pagination_optoin > 0 ){
            $style = "style='display: none;'";
        }
        $section_display .= "<div class='qsm-auto-page-row quiz_section quiz_end' {$style}>";
        if (!empty($qmn_quiz_options->message_end_template)) {
            $message_end = wpautop(htmlspecialchars_decode($qmn_quiz_options->message_end_template, ENT_QUOTES));
            $message_end = apply_filters('mlw_qmn_template_variable_quiz_page', $message_end, $qmn_array_for_variables);
            $section_display .= "<span class='mlw_qmn_message_end'>$message_end</span>";
            $section_display .= '<br /><br />';
        }
        if (1 == $qmn_quiz_options->contact_info_location) {
            $section_display .= QSM_Contact_Manager::display_fields($qmn_quiz_options);
        }

        // Legacy Code.
        ob_start();
        do_action('mlw_qmn_end_quiz_section');
        $section_display .= ob_get_contents();
        ob_end_clean();
        $section_display .= "<input type='submit' class='qsm-btn qsm-submit-btn qmn_btn' value='" . esc_attr(htmlspecialchars_decode($qmn_quiz_options->submit_button_text, ENT_QUOTES)) . "' />";
        $section_display .= "</div>";

        return $section_display;
    }

    /**
     * Generates Content Results Page
     *
     * Generates the content for the results page part of the shortcode
     *
     * @since 4.0.0
     * @param array $options The database row of the quiz.
     * @param array $data The array of results for the quiz.
     * @uses QMNQuizManager:submit_results() Perform The Quiz/Survey Submission
     * @return string The content for the results page section
     */
    public function display_results($options, $data) {
        $result = $this->submit_results($options, $data);
        $results_array = $result;
        return $results_array['display'];
    }

    /**
     * Calls the results page from ajax
     *
     * @since 4.6.0
     * @uses QMNQuizManager:submit_results() Perform The Quiz/Survey Submission
     * @return string The content for the results page section
     */
    public function ajax_submit_results() {
        global $qmn_allowed_visit;
        global $mlwQuizMasterNext;

        $qmn_allowed_visit = true;
        $quiz = intval($_POST["qmn_quiz_id"]);
        $mlwQuizMasterNext->pluginHelper->prepare_quiz($quiz);
        $options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
        $data = array(
            'quiz_id' => $options->quiz_id,
            'quiz_name' => $options->quiz_name,
            'quiz_system' => $options->system,
            'quiz_payment_id' => isset($_POST['main_payment_id']) ? sanitize_text_field($_POST['main_payment_id']) : ''
        );
        $post_data = array(
            'g-recaptcha-response' => isset($_POST['g-recaptcha-response']) ? sanitize_textarea_field($_POST['g-recaptcha-response']) : ''
        );
        if(class_exists('QSM_Recaptcha')){
            $recaptcha_data = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( "recaptcha_integration_settings" );
            if(isset($recaptcha_data['enable_recaptcha']) && $recaptcha_data['enable_recaptcha'] != 'no'){
                $verified = qsm_verify_recaptcha($post_data);
                if(!$verified){
                    echo json_encode(array(
                        'display' => htmlspecialchars_decode('ReCaptcha Validation failed'),
                        'redirect' => FALSE,
                    ));
                    exit;
                }
            }
        }
        echo json_encode($this->submit_results($options, $data));
        die();
    }

    /**
     * @version 6.3.2
     * Show quiz on button click
     */
    public function qsm_get_quiz_to_reload(){
        $quiz_id = sanitize_text_field(intval($_POST['quiz_id']));
        echo do_shortcode('[qsm quiz="'. $quiz_id .'"]');
        exit;
    }

    /**
     * Perform The Quiz/Survey Submission
     *
     * Prepares and save the results, prepares and send emails, prepare results page
     *
     * @since 4.6.0
     * @param array $qmn_quiz_options The database row of the quiz.
     * @param array $qmn_array_for_variables The array of results for the quiz.
     * @uses QMNQuizManager:check_answers() Creates display for beginning section
     * @uses QMNQuizManager:check_comment_section() Creates display for questions
     * @uses QMNQuizManager:display_results_text() Creates display for end section
     * @uses QMNQuizManager:display_social() Creates display for comment section
     * @uses QMNQuizManager:send_user_email() Creates display for end section
     * @uses QMNQuizManager:send_admin_email() Creates display for end section
     * @return string The content for the results page section
     */
    public function submit_results($qmn_quiz_options, $qmn_array_for_variables) {
        global $qmn_allowed_visit;
        $result_display = '';

        $qmn_array_for_variables['user_ip'] = $this->get_user_ip();

        $result_display = apply_filters('qmn_begin_results', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
        if (!$qmn_allowed_visit) {
            return $result_display;
        }
        //Add form type for new quiz system 7.0.0
        $qmn_array_for_variables['form_type'] = isset( $qmn_quiz_options->form_type ) ? $qmn_quiz_options->form_type : 0;
        // Gathers contact information.
        $qmn_array_for_variables['user_name'] = 'None';
        $qmn_array_for_variables['user_business'] = 'None';
        $qmn_array_for_variables['user_email'] = 'None';
        $qmn_array_for_variables['user_phone'] = 'None';
        $contact_responses = QSM_Contact_Manager::process_fields($qmn_quiz_options);
        foreach ($contact_responses as $field) {
            if (isset($field['use'])) {
                if ('name' === $field['use']) {
                    $qmn_array_for_variables['user_name'] = $field["value"];
                }
                if ('comp' === $field['use']) {
                    $qmn_array_for_variables['user_business'] = $field["value"];
                }
                if ('email' === $field['use']) {
                    $qmn_array_for_variables['user_email'] = $field["value"];
                }
                if ('phone' === $field['use']) {
                    $qmn_array_for_variables['user_phone'] = $field["value"];
                }
            }
        }
        
        if( is_user_logged_in() ){
            $current_user = wp_get_current_user();
            if( $qmn_array_for_variables['user_email'] == 'None' )
                $qmn_array_for_variables['user_email'] = esc_html( $current_user->user_email );
            
            if( $qmn_array_for_variables['user_name'] == 'None' )
                $qmn_array_for_variables['user_name'] = esc_html( $current_user->display_name );
        }
        
        $mlw_qmn_pagetime = isset($_POST["pagetime"]) ? $_POST["pagetime"] : array();
        $mlw_qmn_timer = isset($_POST["timer"]) ? sanitize_text_field(intval($_POST["timer"])) : 0;
        $mlw_qmn_timer_ms = isset($_POST["timer_ms"]) ? sanitize_text_field(intval($_POST["timer_ms"])) : 0;
        $qmn_array_for_variables['user_id'] = get_current_user_id();
        $qmn_array_for_variables['timer'] = $mlw_qmn_timer;
        $qmn_array_for_variables['timer_ms'] = $mlw_qmn_timer_ms;
        $qmn_array_for_variables['time_taken'] = current_time('h:i:s A m/d/Y');
        $qmn_array_for_variables['contact'] = $contact_responses;
        $qmn_array_for_variables['hidden_questions'] = isset($_POST['qsm_hidden_questions']) ? json_decode(html_entity_decode(stripslashes($_POST['qsm_hidden_questions'])),true) : array();
	$qmn_array_for_variables = apply_filters('qsm_result_variables', $qmn_array_for_variables);

        if (!isset($_POST["mlw_code_captcha"]) || ( isset($_POST["mlw_code_captcha"]) && $_POST["mlw_user_captcha"] == $_POST["mlw_code_captcha"] )) {

            $qmn_array_for_variables = array_merge($qmn_array_for_variables, $this->check_answers($qmn_quiz_options, $qmn_array_for_variables));            
            $result_display = apply_filters('qmn_after_check_answers', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
            $qmn_array_for_variables['comments'] = $this->check_comment_section($qmn_quiz_options, $qmn_array_for_variables);
            $result_display = apply_filters('qmn_after_check_comments', $result_display, $qmn_quiz_options, $qmn_array_for_variables);

            $unique_id = md5(date("Y-m-d H:i:s"));
			$results_id = 0;
            // If the store responses in database option is set to Yes.
            if (0 != $qmn_quiz_options->store_responses) {

                // Creates our results array.
                $results_array = array(
                    intval($qmn_array_for_variables['timer']),
                    $qmn_array_for_variables['question_answers_array'],
                    htmlspecialchars(stripslashes($qmn_array_for_variables['comments']), ENT_QUOTES),
                    'contact' => $contact_responses,
                    'timer_ms' => intval($qmn_array_for_variables['timer_ms']),
					'pagetime' => $mlw_qmn_pagetime,
                );
                $results_array = apply_filters('qsm_results_array', $results_array, $qmn_array_for_variables);
                if(isset($results_array['parameters'])) {
                  $qmn_array_for_variables['parameters'] = $results_array['parameters'];
                }
                $results_array['hidden_questions'] = $qmn_array_for_variables['hidden_questions'];
                $results_array['total_possible_points'] = $qmn_array_for_variables['total_possible_points'];
                $results_array['total_attempted_questions'] = $qmn_array_for_variables['total_attempted_questions'];
                $serialized_results = serialize($results_array);

                // Inserts the responses in the database.
                global $wpdb;
                $table_name = $wpdb->prefix . "mlw_results";
				if (isset($_POST['update_result']) && !empty($_POST['update_result'])) {
					$results_id = $_POST['update_result'];
					$results_update = $wpdb->update($table_name, array(
						'point_score' => $qmn_array_for_variables['total_points'],
						'correct_score' => $qmn_array_for_variables['total_score'],
						'correct' => $qmn_array_for_variables['total_correct'],
						'total' => $qmn_array_for_variables['total_questions'],
						'user_ip' => $qmn_array_for_variables['user_ip'],
						'time_taken' => $qmn_array_for_variables['time_taken'],
						'time_taken_real' => date('Y-m-d H:i:s', strtotime($qmn_array_for_variables['time_taken'])),
						'quiz_results' => $serialized_results,
						), array('result_id' => $results_id));
				} else {
					$results_insert = $wpdb->insert($table_name, array(
						'quiz_id' => $qmn_array_for_variables['quiz_id'],
						'quiz_name' => $qmn_array_for_variables['quiz_name'],
						'quiz_system' => $qmn_array_for_variables['quiz_system'],
						'point_score' => $qmn_array_for_variables['total_points'],
						'correct_score' => $qmn_array_for_variables['total_score'],
						'correct' => $qmn_array_for_variables['total_correct'],
						'total' => $qmn_array_for_variables['total_questions'],
						'name' => $qmn_array_for_variables['user_name'],
						'business' => $qmn_array_for_variables['user_business'],
						'email' => $qmn_array_for_variables['user_email'],
						'phone' => $qmn_array_for_variables['user_phone'],
						'user' => $qmn_array_for_variables['user_id'],
						'user_ip' => $qmn_array_for_variables['user_ip'],
						'time_taken' => $qmn_array_for_variables['time_taken'],
						'time_taken_real' => date('Y-m-d H:i:s', strtotime($qmn_array_for_variables['time_taken'])),
						'quiz_results' => $serialized_results,
						'deleted' => 0,
						'unique_id' => $unique_id,
						'form_type' => isset($qmn_quiz_options->form_type) ? $qmn_quiz_options->form_type : 0,
						), array(
						'%d',
						'%s',
						'%d',
						'%f',
						'%d',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%d',
						)
					);
					$results_id = $wpdb->insert_id;
				}
			}
            $qmn_array_for_variables['result_id'] = $results_id;

			// Determines redirect/results page.
            $results_pages = $this->display_results_text($qmn_quiz_options, $qmn_array_for_variables);
            $result_display .= $results_pages['display'];
            $result_display = apply_filters('qmn_after_results_text', $result_display, $qmn_quiz_options, $qmn_array_for_variables);

            $result_display .= $this->display_social($qmn_quiz_options, $qmn_array_for_variables);
            $result_display = apply_filters('qmn_after_social_media', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
			if ($this->qsm_plugin_active('qsm-save-resume/qsm-save-resume.php') != 1 && $qmn_quiz_options->enable_retake_quiz_button == 1) {
				$result_display .= '<a style="float: right;" class="button btn-reload-quiz" data-quiz_id="' . $qmn_array_for_variables['quiz_id'] . '" href="#" >' . apply_filters('qsm_retake_quiz_text', __('Retake Quiz', 'quiz-master-next')) . '</a>';
            }

			/*
			 * Update the option `qmn_quiz_taken_cnt` value by 1 each time
			 * whenever the record inserted into the required table.
			 */
			if( $results_insert ) {
				$rec_inserted = intval( get_option( 'qmn_quiz_taken_cnt' ) );
				if( 1000 > $rec_inserted ) {
					if( ! $rec_inserted ) {
						update_option( 'qmn_quiz_taken_cnt', 1, true );
					}else {
						update_option( 'qmn_quiz_taken_cnt', ++$rec_inserted );
					}
				}
			}

            // Hook is fired after the responses are submitted. Passes responses, result ID, quiz settings, and response data.
            do_action('qsm_quiz_submitted', $results_array, $results_id, $qmn_quiz_options, $qmn_array_for_variables);

            $qmn_array_for_variables = apply_filters( 'qmn_filter_email_content', $qmn_array_for_variables, $results_id);
            
            $qmn_global_settings = (array) get_option('qmn-settings');
            $background_quiz_email_process = isset( $qmn_global_settings['background_quiz_email_process'] ) ? esc_attr( $qmn_global_settings['background_quiz_email_process'] ) : '1';
            if( $background_quiz_email_process == 1 ){
                // Send the emails in background.
                $qmn_array_for_variables['quiz_settings'] = isset( $qmn_quiz_options->quiz_settings ) ? @unserialize( $qmn_quiz_options->quiz_settings ) : array();
                $qmn_array_for_variables['email_processed'] = 'yes';
                $this->qsm_background_email->data( array( 'name' => 'send_emails', 'variables' => $qmn_array_for_variables ) )->dispatch();
            }else{
                // Sends the emails.
                $qmn_array_for_variables['email_processed'] = 'yes';
                QSM_Emails::send_emails($qmn_array_for_variables);
            }

            /**
             * Filters for filtering the results text after emails are sent.
             *
             * @deprecated 6.2.0 There's no reason to use these over the actions
             * in the QSM_Results_Pages class or the other filters in this function.
             */
            $result_display = apply_filters('qmn_after_send_user_email', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
            $result_display = apply_filters('qmn_after_send_admin_email', $result_display, $qmn_quiz_options, $qmn_array_for_variables);

            // Last time to filter the HTML results page.
            $result_display = apply_filters('qmn_end_results', $result_display, $qmn_quiz_options, $qmn_array_for_variables);

            // Legacy Code.
            do_action('mlw_qmn_load_results_page', $wpdb->insert_id, $qmn_quiz_options->quiz_settings);
        } else {
            $result_display .= apply_filters('qmn_captcha_varification_failed_msg', __('Captcha verification failed.', 'quiz-master-next'), $qmn_quiz_options, $qmn_array_for_variables);
        }

        $result_display = str_replace('%FB_RESULT_ID%', $unique_id, $result_display);

        // Prepares data to be sent back to front-end.
        $return_array = array(
            'display' => htmlspecialchars_decode($result_display),
            'redirect' => apply_filters('mlw_qmn_template_variable_results_page', $results_pages['redirect'], $qmn_array_for_variables),
        );

        return $return_array;
    }

    /**
     * Scores User Answers
     *
     * Calculates the users scores for the quiz
     *
     * @since 4.0.0
     * @param array $options The database row of the quiz
     * @param array $quiz_data The array of results for the quiz
     * @uses QMNPluginHelper:display_review() Scores the question
     * @return array The results of the user's score
     */
    public function check_answers($options, $quiz_data) {

        global $mlwQuizMasterNext;

        // Load the pages and questions
        $pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('pages', array());
        $questions = QSM_Questions::load_questions_by_pages($options->quiz_id);

        // Retrieve data from submission
        $total_questions = isset($_POST["total_questions"]) ? sanitize_text_field(intval($_POST["total_questions"])) : 0;
        $question_list =  array();
        if(isset($_POST["qmn_question_list"])){
            $qmn_question_list = sanitize_text_field($_POST["qmn_question_list"]);
            $question_list = explode('Q', $qmn_question_list);
        }

        // Prepare variables
        $points_earned = 0;
        $total_correct = 0;
        $total_score = 0;
        $user_answer = "";
        $correct_answer = "";
        $correct_status = "incorrect";
        $answer_points = 0;
        $question_data = array();
        $total_possible_points = 0;
        $attempted_question = 0;
        
        // Question types to calculate result on
        $result_question_types = array(
          0, // Multiple Choice
          1, // Horizontal Multiple Choice
          2, // Drop Down
          4, // Multiple Response
          10, // Horizontal Multiple Response
          12, // Date
          3, // Small Open Answer
          5, // Large Open Answer
          7, // Number
          14, // Fill In The Blank
          13 //Polar.  
        );

        // If deprecated pagination setting is not used, use new system...
        if (0 == $options->question_from_total && 0 !== count($pages)) {

            // Cycle through each page in quiz.
            foreach ($pages as $page) {

                // Cycle through each question on a page
                foreach ($page as $page_question_id) {

                    // Cycle through each question that appeared to the user                    
                    foreach ($question_list as $question_id) {

                        // When the questions are the same...
                        if ($page_question_id == $question_id) {

                            $question = $questions[$page_question_id];                            
                            // Ignore non points questions from result
                            $question_type_new = $question['question_type_new'];
                            $hidden_questions = is_array($quiz_data['hidden_questions']) ? $quiz_data['hidden_questions']: array();

                            // Reset question-specific variables
                            $user_answer = "";
                            $correct_answer = "";
                            $correct_status = "incorrect";
                            $answer_points = 0;
                            
                            //Get total correct points                            
                            if( ( $options->system == 3 || $options->system == 1 ) && isset($question['answers']) && !empty( $question['answers'] ) ){
                              if(!in_array($question_id,$hidden_questions)) {
                                if( $question_type_new == 4 || $question_type_new == 10 ){
                                    foreach ($question['answers'] as $single_answerk_key => $single_answer_arr) {
                                        if ( $options->system == 1 && isset( $single_answer_arr[1] ) ){
                                            $total_possible_points = $total_possible_points + $single_answer_arr[1];
                                        }
                                        if( $options->system == 3 && isset( $single_answer_arr[2] ) && $single_answer_arr[2] == 1 ){
                                            $total_possible_points = $total_possible_points + $single_answer_arr[1];
                                        }                                        
                                    }                                
                                }else{
                                    $max_value = max(array_column($question['answers'], '1'));
                                    $total_possible_points = $total_possible_points + $max_value;
                                }
                              }
                            }
                            
                            // Send question to our grading function
                            $results_array = apply_filters('qmn_results_array', $mlwQuizMasterNext->pluginHelper->display_review($question['question_type_new'], $question['question_id']));
                            if( isset($results_array['question_type']) && $results_array['question_type'] == 'file_upload') {
                              $results_array['user_text'] = '<a target="_blank" href="'.$results_array['user_text'].'">' . __('Click here to view', 'quiz-master-next') . '</a>';
                            }
                            // If question was graded correctly.
                            if (!isset($results_array["null_review"])) {
                                if(in_array($question_type_new,$result_question_types)) {
                                  if(!in_array($question_id,$hidden_questions)) {
                                    $points_earned += $results_array["points"];
                                    $answer_points += $results_array["points"];
                                  }
                                }
                                

                                // If the user's answer was correct
                                if ('correct' == $results_array["correct"]) {
                                    if(in_array($question_type_new,$result_question_types)) {
                                      if(!in_array($question_id,$hidden_questions)) {
                                        $total_correct += 1;
                                        $correct_status = "correct";
                                      }
                                    }
                                    
                                }
                                $user_answer = $results_array["user_text"];
                                $correct_answer = $results_array["correct_text"];
                                $user_compare_text = isset( $results_array["user_compare_text"] ) ? $results_array["user_compare_text"] : '';
                                
                                if( trim( $user_answer ) != '' ){
                                    if( $user_answer != 'No Answer Provided' ){
                                        $attempted_question++;
                                    }                                
                                }
                                
                                // If a comment was submitted
                                if (isset($_POST["mlwComment" . $question['question_id']])) {
                                    $comment = sanitize_textarea_field( htmlspecialchars(stripslashes($_POST["mlwComment" . $question['question_id']]), ENT_QUOTES) );
                                } else {
                                    $comment = "";
                                }

                                // Get text for question
                                $question_text = $question['question_name'];
                                if (isset($results_array["question_text"])) {
                                    $question_text = $results_array["question_text"];
                                }

                                // Save question data into new array in our array
                                $question_data[] = apply_filters('qmn_answer_array', array(
                                    $question_text,
                                    htmlspecialchars($user_answer, ENT_QUOTES),
                                    htmlspecialchars($correct_answer, ENT_QUOTES),
                                    $comment,
                                    "correct" => $correct_status,
                                    "id" => $question['question_id'],
                                    "points" => $answer_points,
                                    "category" => $question['category'],
                                    "question_type" => $question['question_type_new'],
                                    "question_title" => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
                                    "user_compare_text" => $user_compare_text
                                        ), $options, $quiz_data);
                            }
                            break;
                        }
                    }
                }
            }
        } else {
            // Cycle through each page in quiz.
            foreach ($questions as $question) {

                // Cycle through each question that appeared to the user
                foreach ($question_list as $question_id) {

                    // When the questions are the same...
                    if ($question['question_id'] == $question_id) {

                        // Reset question-specific variables
                        $user_answer = "";
                        $correct_answer = "";
                        $correct_status = "incorrect";
                        $answer_points = 0;
                        
                        //Get total correct points
                        if( ( $options->system == 3 || $options->system == 1 ) && isset($question['answers']) && !empty( $question['answers'] ) ){
                            if( $question_type_new == 4 || $question_type_new == 10 ){
                                foreach ($question['answers'] as $single_answerk_key => $single_answer_arr) {
                                    if ( $options->system == 1 && isset( $single_answer_arr[1] ) ){
                                        $total_possible_points = $total_possible_points + $single_answer_arr[1];
                                    }
                                    if( $options->system == 3 && isset( $single_answer_arr[2] ) && $single_answer_arr[2] == 1 ){
                                        $total_possible_points = $total_possible_points + $single_answer_arr[1];
                                    }                                        
                                }                                
                            }else{
                                $max_value = max(array_column($question['answers'], '1'));
                                $total_possible_points = $total_possible_points + $max_value;
                            }                            
                        }
                        
                        // Send question to our grading function
                        $results_array = apply_filters('qmn_results_array', $mlwQuizMasterNext->pluginHelper->display_review($question['question_type_new'], $question['question_id']));

							
                        // If question was graded correctly.
                        if (!isset($results_array["null_review"])) {
                            $points_earned += $results_array["points"];
                            $answer_points += $results_array["points"];

                            // If the user's answer was correct
                            if ('correct' == $results_array["correct"]) {
                                $total_correct += 1;
                                $correct_status = "correct";
                            }
                            $user_answer = $results_array["user_text"];
                            $correct_answer = $results_array["correct_text"];
                            $user_compare_text = isset( $results_array["user_compare_text"] ) ? $results_array["user_compare_text"] : '';
                            if( trim( $user_answer ) != '' ){
                                if( $user_answer != 'No Answer Provided' ){
                                    $attempted_question++;
                                }   
                            }
                            // If a comment was submitted
                            if (isset($_POST["mlwComment" . $question['question_id']])) {
                                $comment = sanitize_textarea_field( htmlspecialchars(stripslashes($_POST["mlwComment" . $question['question_id']]), ENT_QUOTES) );
                            } else {
                                $comment = "";
                            }

                            // Get text for question
                            $question_text = $question['question_name'];
                            if (isset($results_array["question_text"])) {
                                $question_text = $results_array["question_text"];
                            }

                            // Save question data into new array in our array
                            $question_data[] = apply_filters('qmn_answer_array', array(
                                $question_text,
                                htmlspecialchars($user_answer, ENT_QUOTES),
                                htmlspecialchars($correct_answer, ENT_QUOTES),
                                $comment,
                                "correct" => $correct_status,
                                "id" => $question['question_id'],
                                "points" => $answer_points,
                                "category" => $question['category'],
                                "question_type" => $question['question_type_new'],
                                "question_title" => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
                                "user_compare_text" => $user_compare_text
                                    ), $options, $quiz_data);
                        }
                        break;
                    }
                }
            }
        }

        // Calculate Total Percent Score And Average Points Only If Total Questions Doesn't Equal Zero To Avoid Division By Zero Error
        if (0 !== $total_questions) {
            $total_score = round(( ( $total_correct / $total_questions ) * 100), 2);
        } else {
            $total_score = 0;
        }

        // Return array to be merged with main user response array
        return array(
            'total_points' => $points_earned,
            'total_score' => $total_score,
            'total_correct' => $total_correct,
            'total_questions' => $total_questions,
            'question_answers_display' => '', // Kept for backwards compatibility
            'question_answers_array' => $question_data,
            'total_possible_points' => $total_possible_points,
            'total_attempted_questions' => $attempted_question
        );
    }

    /**
     * Retrieves User's Comments
     *
     * Checks to see if the user left a comment and returns the comment
     *
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz
     * @param array $qmn_array_for_variables The array of results for the quiz
     * @return string The user's comments
     */
    public function check_comment_section($qmn_quiz_options, $qmn_array_for_variables) {
        $qmn_quiz_comments = "";
        if (isset($_POST["mlwQuizComments"])) {
            $qmn_quiz_comments = esc_textarea(stripslashes($_POST["mlwQuizComments"]));
        }
        return apply_filters('qmn_returned_comments', $qmn_quiz_comments, $qmn_quiz_options, $qmn_array_for_variables);
    }

    /**
     * Displays Results Text
     *
     * @since 4.0.0
     * @deprecated 6.1.0 Use the newer results page class instead.
     * @param array $options The quiz settings.
     * @param array $response_data The array of results for the quiz.
     * @return string The contents for the results text
     */
    public function display_results_text($options, $response_data) {
        return QSM_Results_Pages::generate_pages($response_data);
    }

    /**
     * Displays social media buttons
     *
     * @deprecated 6.1.0 Use the social media template variables instead.
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz.
     * @param array $qmn_array_for_variables The array of results for the quiz.
     * @return string The content of the social media button section
     */
    public function display_social($qmn_quiz_options, $qmn_array_for_variables) {
        $social_display = '';
        if ($qmn_quiz_options->social_media == 1) {
            $settings = (array) get_option('qmn-settings');
            $facebook_app_id = '594986844960937';
            if (isset($settings['facebook_app_id'])) {
                $facebook_app_id = esc_js($settings['facebook_app_id']);
            }

            // Loads Social Media Text.
            $qmn_social_media_text = "";
            if (is_serialized($qmn_quiz_options->social_media_text) && is_array(@unserialize($qmn_quiz_options->social_media_text))) {
                $qmn_social_media_text = @unserialize($qmn_quiz_options->social_media_text);
            } else {
                $qmn_social_media_text = array(
                    'twitter' => $qmn_quiz_options->social_media_text,
                    'facebook' => $qmn_quiz_options->social_media_text,
                );
            }
            $qmn_social_media_text["twitter"] = apply_filters('mlw_qmn_template_variable_results_page', $qmn_social_media_text["twitter"], $qmn_array_for_variables);
            $qmn_social_media_text["facebook"] = apply_filters('mlw_qmn_template_variable_results_page', $qmn_social_media_text["facebook"], $qmn_array_for_variables);
            $social_display .= "<br /><a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('facebook', '" . esc_js($qmn_social_media_text["facebook"]) . "', '" . esc_js($qmn_quiz_options->quiz_name) . "', '$facebook_app_id');\">Facebook</a><a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('twitter', '" . esc_js($qmn_social_media_text["twitter"]) . "', '" . esc_js($qmn_quiz_options->quiz_name) . "');\">Twitter</a><br />";
        }
        return apply_filters('qmn_returned_social_buttons', $social_display, $qmn_quiz_options, $qmn_array_for_variables);
    }

    /**
     * Send User Email
     *
     * Prepares the email to the user and then sends the email
     *
     * @deprecated 6.2.0 Use the newer QSM_Emails class instead.
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz
     * @param array $qmn_array_for_variables The array of results for the quiz
     */
    public function send_user_email($qmn_quiz_options, $qmn_array_for_variables) {
        add_filter('wp_mail_content_type', 'mlw_qmn_set_html_content_type');
        $mlw_message = "";

        //Check if this quiz has user emails turned on
        if ($qmn_quiz_options->send_user_email == "0") {

            //Make sure that the user filled in the email field
            if ($qmn_array_for_variables['user_email'] != "") {

                //Prepare from email and name
                $from_email_array = maybe_unserialize($qmn_quiz_options->email_from_text);
                if (!isset($from_email_array["from_email"])) {
                    $from_email_array = array(
                        'from_name' => $qmn_quiz_options->email_from_text,
                        'from_email' => $qmn_quiz_options->admin_email,
                        'reply_to' => 1
                    );
                }

                if (!is_email($from_email_array["from_email"])) {
                    if (is_email($qmn_quiz_options->admin_email)) {
                        $from_email_array["from_email"] = $qmn_quiz_options->admin_email;
                    } else {
                        $from_email_array["from_email"] = get_option('admin_email ', 'test@example.com');
                    }
                }

                //Prepare email attachments
                $attachments = array();
                $attachments = apply_filters('qsm_user_email_attachments', $attachments, $qmn_array_for_variables);

                if (is_serialized($qmn_quiz_options->user_email_template) && is_array(@unserialize($qmn_quiz_options->user_email_template))) {

                    $mlw_user_email_array = @unserialize($qmn_quiz_options->user_email_template);

                    //Cycle through emails
                    foreach ($mlw_user_email_array as $mlw_each) {

                        //Generate Email Subject
                        if (!isset($mlw_each[3])) {
                            $mlw_each[3] = "Quiz Results For %QUIZ_NAME";
                        }
                        $mlw_each[3] = apply_filters('mlw_qmn_template_variable_results_page', $mlw_each[3], $qmn_array_for_variables);

                        //Check to see if default
                        if ($mlw_each[0] == 0 && $mlw_each[1] == 0) {
                            $mlw_message = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
                            $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                            $mlw_message = str_replace("\n", "<br>", $mlw_message);
                            $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                            $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                            $mlw_headers = 'From: ' . $from_email_array["from_name"] . ' <' . $from_email_array["from_email"] . '>' . "\r\n";
                            wp_mail($qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments);
                            break;
                        } else {

                            //Check to see if this quiz uses points and check if the points earned falls in the point range for this email
                            if ($qmn_quiz_options->system == 1 && $qmn_array_for_variables['total_points'] >= $mlw_each[0] && $qmn_array_for_variables['total_points'] <= $mlw_each[1]) {
                                $mlw_message = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
                                $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                                $mlw_message = str_replace("\n", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                                $mlw_headers = 'From: ' . $from_email_array["from_name"] . ' <' . $from_email_array["from_email"] . '>' . "\r\n";
                                wp_mail($qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments);
                                break;
                            }

                            //Check to see if score fall in correct range
                            if ($qmn_quiz_options->system == 0 && $qmn_array_for_variables['total_score'] >= $mlw_each[0] && $qmn_array_for_variables['total_score'] <= $mlw_each[1]) {
                                $mlw_message = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
                                $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                                $mlw_message = str_replace("\n", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                                $mlw_headers = 'From: ' . $from_email_array["from_name"] . ' <' . $from_email_array["from_email"] . '>' . "\r\n";
                                wp_mail($qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments);
                                break;
                            }
                        }
                    }
                } else {

                    //Uses older email system still which was before different emails were created.
                    $mlw_message = htmlspecialchars_decode($qmn_quiz_options->user_email_template, ENT_QUOTES);
                    $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                    $mlw_message = str_replace("\n", "<br>", $mlw_message);
                    $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                    $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                    $mlw_headers = 'From: ' . $from_email_array["from_name"] . ' <' . $from_email_array["from_email"] . '>' . "\r\n";
                    wp_mail($qmn_array_for_variables['user_email'], "Quiz Results For " . $qmn_quiz_options->quiz_name, $mlw_message, $mlw_headers, $attachments);
                }
            }
        }
        remove_filter('wp_mail_content_type', 'mlw_qmn_set_html_content_type');
    }

    /**
     * Send Admin Email
     *
     * Prepares the email to the admin and then sends the email
     *
     * @deprecated 6.2.0 Use the newer QSM_Emails class instead.
     * @since 4.0.0
     * @param array $qmn_quiz_options The database row of the quiz
     * @param arrar $qmn_array_for_variables The array of results for the quiz
     */
    public function send_admin_email($qmn_quiz_options, $qmn_array_for_variables) {
        //Switch email type to HTML
        add_filter('wp_mail_content_type', 'mlw_qmn_set_html_content_type');

        $mlw_message = "";
        if ($qmn_quiz_options->send_admin_email == "0") {
            if ($qmn_quiz_options->admin_email != "") {
                $from_email_array = maybe_unserialize($qmn_quiz_options->email_from_text);
                if (!isset($from_email_array["from_email"])) {
                    $from_email_array = array(
                        'from_name' => $qmn_quiz_options->email_from_text,
                        'from_email' => $qmn_quiz_options->admin_email,
                        'reply_to' => 1
                    );
                }

                if (!is_email($from_email_array["from_email"])) {
                    if (is_email($qmn_quiz_options->admin_email)) {
                        $from_email_array["from_email"] = $qmn_quiz_options->admin_email;
                    } else {
                        $from_email_array["from_email"] = get_option('admin_email ', 'test@example.com');
                    }
                }

                $mlw_message = "";
                $mlw_subject = "";
                if (is_serialized($qmn_quiz_options->admin_email_template) && is_array(@unserialize($qmn_quiz_options->admin_email_template))) {
                    $mlw_admin_email_array = @unserialize($qmn_quiz_options->admin_email_template);

                    //Cycle through landing pages
                    foreach ($mlw_admin_email_array as $mlw_each) {

                        //Generate Email Subject
                        if (!isset($mlw_each["subject"])) {
                            $mlw_each["subject"] = "Quiz Results For %QUIZ_NAME";
                        }
                        $mlw_each["subject"] = apply_filters('mlw_qmn_template_variable_results_page', $mlw_each["subject"], $qmn_array_for_variables);

                        //Check to see if default
                        if ($mlw_each["begin_score"] == 0 && $mlw_each["end_score"] == 0) {
                            $mlw_message = htmlspecialchars_decode($mlw_each["message"], ENT_QUOTES);
                            $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                            $mlw_message = str_replace("\n", "<br>", $mlw_message);
                            $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                            $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                            $mlw_subject = $mlw_each["subject"];
                            break;
                        } else {
                            //Check to see if points fall in correct range
                            if ($qmn_quiz_options->system == 1 && $qmn_array_for_variables['total_points'] >= $mlw_each["begin_score"] && $qmn_array_for_variables['total_points'] <= $mlw_each["end_score"]) {
                                $mlw_message = htmlspecialchars_decode($mlw_each["message"], ENT_QUOTES);
                                $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                                $mlw_message = str_replace("\n", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                                $mlw_subject = $mlw_each["subject"];
                                break;
                            }

                            //Check to see if score fall in correct range
                            if ($qmn_quiz_options->system == 0 && $qmn_array_for_variables['total_score'] >= $mlw_each["begin_score"] && $qmn_array_for_variables['total_score'] <= $mlw_each["end_score"]) {
                                $mlw_message = htmlspecialchars_decode($mlw_each["message"], ENT_QUOTES);
                                $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                                $mlw_message = str_replace("\n", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                                $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                                $mlw_subject = $mlw_each["subject"];
                                break;
                            }
                        }
                    }
                } else {
                    $mlw_message = htmlspecialchars_decode($qmn_quiz_options->admin_email_template, ENT_QUOTES);
                    $mlw_message = apply_filters('mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
                    $mlw_message = str_replace("\n", "<br>", $mlw_message);
                    $mlw_message = str_replace("<br/>", "<br>", $mlw_message);
                    $mlw_message = str_replace("<br />", "<br>", $mlw_message);
                    $mlw_subject = "Quiz Results For " . $qmn_quiz_options->quiz_name;
                }
            }
            if (get_option('mlw_advert_shows') == 'true') {
                $mlw_message .= "<br>This email was generated by the Quiz And Survey Master plugin";
            }
            $headers = array(
                'From: ' . $from_email_array["from_name"] . ' <' . $from_email_array["from_email"] . '>'
            );
            if ($from_email_array["reply_to"] == 0) {
                $headers[] = 'Reply-To: ' . $qmn_array_for_variables["user_name"] . " <" . $qmn_array_for_variables["user_email"] . ">";
            }
            $admin_emails = explode(",", $qmn_quiz_options->admin_email);
            foreach ($admin_emails as $admin_email) {
                if (is_email($admin_email)) {
                    wp_mail($admin_email, $mlw_subject, $mlw_message, $headers);
                }
            }
        }

        //Remove HTML type for emails
        remove_filter('wp_mail_content_type', 'mlw_qmn_set_html_content_type');
    }

    /**
     * Returns the quiz taker's IP if IP collection is enabled
     *
     * @since 5.3.0
     * @return string The IP address or a phrase if not collected
     */
    private function get_user_ip() {
        $ip = __('Not collected', 'quiz-master-next');
        $settings = (array) get_option('qmn-settings');
        $ip_collection = '0';
        if (isset($settings['ip_collection'])) {
            $ip_collection = $settings['ip_collection'];
        }
        if ('1' != $ip_collection) {
            if ($_SERVER['REMOTE_ADDR']) {
                $ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $ip = __('Unknown', 'quiz-master-next');
            }

            if (getenv('HTTP_CLIENT_IP'))
                $ip = getenv('HTTP_CLIENT_IP');
            else if (getenv('HTTP_X_FORWARDED_FOR'))
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            else if (getenv('HTTP_X_FORWARDED'))
                $ip = getenv('HTTP_X_FORWARDED');
            else if (getenv('HTTP_FORWARDED_FOR'))
                $ip = getenv('HTTP_FORWARDED_FOR');
            else if (getenv('HTTP_FORWARDED'))
                $ip = getenv('HTTP_FORWARDED');
            else if (getenv('REMOTE_ADDR'))
                $ip = getenv('REMOTE_ADDR');
            else
                $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
    * Determines whether a plugin is active.
    *
    * @since 6.4.11
    *
    * @param string $plugin Path to the plugin file relative to the plugins directory.
    * @return bool True, if in the active plugins list. False, not in the list.
    */
    private function qsm_plugin_active( $plugin ){
        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || $this->qsm_plugin_active_for_network( $plugin );
    }

    /**
    * Determines whether the plugin is active for the entire network.
    *
    * @since 6.4.11
    *
    * @param string $plugin Path to the plugin file relative to the plugins directory.
    * @return bool True if active for the network, otherwise false.
    */
    private function qsm_plugin_active_for_network(){
        if ( ! is_multisite() ) {
		return false;
	}

	$plugins = get_site_option( 'active_sitewide_plugins' );
	if ( isset( $plugins[ $plugin ] ) ) {
		return true;
	}

	return false;
    }

    /**
     * Include background process files
     *
     * @singce 7.0
     */
    public function qsm_process_background_email(){
        require_once plugin_dir_path( __FILE__ ) . 'class-qmn-background-process.php';
        $this->qsm_background_email = new QSM_Background_Request();
    }
    
    /**
     * Convert editor text into respective shortcodes
     * 
     * @since 7.0.2
     * @param string $editor_text
     */
    public function qsm_convert_editor_text_to_shortcode( $editor_text ){
        global $wp_embed;
        $editor_text = $wp_embed->run_shortcode( $editor_text );
        $editor_text = preg_replace("/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i","<iframe width=\"420\" height=\"315\" src=\"//www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>",$editor_text);
        $allowed_html = wp_kses_allowed_html( 'post' );
        return do_shortcode( wp_kses( $editor_text, $allowed_html ) ); 
    }
    
    /**
     * Get attachment id from attachment url
     * 
     * @since 7.1.2
     * 
     * @global obj $wpdb
     * @param url $attachment_url
     * @return int
     */
    public function qsm_get_attachment_id_from_url( $attachment_url = '' ) {

            global $wpdb;
            $attachment_id = false;

            // If there is no url, return.
            if ( '' == $attachment_url )
                    return;

            // Get the upload directory paths
            $upload_dir_paths = wp_upload_dir();

            // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
            if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

                    // If this is the URL of an auto-generated thumbnail, get the URL of the original image
                    $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

                    // Remove the upload path base directory from the attachment URL
                    $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

                    // Finally, run a custom database query to get the attachment ID from the modified attachment URL
                    $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

            }

            return $attachment_id;
    }
}

global $qmnQuizManager;
$qmnQuizManager = new QMNQuizManager();

add_filter('qmn_begin_shortcode', 'qmn_require_login_check', 10, 3);

function qmn_require_login_check($display, $qmn_quiz_options, $qmn_array_for_variables) {
    global $qmn_allowed_visit;
    if ($qmn_quiz_options->require_log_in == 1 && !is_user_logged_in()) {
        $qmn_allowed_visit = false;
        if(isset($qmn_quiz_options->require_log_in_text) && $qmn_quiz_options->require_log_in_text != ''){
            $mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->require_log_in_text, ENT_QUOTES));
        }else{
            $mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->require_log_in_text, ENT_QUOTES));
        }
        $mlw_message = apply_filters('mlw_qmn_template_variable_quiz_page', $mlw_message, $qmn_array_for_variables);
        $mlw_message = str_replace("\n", "<br>", $mlw_message);
        //$display .= do_shortcode($mlw_message);
        $display .= do_shortcode($mlw_message);
        $display .= wp_login_form(array('echo' => false));
    }
    return $display;
}

add_filter('qmn_begin_shortcode', 'qsm_scheduled_timeframe_check', 99, 3);

/**
 * @since 7.0.0 Added the condition for start time ( end time blank ) and end time ( start time blank ).
 *
 * @global boolean $qmn_allowed_visit
 * @param HTML $display
 * @param Object $options
 * @param Array $variable_data
 * @return HTML This function check the time frame of quiz.
 */
function qsm_scheduled_timeframe_check($display, $options, $variable_data) {
    global $qmn_allowed_visit;

    $checked_pass = FALSE;
    // Checks if the start and end dates have data
    if (!empty($options->scheduled_time_start) && !empty($options->scheduled_time_end)) {        
        $start = strtotime($options->scheduled_time_start);
        $end = strtotime($options->scheduled_time_end);        
        if( strpos( $options->scheduled_time_end, ':' ) === false || strpos( $options->scheduled_time_end, '00:00' ) !== false )
            $end = strtotime($options->scheduled_time_end) + 86399;
        
        $current_time = strtotime( current_time( 'm/d/Y H:i' ) );
        // Checks if the current timestamp is outside of scheduled timeframe
        if ( $current_time < $start || $current_time > $end) {
            $checked_pass = TRUE;
        }
    }
    if ( !empty( $options->scheduled_time_start ) && empty( $options->scheduled_time_end ) ){        
        $start = new DateTime( $options->scheduled_time_start );
        $current_datetime = new DateTime( current_time( 'm/d/Y H:i' ) );
        if ( $current_datetime < $start ){
            $checked_pass = TRUE;
        }
    }
    if ( empty( $options->scheduled_time_start ) && !empty( $options->scheduled_time_end ) ){        
        $end = new DateTime( $options->scheduled_time_end );
        $current_datetime = new DateTime( current_time( 'm/d/Y H:i' ) );
        if ( $current_datetime > $end ) {
            $checked_pass = TRUE;
        }
    }    
    if( $checked_pass == TRUE ){
        $qmn_allowed_visit = false;
        $message = wpautop(htmlspecialchars_decode($options->scheduled_timeframe_text, ENT_QUOTES));
        $message = apply_filters('mlw_qmn_template_variable_quiz_page', $message, $variable_data);
        $display .= str_replace("\n", "<br>", $message);
    }
    return $display;
}

add_filter('qmn_begin_shortcode', 'qmn_total_user_tries_check', 10, 3);

/**
 * Checks if user has already reach the user limit of the quiz
 *
 * @since 5.0.0
 * @param string $display The HTML displayed for the quiz
 * @param array $qmn_quiz_options The settings for the quiz
 * @param array $qmn_array_for_variables The array of data by the quiz
 * @return string The altered HTML display for the quiz
 */
function qmn_total_user_tries_check($display, $qmn_quiz_options, $qmn_array_for_variables) {

    global $qmn_allowed_visit;
    if ($qmn_quiz_options->total_user_tries != 0) {

        // Prepares the variables
        global $wpdb;
        $mlw_qmn_user_try_count = 0;

        // Checks if the user is logged in. If so, check by user id. If not, check by IP.
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $mlw_qmn_user_try_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE user=%d AND deleted=0 AND quiz_id=%d", $current_user->ID, $qmn_array_for_variables['quiz_id']));
        } else {
            $mlw_qmn_user_try_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE user_ip=%s AND deleted=0 AND quiz_id=%d", $qmn_array_for_variables['user_ip'], $qmn_array_for_variables['quiz_id']));
        }

        // If user has already reached the limit for this quiz
        if ($mlw_qmn_user_try_count >= $qmn_quiz_options->total_user_tries) {

            // Stops the quiz and prepares entered text
            $qmn_allowed_visit = false;
            $mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->total_user_tries_text, ENT_QUOTES));
            $mlw_message = apply_filters('mlw_qmn_template_variable_quiz_page', $mlw_message, $qmn_array_for_variables);
            $display .= $mlw_message;
        }
    }
    return $display;
}

add_filter('qmn_begin_quiz', 'qmn_total_tries_check', 10, 3);

function qmn_total_tries_check($display, $qmn_quiz_options, $qmn_array_for_variables) {
    global $qmn_allowed_visit;
    if ($qmn_quiz_options->limit_total_entries != 0) {
        global $wpdb;
        $mlw_qmn_entries_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(quiz_id) FROM {$wpdb->prefix}mlw_results WHERE deleted=0 AND quiz_id=%d", $qmn_array_for_variables['quiz_id']));
        if ($mlw_qmn_entries_count >= $qmn_quiz_options->limit_total_entries) {
            $mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->limit_total_entries_text, ENT_QUOTES));
            $mlw_message = apply_filters('mlw_qmn_template_variable_quiz_page', $mlw_message, $qmn_array_for_variables);
            $display .= $mlw_message;
            $qmn_allowed_visit = false;
        }
    }
    return $display;
}

add_filter('qmn_begin_quiz', 'qmn_pagination_check', 10, 3);

function qmn_pagination_check($display, $qmn_quiz_options, $qmn_array_for_variables) {    
    if ($qmn_quiz_options->pagination != 0) {
        global $wpdb;
        global $qmn_json_data;
        $total_questions = 0;
        if ($qmn_quiz_options->question_from_total != 0) {
            $total_questions = $qmn_quiz_options->question_from_total;
        } else {
            $questions = QSM_Questions::load_questions_by_pages($qmn_quiz_options->quiz_id);
            $total_questions = count($questions);
        }
        //$display .= "<style>.quiz_section { display: none; }</style>";

        $qmn_json_data["pagination"] = array(
            'amount' => $qmn_quiz_options->pagination,
            'section_comments' => $qmn_quiz_options->comment_section,
            'total_questions' => $total_questions,
            'previous_text' => $qmn_quiz_options->previous_button_text,
            'next_text' => $qmn_quiz_options->next_button_text
        );
    }
    return $display;
}

add_filter('qmn_begin_quiz_form', 'qmn_timer_check', 15, 3);

function qmn_timer_check($display, $qmn_quiz_options, $qmn_array_for_variables) {
    global $qmn_allowed_visit;
    global $qmn_json_data;
    if ($qmn_allowed_visit && $qmn_quiz_options->timer_limit != 0) {
        $qmn_json_data["timer_limit"] = $qmn_quiz_options->timer_limit;
        $display .= '<div style="display:none;" id="mlw_qmn_timer" class="mlw_qmn_timer"></div>';
    }
    return $display;
}

add_filter('qmn_begin_quiz', 'qmn_update_views', 10, 3);

function qmn_update_views($display, $qmn_quiz_options, $qmn_array_for_variables) {
    global $wpdb;
    $mlw_views = $qmn_quiz_options->quiz_views;
    $mlw_views += 1;
    $results = $wpdb->update(
            $wpdb->prefix . "mlw_quizzes", array(
        'quiz_views' => $mlw_views
            ), array('quiz_id' => $qmn_array_for_variables["quiz_id"]), array(
        '%d'
            ), array('%d')
    );
    return $display;
}

add_filter('qmn_begin_results', 'qmn_update_taken', 10, 3);

function qmn_update_taken($display, $qmn_quiz_options, $qmn_array_for_variables) {
    global $wpdb;
    $mlw_taken = $qmn_quiz_options->quiz_taken;
    $mlw_taken += 1;
    $results = $wpdb->update(
            $wpdb->prefix . "mlw_quizzes", array(
        'quiz_taken' => $mlw_taken
            ), array('quiz_id' => $qmn_array_for_variables["quiz_id"]), array(
        '%d'
            ), array('%d')
    );
    return $display;
}

/*
  This function helps set the email type to HTML
 */

function mlw_qmn_set_html_content_type() {

    return 'text/html';
}

function qsm_time_in_milliseconds() {
	return round(microtime(true) * 1000);
}

add_filter( 'wp_video_extensions', function( $exts ) {
	$exts[] = 'mov';
	$exts[] = 'avi';
	$exts[] = 'wmv';
	return $exts;
});
