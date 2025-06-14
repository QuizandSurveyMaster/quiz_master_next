<?php
/**
 * This file handles the "Questions" tab when editing a quiz/survey
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Adds the settings for questions tab to the Quiz Settings page.
 *
 * @return void
 * @since  4.4.0
 */
function qsm_settings_questions_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Questions', 'quiz-master-next' ), 'qsm_options_questions_tab_content', 'questions' );
}
add_action( 'init', 'qsm_settings_questions_tab', 5 );

/**
 * Adds the content for the options for questions tab.
 *
 * @return void
 * @since  4.4.0
 */
function qsm_options_questions_tab_content() {
	global $wpdb, $mlwQuizMasterNext;
	$quiz_data           = $wpdb->get_results( "SELECT quiz_id, quiz_name	FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted=0 ORDER BY quiz_id DESC" );
	$question_categories = $wpdb->get_results( "SELECT DISTINCT category FROM {$wpdb->prefix}mlw_questions", 'ARRAY_A' );
	$enabled             = get_option( 'qsm_multiple_category_enabled' );

	if ( $enabled && 'cancelled' !== $enabled ) {
		$question_categories = array();
		$terms               = get_terms(
			array(
				'taxonomy'   => 'qsm_category',
				'hide_empty' => false,
			)
		);
		foreach ( $terms as $term ) {
			$question_categories[] = array(
				'category' => $term->name,
				'cat_id'   => $term->term_id,
			);
		}
	}
	$quiz_id         = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$user_id         = get_current_user_id();
	$form_type       = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'form_type' );
	$quiz_system     = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'system' );
	$default_answers = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'default_answers' );
	$pages           = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array(), 'admin' );
	$db_qpages       = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'qpages', array(), 'admin' );
	$qpages          = array();
	if ( ! empty( $pages ) ) {
		$defaultQPage = array(
			'id'           => 1,
			'quizID'       => $quiz_id,
			'pagekey'      => '',
			'hide_prevbtn' => 0,
			'questions'    => array(),
		);
		foreach ( $pages as $k => $val ) {
			$qpage                   = isset( $db_qpages[ $k ] ) ? $db_qpages[ $k ] : $defaultQPage;
			$qpage['id']             = $k + 1;
			$qpage['pagekey']        = ( isset( $qpage['pagekey'] ) && ! empty( $qpage['pagekey'] ) ) ? $qpage['pagekey'] : uniqid();
			$qpage['hide_prevbtn']   = ( isset( $qpage['hide_prevbtn'] ) && ! empty( $qpage['hide_prevbtn'] ) ) ? $qpage['hide_prevbtn'] : 0;
			$pages[ $k ]           = array_values( $val );
			$qpage['questions']      = array_values( $val );
			$qpages[]                = $qpage;
		}
	} else {
		$defaultQPage    = array(
			'id'           => 1,
			'quizID'       => $quiz_id,
			'pagekey'      => uniqid(),
			'hide_prevbtn' => 0,
			'questions'    => array(),
		);
		$qpages[]        = $defaultQPage;
	}
	$qpages      = apply_filters( 'qsm_filter_quiz_page_attributes', $qpages, $pages );
	$json_data   = array(
		'quizID'                => $quiz_id,
		'answerText'            => __( 'Answer', 'quiz-master-next' ),
		'linked_view'           => __( 'View', 'quiz-master-next' ),
		'linked_close'          => __( 'Close', 'quiz-master-next' ),
		'nonce'                 => wp_create_nonce( 'wp_rest' ),
		'pages'                 => $pages,
		'qpages'                => $qpages,
		'qsm_user_ve'           => get_user_meta( $user_id, 'rich_editing', true ),
		'saveNonce'             => wp_create_nonce( 'ajax-nonce-sandy-page' ),
		'unlinkNonce'           => wp_create_nonce( 'ajax-nonce-unlink-question' ),
		'categories'            => $question_categories,
		'form_type'             => $form_type,
		'quiz_system'           => $quiz_system,
		'question_bank_nonce'   => wp_create_nonce( 'delete_question_question_bank_nonce' ),
		'single_question_nonce' => wp_create_nonce( 'delete_question_from_database' ),
		'rest_user_nonce'       => wp_create_nonce( 'wp_rest_nonce_' . $quiz_id . '_' . get_current_user_id() ),
		'default_answers'       => $default_answers,
	);
	wp_localize_script( 'qsm_admin_js', 'qsmQuestionSettings', $json_data );

	// Load Question Types.
	$question_types              = $mlwQuizMasterNext->pluginHelper->get_question_type_options();
	$question_types_categorized  = $mlwQuizMasterNext->pluginHelper->categorize_question_types();

	// Display warning if using competing options.
	$pagination = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'pagination' );
	if ( 0 != $pagination ) {
		?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'This quiz has the "How many questions per page would you like?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to 0.', 'quiz-master-next' ); ?></p>
		</div>
		<?php
	}
	$from_total = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'question_from_total' );
	if ( 0 != $from_total ) {
		?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'This quiz has the "How many questions should be loaded for quiz?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to 0.', 'quiz-master-next' ); ?></p>
		</div>
		<?php
	}

	$question_ids = $mlwQuizMasterNext->pluginHelper->get_questions_ids( $quiz_id );
	if ( ! empty( $question_ids ) ) {
		/**
		 * Check for invalid Questions.
		 */
		$q_types         = array();
		$invalid_types   = array();
		$question_types_new  = $wpdb->get_results( "SELECT `question_type_new` as type FROM `{$wpdb->prefix}mlw_questions` WHERE `question_id` IN (" . implode( ',', $question_ids ) . ")" );
		if ( ! empty( $question_types_new ) ) {
			foreach ( $question_types_new as $data ) {
				$q_types[] = $data->type;
			}
		}
		if ( ! class_exists( 'QSM_Advance_Question' ) ) {
			$invalid_types[] = 15;
			$invalid_types[] = 16;
			$invalid_types[] = 17;
		}
		if ( ! class_exists( 'QSM_Flashcards' ) ) {
			$invalid_types[] = 18;
		}
		if ( ! empty( array_intersect( $invalid_types, $q_types ) ) ) {
			?>
			<div class="notice notice-error notice-invalid-question-type">
				<p><?php esc_html_e( 'This quiz contains advance question types which will be skipped on quiz page as there are no active add-ons to support these questions.', 'quiz-master-next' ); ?></p>
				<p><?php esc_html_e( 'Please reactivate the related add-ons to make sure the quiz works as expected.', 'quiz-master-next' ); ?></p>
			</div>
			<?php
		}
	}
	$read_only = "";
	$disable_class = "";
	$background = "#FFFFFF";
	$display_advance = "yes";
	if ( ! class_exists ( 'QSM_AdvancedTimer' ) ) {
		$read_only = 'readonly';
		$disable_class = 'qsm-disabled-td';
		$background = "#F0F0F0";
	} else {
		$date_now = gmdate("d-m-Y");
		$settings_data   = get_option('qsm_addon_advanced_timer_settings', '');
		$license         = isset( $settings_data['license_key'] ) ? trim( $settings_data['license_key'] ) : '';
		if ( version_compare( $mlwQuizMasterNext->version, '8.1.6', '<' ) && (isset($settings_data['expiry_date']) && "" != $settings_data['expiry_date']) && (isset($settings_data['last_validate']) && "" != $settings_data['last_validate']) && strtotime($date_now) > strtotime($settings_data['expiry_date']) && strtotime($date_now) >= strtotime($settings_data['last_validate']) ) {
			$item_url = 'https://quizandsurveymaster.com/checkout/?edd_license_key='.$license.'&download_id=109654';
			$target_text = __('License Key Expired. ', 'quiz-master-next');
			$target_link = sprintf( '<div class="notice notice-error notice-advance-timer"><strong>'.__('Error! ', 'quiz-master-next').'</strong>'.$target_text.'<a style="font-weight: bolder;" href="%s" target="_blank">%s</a></div>', esc_url( $item_url ), __( 'Click here to renew', 'quiz-master-next' ) );
			echo wp_kses_post($target_link);
		}
		if ( ( isset($settings_data['last_validate']) && "invalid" == $settings_data['last_validate'] ) || empty($settings_data) ) {
			$read_only = 'readonly';
			$disable_class = 'qsm-disabled-td';
			$background = "#F0F0F0";
			$admin_page_url = esc_url(admin_url('admin.php?page=qmn_addons&tab=advanced-timer'));
			$error_message = sprintf(
				'<div class="notice notice-error notice-advance-timer">
					<strong>%s</strong> %s <a href="%s" target="_blank">%s</a> %s
				</div>',
				__('Error! ', 'quiz-master-next'),
				__('Your Advanced Timer Settings are not saved successfully. ', 'quiz-master-next'),
				$admin_page_url,
				__('Validate license', 'quiz-master-next'),
				__('to save the settings. ', 'quiz-master-next')
			);
			echo wp_kses_post($error_message);
		}
		$advancetimer = 'qsm-advanced-timer/qsm-advanced-timer.php';
		$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/'. $advancetimer);
		$version_number = $plugin_data['Version'];
		if ( version_compare($version_number, '2.0.0', '<') ) {
			$display_advance = "no";
		}
	}
	?>
	<div class="question-controls">
		<span><b><?php esc_html_e( 'Total Questions:', 'quiz-master-next' ); ?></b> <span id="total-questions"></span></span>
		<p class="search-box">
			<label class="screen-reader-text" for="question_search"><?php esc_html_e( 'Search Questions:', 'quiz-master-next' ); ?></label>
			<input type="search" id="question_search" name="question_search" value="" placeholder="<?php esc_html_e( 'Search Questions', 'quiz-master-next' ); ?>">
			<?php do_action('qsm_question_controls_head'); ?>
		</p>
	</div>
	<div class="qsm-admin-bulk-actions">
		<button id="qsm-bulk-delete-question" class="button button-danger"><?php esc_html_e( 'Delete Selected', 'quiz-master-next' ); ?> (<span class="qsm-selected-question-count">0</span>)</button>
		<button id="qsm-bulk-delete-all-question" class="button button-danger"><?php esc_html_e( 'Delete All', 'quiz-master-next' ); ?></button>
	</div>
	<div class="questions quiz_form_type_<?php echo esc_attr( $form_type ); ?> quiz_quiz_systen_<?php echo esc_attr( $quiz_system ); ?>">
		<div class="qsm-showing-loader" style="text-align: center;margin-bottom: 20px;">
			<div class="qsm-spinner-loader"></div>
		</div>
	</div>
	<div class="question-create-page">
		<div>
			<button class="new-page-button button button-primary qsm-common-button-styles"><span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'Create New Page', 'quiz-master-next' ); ?></button>
			<button style="display: none;"
				class="save-page-button button button-primary"><?php esc_html_e( 'Save Questions and Pages', 'quiz-master-next' ); ?></button>
			<span class="spinner" id="save-edit-quiz-pages" style="float: none;"></span>
		</div>
	</div>
	<!-- Popup for question bank -->
	<div class="qsm-popup qsm-popup-slide qsm-standard-popup qsm-popup-bank" id="modal-2" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header qsm-question-bank-header">
					<h2 class="qsm-popup__title" id="modal-2-title"><?php esc_html_e( 'Question Bank', 'quiz-master-next' ); ?></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-2-content">
					<?php do_action('qsm-question-categories-setting')?>
					<input type="hidden" name="add-question-bank-page" id="add-question-bank-page" value="">
					<div class="qsm-question-bank-filters">
						<div class="qsm-question-bank-search">
							<form action="" method="post" id="question-bank-search-form">
								<input type="search" name="search" value="" id="question-bank-search-input" placeholder="<?php esc_html_e( 'Search questions', 'quiz-master-next' ); ?>">
							</form>
							<select name="question-bank-cat" id="question-bank-cat">
								<option value=""><?php esc_html_e( 'All Categories', 'quiz-master-next' ); ?></option>
							</select>
							<select name="question-bank-quiz" id="question-bank-quiz">
								<option value=""><?php esc_html_e( 'All Quiz', 'quiz-master-next' ); ?></option>
								<?php
								foreach ( $quiz_data as $quiz ) {
									echo '<option value="' . esc_attr( $quiz->quiz_id ) . '">' . esc_html( $quiz->quiz_name ) . '</option>';
								}
								?>
							</select>
							<select name="question-bank-type" id="question-bank-type">
								<option value=""><?php esc_html_e( 'All Question Types', 'quiz-master-next' ); ?></option>
								<?php
								if ( ! empty( $question_types ) ) {
									foreach ( $question_types as $type ) {
										$slug = isset( $type['slug'] ) ? esc_attr( $type['slug'] ) : '';
										$name = isset( $type['name'] ) ? esc_html( $type['name'] ) : '';
										echo '<option value="' . esc_attr( $slug ) . '">' . esc_html( $name ) . '</option>';
									}
								}
								?>
							</select>
						</div>
						<div class="qsm-question-bank-select">
							<label class="qsm-select-all-label"><input type="checkbox" id="qsm_select_all_question" /><?php esc_html_e( 'Select All Question', 'quiz-master-next' ); ?></label>
						</div>
					</div>
					<div id="question-bank"></div>
				</main>
				<footer class="qsm-popup__footer qsm-question-bank-footer">
					<a href="javascript:void(0)" class="qsm-action-link-delete" id="qsm-delete-selected-question"><?php esc_html_e( 'Delete from Question Bank', 'quiz-master-next' ); ?></a>
					<button class="button button-primary" id="qsm-import-selected-question"><?php esc_html_e( 'Add Questions', 'quiz-master-next' ); ?></button>
				</footer>
			</div>
		</div>
	</div>

	<!-- Popup for editing question -->
	<div class="qsm-popup qsm-popup-slide" id="modal-1" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-1-title"><?php esc_html_e( 'Edit Question', 'quiz-master-next' ); ?> [
						ID:
						<span id="edit-question-id"></span> ]
					</h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-1-content">
					<input type="hidden" name="edit_question_id" id="edit_question_id" value="">
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content" style="position: relative;">
								<div class="qsm-linked-list-div-block">
									<p><?php esc_attr_e( 'This question is linked with other quizzes ', 'quiz-master-next' ); ?> <span class="qsm-linked-list-view-button"><?php esc_attr_e( 'View', 'quiz-master-next' ); ?></span></p>
									<div class="qsm-linked-list-container">
										<div class="qsm-linked-list-inside"></div>
									</div>
								</div>
								<div class="qsm-row">
									<textarea id="question_title" rows="1" class="question-title" name="question-title" placeholder="<?php esc_attr_e( 'Type your question here', 'quiz-master-next' ); ?>"></textarea>
								</div>
								<a href="javascript:void(0)" class="qsm-show-question-desc-box">+ <?php esc_html_e( 'Edit description', 'quiz-master-next' ); ?></a>
								<div class="qsm-row qsm-editor-wrap" style="display: none;">
									<a href="javascript:void(0)" class="qsm-hide-question-desc-box">- <?php esc_html_e( 'Hide description', 'quiz-master-next' ); ?></a>
									<textarea placeholder="<?php esc_attr_e( 'Add your description here', 'quiz-master-next' ); ?>" id="question-text"></textarea>
								</div>
								<div class="qsm-row" style="margin-bottom: 0;">
									<?php
									$description_arr = $mlwQuizMasterNext->pluginHelper->description_array();
									foreach ( $question_types as $type ) {
										if ( isset( $type['options']['description'] ) && null !== $type['options']['description'] ) {
											$description = array(
												'question_type_id'   => $type['slug'],
												'description'        => $type['options']['description'],
											);
											array_push( $description_arr, $description );
										}
									}
									// disabling polar for form type quiz and system correct/incorrect
									if ( 0 === intval( $form_type ) && 0 === intval( $quiz_system ) ) {
										$polar_class         = $polar_question_use   = '';
										$description_arr[]   = array(
											'question_type_id' => '13',
											'description' => __( 'Use points based grading system for Polar questions.', 'quiz-master-next' ),
										);
									} else {
										$polar_class         = 'qsm_show_question_type_13';
										$polar_question_use  = ',13';
									}

									$show_answer_option = '';
									foreach ( $question_types as $type ) {
										if ( isset( $type['options']['show_answer_option'] ) && $type['options']['show_answer_option'] ) {
											$show_answer_option .= ' qsm_show_question_type_' . $type['slug'];
										}
									}

									$description_arr = apply_filters( 'qsm_question_type_description', $description_arr );
									if ( $description_arr ) {
										foreach ( $description_arr as $value ) {
											$question_type_id = $value['question_type_id'];
											?><p id="question_type_<?php echo esc_attr( $question_type_id ); ?>_description" class="question-type-description"><?php echo esc_attr( $value['description'] ); ?></p><?php
										}
									}
									?>
								</div>
								<div id="qsm_optoins_wrapper" class="qsm-row qsm_hide_for_other qsm_show_question_type_0 qsm_show_question_type_1 qsm_show_question_type_2 qsm_show_question_type_3 qsm_show_question_type_4 qsm_show_question_type_5 qsm_show_question_type_7 qsm_show_question_type_10 qsm_show_question_type_12 qsm_show_question_type_14 <?php echo apply_filters( 'qsm_polar_class', esc_attr( $polar_class . $show_answer_option ) ); ?>">
									<div class="correct-header"><?php esc_html_e( 'Correct', 'quiz-master-next' ); ?></div>
									<div class="answers" id="answers">

									</div>
									<div class="qsm-wrap-add-new-answer">
										<div class="new-answer-button">
											<a href="javascript:void(0)" class="button-secondary" id="new-answer-button"><span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'Add Answer!', 'quiz-master-next' ); ?></a>
										</div>
										<?php do_action( 'qsm_question_editor_button_section_after' ); ?>
									</div>
									<?php do_action( 'qsm_after_options' ); ?>
								</div>
								<div class="qsm-question-misc-options advanced-content">
									<?php
									$show_correct_answer_info        = '';
									$show_autofill                   = '';
									$show_case_sensitive             = '';
									$show_limit_text                 = '';
									$show_limit_multiple_response    = '';
									$show_file_upload_type           = '';
									$show_file_upload_limit          = '';
									$placeholder_text                = '';
									foreach ( $question_types as $type ) {
										if ( isset( $type['options']['show_correct_answer_info'] ) && $type['options']['show_correct_answer_info'] ) {
											$show_correct_answer_info .= ',' . $type['slug'];
										}
										if ( isset( $type['options']['show_autofill'] ) && $type['options']['show_autofill'] ) {
											$show_autofill .= ',' . $type['slug'];
										}
										if ( isset( $type['options']['show_case_sensitive'] ) && $type['options']['show_case_sensitive'] ) {
											$show_case_sensitive .= ',' . $type['slug'];
										}
										if ( isset( $type['options']['show_limit_text'] ) && $type['options']['show_limit_text'] ) {
											$show_limit_text .= ',' . $type['slug'];
										}
										if ( isset( $type['options']['show_limit_multiple_response'] ) && $type['options']['show_limit_multiple_response'] ) {
											$show_limit_multiple_response .= ',' . $type['slug'];
										}
										if ( isset( $type['options']['show_file_upload_type'] ) && $type['options']['show_file_upload_type'] ) {
											$show_file_upload_type .= ',' . $type['slug'];
										}
										if ( isset( $type['options']['show_file_upload_limit'] ) && $type['options']['show_file_upload_limit'] ) {
											$show_file_upload_limit .= ',' . $type['slug'];
										}
										if ( isset( $type['options']['placeholder_text'] ) && $type['options']['placeholder_text'] ) {
											$placeholder_text .= ',' . $type['slug'];
										}
									}
									$advanced_question_option    = array(
										'correct_answer_info' => array(
											'heading'  => __( 'Correct Answer Info', 'quiz-master-next' ),
											'type'     => 'textarea',
											'default'  => '',
											'priority' => '2',
											'show'     => '0,1,2,3,4,5,7,10,12,14' . $polar_question_use . $show_correct_answer_info,
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'correct_answer_info', 'quizsurvey-correct_answer_info_doc' ),
										),
										'image_size'       => array(
											'heading'  => __( 'Set custom image size', 'quiz-master-next' ),
											'type'     => 'multi_text',
											'priority' => '2',
											'options'  => array(
												'width'  => __( 'Width ', 'quiz-master-next' ),
												'height' => __( 'Height', 'quiz-master-next' ),
											),
											'default'  => '',
											'show'     => '',
											'documentation_link' => 'https://quizandsurveymaster.com/docs/creating-quizzes-and-surveys/adding-and-editing-questions/#7-set-custom-image-size',
										),
										'comments'         => array(
											'heading'  => __( 'Comment Box', 'quiz-master-next' ),
											'label'    => __( 'Field Type', 'quiz-master-next' ),
											'type'     => 'select',
											'priority' => '3',
											'options'  => array(
												'0' => __( 'Small Text Field', 'quiz-master-next' ),
												'2' => __( 'Large Text Field', 'quiz-master-next' ),
												'1' => __( 'None', 'quiz-master-next' ),
											),
											'default'  => '1',
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'comment-box', 'quizsurvey-comment-box_doc' ),
										),
										'hint'             => array(
											'heading'  => __( 'Hint', 'quiz-master-next' ),
											'label'    => __( 'Hint Text', 'quiz-master-next' ),
											'type'     => 'text',
											'default'  => '',
											'priority' => '4',
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'hints', 'quizsurvey-hints_doc' ),
										),
										'answer_limit'     => array(
											'heading'  => __( 'Answer Limit', 'quiz-master-next' ),
											'label'    => __( 'Answer Limit', 'quiz-master-next' ),
											'type'     => 'text',
											'default'  => '',
											'priority' => '0,1,2,4,10',
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'answer_limit', 'quizsurvey-answer_limit_doc' ),
										),
										'autofill'         => array(
											'heading'  => __( 'Autofill', 'quiz-master-next' ),
											'label'    => __( 'Hide Autofill?', 'quiz-master-next' ),
											'type'     => 'single_checkbox',
											'priority' => '6',
											'options'  => array(
												'1' => __( 'Yes', 'quiz-master-next' ),
											),
											'default'  => '0',
											'show'     => '3, 14' . $show_autofill,
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'autofill', 'quizsurvey-autofill_doc' ),
										),
										'case_sensitive'   => array(
											'heading'  => __( 'Case Sensitivity', 'quiz-master-next' ),
											'label'    => __( 'Require correct input of uppercase and lowercase letters', 'quiz-master-next' ),
											'type'     => 'single_checkbox',
											'priority' => '1',
											'options'  => array(
												'1' => __( 'Yes', 'quiz-master-next' ),
											),
											'default'  => '0',
											'show'     => '3, 5, 14' . $show_case_sensitive,
										),
										'limit_text'       => array(
											'heading'  => __( 'Limit Text', 'quiz-master-next' ),
											'label'    => __( 'Maximum number of characters allowed', 'quiz-master-next' ),
											'type'     => 'text',
											'priority' => '7',
											'default'  => '',
											'show'     => '3, 5, 7, 14' . $show_limit_text,
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'limit_text', 'quizsurvey-limit_text_doc' ),
										),
										'min_text_length'  => array(
											'heading'  => __( 'Minimum Characters', 'quiz-master-next' ),
											'label'    => __( 'Minimum number of characters required', 'quiz-master-next' ),
											'type'     => 'text',
											'priority' => '11',
											'default'  => '',
											'show'     => '3, 5, 7, 14',
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'min_text_length', 'quizsurvey-min_text_length_doc' ),
										),
										'limit_multiple_response' => array(
											'heading'  => __( 'Limit Multiple choice', 'quiz-master-next' ),
											'label'    => __( 'Maximum number of choice selection allowed', 'quiz-master-next' ),
											'type'     => 'text',
											'priority' => '8',
											'default'  => '',
											'show'     => '4,10' . $show_limit_multiple_response,
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'limit_multiple_response', 'quizsurvey-limit_multiple_response_doc' ),
										),
										'file_upload_limit' => array(
											'heading'  => __( 'File upload limit ( in MB )', 'quiz-master-next' ),
											'type'     => 'number',
											'priority' => '9',
											'default'  => '4',
											'show'     => '11' . $show_file_upload_limit,
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'file_upload_limit', 'quizsurvey-file_upload_limit_doc' ),
										),
										'file_upload_type' => array(
											'heading'  => __( 'Allow File type', 'quiz-master-next' ),
											'type'     => 'multi_checkbox',
											'priority' => '10',
											'options'  => array(
												'text/plain' => __( 'Text File', 'quiz-master-next' ),
												'image' => __( 'Image', 'quiz-master-next' ),
												'application/pdf' => __( 'PDF File', 'quiz-master-next' ),
												'doc'   => __( 'Doc File', 'quiz-master-next' ),
												'excel' => __( 'Excel File', 'quiz-master-next' ),
												'video/mp4' => __( 'Video', 'quiz-master-next' ),
											),
											'default'  => 'image,application/pdf',
											'show'     => '11' . $show_file_upload_type,
											'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'file_upload_type', 'quizsurvey-file_upload_type_doc' ),
										),
										'placeholder_text' => array(
											'heading'  => __( 'Placeholder Text', 'quiz-master-next' ),
											'type'     => 'text',
											'default'  => '',
											'priority' => '1',
											'show'     => '3, 5, 7' . $placeholder_text,
										),
									);
									$advanced_question_option    = apply_filters( 'qsm_question_advanced_option', $advanced_question_option );
									$keys                        = array_column( $advanced_question_option, 'priority' );
									array_multisort( $keys, SORT_ASC, $advanced_question_option );
									foreach ( $advanced_question_option as $qo_key => $single_option ) {
										qsm_generate_question_option( $qo_key, $single_option );
									}

									do_action( 'qsm_question_form_fields', $quiz_id );
									?>
								</div>
							</div>
							<div id="postbox-container-1" class="postbox-container">
								<div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
									<div id="submitdiv" class="postbox ">
										<h2 class="hndle ui-sortable-handle">
											<label class="qsm-checkbox-switch small-switch">
												<input type="checkbox" name="question_status" id="qsm-question-status" value="1"><span class="qsm-switch-slider round"></span>
											</label>
											<span id="qsm-question-status-text"><?php esc_html_e( 'Published', 'quiz-master-next' ); ?></span>
											<span id="qsm-question-id"></span>
										</h2>
										<div class="inside">
											<div class="submitbox" id="submitpost">
												<div id="minor-publishing">
													<div class="qsm-row">
														<label>
															<?php esc_html_e( 'Question Type', 'quiz-master-next' ); ?>
															<?php
															echo '<a class="qsm-question-doc" href="' . esc_url( qsm_get_plugin_link( 'docs/about-quiz-survey-master/question-types/', 'quiz_editor', 'question_type', 'quizsurvey-question-type_doc' ) ) . '" target="_blank" title="' . esc_html__( 'View Documentation', 'quiz-master-next' ) . '">';
															echo '<span class="dashicons dashicons-editor-help"></span>';
															echo '</a>';
															?>
														</label>
														<select name="question_type" id="question_type">
															<?php
															foreach ( $question_types_categorized as $category_name => $category_items ) {
																?>
																	<optgroup label="<?php echo esc_attr( $category_name ) ?>">
																	<?php
																	foreach ( $category_items as $type ) {
																		if ( isset( $type['disabled'] ) && true === $type['disabled'] ) {
																			echo '<option disabled value="' . esc_attr( $type['slug'] ) . '">' . esc_html( $type['name'] ) . '</option>';
																		} else {
																			echo '<option value="' . esc_attr( $type['slug'] ) . '">' . esc_html( $type['name'] ) . '</option>';
																		}
																	}
																	?>
																	</optgroup>
																<?php
															}
															?>
														</select>
														<p class="hidden" id="question_type_info"></p>
													</div>
													<?php
													$show_change_answer_editor = '';
													foreach ( $question_types as $type ) {
														if ( isset( $type['options']['show_change_answer_editor'] ) && $type['options']['show_change_answer_editor'] ) {
															$show_change_answer_editor .= ',' . $type['slug'];
														}
													}
													$show_match_answer = '';
													foreach ( $question_types as $type ) {
														if ( isset( $type['options']['show_match_answer'] ) && $type['options']['show_match_answer'] ) {
															$show_match_answer .= ',' . $type['slug'];
														}
													}
													$simple_question_option  = array(
														'change-answer-editor'   => array(
															'label'              => __( 'Answers Type', 'quiz-master-next' ),
															'type'               => 'select',
															'priority'           => '1',
															'options'            => array(
																'text'   => __( 'Text Answers', 'quiz-master-next' ),
																'rich'   => __( 'Rich Answers', 'quiz-master-next' ),
																'image'  => __( 'Image Answers', 'quiz-master-next' ),
															),
															'default'            => 'text',
															'show'               => '0,1,4,10,13' . $show_change_answer_editor,
															// 'tooltip' => __('You can use text and rich answer for question answers.', 'quiz-master-next'),.
															'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'answer_type', 'answer_type_doc#Answer-Type' ),
														),
														'match-answer'           => array(
															'label'      => __( 'Match Answer', 'quiz-master-next' ),
															'type'       => 'select',
															'priority'   => '3',
															'options'    => array(
																'random'     => __( 'Randomly', 'quiz-master-next' ),
																'sequence'   => __( 'Sequentially', 'quiz-master-next' ),
															),
															'default'    => 'random',
															'show'       => '14' . $show_match_answer,
														),
														'required'               => array(
															'label'      => __( 'Required?', 'quiz-master-next' ),
															'type'       => 'single_checkbox',
															'priority'   => '3',
															'options'    => array(
																'0' => __( 'Yes', 'quiz-master-next' ),
															),
															'default'    => '0',
														),
													);
													$simple_question_option  = apply_filters( 'qsm_question_format_option', $simple_question_option );
													$keys                    = array_column( $simple_question_option, 'priority' );
													array_multisort( $keys, SORT_ASC, $simple_question_option );
													foreach ( $simple_question_option as $qo_key => $single_option ) {
														qsm_display_question_option( $qo_key, $single_option );
													}
													?>
													<div class="clear clearfix"></div>
													<div id="publishing-action">
														<span class="spinner" id="save-edit-question-spinner" style="float: none;"></span>
														<button id="save-popup-button" class="button button-primary">Save Question</button>
													</div>
													<div id="delete-action" style="float: none;">
														<a class="submitdelete deletion" data-micromodal-close aria-label="Close this">Cancel</a>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div id="categorydiv" class="postbox">
										<h2 class="hndle ui-sortable-handle">
											<span><?php esc_html_e( 'Select Category', 'quiz-master-next' ); ?></span>
											<a class="qsm-question-doc" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'category', 'quizsurvey-category_doc' ) ); ?>" target="_blank" title="View Documentation"><span class="dashicons dashicons-editor-help"></span></a>
										</h2>
										<div class="inside">
											<?php
											$enabled_multiple_category = get_option( 'qsm_multiple_category_enabled' );
											if ( $enabled_multiple_category && 'cancelled' !== $enabled_multiple_category ) {
												$category_question_option = array(
													'categories' => array(
														'label'      => '',
														'type'       => 'multi_category',
														'priority'   => '5',
														'default'    => '',
													),
												);
											} else {
												$category_question_option = array(
													'categories' => array(
														'label'              => '',
														'type'               => 'category',
														'priority'           => '5',
														'default'            => '',
														'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'category', 'quizsurvey-category_doc' ),
													),
												);
											}
											$category_question_option    = apply_filters( 'qsm_question_category_option', $category_question_option );
											$keys                        = array_column( $category_question_option, 'priority' );
											array_multisort( $keys, SORT_ASC, $category_question_option );
											foreach ( $category_question_option as $qo_key => $single_cat_option ) {
												qsm_display_question_option( $qo_key, $single_cat_option );
											}
											?>
										</div>
									</div>
									<div id="featureImagediv" class="postbox">
										<h2 class="hndle ui-sortable-handle">
											<span><?php esc_html_e( 'Featured image', 'quiz-master-next' ); ?></span>
										</h2>
										<div class="inside">
											<?php
											echo '<a href="javascript:void(0)" class="qsm-feature-image-upl">' . esc_html__( 'Upload Image', 'quiz-master-next' ) . '</a>
                                            <a href="javascript:void(0)" class="qsm-feature-image-rmv" style="display:none">' . esc_html__( 'Remove Image', 'quiz-master-next' ) . '</a>'
											. '<input type="hidden" name="qsm-feature-image-id" class="qsm-feature-image-id" value="">'
											. '<input type="hidden" name="qsm-feature-image-src" class="qsm-feature-image-src" value="">';
											?>
										</div>
									</div>
									<?php do_action( 'qsm_question_form_fields_side', $quiz_id ); ?>
								</div>
							</div>
						</div>
					</div>
				</main>
			</div>
		</div>
	</div>

	<!-- Popup for page settings -->
	<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="modal-page-1" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-1-title"><?php esc_html_e( 'Edit Page', 'quiz-master-next' ); ?> <span
							style="display: none;">[ ID: <span id="edit-page-id"></span> ]</span></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-page-1-content">
					<input type="hidden" name="edit_page_id" id="edit_page_id" value="">
					<div id="page-options">
						<div class="qsm-row">
							<label class="qsm-page-setting-label"><?php esc_html_e( 'Page Name', 'quiz-master-next' ); ?></label>
							<input type="text" id="pagekey" name="pagekey" value="">
						</div>
						<div class="qsm-row">
							<label class="qsm-page-setting-label"><?php esc_html_e( 'Button Control', 'quiz-master-next' ); ?></label>
							<input name="hide_prevbtn" id="hide_prevbtn" type="checkbox" value="" />
							<span class="qsm-page-setting-span"><?php esc_html_e( 'Hide Previous button', 'quiz-master-next' ); ?></span>
						</div>
						<?php if ( "yes" == $display_advance ) { ?>
						<div class="qsm-page-setting-container" style="background-color:<?php echo esc_attr($background); ?>">
							<div class="qsm-page-setting-top ">
								<div class="qsm-page-setting-left">
									<span class="qsm-page-setting-label"><?php esc_html_e( 'Page Timer', 'quiz-master-next' ); ?></span>
								</div>
								<div class="qsm-page-setting-right">
									<div class="qsm-row <?php echo esc_attr($disable_class); ?>">
										<label><?php esc_html_e( 'How many minutes does the user have to finish the page?', 'quiz-master-next' ); ?></label>
										<span><?php esc_html_e( 'Set a time limit to complete this page in ', 'quiz-master-next' ); ?></span><input <?php echo esc_html($read_only); ?> type="number" step="1" class="small-text" min="0" id="pagetimer" name="pagetimer" value="0" placeholder="<?php esc_attr_e( 'MM', 'quiz-master-next' ); ?>"> : <input type="number" <?php echo esc_html($read_only); ?> step="1" class="small-text" min="0" id="pagetimer_second" name="pagetimer_second" value="0" placeholder="<?php esc_attr_e( 'SS', 'quiz-master-next' ); ?>">
									</div>
									<div class="qsm-row <?php echo esc_attr($disable_class); ?>">
										<input <?php echo esc_html($read_only); ?> name="warning_checkbox" type="checkbox" value="" /><span><?php esc_html_e( 'Show warning at', 'quiz-master-next' ); ?></span>
										<input <?php echo esc_html($read_only); ?> type="number" step="1" class="small-text" min="0" placeholder="<?php esc_html_e( 'MM', 'quiz-master-next' ); ?>" id="pagetimer_warning" name="pagetimer_warning" value="0"> :
										<input <?php echo esc_html($read_only); ?> type="number" step="1" class="small-text" min="0" placeholder="<?php esc_html_e( 'SS', 'quiz-master-next' ); ?>" id="pagetimer_warning_second" name="pagetimer_warning_second" value="0">
									</div>
								</div>
							</div>
							<div class="qsm-page-setting-bottom ">
								<?php if ( ! class_exists ( 'QSM_AdvancedTimer' ) ) { ?>
								<div class="qsm-popup-upgrade-warning">
									<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'php/images/info-yellow.png' ); ?>" alt="information">
									<span><?php esc_html_e( 'You can set timer in each page using Advanced Timer Add-on. ', 'quiz-master-next'); echo sprintf( '<a style="margin-right: 5px;font-weight: bolder;" href="%s" target="_blank">%s</a>', esc_url( qsm_get_plugin_link( 'downloads/wordpress-quiz-timer-advanced', 'advanced-timer-popup', 'quiz_editor', 'get_addon', 'qsm_plugin_upsell' ) ), esc_html__( 'Get this add-on ', 'quiz-master-next' ) ); esc_html_e( 'and extend your quiz features.', 'quiz-master-next' ); ?></span>
								</div>
							<?php } ?>
							</div>
						</div>
						<?php } ?>
						<?php do_action( 'qsm_action_quiz_page_attributes_fields' ); ?>
					</div>
				</main>
				<footer class="qsm-popup__footer">
					<button id="delete-page-popup-button" class="delete-page-button"><?php esc_html_e( 'Delete Page', 'quiz-master-next' ); ?></button>
					<button id="save-page-popup-button" class="button button-primary"><?php esc_html_e( 'Save Page', 'quiz-master-next' ); ?></button>
				</footer>
			</div>
		</div>
	</div>

	<?php add_action( 'admin_footer', 'qsm_options_questions_tab_template' ); ?>

	<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="modal-7" aria-hidden="false">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-7-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-7-title"><?php esc_html_e( 'Delete Options', 'quiz-master-next' ); ?></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close=""></a>
				</header>
				<main class="qsm-popup__content" id="modal-7-content">
					<form action='' method='post' id="delete-question-form">
						<table class="modal-7-table qsm-popup-table">
							<tr class="qsm-popup-table-row">
								<td>
									<h3><?php esc_html_e( 'Unlink', 'quiz-master-next' ); ?></h3>
									<?php esc_html_e( 'Remove this question from the quiz only.', 'quiz-master-next' ); ?></td>
								<td><button id="unlink-question-button" class="qsm-popup__btn qsm-popup__btn-primary qsm-unlink-question-button-btn"><span class="dashicons dashicons-editor-unlink"></span><?php esc_html_e( 'Unlink', 'quiz-master-next' ); ?></button></td>
							<tr>
							<tr class="qsm-popup-table-row">
								<td>
									<h3><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></h3>
									<?php esc_html_e( 'Permanently remove this question from all quizzes and the database. This cannot be undone.', 'quiz-master-next' ); ?></td>
								<td><button id="delete-question-button" class="qsm-popup__btn qsm-popup__btn-primary qsm-delete-question-button-btn"><span class="dashicons dashicons-trash"></span><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></button></td>
							<tr>
						</table>
					</form>
				</main>
			</div>
		</div>
	</div>

	<div class="qsm-popup qsm-popup-slide" id="modal-8" aria-hidden="false">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-8-title">
				<header class="qsm-popup__header">
					<h3 class="qsm-popup__title" id="modal-8-title"><?php esc_html_e( 'Alert', 'quiz-master-next' ); ?>
					</h3>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close=""></a>
				</header>
				<hr />
				<main class="qsm-popup__content" id="modal-8-content">
					<div class="modal-8-table">
					</div>
				</main>
				<hr />
				<footer class="qsm-popup__footer">
					<button id="cancel-button" class="qsm-popup__btn" data-micromodal-close=""
						aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
				</footer>
			</div>
		</div>
	</div>

	<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="modal-9" aria-hidden="false">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-9-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-9-title"><?php esc_html_e( 'Add New Category', 'quiz-master-next' ); ?></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close=""></a>
				</header>
				<main class="qsm-popup__content" id="modal-9-content">
					<table class="modal-9-table">
						<tr>
							<td><?php esc_html_e( 'Category Name', 'quiz-master-next' ); ?>
							</td>
							<td><input type="text" id="new-category-name" /></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Parent Category', 'quiz-master-next' ); ?></td>
							<td>
								<?php
								wp_dropdown_categories(
									array(
										'taxonomy'         => 'qsm_category',
										'descendants_and_self' => 0,
										'selected_cats'    => true,
										'echo'             => true,
										'id'               => 'qsm-parent-category',
										'hide_empty'       => false,
										'hirerichal'       => 1,
										'show_option_none' => 'None',
										'option_none_value' => -1,
										'orderby'          => 'name',
									)
								);
								?>
							</td>
						<tr>
					</table>
					<div class="info"></div>
				</main>
				<footer class="qsm-popup__footer">
					<button id="save-multi-category-button" class="qsm-popup__btn qsm-popup__btn-primary"></span><?php esc_html_e( 'Save', 'quiz-master-next' ); ?></button>
				</footer>
			</div>
		</div>
	</div>

	<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="modal-10" aria-hidden="false">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-10-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-10-title"><?php esc_html_e( 'Move Question To', 'quiz-master-next' ); ?></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close=""></a>
				</header>
				<main class="qsm-popup__content" id="modal-10-content">
					<form action='' method='post' id="move-question-form">
						<input type="hidden" id="current_question_page_no" />
						<input type="hidden" id="current_question_position" />
						<input type="hidden" id="current_question_id" />
						<table class="modal-10-table">
							<tr>
								<td class="custom-error-field" colspan="2"><span id="move-question-error"></span></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Page No.', 'quiz-master-next' ); ?></strong></td>
								<td><input type="number" class="page-no-text" id="changed_question_page_no" value="" placeholder="<?php esc_attr_e( 'Enter page no.', 'quiz-master-next' ); ?>"/></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Question Position', 'quiz-master-next' ); ?></Strong></td>
								<td><input type="number" class="question-position-text" id="changed_question_position" value="" placeholder="<?php esc_attr_e( 'Enter question position.', 'quiz-master-next' ); ?>"/></td>
							</tr>
						</table>
					</form>
				</main>
				<footer class="qsm-popup__footer">
					<button id="cancel-question-button" class="cancel-move-question-button"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
					<button id="move-question-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Save', 'quiz-master-next' ); ?></button>
				</footer>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Unlinks a question from all quizzes it is associated with.
 * This function checks for a valid nonce, retrieves the question ID from the request.
 *
 * @since 9.1.3
 * @return void
 */
