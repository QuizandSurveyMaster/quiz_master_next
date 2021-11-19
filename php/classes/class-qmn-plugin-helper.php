<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * This class is a helper class to be used for extending the plugin
 *
 * This class contains many functions for extending the plugin
 *
 * @since 4.0.0
 */
class QMNPluginHelper
{

    /**
     * Addon Page tabs array
     *
     * @var array
     * @since 4.0.0
     */
    public $addon_tabs = array();

    /**
     * Stats Page tabs array
     *
     * @var array
     * @since 4.0.0
     */
    public $stats_tabs = array();

    /**
     * Admin Results Page tabs array
     *
     * @var array
     * @since 5.0.0
     */
    public $admin_results_tabs = array();

    /**
     * Results Details Page tabs array
     *
     * @var array
     * @since 4.1.0
     */
    public $results_tabs = array();

    /**
     * Settings Page tabs array
     *
     * @var array
     * @since 4.0.0
     */
    public $settings_tabs = array();

    /**
     * Question types array
     *
     * @var array
     * @since 4.0.0
     */
    public $question_types = array();

    /**
     * Template array
     *
     * @var array
     * @since 4.5.0
     */
    public $quiz_templates = array();

    /**
     * Main Construct Function
     *
     * Call functions within class
     *
     * @since 4.0.0
     * @return void
     */
    public function __construct()
    {
        add_action('wp_ajax_qmn_question_type_change', array($this, 'get_question_type_edit_content'));
    }

