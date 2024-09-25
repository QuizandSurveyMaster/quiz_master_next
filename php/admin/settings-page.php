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
		add_settings_field( 'enable-qsm-log', __( 'Enable QSM log', 'quiz-master-next' ), array( $this, 'enable_qsm_log' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'ip-collection', __( 'Disable collecting and storing IP addresses?', 'quiz-master-next' ), array( $this, 'ip_collection_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-search', __( 'Disable Quiz Posts From Being Searched?', 'quiz-master-next' ), array( $this, 'cpt_search_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-archive', __( 'Quiz Archive Settings', 'quiz-master-next' ), array( $this, 'cpt_archive_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'duplicate-quiz-with-theme', __( 'Duplicate Quiz Controls', 'quiz-master-next' ), array( $this, 'qsm_duplicate_quiz_with_theme' ), 'qmn_global_settings', 'qmn-global-section' );
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
		add_settings_field( 'api-key-options', __( 'Enable APIs', 'quiz-master-next' ), array( $this, 'api_key_options' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'api-key', __( 'API Key', 'quiz-master-next' ), array( $this, 'api_key_field' ), 'qmn_global_settings', 'qmn-global-section' );
	}

	/**
	 * Generates Setting Field For Post Archive
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function api_key_options() {
		$settings    = (array) get_option( 'qmn-settings' );
		$get_questions = ! empty( $settings['get_questions'] ) ? esc_attr( $settings['get_questions'] ) : '';
		$get_quiz = ! empty( $settings['get_quiz'] ) ? esc_attr( $settings['get_quiz'] ) : '';
		$allow_submit_quiz = ! empty( $settings['allow_submit_quiz'] ) ? esc_attr( $settings['allow_submit_quiz'] ) : '';
		$get_result = ! empty( $settings['get_result'] ) ? esc_attr( $settings['get_result'] ) : '';
		?>
		<fieldset>
			<label for="qmn-settings-get_questions">
				<input type="checkbox" name="qmn-settings[get_questions]" id="qmn-settings-get_questions" value="1" <?php checked( $get_questions, 1, true ); ?> />
				<?php esc_html_e( 'Get Questions', 'quiz-master-next'); ?>
			</label><br/>
			<label for="qmn-settings-get_quiz">
				<input type="checkbox" name="qmn-settings[get_quiz]" id="qmn-settings-get_quiz" value="1" <?php checked( $get_quiz, 1, true ); ?> />
				<?php esc_html_e( 'Get Quiz', 'quiz-master-next'); ?>
			</label><br/>
			<label for="qmn-settings-allow_submit_quiz">
				<input type="checkbox" name="qmn-settings[allow_submit_quiz]" id="qmn-settings-allow_submit_quiz" value="1" <?php checked( $allow_submit_quiz, 1, true ); ?> />
				<?php esc_html_e( 'Submit Quiz', 'quiz-master-next'); ?>
			</label><br/>
			<label for="qmn-settings-get_result">
				<input type="checkbox" name="qmn-settings[get_result]" id="qmn-settings-get_result" value="1" <?php checked( $get_result, 1, true ); ?> />
				<?php esc_html_e( 'Get Result', 'quiz-master-next'); ?>
			</label><br/>
		</fieldset>
		<?php
	}


	public function api_key_field() {
		$settings   = (array) get_option( 'qmn-settings' );
		$api_key = ! empty( $settings['api_key'] ) ? esc_attr( $settings['api_key'] ) : '';

		$qpi_script_inline = array(
			'confirmation_message' => __('Are you sure you want to regenerate the API Key? This will affect your settings when you save changes, and the old key will no longer work.', 'quiz-master-next'),
			'nonce'                => wp_create_nonce('regenerate_api_key_nonce'),
		);
		wp_localize_script( 'qsm_admin_js', 'qsm_api_object', $qpi_script_inline );
		?>
		<input type='text' name='qmn-settings[api_key]' class="qsm-api-key-input" id='qmn-settings[api_key]' readonly value='<?php echo esc_attr( $api_key ); ?>' />
		<?php if ( "" != $api_key ) { ?>
			<button class="button qsm-generate-api-key confirmation" ><?php esc_html_e('Regenerate Key', 'quiz-master-next'); ?></button>
		<?php } else { ?>
			<button class="button qsm-generate-api-key"><?php esc_html_e('Generate Key', 'quiz-master-next'); ?></button>
		<?php } ?>
		<?php
	}

	/**
	 * Default settings value
	 *
	 * @since 7.3.10
	 * @return array
	 */
	public static function default_settings() {
		return array(
			'form_type'                              => 0,
			'system'                                 => 3,
			'score_roundoff'                         => 0,
			'progress_bar'                           => 0,
			'require_log_in'                         => 0,
			'pagination'                             => 0,
			'timer_limit'                            => 0,
			'enable_result_after_timer_end'          => 0,
			'skip_validation_time_expire'            => 0,
			'total_user_tries'                       => 0,
			'limit_total_entries'                    => 0,
			'question_from_total'                    => 0,
			'question_per_category'                  => 0,
			'contact_info_location'                  => 0,
			'loggedin_user_contact'                  => 0,
			'comment_section'                        => 1,
			'question_numbering'                     => 0,
			'show_optin'                             => 0,
			'show_text_html'                         => 0,
			'store_responses'                        => 1,
			'send_email'                             => 1,
			'disable_answer_onselect'                => 0,
			'ajax_show_correct'                      => 0,
			'contact_disable_autofill'               => 0,
			'form_disable_autofill'                  => 0,
			'show_category_on_front'                 => 0,
			'enable_quick_result_mc'                 => 0,
			'end_quiz_if_wrong'                      => 0,
			'enable_quick_correct_answer_info'       => 0,
			'show_question_featured_image_in_result' => 0,
			'enable_retake_quiz_button'              => 1,
			'enable_pagination_quiz'                 => 0,
			'enable_deselect_option'                 => 0,
			'disable_description_on_result'          => 0,
			'disable_scroll_next_previous_click'     => 0,
			'disable_first_page'                     => 0,
			'disable_mathjax'                        => 0,
			'quiz_animation'                         => '',
			'result_page_fb_image'                   => QSM_PLUGIN_URL . 'assets/icon-200x200.png',
			'randomness_order'                       => 0,
			'scheduled_time_start'                   => '',
			'scheduled_time_end'                     => '',
			'not_allow_after_expired_time'           => 0,
			'preferred_date_format'                  => 'F j, Y',
			'default_answers'                        => 1,
			'correct_answer_logic'                   => 0,
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
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_general' );
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_quiz_submission' );
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_display' );
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_contact' );
		add_settings_field( 'quiz-type', __( 'Select Type', 'quiz-master-next' ), array( $this, 'qsm_global_quiz_type' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'grading-system', __( 'Grading System', 'quiz-master-next' ), array( $this, 'qsm_global_grading_system' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'correct_answer_logic', __( 'Answer Settings', 'quiz-master-next' ), array( $this, 'correct_answer_logic' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'random-questions', __( 'Randomize Question', 'quiz-master-next' ), array( $this, 'qsm_global_random_questions' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'quiz-dates', __( 'Quiz Dates', 'quiz-master-next' ), array( $this, 'qsm_global_quiz_dates' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'limit-number-of-questions', __( 'Limit number of Questions', 'quiz-master-next' ), array( $this, 'qsm_global_limit_number_of_questions' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'default_answers', __( 'Answer Fields in Question Editor', 'quiz-master-next' ), array( $this, 'default_answers' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'require_log_in', __( 'User Access', 'quiz-master-next' ), array( $this, 'qsm_global_require_log_in' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'enable-comments', __( 'Enable comments', 'quiz-master-next' ), array( $this, 'qsm_global_enable_comments' ), 'qsm_default_global_option_general', 'qmn-global-section' );
		add_settings_field( 'time-limit-in-minutes', __( 'Timer Settings', 'quiz-master-next' ), array( $this, 'qsm_global_time_limit_in_minutes' ), 'qsm_default_global_option_quiz_submission', 'qmn-global-section' );
		add_settings_field( 'end-quiz-if-there-is-wrong-answer', __( 'Response Settings', 'quiz-master-next' ), array( $this, 'qsm_global_end_quiz_if_there_is_wrong_answer' ), 'qsm_default_global_option_quiz_submission', 'qmn-global-section' );
		add_settings_field( 'limit-attempts', __( 'Quiz Controls', 'quiz-master-next' ), array( $this, 'qsm_global_limit_attempts' ), 'qsm_default_global_option_quiz_submission', 'qmn-global-section' );
		add_settings_field( 'save-responses', __( 'Submit Actions', 'quiz-master-next' ), array( $this, 'qsm_global_save_responses' ), 'qsm_default_global_option_quiz_submission', 'qmn-global-section' );
		add_settings_field( 'show-progress-bar', __( 'Progress Controls', 'quiz-master-next' ), array( $this, 'qsm_global_show_progress_bar' ), 'qsm_default_global_option_display', 'qmn-global-section' );
		add_settings_field( 'questions-per-page', __( 'Question Preferences', 'quiz-master-next' ), array( $this, 'qsm_global_questions_per_page' ), 'qsm_default_global_option_display', 'qmn-global-section' );
		add_settings_field( 'show-opt-in-answers-default', __( 'Result Page Controls', 'quiz-master-next' ), array( $this, 'qsm_global_show_optin_answers' ), 'qsm_default_global_option_display', 'qmn-global-section' );
		add_settings_field( 'quiz-animation', __( 'Quiz Page Settings', 'quiz-master-next' ), array( $this, 'qsm_global_quiz_animation' ), 'qsm_default_global_option_display', 'qmn-global-section' );
		add_settings_field( 'logo-url', __( 'Advanced Settings', 'quiz-master-next' ), array( $this, 'qsm_global_logo_url' ), 'qsm_default_global_option_display', 'qmn-global-section' );
		add_settings_field( 'contact-form-position', __( 'Contact form position', 'quiz-master-next' ), array( $this, 'qsm_global_contact_form_position' ), 'qsm_default_global_option_contact', 'qmn-global-section' );
		add_settings_field( 'show-contact-form-to-logged-in-users', __( 'Show contact form to logged in users', 'quiz-master-next' ), array( $this, 'qsm_global_show_contact_form_to_logged_in_users' ), 'qsm_default_global_option_contact', 'qmn-global-section' );
		add_settings_field( 'disable-auto-fill-for-contact-input', __( 'Disable auto fill for contact input', 'quiz-master-next' ), array( $this, 'qsm_global_disable_auto_fill_for_contact_input' ), 'qsm_default_global_option_contact', 'qmn-global-section' );
		add_settings_field( 'disable-first-page', __( 'Disable first page on quiz', 'quiz-master-next' ), array( $this, 'qsm_global_disable_first_page' ), 'qsm_default_global_option_contact', 'qmn-global-section' );
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
	 * Generates Setting Field To Duplicate Quiz with Theme Settings
	 *
	 * @since 8.1.19
	 * @return void
	 */
	public function qsm_duplicate_quiz_with_theme() {
		$settings = (array) get_option( 'qmn-settings' );
		$duplicate_quiz_with_theme = ! empty( $settings['duplicate_quiz_with_theme'] ) ? esc_attr( $settings['duplicate_quiz_with_theme'] ) : 0;
		?>
		<fieldset>
			<label for="qmn-settings-duplicate_quiz_with_theme">
				<input type="checkbox" name="qmn-settings[duplicate_quiz_with_theme]" id="qmn-settings-duplicate_quiz_with_theme" value="1" <?php checked( $duplicate_quiz_with_theme, 1, true ); ?> />
				<?php esc_html_e( 'Enable quiz duplication along with theme settings', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Generates Setting Field For Post Archive
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function cpt_archive_field() {
		$settings    = (array) get_option( 'qmn-settings' );
		$cpt_archive = ! empty( $settings['cpt_archive'] ) ? esc_attr( $settings['cpt_archive'] ) : 0;
		$cpt_link = ! empty( $settings['disable_quiz_public_link'] ) ? esc_attr( $settings['disable_quiz_public_link'] ) : 0;
		?>
		<fieldset>
			<label for="qmn-settings-cpt_archive">
				<input type="checkbox" name="qmn-settings[cpt_archive]" id="qmn-settings-cpt_archive" value="1" <?php checked( $cpt_archive, 1, true ); ?> />
				<?php esc_html_e( 'Disable Quiz Archive', 'quiz-master-next'); ?>
			</label><br/>
			<label for="qmn-settings-qsm-quiz-public-link">
				<input type="checkbox" name="qmn-settings[disable_quiz_public_link]" id="qmn-settings-qsm-quiz-public-link" value="1" <?php echo checked( $cpt_link, 1, true ); ?> />
				<?php esc_html_e( 'Disable Quiz Public link', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
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
		wp_editor( $template, 'results_template', array(
			'textarea_name' => 'qmn-settings[results_details_template]',
			'tinymce'       => true,
		)
		);
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
	 * Generates Setting Field For QSM logs
	 *
	 * @since 8.1.9
	 * @return void
	 */
	public function enable_qsm_log() {
		$settings         = (array) get_option( 'qmn-settings' );
		$enable_qsm_log = ! empty( $settings['enable_qsm_log'] ) ? esc_attr( $settings['enable_qsm_log'] ) : 0;
		?>
		<label class="switch">
			<input type="checkbox" name="qmn-settings[enable_qsm_log]" id="qmn-settings[enable_qsm_log]" value="1"' <?php checked( $enable_qsm_log, 1, true ); ?>/><span class="slider round"></span>
		</label>
		<span class='global-sub-text' for='qmn-settings[enable_qsm_log]'><?php esc_html_e( "Enable this option to generate QSM error logs", 'quiz-master-next' );?></span>
		<?php
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
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Global Settings', 'quiz-master-next' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<!-- when tab buttons are clicked we jump back to the same page but with a new parameter that represents the clicked tab. accordingly we make it active -->
				<a href="?page=qmn_global_settings&tab=qmn_global_settings" class="nav-tab <?php echo empty( $_GET['tab'] ) || 'qmn_global_settings' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Main Settings', 'quiz-master-next' ); ?></a>
				<a href="?page=qmn_global_settings&tab=quiz-default-options" class="nav-tab <?php echo ! empty( $_GET['tab'] ) && 'quiz-default-options' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Quiz Default Options', 'quiz-master-next' ); ?></a>
				<?php do_action( 'qsm_global_settings_page_add_tab_after' ); ?>
			</h2>
			<?php do_action( 'qsm_global_settings_page_added_tab_content' ); ?>
			<?php if ( empty( $_GET['tab'] ) || 'qmn_global_settings' === $_GET['tab'] || 'quiz-default-options' === $_GET['tab'] ) { ?>

				<form action="options.php" method="POST" class="qsm_global_settings">
					<?php
					if ( isset( $_GET['settings-updated'] ) ) {
						flush_rewrite_rules( true );
						echo '<div class="updated" style="padding: 10px;">';
							echo '<span>' . esc_html__( ' Settings have been updated!', 'quiz-master-next' ) . '</span>';
						echo '</div>';
					}

					if ( empty( $_GET['tab'] ) || 'qmn_global_settings' === $_GET['tab'] ) {
						settings_fields( 'qmn-settings-group' );
						do_settings_sections( 'qmn_global_settings' );
					}
					if ( ! empty( $_GET['tab'] ) && 'quiz-default-options' === $_GET['tab'] ) {
						settings_fields( 'qsm-quiz-settings-group' );
						?>
						<div class="qsm-sub-tab-menu" style="display: inline-block;width: 100%;">
							<ul class="subsubsub">
								<li>
									<a href="javascript:void(0)" data-id="qsm_general" class="current quiz_style_tab"><?php esc_html_e( 'General', 'quiz-master-next' ); ?></a>
								</li>
								<li>
									<a href="javascript:void(0)" data-id="quiz_submission" class="quiz_style_tab"><?php esc_html_e( 'Quiz submission', 'quiz-master-next' ); ?></a>
								</li>
								<li>
									<a href="javascript:void(0)" data-id="display" class="quiz_style_tab"><?php esc_html_e( 'Display', 'quiz-master-next' ); ?></a>
								</li>
								<li>
									<a href="javascript:void(0)" data-id="contact_form" class="quiz_style_tab"><?php esc_html_e( 'Contact form', 'quiz-master-next' ); ?></a>
								</li>
							</ul>
						</div>

						<div id="qsm_general" class="quiz_style_tab_content">
							<?php do_settings_sections( 'qsm_default_global_option_general' ); ?>
						</div>
						<div id="quiz_submission" class="quiz_style_tab_content" style="display:none">
							<?php do_settings_sections( 'qsm_default_global_option_quiz_submission' ); ?>
						</div>
						<div id="display" class="quiz_style_tab_content" style="display:none">
							<?php do_settings_sections( 'qsm_default_global_option_display' ); ?>
						</div>
						<div id="contact_form" class="quiz_style_tab_content" style="display:none">
							<?php do_settings_sections( 'qsm_default_global_option_contact' ); ?>
						</div>
						<?php
					}
					?>
					<div class="option-page-option-tab-footer">
						<p></p>
						<div>
							<a class="qsm-btn-link-global-settings" id="qsm-apply-global-settings" href="javascript:void(0);"><?php esc_html_e( 'Apply to multiple quizzes', 'quiz-master-next'); ?></a>
							<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'quiz-master-next'); ?>">
						</div>
					</div>
				</form>
			<?php }
				if ( isset( $_POST['qsm-apply-global-settings-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm-apply-global-settings-nonce'] ) ), 'qsm-apply-global-settings-nonce' ) && ! empty( $_POST['qsm-select-quiz'] ) ) {
					global $mlwQuizMasterNext;
					$quizzes = qsm_sanitize_rec_array( wp_unslash( $_POST['qsm-select-quiz'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					foreach ( $quizzes as $quiz_id ) {
						$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
						$quiz_settings  = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'quiz_options' );
						$global_settings = QMNGlobalSettingsPage::get_global_quiz_settings();
						$global_settings = wp_parse_args( $global_settings, $quiz_settings );
						$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'quiz_options', $global_settings );
					}
					echo '<div class="updated" style="padding: 10px;">';
						echo '<span>' . count( $quizzes ) . esc_html__( ' Quiz have been updated!', 'quiz-master-next' ) . '</span>';
					echo '</div>';
				}
				?>
		</div>
		<!-- set global setting popup start -->
		<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="qsm-global-apply-default-popup" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
					<form action="" method="POST" id="qsm-apply-global-settings-form">
						<header class="qsm-popup__header">
							<h2 class="qsm-popup__title" id="modal-1-title"><?php esc_html_e( 'Apply default settings to form', 'quiz-master-next' ); ?></h2>
							<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
						</header>
						<main class="qsm-popup__content" id="qsm-global-default-popup-content">
							<?php wp_nonce_field( 'qsm-apply-global-settings-nonce', 'qsm-apply-global-settings-nonce' ); ?>
							<?php
							$args    = array(
								'post_type'      => 'qsm_quiz',
								'posts_per_page' => -1,
								'post_status'    => 'publish',
							);
							$quizzes = get_posts( $args );
							?>
							<div class="qsm-field-row">
								<label class="qsm-label" for="qsm-select-quiz-apply"><strong><?php esc_html_e( 'Apply the default settings to selected form type', 'quiz-master-next' ); ?></strong></label>
								<div id="qsm-export-settings-options">
									<select name="qsm-select-quiz[]" multiple="multiple" id="qsm-select-quiz-apply" required>
										<?php if ( $quizzes ) : ?>
											<?php foreach ( $quizzes as $quiz ) : ?>
												<?php $quiz_id = get_post_meta( $quiz->ID, 'quiz_id', true ); ?>
												<option value="<?php echo esc_attr( $quiz_id ); ?>" id="<?php echo esc_attr( $quiz_id ); ?>"><?php echo esc_html( $quiz->post_title ); ?></option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
							</div>
							<div class="qsm-popup-upgrade-warning" style="margin-top: 15px;background:#FFDEDD;border-color:#AD0000;color:#AD0000">
								<span class="dashicons dashicons-info" style=" font-size: 35px; line-height: 20px; margin-right: 20px; "></span>
								<span>
									<?php esc_html_e( 'Do you want to continue and reset all the settings?', 'quiz-master-next' ); ?>
									<br/><strong> <?php esc_html_e( 'Please note that this action is not reversible.', 'quiz-master-next' ); ?></strong>
								</span>
							</div>
						</main>
						<footer class="qsm-popup__footer">
							<button class="qsm-popup__btn" data-micromodal-close="" aria-label="<?php esc_html_e( 'Close this dialog window', 'quiz-master-next' ); ?>"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
							<button class="button button-primary" type="submit" id="qsm-apply-global-default-btn"><?php esc_html_e( 'Apply', 'quiz-master-next' ); ?></button>
						</footer>
					</form>
				</div>
			</div>
		</div>
		<!-- set global setting popup end -->
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

	/* ====== General Tab start ==========*/
	/**
	 * Generates Quiz Global  Field For Quiz Type
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_quiz_type() {
		global $globalQuizsetting;
		$qsm_form_type = ( isset( $globalQuizsetting['form_type'] ) && '' !== $globalQuizsetting['form_type'] ? $globalQuizsetting['form_type'] : '' );
		?>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="form-type">
			<label class="qsm-option-label" for="qsm-form-type-0">
				<input type="radio" id="qsm-form-type-0" name="qsm-quiz-settings[form_type]" value="0" <?php checked( $qsm_form_type, 0 ); ?>>
				<?php esc_html_e( 'Quiz', 'quiz-master-next' ); ?>
			</label>
			<label class="qsm-option-label" for="qsm-form-type-1">
				<input type="radio" id="qsm-form-type-1" name="qsm-quiz-settings[form_type]" value="1" <?php checked( $qsm_form_type, 1 ); ?>>
				<?php esc_html_e( 'Survey', 'quiz-master-next' ); ?>
			</label>
			<label class="qsm-option-label" for="qsm-form-type-2">
				<input type="radio" id="qsm-form-type-2" name="qsm-quiz-settings[form_type]" value="2" <?php checked( $qsm_form_type, 2 ); ?>>
				<?php esc_html_e( 'Simple Form', 'quiz-master-next' ); ?>
			</label>
		</fieldset>
		<?php
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
		$qsm_score_roundoff = ( isset( $globalQuizsetting['score_roundoff'] ) && '' !== $globalQuizsetting['score_roundoff'] ? $globalQuizsetting['score_roundoff'] : '' );
		?>
		<fieldset class="buttonset buttonset-hide global_setting_system" >
			<label for="qsm-system-0">
				<input type="radio" id="qsm-system-0" name="qsm-quiz-settings[system]" value="0" <?php checked( $qsm_system, 0 ); ?>>
				<?php esc_html_e( 'Correct/Incorrect', 'quiz-master-next' ); ?>
			</label>
			<label for="qsm-system-1">
				<input type="radio" id="qsm-system-1" name="qsm-quiz-settings[system]" value="1" <?php checked( $qsm_system, 1 ); ?>>
				<?php esc_html_e( 'Points', 'quiz-master-next' ); ?>
			</label>
			<label for="qsm-system-3">
				<input type="radio" id="qsm-system-3" name="qsm-quiz-settings[system]"  value="3" <?php checked( $qsm_system, 3 ); ?>>
				<?php esc_html_e( 'Both', 'quiz-master-next' ); ?>
			</label>
			<label for="qsm-score-roundoff">
				<input type="checkbox" id="qsm-score-roundoff" name="qsm-quiz-settings[score_roundoff]"  value="1" <?php checked( $qsm_score_roundoff, 1 ); ?>>
				<?php esc_html_e( 'Round off all scores and points', 'quiz-master-next' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Generates quiz global field for correct answers logic field
	 *
	 * @since 7.3.15
	 * @return void
	 */
	public function correct_answer_logic(){
		global $globalQuizsetting;
		$qsm_all_correct_selected = ( isset( $globalQuizsetting['correct_answer_logic'] ) && '' !== $globalQuizsetting['correct_answer_logic'] ? $globalQuizsetting['correct_answer_logic'] : '' );
		$qsm_enable_deselect_option = ( isset( $globalQuizsetting['enable_deselect_option'] ) && '' !== $globalQuizsetting['enable_deselect_option'] ? $globalQuizsetting['enable_deselect_option'] : '0' );
		$qsm_form_disable_autofill = ( isset( $globalQuizsetting['form_disable_autofill'] ) && '' !== $globalQuizsetting['form_disable_autofill'] ? $globalQuizsetting['form_disable_autofill'] : '0' );
		$qsm_disable_mathjax = ( isset( $globalQuizsetting['disable_mathjax'] ) && '' !== $globalQuizsetting['disable_mathjax'] ? $globalQuizsetting['disable_mathjax'] : '0' );
		?>
		<fieldset class="buttonset buttonset-hide qsm-p-b-10" id="qsm-correct-answer-logic">
			<div class="qsm-mb-1"><?php esc_html_e( 'Correct Answer Logic', 'quiz-master-next' ); ?>:
				<br/><small><?php
				/* translators: %s: HTML tag */
				echo sprintf( esc_html__( 'Works with %1$sMultiple Response, Horizontal Multiple Response%2$s and %3$sFill in the Blanks%4$s Question Types.', 'quiz-master-next' ), '<b>', '</b>', '<b>', '</b>' ); ?></small>
			</div>
			<label for="qsm-correct-answer-logic-1">
				<input type="radio" id="qsm-correct-answer-logic-1" name="qsm-quiz-settings[correct_answer_logic]" value="1" <?php checked( $qsm_all_correct_selected, 1 ); ?>>
				<?php esc_html_e( 'Accept all correct answers', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-correct-answer-logic-0">
				<input type="radio" id="qsm-correct-answer-logic-0" name="qsm-quiz-settings[correct_answer_logic]"  value="0" <?php checked( $qsm_all_correct_selected, 0 ); ?>>
				<?php esc_html_e( 'Accept any correct answer', 'quiz-master-next' ); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<div class="qsm-mb-1"><?php esc_html_e( 'Other Answer Settings', 'quiz-master-next' ); ?>:</div>
			<label class="qsm-option-label" for="qsm-enable-deselect-option">
				<input type="checkbox" id="qsm-enable-deselect-option" name="qsm-quiz-settings[enable_deselect_option]" value="1" <?php checked( $qsm_enable_deselect_option, 1 ); ?>>
				<?php esc_html_e( 'Allow user to deselect an answer and leave it blank.', 'quiz-master-next' ); ?>
				<i class="qsm-font-light">(<?php esc_html_e( 'Works with multiple choice & horizontal multiple choice questions only', 'quiz-master-next' ); ?>)</i>
			</label>
			<label class="qsm-option-label" for="qsm-form-disable-autofill">
				<input type="checkbox" id="qsm-form-disable-autofill" name="qsm-quiz-settings[form_disable_autofill]" value="1" <?php checked( $qsm_form_disable_autofill, 1 ); ?>>
				<?php esc_html_e( 'Disable auto-fill suggestions for the quiz inputs.', 'quiz-master-next' ); ?>
			</label>
			<label class="qsm-option-label" for="qsm-disable-mathjax">
				<input type="checkbox" id="qsm-disable-mathjax" name="qsm-quiz-settings[disable_mathjax]" value="1" <?php checked( $qsm_disable_mathjax, 1 ); ?>>
				<?php esc_html_e( 'Disable entering math formulas in questions, using TeX and LaTeX notation.', 'quiz-master-next' ); ?>
			</label>
		</fieldset>
		<?php
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
		?>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label for="qsm-randomness-order-0">
				<input type="radio" id="qsm-randomness-order-0" name="qsm-quiz-settings[randomness_order]" <?php checked( $qsm_randomness_order, 0 ); ?> value="0">
				<?php esc_html_e( 'Disabled', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-randomness-order-1">
				<input type="radio" id="qsm-randomness-order-1" name="qsm-quiz-settings[randomness_order]" <?php checked( $qsm_randomness_order, 1 ); ?> value="1">
				<?php esc_html_e( 'Randomize question only', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-randomness-order-3">
				<input type="radio" id="qsm-randomness-order-3" name="qsm-quiz-settings[randomness_order]" <?php checked( $qsm_randomness_order, 3 ); ?> value="3">
				<?php esc_html_e( 'Randomize answers only', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-randomness-order-2">
				<input type="radio" id="qsm-randomness-order-2" name="qsm-quiz-settings[randomness_order]" <?php checked( $qsm_randomness_order, 2 ); ?> value="2">
				<?php esc_html_e( 'Randomize questions and their answers', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
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
		$qsm_not_allow_after_expired_time = ( isset( $globalQuizsetting['not_allow_after_expired_time'] ) && '' !== $globalQuizsetting['not_allow_after_expired_time'] ? $globalQuizsetting['not_allow_after_expired_time'] : '' );
		?>
		<fieldset class="buttonset buttonset-hide qsm_tab_content" data-hide="1" id="qsm-scheduled-time" style="padding-left:0">
			<input autocomplete="off" class="qsm-date-picker" type="text" placeholder="<?php esc_attr_e( 'Start Date', 'quiz-master-next'); ?> " id="qsm-scheduled-time-start-input" name="qsm-quiz-settings[scheduled_time_start]" value="<?php echo esc_attr($qsm_scheduled_time_start); ?>">
			<input autocomplete="off" class="qsm-date-picker" type="text" placeholder="<?php esc_attr_e( 'End Date', 'quiz-master-next'); ?>" id="scheduled_time_end-input" name="qsm-quiz-settings[scheduled_time_end]" value="<?php echo esc_attr($qsm_scheduled_time_end); ?>">
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="not_allow_after_expired_time">
			<label class="qsm-option-label" for="not_allow_after_expired_time-1">
				<input type="checkbox" id="not_allow_after_expired_time-1" name="qsm-quiz-settings[not_allow_after_expired_time]" value="1" <?php checked( $qsm_not_allow_after_expired_time, 1 ); ?> >
				<?php esc_html_e( 'Do not allow quiz submission after the end date/time', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
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
		$qsm_question_per_category = ( isset( $globalQuizsetting['question_per_category'] ) && '' !== $globalQuizsetting['question_per_category'] ? $globalQuizsetting['question_per_category'] : '0' );
		?>
		<fieldset class="buttonset buttonset-hide qsm-p-b-10" data-hide="1" id="qsm-question-from-total">
			<input class="small-text" type="number" step="1" min="0" id="qsm-question-from-total-input" name="qsm-quiz-settings[question_from_total]" value="<?php echo esc_attr( $qsm_question_from_total ); ?>">
			<?php esc_html_e( 'Maximum question limit', 'quiz-master-next'); ?>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="qsm-question-per-category">
			<input class="small-text" type="number" step="1" min="0" id="qsm-question-per-category-input" name="qsm-quiz-settings[question_per_category]" value="<?php echo esc_attr( $qsm_question_per_category ); ?>">
				<span class="qsm-opt-tr">
					<?php esc_html_e( 'Limit number of questions per category', 'quiz-master-next'); ?>
					<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
						<span class="qsm-tooltips">
							<?php esc_html_e( 'Show only limited number of category questions from your quiz.You also need to set Limit Number of questions.', 'quiz-master-next'); ?>
						</span>
					</span>
				</span>
		</fieldset>
		<?php
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
		?>
		<label class="qsm-option-label" for="qsm-default-answers">
			<?php esc_html_e( 'Show ', 'quiz-master-next' ); ?>
			<input class="small-text" type="number" step="1" min="0" id="qsm-default-answers" name="qsm-quiz-settings[default_answers]" value="<?php echo esc_attr( $default_answers ); ?>">
			<?php esc_html_e( 'Answer Field in Question Editor.', 'quiz-master-next' ); ?>
		<?php
	}
	/**
	 * Generates Quiz Global  Field For required login
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_require_log_in() {
		global $globalQuizsetting;
		$qsm_require_log_in = ( isset( $globalQuizsetting['require_log_in'] ) && '' !== $globalQuizsetting['require_log_in'] ? $globalQuizsetting['require_log_in'] : 0 );
		?>
		<label for="qsm-require-log-in">
			<input type="checkbox" id="qsm-require-log-in" name="qsm-quiz-settings[require_log_in]" value="1" <?php checked( $qsm_require_log_in, 1 ); ?>>
			<?php esc_html_e( 'Allow only logged-in users to access the content', 'quiz-master-next' ); ?>
		</label>
		<?php
	}
	/**
	 * Generates Quiz Global  Field For Enable comments
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_enable_comments() {
		global $globalQuizsetting;
		$qsm_comment_section = ( isset( $globalQuizsetting['comment_section'] ) && '' !== $globalQuizsetting['comment_section'] ? $globalQuizsetting['comment_section'] : 0 );
		?>
		<label class="qsm-opt-tr" for="qsm-comment-section">
			<input type="checkbox" id="qsm-comment-section" name="qsm-quiz-settings[comment_section]" value="0" <?php checked( $qsm_comment_section, 0 ); ?> >
			<?php esc_html_e( 'Allow users to post comments at the end of the form type', 'quiz-master-next' ); ?>
		</label>
		<?php
	}
	/* ====== General Tab End ==========*/
	/* ====== Submission Tab Start ==========*/
	/**
	 * Generates Quiz Global  Field For Time Limit (in minutes)
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_time_limit_in_minutes() {
		global $globalQuizsetting;
		$qsm_timer_limit = ( isset( $globalQuizsetting['timer_limit'] ) && '' !== $globalQuizsetting['timer_limit'] ? $globalQuizsetting['timer_limit'] : '0' );
		$qsm_enable_result_after_timer_end = ( isset( $globalQuizsetting['enable_result_after_timer_end'] ) && '' !== $globalQuizsetting['enable_result_after_timer_end'] ? $globalQuizsetting['enable_result_after_timer_end'] : '0' );
		$qsm_skip_validation_time_expire = ( isset( $globalQuizsetting['skip_validation_time_expire'] ) && '' !== $globalQuizsetting['skip_validation_time_expire'] ? $globalQuizsetting['skip_validation_time_expire'] : '0' );
		?>
		<fieldset class="buttonset buttonset-hide">
			<input class="small-text" type="number" placeholder="" step="1" min="0" name="qsm-quiz-settings[timer_limit]" value="<?php echo esc_attr( $qsm_timer_limit ); ?>">
			<?php esc_html_e( 'Minutes', 'quiz-master-next' ); ?>
			<label class="qsm-opt-desc"><?php esc_html_e( 'Set it to 0 or blank to remove the time restriction.', 'quiz-master-next' ); ?></label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<label class="qsm-option-label" for="qsm-enable-result-after-timer-end">
				<input type="checkbox" id="qsm-enable-result-after-timer-end" name="qsm-quiz-settings[enable_result_after_timer_end]" value="1" <?php checked( $qsm_enable_result_after_timer_end, 1 ); ?> >
				<?php esc_html_e( 'Submit automatically when timer ends', 'quiz-master-next' ); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<label class="qsm-option-label" for="qsm-skip-validation-time-expire">
				<input type="checkbox" id="qsm-skip-validation-time-expire" name="qsm-quiz-settings[skip_validation_time_expire]" value="1" <?php checked( $qsm_skip_validation_time_expire, 1 ); ?>>
				<?php esc_html_e( 'Ignore validations after timer expires', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
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
		$qsm_disable_answer_onselect = ( isset( $globalQuizsetting['disable_answer_onselect'] ) && '' !== $globalQuizsetting['disable_answer_onselect'] ? $globalQuizsetting['disable_answer_onselect'] : '0' );
		?>
		<fieldset class="buttonset buttonset-hide">
			<input class="small-text" type="number" placeholder="Set Limit" step="1" min="0" name="qsm-quiz-settings[end_quiz_if_wrong]" value="<?php echo esc_attr( $qsm_end_quiz_if_wrong ); ?>">
			<?php esc_html_e( 'Incorrect answers will end the quiz', 'quiz-master-next'); ?>
			<label class="qsm-opt-desc">
				<?php esc_html_e( 'Set it to 0 or blank to remove the Incorrect answers limit', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<label class="qsm-option-label" for="qsm-disable-answer-onselect">
				<input type="checkbox" id="qsm-disable-answer-onselect" name="qsm-quiz-settings[disable_answer_onselect]" value="1" <?php checked( $qsm_disable_answer_onselect, 1 ); ?>>
				<?php esc_html_e( 'Prevent users from changing their response.', 'quiz-master-next'); ?>
				<i class="qsm-font-light">(<?php esc_html_e( 'Works with multiple choice questions only', 'quiz-master-next'); ?>)</i>
			</label>
		</fieldset>
		<?php
	}
	/**
	 * Generates Quiz Global  Field For Limit Attempts
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_limit_attempts() {
		global $globalQuizsetting;
		$qsm_total_user_tries = ( isset( $globalQuizsetting['total_user_tries'] ) && '' !== $globalQuizsetting['total_user_tries'] ? $globalQuizsetting['total_user_tries'] : 0 );
		$qsm_limit_total_entries = ( isset( $globalQuizsetting['limit_total_entries'] ) && '' !== $globalQuizsetting['limit_total_entries'] ? $globalQuizsetting['limit_total_entries'] : 0 );
		$qsm_enable_retake_quiz_button = ( isset( $globalQuizsetting['enable_retake_quiz_button'] ) && '' !== $globalQuizsetting['enable_retake_quiz_button'] ? $globalQuizsetting['enable_retake_quiz_button'] : 0 );
		?>
		<fieldset class="buttonset buttonset-hide">
			<input class="small-text" id="qsm-global-setting-total-user-tries" type="number" placeholder="Set Limit" step="1" min="0" name="qsm-quiz-settings[total_user_tries]" value="<?php echo esc_attr( $qsm_total_user_tries ); ?>">
			<?php esc_html_e( 'attempts or submissions can be done by a respondent', 'quiz-master-next'); ?>
			<label class="qsm-opt-desc" for="qsm-global-setting-total-user-tries"><?php esc_html_e( 'Set the limit to 0 or leave it blank to remove the limit on attempts.', 'quiz-master-next'); ?></label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<input class="small-text" type="number" id="qsm-global-setting-total-limit-entries" placeholder="Set Limit" step="1" min="0" name="qsm-quiz-settings[limit_total_entries]" value="<?php echo esc_attr( $qsm_limit_total_entries ); ?>">
			<?php esc_html_e( 'users can respond to this form type', 'quiz-master-next'); ?>
			<label class="qsm-opt-desc" for="qsm-global-setting-total-limit-entries"><?php esc_html_e( 'Set the limit to 0 or leave it blank to remove the limit on entries.', 'quiz-master-next'); ?></label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<input type="hidden" name="qsm-quiz-settings[enable_retake_quiz_button]"  value="0">
			<label class="qsm-option-label" for="qsm-enable-retake-quiz-button">
				<input type="checkbox" id="qsm-enable-retake-quiz-button" name="qsm-quiz-settings[enable_retake_quiz_button]" value="1" <?php checked( $qsm_enable_retake_quiz_button, 1 ); ?>>
				<?php esc_html_e( 'Allow users to retake the quiz', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
	}
	/**
	 * Generates Quiz Global  Field For Save Responses
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_save_responses() {
		global $globalQuizsetting;
		$qsm_store_responses = ( isset( $globalQuizsetting['store_responses'] ) && '' !== $globalQuizsetting['store_responses'] ? $globalQuizsetting['store_responses'] : '' );
		$qsm_send_email = ( isset( $globalQuizsetting['send_email'] ) && '' !== $globalQuizsetting['send_email'] ? $globalQuizsetting['send_email'] : '' );
		?>
		<fieldset class="buttonset buttonset-hide">
			<input type="hidden" name="qsm-quiz-settings[store_responses]"  value="0">
			<label for="store_responses">
				<input type="hidden" name="qsm-quiz-settings[store_responses]" value="0">
				<input type="checkbox" id="store_responses" name="qsm-quiz-settings[store_responses]"  value="1" <?php checked( $qsm_store_responses, 1 ); ?>>
				<?php esc_html_e('Store results permanently in database', 'quiz-master-next'); ?>
			</label>
			<input type="hidden" name="qsm-quiz-settings[send_email]"  value="0">
			<label for="send_email">
				<input type="checkbox" id="send_email" name="qsm-quiz-settings[send_email]" value="1" <?php checked( $qsm_send_email, 1 ); ?>>
				<?php esc_html_e('Send email notifications', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
	}
	/* ====== Submission Tab End ==========*/
	/* ====== Display Tab Start ==========*/
	/**
	 * Generates Quiz Global  Field For Show progress bar
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_show_progress_bar() {
		global $globalQuizsetting;
		$qsm_progress_bar = ( isset( $globalQuizsetting['progress_bar'] ) && '' !== $globalQuizsetting['progress_bar'] ? $globalQuizsetting['progress_bar'] : 0 );
		$qsm_enable_quick_result_mc = ( isset( $globalQuizsetting['enable_quick_result_mc'] ) && '' !== $globalQuizsetting['enable_quick_result_mc'] ? $globalQuizsetting['enable_quick_result_mc'] : 0 );
		$qsm_enable_quick_correct_answer_info = ( isset( $globalQuizsetting['enable_quick_correct_answer_info'] ) && '' !== $globalQuizsetting['enable_quick_correct_answer_info'] ? $globalQuizsetting['enable_quick_correct_answer_info'] : 0 );
		?>
		<fieldset class="buttonset buttonset-hide">
			<label class="qsm-option-label" for="progress_bar-1">
				<input type="checkbox" id="progress_bar-1" name="qsm-quiz-settings[progress_bar]" value="1" <?php checked( $qsm_progress_bar, 1 ); ?>>
				<?php esc_html_e('Show progress bar', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="enable_quick_result_mc">
			<label class="qsm-option-label" for="enable_quick_result_mc-1">
				<input type="checkbox" id="enable_quick_result_mc-1" name="qsm-quiz-settings[enable_quick_result_mc]" value="1" <?php checked( $qsm_enable_quick_result_mc, 1 ); ?>>
				<?php esc_html_e("Show the results of each question's response in real-time", 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="enable_quick_correct_answer_info">
			<?php esc_html_e('Display the correct answer information in real-time', 'quiz-master-next'); ?>
			<label class="qsm-option-label" for="enable_quick_correct_answer_info-2">
				<input type="radio" id="enable_quick_correct_answer_info-2" name="qsm-quiz-settings[enable_quick_correct_answer_info]" value="2" <?php checked( $qsm_enable_quick_correct_answer_info, 2 ); ?>>
				<?php esc_html_e('Always display', 'quiz-master-next'); ?>
			</label>
			<label class="qsm-option-label" for="enable_quick_correct_answer_info-0">
				<input type="radio" id="enable_quick_correct_answer_info-0" name="qsm-quiz-settings[enable_quick_correct_answer_info]" value="0" <?php checked( $qsm_enable_quick_correct_answer_info, 0 ); ?>>
				<?php esc_html_e("Never Display", 'quiz-master-next'); ?>
			</label>
			<label class="qsm-option-label" for="enable_quick_correct_answer_info-1">
				<input type="radio" id="enable_quick_correct_answer_info-1" name="qsm-quiz-settings[enable_quick_correct_answer_info]" value="1" <?php checked( $qsm_enable_quick_correct_answer_info, 1 ); ?>>
				<?php esc_html_e('Display only if the answer is correct', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
	}
	/**
	 * Generates Quiz Global  Field For Questions Per Page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_questions_per_page() {
		global $globalQuizsetting;
		$qsm_pagination = isset( $globalQuizsetting['pagination'] ) && '' !== $globalQuizsetting['pagination'] ? $globalQuizsetting['pagination'] : 0;
		$qsm_question_numbering = ( isset( $globalQuizsetting['question_numbering'] ) && '' !== $globalQuizsetting['question_numbering'] ? $globalQuizsetting['question_numbering'] : '0' );
		$qsm_show_category_on_front = ( isset( $globalQuizsetting['show_category_on_front'] ) && '' !== $globalQuizsetting['show_category_on_front'] ? $globalQuizsetting['show_category_on_front'] : '0' );
		?>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<?php esc_html_e('Show ', 'quiz-master-next'); ?>
			<input class="small-text" type="number" placeholder="<?php esc_html_e('Set Limit', 'quiz-master-next'); ?>" step="1" min="0" name="qsm-quiz-settings[pagination]" value="<?php echo esc_attr( $qsm_pagination ); ?>">
			<?php esc_html_e('Questions Per Page', 'quiz-master-next'); ?>
			<label class="qsm-opt-desc"><i><?php esc_html_e('Setting a limit overrides the quiz questions default pagination. Set it to 0 or blank for default pagination.', 'quiz-master-next'); ?></i></label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-question-numbering">
				<input type="checkbox" id="qsm-question-numbering" name="qsm-quiz-settings[question_numbering]" <?php checked( $qsm_question_numbering, 1 ); ?> value="1">
				<?php esc_html_e('Show question numbers', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-show-category-on-front">
				<input type="checkbox" id="qsm-show-category-on-front" name="qsm-quiz-settings[show_category_on_front]" <?php checked( $qsm_show_category_on_front, 1 ); ?> value="1">
				<?php esc_html_e('Display the category name next to each quiz question', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
	}
	/**
	 * Generates quiz global field to check if want to Result Page Controls
	 *
	 * @since 7.3.15
	 * @return void
	 */
	public function qsm_global_show_optin_answers() {
		global $globalQuizsetting;
		$qsm_question_show_optin_default = ( isset( $globalQuizsetting['show_optin'] ) && '' !== $globalQuizsetting['show_optin'] ? $globalQuizsetting['show_optin'] : 0 );
		$qsm_question_show_text_html_default = ( isset( $globalQuizsetting['show_text_html'] ) && '' !== $globalQuizsetting['show_text_html'] ? $globalQuizsetting['show_text_html'] : 0 );
		$qsm_hide_correct_answer = ( isset( $globalQuizsetting['hide_correct_answer'] ) && '' !== $globalQuizsetting['hide_correct_answer'] ? $globalQuizsetting['hide_correct_answer'] : 0 );
		$qsm_show_question_featured_image_in_result = ( isset( $globalQuizsetting['show_question_featured_image_in_result'] ) && '' !== $globalQuizsetting['show_question_featured_image_in_result'] ? $globalQuizsetting['show_question_featured_image_in_result'] : 0 );
		$qsm_disable_description_on_result = ( isset( $globalQuizsetting['disable_description_on_result'] ) && '' !== $globalQuizsetting['disable_description_on_result'] ? $globalQuizsetting['disable_description_on_result'] : '0' );
		?>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-show-optin">
				<input type="checkbox" id="qsm-show-optin" name="qsm-quiz-settings[show_optin]" <?php checked( $qsm_question_show_optin_default, 1 ); ?> value="1">
				<?php esc_html_e('Show responses to opt-in question type in results', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-show-text-html">
				<input type="checkbox" id="qsm-show-text-html" name="qsm-quiz-settings[show_text_html]" <?php checked( $qsm_question_show_text_html_default, 1 ); ?> value="1">
				<?php esc_html_e('Show Text/HTML Section in results', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-hide-correct-answer">
				<input type="checkbox" id="qsm-hide-correct-answer" name="qsm-quiz-settings[hide_correct_answer]" <?php checked( $qsm_hide_correct_answer, 1 ); ?> value="1">
				<?php esc_html_e('Hide correct answer in results if the user selected the incorrect answer', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-show-question-featured-image-in-result">
				<input type="checkbox" id="qsm-show-question-featured-image-in-result" name="qsm-quiz-settings[show_question_featured_image_in_result]" <?php checked( $qsm_show_question_featured_image_in_result, 1 ); ?> value="1">
				<?php esc_html_e('Display the featured image of the question on the results page', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-disable-description-on-result">
				<input type="checkbox" id="qsm-disable-description-on-result" name="qsm-quiz-settings[disable_description_on_result]" <?php checked( $qsm_disable_description_on_result, 1 ); ?> value="1">
				<?php esc_html_e('Disable description on quiz result page', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
	}
	/**
	 * Generates Quiz Global  Field For Quiz Page Settings
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_quiz_animation() {
		global $globalQuizsetting;
		global $mlwQuizMasterNext;
		$qsm_quiz_animation = ( isset( $globalQuizsetting['quiz_animation'] ) && '' !== $globalQuizsetting['quiz_animation'] ? $globalQuizsetting['quiz_animation'] : '' );
		$options            = $mlwQuizMasterNext->pluginHelper->quiz_animation_effect();
		$qsm_enable_pagination_quiz = ( isset( $globalQuizsetting['enable_pagination_quiz'] ) && '' !== $globalQuizsetting['enable_pagination_quiz'] ? $globalQuizsetting['enable_pagination_quiz'] : 0 );
		$qsm_disable_scroll_next_previous_click = ( isset( $globalQuizsetting['disable_scroll_next_previous_click'] ) && '' !== $globalQuizsetting['disable_scroll_next_previous_click'] ? $globalQuizsetting['disable_scroll_next_previous_click'] : 0 );
		?>
		<select name="qsm-quiz-settings[quiz_animation]">
		<?php foreach ( $options as $value ) { ?>
			<option value="<?php echo esc_attr( $value['value'] ); ?>" <?php selected( $qsm_quiz_animation, $value['value'] ); ?> ><?php echo esc_html( $value['label'] ); ?></option>
		<?php } ?>
		</select>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-enable-pagination-quiz">
				<input type="checkbox" id="qsm-enable-pagination-quiz" name="qsm-quiz-settings[enable_pagination_quiz]" <?php checked( $qsm_enable_pagination_quiz, 1 ); ?> value="1">
				<?php esc_html_e('Display current page number of the quiz', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-disable-scroll-next-previous-click">
				<input type="checkbox" id="qsm-disable-scroll-next-previous-click" name="qsm-quiz-settings[disable_scroll_next_previous_click]" <?php checked( $qsm_disable_scroll_next_previous_click, 1 ); ?> value="1">
				<?php esc_html_e('Do not scroll the page on clicking next/previous buttons', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
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
		$qsm_ajax_show_correct = ( isset( $globalQuizsetting['ajax_show_correct'] ) && '' !== $globalQuizsetting['ajax_show_correct'] ? $globalQuizsetting['ajax_show_correct'] : 0 );
		$preferred_date_format = ( isset( $globalQuizsetting['preferred_date_format'] ) ? $globalQuizsetting['preferred_date_format'] : get_option( 'date_format' ) );
		?>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<span><strong><?php esc_html_e('Set a logo for Facebook sharing', 'quiz-master-next'); ?></strong></span>
			<div class="qsm-image-field">
				<input type="text" class="qsm-image-input" name="qsm-quiz-settings[result_page_fb_image]" value="<?php echo esc_url( $qsm_result_page_fb_image ); ?>">
				<a class="qsm-image-btn button"><span class="dashicons dashicons-format-image"></span><?php esc_html_e('Select Logo', 'quiz-master-next'); ?></a>
			</div>
			<label class="qsm-font-light"><i><?php esc_html_e('This logo will be used for Facebook sharing. If left blank, QSM\'s logo will appear.', 'quiz-master-next'); ?></i></label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-ajax-show-correct">
				<input type="checkbox" id="qsm-ajax-show-correct" name="qsm-quiz-settings[ajax_show_correct]" <?php checked( $qsm_ajax_show_correct, 1 ); ?> value="1">
				<?php esc_html_e('Add class for correct/incorrect answers', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="preferred_date_format">
			<label class="qsm-option-label" for="qsm-preferred-date-format-1">
				<input type="radio" id="qsm-preferred-date-format-1" name="qsm-quiz-settings[preferred_date_format]" <?php checked( $preferred_date_format, 'F j, Y' ); ?> value="F j, Y">
				<span class="qsm-date-time-text"><?php esc_html_e('June 15, 2023', 'quiz-master-next'); ?> </span><code>F j, Y</code>
			</label>
			<label class="qsm-option-label" for="qsm-preferred-date-format-2">
				<input type="radio" id="qsm-preferred-date-format-2" name="qsm-quiz-settings[preferred_date_format]" <?php checked( $preferred_date_format, 'Y-m-d' ); ?> value="Y-m-d">
				<span class="qsm-date-time-text"><?php esc_html_e('2023-06-15', 'quiz-master-next'); ?> </span><code>Y-m-d</code>
			</label>
			<label class="qsm-option-label" for="qsm-preferred-date-format-3">
				<input type="radio" id="qsm-preferred-date-format-3" name="qsm-quiz-settings[preferred_date_format]" <?php checked( $preferred_date_format, 'm/d/Y' ); ?> value="m/d/Y">
				<span class="qsm-date-time-text"><?php esc_html_e('06/15/2023', 'quiz-master-next'); ?> </span><code>m/d/Y</code>
			</label>
			<label class="qsm-option-label" for="qsm-preferred-date-format-4">
				<input type="radio" id="qsm-preferred-date-format-4" name="qsm-quiz-settings[preferred_date_format]" <?php checked( $preferred_date_format, 'd/m/Y' ); ?> value="d/m/Y">
				<span class="qsm-date-time-text"><?php esc_html_e('15/06/2023', 'quiz-master-next'); ?> </span><code>d/m/Y</code>
			</label>
			<label class="qsm-option-label" for="preferred_date_format-custom">
				<input type="radio" id="preferred_date_format-custom" name="qsm-quiz-settings[preferred_date_format]" <?php echo ! in_array( $preferred_date_format, array( 'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' ), true ) ? 'checked' : ''; ?> value="<?php echo esc_attr( $preferred_date_format ); ?>">
				<span class="qsm-date-time-text"><?php esc_html_e('Custom', 'quiz-master-next'); ?></span>
				<input type="text" id="preferred-date-format-custom" value="<?php echo esc_attr( $preferred_date_format ); ?>">
			</label>
		</fieldset>
		<?php
	}
	/* ====== Display Tab End ==========*/
	/* ====== Contact Tab Start ==========*/
	/**
	 * Generates Quiz Global  Field For Contact form position
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function qsm_global_contact_form_position() {
		global $globalQuizsetting;
		$qsm_contact_info_location = ( isset( $globalQuizsetting['contact_info_location'] ) && '' !== $globalQuizsetting['contact_info_location'] ? $globalQuizsetting['contact_info_location'] : '0' );
		?>
		<fieldset class="buttonset buttonset-hide">
			<label for="qsm-contact-info-location-0">
				<input type="radio" id="qsm-contact-info-location-0" name="qsm-quiz-settings[contact_info_location]"  value="0" <?php checked( $qsm_contact_info_location, 0 ); ?>>
				<?php esc_html_e( 'Show before quiz begins', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-contact-info-location-1">
				<input type="radio" id="qsm-contact-info-location-1" name="qsm-quiz-settings[contact_info_location]" value="1" <?php checked( $qsm_contact_info_location, 1 ); ?>>
				<?php esc_html_e( 'Show after the quiz ends', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<span class="qsm-opt-desc"><?php esc_html_e( 'Select when to display the contact form', 'quiz-master-next'); ?></span>
		<?php
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
		?>
		<fieldset class="buttonset buttonset-hide">
			<label for="qsm-loggedin-user-contact-0">
				<input type="radio" id="qsm-loggedin-user-contact-0" name="qsm-quiz-settings[loggedin_user_contact]" value="0" <?php checked( $qsm_loggedin_user_contact, 0 ); ?>>
				<?php esc_html_e( 'Yes', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-loggedin-user-contact-1">
				<input type="radio" id="qsm-loggedin-user-contact-1" name="qsm-quiz-settings[loggedin_user_contact]" value="1" <?php checked( $qsm_loggedin_user_contact, 1 ); ?>>
				<?php esc_html_e( 'No', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<span class="qsm-opt-desc"><?php esc_html_e( 'Logged in users can edit their contact information', 'quiz-master-next'); ?></span>
		<?php
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
		?>
		<fieldset class="buttonset buttonset-hide" >
			<label for="qsm-contact-disable-autofill-1">
				<input type="radio" id="qsm-contact-disable-autofill-1" name="qsm-quiz-settings[contact_disable_autofill]" value="1" <?php checked( $qsm_contact_disable_autofill, 1 ); ?>>
				<?php esc_html_e( 'Yes', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-contact-disable-autofill-0">
				<input type="radio" id="qsm-contact-disable-autofill-0" name="qsm-quiz-settings[contact_disable_autofill]"  value="0" <?php checked( $qsm_contact_disable_autofill, 0 ); ?>>
				<?php esc_html_e( 'No', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
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
		?>
		<fieldset class="buttonset buttonset-hide">
			<label for="qsm-disable-first-page-1">
				<input type="radio" id="qsm-disable-first-page-1" name="qsm-quiz-settings[disable_first_page]" value="1" <?php checked( $qsm_disable_first_page, 1 ); ?>>
				<?php esc_html_e( 'Yes', 'quiz-master-next'); ?>
			</label>
			<label for="qsm-disable-first-page-0">
				<input type="radio" id="qsm-disable-first-page-0" name="qsm-quiz-settings[disable_first_page]"  value="0" <?php checked( $qsm_disable_first_page, 0 ); ?>>
				<?php esc_html_e( 'No', 'quiz-master-next'); ?>
			</label>
		</fieldset>
		<?php
	}
	/* ====== Contact Tab End ==========*/
}

$qmnGlobalSettingsPage = new QMNGlobalSettingsPage();
?>