function qsm_ajax_unlink_question_from_list() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-unlink-question' ) ) {
        wp_send_json_error( array(
			'message' => __( 'Nonce verification failed.', 'quiz-master-next'),
		));
    }
    $question_id = isset( $_POST['question_id'] ) ? intval( $_POST['question_id'] ) : 0;
    if ( $question_id > 0 ) {
		qsm_process_unlink_question_from_list_by_question_id($question_id);
		wp_send_json_success( array(
			'message' => __( 'Question is unlinked from all quizzes.', 'quiz-master-next' ),
		));
    } else {
		wp_send_json_error( array(
			'message' => __( 'Invalid question ID.', 'quiz-master-next' ),
		));
    }
}
add_action( 'wp_ajax_qsm_unlink_question_from_list', 'qsm_ajax_unlink_question_from_list' );

/**
 * Unlinks a question from all quizzes it is associated with.
 * @since 9.1.3
 * @param int $question_id The ID of the question to unlink.
 * @return void
 */
function qsm_process_unlink_question_from_list_by_question_id( $question_id ) {
    global $wpdb;
    $current_linked_questions = $wpdb->get_var( $wpdb->prepare(
		"SELECT linked_question FROM {$wpdb->prefix}mlw_questions WHERE question_id = %d",
		$question_id
	) );

	if ( $current_linked_questions ) {
		$current_links = explode(',', $current_linked_questions);
		$current_links = array_map('trim', $current_links);
		$current_links = array_diff($current_links, [ $question_id ]);
		$updated_linked_list = implode(',', array_filter($current_links));
		$linked_ids = explode(',', $updated_linked_list);
		foreach ( $linked_ids as $linked_id ) {
			$wpdb->update(
				$wpdb->prefix . 'mlw_questions',
				array( 'linked_question' => $updated_linked_list ),
				array( 'question_id' => intval($linked_id) ),
				array( '%s' ),
				array( '%d' )
			);
		}
		$wpdb->update(
			$wpdb->prefix . 'mlw_questions',
			array( 'linked_question' => '' ),
			array( 'question_id' => intval($question_id) ),
			array( '%s' ),
			array( '%d' )
		);
	}
}