    /**
     * Calls all class functions to initialize quiz
     *
     * @param int $quiz_id The ID of the quiz or survey to load.
     * @return bool True or False if ID is valid.
     */
    public function prepare_quiz($quiz_id)
    {
        $quiz_id = intval($quiz_id);

        // Tries to load quiz name to ensure this is a valid ID.
        global $wpdb;
        $quiz_name = $wpdb->get_var($wpdb->prepare("SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id));
        if (is_null($quiz_name)) {
            return false;
        }

        global $mlwQuizMasterNext;
        $mlwQuizMasterNext->quizCreator->set_id($quiz_id);
        $mlwQuizMasterNext->quiz_settings->prepare_quiz($quiz_id);

        return true;
    }

    /**
     * Retrieves all quizzes.
     *
     * @param bool $include_deleted If set to true, returned array will include all deleted quizzes
     * @param string $order_by The column the quizzes should be ordered by
     * @param string $order whether the $order_by should be ordered as ascending or decending. Can be "ASC" or "DESC"
     * @param arr $user_role role of current user
     * @param int $user_id Get the quiz based on user id
     * @return array All of the quizzes as a numerical array of objects
     */
    public function get_quizzes($include_deleted = false, $order_by = 'quiz_id', $order = 'DESC', $user_role = array(), $user_id = '', $limit = '', $offset = '', $where = '')
    {
        global $wpdb;

        // Set order direction
        $order_direction = 'DESC';
        if ('ASC' == $order) {
            $order_direction = 'ASC';
        }

        // Set field to sort by
        switch ($order_by) {
            case 'last_activity':
                $order_field = 'last_activity';
                break;

            case 'quiz_views':
                $order_field = 'quiz_views';
                break;

            case 'quiz_taken':
                $order_field = 'quiz_taken';
                break;

            case 'title':
                $order_field = 'quiz_name';
                break;

            default:
                $order_field = 'quiz_id';
                break;
        }

        // Should we include deleted?
        $delete = "WHERE deleted=0";
        if ($where != '') {
            $delete = $delete . ' AND ' . $where;
        }
        if ($include_deleted) {
            $delete = '';
        }
        $user_str = '';
        if (in_array('author', (array) $user_role)) {
            if ($user_id && $delete == '') {
                $user_str = "WHERE quiz_author_id = '$user_id'";
            } else if ($user_id && $delete !== '') {
                $user_str = " AND quiz_author_id = '$user_id'";
            }
        }
        if ($where != '' && $user_str != '') {
            $user_str = $user_str . ' AND ' . $where;
        }
        $where_str = '';
        if ($user_str == '' && $delete === '' && $where != '') {
            $where_str = "WHERE $where";
        }
        if ($limit != '') {
            $limit = ' limit ' . $offset . ', ' . $limit;
        }
        // Get quizzes and return them
        $delete  = apply_filters( 'quiz_query_delete_clause', $delete );
        $quizzes = $wpdb->get_results(stripslashes($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mlw_quizzes %1s %2s %3s ORDER BY %4s %5s %6s", $delete, $user_str, $where_str, $order_field, $order_direction, $limit)));
        return $quizzes;
    }

    /**
     * Registers a quiz setting
     *
     * @since 5.0.0
     * @param array $field_array An array of the components for the settings field
     */
    public function register_quiz_setting($field_array, $section = 'quiz_options')
    {
        global $mlwQuizMasterNext;
        $mlwQuizMasterNext->quiz_settings->register_setting($field_array, $section);
    }

    /**
     * Retrieves a setting value from a section based on name of section and setting
     *
     * @since 5.0.0
     * @param string $section The name of the section the setting is registered in
     * @param string $setting The name of the setting whose value we need to retrieve
     * @param mixed $default What we need to return if no setting exists with given $setting
     * @return $mixed Value set for $setting or $default if setting does not exist
     */
    public function get_section_setting($section, $setting, $default = false)
    {
        global $mlwQuizMasterNext;
        return apply_filters('qsm_section_setting_text', $mlwQuizMasterNext->quiz_settings->get_section_setting($section, $setting, $default));
    }

    /**
     * Retrieves setting value based on name of setting
     *
     * @since 4.0.0
     * @param string $setting The name of the setting whose value we need to retrieve
     * @param mixed $default What we need to return if no setting exists with given $setting
     * @return $mixed Value set for $setting or $default if setting does not exist
     */
    public function get_quiz_setting($setting, $default = false)
    {
        global $mlwQuizMasterNext;
        return $mlwQuizMasterNext->quiz_settings->get_setting($setting, $default);
    }

    /**
     * Updates a settings value, adding it if it didn't already exist
     *
     * @since 4.0.0
     * @param string $setting The name of the setting whose value we need to retrieve
     * @param mixed $value The value that needs to be stored for the setting
     * @return bool True if successful or false if fails
     */
    public function update_quiz_setting($setting, $value)
    {
        global $mlwQuizMasterNext;
        return $mlwQuizMasterNext->quiz_settings->update_setting($setting, $value);
    }

    /**
     * Outputs the section of input fields
     *
     * @since 5.0.0
     * @since 7.0 Added new parameter settings_fields for default setting
     * @param string $section The section that the settings were registered with
     */
    public function generate_settings_section($section = 'quiz_options', $settings_fields = array())
    {
        global $mlwQuizMasterNext;
        if (empty($settings_fields)) {
            $settings_fields = $mlwQuizMasterNext->quiz_settings->load_setting_fields($section);
        }
        QSM_Fields::generate_section($settings_fields, $section);
    }

    /**
     * Registers Quiz Templates
     *
     * @since 4.5.0
     * @param $name String of the name of the template
     * @param $file_path String of the path to the css file
     */
    public function register_quiz_template($name, $file_path)
    {
        $slug = strtolower(str_replace(" ", "-", $name));
        $this->quiz_templates[$slug] = array(
            "name" => $name,
            "path" => $file_path,
        );
    }

    /**
     * Returns Template Array
     *
     * @since 4.5.0
     * @param $name String of the name of the template. If left empty, will return all templates
     * @return array The array of quiz templates
     */
    public function get_quiz_templates($slug = null)
    {
        if (is_null($slug)) {
            return $this->quiz_templates;
        } elseif (isset($this->quiz_templates[$slug])) {
            return $this->quiz_templates[$slug];
        } else {
            return false;
        }
    }

    /**
     * Register Question Types
     *
     * Adds a question type to the question type array using the parameters given
     *
     * @since 4.0.0
     * @param string $name The name of the Question Type which will be shown when selecting type
     * @param string $display_function The name of the function to call when displaying the question
     * @param bool $graded Tells the plugin if this question is graded or not. This will affect scoring.
     * @param string $review_function The name of the function to call when scoring the question
     * @param string $slug The slug of the question type to be stored with question in database
     * @return void
     */
    public function register_question_type($name, $display_function, $graded, $review_function = null, $edit_args = null, $save_edit_function = null, $slug = null)
    {
        if (is_null($slug)) {
            $slug = strtolower(str_replace(" ", "-", $name));
        } else {
            $slug = strtolower(str_replace(" ", "-", $slug));
        }
        if (is_null($edit_args) || !is_array($edit_args)) {
            $validated_edit_function = array(
                'inputs' => array(
                    'question',
                    'answer',
                    'hint',
                    'correct_info',
                    'comments',
                    'category',
                    'required',
                ),
                'information' => '',
                'extra_inputs' => array(),
                'function' => '',
            );
        } else {
            $validated_edit_function = array(
                'inputs' => $edit_args['inputs'],
                'information' => $edit_args['information'],
                'extra_inputs' => $edit_args['extra_inputs'],
                'function' => $edit_args['function'],
            );
        }
        if (is_null($save_edit_function)) {
            $save_edit_function = '';
        }
        $new_type = array(
            'name' => $name,
            'display' => $display_function,
            'review' => $review_function,
            'graded' => $graded,
            'edit' => $validated_edit_function,
            'save' => $save_edit_function,
            'slug' => $slug,
        );
        $this->question_types[] = $new_type;
    }

    /**
     * Retrieves List Of Question Types
     *
     * retrieves a list of the slugs and names of the question types
     *
     * @since 4.0.0
     * @return array An array which contains the slug and name of question types that have been registered
     */
    public function get_question_type_options()
    {
        $type_array = array();
        foreach ($this->question_types as $type) {
            $type_array[] = array(
                'slug' => $type["slug"],
                'name' => $type["name"],
            );
        }
        return $type_array;
    }

    public function get_question_type_edit_fields()
    {
        $type_array = array();
        foreach ($this->question_types as $type) {
            $type_array[$type["slug"]] = $type["edit"];
        }
        return $type_array;
    }

    /**
     * Displays A Question
     *
     * Retrieves the question types display function and creates the HTML for the question
     *
     * @since 4.0.0
     * @param string $slug The slug of the question type that the question is
     * @param int $question_id The id of the question
     * @param array $quiz_options An array of the columns of the quiz row from the database
     * @return string The HTML for the question
     */
    public function display_question($slug, $question_id, $quiz_options)
    {
        $display = '';
        global $wpdb;
        global $qmn_total_questions;
        $question = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "mlw_questions WHERE question_id=%d", intval($question_id)));
        $answers = array();
        if (is_serialized($question->answer_array) && is_array(maybe_unserialize($question->answer_array))) {
            $answers = maybe_unserialize($question->answer_array);
        } else {
            $mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
            $mlw_answer_array_correct[$question->correct_answer - 1] = 1;
            $answers = array(
                array($question->answer_one, $question->answer_one_points, $mlw_answer_array_correct[0]),
                array($question->answer_two, $question->answer_two_points, $mlw_answer_array_correct[1]),
                array($question->answer_three, $question->answer_three_points, $mlw_answer_array_correct[2]),
                array($question->answer_four, $question->answer_four_points, $mlw_answer_array_correct[3]),
                array($question->answer_five, $question->answer_five_points, $mlw_answer_array_correct[4]),
                array($question->answer_six, $question->answer_six_points, $mlw_answer_array_correct[5]));
        }
        if (2 == $quiz_options->randomness_order || 3 == $quiz_options->randomness_order) {
            shuffle($answers);
            update_post_meta($question_id,'qsm_random_quetion_answer',$answers);
        }
		/**
		 * Filter Answers of specific question before display
		 */
		$answers = apply_filters('qsm_single_question_answers', $answers, $question, $quiz_options);

        foreach ($this->question_types as $type) {
            if ($type["slug"] == strtolower(str_replace(" ", "-", $slug))) {
                if ($type["graded"]) {
                    $qmn_total_questions += 1;
                    if ($quiz_options->question_numbering == 1) {
                        $display .= "<span class='mlw_qmn_question_number'>$qmn_total_questions. </span>";
                    }
                }
                if ($quiz_options->show_category_on_front ) {                    
                    $categories = QSM_Questions::get_question_categories( $question_id );                   
                    if(!empty($categories['category_name'])){
                        $display .= '<div class="quiz-cat">[ ' .implode(',' ,$categories['category_name']) . ' ]</div>';
                    }                    
                }
                $display .= call_user_func($type['display'], intval($question_id), $question->question_name, $answers);
            }
        }
        return $display;
    }

    /**
     * Calculates Score For Question
     *
     * Calculates the score for the question based on the question type
     *
     * @since 4.0.0
     * @param string $slug The slug of the question type that the question is
     * @param int $question_id The id of the question
     * @return array An array of the user's score from the question
     */
    public function display_review($slug, $question_id)
    {
        $results_array = array();
        global $wpdb;
        $question = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "mlw_questions WHERE question_id=%d", intval($question_id)));
        $answers = array();
        if (is_serialized($question->answer_array) && is_array(@unserialize($question->answer_array))) {
            $answers = @unserialize($question->answer_array);
        } else {
            $mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
            $mlw_answer_array_correct[$question->correct_answer - 1] = 1;
            $answers = array(
                array($question->answer_one, $question->answer_one_points, $mlw_answer_array_correct[0]),
                array($question->answer_two, $question->answer_two_points, $mlw_answer_array_correct[1]),
                array($question->answer_three, $question->answer_three_points, $mlw_answer_array_correct[2]),
                array($question->answer_four, $question->answer_four_points, $mlw_answer_array_correct[3]),
                array($question->answer_five, $question->answer_five_points, $mlw_answer_array_correct[4]),
                array($question->answer_six, $question->answer_six_points, $mlw_answer_array_correct[5]));
        }
        foreach ($this->question_types as $type) {
            if ($type["slug"] == strtolower(str_replace(" ", "-", $slug))) {
                if (!is_null($type["review"])) {
                    $results_array = call_user_func($type['review'], intval($question_id), $question->question_name, $answers);
                } else {
                    $results_array = array('null_review' => true);
                }
            }
        }
        return $results_array;
    }

