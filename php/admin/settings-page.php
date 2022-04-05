<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates The Settings Page For The Plugin
 *
 * @since 4.1.0
 */
class QMNGlobalSettingsPage {

	/**
	 * Main Construct Function
	 *
	 * Call functions within class
	 *
	 * @since 4.1.0
	 * @uses QMNGlobalSettingsPage::load_dependencies() Loads required filed
	 * @uses QMNGlobalSettingsPage::add_hooks() Adds actions to hooks and filters
	 * @return void
	 */
	function __construct() {
		$this->add_hooks();
		global $globalQuizsetting;
	}

	/**
	 * Add Hooks
	 *
	 * Adds functions to relavent hooks and filters
	 *
	 * @since 4.1.0
	 * @return void
	 */
	private function add_hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'quiz_default_global_option_init' ) );
		add_filter(
			'pre_update_option_qmn-settings',
			function( $new_value ) {
				$new_value['cpt_slug'] = isset( $new_value['cpt_slug'] ) ? sanitize_title( $new_value['cpt_slug'] ) : '';
				return $new_value;
			},
			10,
			2
		);
		add_action( 'admin_enqueue_scripts', array( $this, 'qsm_admin_enqueue_scripts_settings_page' ), 20 );
	}

	/**
	 * Loads admin scripts and style
	 *
	 * @since 7.3.5
	 */
	public function qsm_admin_enqueue_scripts_settings_page( $hook ) {
		if ( 'qsm_page_qmn_global_settings' !== $hook ) {
			return;
		}
		global $mlwQuizMasterNext;
		wp_enqueue_script( 'qmn_datetime_js', QSM_PLUGIN_JS_URL . '/jquery.datetimepicker.full.min.js', array(), $mlwQuizMasterNext->version, false );
		wp_enqueue_style( 'qsm_datetime_style', QSM_PLUGIN_CSS_URL . '/jquery.datetimepicker.css', array(), $mlwQuizMasterNext->version );
	}

	/**
	 * Prepares Settings Fields And Sections
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function init() {
		register_setting( 'qmn-settings-group', 'qmn-settings' );
		add_settings_section( 'qmn-global-section', __( 'Main Settings', 'quiz-master-next' ), array( $this, 'global_section' ), 'qmn_global_settings' );
		add_settings_field( 'usage-tracker', __( 'Allow Usage Tracking?', 'quiz-master-next' ), array( $this, 'usage_tracker_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'ip-collection', __( 'Disable collecting and storing IP addresses?', 'quiz-master-next' ), array( $this, 'ip_collection_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-search', __( 'Disable Quiz Posts From Being Searched?', 'quiz-master-next' ), array( $this, 'cpt_search_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-archive', __( 'Disable Quiz Archive?', 'quiz-master-next' ), array( $this, 'cpt_archive_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'detele-qsm-data', __( 'Delete all the data related to QSM on deletion?', 'quiz-master-next' ), array( $this, 'qsm_delete_data' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'background-quiz-email-process', __( 'Process emails in background?', 'quiz-master-next' ), array( $this, 'qsm_background_quiz_email_process' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-slug', __( 'Quiz Url Slug', 'quiz-master-next' ), array( $this, 'cpt_slug_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'plural-name', __( 'Post Type Plural Name (Shown in various places such as on archive pages)', 'quiz-master-next' ), array( $this, 'plural_name_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'facebook-app-id', __( 'Facebook App Id', 'quiz-master-next' ), array( $this, 'facebook_app_id' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'from-name', __( 'From Name (The name emails come from)', 'quiz-master-next' ), array( $this, 'from_name' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'from-email', __( 'From Email (The email address that emails come from)', 'quiz-master-next' ), array( $this, 'from_email' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'items-per-page-question-bank', __( 'Items per page in question bank pagination', 'quiz-master-next' ), array( $this, 'items_per_page_question_bank' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'new-template-result-detail', __( 'New Template For Admin Results Details', 'quiz-master-next' ), array( $this, 'new_template_results_details' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'results-details', __( 'Template For Admin Results Details', 'quiz-master-next' ), array( $this, 'results_details_template' ), 'qmn_global_settings', 'qmn-global-section' );
	}

	/**
	 * Default settings value
	 *
	 * @since 7.3.10
	 * @return array
	 */
	public static function default_settings() {
		return array(
			'form_type'                          => 0,
			'system'                             => 3,
			'score_roundoff'                     => 0,
			'progress_bar'                       => 0,
			'require_log_in'                     => 0,
			'pagination'                         => 0,
			'timer_limit'                        => 0,
			'enable_result_after_timer_end'      => 0,
			'skip_validation_time_expire'        => 0,
			'total_user_tries'                   => 0,
			'limit_total_entries'                => 0,
			'question_from_total'                => 0,
			'question_per_category'              => 0,
			'contact_info_location'              => 0,
			'loggedin_user_contact'              => 0,
			'comment_section'                    => 0,
			'question_numbering'                 => 0,
			'store_responses'                    => 1,
			'disable_answer_onselect'            => 0,
			'ajax_show_correct'                  => 0,
			'contact_disable_autofill'           => 0,
			'form_disable_autofill'              => 0,
			'show_category_on_front'             => 0,
			'enable_quick_result_mc'             => 0,
			'end_quiz_if_wrong'                  => 0,
			'enable_quick_correct_answer_info'   => 0,
			'enable_retake_quiz_button'          => 1,
			'enable_pagination_quiz'             => 0,
			'enable_deselect_option'             => 0,
			'disable_description_on_result'      => 0,
			'disable_scroll_next_previous_click' => 0,
			'disable_first_page'                 => 0,
			'quiz_animation'                     => '',
			'result_page_fb_image'               => QSM_PLUGIN_URL . 'assets/icon-200x200.png',
			'randomness_order'                   => 0,
			'scheduled_time_start'               => '',
			'scheduled_time_end'                 => '',
			'not_allow_after_expired_time'       => 0,
			'preferred_date_format'              => 'F j, Y',
			'default_answers'                    => 1,
		);
	}

	/**
	 * Prepares Settings Fields of global quiz default option
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function quiz_default_global_option_init() {
		register_setting( 'qsm-quiz-settings-group', 'qsm-quiz-settings' );
		add_settings_section( 'qmn-global-section', __( 'Quiz Settings', 'quiz-master-next' ), array( $this, 'global_section' ), 'qsm_default_global_option' );
		add_settings_field( 'quiz-type', __( 'Quiz Type', 'quiz-master-next' ), array( $this, 'qsm_global_quiz_type' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'grading-system', __( 'Grading System', 'quiz-master-next' ), array( $this, 'qsm_global_grading_system' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'default_answers', __( 'Default Number of Answers', 'quiz-master-next' ), array( $this, 'default_answers' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'allow-score-round-off', __( 'Allow Score Round-off', 'quiz-master-next' ), array( $this, 'qsm_global_score_roundoff' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'show-progress-bar', __( 'Show progress bar', 'quiz-master-next' ), array( $this, 'qsm_global_show_progress_bar' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'require-user-login', __( 'Require User Login', 'quiz-master-next' ), array( $this, 'qsm_global_require_user_login' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'questions-per-page', __( 'Questions Per Page', 'quiz-master-next' ), array( $this, 'qsm_global_questions_per_page' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'time-limit-in-minutes', __( 'Time Limit (in minutes)', 'quiz-master-next' ), array( $this, 'qsm_global_time_limit_in_minutes' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'force-submit-after-timer-expiry', __( 'Force submit after timer expiry', 'quiz-master-next' ), array( $this, 'qsm_global_force_submit_after_timer_expiry' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'skip-validations-when-time-expire', __( 'Skip validations when time expire', 'quiz-master-next' ), array( $this, 'qsm_global_skip_validations_when_time_expire' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'limit-attempts', __( 'Limit Attempts', 'quiz-master-next' ), array( $this, 'qsm_global_limit_attempts' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'limit-entries', __( 'Limit Entries', 'quiz-master-next' ), array( $this, 'qsm_global_limit_entries' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'limit-number-of-questions', __( 'Limit number of Questions', 'quiz-master-next' ), array( $this, 'qsm_global_limit_number_of_questions' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'limit-number-of-questions-per-category', __( 'Limit number of Questions Per Category', 'quiz-master-next' ), array( $this, 'qsm_global_limit_number_of_questions_per_category' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'quiz-dates', __( 'Quiz Dates', 'quiz-master-next' ), array( $this, 'qsm_global_quiz_dates' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'can-submit-after-end-date', __( 'Do not allow quiz submission after the end date/time', 'quiz-master-next' ), array( $this, 'qsm_global_do_not_allow_quiz_submission_after_the_end_datetime' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'random-questions', __( 'Random Questions', 'quiz-master-next' ), array( $this, 'qsm_global_random_questions' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'contact-form-position', __( 'Contact form position', 'quiz-master-next' ), array( $this, 'qsm_global_contact_form_position' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'show-contact-form-to-logged-in-users', __( 'Show contact form to logged in users', 'quiz-master-next' ), array( $this, 'qsm_global_show_contact_form_to_logged_in_users' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'enable-comments', __( 'Enable comments', 'quiz-master-next' ), array( $this, 'qsm_global_enable_comments' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'show-question-numbers', __( 'Show question numbers', 'quiz-master-next' ), array( $this, 'qsm_global_show_question_numbers' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'save-responses', __( 'Save Responses', 'quiz-master-next' ), array( $this, 'qsm_global_save_responses' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'disable-change-of-answers', __( 'Disable change of answers', 'quiz-master-next' ), array( $this, 'qsm_global_disable_change_of_answers' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'add-class-for-correct-incorrect-answers', __( 'Add class for correct/incorrect answers', 'quiz-master-next' ), array( $this, 'qsm_global_add_class_for_correct_incorrect_answers' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'disable-auto-fill-for-contact-input', __( 'Disable auto fill for contact input', 'quiz-master-next' ), array( $this, 'qsm_global_disable_auto_fill_for_contact_input' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'disable-auto-fill-for-quiz-input', __( 'Disable auto fill for Quiz input', 'quiz-master-next' ), array( $this, 'qsm_global_disable_auto_fill_for_quiz_input' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'display-category-name-on-front-end', __( 'Display category name on front end', 'quiz-master-next' ), array( $this, 'qsm_global_display_category_name_on_front_end' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'show-results-inline', __( 'Show results inline', 'quiz-master-next' ), array( $this, 'qsm_global_show_results_inline' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'end-quiz-if-there-is-wrong-answer', __( 'End quiz if there is wrong answer', 'quiz-master-next' ), array( $this, 'qsm_global_end_quiz_if_there_is_wrong_answer' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'show-correct-answer-inline', __( 'Show correct answer inline', 'quiz-master-next' ), array( $this, 'qsm_global_show_correct_answer_inline' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'retake-quiz', __( 'Retake Quiz', 'quiz-master-next' ), array( $this, 'qsm_global_retake_quiz' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'show-current-page-number', __( 'Show current page number', 'quiz-master-next' ), array( $this, 'qsm_global_show_current_page_number' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'deselect-answer', __( 'Deselect Answer', 'quiz-master-next' ), array( $this, 'qsm_global_deselect_answer' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'disable-description-on-quiz-result-page', __( 'Disable description on quiz result page?', 'quiz-master-next' ), array( $this, 'qsm_global_disable_description_on_quiz_result_page' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'disable-scroll-on-next-and-previous-button-click', __( 'Disable scroll on next and previous button click?', 'quiz-master-next' ), array( $this, 'qsm_global_disable_scroll_on_next_and_previous_button_click' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'disable-first-page', __( 'Disable first page on quiz', 'quiz-master-next' ), array( $this, 'qsm_global_disable_first_page' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'quiz-animation', __( 'Quiz Animation', 'quiz-master-next' ), array( $this, 'qsm_global_quiz_animation' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'logo-url', __( 'Logo URL', 'quiz-master-next' ), array( $this, 'qsm_global_logo_url' ), 'qsm_default_global_option', 'qmn-global-section' );
		add_settings_field( 'preferred_date_format', __( 'Preferred Date Format', 'quiz-master-next' ), array( $this, 'preferred_date_format' ), 'qsm_default_global_option', 'qmn-global-section' );
		global $globalQuizsetting;
		$get_default_value = self::default_settings();
		$get_saved_value   = get_option( 'qsm-quiz-settings' );
		$globalQuizsetting = wp_parse_args( $get_saved_value, $get_default_value );
	}

	public static function get_global_quiz_settings() {
		$get_default_value = self::default_settings();
		$get_saved_value   = get_option( 'qsm-quiz-settings' );
		return wp_parse_args( $get_saved_value, $get_default_value );
	}

	/**
	 * Generates Section Text
	 *
	 * Generates the section text. If page has been saved, flush rewrite rules for updated post type slug
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function global_section() {
		esc_html_e( 'These settings are applied to the entire plugin and all quizzes.', 'quiz-master-next' );
		if ( isset( $_GET['settings-updated'] ) ) {
			flush_rewrite_rules( true );
			echo '<div class="updated" style="padding: 10px;">';
				echo '<span>' . esc_html__( ' Settings have been updated!', 'quiz-master-next' ) . '</span>';
			echo '</div>';
		}
		$enabled = get_option( 'qsm_multiple_category_enabled' );
		if ( 'cancelled' === $enabled ) {
			?>
<div class="notice notice-info multiple-category-notice">
	<h3>
			<?php esc_html_e( 'Database update required', 'quiz-master-next' ); ?>
	</h3>
	<p>
			<?php esc_html_e( 'QSM has been updated!', 'quiz-master-next' ); ?><br/>
			<?php esc_html_e( 'We need to upgrade your database so that you can enjoy the latest features.', 'quiz-master-next' ); ?><br/>
			<?php
			/* translators: %s: HTML tag */
			echo sprintf( esc_html__( 'Please note that this action %1$s can not be %2$s rolled back. We recommend you to take a backup of your current site before proceeding.', 'quiz-master-next' ), '<b>', '</b>' );
			?>
	</p>
	<p class="category-action">
		<a href="javascript:void(0)" class="button button-primary enable-multiple-category"><?php esc_html_e( 'Update Database', 'quiz-master-next' ); ?></a>
	</p>