add_action( 'wp_ajax_qsm_save_pages', 'qsm_ajax_save_pages' );

/**
 * Saves the pages and order from the Questions tab
 *
 * @since 5.2.0
 */
function qsm_ajax_save_pages() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-sandy-page' ) ) {
		die( 'Busted!' );
	}

	global $mlwQuizMasterNext;
	$json            = array(
		'status' => 'error',
	);
	$quiz_id         = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
	$post_id         = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
	$pages           = isset( $_POST['pages'] ) ? qsm_sanitize_rec_array( wp_unslash( $_POST['pages'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$qpages          = isset( $_POST['qpages'] ) ? qsm_sanitize_rec_array( wp_unslash( $_POST['qpages'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$all_questions   = array();
	//merge duplicate questions
	foreach ( $pages as $page_key => $questions ) {
		$page_questions  = array();
		$questions       = array_unique( $questions );
		foreach ( $questions as $id ) {
			if ( ! in_array( $id, $all_questions, true ) ) {
				$page_questions[] = $id;
			}
		}
		$all_questions       = array_merge( $all_questions, $questions );
		$pages[ $page_key ]    = $page_questions;
		if ( isset( $qpages[ $page_key ] ) ) {
			$qpages[ $page_key ]['questions'] = $page_questions;
		}
	}

	$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'qpages', $qpages );
	$response = $mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'pages', $pages );
	if ( $response ) {
		$json['status'] = 'success';
		// update post_modified
		$datetime  = current_time( 'Y-m-d H:i:s', 0 );
		$update = array(
			'ID'            => $post_id,
			'post_modified' => $datetime,
		);
		wp_update_post( $update );
	}
	if ( is_qsm_block_api_call( 'save_entire_quiz' ) ) {
		return true;
	}
	echo wp_json_encode( $json );
	wp_die();
}

add_action( 'wp_ajax_qsm_load_all_quiz_questions', 'qsm_load_all_quiz_questions_ajax' );

/**
 * Loads all the questions and echos out JSON
 *
 * @since  0.1.0
 * @return void
 */
function qsm_load_all_quiz_questions_ajax() {
	global $wpdb;
	global $mlwQuizMasterNext;

	// Loads questions.
	$questions = $wpdb->get_results( "SELECT question_id, question_name FROM {$wpdb->prefix}mlw_questions WHERE deleted = '0' ORDER BY question_id DESC" );

	// Creates question array.
	$question_json = array();
	foreach ( $questions as $question ) {
		$question_json[] = array(
			'id'       => $question->question_id,
			'question' => $question->question_name,
		);
	}

	// Echos JSON and dies.
	echo wp_json_encode( $question_json );
	wp_die();
}

add_action( 'wp_ajax_qsm_send_data_sendy', 'qsm_send_data_sendy' );

/**
 * @version 6.3.2
 * Send data to sendy
 */
function qsm_send_data_sendy() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-sendy-save' ) ) {
		die( 'Busted!' );
	}

	$sendy_url = 'http://sendy.expresstech.io';
	$list      = '4v8zvoyXyTHSS80jeavOpg';
	$name      = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	// subscribe
	$postdata = http_build_query(
		array(
			'name'    => $name,
			'email'   => $email,
			'list'    => $list,
			'boolean' => 'true',
		)
	);
	$opts     = array(
		'http' => array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata,
		),
	);
	$context  = stream_context_create( $opts );
	$result   = wp_remote_post(
		$sendy_url . '/subscribe',
		array(
			'body' => array(
				'name'    => $name,
				'email'   => $email,
				'list'    => $list,
				'boolean' => 'true',
			),
		)
	);

	if ( isset( $result['response'] ) && isset( $result['response']['code'] ) && 200 == $result['response']['code'] ) {
		$apiBody = json_decode( wp_remote_retrieve_body( $result ) );
		echo wp_json_encode( $apiBody );
	}
	exit;
}

