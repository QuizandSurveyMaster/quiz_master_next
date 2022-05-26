<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles installation, updates, and plugin row meta
 *
 * @since 4.7.1
 */
class QSM_Install {
	
	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * Please note that these functions are invoked when QSM is updated from a previous version,
	 * but NOT when QSM is newly installed.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'8.0' => array(
			'qsm_update_80',
			'qsm_update_80_db_version'
		),
	);

	/**
	 * Main Constructor
	 *
	 * @uses QSM_Install::add_hooks
	 * @since 4.7.1
	 */
	function __construct() {
		$this->add_hooks();
	}

	/**
	 * Adds the various class functions to hooks and filters
	 *
	 * @since 4.7.1
	 */
	public function add_hooks() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'register_default_settings' ) );
	}
	
	/**
	 * Check QSM version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( version_compare( get_option( 'qsm_version' ), QSM()->version, '<' ) ) {
			self::update_qsm_version();
			self::maybe_update_db_version();
		}
	}

	/**
	 * Adds the default quiz settings
	 *
	 * @since 5.0.0
	 */
	public function register_default_settings() {

		global $mlwQuizMasterNext;

		// Registers system setting
		$field_array = array(
			'id'      => 'form_type',
			'label'   => __( 'Quiz Type', 'quiz-master-next' ),
			'type'    => 'select',
			'options' => array(
				array(
					'label' => __( 'Quiz', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'Survey', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'Simple Form', 'quiz-master-next' ),
					'value' => 2,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers system setting
		$field_array = array(
			'id'          => 'system',
			'label'       => __( 'Grading System', 'quiz-master-next' ),
			'type'        => 'radio',
			'options'     => array(
				array(
					'label' => __( 'Correct/Incorrect', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'Points', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'Both', 'quiz-master-next' ),
					'value' => 3,
				),
			),
			'default'     => 0,
			'help'        => __( 'Select the system for grading the quiz.', 'quiz-master-next' ),
			'tooltip'     => __( 'To know more about our grading systems please ', 'quiz-master-next' ) . '<a target="_blank" href="https://quizandsurveymaster.com/docs/">' . __( 'read the documentation.', 'quiz-master-next' ) . '</a>',
			'show_option' => 'form_type_0',
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers correct_answer_logic field
		$field_array = array(
			'id'          => 'correct_answer_logic',
			'label'       => __( 'Correct Answer Logic', 'quiz-master-next' ),
			'type'        => 'radio',
			'options'     => array(
				array(
					'label' => __( 'All correct answers', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'Any correct answer', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default'     => 0,
			'show_option' => 'qsm_hidden_tr_gradingsystem',
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers default number of answers field
		$field_array = array(
			'id'      => 'default_answers',
			'label'   => __( 'Default Number of Answers', 'quiz-master-next' ),
			'type'    => 'number',
			'options' => array(),
			'default' => 1,
			'help'    => __( 'Adds number of answer fields', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers Rounding setting
		$field_array = array(
			'id'          => 'score_roundoff',
			'label'       => __( 'Allow Score Round-off', 'quiz-master-next' ),
			'type'        => 'checkbox',
			'options'     => array(
				array(
					'value' => 1,
				),
			),
			'default'     => 0,
			'show_option' => 'form_type_0',
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers progress_bar setting
		$field_array = array(
			'id'      => 'progress_bar',
			'label'   => __( 'Show progress bar', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers require_log_in setting
		$field_array = array(
			'id'      => 'require_log_in',
			'label'   => __( 'Require User Login', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'Enabling this allows only logged in users to take the quiz', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers pagination setting
		$field_array = array(
			'id'      => 'pagination',
			'label'   => __( 'Questions Per Page', 'quiz-master-next' ),
			'type'    => 'number',
			'options' => array(),
			'default' => 0,
			'help'    => __( 'Override the default pagination created on questions tab', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers timer_limit setting
		$field_array = array(
			'id'      => 'timer_limit',
			'label'   => __( 'Time Limit (in minutes)', 'quiz-master-next' ),
			'type'    => 'number',
			'options' => array(),
			'default' => 0,
			'help'    => __( 'Leave 0 for no time limit', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Settings for quick result
		$field_array = array(
			'id'      => 'enable_result_after_timer_end',
			'label'   => __( 'Force submit after timer expiry', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		$field_array = array(
			'id'      => 'skip_validation_time_expire',
			'label'   => __( 'Skip validations when time expire', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 1,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers total_user_tries setting
		$field_array = array(
			'id'      => 'total_user_tries',
			'label'   => __( 'Limit Attempts', 'quiz-master-next' ),
			'type'    => 'number',
			'options' => array(),
			'default' => 0,
			'help'    => __( 'Leave 0 for unlimited attempts', 'quiz-master-next' ),
			'tooltip' => __( 'Limits how many times a user can take the quiz', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers limit_total_entries setting
		$field_array = array(
			'id'      => 'limit_total_entries',
			'label'   => __( 'Limit Entries', 'quiz-master-next' ),
			'type'    => 'number',
			'options' => array(),
			'default' => 0,
			'help'    => __( 'Leave 0 for unlimited entries', 'quiz-master-next' ),
			'tooltip' => __( 'Limits how many users can take the quiz.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers question_from_total setting
		$field_array = array(
			'id'      => 'question_from_total',
			'label'   => __( 'Limit number of Questions', 'quiz-master-next' ),
			'type'    => 'number',
			'options' => array(),
			'default' => 0,
			'help'    => __( 'Leave 0 to load all questions', 'quiz-master-next' ),
			'tooltip' => __( 'Show only limited number of questions from your quiz.', 'quiz-master-next' ),
		);

		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers question_per_category setting
		$field_array = array(
			'id'      => 'question_per_category',
			'label'   => __( 'Limit number of Questions Per Category ', 'quiz-master-next' ),
			'type'    => 'number',
			'options' => array(),
			'default' => 0,
			'help'    => __( 'Leave 0 to load all questions. You also need to set Limit Number of questions, as well as select Question Categories', 'quiz-master-next' ),
			'tooltip' => __( 'Show only limited number of category questions from your quiz.You also need to set Limit Number of questions.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers category setting
		$field_array = array(
			'id'      => 'randon_category',
			'label'   => __( 'Questions Categories', 'quiz-master-next' ),
			'type'    => 'category',
			'default' => '',
			'help'    => __( 'Questions will load only from selected categories.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers randomness_order setting
		$field_array = array(
			'id'      => 'randomness_order',
			'label'   => __( 'Random Questions', 'quiz-master-next' ),
			'type'    => 'select',
			'options' => array(
				array(
					'label' => __( 'Random Questions', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'Random Questions And Answers', 'quiz-master-next' ),
					'value' => 2,
				),
				array(
					'label' => __( 'Random Answers', 'quiz-master-next' ),
					'value' => 3,
				),
				array(
					'label' => __( 'Disabled', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'tooltip' => __( 'Randomize the order of questions or answers every time a quiz loads', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers scheduled_time_start setting
		$field_array = array(
			'id'      => 'scheduled_time_start',
			'label'   => __( 'Quiz Dates', 'quiz-master-next' ),
			'type'    => 'date',
			'options' => array(),
			'default' => '',
			'help'    => '',
			'ph_text' => __( 'Start Date', 'quiz-master-next' ),
			'help'    => __( 'If set, Quiz will be accessible only after this date', 'quiz-master-next' ),
			'tooltip' => __( 'Leave blank for no date limit', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers scheduled_time_end setting
		$field_array = array(
			'id'      => 'scheduled_time_end',
			'label'   => '',
			'type'    => 'date',
			'options' => array(),
			'default' => '',
			'help'    => __( ' If set, Quiz will not be accessible after this date', 'quiz-master-next' ),
			'ph_text' => __( 'End Date', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );
		$field_array = array(
			'id'      => 'not_allow_after_expired_time',
			'label'   => __( 'Do not allow quiz submission after the end date/time', 'quiz-master-next' ),
			'type'    => 'checkbox',
			'options' => array(
				array(
					'value' => 1,
				),
			),
			'default' => 0,
			'ph_text' => '',
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers contact_info_location setting
		$field_array = array(
			'id'      => 'contact_info_location',
			'label'   => __( 'Contact form position', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Show before quiz begins', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'Show after the quiz ends', 'quiz-master-next' ),
					'value' => 1,
				),
			),
			'default' => 0,
			'help'    => __( 'Select when to display the contact form', 'quiz-master-next' ),
			'tooltip' => __( 'The form can be configured in Contact tab', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers loggedin_user_contact setting
		$field_array = array(
			'id'      => 'loggedin_user_contact',
			'label'   => __( 'Show contact form to logged in users', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 1,
				),
			),
			'default' => 0,
			'help'    => __( 'Logged in users can edit their contact information', 'quiz-master-next' ),
			'tooltip' => __( 'The information will still get saved if this option is disabled', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers comment_section setting
		$field_array = array(
			'id'      => 'comment_section',
			'label'   => __( 'Enable comments', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 1,
				),
			),
			'default' => 1,
			'help'    => __( 'Allow users to enter their comments after the quiz', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers question_numbering setting
		$field_array = array(
			'id'      => 'question_numbering',
			'label'   => __( 'Show question numbers', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers show-optin setting
		$field_array = array(
			'id'      => 'show_optin',
			'label'   => __( 'Show Opt-in type Answers to User', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers store_responses setting
		$field_array = array(
			'id'      => 'store_responses',
			'label'   => __( 'Save Responses', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 1,
			'help'    => __( 'The results will be permanently stored in a database', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers disable_answer_onselect setting
		$field_array = array(
			'id'      => 'disable_answer_onselect',
			'label'   => __( 'Disable change of answers', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'Works with multiple choice questions only', 'quiz-master-next' ),
			'tooltip' => __( 'The question will be disabled once an answer is selected', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers ajax_show_correct setting
		$field_array = array(
			'id'      => 'ajax_show_correct',
			'label'   => __( 'Add class for correct/incorrect answers', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'Works with multiple choice questions only', 'quiz-master-next' ),
			'tooltip' => __( 'Dynamically add class for incorrect/correct answer after user selects answer.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers hide_auto fill setting
		$field_array = array(
			'id'      => 'contact_disable_autofill',
			'label'   => __( 'Disable auto fill for contact input', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		$field_array = array(
			'id'      => 'form_disable_autofill',
			'label'   => __( 'Disable auto fill for Quiz input', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers show category on front setting
		$field_array = array(
			'id'      => 'show_category_on_front',
			'label'   => __( 'Display category name on front end', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Settings for quick result
		$field_array = array(
			'id'      => 'enable_quick_result_mc',
			'label'   => __( 'Show results inline', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'Instantly displays the result for each question', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		$field_array = array(
			'id'      => 'end_quiz_if_wrong',
			'label'   => __( 'End quiz if there is wrong answer', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'This option works with vertical Multiple Choice , horizontal Multiple Choice , drop down , multiple response and horizontal multiple response question types', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Settings for quick result
		$field_array = array(
			'id'      => 'enable_quick_correct_answer_info',
			'label'   => __( 'Show correct answer inline', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes When answer is correct', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'Yes Independent of correct/incorrect', 'quiz-master-next' ),
					'value' => 2,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'Show correct user info when inline result is enabled.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Setting for retake quiz
		$field_array = array(
			'id'      => 'enable_retake_quiz_button',
			'label'   => __( 'Retake Quiz', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'Show a button on result page to retake the quiz', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Setting for pagination of quiz
		$field_array = array(
			'id'      => 'enable_pagination_quiz',
			'label'   => __( 'Show current page number', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Setting for pagination of quiz
		$field_array = array(
			'id'      => 'enable_deselect_option',
			'label'   => __( 'Deselect Answer', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			'help'    => __( 'Users are able deselect an answer and leave it blank. Works with Multiple Choice and Horizintal Multiple Choice questions only', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Setting for pagination of quiz
		$field_array = array(
			'id'      => 'disable_description_on_result',
			'label'   => __( 'Disable description on quiz result page?', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Setting for pagination of quiz
		$field_array = array(
			'id'      => 'disable_scroll_next_previous_click',
			'label'   => __( 'Disable scroll on next and previous button click?', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Setting for display first page
		$field_array = array(
			'id'      => 'disable_first_page',
			'label'   => __( 'Disable first page on quiz', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Setting for animation
		$field_array = array(
			'id'      => 'quiz_animation',
			'label'   => __( 'Quiz Animation', 'quiz-master-next' ),
			'type'    => 'select',
			'options' => $mlwQuizMasterNext->pluginHelper->quiz_animation_effect(),
			'default' => '',
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// result page for sharing
		$field_array = array(
			'id'      => 'result_page_fb_image',
			'label'   => __( 'Logo URL', 'quiz-master-next' ),
			'type'    => 'url',
			'default' => QSM_PLUGIN_URL . 'assets/icon-200x200.png',
			'tooltip' => __( 'Enter the url of an image which will be used as logo while sharing on facebook.', 'quiz-master-next' ),
			'help'    => __( 'If left blank, this will default to QSM logo', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers Preferred date type settings in the quiz options
		$field_array = array(
			'id'      => 'preferred_date_format',
			'label'   => __( 'Preferred Date Format', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => isset( get_option( 'qsm-quiz-settings' )['preferred_date_format'] ) ? get_option( 'qsm-quiz-settings' )['preferred_date_format'] : get_option( 'date_format' ),
			'help'    => __( 'Overrides global settings for preferred date format', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		do_action( 'qsm_extra_setting_fields' );
		// Setting for animation
		$field_array = array(
			'id'      => 'legacy_options',
			'label'   => __( 'Show Legacy Options', 'quiz-master-next' ),
			'type'    => 'hide_show',
			'default' => '',
			'help'    => __( 'All the legacy options are deprecated and will be removed in upcoming version', 'quiz-master-next' ),
			// 'tooltip' => __('All the legacy options are deprecated and will be removed in upcoming version', 'quiz-master-next')
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers social_media setting
		$field_array = array(
			'id'      => 'social_media',
			'label'   => __( 'Social Sharing Buttons', 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 1,
				),
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 0,
				),
			),
			'default' => 0,
			/* translators: %FACEBOOK_SHARE%: Facebook share link, %TWITTER_SHARE%: Twitter share link */
			'tooltip' => __( 'Please use the new template variables instead.%FACEBOOK_SHARE% %TWITTER_SHARE%', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers user_name setting
		$field_array = array(
			'id'      => 'user_name',
			'label'   => __( "Ask user's name", 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 2,
				),
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'Require', 'quiz-master-next' ),
					'value' => 1,
				),
			),
			'default' => 2,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers user_comp setting
		$field_array = array(
			'id'      => 'user_comp',
			'label'   => __( "Ask user's business", 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 2,
				),
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'Require', 'quiz-master-next' ),
					'value' => 1,
				),
			),
			'default' => 2,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers user_email setting
		$field_array = array(
			'id'      => 'user_email',
			'label'   => __( "Ask user's email", 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 2,
				),
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'Require', 'quiz-master-next' ),
					'value' => 1,
				),
			),
			'default' => 2,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers user_phone setting
		$field_array = array(
			'id'      => 'user_phone',
			'label'   => __( "Ask user's phone", 'quiz-master-next' ),
			'type'    => 'radio',
			'options' => array(
				array(
					'label' => __( 'No', 'quiz-master-next' ),
					'value' => 2,
				),
				array(
					'label' => __( 'Yes', 'quiz-master-next' ),
					'value' => 0,
				),
				array(
					'label' => __( 'Require', 'quiz-master-next' ),
					'value' => 1,
				),
			),
			'default' => 2,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_options' );

		// Registers message_before setting
		$field_array = array(
			'id'        => 'message_before',
			'label'     => __( 'Message Displayed Before Quiz', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers message_comment setting
		$field_array = array(
			'id'        => 'message_comment',
			'label'     => __( 'Message Displayed Before Comments Box If Enabled', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers message_end_template setting
		$field_array = array(
			'id'        => 'message_end_template',
			'label'     => __( 'Message Displayed At End Of Quiz (Leave Blank To Omit Text Section)', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers total_user_tries_text setting
		$field_array = array(
			'id'        => 'total_user_tries_text',
			'label'     => __( 'Message Displayed If User Has Tried Quiz Too Many Times', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers require_log_in_text setting
		$field_array = array(
			'id'        => 'require_log_in_text',
			'label'     => __( 'Message Displayed If User Is Not Logged In And Quiz Requires Users To Be Logged In', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers scheduled_timeframe_text setting
		$field_array = array(
			'id'        => 'scheduled_timeframe_text',
			'label'     => __( 'Message Displayed If Date Is Outside Scheduled Timeframe', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers limit_total_entries_text setting
		$field_array = array(
			'id'        => 'limit_total_entries_text',
			'label'     => __( 'Message Displayed If The Limit Of Total Entries Has Been Reached', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers question_answer_template setting
		$field_array = array(
			'id'        => 'question_answer_template',
			'label'     => __( 'Results Page %QUESTIONS_ANSWERS% Text', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%QUESTION%',
				'%USER_ANSWERS_DEFAULT%',
				'%USER_ANSWER%',
				'%CORRECT_ANSWER%',
				'%USER_COMMENTS%',
				'%CORRECT_ANSWER_INFO%',
				'%QUESTION_POINT_SCORE%',
				'%QUESTION_MAX_POINTS%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers question_answer_template setting
		$field_array = array(
			'id'        => 'question_answer_email_template',
			'label'     => __( '%QUESTIONS_ANSWERS_EMAIL% Text', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => '%QUESTION%<br />Answer Provided: %USER_ANSWER%<br/>Correct Answer: %CORRECT_ANSWER%<br/>Comments Entered: %USER_COMMENTS%',
			'variables' => array(
				'%QUESTION%',
				'%USER_ANSWER%',
				'%CORRECT_ANSWER%',
				'%USER_COMMENTS%',
				'%CORRECT_ANSWER_INFO%',
				'%QUESTION_POINT_SCORE%',
				'%QUESTION_MAX_POINTS%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers twitter_sharing_text setting
		$field_array = array(
			'id'        => 'twitter_sharing_text',
			'label'     => __( 'Twitter Sharing Text', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%POINT_SCORE%',
				'%AVERAGE_POINT%',
				'%AMOUNT_CORRECT%',
				'%TOTAL_QUESTIONS%',
				'%CORRECT_SCORE%',
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%RESULT_LINK%',
				'%TIMER%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers facebook_sharing_text setting
		$field_array = array(
			'id'        => 'facebook_sharing_text',
			'label'     => __( 'Facebook Sharing Text', 'quiz-master-next' ),
			'type'      => 'editor',
			'default'   => 0,
			'variables' => array(
				'%POINT_SCORE%',
				'%AVERAGE_POINT%',
				'%AMOUNT_CORRECT%',
				'%TOTAL_QUESTIONS%',
				'%CORRECT_SCORE%',
				'%QUIZ_NAME%',
				'%QUIZ_LINK%',
				'%RESULT_LINK%',
				'%TIMER%',
				'%CURRENT_DATE%',
			),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers submit_button_text setting
		$field_array = array(
			'id'      => 'button_section',
			'label'   => __( 'Buttons', 'quiz-master-next' ),
			'type'    => 'section_heading',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		$field_array = array(
			'id'      => 'submit_button_text',
			'label'   => __( 'Submit Button', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		$field_array = array(
			'id'      => 'retake_quiz_button_text',
			'label'   => __( 'Retake Quiz Button', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'Retake Quiz', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers previous_button_text setting
		$field_array = array(
			'id'      => 'previous_button_text',
			'label'   => __( 'Previous button', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers next_button_text setting
		$field_array = array(
			'id'      => 'next_button_text',
			'label'   => __( 'Next button', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers submit_button_text setting
		$field_array = array(
			'id'      => 'validation_text_section',
			'label'   => __( 'Validation Messages', 'quiz-master-next' ),
			'type'    => 'section_heading',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers empty_error_text setting
		$field_array = array(
			'id'      => 'empty_error_text',
			'label'   => __( 'All required fields', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'Please complete all required fields!', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers email_error_text setting
		$field_array = array(
			'id'      => 'email_error_text',
			'label'   => __( 'Invalid email', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'Not a valid e-mail address!', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers number_error_text setting
		$field_array = array(
			'id'      => 'number_error_text',
			'label'   => __( 'Invalid number', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'This field must be a number!', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers incorrect_error_text setting
		$field_array = array(
			'id'      => 'incorrect_error_text',
			'label'   => __( 'Invalid Captcha', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'The entered text is not correct!', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers submit_button_text setting
		$field_array = array(
			'id'      => 'other_text_section',
			'label'   => __( 'Other', 'quiz-master-next' ),
			'type'    => 'section_heading',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers comment_field_text setting
		$field_array = array(
			'id'      => 'comment_field_text',
			'label'   => __( 'Comments field', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers hint_text setting
		$field_array = array(
			'id'      => 'hint_text',
			'label'   => __( 'Hint Text', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'Hint', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers quick result correct answer setting
		$field_array = array(
			'id'      => 'quick_result_correct_answer_text',
			'label'   => __( 'Correct answer message', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'Correct! You have selected correct answer.', 'quiz-master-next' ),
			'tooltip' => __( 'Text to show when the selected option is correct answer.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers quick result wrong answer setting
		$field_array = array(
			'id'      => 'quick_result_wrong_answer_text',
			'label'   => __( 'Incorrect answer message', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => __( 'Wrong! You have selected wrong answer.', 'quiz-master-next' ),
			'tooltip' => __( 'Text to show when the selected option is wrong answer.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers quick result wrong answer setting
		$field_array = array(
			'id'      => 'quiz_processing_message',
			'label'   => __( 'Quiz Submit/Processing Message', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => '',
			'tooltip' => __( 'Text to show while submitting the quiz.', 'quiz-master-next' ),
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Setting for animation
		$field_array = array(
			'id'      => 'legacy_options',
			'label'   => __( 'Show Legacy Options', 'quiz-master-next' ),
			'type'    => 'hide_show',
			'default' => '',
			'help'    => __( 'All the legacy options are deprecated and will be removed in upcoming version', 'quiz-master-next' ),
			// 'tooltip' => __('All the legacy options are deprecated and will be removed in upcoming version', 'quiz-master-next')
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers name_field_text setting
		$field_array = array(
			'id'      => 'name_field_text',
			'label'   => __( 'Name field', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers business_field_text setting
		$field_array = array(
			'id'      => 'business_field_text',
			'label'   => __( 'Business field', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers email_field_text setting
		$field_array = array(
			'id'      => 'email_field_text',
			'label'   => __( 'Email field', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );

		// Registers phone_field_text setting
		$field_array = array(
			'id'      => 'phone_field_text',
			'label'   => __( 'Phone number field', 'quiz-master-next' ),
			'type'    => 'text',
			'default' => 0,
		);
		$mlwQuizMasterNext->pluginHelper->register_quiz_setting( $field_array, 'quiz_text' );
	}

	/**
	 * Installs the plugin and its database tables
	 *
	 * @since 4.7.1
	 */
	public static function install() {
		set_transient( 'qsm_installing', 'yes', MINUTE_IN_SECONDS * 10 );

		/**
		 * Create Required Database Tables.
		 */
		self::create_tables();
		self::update_qsm_version();
		self::maybe_update_db_version();

		/**
		 * Set migration flags for fresh install
		 */
		update_option( 'qsm_quizzes_migrated', gmdate( time() ) );
		update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
		update_option( 'qsm_db_migrated', gmdate( time() ) );

		delete_transient( 'qsm_installing' );
		/**
		 * Action after QSM plugin Installed.
		 */
		do_action( 'qsm_installed' );

		/**
		 * Create a folder in upload folder
		 */
		$upload      = wp_upload_dir();
		$upload_dir  = $upload['basedir'];
		$upload_dir  = $upload_dir . '/qsm_themes';
		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir, 0700 );
		}
		flush_rewrite_rules();
	}

	/**
	 * Return a list of tables.
	 *
	 * @return array QSM tables.
	 */
	public static function get_tables() {
		global $wpdb;
		$tables = array(
			"{$wpdb->prefix}qsm_quizzes",
			"{$wpdb->prefix}qsm_meta",
			"{$wpdb->prefix}qsm_questions",
			"{$wpdb->prefix}qsm_terms",
			"{$wpdb->prefix}qsm_answers",
			"{$wpdb->prefix}qsm_results",
			"{$wpdb->prefix}qsm_result_meta",
		);

		/**
		 * Filter the list of known QSM tables.
		 *
		 * If QSM plugins need to add new tables, they can inject them here.
		 *
		 * @param array $tables An array of QSM-specific database table names.
		 */
		$tables = apply_filters( 'qsm_get_db_tables', $tables );

		return $tables;
	}

	/**
	 * Get database schema.
	 *
	 * @return string
	 */
	protected static function get_schema() {
		global $wpdb;

		$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

		$quiz_auto_increment     = self::get_current_auto_increment_value( $wpdb->prefix . 'mlw_quizzes' );
		$question_auto_increment = self::get_current_auto_increment_value( $wpdb->prefix . 'mlw_questions' );
		$result_auto_increment   = self::get_current_auto_increment_value( $wpdb->prefix . 'mlw_results' );

		$tables = "
CREATE TABLE `{$wpdb->prefix}qsm_quizzes` (
  `quiz_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `system` tinyint(4) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL,
  `taken` int(11) NOT NULL,
  `author_id` bigint(20) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`quiz_id`)
) ENGINE=InnoDB AUTO_INCREMENT=$quiz_auto_increment $collate;
CREATE TABLE `{$wpdb->prefix}qsm_meta` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `object_id_type` (`object_id`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB $collate;
CREATE TABLE `{$wpdb->prefix}qsm_questions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `name` text NOT NULL,
  `description` longtext,
  `type` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `deleted_from_bank` int(11) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `order` (`order`)
) ENGINE=InnoDB AUTO_INCREMENT=$question_auto_increment $collate;
CREATE TABLE `{$wpdb->prefix}qsm_terms` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `parent_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `term_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `taxonomy` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `term_id` (`term_id`),
  KEY `object_id_type` (`object_id`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB $collate;
CREATE TABLE `{$wpdb->prefix}qsm_answers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `answer` longtext,
  `point_score` float NOT NULL DEFAULT '0',
  `correct` tinyint(4) NOT NULL DEFAULT '0',
  `meta` longtext,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `correct` (`correct`)
) ENGINE=InnoDB $collate;
CREATE TABLE `{$wpdb->prefix}qsm_results` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `user` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `user_email` varchar(255) DEFAULT NULL,
  `user_ip` varchar(255) DEFAULT NULL,
  `point_score` float NOT NULL DEFAULT '0',
  `correct_score` float NOT NULL DEFAULT '0',
  `total_questions` int(11) NOT NULL,
  `time_taken` int(11) NOT NULL,
  `unique_id` varchar(255) DEFAULT NULL,
  `autosaved` int(11) NOT NULL DEFAULT '0',
  `deleted` int(11) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `user` (`user`),
  KEY `time_taken` (`time_taken`)
) ENGINE=InnoDB AUTO_INCREMENT=$result_auto_increment $collate;
CREATE TABLE `{$wpdb->prefix}qsm_result_meta` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `result_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`)
) ENGINE=InnoDB $collate;
		";

		return $tables;
	}

	/**
	 * Create database tables.
	 */
	public static function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}

	/**
	 * Drop QSM tables.
	 *
	 * @return void
	 */
	public static function drop_tables() {
		global $wpdb;

		$tables = self::get_tables();

		foreach ( $tables as $table ) {
			/**
			 * Fires before dropping database table.
			 */
			do_action('qsm_before_drop_db_table', $table);
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}
	
	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  8.0
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}
	
	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 8.0
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			self::update();
		} else {
			self::update_db_version();
		}
	}
	
	/**
	 * Is a DB update needed?
	 *
	 * @since  8.0
	 * @return boolean
	 */
	public static function needs_db_update() {
		$current_db_version = get_option( 'qsm_db_version', null );
		$updates            = self::get_db_update_callbacks();
		$update_versions    = array_keys( $updates );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}
	
	/**
	 * Update WC version to current.
	 */
	public static function update_qsm_version() {
		update_option( 'qsm_version', QSM()->version );
	}
	
	/**
	 * Update DB version to current.
	 *
	 * @param string|null $version New QSM DB version or null.
	 */
	public static function update_db_version( $version = null ) {
		update_option( 'qsm_db_version', is_null( $version ) ? QSM()->version : $version );
	}

	/**
	 * Updates the plugin
	 *
	 * @since 4.7.1
	 */
	public static function update() {
		global $wpdb, $mlwQuizMasterNext;

		/**
		 * Include Updater file
		 */
		include_once dirname( __FILE__ ) . '/qsm-update-functions.php';

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( get_option( 'qsm_db_version' ), $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					if ( function_exists( $update_callback ) ) {
						$update_callback();
					}
				}
			}
		}
		
		self::update_db_version();
	}

	public static function get_current_auto_increment_value( $table_name = '' ) {
		global $wpdb;
		$auto_increment = 1;
		if ( ! empty( $table_name ) ) {
			if ( $table_name == $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) ) {
				$auto_increment = $wpdb->get_var( "SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$table_name}';" );
			}
		}
		return $auto_increment;
	}

}

$qsm_install = new QSM_Install();