</div>
			<?php
		}
	}

	/**
	 * Generates Setting Field For From Email
	 *
	 * @since 6.2.0
	 * @return void
	 */
	public function from_email() {
		$settings   = (array) get_option( 'qmn-settings' );
		$from_email = get_option( 'admin_email', '' );
		if ( isset( $settings['from_email'] ) ) {
			$from_email = $settings['from_email'];
		}
		?>
<input type='email' name='qmn-settings[from_email]' id='qmn-settings[from_email]'
	value='<?php echo esc_attr( $from_email ); ?>' />
		<?php
	}

	/**
	 * Generates Setting Field For items per page in question bank pagination
	 *
	 * @since 7.0.1
	 * @return void
	 */
	public function items_per_page_question_bank() {
		$settings                     = (array) get_option( 'qmn-settings' );
		$items_per_page_question_bank = 20;
		if ( isset( $settings['items_per_page_question_bank'] ) ) {
					$items_per_page_question_bank = $settings['items_per_page_question_bank'];
		}
		?>
<input type='number' name='qmn-settings[items_per_page_question_bank]' id='qmn-settings[items_per_page_question_bank]'
	value='<?php echo esc_attr( $items_per_page_question_bank ); ?>' />
		<?php
	}

	/**
	 * Generates Setting Field For From Name
	 *
	 * @since 6.2.0
	 * @return void
	 */
	public function from_name() {
		$settings  = (array) get_option( 'qmn-settings' );
		$from_name = get_bloginfo( 'name' );
		if ( isset( $settings['from_name'] ) ) {
			$from_name = $settings['from_name'];
		}
		?>
<input type='text' name='qmn-settings[from_name]' id='qmn-settings[from_name]'
	value='<?php echo esc_attr( $from_name ); ?>' />
		<?php
	}

	/**
	 * Generates Setting Field For App Id
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function facebook_app_id() {
		$settings        = (array) get_option( 'qmn-settings' );
		$facebook_app_id = '594986844960937';
		if ( isset( $settings['facebook_app_id'] ) ) {
			$facebook_app_id = esc_attr( $settings['facebook_app_id'] );
		}
		echo '<input type="text" name="qmn-settings[facebook_app_id]" id="qmn-settings[facebook_app_id]" value="' . esc_attr( $facebook_app_id ) . '" />';
	}

	/**
	 * Generates Setting Field For Post Slug
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function cpt_slug_field() {
		$settings = (array) get_option( 'qmn-settings' );
		$cpt_slug = 'quiz';
		if ( isset( $settings['cpt_slug'] ) ) {
			$cpt_slug = esc_attr( $settings['cpt_slug'] );
		}
		echo '<input type="text" name="qmn-settings[cpt_slug]" id="qmn-settings[cpt_slug]" value="' . esc_attr( $cpt_slug ) . '" />';
	}

	/**
	 * Generates Setting Field For Plural name
	 *
	 * @since 5.3.0
	 * @return void
	 */
	public function plural_name_field() {
		$settings    = (array) get_option( 'qmn-settings' );
		$plural_name = __( 'Quizzes & Surveys', 'quiz-master-next' );
		if ( isset( $settings['plural_name'] ) ) {
			$plural_name = esc_attr( $settings['plural_name'] );
		}
		echo '<input type="text" name="qmn-settings[plural_name]" id="qmn-settings[plural_name]" value="' . esc_attr( $plural_name ) . '" />';
	}

	/**
	 * Generates Setting Field For Exclude Search
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function cpt_search_field() {
		$settings   = (array) get_option( 'qmn-settings' );
		$cpt_search = '0';
		if ( isset( $settings['cpt_search'] ) ) {
			$cpt_search = esc_attr( $settings['cpt_search'] );
		}
		$checked = '';
		if ( '1' == $cpt_search ) {
			$checked = " checked='checked'";
		}

		echo '<label class="switch">';
			echo '<input type="checkbox" name="qmn-settings[cpt_search]" id="qmn-settings[cpt_search]" value="1"' . esc_attr( $checked ) . ' />';
		echo '<span class="slider round"></span></label>';
	}

	/**
	 * Generates Setting Field For Post Archive
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function cpt_archive_field() {
		$settings    = (array) get_option( 'qmn-settings' );
		$cpt_archive = '0';
		if ( isset( $settings['cpt_archive'] ) ) {
			$cpt_archive = esc_attr( $settings['cpt_archive'] );
		}
		$checked = '';
		if ( '1' == $cpt_archive ) {
			$checked = " checked='checked'";
		}

		echo '<label class="switch">';
			echo '<input type="checkbox" name="qmn-settings[cpt_archive]" id="qmn-settings[cpt_archive]" value="1"' . esc_attr( $checked ) . '/>';
		echo '<span class="slider round"></span></label>';
	}

	/**
	 * Generates Setting Field For delete QSM data
	 *
	 * @since 7.0.3
	 * @return void
	 */
	public function qsm_delete_data() {
		$settings    = (array) get_option( 'qmn-settings' );
		$cpt_archive = '0';
		if ( isset( $settings['delete_qsm_data'] ) ) {
			$cpt_archive = esc_attr( $settings['delete_qsm_data'] );
		}
		$checked = '';
		if ( '1' == $cpt_archive ) {
			$checked = " checked='checked'";
		}

		echo '<label class="switch">';
			echo '<input type="checkbox" name="qmn-settings[delete_qsm_data]" id="qmn-settings[delete_qsm_data]" value="1"' . esc_attr( $checked ) . '/>';
		echo '<span class="slider round"></span></label>';
	}

	/**
	 * Generates Setting Field For background email process
	 *
	 * @since 7.0.3
	 * @return void
	 */
	public function qsm_background_quiz_email_process() {
		$settings                              = (array) get_option( 'qmn-settings' );
				$background_quiz_email_process = '1';
		if ( isset( $settings['background_quiz_email_process'] ) ) {
			$background_quiz_email_process = esc_attr( $settings['background_quiz_email_process'] );
		}

		echo '<label style="margin-bottom: 10px;display: inline-block;">';
			echo "<input type='radio' name='qmn-settings[background_quiz_email_process]' class='background_quiz_email_process' value='1' " . checked( $background_quiz_email_process, '1', false ) . '/>';
				esc_html_e( 'Yes', 'quiz-master-next' );
				echo '</label>';
				echo '<br/>';
				echo '<label>';
			echo "<input type='radio' name='qmn-settings[background_quiz_email_process]' class='background_quiz_email_process' value='0' " . checked( $background_quiz_email_process, '0', false ) . '/>';
				esc_html_e( 'No', 'quiz-master-next' );
		echo '</label>';
	}

	/**
	 * Generates Setting Field For Results Details Template
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function results_details_template() {
		$settings = (array) get_option( 'qmn-settings' );
		if ( isset( $settings['results_details_template'] ) ) {
			$template = htmlspecialchars_decode( $settings['results_details_template'], ENT_QUOTES );
		} else {
			$template = '<h2>Quiz Results for %QUIZ_NAME%</h2>
			<p>%CONTACT_ALL%</p>
			<p>Name Provided: %USER_NAME%</p>
			<p>Business Provided: %USER_BUSINESS%</p>
			<p>Phone Provided: %USER_PHONE%</p>
			<p>Email Provided: %USER_EMAIL%</p>
			<p>Score Received: %AMOUNT_CORRECT%/%TOTAL_QUESTIONS% or %CORRECT_SCORE%% or %POINT_SCORE% points</p>
			<h2>Answers Provided:</h2>
			<p>The user took %TIMER% to complete quiz.</p>
			<p>Comments entered were: %COMMENT_SECTION%</p>
			<p>The answers were as follows:</p>
			%QUESTIONS_ANSWERS%';
		}
		wp_editor( $template, 'results_template', array( 'textarea_name' => 'qmn-settings[results_details_template]' ) );
	}

	/**
	 * Generates Setting Field For Usage Tracker Authorization
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function usage_tracker_field() {
		$settings         = (array) get_option( 'qmn-settings' );
		$tracking_allowed = '0';
		if ( isset( $settings['tracking_allowed'] ) ) {
			$tracking_allowed = esc_attr( $settings['tracking_allowed'] );
		}
		$checked = '';
		if ( '2' == $tracking_allowed ) {
			$checked = " checked='checked'";
		}

		echo '<label class="switch">';
			echo '<input type="checkbox" name="qmn-settings[tracking_allowed]" id="qmn-settings[tracking_allowed]" value="2"' . esc_attr( $checked ) . '/><span class="slider round"></span>';
		echo '</label>';
		echo "<span class='global-sub-text' for='qmn-settings[tracking_allowed]'>" . esc_html__( "Allow Quiz And Survey Master to anonymously track this plugin's usage and help us make this plugin better.", 'quiz-master-next' ) . '</span>';
	}

	/**
	 * Generates Setting Field For IP Collection
	 *
	 * @since 5.3.0
	 * @return void
	 */
	public function ip_collection_field() {
		$settings      = (array) get_option( 'qmn-settings' );
		$ip_collection = '0';
		if ( isset( $settings['ip_collection'] ) ) {
			$ip_collection = esc_attr( $settings['ip_collection'] );
		}
		$checked = '';
		if ( '1' == $ip_collection ) {
			$checked = " checked='checked'";
		}
		echo '<label class="switch">';
		echo '<input type="checkbox" name="qmn-settings[ip_collection]" id="qmn-settings[ip_collection]" value="1"' . esc_attr( $checked ) . '/>';
		echo '<span class="slider round"></span></label>';
		echo "<span class='global-sub-text' for='qmn-settings[ip_collection]'>" . esc_html__( 'You must not restrict number of quiz attempts when this option is enabled.', 'quiz-master-next' ) . '</span>';
	}

	/**
	 * Generates Settings Page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public static function display_page() {
		global $mlwQuizMasterNext;
		$active_tab = 'qmn_global_settings';
		if ( isset( $_GET['tab'] ) ) {
			if ( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) == 'qmn_global_settings' ) {
				$active_tab = 'qmn_global_settings';
			} else {
				$active_tab = 'quiz-default-qptions';
			}
		}
		$g_class = $d_class = '';
		if ( 'qmn_global_settings' === $active_tab ) {
			$g_class = 'nav-tab-active';
		}
		if ( 'quiz-default-qptions' === $active_tab ) {
			$d_class = 'nav-tab-active';
		}
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Global Settings', 'quiz-master-next' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<!-- when tab buttons are clicked we jump back to the same page but with a new parameter that represents the clicked tab. accordingly we make it active -->
				<a href="?page=qmn_global_settings&tab=qmn_global_settings" class="nav-tab <?php echo esc_attr( $g_class ); ?> "><?php esc_html_e( 'Main Settings', 'quiz-master-next' ); ?></a>
				<a href="?page=qmn_global_settings&tab=quiz-default-qptions" class="nav-tab <?php echo esc_attr( $d_class ); ?>"><?php esc_html_e( 'Quiz Default Options', 'quiz-master-next' ); ?></a>
			</h2>
			<form action="options.php" method="POST" class="qsm_global_settings">
				<?php
				if ( 'qmn_global_settings' === $active_tab ) {
					settings_fields( 'qmn-settings-group' );
					do_settings_sections( 'qmn_global_settings' );
				}
				?>
				<?php
				if ( 'quiz-default-qptions' === $active_tab ) {
					settings_fields( 'qsm-quiz-settings-group' );
					do_settings_sections( 'qsm_default_global_option' );
				}
				?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Generates Setting Field For new template for result detail
	 *
	 * @since 7.0.0
	 * @return void
	 */
	public function new_template_results_details() {
		$settings                   = (array) get_option( 'qmn-settings' );
		$new_template_result_detail = '1';
		if ( isset( $settings['new_template_result_detail'] ) ) {
			$new_template_result_detail = esc_attr( $settings['new_template_result_detail'] );
		}
		echo '<label style="margin-bottom: 10px;display: inline-block;">';
			echo "<input type='radio' name='qmn-settings[new_template_result_detail]' class='new_template_result_detail' value='1' " . checked( $new_template_result_detail, '1', false ) . '/>';
				esc_html_e( 'New Template', 'quiz-master-next' );
		echo '</label>';
		echo '<br/>';
		echo '<label>';
			echo "<input type='radio' name='qmn-settings[new_template_result_detail]' class='new_template_result_detail' value='0' " . checked( $new_template_result_detail, '0', false ) . '/>';
				esc_html_e( 'Old Template', 'quiz-master-next' );
		echo '</label>';
	}

	/**
	 * Generates Quiz Global  Field For Quiz Type
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_quiz_type() {
		global $globalQuizsetting;
		$qsm_form_type = ( isset( $globalQuizsetting['form_type'] ) && '' !== $globalQuizsetting['form_type'] ? $globalQuizsetting['form_type'] : '' );
		echo '<div class="global_form_type_settiong"><select name ="qsm-quiz-settings[form_type]">
			<option value="0" ' . ( 0 === intval( $qsm_form_type ) ? 'Selected' : '' ) . '>Quiz</option>
			<option value="1" ' . ( 1 === intval( $qsm_form_type ) ? 'Selected' : '' ) . ' >Survey</option>
			<option value="2" ' . ( 2 === intval( $qsm_form_type ) ? 'Selected' : '' ) . '>Simple Form</option>
		</select></div>';
	}

	/**
	 * Generates Quiz Global  Field For grading system
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_grading_system() {
		global $globalQuizsetting;
		$qsm_system = ( isset( $globalQuizsetting['system'] ) && '' !== $globalQuizsetting['system'] ? $globalQuizsetting['system'] : '' );
		echo '<fieldset class="buttonset buttonset-hide global_setting_system" >
					<input type="radio" id="system-0" name="qsm-quiz-settings[system]" value="0" ' . checked( $qsm_system, '0', false ) . '>
					<label for="system-0">Correct/Incorrect</label><br>
					<input type="radio" id="system-1" name="qsm-quiz-settings[system]" value="1" ' . checked( $qsm_system, '1', false ) . '>
					<label for="system-1">Points</label><br>
					<input type="radio" id="system-3" name="qsm-quiz-settings[system]"  value="3" ' . checked( $qsm_system, '3', false ) . '>
					<label for="system-3">Both</label><br>
				</fieldset>
				<span class="qsm-opt-desc">Select the system for grading the quiz.</span>';
	}

	/**
	 * Generates Quiz Global  Field For Allow Score Round-off
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_score_roundoff() {
		global $globalQuizsetting;
		$qsm_score_roundoff = ( isset( $globalQuizsetting['score_roundoff'] ) && '' !== $globalQuizsetting['score_roundoff'] ? $globalQuizsetting['score_roundoff'] : '' );
		echo '<fieldset class="buttonset buttonset-hide global_setting_score_roundoff" >
				<input type="checkbox" id="score_roundoff-1" name="qsm-quiz-settings[score_roundoff]" value="1" ' . checked( $qsm_score_roundoff, '1', false ) . '>
			  </fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Show progress bar
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_show_progress_bar() {
		global $globalQuizsetting;
		$qsm_progress_bar = ( isset( $globalQuizsetting['progress_bar'] ) && '' !== $globalQuizsetting['progress_bar'] ? $globalQuizsetting['progress_bar'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide qsm_tab_content" >
				<input type="radio" id="progress_bar-1" name="qsm-quiz-settings[progress_bar]" value="1"  ' . checked( $qsm_progress_bar, '1', false ) . ' >
				<label for="progress_bar-1">Yes</label><br>
				<input type="radio" id="progress_bar-0" name="qsm-quiz-settings[progress_bar]"  value="0"  ' . checked( $qsm_progress_bar, '0', false ) . '>
				<label for="progress_bar-0">No</label><br>
			 </fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Require User Login
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_require_user_login() {
		global $globalQuizsetting;

		$qsm_require_log_in = ( isset( $globalQuizsetting['require_log_in'] ) && '' !== $globalQuizsetting['require_log_in'] ? $globalQuizsetting['require_log_in'] : '0' );
			echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="require_log_in-1" name="qsm-quiz-settings[require_log_in]" value="1" ' . checked( $qsm_require_log_in, '1', false ) . '>
					<label for="require_log_in-1">Yes</label><br>
					<input type="radio" id="require_log_in-0" name="qsm-quiz-settings[require_log_in]"  value="0" ' . checked( $qsm_require_log_in, '0', false ) . '>
					<label for="require_log_in-0">No</label><br>
				  </fieldset>
				  <span class="qsm-opt-desc">Enabling this allows only logged in users to take the quiz</span>';
	}

	/**
	 * Generates Quiz Global  Field For Questions Per Page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_questions_per_page() {
		global $globalQuizsetting;
		$qsm_pagination = isset( $globalQuizsetting['pagination'] ) && '' !== $globalQuizsetting['pagination'] ? $globalQuizsetting['pagination'] : '0';
		echo '<input type="number" step="1" min="0" id="pagination" name="qsm-quiz-settings[pagination]" value="' . esc_attr( $qsm_pagination ) . '">
			  <span class="qsm-opt-desc">Override the default pagination created on questions tab</span>';
	}

	/**
	 * Generates Quiz Global  Field For Time Limit (in minutes)
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_time_limit_in_minutes() {
		global $globalQuizsetting;
		$qsm_timer_limit = ( isset( $globalQuizsetting['timer_limit'] ) && '' !== $globalQuizsetting['timer_limit'] ? $globalQuizsetting['timer_limit'] : '0' );
		echo '<input type="number" step="1" min="0" id="timer_limit" name="qsm-quiz-settings[timer_limit]" value="' . esc_attr( $qsm_timer_limit ) . '">
			  <span class="qsm-opt-desc">Leave 0 for no time limit</span>';
	}

	/**
	 * Generates Quiz Global  Field For Force submit after timer expiry
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_force_submit_after_timer_expiry() {
		global $globalQuizsetting;
		$qsm_enable_result_after_timer_end = ( isset( $globalQuizsetting['enable_result_after_timer_end'] ) && '' !== $globalQuizsetting['enable_result_after_timer_end'] ? $globalQuizsetting['enable_result_after_timer_end'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="enable_result_after_timer_end-1" name="qsm-quiz-settings[enable_result_after_timer_end]" value="1" ' . checked( $qsm_enable_result_after_timer_end, '1', false ) . '>
				<label for="enable_result_after_timer_end-1">Yes</label><br>
				<input type="radio" id="enable_result_after_timer_end-0" name="qsm-quiz-settings[enable_result_after_timer_end]"  value="0" ' . checked( $qsm_enable_result_after_timer_end, '0', false ) . '>
				<label for="enable_result_after_timer_end-0">No</label><br>
			  </fieldset>';
	}
	/**
	 * Generates Quiz Global  Field For Skip validations when time expire
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_skip_validations_when_time_expire() {
		global $globalQuizsetting;
		$qsm_skip_validation_time_expire = ( isset( $globalQuizsetting['skip_validation_time_expire'] ) && '' !== $globalQuizsetting['skip_validation_time_expire'] ? $globalQuizsetting['skip_validation_time_expire'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				 <input type="radio" id="skip_validation_time_expire-1" name="qsm-quiz-settings[skip_validation_time_expire]"  value="1" ' . checked( $qsm_skip_validation_time_expire, '1', false ) . '>
				 <label for="skip_validation_time_expire-1">Yes</label><br>
				 <input type="radio" id="skip_validation_time_expire-0" name="qsm-quiz-settings[skip_validation_time_expire]" value="0" ' . checked( $qsm_skip_validation_time_expire, '0', false ) . '>
				 <label for="skip_validation_time_expire-0">No</label><br>
			 </fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Limit Attempts
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_limit_attempts() {
		global $globalQuizsetting;
		$qsm_total_user_tries = ( isset( $globalQuizsetting['total_user_tries'] ) && '' !== $globalQuizsetting['total_user_tries'] ? $globalQuizsetting['total_user_tries'] : '0' );
		echo '<input type="number" step="1" min="0" id="total_user_tries" name="qsm-quiz-settings[total_user_tries]" value="' . esc_attr( $qsm_total_user_tries ) . '">
			  <span class="qsm-opt-desc">Leave 0 for unlimited attempts</span>';
	}


	/**
	 * Generates Quiz Global  Field For Limit Entries
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_limit_entries() {
		global $globalQuizsetting;
		$qsm_limit_total_entries = ( isset( $globalQuizsetting['limit_total_entries'] ) && '' !== $globalQuizsetting['limit_total_entries'] ? $globalQuizsetting['limit_total_entries'] : '0' );
		echo '<input type="number" step="1" min="0" id="limit_total_entries" name="qsm-quiz-settings[limit_total_entries]" value="' . esc_attr( $qsm_limit_total_entries ) . '">
			  <span class="qsm-opt-desc">Leave 0 for unlimited entries</span>';

	}

	/**
	 * Generates Quiz Global  Field For Limit number of Questions
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_limit_number_of_questions() {
		global $globalQuizsetting;
		$qsm_question_from_total = ( isset( $globalQuizsetting['question_from_total'] ) && '' !== $globalQuizsetting['question_from_total'] ? $globalQuizsetting['question_from_total'] : '0' );
		echo '<input type="number" step="1" min="0" id="question_from_total" name="qsm-quiz-settings[question_from_total]" value="' . esc_attr( $qsm_question_from_total ) . '">
			  <span class="qsm-opt-desc">Leave 0 to load all questions</span>';
	}



	/**
	 * Generates Quiz Global  Field For Limit number of Questions Per Category
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_limit_number_of_questions_per_category() {
		global $globalQuizsetting;
		$qsm_question_per_category = ( isset( $globalQuizsetting['question_per_category'] ) && '' !== $globalQuizsetting['question_per_category'] ? $globalQuizsetting['question_per_category'] : '0' );
		echo '<input type="number" step="1" min="0" id="question_per_category" name="qsm-quiz-settings[question_per_category]" value="' . esc_attr( $qsm_question_per_category ) . '">
			  <span class="qsm-opt-desc">Leave 0 to load all questions. You also need to set Limit Number of questions, as well as select Question Categories</span>';
	}


	/**
	 * Generates Quiz Global  Field For Contact form position
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_contact_form_position() {
		global $globalQuizsetting;
		$qsm_contact_info_location = ( isset( $globalQuizsetting['contact_info_location'] ) && '' !== $globalQuizsetting['contact_info_location'] ? $globalQuizsetting['contact_info_location'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="contact_info_location-0" name="qsm-quiz-settings[contact_info_location]"  value="0"  ' . checked( $qsm_contact_info_location, '0', false ) . '>
				<label for="contact_info_location-0">Show before quiz begins</label><br>
				<input type="radio" id="contact_info_location-1" name="qsm-quiz-settings[contact_info_location]" value="1"  ' . checked( $qsm_contact_info_location, '1', false ) . '>
				<label for="contact_info_location-1">Show after the quiz ends</label><br>
			</fieldset>
			  <span class="qsm-opt-desc">Select when to display the contact form</span>';
	}

	/**
	 * Generates Quiz Global  Field For Show contact form to logged in users
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_show_contact_form_to_logged_in_users() {
		global $globalQuizsetting;
		$qsm_loggedin_user_contact = ( isset( $globalQuizsetting['loggedin_user_contact'] ) && '' !== $globalQuizsetting['loggedin_user_contact'] ? $globalQuizsetting['loggedin_user_contact'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="loggedin_user_contact-0" name="qsm-quiz-settings[loggedin_user_contact]" value="0" ' . checked( $qsm_loggedin_user_contact, '0', false ) . '>
				<label for="loggedin_user_contact-0">Yes</label><br>
				<input type="radio" id="loggedin_user_contact-1" name="qsm-quiz-settings[loggedin_user_contact]" value="1" ' . checked( $qsm_loggedin_user_contact, '1', false ) . '>
				<label for="loggedin_user_contact-1">No</label><br>
			  </fieldset>
			  <span class="qsm-opt-desc">Logged in users can edit their contact information</span>';
	}

	/**
	 * Generates Quiz Global  Field For Enable comments
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_enable_comments() {
		global $globalQuizsetting;
		$qsm_comment_section = ( isset( $globalQuizsetting['comment_section'] ) && '' !== $globalQuizsetting['comment_section'] ? $globalQuizsetting['comment_section'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="comment_section-0" name="qsm-quiz-settings[comment_section]" value="0"  ' . checked( $qsm_comment_section, '0', false ) . '>
					<label for="comment_section-0">Yes</label><br>
					<input type="radio" id="comment_section-1" name="qsm-quiz-settings[comment_section]"  value="1"  ' . checked( $qsm_comment_section, '1', false ) . '>
					<label for="comment_section-1">No</label><br>
				</fieldset>
				<span class="qsm-opt-desc">Allow users to enter their comments after the quiz</span>';
	}

	/**
	 * Generates Quiz Global  Field For Show question numbers
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_show_question_numbers() {
		global $globalQuizsetting;
		$qsm_question_numbering = ( isset( $globalQuizsetting['question_numbering'] ) && '' !== $globalQuizsetting['question_numbering'] ? $globalQuizsetting['question_numbering'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="question_numbering-1" name="qsm-quiz-settings[question_numbering]" value="1"  ' . checked( $qsm_question_numbering, '1', false ) . '>
					<label for="question_numbering-1">Yes</label><br>
					<input type="radio" id="question_numbering-0" name="qsm-quiz-settings[question_numbering]"  value="0"  ' . checked( $qsm_question_numbering, '0', false ) . '>
					<label for="question_numbering-0">No</label><br>
			 </fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Save Responses
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_save_responses() {
		global $globalQuizsetting;
		$qsm_store_responses = ( isset( $globalQuizsetting['store_responses'] ) && '' !== $globalQuizsetting['store_responses'] ? $globalQuizsetting['store_responses'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="store_responses-1" name="qsm-quiz-settings[store_responses]"  value="1" ' . checked( $qsm_store_responses, '1', false ) . '>
				<label for="store_responses-1">Yes</label><br>
				<input type="radio" id="store_responses-0" name="qsm-quiz-settings[store_responses]" value="0" ' . checked( $qsm_store_responses, '0', false ) . '>
				<label for="store_responses-0">No</label><br>
			</fieldset>
			<span class="qsm-opt-desc">The results will be permanently stored in a database</span>';
	}

	/**
	 * Generates Quiz Global  Field For Disable change of answers
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_disable_change_of_answers() {
		global $globalQuizsetting;
		$qsm_disable_answer_onselect = ( isset( $globalQuizsetting['disable_answer_onselect'] ) && '' !== $globalQuizsetting['disable_answer_onselect'] ? $globalQuizsetting['disable_answer_onselect'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="disable_answer_onselect-1" name="qsm-quiz-settings[disable_answer_onselect]" value="1" ' . checked( $qsm_disable_answer_onselect, '1', false ) . '>
					<label for="disable_answer_onselect-1">Yes</label><br>
					<input type="radio" id="disable_answer_onselect-0" name="qsm-quiz-settings[disable_answer_onselect]"  value="0" ' . checked( $qsm_disable_answer_onselect, '0', false ) . '>
					<label for="disable_answer_onselect-0">No</label><br>
			 </fieldset>
			 <span class="qsm-opt-desc">Works with multiple choice questions only</span>';
	}

	/**
	 * Generates Quiz Global  Field For Add class for correct/incorrect answers
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_add_class_for_correct_incorrect_answers() {
		global $globalQuizsetting;
		$qsm_ajax_show_correct = ( isset( $globalQuizsetting['ajax_show_correct'] ) && '' !== $globalQuizsetting['ajax_show_correct'] ? $globalQuizsetting['ajax_show_correct'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="ajax_show_correct-1" name="qsm-quiz-settings[ajax_show_correct]" value="1" ' . checked( $qsm_ajax_show_correct, '1', false ) . '>
				<label for="ajax_show_correct-1">Yes</label><br>
				<input type="radio" id="ajax_show_correct-0" name="qsm-quiz-settings[ajax_show_correct]"value="0" ' . checked( $qsm_ajax_show_correct, '0', false ) . '>
				<label for="ajax_show_correct-0">No</label><br>
			 </fieldset>
			<span class="qsm-opt-desc">Works with multiple choice questions only</span>';
	}

	/**
	 * Generates Quiz Global  Field For Disable auto fill for contact input
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_disable_auto_fill_for_contact_input() {
		global $globalQuizsetting;
		$qsm_contact_disable_autofill = ( isset( $globalQuizsetting['contact_disable_autofill'] ) && '' !== $globalQuizsetting['contact_disable_autofill'] ? $globalQuizsetting['contact_disable_autofill'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="contact_disable_autofill-1" name="qsm-quiz-settings[contact_disable_autofill]" value="1"  ' . checked( $qsm_contact_disable_autofill, '1', false ) . '>
					<label for="contact_disable_autofill-1">Yes</label><br>
					<input type="radio" id="contact_disable_autofill-0" name="qsm-quiz-settings[contact_disable_autofill]"  value="0"  ' . checked( $qsm_contact_disable_autofill, '0', false ) . '>
					<label for="contact_disable_autofill-0">No</label><br>
			  </fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Disable auto fill for Quiz input
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_disable_auto_fill_for_quiz_input() {
		global $globalQuizsetting;
		$qsm_form_disable_autofill = ( isset( $globalQuizsetting['form_disable_autofill'] ) && '' !== $globalQuizsetting['form_disable_autofill'] ? $globalQuizsetting['form_disable_autofill'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="form_disable_autofill-1" name="qsm-quiz-settings[form_disable_autofill]" value="1" ' . checked( $qsm_form_disable_autofill, '1', false ) . '>
					<label for="form_disable_autofill-1">Yes</label><br>
					<input type="radio" id="form_disable_autofill-0" name="qsm-quiz-settings[form_disable_autofill]"  value="0" ' . checked( $qsm_form_disable_autofill, '0', false ) . '>
					<label for="form_disable_autofill-0">No</label><br>
			 </fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Display category name on front end
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_display_category_name_on_front_end() {
		global $globalQuizsetting;
		$qsm_show_category_on_front = ( isset( $globalQuizsetting['show_category_on_front'] ) && '' !== $globalQuizsetting['show_category_on_front'] ? $globalQuizsetting['show_category_on_front'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="show_category_on_front-1" name="qsm-quiz-settings[show_category_on_front]" value="1" ' . checked( $qsm_show_category_on_front, '1', false ) . ' >
				<label for="show_category_on_front-1">Yes</label><br>
				<input type="radio" id="show_category_on_front-0" name="qsm-quiz-settings[show_category_on_front]"  value="0" ' . checked( $qsm_show_category_on_front, '0', false ) . '>
				<label for="show_category_on_front-0">No</label><br>
			 </fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Show results inline
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_show_results_inline() {
		global $globalQuizsetting;
		$qsm_enable_quick_result_mc = ( isset( $globalQuizsetting['enable_quick_result_mc'] ) && '' !== $globalQuizsetting['enable_quick_result_mc'] ? $globalQuizsetting['enable_quick_result_mc'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="enable_quick_result_mc-1" name="qsm-quiz-settings[enable_quick_result_mc]" value="1" ' . checked( $qsm_enable_quick_result_mc, '1', false ) . '>
				<label for="enable_quick_result_mc-1">Yes</label><br>
				<input type="radio" id="enable_quick_result_mc-0" name="qsm-quiz-settings[enable_quick_result_mc]" value="0" ' . checked( $qsm_enable_quick_result_mc, '0', false ) . '>
				<label for="enable_quick_result_mc-0">No</label><br>
			 </fieldset>
			<span class="qsm-opt-desc">Instantly displays the result for each question</span>';
	}

	/**
	 * Generates Quiz Global  Field For End quiz if there is wrong answer
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_end_quiz_if_there_is_wrong_answer() {
		global $globalQuizsetting;
		$qsm_end_quiz_if_wrong = ( isset( $globalQuizsetting['end_quiz_if_wrong'] ) && '' !== $globalQuizsetting['end_quiz_if_wrong'] ? $globalQuizsetting['end_quiz_if_wrong'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="end_quiz_if_wrong-1" name="qsm-quiz-settings[end_quiz_if_wrong]" value="1" ' . checked( $qsm_end_quiz_if_wrong, '1', false ) . ' >
				<label for="end_quiz_if_wrong-1">Yes</label><br>
				<input type="radio" id="end_quiz_if_wrong-0" name="qsm-quiz-settings[end_quiz_if_wrong]"  value="0" ' . checked( $qsm_end_quiz_if_wrong, '0', false ) . '>
				<label for="end_quiz_if_wrong-0">No</label><br>
			 </fieldset>
			 <span class="qsm-opt-desc">This option works with vertical Multiple Choice , horizontal Multiple Choice , drop down , multiple response and horizontal multiple response question types</span>';
	}

	/**
	 * Generates Quiz Global  Field For Show correct answer inline
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_show_correct_answer_inline() {
		global $globalQuizsetting;
		$qsm_enable_quick_correct_answer_info = ( isset( $globalQuizsetting['enable_quick_correct_answer_info'] ) && '' !== $globalQuizsetting['enable_quick_correct_answer_info'] ? $globalQuizsetting['enable_quick_correct_answer_info'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="enable_quick_correct_answer_info-1" name="qsm-quiz-settings[enable_quick_correct_answer_info]" value="1" ' . checked( $qsm_enable_quick_correct_answer_info, '1', false ) . '>
					<label for="enable_quick_correct_answer_info-1">Yes When answer is correct</label><br>
					<input type="radio" id="enable_quick_correct_answer_info-2" name="qsm-quiz-settings[enable_quick_correct_answer_info]" value="2" ' . checked( $qsm_enable_quick_correct_answer_info, '2', false ) . '>
					<label for="enable_quick_correct_answer_info-2">Yes Independent of correct/incorrect</label><br>
					<input type="radio" id="enable_quick_correct_answer_info-0" name="qsm-quiz-settings[enable_quick_correct_answer_info]"  value="0" ' . checked( $qsm_enable_quick_correct_answer_info, '0', false ) . '>
					<label for="enable_quick_correct_answer_info-0">No</label><br>
			 </fieldset>
			 <span class="qsm-opt-desc">Show correct user info when inline result is enabled.</span>';
	}

	/**
	 * Generates Quiz Global  Field For Retake Quiz
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_retake_quiz() {
		global $globalQuizsetting;
		$qsm_enable_retake_quiz_button = ( isset( $globalQuizsetting['enable_retake_quiz_button'] ) && '' !== $globalQuizsetting['enable_retake_quiz_button'] ? $globalQuizsetting['enable_retake_quiz_button'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="enable_retake_quiz_button-1" name="qsm-quiz-settings[enable_retake_quiz_button]" value="1" ' . checked( $qsm_enable_retake_quiz_button, '1', false ) . '>
					<label for="enable_retake_quiz_button-1">Yes</label><br>
					<input type="radio" id="enable_retake_quiz_button-0" name="qsm-quiz-settings[enable_retake_quiz_button]"  value="0" ' . checked( $qsm_enable_retake_quiz_button, '0', false ) . '>
					<label for="enable_retake_quiz_button-0">No</label><br>
			</fieldset>
			<span class="qsm-opt-desc">Show a button on result page to retake the quiz</span>';
	}


	/**
	 * Generates Quiz Global  Field For Show current page number
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_show_current_page_number() {
		global $globalQuizsetting;
		$qsm_enable_pagination_quiz = ( isset( $globalQuizsetting['enable_pagination_quiz'] ) && '' !== $globalQuizsetting['enable_pagination_quiz'] ? $globalQuizsetting['enable_pagination_quiz'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="enable_pagination_quiz-1" name="qsm-quiz-settings[enable_pagination_quiz]" value="1" ' . checked( $qsm_enable_pagination_quiz, '1', false ) . '>
				<label for="enable_pagination_quiz-1">Yes</label><br>
				<input type="radio" id="enable_pagination_quiz-0" name="qsm-quiz-settings[enable_pagination_quiz]" value="0" ' . checked( $qsm_enable_pagination_quiz, '0', false ) . '>
				<label for="enable_pagination_quiz-0">No</label><br>
			</fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Deselect Answer
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_deselect_answer() {
		global $globalQuizsetting;
		$qsm_enable_deselect_option = ( isset( $globalQuizsetting['enable_deselect_option'] ) && '' !== $globalQuizsetting['enable_deselect_option'] ? $globalQuizsetting['enable_deselect_option'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
					<input type="radio" id="enable_deselect_option-1" name="qsm-quiz-settings[enable_deselect_option]" value="1" ' . checked( $qsm_enable_deselect_option, '1', false ) . '>
					<label for="enable_deselect_option-1">Yes</label><br>
					<input type="radio" id="enable_deselect_option-0" name="qsm-quiz-settings[enable_deselect_option]"  value="0" ' . checked( $qsm_enable_deselect_option, '0', false ) . '>
					<label for="enable_deselect_option-0">No</label><br>
			 </fieldset>
			 <span class="qsm-opt-desc">Users are able deselect an answer and leave it blank. Works with Multiple Choice and Horizintal Multiple Choice questions only</span>';
	}
	/**
	 * Generates Quiz Global  Field For Disable description on quiz result page?
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_disable_description_on_quiz_result_page() {
		global $globalQuizsetting;
		$qsm_disable_description_on_result = ( isset( $globalQuizsetting['disable_description_on_result'] ) && '' !== $globalQuizsetting['disable_description_on_result'] ? $globalQuizsetting['disable_description_on_result'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="disable_description_on_result-1" name="qsm-quiz-settings[disable_description_on_result]" value="1" ' . checked( $qsm_disable_description_on_result, '1', false ) . ' >
				<label for="disable_description_on_result-1">Yes</label><br>
				<input type="radio" id="disable_description_on_result-0" name="qsm-quiz-settings[disable_description_on_result]" value="0" ' . checked( $qsm_disable_description_on_result, '0', false ) . '>
				<label for="disable_description_on_result-0">No</label><br>
			</fieldset>';
	}
	/**
	 * Generates Quiz Global  Field For Disable scroll on next and previous button click?
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_disable_scroll_on_next_and_previous_button_click() {
		global $globalQuizsetting;
		$qsm_disable_scroll_next_previous_click = ( isset( $globalQuizsetting['disable_scroll_next_previous_click'] ) && '' !== $globalQuizsetting['disable_scroll_next_previous_click'] ? $globalQuizsetting['disable_scroll_next_previous_click'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="disable_scroll_next_previous_click-1" name="qsm-quiz-settings[disable_scroll_next_previous_click]" value="1" ' . checked( $qsm_disable_scroll_next_previous_click, '1', false ) . '>
				<label for="disable_scroll_next_previous_click-1">Yes</label><br>
				<input type="radio" id="disable_scroll_next_previous_click-0" name="qsm-quiz-settings[disable_scroll_next_previous_click]"  value="0" ' . checked( $qsm_disable_scroll_next_previous_click, '0', false ) . '>
				<label for="disable_scroll_next_previous_click-0">No</label><br>
			</fieldset>';
	}

	/**
	 * Generates Quiz Global  Field For Disable First page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_disable_first_page() {
		global $globalQuizsetting;
		$qsm_disable_first_page = ( isset( $globalQuizsetting['disable_first_page'] ) && '' !== $globalQuizsetting['disable_first_page'] ? $globalQuizsetting['disable_first_page'] : '0' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="radio" id="disable_first_page-1" name="qsm-quiz-settings[disable_first_page]" value="1" ' . checked( $qsm_disable_first_page, '1', false ) . '>
				<label for="disable_first_page-1">Yes</label><br>
				<input type="radio" id="disable_first_page-0" name="qsm-quiz-settings[disable_first_page]"  value="0" ' . checked( $qsm_disable_first_page, '0', false ) . '>
				<label for="disable_first_page-0">No</label><br>
			</fieldset>';
	}


	/**
	 * Generates Quiz Global  Field For Quiz Animation
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_quiz_animation() {
		global $globalQuizsetting;
		global $mlwQuizMasterNext;
		$qsm_quiz_animation = ( isset( $globalQuizsetting['quiz_animation'] ) && '' !== $globalQuizsetting['quiz_animation'] ? $globalQuizsetting['quiz_animation'] : '' );
		$options            = $mlwQuizMasterNext->pluginHelper->quiz_animation_effect();

		echo '<select  name="qsm-quiz-settings[quiz_animation]">';
		foreach ( $options as $value ) {
			echo '<option value="' . esc_attr( $value['value'] ) . '" ' . ( isset( $qsm_quiz_animation ) && $qsm_quiz_animation == $value['value'] ? 'Selected' : '' ) . ' >' . esc_html( $value['label'] ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Generates Quiz Global  Field For Logo URL
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_logo_url() {
		global $globalQuizsetting;
		$qsm_result_page_fb_image = ( isset( $globalQuizsetting['result_page_fb_image'] ) && '' !== $globalQuizsetting['result_page_fb_image'] ? $globalQuizsetting['result_page_fb_image'] : QSM_PLUGIN_URL . 'assets/icon-200x200.png' );
		echo '<input type="url" id="result_page_fb_image" name="qsm-quiz-settings[result_page_fb_image]" value="' . esc_url( $qsm_result_page_fb_image ) . '">
		<span class="qsm-opt-desc">If left blank, this will default to QSM logo</span>';
	}

	/**
	 * Generates Quiz Global  Field For Random Questions
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_random_questions() {
		global $globalQuizsetting;
		$qsm_randomness_order = ( isset( $globalQuizsetting['randomness_order'] ) && '' !== $globalQuizsetting['randomness_order'] ? $globalQuizsetting['randomness_order'] : '' );
		$options              = array(
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
		);
		echo '<select name="qsm-quiz-settings[randomness_order]">';
		foreach ( $options as $value ) {
			echo '<option value="' . esc_attr( $value['value'] ) . '" ' . ( isset( $qsm_randomness_order ) && $qsm_randomness_order == $value['value'] ? 'Selected' : '' ) . ' >' . esc_html( $value['label'] ) . '</option>';
		}
		echo '</select>';
	}
	/**
	 * Generates Quiz Global  Field For Quiz Dates
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_quiz_dates() {
		global $globalQuizsetting;
		$qsm_scheduled_time_start = ( isset( $globalQuizsetting['scheduled_time_start'] ) && '' !== $globalQuizsetting['scheduled_time_start'] ? $globalQuizsetting['scheduled_time_start'] : '' );
		$qsm_scheduled_time_end   = ( isset( $globalQuizsetting['scheduled_time_end'] ) && '' !== $globalQuizsetting['scheduled_time_end'] ? $globalQuizsetting['scheduled_time_end'] : '' );
		echo '<div>
				<span class="qsm-ph_text">Start Date</span>
				<input autocomplete="off" type="text" id="scheduled_time_start" name="qsm-quiz-settings[scheduled_time_start]" value="' . esc_attr( $qsm_scheduled_time_start ) . '">
				<span class="qsm-opt-desc">If set, Quiz will be accessible only after this date</span>
			</div>';
		echo '<div>
				<span class="qsm-ph_text">End Date</span>
				<input autocomplete="off" type="text" id="scheduled_time_end" name="qsm-quiz-settings[scheduled_time_end]" value="' . esc_attr( $qsm_scheduled_time_end ) . '">
				<span class="qsm-opt-desc"> If set, Quiz will not be accessible after this date</span>
			 </div>';
			wp_add_inline_script( 'qsm_admin_js', 'jQuery(function(){jQuery("#scheduled_time_start,#scheduled_time_end").datetimepicker({format: "m/d/Y H:i",step: 1});});' );
	}
	/**
	 * Generates Quiz Global  Field For Do not allow quiz submission after the end date/time
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_do_not_allow_quiz_submission_after_the_end_datetime() {
		global $globalQuizsetting;
		$qsm_not_allow_after_expired_time = ( isset( $globalQuizsetting['not_allow_after_expired_time'] ) && '' !== $globalQuizsetting['not_allow_after_expired_time'] ? $globalQuizsetting['not_allow_after_expired_time'] : '' );
		echo '<fieldset class="buttonset buttonset-hide" >
				<input type="checkbox" id="not_allow_after_expired_time-1" name="qsm-quiz-settings[not_allow_after_expired_time]" value="1" ' . checked( $qsm_not_allow_after_expired_time, '1', false ) . '>
				<br>
			</fieldset>';
	}

	/**
	 * Generates quiz global field for preferred date format
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function preferred_date_format() {

		global $globalQuizsetting;
		$preferred_date_format = ( isset( $globalQuizsetting['preferred_date_format'] ) ? $globalQuizsetting['preferred_date_format'] : get_option( 'date_format' ) );
		echo '<input type="text" id="preferred_date_format" name="qsm-quiz-settings[preferred_date_format]" value="' . esc_attr( $preferred_date_format ) . '">';
		echo '<span class="qsm-opt-desc">Set your preferred date format.</span>';
	}

	/**
	 * Generates quiz global field for default answers field
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function default_answers(){
		global $globalQuizsetting;
		$default_answers = ( isset( $globalQuizsetting['default_answers'] ) ? $globalQuizsetting['default_answers'] : 1 );
		echo '<input type="number" id="default_answers" name="qsm-quiz-settings[default_answers]" value="' . esc_attr( $default_answers ) . '" min="1">';
		echo '<span class="qsm-opt-desc">Adds number of answer fields.</span>';
	}

}

$qmnGlobalSettingsPage = new QMNGlobalSettingsPage();
?>