add_action( 'wp_ajax_qsm_dashboard_delete_result', 'qsm_dashboard_delete_result' );
function qsm_dashboard_delete_result() {
	$result_id = isset( $_POST['result_id'] ) ? intval( $_POST['result_id'] ) : 0;
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wp_rest' ) && $result_id ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'mlw_results',
			array(
				'deleted' => 1,
			),
			array( 'result_id' => $result_id ),
			array(
				'%d',
			),
			array( '%d' )
		);
		wp_send_json_success();
	}
	wp_send_json_error();
}

/**
 * Delete question from question bank
 *
 * @since 7.1.3
 */
function qsm_delete_question_question_bank() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'delete_question_question_bank_nonce' ) ) {
		echo wp_json_encode(
			array(
				'success' => false,
				'message' => __(
					'Nonce verification failed.',
					'quiz-master-next'
				),
			)
		);
		wp_die();
	}
	$question_ids = isset( $_POST['question_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['question_ids'] ) ) : '';
	$question_arr = explode( ',', $question_ids );
	$response     = array();
	if ( $question_arr ) {
		global $wpdb;
		foreach ( $question_arr as $key => $value ) {
			$wpdb->update(
				$wpdb->prefix . 'mlw_questions',
				array(
					'deleted_question_bank' => 1,
				),
				array( 'question_id' => $value ),
				array(
					'%d',
				),
				array( '%d' )
			);
		}
		echo wp_json_encode(
			array(
				'success' => true,
				'message' => __(
					'Selected Questions are removed from question bank.',
					'quiz-master-next'
				),
			)
		);
	}
	exit;
}
add_action( 'wp_ajax_qsm_delete_question_question_bank', 'qsm_delete_question_question_bank' );
/**
 * Delete quiz question from Database
 *
 * @since 7.1.11
 */
function qsm_delete_question_from_database() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'delete_question_from_database' ) ) {
		wp_send_json_error( __( 'Nonce verification failed.', 'quiz-master-next' ) );
	}
	$base_question_id = $question_id = isset( $_POST['question_id'] ) ? intval( $_POST['question_id'] ) : 0;
	if ( $question_id ) {

		global $wpdb, $mlwQuizMasterNext;
		$update_qpages_after_delete = array();
		$connected_question_ids = qsm_get_unique_linked_question_ids_to_remove( [ $question_id ] );
		$question_ids_to_delete = array_merge($connected_question_ids, [ $question_id ]);
		$question_ids_to_delete = array_unique( $question_ids_to_delete );
		$placeholders = array_fill( 0, count( $question_ids_to_delete ), '%d' );

		do_action('qsm_question_deleted',$question_id);

		if ( ! empty($connected_question_ids) ) {
			$connected_question_ids = array_diff($connected_question_ids, [ $base_question_id ] );
			$update_qpages_after_delete = qsm_process_to_update_qpages_after_unlink($connected_question_ids);
		}

		// Construct the query with placeholders
		$query = sprintf(
			"DELETE FROM {$wpdb->prefix}mlw_questions WHERE question_id IN (%s)",
			implode( ', ', $placeholders )
		);

		// Prepare the query
		$query = $wpdb->prepare( $query, $question_ids_to_delete );
		$results = $wpdb->query( $query );
		if ( $results ) {
			if ( ! empty($update_qpages_after_delete) ) {
				foreach ( $update_qpages_after_delete as $quiz_id => $aftervalue ) {
					$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
					$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'qpages', $aftervalue['qpages'] );
					$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'pages', $aftervalue['pages'] );
				}
			}
			wp_send_json_success( __( 'Question removed Successfully.', 'quiz-master-next' ) );
		}else {
			wp_send_json_error( __( 'Question delete failed!', 'quiz-master-next' ) );
			$mlwQuizMasterNext->log_manager->add( __('Error 0001 delete questions failed - question ID:', 'quiz-master-next') . $question_id, '<br><b>Error:</b>' . $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}
}
add_action( 'wp_ajax_qsm_delete_question_from_database', 'qsm_delete_question_from_database' );