    /**
     * Retrieves A Question Setting
     *
     * Retrieves a setting stored in the question settings array
     *
     * @since 4.0.0
     * @param int $question_id The id of the question
     * @param string $setting The name of the setting
     * @return string The value stored for the setting
     */
    public function get_question_setting($question_id, $setting)
    {
        global $wpdb;
        $qmn_settings_array = '';
        $settings = $wpdb->get_var($wpdb->prepare("SELECT question_settings FROM " . $wpdb->prefix . "mlw_questions WHERE question_id=%d", $question_id));
        if (is_serialized($settings) && is_array(@unserialize($settings))) {
            $qmn_settings_array = @unserialize($settings);
        }
        if (is_array($qmn_settings_array) && isset($qmn_settings_array[$setting])) {
            return $qmn_settings_array[$setting];
        } else {
            return '';
        }
    }

    /**
     * Registers Addon Settings Tab
     *
     * Registers a new tab on the addon settings page
     *
     * @since 4.0.0
     * @param string $title The name of the tab
     * @param string $function The function that displays the tab's content
     * @return void
     */
    public function register_addon_settings_tab($title, $function)
    {
        $slug = strtolower(str_replace(" ", "-", $title));
        $new_tab = array(
            'title' => $title,
            'function' => $function,
            'slug' => $slug,
        );
        $this->addon_tabs[] = $new_tab;
    }

