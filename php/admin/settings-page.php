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
	private const QSM_UPGRADE_BADGE_TEMPLATE = "<a href=%s target='_blank' class='qsm-upgrade-popup-badge'>%s</a>";

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
		wp_enqueue_script( 'wp-tinymce' );
		wp_enqueue_script( 'qmn_datetime_js', QSM_PLUGIN_JS_URL . '/jquery.datetimepicker.full.min.js', array(), $mlwQuizMasterNext->version, false );
		wp_enqueue_style( 'qsm_datetime_style', QSM_PLUGIN_CSS_URL . '/jquery.datetimepicker.css', array(), $mlwQuizMasterNext->version );
		add_action( 'admin_footer', array( $this, 'qsm_default_template_variables_popup' ) );
	}

	/**
	 * Displays template variables popup for default templates
	 *
	 * @since 10.3.5
	 */
	public function qsm_default_template_variables_popup() {
		?>
		<div class="qsm-popup qsm-popup-slide" id="show-all-variable" aria-hidden="false">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
				<div class="qsm-popup__container" aria-modal="true" aria-labelledby="modal-3-title">
					<header class="qsm-popup__header" style="display: block;">
						<h2 class="qsm-popup__title"><?php esc_html_e( 'Template Variables', 'quiz-master-next' ); ?></h2>
						<span class="description">
							<?php esc_html_e( 'Use these dynamic variables to customize your quiz or survey. Just copy and paste one or more variables into the content templates and these will be replaced by actual values when user takes a quiz.', 'quiz-master-next' ); ?>
							<br /><b><?php esc_html_e( 'Note: ', 'quiz-master-next' ); ?></b>
							<?php esc_html_e( 'Always use uppercase while using these variables.', 'quiz-master-next' ); ?>
						</span>
					</header>
					<main class="qsm-popup__content" id="show-all-variable-content">
						<?php
						$variable_list = qsm_text_template_variable_list();
						$variable_list = qsm_extra_template_and_leaderboard($variable_list);

						$variable_list = apply_filters( 'qsm_text_variable_list_default_template', $variable_list );
						$grouped_variables = $this->qsm_group_template_variables( $variable_list );
						if ( $grouped_variables ) {
							foreach ( $grouped_variables as $category_name => $category_variables ) {
								if ( ! is_array( $category_variables ) ) {
									continue;
								}
								$upgrade_meta = $this->qsm_default_template_variable_upgrade_meta( $category_name );
								$classname = $upgrade_meta['classname'];
								$qsm_badge = $upgrade_meta['badge'];
								$is_upgrade = '' != $classname;
								?>
								<div><h2 class="qsm-upgrade-popup-category-name"><?php echo esc_attr( $category_name );?></h2><?php echo wp_kses_post( $qsm_badge ); ?></div>
								<?php
								foreach ( $category_variables as $variable_key => $variable ) {
									?>
									<div class="popup-template-span-wrap">
										<span class="qsm-text-template-span <?php echo esc_attr( $classname ); ?>">
										<?php if ( $is_upgrade ) { ?>
											<span class="button button-default template-variable qsm-tooltips-icon"><?php echo esc_attr( $variable_key ); ?>
												<span class="qsm-tooltips qsm-upgrade-tooltip"><?php echo esc_html__( 'Available in pro', 'quiz-master-next' );?></span>
											</span>
										<?php } else { ?>
											<span class="button button-default template-variable"><?php echo esc_attr( $variable_key ); ?></span>
												<span class='button click-to-copy'><?php esc_html_e( 'Click to Copy', 'quiz-master-next' ); ?></span>
												<span class="temp-var-seperator">
													<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
														<span class="qsm-tooltips"><?php echo esc_attr( $variable ); ?></span>
														</span>
													</span>
											<?php } ?>
										</span>
									</div>
									<?php
								}
							}
							?>
						<?php
						}
						?>
					</main>
					<footer class="qsm-popup__footer" style="text-align: right;">
						<button class="button button-default" data-micromodal-close=""
							aria-label="Close this dialog window"><?php esc_html_e( 'Close [Esc]', 'quiz-master-next' ); ?></button>
					</footer>
				</div>
			</div>
		</div>
		<?php
	}

	private function qsm_group_template_variables( $variable_list ) {
		$grouped = array();
		if ( ! $variable_list ) {
			return $grouped;
		}
		foreach ( $variable_list as $name => $value ) {
			if ( is_array( $value ) ) {
				$grouped[ $name ] = $value;
				continue;
			}
			if ( ! isset( $grouped['Other Variables'] ) ) {
				$grouped['Other Variables'] = array();
			}
			$grouped['Other Variables'][ $name ] = $value;
		}
		return $grouped;
	}

	private function qsm_default_template_variable_upgrade_meta( $category_name ) {
		$meta = array(
			'classname' => '',
			'badge'     => '',
		);
		switch ( $category_name ) {
			case 'Extra Template Variables':
				$meta['classname'] = 'qsm-upgrade-popup-variable';
				$meta['badge'] = sprintf(
					self::QSM_UPGRADE_BADGE_TEMPLATE,
					qsm_get_plugin_link('extra-template-variables'),
					esc_html__( 'PRO', 'quiz-master-next' )
				);
				break;
			case 'Advanced Leaderboard':
				$meta['classname'] = 'qsm-upgrade-popup-variable';
				$meta['badge'] = sprintf(
					self::QSM_UPGRADE_BADGE_TEMPLATE,
					qsm_get_plugin_link('downloads/advanced-leaderboard/'),
					esc_html__( 'PRO', 'quiz-master-next' )
				);
				break;
			case 'Analysis':
				$meta['classname'] = 'qsm-upgrade-popup-variable';
				$meta['badge'] = sprintf(
					self::QSM_UPGRADE_BADGE_TEMPLATE,
					qsm_get_plugin_link('downloads/results-analysis/'),
					esc_html__( 'PRO', 'quiz-master-next' )
				);
				break;
			case 'Advanced Assessment':
				$meta['classname'] = 'qsm-upgrade-popup-variable qsm-upgrade-popup-advanced-assessment-variable';
				$meta['badge'] = sprintf(
					self::QSM_UPGRADE_BADGE_TEMPLATE,
					qsm_get_plugin_link( 'downloads/advanced-assessment/' ),
					esc_html__( 'PRO', 'quiz-master-next' )
				);
				break;
			default:
				break;
		}
		return $meta;
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
		add_settings_field( 'enable-new-render', __( 'Enable New Render', 'quiz-master-next' ), array( $this, 'enable_new_render' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'usage-tracker', __( 'Allow Usage Tracking?', 'quiz-master-next' ), array( $this, 'usage_tracker_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'enable-qsm-log', __( 'Enable QSM log', 'quiz-master-next' ), array( $this, 'enable_qsm_log' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'ip-collection', __( 'Disable collecting and storing IP addresses?', 'quiz-master-next' ), array( $this, 'ip_collection_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-search', __( 'Disable Quiz Posts From Being Searched?', 'quiz-master-next' ), array( $this, 'cpt_search_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'enable-preloader', __( 'Enable preloader', 'quiz-master-next' ), array( $this, 'qsm_enable_preloader' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-archive', __( 'Quiz Archive Settings', 'quiz-master-next' ), array( $this, 'cpt_archive_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'duplicate-quiz-with-theme', __( 'Duplicate Quiz Controls', 'quiz-master-next' ), array( $this, 'qsm_duplicate_quiz_with_theme' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'result-link-visibility', __( 'Result Link Visibility', 'quiz-master-next' ), array( $this, 'result_link_visibility_field' ), 'qmn_global_settings', 'qmn-global-section' );
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
		do_action('qsm_global_setting_field_after');
	}

	/**
	 * Generates Setting Field For Result Link Visibility
	 *
	 * @return void
	 */
	public function result_link_visibility_field() {
		$settings               = (array) get_option( 'qmn-settings' );
		$result_link_visibility = isset( $settings['result_link_visibility'] ) ? $settings['result_link_visibility'] : '0';
		?>
		<label class="qsm-checkbox-switch qsm-ui-gs-switch">
			<input type="checkbox" name="qmn-settings[result_link_visibility]" value="1" <?php checked( $result_link_visibility, '1' ); ?>>
			<span class="qsm-switch-slider round qsm-ui-gs-slider"></span>
		</label>
		<span class="global-sub-text"><?php esc_html_e( 'Restrict public access to results', 'quiz-master-next' ); ?></span>
		<?php
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
			<button type="button" class="qsm-ui-btn qsm-ui-btn-sm qsm-generate-api-key confirmation" ><?php esc_html_e('Regenerate Key', 'quiz-master-next'); ?></button>
		<?php } else { ?>
			<button type="button" class="qsm-ui-btn qsm-ui-btn-sm qsm-generate-api-key"><?php esc_html_e('Generate Key', 'quiz-master-next'); ?></button>
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
			'apply_pagination_to_manual_pages'       => 0,
			'timer_limit'                            => 0,
			'enable_result_after_timer_end'          => 0,
			'skip_validation_time_expire'            => 0,
			'total_user_tries'                       => 0,
			'limit_total_entries'                    => 0,
			'limit_email_based_submission'           => 0,
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
			'highlight_correct_answer'               => 1,
			'end_quiz_if_wrong'                      => 0,
			'enable_quick_correct_answer_info'       => 0,
			'show_question_featured_image_in_result' => 0,
			'enable_retake_quiz_button'              => 1,
			'enable_pagination_quiz'                 => 0,
			'enable_deselect_option'                 => 0,
			'disable_description_on_result'          => 0,
			'disable_scroll_next_previous_click'     => 0,
			'disable_scroll_on_result'               => 0,
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
			'enable-preloader'                       => 0,
			'enable_server_side_validation'          => 0,
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
		register_setting( 'qsm-quiz-default-template-group', 'qsm-quiz-default-template' );
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_general' );
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_quiz_submission' );
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_display' );
		add_settings_section( 'qmn-global-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_contact' );
		add_settings_section( 'qsm-default-template-section', '', array( $this, 'global_section' ), 'qsm_default_global_option_default_template' );
		add_settings_field( 'default-email-template', __( 'Default Email Template', 'quiz-master-next' ), array( $this, 'qsm_default_email_template' ), 'qsm_default_global_option_default_template', 'qsm-default-template-section' );
		add_settings_field( 'default-result-template', __( 'Default Result Template', 'quiz-master-next' ), array( $this, 'qsm_default_result_template' ), 'qsm_default_global_option_default_template', 'qsm-default-template-section' );
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
						printf( esc_html__( 'Please note that this action %1$s can not be %2$s rolled back. We recommend you to take a backup of your current site before proceeding.', 'quiz-master-next' ), '<b>', '</b>' );
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

		echo '<label class="qsm-checkbox-switch qsm-ui-gs-switch">';
			echo '<input type="checkbox" name="qmn-settings[cpt_search]" id="qmn-settings[cpt_search]" value="1"' . esc_attr( $checked ) . ' />';
		echo '<span class="qsm-switch-slider round qsm-ui-gs-slider"></span></label>';
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
		<label for="qmn-settings-duplicate_quiz_with_theme" class="qsm-checkbox-switch qsm-ui-gs-switch">
			<input type="checkbox" name="qmn-settings[duplicate_quiz_with_theme]" id="qmn-settings-duplicate_quiz_with_theme" aria-labelledby="qsm-duplicate-quiz-text" value="1" <?php checked( $duplicate_quiz_with_theme, 1, true ); ?> />
			<span class="qsm-switch-slider round qsm-ui-gs-slider"></span>
		</label>
		<span id="qsm-duplicate-quiz-text" class="global-sub-text"><?php esc_html_e( 'Enable quiz duplication along with theme settings', 'quiz-master-next'); ?></span>
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
		<p>
			<label for="qmn-settings-cpt_archive" class="qsm-checkbox-switch qsm-ui-gs-switch">
				<input type="checkbox" name="qmn-settings[cpt_archive]" id="qmn-settings-cpt_archive" aria-labelledby="qsm-cpt-archive-text" value="1" <?php checked( $cpt_archive, 1, true ); ?> />
				<span class="qsm-switch-slider round qsm-ui-gs-slider"></span>
			</label>
			<span id="qsm-cpt-archive-text" class='global-sub-text'><?php esc_html_e( 'Disable Quiz Archive', 'quiz-master-next'); ?></span>
		</p>
		<p>
			<label for="qmn-settings-qsm-quiz-public-link" class="qsm-checkbox-switch qsm-ui-gs-switch">
				<input type="checkbox" name="qmn-settings[disable_quiz_public_link]" id="qmn-settings-qsm-quiz-public-link" aria-labelledby="qsm-public-link-text" value="1" <?php echo checked( $cpt_link, 1, true ); ?> />
				<span class="qsm-switch-slider round qsm-ui-gs-slider"></span>
			</label>
			<span id="qsm-public-link-text" class='global-sub-text'><?php esc_html_e( 'Disable Quiz Public link', 'quiz-master-next'); ?></span>
		</p>
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

		echo '<label class="qsm-checkbox-switch qsm-ui-gs-switch">';
			echo '<input type="checkbox" name="qmn-settings[delete_qsm_data]" id="qmn-settings[delete_qsm_data]" value="1"' . esc_attr( $checked ) . '/>';
		echo '<span class="qsm-switch-slider round qsm-ui-gs-slider"></span></label>';
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

		echo '<label class="qsm-checkbox-switch qsm-ui-gs-switch">';
			echo '<input type="checkbox" name="qmn-settings[tracking_allowed]" id="qmn-settings[tracking_allowed]" value="2"' . esc_attr( $checked ) . '/><span class="qsm-switch-slider round qsm-ui-gs-slider"></span>';
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
		<label class="qsm-checkbox-switch qsm-ui-gs-switch">
			<input type="checkbox" name="qmn-settings[enable_qsm_log]" id="qmn-settings[enable_qsm_log]" value="1"' <?php checked( $enable_qsm_log, 1, true ); ?>/><span class="qsm-switch-slider round qsm-ui-gs-slider"></span>
		</label>
		<span class='global-sub-text' for='qmn-settings[enable_qsm_log]'><?php esc_html_e( "Enable this option to generate QSM error logs", 'quiz-master-next' );?></span>
		<?php
	}

	/**
	 * Generates Setting Field For QSM logs
	 *
	 * @since 8.1.9
	 * @return void
	 */
	public function enable_new_render() {
		$settings         = (array) get_option( 'qmn-settings' );
		$enable_new_render = ! empty( $settings['enable_new_render'] ) ? esc_attr( $settings['enable_new_render'] ) : 0;
		?>
		<label class="qsm-checkbox-switch qsm-ui-gs-switch">
			<input type="checkbox" name="qmn-settings[enable_new_render]" id="qmn-settings[enable_new_render]" value="1" <?php checked( $enable_new_render, 1, true ); ?>/><span class="qsm-switch-slider round qsm-ui-gs-slider"></span>
		</label>
		<span class='global-sub-text' for='qmn-settings[enable_new_render]'><?php esc_html_e( "Enable this option to use new render", 'quiz-master-next' );?></span>
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
		echo '<label class="qsm-checkbox-switch qsm-ui-gs-switch">';
		echo '<input type="checkbox" name="qmn-settings[ip_collection]" id="qmn-settings[ip_collection]" value="1"' . esc_attr( $checked ) . '/>';
		echo '<span class="qsm-switch-slider round qsm-ui-gs-slider"></span></label>';
		echo "<span class='global-sub-text' for='qmn-settings[ip_collection]'>" . esc_html__( 'You must not restrict number of quiz attempts when this option is enabled.', 'quiz-master-next' ) . '</span>';
	}

		/**
	 * Generates Setting Field For Preloader
	 *
	 * @since 5.3.0
	 * @return void
	 */
	public function qsm_enable_preloader() {
		$settings      = (array) get_option( 'qmn-settings' );
		$enable_preloader = '0';
		if ( isset( $settings['enable_preloader'] ) ) {
			$enable_preloader = esc_attr( $settings['enable_preloader'] );
		}
		$checked = '';
		if ( '1' == $enable_preloader ) {
			$checked = " checked='checked'";
		}
		echo '<label class="qsm-checkbox-switch qsm-ui-gs-switch">';
		echo '<input type="checkbox" name="qmn-settings[enable_preloader]" id="qmn-settings[enable_preloader]" value="1"' . esc_attr( $checked ) . '/>';
		echo '<span class="qsm-switch-slider round qsm-ui-gs-slider"></span></label>';
		echo "<span class='global-sub-text' for='qmn-settings[enable_preloader]'>" . esc_html__( 'Enable this option to preload featured images in the quiz', 'quiz-master-next' ) . '</span>';
	}

	/**
	 * Generates Settings Page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public static function display_page() {
		global $mlwQuizMasterNext;
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only routing.
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		// phpcs:enable
		if ( '' === $tab ) {
			$tab = 'qmn_global_settings';
		}
		$core_tabs   = array(
			'qmn_global_settings'   => array(
				'label' => __( 'Main Settings', 'quiz-master-next' ),
				'icon'  => 'settings',
			),
			'quiz-default-options'  => array(
				'label' => __( 'Quiz Default Options', 'quiz-master-next' ),
				'icon'  => 'quiz',
			),
			'quiz-default-template' => array(
				'label' => __( 'Quiz Default Template', 'quiz-master-next' ),
				'icon'  => 'mail',
			),
		);
		$is_core_tab = isset( $core_tabs[ $tab ] );

		// "Apply to multiple quizzes" posts back to this page.
		$applied_count = 0;
		if ( isset( $_POST['qsm-apply-global-settings-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm-apply-global-settings-nonce'] ) ), 'qsm-apply-global-settings-nonce' ) && ! empty( $_POST['qsm-select-quiz'] ) ) {
			$quizzes = qsm_sanitize_rec_array( wp_unslash( $_POST['qsm-select-quiz'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $quizzes as $quiz_id ) {
				$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
				$quiz_settings   = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'quiz_options' );
				$global_settings = QMNGlobalSettingsPage::get_global_quiz_settings();
				$global_settings = wp_parse_args( $global_settings, $quiz_settings );
				$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'quiz_options', $global_settings );
			}
			$applied_count = count( $quizzes );
		}
		$default_option_sections = array(
			'qsm_general'     => array(
				'label'       => __( 'General', 'quiz-master-next' ),
				'description' => __( 'Defaults for the quiz type, grading, answers and access.', 'quiz-master-next' ),
				'page'        => 'qsm_default_global_option_general',
			),
			'quiz_submission' => array(
				'label'       => __( 'Quiz submission', 'quiz-master-next' ),
				'description' => __( 'Timer, responses, attempts and what happens on submit.', 'quiz-master-next' ),
				'page'        => 'qsm_default_global_option_quiz_submission',
			),
			'display'         => array(
				'label'       => __( 'Display', 'quiz-master-next' ),
				'description' => __( 'Progress, question layout and the result page.', 'quiz-master-next' ),
				'page'        => 'qsm_default_global_option_display',
			),
			'contact_form'    => array(
				'label'       => __( 'Contact form', 'quiz-master-next' ),
				'description' => __( 'Where the contact form shows and who sees it.', 'quiz-master-next' ),
				'page'        => 'qsm_default_global_option_contact',
			),
		);
		?>
		<div class="wrap qsm-ui qsm-ui-gs-page">
			<div class="qsm-ui-page-header qsm-ui-text">
				<div>
					<h1 class="qsm-ui-h1"><?php esc_html_e( 'Global Settings', 'quiz-master-next' ); ?></h1>
					<p class="qsm-ui-subtitle"><?php esc_html_e( 'Site-wide settings, and the defaults every new quiz starts with.', 'quiz-master-next' ); ?></p>
				</div>
			</div>
			<hr class="wp-header-end">
			<nav class="qsm-ui-page-tabs-nav qsm-ui-gs-nav" aria-label="<?php esc_attr_e( 'Settings sections', 'quiz-master-next' ); ?>">
				<div class="qsm-ui-tabs qsm-ui-page-tabs qsm-ui-gs-tabs">
					<?php foreach ( $core_tabs as $slug => $core_tab ) { ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=qmn_global_settings&tab=' . $slug ) ); ?>" class="qsm-ui-tab"<?php echo $slug === $tab ? ' aria-current="page"' : ''; ?>>
							<?php echo QSM_Admin_UI::icon( $core_tab['icon'], 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
							<?php echo esc_html( $core_tab['label'] ); ?>
						</a>
					<?php } ?>
					<?php
					// Add-on tabs (e.g. QSM Ultimate's User Permissions) print their own nav-tab links; they sit in the same bar.
					ob_start();
					do_action( 'qsm_global_settings_page_add_tab_after' );
					$addon_tabs = trim( ob_get_clean() );
					if ( '' !== $addon_tabs ) {
						echo '<span class="qsm-ui-gs-addon-tabs">' . $addon_tabs . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- add-on markup, printed as before.
					}
					?>
				</div>
			</nav>
			<?php if ( 'quiz-default-options' === $tab ) { ?>
				<div class="qsm-ui-tabs qsm-ui-tabs-pill qsm-ui-gs-subtabs" role="tablist" aria-label="<?php esc_attr_e( 'Quiz Default Options', 'quiz-master-next' ); ?>">
					<?php
					$first = true;
					foreach ( $default_option_sections as $section_id => $section ) {
						// qsm-admin.js switches the panels on .quiz_style_tab (data-id = panel id, .current = selected).
						?>
						<a href="javascript:void(0)" role="tab" id="<?php echo esc_attr( 'qsm-gs-tab-' . $section_id ); ?>" aria-controls="<?php echo esc_attr( $section_id ); ?>" data-id="<?php echo esc_attr( $section_id ); ?>" class="qsm-ui-tab quiz_style_tab<?php echo $first ? ' current' : ''; ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>" tabindex="<?php echo $first ? '0' : '-1'; ?>"><?php echo esc_html( $section['label'] ); ?></a>
						<?php
						$first = false;
					}
					?>
				</div>
			<?php } ?>
				<div class="qsm-ui-gs-main">
					<?php if ( $applied_count ) { ?>
						<div class="qsm-ui-gs-notices">
							<?php
							/* translators: %s: number of quizzes */
							echo QSM_Admin_UI::notice( esc_html( sprintf( _n( '%s quiz has been updated!', '%s quizzes have been updated!', $applied_count, 'quiz-master-next' ), number_format_i18n( $applied_count ) ) ), array( 'type' => 'success' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
							?>
						</div>
					<?php } ?>
					<?php
					// Add-on tab content keeps the add-on's own markup.
					do_action( 'qsm_global_settings_page_added_tab_content' );
					?>
					<?php if ( $is_core_tab ) { ?>
						<form action="options.php" method="POST" class="qsm_global_settings qsm-ui-gs-form">
							<?php
							if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								flush_rewrite_rules( true );
								echo '<div class="qsm-ui-gs-notices">' . QSM_Admin_UI::notice( esc_html__( 'Settings have been updated!', 'quiz-master-next' ), array( 'type' => 'success', 'dismissible' => true ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
							}

							if ( 'qmn_global_settings' === $tab ) {
								settings_fields( 'qmn-settings-group' );
								self::render_kit_cards(
									'qmn_global_settings',
									array(
										array(
											'title'       => __( 'Quiz experience', 'quiz-master-next' ),
											'description' => __( 'How quizzes render and behave on your site.', 'quiz-master-next' ),
											'fields'      => array( 'enable-new-render', 'enable-preloader', 'duplicate-quiz-with-theme', 'items-per-page-question-bank', 'background-quiz-email-process' ),
										),
										array(
											'title'       => __( 'Privacy and data', 'quiz-master-next' ),
											'description' => __( 'What QSM collects, logs and keeps.', 'quiz-master-next' ),
											'fields'      => array( 'usage-tracker', 'enable-qsm-log', 'ip-collection', 'result-link-visibility', 'detele-qsm-data' ),
										),
										array(
											'title'       => __( 'Quiz posts and URLs', 'quiz-master-next' ),
											'description' => __( 'Archive, search, slug and sharing.', 'quiz-master-next' ),
											'fields'      => array( 'cpt-search', 'cpt-archive', 'cpt-slug', 'plural-name', 'facebook-app-id' ),
										),
										array(
											'title'       => __( 'Emails', 'quiz-master-next' ),
											'description' => __( 'The sender of the emails QSM sends.', 'quiz-master-next' ),
											'fields'      => array( 'from-name', 'from-email' ),
										),
										array(
											'title'       => __( 'Admin result details', 'quiz-master-next' ),
											'description' => __( 'The template of a result on the Results page.', 'quiz-master-next' ),
											'fields'      => array( 'new-template-result-detail', 'results-details' ),
										),
										array(
											'title'       => __( 'API', 'quiz-master-next' ),
											'description' => __( 'REST endpoints and the key that unlocks them.', 'quiz-master-next' ),
											'fields'      => array( 'api-key-options', 'api-key' ),
										),
									)
								);
							}
							if ( 'quiz-default-options' === $tab ) {
								settings_fields( 'qsm-quiz-settings-group' );
								$first = true;
								foreach ( $default_option_sections as $section_id => $section ) {
									?>
									<div id="<?php echo esc_attr( $section_id ); ?>" class="quiz_style_tab_content" role="tabpanel" aria-labelledby="<?php echo esc_attr( 'qsm-gs-tab-' . $section_id ); ?>"<?php echo $first ? '' : ' style="display:none"'; ?>>
										<?php
										self::render_kit_cards(
											$section['page'],
											array(
												array(
													'title'       => $section['label'],
													'description' => $section['description'],
													'fields'      => '*',
												),
											),
											$first
										);
										?>
									</div>
									<?php
									$first = false;
								}
							}
							if ( 'quiz-default-template' === $tab ) {
								settings_fields( 'qsm-quiz-default-template-group' );
								?>
								<div id="qsm_quiz_default_template" class="quiz_style_tab_content">
									<?php
									self::render_kit_cards(
										'qsm_default_global_option_default_template',
										array(
											array(
												'title'       => __( 'Default Template', 'quiz-master-next' ),
												'description' => __( 'The email and result page templates new quizzes start with.', 'quiz-master-next' ),
												'fields'      => '*',
												'stacked'     => true,
											),
										)
									);
									?>
								</div>
								<?php
							}
							?>
							<div class="option-page-option-tab-footer qsm-ui-gs-savebar">
								<p></p>
								<div class="qsm-ui-row">
									<a class="qsm-ui-btn qsm-btn-link-global-settings" id="qsm-apply-global-settings" href="javascript:void(0);"><?php esc_html_e( 'Apply to multiple quizzes', 'quiz-master-next' ); ?></a>
									<input type="submit" name="submit" id="submit" class="qsm-ui-btn qsm-ui-btn-primary" value="<?php esc_attr_e( 'Save Changes', 'quiz-master-next' ); ?>">
								</div>
							</div>
						</form>
					<?php } ?>
				</div>
		</div>
		<!-- set global setting popup start -->
		<div class="qsm-popup qsm-popup-slide qsm-standard-popup qsm-ui-popup" id="qsm-global-apply-default-popup" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<dialog open class="qsm-popup__container qsm-ui" aria-modal="true" aria-labelledby="modal-1-title">
					<form action="" method="POST" id="qsm-apply-global-settings-form">
						<header class="qsm-popup__header qsm-ui-text">
							<h2 class="qsm-popup__title" id="modal-1-title"><?php esc_html_e( 'Apply default settings to form', 'quiz-master-next' ); ?></h2>
							<a class="qsm-popup__close" href="javascript:void(0)" aria-label="<?php esc_attr_e( 'Close', 'quiz-master-next' ); ?>" data-micromodal-close></a>
						</header>
						<main class="qsm-popup__content qsm-ui-text" id="qsm-global-default-popup-content">
							<?php wp_nonce_field( 'qsm-apply-global-settings-nonce', 'qsm-apply-global-settings-nonce' ); ?>
							<?php
							$args    = array(
								'post_type'      => 'qsm_quiz',
								'posts_per_page' => -1,
								'post_status'    => 'publish',
							);
							$quizzes = get_posts( $args );
							?>
							<div class="qsm-ui-stack">
								<div>
									<label class="qsm-ui-field-label" for="qsm-select-quiz-apply"><?php esc_html_e( 'Apply the default settings to selected form type', 'quiz-master-next' ); ?></label>
									<div id="qsm-export-settings-options">
										<select name="qsm-select-quiz[]" multiple="multiple" id="qsm-select-quiz-apply" class="qsm-ui-select qsm-ui-gs-quiz-select" required>
											<?php if ( $quizzes ) : ?>
												<?php foreach ( $quizzes as $quiz ) : ?>
													<?php $quiz_id = get_post_meta( $quiz->ID, 'quiz_id', true ); ?>
													<option value="<?php echo esc_attr( $quiz_id ); ?>" id="<?php echo esc_attr( $quiz_id ); ?>"><?php echo esc_html( $quiz->post_title ); ?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
									</div>
								</div>
								<?php
								echo QSM_Admin_UI::notice( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
									esc_html__( 'Please note that this action is not reversible.', 'quiz-master-next' ),
									array(
										'type'  => 'danger',
										'title' => __( 'Do you want to continue and reset all the settings?', 'quiz-master-next' ),
									)
								);
								?>
							</div>
						</main>
						<footer class="qsm-popup__footer">
							<button type="button" class="qsm-ui-btn qsm-ui-foot-left" data-micromodal-close="" aria-label="<?php esc_attr_e( 'Close this dialog window', 'quiz-master-next' ); ?>"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
							<button class="qsm-ui-btn qsm-ui-btn-primary" type="submit" id="qsm-apply-global-default-btn"><?php esc_html_e( 'Apply', 'quiz-master-next' ); ?></button>
						</footer>
					</form>
				</dialog>
			</div>
		</div>
		<!-- set global setting popup end -->
		<?php
	}

	/**
	 * Prints a settings page's fields as kit cards with setting rows (L-05, L-06).
	 *
	 * Same callbacks, option names and saving as do_settings_sections(); only the frame
	 * around each field changes. Core's own fields get the kit look on their controls
	 * (.is-core); fields an add-on registers on the page keep their own control markup.
	 *
	 * @since 11.3.0
	 * @param string $page              Settings page slug.
	 * @param array  $groups            Cards: title, description, fields (field ids, or '*' for the rest), stacked (label above control).
	 * @param bool   $section_callbacks Print the sections' callbacks (the database update notice) above the cards.
	 * @return void
	 */
	private static function render_kit_cards( $page, $groups, $section_callbacks = true ) {
		global $wp_settings_sections;
		if ( empty( $wp_settings_sections[ $page ] ) ) {
			return;
		}
		list( $fields, $addon_sections ) = self::collect_kit_fields( $page, $section_callbacks );
		foreach ( self::group_kit_fields( $groups, $fields ) as $card ) {
			self::render_kit_card( $card[0], $card[1] );
		}
		foreach ( $addon_sections as $addon_section ) {
			self::render_addon_section_card( $addon_section[0], $addon_section[1] );
		}
	}

	/**
	 * Whether a settings section or field callback belongs to this page (Core) rather than an add-on.
	 *
	 * @since 11.3.0
	 * @param mixed $callback Section or field callback.
	 * @return bool
	 */
	private static function is_core_callback( $callback ) {
		return is_array( $callback ) && isset( $callback[0] ) && ( $callback[0] instanceof self || __CLASS__ === $callback[0] );
	}

	/**
	 * Fields of Core's sections, and add-on sections with their own fields.
	 *
	 * Core section callbacks (the database update notice) print above the cards. A section an
	 * add-on registers keeps its heading and intro, like do_settings_sections() prints them.
	 *
	 * @since 11.3.0
	 * @param string $page              Settings page slug.
	 * @param bool   $section_callbacks Print Core's section callbacks.
	 * @return array array( fields, addon sections as array( section, fields ) ).
	 */
	private static function collect_kit_fields( $page, $section_callbacks ) {
		global $wp_settings_sections, $wp_settings_fields;
		$fields         = array();
		$addon_sections = array();
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			$section_fields = empty( $wp_settings_fields[ $page ][ $section['id'] ] ) ? array() : array_values( (array) $wp_settings_fields[ $page ][ $section['id'] ] );
			if ( ! self::is_core_callback( isset( $section['callback'] ) ? $section['callback'] : null ) ) {
				$addon_sections[] = array( $section, $section_fields );
				continue;
			}
			if ( $section_callbacks ) {
				$section_html = self::section_intro( $section );
				if ( '' !== $section_html ) {
					echo '<div class="qsm-ui-gs-notices">' . $section_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the section callback escapes its own output.
				}
			}
			$fields = array_merge( $fields, $section_fields );
		}
		return array( $fields, $addon_sections );
	}

	/**
	 * Output of a section's callback.
	 *
	 * @since 11.3.0
	 * @param array $section Settings section.
	 * @return string
	 */
	private static function section_intro( $section ) {
		if ( empty( $section['callback'] ) || ! is_callable( $section['callback'] ) ) {
			return '';
		}
		ob_start();
		call_user_func( $section['callback'], $section );
		return trim( ob_get_clean() );
	}

	/**
	 * Sorts fields into the listed cards. Fields no card lists go to the '*' card, or to "More settings".
	 *
	 * @since 11.3.0
	 * @param array $groups Cards: title, description, fields, stacked.
	 * @param array $fields Settings fields.
	 * @return array array( group, rows ) per card.
	 */
	private static function group_kit_fields( $groups, $fields ) {
		$placed    = array();
		$cards     = array();
		$catch_all = array(
			'title'       => __( 'More settings', 'quiz-master-next' ),
			'description' => __( 'Settings added by your add-ons.', 'quiz-master-next' ),
		);
		foreach ( $groups as $group ) {
			if ( '*' === $group['fields'] ) {
				$catch_all = $group;
				continue;
			}
			$rows = array();
			foreach ( $fields as $field ) {
				if ( in_array( $field['id'], (array) $group['fields'], true ) ) {
					$rows[]                 = $field;
					$placed[ $field['id'] ] = true;
				}
			}
			$cards[] = array( $group, $rows );
		}
		$rest = array();
		foreach ( $fields as $field ) {
			if ( ! isset( $placed[ $field['id'] ] ) ) {
				$rest[] = $field;
			}
		}
		$cards[] = array( $catch_all, $rest );
		return $cards;
	}

	/**
	 * Prints one card of setting rows. Cards without rows are skipped.
	 *
	 * @since 11.3.0
	 * @param array $group Card: title, description, stacked.
	 * @param array $rows  Settings fields.
	 * @return void
	 */
	private static function render_kit_card( $group, $rows ) {
		if ( empty( $rows ) ) {
			return;
		}
		echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'title'       => isset( $group['title'] ) ? $group['title'] : '',
				'description' => isset( $group['description'] ) ? $group['description'] : '',
				'class'       => 'qsm-ui-gs-card qsm-ui-text',
			)
		);
		foreach ( $rows as $field ) {
			self::render_kit_row( $field, ! empty( $group['stacked'] ) );
		}
		echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	}

	/**
	 * Prints an add-on's settings section as its own card: its title, its intro, then its fields.
	 *
	 * @since 11.3.0
	 * @param array $section Settings section.
	 * @param array $rows    The section's fields.
	 * @return void
	 */
	private static function render_addon_section_card( $section, $rows ) {
		$intro = self::section_intro( $section );
		if ( empty( $rows ) && '' === $intro ) {
			return;
		}
		echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'title' => '' !== (string) $section['title'] ? wp_strip_all_tags( $section['title'] ) : __( 'More settings', 'quiz-master-next' ),
				'class' => 'qsm-ui-gs-card qsm-ui-text',
			)
		);
		if ( '' !== $intro ) {
			echo '<div class="qsm-ui-gs-section-intro">' . $intro . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the section callback escapes its own output.
		}
		foreach ( $rows as $field ) {
			self::render_kit_row( $field );
		}
		echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	}

	/**
	 * Prints one settings field as a kit setting row (L-06), like do_settings_fields() prints a <tr>.
	 *
	 * @since 11.3.0
	 * @param array $field   Entry of $wp_settings_fields.
	 * @param bool  $stacked Label above the control instead of beside it.
	 * @return void
	 */
	private static function render_kit_row( $field, $stacked = false ) {
		$args     = isset( $field['args'] ) ? (array) $field['args'] : array();
		$callback = isset( $field['callback'] ) ? $field['callback'] : null;
		$is_core  = is_array( $callback ) && isset( $callback[0] ) && ( $callback[0] instanceof self || __CLASS__ === $callback[0] );
		$classes  = array( 'qsm-ui-setting-row', 'qsm-ui-gs-row' );
		if ( $is_core && ! $stacked ) {
			$classes[] = 'is-core';
		}
		if ( $stacked ) {
			$classes[] = 'is-stacked';
		}
		if ( ! empty( $args['class'] ) ) {
			$classes[] = $args['class'];
		}
		$title_id = 'qsm-gs-title-' . sanitize_html_class( $field['id'] );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-qsm-setting="<?php echo esc_attr( $field['id'] ); ?>">
			<div class="qsm-ui-setting-label">
				<?php if ( ! empty( $args['label_for'] ) ) { ?>
					<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
				<?php } else { ?>
					<span id="<?php echo esc_attr( $title_id ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></span>
				<?php } ?>
			</div>
			<div class="qsm-ui-gs-control">
				<?php
				if ( is_callable( $callback ) ) {
					ob_start();
					call_user_func( $callback, $args );
					$control = ob_get_clean();
					echo empty( $args['label_for'] ) ? self::label_controls( $control, $title_id ) : $control; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the field callback escapes its own output.
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Points controls that have no label of their own at the row title, so screen readers announce them.
	 *
	 * A control counts as labelled when it sits inside a <label> with text, has aria-label(ledby),
	 * or another label in the row names its id. Switches (an empty label around the checkbox) are not.
	 *
	 * @since 11.3.0
	 * @param string $html     Field callback output.
	 * @param string $title_id Id of the row title.
	 * @return string
	 */
	private static function label_controls( $html, $title_id ) {
		if ( ! preg_match_all( '/<(input|select|textarea)\b[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE ) ) {
			return $html;
		}
		// Walk backwards so earlier offsets stay valid while tags grow.
		for ( $i = count( $matches[0] ) - 1; $i >= 0; $i-- ) {
			$tag    = $matches[0][ $i ][0];
			$offset = $matches[0][ $i ][1];
			if ( self::control_has_label( $tag, strtolower( $matches[1][ $i ][0] ), $html, $offset ) ) {
				continue;
			}
			$labelled = preg_replace( '/\s*\/?>$/', ' aria-labelledby="' . esc_attr( $title_id ) . '"$0', $tag, 1 );
			$html     = substr_replace( $html, $labelled, $offset, strlen( $tag ) );
		}
		return $html;
	}

	/**
	 * Whether a control in a field's output already has a label (or needs none).
	 *
	 * @since 11.3.0
	 * @param string $tag     The control's opening tag.
	 * @param string $element input, select or textarea.
	 * @param string $html    The field's whole output.
	 * @param int    $offset  Where the tag starts in $html.
	 * @return bool
	 */
	private static function control_has_label( $tag, $element, $html, $offset ) {
		$has_aria    = (bool) preg_match( '/\baria-label(ledby)?\s*=/i', $tag );
		$not_a_field = 'input' === $element && preg_match( '/\btype\s*=\s*["\']?(hidden|submit|button|reset|image)\b/i', $tag );
		$has_for     = preg_match( '/\bid\s*=\s*["\']([^"\']+)["\']/i', $tag, $id ) && false !== strpos( $html, 'for="' . $id[1] . '"' );
		return $has_aria || $not_a_field || $has_for || self::inside_text_label( $html, $offset );
	}

	/**
	 * Whether the position sits inside a <label> that has text. A switch (empty label) does not count.
	 *
	 * @since 11.3.0
	 * @param string $html   Field output.
	 * @param int    $offset Position of the control.
	 * @return bool
	 */
	private static function inside_text_label( $html, $offset ) {
		$before = substr( $html, 0, $offset );
		$open   = strripos( $before, '<label' );
		if ( false === $open || $open < (int) strripos( $before, '</label>' ) ) {
			return false;
		}
		$close = stripos( $html, '</label>', $offset );
		return false !== $close && '' !== trim( wp_strip_all_tags( substr( $html, $open, $close - $open ) ) );
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
			<div class="qsm-mb-1 qsm-ui-gs-caption"><?php esc_html_e( 'Correct Answer Logic', 'quiz-master-next' ); ?>:
				<br/><small><?php
				/* translators: %s: HTML tag */
				printf( esc_html__( 'Works with %1$sMultiple Response, Horizontal Multiple Response%2$s and %3$sFill in the Blanks%4$s Question Types.', 'quiz-master-next' ), '<b>', '</b>', '<b>', '</b>' ); ?></small>
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
			<div class="qsm-mb-1 qsm-ui-gs-caption"><?php esc_html_e( 'Other Answer Settings', 'quiz-master-next' ); ?>:</div>
			<label class="qsm-option-label" for="qsm-enable-deselect-option">
				<input type="checkbox" id="qsm-enable-deselect-option" name="qsm-quiz-settings[enable_deselect_option]" value="1" <?php checked( $qsm_enable_deselect_option, 1 ); ?>>
				<?php esc_html_e( 'Allow user to deselect an answer and leave it blank.', 'quiz-master-next' ); ?>
				<i class="qsm-font-light qsm-ui-gs-note">(<?php esc_html_e( 'Works with multiple choice & horizontal multiple choice questions only', 'quiz-master-next' ); ?>)</i>
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
			<input autocomplete="off" class="qsm-date-picker qsm-ui-gs-date" type="text" placeholder="<?php esc_attr_e( 'Start Date', 'quiz-master-next'); ?> " aria-label="<?php esc_attr_e( 'Start Date', 'quiz-master-next' ); ?>" id="qsm-scheduled-time-start-input" name="qsm-quiz-settings[scheduled_time_start]" value="<?php echo esc_attr($qsm_scheduled_time_start); ?>">
			<input autocomplete="off" class="qsm-date-picker qsm-ui-gs-date" type="text" placeholder="<?php esc_attr_e( 'End Date', 'quiz-master-next'); ?>" aria-label="<?php esc_attr_e( 'End Date', 'quiz-master-next' ); ?>" id="scheduled_time_end-input" name="qsm-quiz-settings[scheduled_time_end]" value="<?php echo esc_attr($qsm_scheduled_time_end); ?>">
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="not_allow_after_expired_time">
			<label class="qsm-option-label" for="not_allow_after_expired_time-1">
				<input type="checkbox" id="not_allow_after_expired_time-1" name="qsm-quiz-settings[not_allow_after_expired_time]" value="1" aria-describedby="not_allow_after_expired_time-hint" <?php checked( $qsm_not_allow_after_expired_time, 1 ); ?> >
				<?php esc_html_e( 'Do not allow quiz submission after the end date/time', 'quiz-master-next'); ?>
			</label>
			<?php // qsm-admin.js shows this while the checkbox is disabled (no end date yet). ?>
			<span id="not_allow_after_expired_time-hint" class="qsm-ui-gs-help"><?php esc_html_e( 'Set an end date to turn this on.', 'quiz-master-next' ); ?></span>
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
			<input class="small-text" type="number" step="1" min="0" id="qsm-question-from-total-input" name="qsm-quiz-settings[question_from_total]" value="<?php echo esc_attr( $qsm_question_from_total ); ?>" aria-labelledby="qsm-question-from-total-label">
			<span id="qsm-question-from-total-label"><?php esc_html_e( 'Maximum question limit', 'quiz-master-next'); ?></span>
		</fieldset>
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="qsm-question-per-category">
			<input class="small-text" type="number" step="1" min="0" id="qsm-question-per-category-input" name="qsm-quiz-settings[question_per_category]" value="<?php echo esc_attr( $qsm_question_per_category ); ?>" aria-labelledby="qsm-question-per-category-label">
				<span class="qsm-opt-tr">
					<span id="qsm-question-per-category-label"><?php esc_html_e( 'Limit number of questions per category', 'quiz-master-next'); ?></span>
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
			<span class="qsm-opt-desc qsm-ui-gs-help"><?php esc_html_e( 'Set it to 0 or blank to remove the time restriction.', 'quiz-master-next' ); ?></span>
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
			<span class="qsm-opt-desc qsm-ui-gs-help">
				<?php esc_html_e( 'Set it to 0 or blank to remove the Incorrect answers limit', 'quiz-master-next'); ?>
			</span>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<label class="qsm-option-label" for="qsm-disable-answer-onselect">
				<input type="checkbox" id="qsm-disable-answer-onselect" name="qsm-quiz-settings[disable_answer_onselect]" value="1" <?php checked( $qsm_disable_answer_onselect, 1 ); ?>>
				<?php esc_html_e( 'Prevent users from changing their response.', 'quiz-master-next'); ?>
				<i class="qsm-font-light qsm-ui-gs-note">(<?php esc_html_e( 'Works with multiple choice questions only', 'quiz-master-next'); ?>)</i>
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
			<label class="qsm-opt-desc qsm-ui-gs-help" for="qsm-global-setting-total-user-tries"><?php esc_html_e( 'Set the limit to 0 or leave it blank to remove the limit on attempts.', 'quiz-master-next'); ?></label>
		</fieldset>
		<fieldset class="buttonset buttonset-hide">
			<input class="small-text" type="number" id="qsm-global-setting-total-limit-entries" placeholder="Set Limit" step="1" min="0" name="qsm-quiz-settings[limit_total_entries]" value="<?php echo esc_attr( $qsm_limit_total_entries ); ?>">
			<?php esc_html_e( 'users can respond to this form type', 'quiz-master-next'); ?>
			<label class="qsm-opt-desc qsm-ui-gs-help" for="qsm-global-setting-total-limit-entries"><?php esc_html_e( 'Set the limit to 0 or leave it blank to remove the limit on entries.', 'quiz-master-next'); ?></label>
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
		$qsm_highlight_correct_answer = ( isset( $globalQuizsetting['highlight_correct_answer'] ) && '' !== $globalQuizsetting['highlight_correct_answer'] ? $globalQuizsetting['highlight_correct_answer'] : 1 );
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
		<fieldset class="buttonset buttonset-hide" data-hide="1" id="highlight_correct_answer">
			<input type="hidden" name="qsm-quiz-settings[highlight_correct_answer]" value="0">
			<label class="qsm-option-label" for="highlight_correct_answer-1">
				<input type="checkbox" id="highlight_correct_answer-1" name="qsm-quiz-settings[highlight_correct_answer]" value="1" <?php checked( $qsm_highlight_correct_answer, 1 ); ?>>
				<?php esc_html_e( 'Highlight the correct answer when the response is wrong', 'quiz-master-next' ); ?>
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
			<span class="qsm-opt-desc qsm-ui-gs-help"><i><?php esc_html_e('Setting a limit overrides the quiz questions default pagination. Set it to 0 or blank for default pagination.', 'quiz-master-next'); ?></i></span>
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
		$qsm_disable_scroll_on_result = ( isset( $globalQuizsetting['disable_scroll_on_result'] ) && '' !== $globalQuizsetting['disable_scroll_on_result'] ? $globalQuizsetting['disable_scroll_on_result'] : '0' );
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
		<fieldset class="buttonset buttonset-hide" data-hide="1">
			<label class="qsm-option-label" for="qsm-disable-scroll-on-result">
				<input type="checkbox" id="qsm-disable-scroll-on-result" name="qsm-quiz-settings[disable_scroll_on_result]" <?php checked( $qsm_disable_scroll_on_result, 1 ); ?> value="1">
				<?php esc_html_e('Disable scrolling while submitting quizzes and displaying results', 'quiz-master-next'); ?>
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
				<a class="qsm-image-btn qsm-ui-btn"><span class="dashicons dashicons-format-image"></span><?php esc_html_e('Select Logo', 'quiz-master-next'); ?></a>
			</div>
			<span class="qsm-font-light qsm-ui-gs-note"><i><?php esc_html_e('This logo will be used for Facebook sharing. If left blank, QSM\'s logo will appear.', 'quiz-master-next'); ?></i></span>
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
		<span class="qsm-opt-desc qsm-ui-gs-help"><?php esc_html_e( 'Select when to display the contact form', 'quiz-master-next'); ?></span>
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
		<span class="qsm-opt-desc qsm-ui-gs-help"><?php esc_html_e( 'Logged in users can edit their contact information', 'quiz-master-next'); ?></span>
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

	/* ====== Default Template Tab Start ==========*/
	/**
	 * Private helper method to generate default template fields for email or result.
	 *
	 * @param string $type The type of template ('email' or 'result').
	 */
	private function qsm_default_template( $type ) {
		global $wpdb;
		$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
		$user_id = get_current_user_id();

		$default_text = ( 'email' === $type )
			? '%QUESTIONS_ANSWERS_EMAIL%'
			: __( 'Thanks for submitting your response! Here are your quiz results. <br>%QUESTIONS_ANSWERS%', 'quiz-master-next' );
		$settings = (array) get_option( 'qsm-quiz-default-template' );
		$template = isset( $settings[ 'default_' . $type . '_template' ] ) ? htmlspecialchars_decode( $settings[ 'default_' . $type . '_template' ], ENT_QUOTES ) : $default_text;

		$template_from_script = qsm_get_parsing_script_data( 'templates.json' );
		$filter = ( 'email' === $type ) ? 'qsm_email_templates_list_before' : 'qsm_result_templates_list_before';
		$template_from_script = apply_filters( $filter, $template_from_script, $quiz_id );

		$table_name = $wpdb->prefix . 'mlw_quiz_output_templates';
		$template_sql = "SELECT * FROM {$table_name} WHERE template_type='global_{$type}'";
		$my_templates = $wpdb->get_results( $template_sql );

		$qsm_dependency_list = qsm_get_dependency_plugin_list();

		$js_data = array(
			'quizID'                         => $quiz_id,
			'nonce'                          => wp_create_nonce( 'wp_rest' ),
			'qsm_user_ve'                    => get_user_meta( $user_id, 'rich_editing', true ),
			'rest_user_nonce'                => wp_create_nonce( 'wp_rest_nonce_' . $quiz_id . '_' . $user_id ),
			'my_tmpl_data'                   => $my_templates,
			'script_tmpl'                    => $template_from_script,
			'add_tmpl_nonce'                 => wp_create_nonce( 'qsm_add_template' ),
			'remove_tmpl_nonce'              => wp_create_nonce( 'qsm_remove_template' ),
			'dependency'                     => $qsm_dependency_list,
			'required_addons'                => __( 'Required Add-ons', 'quiz-master-next' ),
			'used_addons'                    => __( 'Addons :', 'quiz-master-next' ),
			'default_' . $type . '_template' => $template,
		);

		$object_name = ( 'email' === $type ) ? 'qsmDefaultEmailsObject' : 'qsmDefaultResultsObject';
		wp_localize_script( 'qsm_admin_js', $object_name, $js_data );
		?>
		<header class="qsm-default-template-header">
			<div class="qsm-template-btn-group">
				<div class="qsm-actions-link-box">
					<a href="javascript:void(0)" data-template-type="global_<?php echo esc_attr( $type ); ?>" class="qsm-insert-page-template-anchor" title="<?php esc_attr_e( 'Add Template', 'quiz-master-next' ); ?>" >
						<div class="qsm-insert-template-wrap">
							<div class="qsm-insert-template-options">
								<label>
									<input type="radio" name="qsm-template-action" value="new" class="qsm-insert-template-action" checked="checked">
									<?php esc_html_e( 'New', 'quiz-master-next' ); ?>
								</label>
								<label>
									<input type="radio" name="qsm-template-action" value="replace" class="qsm-insert-template-action">
									<?php esc_html_e( 'Replace', 'quiz-master-next' ); ?>
								</label>
							</div>
							<div class="qsm-insert-template-container">
								<div class="qsm-insert-template-left">
									<input placeholder="<?php esc_attr_e( 'Type Template name here ', 'quiz-master-next' ); ?>" type="text"  id="qsm-insert-page-template-title-<?php echo esc_attr( $type ); ?>" class="qsm-insert-page-template-title">
									<div style="display: none;" class="qsm-to-replace-page-template-wrap">
										<select class="qsm-to-replace-page-template"></select>
									</div>
									<p class="qsm-insert-template-response"></p>
								</div>
								<div class="qsm-insert-template-right">
									<button data-id="global_<?php echo esc_attr( $type ); ?>" data-context="default" class="qsm-save-page-template-button button"><?php esc_html_e( 'Save', 'quiz-master-next' ); ?></button>
								</div>
							</div>
						</div>
						<img class="qsm-common-svg-image-class " src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/save-3-line.svg'); ?>" alt="save-3-line.svg"/>
					</a>
					<a href="javascript:void(0)" data-type="global_<?php echo esc_attr( $type ); ?>" class="qsm-view-templates-list" title="<?php esc_attr_e( 'Change Template', 'quiz-master-next' ); ?>" >
						<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/refresh-line.svg'); ?>" alt="refresh-line.svg"/>
					</a>
				</div>
			<div>
		</header>
		<?php

		wp_editor(
			$template,
			'default_' . $type . '_template',
			array(
				'textarea_name' => 'qsm-quiz-default-template[default_' . $type . '_template]',
				'textarea_rows' => 15,
				'media_buttons' => true,
				'tinymce'       => array(
					'plugins'  => 'qsmslashcommands link image lists charmap colorpicker textcolor hr fullscreen wordpress',
					'toolbar1' => 'formatselect,bold,italic,underline,|,bullist,numlist,|,link,image,|,qsmslashcommands,|,fullscreen',
					'toolbar2' => '',
				),
				'quicktags'     => true,
			)
		);

		?>
		<div class="qsm-default-template-footer-buttons">
			<a class="button-secondary qsm-show-all-variable-text qsm-common-button-styles" href="javascript:void(0)"><?php esc_html_e( 'Insert Template Variables', 'quiz-master-next' ); ?></a>
			<span class="qsm-insert-template-variable-text">Or, Type /  to insert template variables</span>
		</div>
		<?php
		qsm_result_and_email_popups_for_templates( $template_from_script, $my_templates, $type );
		qsm_result_and_email_row_templates();
	}

	/**
	 * Generates Setting Field For Default Email Template
	 */
	public function qsm_default_email_template() {
		$this->qsm_default_template( 'email' );
	}

	/**
	 * Generates Setting Field For Default Result Template
	 */
	public function qsm_default_result_template() {
		$this->qsm_default_template( 'result' );
	}
	/* ====== Default Template Tab End ==========*/
}

$qmnGlobalSettingsPage = new QMNGlobalSettingsPage();
?>