function qsm_bulk_delete_question_from_database() {
	// Validate nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'delete_question_from_database' ) ) {
		wp_send_json_error( __( 'Nonce verification failed!', 'quiz-master-next' ) );
	}

	// check Quiestion ID.
	if ( empty( $_POST['question_id'] ) ) {
		wp_send_json_error( __( 'Missing question ID.', 'quiz-master-next' ) );
	}

	// Global variables
	global $wpdb, $mlwQuizMasterNext;

	// should have delete_published_posts capabilities.
	if ( ! $mlwQuizMasterNext->qsm_is_admin( 'delete_published_posts' ) ) {
		wp_send_json_error( __( 'You do not have permission to delete questions. Please contact the site administrator.', 'quiz-master-next' ) );
	}

	$question_id = sanitize_text_field( wp_unslash( $_POST['question_id'] ) );

	$question_id = explode( ',', $question_id );

	// filter question ids
	$question_id = array_filter( $question_id, function( $questionID ) {
		return is_numeric( $questionID ) && 0 < intval( $questionID );
	} );

	// Sanitize and validate the IDs
	$base_question_ids = $question_id = array_map( 'intval', $question_id );

	if ( ! empty( $question_id ) ) {

		$update_qpages_after_delete = array();
		$connected_question_ids = qsm_get_unique_linked_question_ids_to_remove($question_id);
		$question_ids_to_delete = array_merge($connected_question_ids, $question_id);
		$question_ids_to_delete = array_unique( $question_ids_to_delete );
		$placeholders = array_fill( 0, count( $question_ids_to_delete ), '%d' );

		do_action('qsm_question_deleted',$question_id);

		if ( ! empty($connected_question_ids) ) {
			$connected_question_ids = array_diff($connected_question_ids, $base_question_ids );
			$update_qpages_after_delete = qsm_process_to_update_qpages_after_unlink($connected_question_ids);
		}

		// Construct the query with placeholders
		$query = sprintf(
			"DELETE FROM {$wpdb->prefix}mlw_questions WHERE question_id IN (%s)",
			implode( ', ', $placeholders )
		);

		// Prepare the query
		$query = $wpdb->prepare( $query, $question_ids_to_delete );

		$results = $wpdb->query( $query );
		if ( $results ) {
			if ( ! empty($update_qpages_after_delete) ) {
				foreach ( $update_qpages_after_delete as $quiz_id => $aftervalue ) {
					$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
					$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'qpages', $aftervalue['qpages'] );
					$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'pages', $aftervalue['pages'] );
				}
			}
			wp_send_json_success( __( 'Questions removed Successfully.', 'quiz-master-next' ) );
		}else {
			$mlwQuizMasterNext->log_manager->add( __('Error 0001 delete questions failed - question IDs:', 'quiz-master-next') . $question_id, '<br><b>Error:</b>' . $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
			wp_send_json_error( __( 'Question delete failed!', 'quiz-master-next' ) );
		}
	} else {
		wp_send_json_error( __( 'Question delete failed! Invalid question ID.', 'quiz-master-next' ) );
	}
}
add_action( 'wp_ajax_qsm_bulk_delete_question_from_database', 'qsm_bulk_delete_question_from_database' );