    /**
     * Retrieves Addon Settings Tab Array
     *
     * Retrieves the array of titles and functions of the registered tabs
     *
     * @since 4.0.0
     * @return array The array of registered tabs
     */
    public function get_addon_tabs()
    {
        return $this->addon_tabs;
    }

    /**
     * Registers Stats Tab
     *
     * Registers a new tab on the stats page
     *
     * @since 4.3.0
     * @param string $title The name of the tab
     * @param string $function The function that displays the tab's content
     * @return void
     */
    public function register_stats_settings_tab($title, $function)
    {
        $slug = strtolower(str_replace(" ", "-", $title));
        $new_tab = array(
            'title' => $title,
            'function' => $function,
            'slug' => $slug,
        );
        $this->stats_tabs[] = $new_tab;
    }

    /**
     * Retrieves Stats Tab Array
     *
     * Retrieves the array of titles and functions of the registered tabs
     *
     * @since 4.3.0
     * @return array The array of registered tabs
     */
    public function get_stats_tabs()
    {
        return $this->stats_tabs;
    }

    /**
     * Registers tabs for the Admin Results page
     *
     * Registers a new tab on the admin results page
     *
     * @since 5.0.0
     * @param string $title The name of the tab
     * @param string $function The function that displays the tab's content
     * @return void
     */
    public function register_admin_results_tab($title, $function)
    {
        $slug = strtolower(str_replace(" ", "-", $title));
        $new_tab = array(
            'title' => $title,
            'function' => $function,
            'slug' => $slug,
        );
        $this->admin_results_tabs[] = $new_tab;
    }