/**
 * returns pages and qpages for dependent question ids for update after deleting questions
 *
 * @param array $connected_question_ids An array of question IDs.
 * @since 9.1.3
 */
function qsm_process_to_update_qpages_after_unlink( $connected_question_ids ) {
	$comma_seprated_ids = implode( ',', array_unique($connected_question_ids) );
	$qpages_array = array();
	if ( ! empty($comma_seprated_ids) ) {
		global $wpdb, $mlwQuizMasterNext;
		$quiz_results = $wpdb->get_results( "SELECT `quiz_id`, `question_id` FROM `{$wpdb->prefix}mlw_questions` WHERE `question_id` IN (" .$comma_seprated_ids. ")" );
		if ( ! empty($quiz_results) ) {
			foreach ( $quiz_results as $single_quiz ) {
				$quiz_id         = $single_quiz->quiz_id;
				$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
				$pages                  = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
				$clone_qpages = $qpages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'qpages', array() );
				if ( ! empty($clone_qpages) ) {
					foreach ( $clone_qpages as $clonekey => $clonevalue ) {
						if ( ! empty($clonevalue['questions']) && in_array($single_quiz->question_id, $clonevalue['questions'], true) ) {
							$clone_qpages[ $clonekey ]['questions'] = array_diff($clonevalue['questions'], [ $single_quiz->question_id ]);
							$pages[ $clonekey ] = array_diff($pages[ $clonekey ], [ $single_quiz->question_id ]);
						}
					}
					$qpages = $clone_qpages;
				}
				//merge duplicate questions
				$all_questions   = array();
				foreach ( $pages as $page_key => $questions ) {
					$page_questions  = array();
					$questions       = array_unique( $questions );
					foreach ( $questions as $id ) {
						if ( ! in_array( $id, $all_questions, true ) ) {
							$page_questions[] = $id;
						}
					}
					$all_questions       = array_merge( $all_questions, $questions );
					$pages[ $page_key ]    = $page_questions;
					if ( isset( $qpages[ $page_key ] ) ) {
						$qpages[ $page_key ]['questions'] = $page_questions;
					}
				}
				$qpages_array[ $quiz_id ] = array(
					'pages'  => $pages,
					'qpages' => $qpages,
				);
			}
		}
	}
	return $qpages_array;
}

/**
 * Get Unique Linked Question IDs
 *
 * @param array $question_ids An array of question IDs to query for linked questions.
 * @return array $unique_ids An array of unique question IDs extracted from the linked questions.
 * @since 9.1.3
 */
function qsm_get_unique_linked_question_ids_to_remove( $question_ids ) {
    global $wpdb;
	$all_ids = array();
    foreach ( $question_ids as $id ) {
        $sql = $wpdb->prepare(
            "SELECT linked_question FROM {$wpdb->prefix}mlw_questions WHERE question_id = %d",
            $id
        );
        $linked_question = $wpdb->get_var($sql);
        if ( ! empty($linked_question) ) {
			$all_ids = array_merge($all_ids, explode(',', $linked_question));
        }
    }
    return $all_ids;
}

add_action( 'wp_ajax_save_new_category', 'qsm_save_new_category' );
function qsm_save_new_category() {
	$category   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$parent     = isset( $_POST['parent'] ) ? intval( $_POST['parent'] ) : '';
	$parent     = ( -1 == $parent ) ? 0 : $parent;
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-sandy-page' ) ) {
		$term_array = wp_insert_term(
			$category,
			'qsm_category',
			array(
				'parent' => $parent,
			)
		);
		echo wp_json_encode( $term_array );
	}
	exit;
}