    /**
     * Retrieves Admin Results Tab Array
     *
     * Retrieves the array of titles and functions for the tabs registered for the admin results page
     *
     * @since 5.0.0
     * @return array The array of registered tabs
     */
    public function get_admin_results_tabs()
    {
        return $this->admin_results_tabs;
    }

    /**
     * Registers Results Tab
     *
     * Registers a new tab on the results page
     *
     * @since 4.1.0
     * @param string $title The name of the tab
     * @param string $function The function that displays the tab's content
     * @return void
     */
    public function register_results_settings_tab($title, $function)
    {
        $slug = strtolower(str_replace(" ", "-", $title));
        $new_tab = array(
            'title' => $title,
            'function' => $function,
            'slug' => $slug,
        );
        $this->results_tabs[] = $new_tab;
    }

    /**
     * Retrieves Results Tab Array
     *
     * Retrieves the array of titles and functions of the registered tabs
     *
     * @since 4.1.0
     * @return array The array of registered tabs
     */
    public function get_results_tabs()
    {
        return $this->results_tabs;
    }

    /**
     * Registers Quiz Settings Tab
     *
     * Registers a new tab on the quiz settings page
     *
     * @since 4.0.0
     * @param string $title The name of the tab
     * @param string $function The function that displays the tab's content
     * @return void
     */
    public function register_quiz_settings_tabs($title, $function)
    {
        $slug = strtolower(str_replace(" ", "-", $title));
        $new_tab = array(
            'title' => $title,
            'function' => $function,
            'slug' => $slug,
        );
        $this->settings_tabs[] = $new_tab;
    }

    /**
     * Echos Registered Tabs Title Link
     *
     * Echos the title link of the registered tabs
     *
     * @since 4.0.0
     * @return array The array of registered tabs
     */
    public function get_settings_tabs()
    {
        return apply_filters('qmn_quiz_setting_tabs' , $this->settings_tabs);
    }