/**
 * Adds the templates for the options for questions tab.
 *
 * @since 7.3.5
 */
function qsm_options_questions_tab_template() {
	?>
	<!-- View for Page -->
	<script type="text/template" id="tmpl-page">
		<div class="page page-new" data-page-id="{{data.id }}">
			<div class="page-header">
				<div><span class="dashicons dashicons-move"></span> <span class="page-number"></span></div>
				<div>
					<a href="javascript:void(0)" class="edit-page-button" title="Edit Page"><span class="dashicons dashicons-admin-generic"></span></a>
					<a href="javascript:void(0)" class="add-question-bank-button button button-primary qsm-common-button-styles"><?php esc_html_e( 'Import', 'quiz-master-next' ); ?></a>
					<a href="javascript:void(0)" class="new-question-button button button-primary qsm-common-button-styles"><?php esc_html_e( 'Add Question', 'quiz-master-next' ); ?></a>
				</div>
			</div>
			<label for="qsm-admin-select-page-question-{{data.id}}" class="qsm-admin-select-page-question-label"><input class="qsm-admin-select-page-question" id="qsm-admin-select-page-question-{{data.id}}" value="1" type="checkbox"/><?php esc_html_e( 'Select All', 'quiz-master-next' ); ?></label>
			<div class="page-footer">
				<div class="page-header-buttons">
					<a href="javascript:void(0)" class="add-question-bank-button button button-primary qsm-common-button-styles"><?php esc_html_e( 'Import', 'quiz-master-next' ); ?></a>
					<a href="javascript:void(0)" class="new-question-button button button-primary qsm-common-button-styles"><?php esc_html_e( 'Add Question', 'quiz-master-next' ); ?></a>
				</div>
			</div>
		</div>
	</script>

	<!-- View for Question -->
	<script type="text/template" id="tmpl-question">
		<div class="question question-new" data-question-id="{{data.id}}" data-question-type="{{data.type}}">
			<div class="qsm-question-container">
				<input type="checkbox" class="qsm-admin-select-question-input" value="{{data.id}}">
				<div class="question-content">
					<div><span class="dashicons dashicons-move"></span></div>
					<div class="question-content-title-box">
						<div class="question-content-text">
							{{{data.question}}}
						</div>
						<div class="question-category"><# if ( 0 !== data.category.length ) { #> <?php esc_html_e( 'Category:', 'quiz-master-next' ); ?> {{data.category}} <# } #></div>
					</div>
					<div class="form-actions">
						<div class="qsm-actions-link-box">
							<a href="#" title="Edit Question" class="edit-question-button"><img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/edit-pencil.svg'); ?>" alt="edit-pencil.svg"/></a>
							<a href="#" title="Clone Question" class="duplicate-question-button"><img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/copy.svg'); ?>" alt="copy.svg"/></a>
							<a href="javascript:void(0)" title="Move Question" class="move-question-button"><img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/arrow-up-down-fill.svg'); ?>" alt="arrow-up-down-fill.svg"/></a>
							<a href="#" title="Delete Question" class="delete-question-button" data-question-iid="{{data.id }}"><img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/trash.svg'); ?>" alt="trash.svg"/></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</script>

	<!-- View for question in question bank -->
	<script type="text/template" id="tmpl-single-question-bank-question">
		<div class="question-bank-question" data-question-id="{{data.id}}" data-category-name="{{data.category}}" data-question-type="{{data.type}}">
			<div class="question-bank-selection">
				<input type="checkbox" name="qsm-question-checkbox[]" class="qsm-question-checkbox" />
			</div>
			<div><p>{{{data.question}}}</p><p style="font-size: 12px;color: gray;font-style: italic;"><b>Quiz Name:</b> {{data.quiz_name}}    <# if ( data.category != '' ) { #> <b>Category:</b> {{data.category}} <# } #></p></div>
			<div>
				<a href="javascript:void(0)" class="button import-button" data-question-id="{{data.id}}"><?php esc_html_e( 'Add', 'quiz-master-next' ); ?></a>
				<a href="javascript:void(0)" data-questions="{{data.linked_question}}" class="button link-question" data-question-id="{{data.id}}"><?php esc_html_e( 'Link', 'quiz-master-next' ); ?></a>
			</div>
		</div>
	</script>

	<!-- View for single category -->
	<script type="text/template" id="tmpl-single-category">
		<div class="category">
			<label><input type="radio" name="category" class="category-radio" value="{{data.category}}">{{data.category}}</label>
		</div>
	</script>

	<!-- View for single answer -->
	<script type="text/template" id="tmpl-single-answer">
		<div class="answers-single">
			<div class="remove-answer-icon">
				<a href="javascript:void(0)" class="delete-answer-button"><span class="dashicons dashicons-minus"></span></a>
				<a href="javascript:void(0)" class="qsm-add-answer-button"><span class="dashicons dashicons-plus"></span></a>
			</div>
			<?php do_action( 'qsm_admin_single_answer_option_fields_before' ); ?>
			<div class="answer-text-div qsm-editor-wrap">
				<# if ( 'rich' == data.answerType ) { #>
					<textarea id="answer-{{data.question_id}}-{{data.count}}"></textarea>
				<# } else if ( 'image' == data.answerType ) { #>
					<input type="text" class="answer-text" id="featured_image_textbox" value="{{data.answer}}" placeholder="<?php esc_attr_e( 'Insert image URL', 'quiz-master-next' ); ?>"/>
					<a href="javascript:void(0)" id="set_featured_image"><span class="dashicons dashicons-insert"></span></a>
					<input type="text" class="answer-caption" id="featured_image_caption" value="{{data.caption}}" placeholder="<?php esc_attr_e( 'Image Caption', 'quiz-master-next' ); ?>"/>
				<# } else { #>
					<input type="text" class="answer-text" value="{{data.answer}}" placeholder="<?php esc_attr_e( 'Your answer', 'quiz-master-next' ); ?>"/>
				<# } #>
			</div>
			<# if ( 0 == data.form_type ) { #>
				<# if ( 1 == data.quiz_system || 3 == data.quiz_system ) { #>
					<div class="answer-point-div"><input type="text" class="answer-points" value="{{data.points}}" placeholder="Points"/></div>
				<# } #>
				<# if ( 0 == data.quiz_system || 3 == data.quiz_system ) { #>
					<div class="answer-correct-div"><label class="correct-answer"><input type="checkbox" class="answer-correct" value="1" <# if ( 1 == data.correct ) { #> checked="checked" <# } #>/><?php esc_html_e( 'Correct', 'quiz-master-next' ); ?></label></div>
				<# } #>
			<# } else { #>
					<div class="answer-point-div"><input type="text" class="answer-points" value="{{data.points}}" placeholder="Points"/></div>
			<# } #>
			<?php do_action( 'qsm_admin_single_answer_option_fields' ); ?>
		</div>
	</script>
	<?php
	do_action( 'qsm_admin_after_single_answer_template' );
}