    	/**
	 * global animatiocv array return
	 *
	 * @since 4.7.1
	 */
	public function quiz_animation_effect( ) {
		

		return array(
			array(
				'label' => __( 'bounce', 'quiz-master-next' ),
				'value' => 'bounce',
			),
			array(
				'label' => __( 'flash', 'quiz-master-next' ),
				'value' => 'flash',
			),
			array(
				'label' => __( 'pulse', 'quiz-master-next' ),
				'value' => 'pulse',
			),
			array(
				'label' => __( 'rubberBand', 'quiz-master-next' ),
				'value' => 'rubberBand',
			),
			array(
				'label' => __( 'shake', 'quiz-master-next' ),
				'value' => 'shake',
			),
			array(
				'label' => __( 'swing', 'quiz-master-next' ),
				'value' => 'swing',
			),
			array(
				'label' => __( 'tada', 'quiz-master-next' ),
				'value' => 'tada',
			),
			array(
				'label' => __( 'wobble', 'quiz-master-next' ),
				'value' => 'wobble',
			),
			array(
				'label' => __( 'jello', 'quiz-master-next' ),
				'value' => 'jello',
			),
			array(
				'label' => __( 'heartBeat', 'quiz-master-next' ),
				'value' => 'heartBeat',
			),
			array(
				'label' => __( 'No animation', 'quiz-master-next' ),
				'value' => '',
			),
		);

	}

    /**
	 * converts dates into preferred date format
	 *
	 * @since 7.3.3
	 * @param array $qsm_qna_array The array of results for the quiz
	 * @uses QMNQuizManager:submit_results() submits and displays results
     * @uses qsm_generate_results_details_tab() generates admin results page
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_to_preferred_date_format($qsm_qna_array){
        global $mlwQuizMasterNext;
        $quiz_options= $mlwQuizMasterNext->quiz_settings->get_quiz_options();
        $qsm_quiz_settings = unserialize($quiz_options->quiz_settings);
        $qsm_quiz_options=unserialize($qsm_quiz_settings['quiz_options']);
        $qsm_global_settings = get_option( 'qsm-quiz-settings' );
        //check if preferred date format is set at quiz level or plugin level. Default to WP date format otherwise
        if (isset($qsm_quiz_options['preferred_date_format'])){
            $preferred_date_format= $qsm_quiz_options['preferred_date_format'];
        } elseif ( isset($qsm_global_settings['preferred_date_format']) ){
            $preferred_date_format= isset($qsm_global_settings['preferred_date_format']);
        }else{
            $preferred_date_format= get_option( 'date_format');
        }
        //filter date format
        $GLOBALS['qsm_date_format']= apply_filters('qms_preferred_date_format', $preferred_date_format);

        $qsm_qna_array = $this->convert_contacts_to_preferred_date_format($qsm_qna_array);
        $qsm_qna_array = $this->convert_answers_to_preferred_date_format($qsm_qna_array);
        $this->convert_questions_to_preferred_date_format();        
           
        return $qsm_qna_array;
	}

    /**
	 * converts contacts into preferred date format
	 *
	 * @since 7.3.3
	 * @param array $qsm_qna_array The array of results for the quiz
	 * @uses convert_to_preferred_date_format()
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_contacts_to_preferred_date_format($qsm_qna_array){

        $qsm_contact_array = $qsm_qna_array['contact'];
        foreach ( $qsm_contact_array as $qsm_contact_id => $qsm_contact){
            if ("date" === $qsm_contact['type'] && null!==$GLOBALS['qsm_date_format']){
                $qsm_qna_array['contact'][$qsm_contact_id]['value']=date_i18n( $GLOBALS['qsm_date_format'], strtotime(($qsm_contact['value'])));
            }
        }
        return $qsm_qna_array;
	}

    /**
	 * converts answers into preferred date format
	 *
	 * @since 7.3.3
	 * @param array $qsm_qna_array The array of results for the quiz
	 * @uses convert_to_preferred_date_format()
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_answers_to_preferred_date_format($qsm_qna_array){

        $qsm_qna_list = $qsm_qna_array['question_answers_array'];
        foreach ($qsm_qna_list as $qna_id => $qna){
            if ("12"===$qna['question_type'] && null!==$GLOBALS['qsm_date_format']){
                $qsm_qna_array['question_answers_array'][$qna_id]['1']= date_i18n( $GLOBALS['qsm_date_format'], strtotime(($qna['1'])));
                $qsm_qna_array['question_answers_array'][$qna_id]['2']=  date_i18n( $GLOBALS['qsm_date_format'], strtotime(($qna['2'])));           
            }
        }
        return $qsm_qna_array;
	}

    /**
	 * converts questions into preferred date format
	 *
	 * @since 7.3.3
	 * @param array $qsm_qna_array The array of results for the quiz
	 * @uses convert_to_preferred_date_format()
	 * @return array $qsm_qna_array date formatted array of results for the quiz
	 */

	public function convert_questions_to_preferred_date_format(){
        
        if(!function_exists('qsm_convert_question_array_date_format')){
            function qsm_convert_question_array_date_format($questions){	
                foreach ($questions as $question_id => $question_to_convert){
                    if("12"=== $question_to_convert['question_type_new']){
                        foreach ($question_to_convert['answers'] as $answer_id => $answer_value){
                            $questions[$question_id]['answers'][$answer_id][0]= date_i18n( $GLOBALS['qsm_date_format'], strtotime($answer_value[0]));
                        }	
                    }
                }
                return $questions;
            }
        } 
        add_filter( 'qsm_load_questions_by_pages','qsm_convert_question_array_date_format');              
	}

    /**
	 * 
	 *
	 * @since 7.3.5
	 * @param array 
	 * @uses 
	 * @return array 
	 */

    public function qsm_results_css_inliner($html){

        $incorrect_answer="<span style='color:red;display:block;margin-bottom:5px;'>&#x2715;";
		$correct_answer="<span style='color:green;display:block;margin-bottom:5px;'>&#10003;";
		$simple_answer="<span style='color:#808080;display:block;margin-bottom:5px;'>&#8226;&nbsp;";
		$html = str_replace( '<br/>', '<br>', $html );
		$html = str_replace( '<br />', '<br>', $html );
		$html = str_replace( "<span class='qmn_user_incorrect_answer'>", "<span style='color:red'>&#x2715; ", $html );
		$html = str_replace( "<span class='qmn_user_correct_answer'>", "<span style='color:green'>&#10003; ", $html );
		$html = str_replace( '<span class="qsm-text-wrong-option qmn_image_option">', "$incorrect_answer ", $html );
		$html = str_replace( '<span class="qsm-text-correct-option qmn_image_option">', "$correct_answer ", $html );
		$html = str_replace( '<span class="qsm-text-correct-option qsm-text-user-correct-answer qmn_image_option">', "$correct_answer ", $html );
		$html = str_replace( '<span class="qsm-text-simple-option qmn_image_option">', "$simple_answer ", $html );
		$html = str_replace( '<span class="qsm-text-correct-option qsm-text-user-correct-answer ">', "$correct_answer ", $html );
		$html = str_replace( '<span class="qsm-text-simple-option ">', "$simple_answer ", $html );
		$html = str_replace( '<span class="qsm-text-wrong-option ">', "$incorrect_answer ", $html );
		$html = str_replace( '<span class="qsm-text-correct-option ">', "$correct_answer ", $html );
		$html = str_replace( '<span class="qmn_user_incorrect_answer">', "$incorrect_answer ", $html );
		$html = str_replace( '<span class="qmn_user_incorrect_answer">', "$correct_answer ", $html );
		$html = str_replace( "class='qmn_question_answer", "style='margin-bottom:30px' class='", $html );

        return $html;
    }
}
