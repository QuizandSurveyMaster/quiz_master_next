<?php
/**
 * Admin Question Bank page.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns pre-fetched data required on the Question Bank page.
 *
 * @since 10.3.6
 *
 * @return array
 */
function qsm_get_question_bank_page_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}

	global $wpdb, $mlwQuizMasterNext;

	$quiz_results = $wpdb->get_results( 'SELECT quiz_id, quiz_name FROM ' . $wpdb->prefix . 'mlw_quizzes WHERE deleted = 0 ORDER BY quiz_name ASC' );
	$quizzes      = array();
	if ( ! empty( $quiz_results ) ) {
		foreach ( $quiz_results as $quiz ) {
			$quizzes[] = array(
				'id'   => (int) $quiz->quiz_id,
				'name' => $quiz->quiz_name,
			);
		}
	}
	$quiz_ids_from_questions_table = $wpdb->get_results( 'SELECT DISTINCT quiz_id FROM ' . $wpdb->prefix . 'mlw_questions WHERE deleted = 0 ORDER BY quiz_id ASC' );

	$question_categories = $wpdb->get_results( 'SELECT DISTINCT category FROM ' . $wpdb->prefix . 'mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 ORDER BY category ASC', 'ARRAY_A' );
	$enabled              = get_option( 'qsm_multiple_category_enabled' );
	if ( $enabled && 'cancelled' !== $enabled ) {
		$question_categories = array();
		$terms               = get_terms(
			array(
				'taxonomy'   => 'qsm_category',
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$question_categories[] = array(
					'category' => $term->name,
					'cat_id'   => $term->term_id,
				);
			}
		}
	}

	$question_types = $mlwQuizMasterNext->pluginHelper->get_question_type_options();

	$settings   = (array) get_option( 'qmn-settings', array() );
	$per_page   = isset( $settings['items_per_page_question_bank'] ) ? (int) $settings['items_per_page_question_bank'] : 20;
	$per_page   = $per_page > 0 ? $per_page : 20;

	$data = array(
		'quizzes'                         => $quizzes,
		'categories'                      => $question_categories,
		'question_types'                  => is_array( $question_types ) ? $question_types : array(),
		'per_page'                        => $per_page,
		'quiz_ids_of_questions_table'     => $quiz_ids_from_questions_table,
	);

	return $data;
}

/**
 * Renders the Question Bank admin page.
 *
 * @since 10.3.6
 *
 * @return void
 */
function qsm_render_question_bank_page() {
	if ( ! current_user_can( 'edit_qsm_quizzes' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'quiz-master-next' ) );
	}

	$page_data  = qsm_get_question_bank_page_data();
	$quizzes    = $page_data['quizzes'];
	$categories = $page_data['categories'];
	$types      = $page_data['question_types'];
	$max_upload_size      = wp_max_upload_size();
	$max_upload_size_text = size_format( $max_upload_size );
	$sample_csv_url       = trailingslashit( QSM_PLUGIN_URL ) . 'assets/import-questions-sample.csv';
	?>
	<div class="wrap qsm-question-bank-admin">
		<h1><?php esc_html_e( 'QSM Question Bank', 'quiz-master-next' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Add, browse, search, and filter questions across all of your quizzes without leaving the dashboard.', 'quiz-master-next' ); ?></p>

		<div class="qsm-question-bank-actions">
			<button type="button" class="button button-primary qsm-question-bank-create" id="qsm-question-bank-create">
				<?php esc_html_e( 'Add Single Question', 'quiz-master-next' ); ?>
			</button>
			<button type="button" class="button button-primary qsm-bulk-question-import" id="qsm-bulk-question-import">
				<?php esc_html_e( 'Bulk Upload', 'quiz-master-next' ); ?>
			</button>
		</div>
		<form id="qsm-question-bank-filters" class="qsm-question-bank-filters">
			<div class="qsm-filter-group">
				<label for="qsm-question-bank-search" class="screen-reader-text"><?php esc_html_e( 'Search questions', 'quiz-master-next' ); ?></label>
				<input type="search" id="qsm-question-bank-search" name="search" placeholder="<?php echo esc_attr__( 'Search questions…', 'quiz-master-next' ); ?>" />
			</div>
			<div class="qsm-filter-group">
				<label for="qsm-question-bank-quiz"><?php esc_html_e( 'Quiz', 'quiz-master-next' ); ?></label>
				<select id="qsm-question-bank-quiz" name="quiz">
					<option value=""><?php esc_html_e( 'All quizzes', 'quiz-master-next' ); ?></option>
					<?php foreach ( $quizzes as $quiz ) : ?>
						<option value="<?php echo esc_attr( $quiz['id'] ); ?>"><?php echo esc_html( $quiz['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="qsm-filter-group">
				<label for="qsm-question-bank-category"><?php esc_html_e( 'Category', 'quiz-master-next' ); ?></label>
				<select id="qsm-question-bank-category" name="category">
					<option value=""><?php esc_html_e( 'All categories', 'quiz-master-next' ); ?></option>
					<?php foreach ( $categories as $category ) :
						$value = isset( $category['cat_id'] ) && '' !== $category['cat_id'] ? $category['cat_id'] : $category['category'];
						?>
						<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $category['category'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="qsm-filter-group">
				<label for="qsm-question-bank-type"><?php esc_html_e( 'Question type', 'quiz-master-next' ); ?></label>
				<select id="qsm-question-bank-type" name="type">
					<option value=""><?php esc_html_e( 'All question types', 'quiz-master-next' ); ?></option>
					<?php foreach ( $types as $type ) :
						$slug = isset( $type['slug'] ) ? $type['slug'] : '';
						$name = isset( $type['name'] ) ? $type['name'] : '';
						if ( empty( $slug ) ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="qsm-filter-actions">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply filters', 'quiz-master-next' ); ?></button>
				<button type="button" class="button" id="qsm-question-bank-reset">
					<?php esc_html_e( 'Reset', 'quiz-master-next' ); ?>
				</button>
			</div>
		</form>

		<div id="qsm-bulk-upload-panel" class="qsm-bulk-upload-panel" aria-hidden="true">
			<div class="qsm-bulk-upload-header">
				<div>
					<h2><?php esc_html_e( 'Bulk upload questions', 'quiz-master-next' ); ?></h2>
					<p>
						<?php esc_html_e( 'Upload a CSV file that matches the Question Bank schema. Each question row should be followed by its answer rows.', 'quiz-master-next' ); ?>
						<?php if ( $sample_csv_url ) : ?>
							<a href="<?php echo esc_url( $sample_csv_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Download sample CSV', 'quiz-master-next' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			</div>

			<form id="qsm-bulk-upload-form" class="qsm-bulk-upload-form" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'qsm_bulk_upload_questions', 'qsm_bulk_upload_nonce' ); ?>

				<div class="qsm-bulk-upload-dropzone" id="qsm-bulk-upload-dropzone">
					<input type="file" id="qsm-bulk-upload-file" name="bulk_csv" accept=".csv,text/csv" />
					<div class="qsm-dropzone-content">
						<span class="dashicons dashicons-media-spreadsheet"></span>
						<p>
							<strong><?php esc_html_e( 'Drag & drop your CSV here', 'quiz-master-next' ); ?></strong>
							<br />
							<?php esc_html_e( 'or', 'quiz-master-next' ); ?> <button type="button" class="button-link qsm-bulk-upload-browse"><?php esc_html_e( 'select file', 'quiz-master-next' ); ?></button>
						</p>
						<p class="qsm-dropzone-file" id="qsm-bulk-upload-file-label"></p>
					</div>
				</div>

				<div class="qsm-bulk-upload-actions">
					<button type="submit" class="button button-primary" id="qsm-bulk-upload-submit">
						<?php esc_html_e( 'Upload CSV', 'quiz-master-next' ); ?>
					</button>
					<button type="button" class="button" id="qsm-bulk-upload-cancel">
						<?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?>
					</button>
				</div>

				<div class="qsm-bulk-upload-status" id="qsm-bulk-upload-status" role="status" aria-live="polite"></div>
				<div class="qsm-bulk-upload-summary" id="qsm-bulk-upload-summary"></div>
			</form>
		</div>

		<div class="qsm-admin-bulk-actions qsm-question-bank-page">
			<button id="qsm-bulk-delete-question" class="button button-danger"><?php esc_html_e( 'Delete Selected', 'quiz-master-next' ); ?> (<span class="qsm-selected-question-count">0</span>)</button>
			<button id="qsm-bulk-delete-all-question" class="button button-danger"><?php esc_html_e( 'Delete All', 'quiz-master-next' ); ?></button>
		</div>

		<div class="qsm-question-bank-list-wrapper qsm_tab_content">
			<div id="qsm-question-bank-list" class="qsm-question-bank-list questions"></div>
		</div>

		<div class="qsm-question-bank-loader" id="qsm-question-bank-loader" style="display:none;">
			<span class="spinner is-active"></span>
		</div>

		<div class="notice notice-info" id="qsm-question-bank-empty" style="display:none;">
			<p><?php esc_html_e( 'No questions match your filters. Try adjusting your search criteria.', 'quiz-master-next' ); ?></p>
		</div>

		<div class="qsm-question-bank-pagination" id="qsm-question-bank-pagination" style="display:none;">
			<span class="qsm-question-bank-page-info" id="qsm-question-bank-page-info"></span>
			<button type="button" class="button" id="qsm-question-bank-prev">&lsaquo; <?php esc_html_e( 'Previous', 'quiz-master-next' ); ?></button>
			<div class="qsm-question-bank-page-buttons" id="qsm-question-bank-page-buttons"></div>
			<button type="button" class="button" id="qsm-question-bank-next"><?php esc_html_e( 'Next', 'quiz-master-next' ); ?> &rsaquo;</button>
		</div>
	</div>
	<?php
	if ( function_exists( 'qsm_options_questions_tab_template' ) ) {
		add_action( 'admin_footer', 'qsm_options_questions_tab_template', 5 );
	}
}

/**
 * Enqueues assets for the Question Bank admin page.
 *
 * @since 10.3.6
 *
 * @param string $hook Current admin hook suffix.
 *
 * @return void
 */
function qsm_question_bank_admin_assets( $hook ) {
	if ( 'qsm_page_qsm_question_bank' !== $hook ) {
		return;
	}

	global $mlwQuizMasterNext;

	$page_data = qsm_get_question_bank_page_data();

	wp_enqueue_editor();

	wp_enqueue_style( 'qsm_admin_question_css', QSM_PLUGIN_CSS_URL . '/qsm-admin-question.css', array(), $mlwQuizMasterNext->version );

	wp_enqueue_script(
		'qsm_question_bank_admin_js',
		QSM_PLUGIN_JS_URL . '/qsm-question-bank.js',
		array( 'jquery', 'wp-util', 'qsm_admin_js' ),
		$mlwQuizMasterNext->version,
		true
	);

	if ( ! wp_style_is( 'jquer-multiselect-css', 'registered' ) ) {
		wp_register_style( 'jquer-multiselect-css', QSM_PLUGIN_CSS_URL . '/jquery.multiselect.min.css', array(), $mlwQuizMasterNext->version );
	}
	wp_enqueue_style( 'jquer-multiselect-css' );

	if ( ! wp_script_is( 'qsm-jquery-multiselect-js', 'registered' ) ) {
		wp_register_script( 'qsm-jquery-multiselect-js', QSM_PLUGIN_JS_URL . '/jquery.multiselect.min.js', array( 'jquery' ), $mlwQuizMasterNext->version, true );
	}
	wp_enqueue_script( 'qsm-jquery-multiselect-js' );

	if ( ! wp_script_is( 'qsm_admin_js', 'registered' ) ) {
		wp_register_script(
			'qsm_admin_js',
			QSM_PLUGIN_JS_URL . '/qsm-admin.js',
			array( 'jquery', 'backbone', 'underscore', 'wp-util', 'jquery-ui-sortable', 'jquery-touch-punch', 'qsm-jquery-multiselect-js', 'wp-api-request' ),
			$mlwQuizMasterNext->version,
			true
		);
	}
	wp_enqueue_script( 'qsm_admin_js' );

	if ( ! wp_script_is( 'micromodal_script', 'registered' ) ) {
		wp_register_script( 'micromodal_script', QSM_PLUGIN_JS_URL . '/micromodal.min.js', array( 'jquery' ), $mlwQuizMasterNext->version, true );
	}
	wp_enqueue_script( 'micromodal_script' );

	$quiz_nonces = array(
		0 => wp_create_nonce( 'wp_rest_nonce_0_' . get_current_user_id() ),
	);

	foreach ( $page_data['quiz_ids_of_questions_table'] as $quiz_ids ) {
		if ( 0 !== $quiz_ids->quiz_id ) {
			$quiz_nonces[ $quiz_ids->quiz_id ] = wp_create_nonce( 'wp_rest_nonce_' . $quiz_ids->quiz_id . '_' . get_current_user_id() );
		}
	}

	$bulk_upload_nonce = wp_create_nonce( 'qsm_question_bank_import' );
	$sample_csv_url    = trailingslashit( QSM_PLUGIN_URL ) . 'assets/import-questions-sample.csv';
	$max_upload_size   = wp_max_upload_size();

	$localized = array(
		'restUrl'        => esc_url_raw( rest_url( 'quiz-survey-master/v1/bank_questions/0/' ) ),
		'nonce'          => wp_create_nonce( 'wp_rest' ),
		'quizzes'        => $page_data['quizzes'],
		'categories'     => $page_data['categories'],
		'questionTypes'  => $page_data['question_types'],
		'quizNonces'     => $quiz_nonces,
		'editQuizBase'   => admin_url( 'admin.php?page=mlw_quiz_options&tab=questions&quiz_id=' ),
		'perPage'        => $page_data['per_page'],
		'i18n'           => array(
			'questionPlaceholder' => __( 'Untitled question', 'quiz-master-next' ),
			'quizUnknown'         => __( 'Unknown quiz', 'quiz-master-next' ),
			'allQuizzes'          => __( 'All quizzes', 'quiz-master-next' ),
			'allCategories'       => __( 'All categories', 'quiz-master-next' ),
			'allTypes'            => __( 'All question types', 'quiz-master-next' ),
			'loading'             => __( 'Loading questions…', 'quiz-master-next' ),
			'error'               => __( 'Unable to load questions. Please try again.', 'quiz-master-next' ),
			'viewQuiz'            => __( 'Edit in quiz builder', 'quiz-master-next' ),
			'noResults'           => __( 'No questions match your filters. Try adjusting your search criteria.', 'quiz-master-next' ),
			'pageOf'              => __( 'Page %1$s of %2$s', 'quiz-master-next' ),
			'previous'            => __( 'Previous', 'quiz-master-next' ),
			'next'                => __( 'Next', 'quiz-master-next' ),
			'bulkUploadOpened'    => __( 'Bulk upload ready. Select or drop a CSV to continue.', 'quiz-master-next' ),
			'bulkUploadClosed'    => __( 'Bulk upload closed.', 'quiz-master-next' ),
			'bulkUploadNoFile'    => __( 'Please select a CSV file to upload.', 'quiz-master-next' ),
			'bulkUploadInvalid'   => __( 'Only .csv files are supported for bulk import.', 'quiz-master-next' ),
			'bulkUploadTooLarge'  => __( 'The selected file exceeds the maximum upload size.', 'quiz-master-next' ),
			'bulkUploadUploading' => __( 'Uploading… Please keep this tab open.', 'quiz-master-next' ),
			'bulkUploadSuccess'   => __( 'Upload complete! We are processing your questions.', 'quiz-master-next' ),
			'bulkUploadError'     => __( 'Upload failed. Please try again.', 'quiz-master-next' ),
		),
		'bulkUpload'     => array(
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'action'          => 'qsm_question_bank_import',
			'nonce'           => $bulk_upload_nonce,
			'sample'          => esc_url_raw( $sample_csv_url ),
			'maxFileSize'     => $max_upload_size,
			'maxFileSizeText' => size_format( $max_upload_size ),
		),
	);

	wp_localize_script( 'qsm_question_bank_admin_js', 'qsmQuestionBankData', $localized );

	$current_user = wp_get_current_user();
	$rich_editing  = user_can_richedit( $current_user->ID ) ? 'true' : 'false';
	$default_answers = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'default_answers' );

	$qsm_question_settings = array(
		'quizID'                => 0,
		'pages'                 => array(),
		'qpages'                => array(),
		'qsm_user_ve'           => $rich_editing,
		'nonce'                 => wp_create_nonce( 'wp_rest' ),
		'saveNonce'             => wp_create_nonce( 'ajax-nonce-sandy-page' ),
		'unlinkNonce'           => wp_create_nonce( 'ajax-nonce-unlink-question' ),
		'categories'            => $page_data['categories'],
		'form_type'             => 0,
		'quiz_system'           => 3,
		'question_bank_nonce'   => wp_create_nonce( 'delete_question_question_bank_nonce' ),
		'single_question_nonce' => wp_create_nonce( 'delete_question_from_database' ),
		'rest_user_nonce'       => $quiz_nonces[0],
		'default_answers'       => is_array( $default_answers ) ? $default_answers : array(),
	);

	wp_localize_script( 'qsm_admin_js', 'qsmQuestionSettings', $qsm_question_settings );
	wp_localize_script( 'qsm_admin_js', 'qsmTextTabObject', array( 'quiz_id' => 0 ) );
	wp_localize_script( 'qsm_admin_js', 'qsmQuestionBankAdapter', array( 'disableAutoLoad' => true ) );

}
add_action( 'admin_enqueue_scripts', 'qsm_question_bank_admin_assets' );

add_action( 'admin_footer', 'qsm_questions_bank_question_editor' );
function qsm_questions_bank_question_editor() {
	if ( ! isset( $_GET['page'] ) || 'qsm_question_bank' !== $_GET['page'] ) {
		return;
	}

	global $wpdb, $mlwQuizMasterNext;
	$quiz_id         = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$user_id         = get_current_user_id();
	$form_type       = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'form_type' );
	$quiz_system     = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'system' );

	// Load Question Types.
	$question_types             = $mlwQuizMasterNext->pluginHelper->get_question_type_options();
	$question_types_categorized = $mlwQuizMasterNext->pluginHelper->categorize_question_types();

	?>
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
					<input type="hidden" name="edit_quiz_id" id="edit_quiz_id" value="">
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
										$polar_class       = $polar_question_use   = '';
										$description_arr[] = array(
											'question_type_id' => '13',
											'description' => __( 'Use points based grading system for Polar questions.', 'quiz-master-next' ),
										);
									} else {
										$polar_class        = 'qsm_show_question_type_13';
										$polar_question_use = ',13';
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
											?>
											<p id="question_type_<?php echo esc_attr( $question_type_id ); ?>_description" class="question-type-description"><?php echo esc_attr( $value['description'] ); ?></p>
											<?php
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
									$show_correct_answer_info     = '';
									$show_autofill                = '';
									$show_case_sensitive          = '';
									$show_limit_text              = '';
									$show_limit_multiple_response = '';
									$show_file_upload_type        = '';
									$show_file_upload_limit       = '';
									$placeholder_text             = '';
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
									$advanced_question_option = array(
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
											'priority' => '1',
											'show'     => '0,1,2,4,10',
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
									$advanced_question_option = apply_filters( 'qsm_question_advanced_option', $advanced_question_option );
									$keys                     = array_column( $advanced_question_option, 'priority' );
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
																	<optgroup label="<?php echo esc_attr( $category_name ); ?>">
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
													$simple_question_option = array(
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
													$simple_question_option = apply_filters( 'qsm_question_format_option', $simple_question_option );
													$keys                   = array_column( $simple_question_option, 'priority' );
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
											$category_question_option = apply_filters( 'qsm_question_category_option', $category_question_option );
											$keys                     = array_column( $category_question_option, 'priority' );
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
	<?php
}

add_action( 'wp_ajax_qsm_question_bank_import', 'qsm_question_bank_import' );

/**
 * Processes bulk Question Bank CSV uploads.
 *
 * @since 10.4.0
 * @return void
 */
function qsm_question_bank_import() {
	if ( ! current_user_can( 'edit_qsm_quizzes' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'You are not allowed to import questions.', 'quiz-master-next' ),
			),
			403
		);
	}

	check_ajax_referer( 'qsm_question_bank_import' );

	if ( empty( $_FILES['bulk_csv'] ) || ! isset( $_FILES['bulk_csv']['tmp_name'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'No CSV file was uploaded.', 'quiz-master-next' ),
			)
		);
	}

	$raw_quiz = isset( $_POST['bulk_quiz'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_quiz'] ) ) : '';
	$quiz_id  = absint( $raw_quiz );

	$file_details = $_FILES['bulk_csv'];
	$max_size     = wp_max_upload_size();
	if ( ! empty( $file_details['size'] ) && $file_details['size'] > $max_size ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					__( 'The selected file exceeds the maximum upload size of %s.', 'quiz-master-next' ),
					size_format( $max_size )
				),
			)
		);
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

	$uploaded_file = wp_handle_upload(
		$file_details,
		array(
			'test_form' => false,
			'mimes'     => array( 'csv' => 'text/csv', 'txt' => 'text/plain' ),
		)
	);

	if ( isset( $uploaded_file['error'] ) ) {
		wp_send_json_error(
			array(
				'message' => sprintf( __( 'Upload failed: %s', 'quiz-master-next' ), $uploaded_file['error'] ),
			)
		);
	}

	$file_path = $uploaded_file['file'];
	$result    = qsm_question_bank_process_csv( $file_path, $quiz_id );

	if ( file_exists( $file_path ) ) {
		wp_delete_file( $file_path );
	}

	if ( ! $result ) {
		wp_send_json_error(
			array(
				'message' => __( 'Unable to parse the uploaded CSV file.', 'quiz-master-next' ),
			)
		);
	}

	$summary      = isset( $result['summary'] ) ? $result['summary'] : array();
	$summary     += array(
		'questions_found'    => 0,
		'questions_imported' => 0,
		'questions_failed'   => 0,
		'errors'             => array(),
	);
	$message      = isset( $result['message'] ) ? $result['message'] : '';

	$response = array(
		'message' => $message,
		'summary' => $summary,
	);

	if ( $summary['questions_imported'] > 0 ) {
		wp_send_json_success( $response );
	}

	wp_send_json_error( $response );
}

/**
 * Reads the CSV file and creates questions.
 *
 * @since 10.4.0
 * @param string $file_path CSV file path.
 * @param int    $quiz_id   Quiz to assign questions to.
 * @return array|null
 */
function qsm_question_bank_process_csv( $file_path, $quiz_id ) {
	if ( ! file_exists( $file_path ) ) {
		return null;
	}

	$handle = fopen( $file_path, 'r' );
	if ( false === $handle ) {
		return null;
	}

	$header_row = fgetcsv( $handle );
	if ( empty( $header_row ) ) {
		fclose( $handle );
		return null;
	}

	$header_map    = qsm_question_bank_normalize_headers( $header_row );
	$questions     = array();
	$errors        = array();
	$line          = 1;
	$is_flat_csv   = qsm_question_bank_is_flat_format( $header_map );
	$current       = null;

	while ( ( $row = fgetcsv( $handle ) ) !== false ) {
		$line++;
		if ( qsm_question_bank_row_is_empty( $row ) ) {
			continue;
		}

		if ( $is_flat_csv ) {
			$question = qsm_question_bank_build_flat_question( $row, $header_map, $line );
			if ( is_wp_error( $question ) ) {
				$errors[] = $question->get_error_message();
				continue;
			}
			$questions[] = $question;
			continue;
		}

		$item_type = strtolower( qsm_question_bank_get_value( $row, $header_map, 'item_type' ) );
		if ( 'question' === $item_type ) {
			if ( $current ) {
				$questions[] = $current;
			}
			$current = qsm_question_bank_initialize_question( $row, $header_map, $line );
		} elseif ( 'answer' === $item_type ) {
			if ( ! $current ) {
				$errors[] = sprintf( __( 'Line %1$d: Answer row found before any question row.', 'quiz-master-next' ), $line );
				continue;
			}
			$answer = qsm_question_bank_build_answer( $row, $header_map, $line );
			if ( $answer ) {
				$current['answers'][] = $answer;
			}
		} else {
			$errors[] = sprintf( __( 'Line %1$d: Unknown item type "%2$s".', 'quiz-master-next' ), $line, $item_type );
		}
	}

	if ( ! $is_flat_csv && $current ) {
		$questions[] = $current;
	}

	fclose( $handle );

	if ( empty( $questions ) ) {
		return array(
			'message' => __( 'No questions were found in the uploaded file.', 'quiz-master-next' ),
			'summary' => array(
				'questions_found'    => 0,
				'questions_imported' => 0,
				'questions_failed'   => 0,
				'errors'             => $errors,
			),
		);
	}

	$types_map    = qsm_question_bank_question_types_map();
	$created      = 0;
	$failed       = 0;
	$imported_ids = array();
	foreach ( $questions as $question_index => $question ) {
		$type_value = qsm_question_bank_map_question_type( $question['question_type'], $types_map );
		if ( '' === $type_value ) {
			$failed++;
			$errors[] = sprintf( __( 'Question %1$d: Unable to determine question type.', 'quiz-master-next' ), $question_index + 1 );
			continue;
		}
		$question['question_type'] = $type_value;
		$creation_result           = qsm_question_bank_create_question( $question, $quiz_id );
		if ( is_wp_error( $creation_result ) ) {
			$failed++;
			$errors[] = $creation_result->get_error_message();
		} else {
			$created++;
			$imported_ids[] = intval( $creation_result );
		}
	}

	$total_questions = count( $questions );
	/* translators: %d: number of imported questions */
	$message = sprintf( _n( '%d question imported.', '%d questions imported.', $created, 'quiz-master-next' ), $created );
	if ( $failed > 0 ) {
		/* translators: %d: number of failed questions */
		$message .= ' ' . sprintf( _n( '%d question failed.', '%d questions failed.', $failed, 'quiz-master-next' ), $failed );
	}

	return array(
		'message'      => $message,
		'summary'      => array(
			'questions_found'    => $total_questions,
			'questions_imported' => $created,
			'questions_failed'   => $failed,
			'errors'             => $errors,
		),
		'imported_ids' => $imported_ids,
	);
}

/**
 * Creates a single question from parsed CSV data.
 *
 * @since 10.4.0
 * @param array $question Parsed question data.
 * @param int   $quiz_id  Quiz assignment.
 * @return int|WP_Error Question ID on success.
 */
function qsm_question_bank_create_question( $question, $quiz_id ) {
	global $mlwQuizMasterNext;

	$category_names = isset( $question['categories'] ) ? $question['categories'] : array();
	$category_ids   = qsm_question_bank_ensure_categories( $category_names );
	$primary_cat    = ! empty( $category_names ) ? $category_names[0] : '';

	$settings = array(
		'question_title'          => $question['question_title'],
		'required'                => $question['required'],
		'answerEditor'            => $question['answer_editor'],
		'featureImageSrc'         => $question['feature_image_src'],
		'matchAnswer'             => $question['match_answer'],
		'case_sensitive'          => $question['case_sensitive'],
		'answer_columns'          => $question['answer_columns'],
		'image_size-width'        => $question['image_width'],
		'image_size-height'       => $question['image_height'],
		'autofill'                => $question['autofill'],
		'limit_text'              => $question['text_limit'],
		'limit_multiple_response' => $question['limit_multiple_response'],
		'file_upload_limit'       => $question['file_upload_limit'],
		'file_upload_type'        => $question['file_upload_type'],
		'require_all_rows'        => $question['require_all_rows'],
	);

	$question_data = array(
		'quiz_id'         => $quiz_id,
		'type'            => $question['question_type'],
		'name'            => $question['question_description'],
		'answer_info'     => $question['answer_info'],
		'comments'        => $question['comments'],
		'hint'            => $question['hint'],
		'order'           => 1,
		'category'        => $primary_cat,
		'multicategories' => $category_ids,
	);

	try {
		$question_id = QSM_Questions::create_question( $question_data, $question['answers'], $settings );
		return $question_id;
	} catch ( Exception $exception ) {
		$mlwQuizMasterNext->log_manager->add( 'Question Bank Import', $exception->getMessage(), 0, 'error' );
		return new WP_Error( 'qsm_question_import_failed', $exception->getMessage() );
	}
}

/**
 * Ensures categories exist and returns their IDs.
 *
 * @since 10.4.0
 * @param array $categories Category names.
 * @return array
 */
function qsm_question_bank_ensure_categories( $categories ) {
	$category_ids = array();
	if ( empty( $categories ) ) {
		return $category_ids;
	}

	foreach ( $categories as $category_name ) {
		$category_name = trim( $category_name );
		if ( '' === $category_name ) {
			continue;
		}
		$term = term_exists( $category_name, 'qsm_category' );
		if ( ! $term ) {
			$term = wp_insert_term( $category_name, 'qsm_category' );
		}
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$category_ids[] = intval( $term['term_id'] );
	}

	return array_unique( $category_ids );
}

/**
 * Initializes the base question structure from a CSV row.
 *
 * @since 10.4.0
 * @param array $row        CSV row.
 * @param array $header_map Header map.
 * @param int   $line       Line number.
 * @return array
 */
function qsm_question_bank_initialize_question( $row, $header_map, $line ) {
	$question_title       = qsm_question_bank_get_value( $row, $header_map, 'question_title' );
	$question_description = qsm_question_bank_get_value( $row, $header_map, 'question_description', $question_title );
	$categories_string    = qsm_question_bank_get_value( $row, $header_map, 'categories' );
	$categories           = qsm_question_bank_parse_categories( $categories_string );

	return array(
		'line'                 => $line,
		'question_title'       => $question_title,
		'question_description' => $question_description,
		'question_type'        => qsm_question_bank_get_value( $row, $header_map, 'question_type_new' ),
		'answer_info'          => qsm_question_bank_get_value( $row, $header_map, 'question_answer_info' ),
		'comments'             => qsm_question_bank_get_value( $row, $header_map, 'comments' ),
		'hint'                 => qsm_question_bank_get_value( $row, $header_map, 'hints' ),
		'required'             => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'required' ), true ),
		'answer_editor'        => qsm_question_bank_get_value( $row, $header_map, 'answer_editor', 'text' ),
		'feature_image_src'    => qsm_question_bank_get_value( $row, $header_map, 'feature_image_src' ),
		'match_answer'         => qsm_question_bank_get_value( $row, $header_map, 'match_answer' ),
		'case_sensitive'       => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'case_sensitive' ) ),
		'answer_columns'       => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'answer_columns' ), 1 ),
		'image_width'          => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'image_size_width' ), '' ),
		'image_height'         => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'image_size_height' ), '' ),
		'autofill'             => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'autofill' ) ),
		'text_limit'           => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'text_limit' ), 0 ),
		'limit_multiple_response' => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'limit_multiple_response' ), 0 ),
		'file_upload_limit'       => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'file_upload_limit' ), 4 ),
		'file_upload_type'        => qsm_question_bank_get_value( $row, $header_map, 'file_upload_type', 'image,application/pdf' ),
		'require_all_rows'        => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'require_all_rows' ) ),
		'categories'              => $categories,
		'answers'                 => array(),
	);
}

/**
 * Builds an answer entry from CSV data.
 *
 * @since 10.4.0
 * @param array $row        CSV row.
 * @param array $header_map Header map.
 * @param int   $line       Line number.
 * @return array|null
 */
function qsm_question_bank_build_answer( $row, $header_map, $line ) {
	$answer_text = qsm_question_bank_get_value( $row, $header_map, 'answer_text' );
	if ( '' === $answer_text ) {
		return null;
	}

	$points  = qsm_question_bank_parse_float( qsm_question_bank_get_value( $row, $header_map, 'answer_point' ) );
	$correct = qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'answer_correct_incorrect' ) ) ? 1 : 0;
	$answer  = array(
		0 => $answer_text,
		1 => $points,
		2 => $correct,
	);

	$caption = qsm_question_bank_get_value( $row, $header_map, 'answer_caption' );
	if ( '' !== $caption ) {
		$answer[3] = $caption;
	}
	$label = qsm_question_bank_get_value( $row, $header_map, 'answer_label' );
	if ( '' !== $label ) {
		$answer[4] = $label;
	}

	return $answer;
}

/**
 * Determines if the CSV follows the flat "one row per question" format.
 *
 * @since 10.4.0
 * @param array $header_map Normalized header map.
 * @return bool
 */
function qsm_question_bank_is_flat_format( $header_map ) {
	if ( isset( $header_map['item_type'] ) ) {
		return false;
	}
	$has_question_column = isset( $header_map['question'] ) || isset( $header_map['question_title'] );
	if ( ! $has_question_column ) {
		return false;
	}
	foreach ( $header_map as $key => $index ) {
		if ( 0 === strpos( $key, 'option' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Builds a question entry from the flat CSV structure.
 *
 * @since 10.4.0
 * @param array $row        CSV row.
 * @param array $header_map Header map.
 * @param int   $line       Current line number.
 * @return array|WP_Error
 */
function qsm_question_bank_build_flat_question( $row, $header_map, $line ) {
	$question_title = qsm_question_bank_get_value( $row, $header_map, 'question' );
	if ( '' === $question_title ) {
		$question_title = qsm_question_bank_get_value( $row, $header_map, 'question_title' );
	}
	if ( '' === $question_title ) {
		return new WP_Error( 'qsm_question_bank_missing_title', sprintf( __( 'Line %1$d: Question text is required.', 'quiz-master-next' ), $line ) );
	}

	$question_description = qsm_question_bank_get_value( $row, $header_map, 'description', $question_title );
	$question_type        = qsm_question_bank_get_value( $row, $header_map, 'question_type', qsm_question_bank_get_value( $row, $header_map, 'question_type_new' ) );
	$hint                 = qsm_question_bank_get_value( $row, $header_map, 'hint', qsm_question_bank_get_value( $row, $header_map, 'hints' ) );
	$categories_string    = qsm_question_bank_get_value( $row, $header_map, 'category', qsm_question_bank_get_value( $row, $header_map, 'categories' ) );
	$categories           = qsm_question_bank_parse_categories( $categories_string );
	$limit_multiple       = qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'answer_limit' ), qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'limit_multiple_response' ), 0 ) );

	$question = array(
		'line'                    => $line,
		'question_title'          => $question_title,
		'question_description'    => $question_description,
		'question_type'           => $question_type,
		'answer_info'             => qsm_question_bank_get_value( $row, $header_map, 'question_answer_info' ),
		'comments'                => qsm_question_bank_get_value( $row, $header_map, 'comments' ),
		'hint'                    => $hint,
		'required'                => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'required' ), true ),
		'answer_editor'           => qsm_question_bank_get_value( $row, $header_map, 'answer_editor', 'text' ),
		'feature_image_src'       => qsm_question_bank_get_value( $row, $header_map, 'feature_image_src' ),
		'match_answer'            => qsm_question_bank_get_value( $row, $header_map, 'match_answer' ),
		'case_sensitive'          => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'case_sensitive' ) ),
		'answer_columns'          => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'answer_columns' ), 1 ),
		'image_width'             => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'image_size_width' ), '' ),
		'image_height'            => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'image_size_height' ), '' ),
		'autofill'                => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'autofill' ) ),
		'text_limit'              => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'text_limit' ), 0 ),
		'limit_multiple_response' => $limit_multiple,
		'file_upload_limit'       => qsm_question_bank_parse_int( qsm_question_bank_get_value( $row, $header_map, 'file_upload_limit' ), 4 ),
		'file_upload_type'        => qsm_question_bank_get_value( $row, $header_map, 'file_upload_type', 'image,application/pdf' ),
		'require_all_rows'        => qsm_question_bank_parse_boolean( qsm_question_bank_get_value( $row, $header_map, 'require_all_rows' ) ),
		'categories'              => $categories,
		'answers'                 => array(),
	);

	$answers = qsm_question_bank_collect_flat_answers( $row, $header_map, $line, $question_type );
	if ( is_wp_error( $answers ) ) {
		return $answers;
	}
	$question['answers'] = $answers;

	return $question;
}

/**
 * Builds answers from the flat CSV option columns.
 *
 * @since 10.4.0
 * @param array  $row           CSV row.
 * @param array  $header_map    Header map.
 * @param int    $line          Current line number.
 * @param string $question_type Raw question type value.
 * @return array|WP_Error
 */
function qsm_question_bank_collect_flat_answers( $row, $header_map, $line, $question_type ) {
	$options = array();
	for ( $i = 1; $i <= 20; $i++ ) {
		$option_key = 'option_' . $i;
		$value      = qsm_question_bank_get_value( $row, $header_map, $option_key );
		if ( '' === $value ) {
			$option_key = 'option' . $i;
			$value      = qsm_question_bank_get_value( $row, $header_map, $option_key );
		}
		if ( '' === $value ) {
			continue;
		}
		$options[] = array(
			'value' => $value,
			'index' => $i,
		);
	}

	$correct_raw     = qsm_question_bank_get_value( $row, $header_map, 'correct_option' );
	$correct_tokens  = qsm_question_bank_parse_delimited_list( $correct_raw );
	$correct_numbers = array();
	$correct_labels  = array();
	foreach ( $correct_tokens as $token ) {
		if ( is_numeric( $token ) ) {
			$correct_numbers[] = (int) $token;
			continue;
		}
		$correct_labels[] = strtolower( $token );
	}

	$points = qsm_question_bank_parse_float( qsm_question_bank_get_value( $row, $header_map, 'points' ) );
	if ( $points <= 0 ) {
		$points = 1;
	}

	$answers = array();
	foreach ( $options as $option ) {
		$answer_points = $points;
		$label_key     = 'option_label_' . $option['index'];
		$option_label  = qsm_question_bank_get_value( $row, $header_map, $label_key );
		$is_correct    = in_array( $option['index'], $correct_numbers, true ) || in_array( strtolower( $option['value'] ), $correct_labels, true );
		$answers[]     = array(
			0 => $option['value'],
			1 => $answer_points,
			2 => $is_correct ? 1 : 0,
			3 => '',
			4 => $option_label,
		);
	}

	return $answers;
}

/**
 * Splits a delimited string into tokens.
 *
 * @since 10.4.0
 * @param string $value Raw input string.
 * @return array
 */
function qsm_question_bank_parse_delimited_list( $value ) {
	if ( '' === $value ) {
		return array();
	}
	$separators = array( ',', '|', ';' );
	$normalized = str_replace( $separators, ',', strtolower( $value ) );
	$parts      = array_filter( array_map( 'trim', explode( ',', $normalized ) ) );
	return $parts;
}

/**
 * Normalizes header labels into keys.
 *
 * @since 10.4.0
 * @param array $headers Raw header row.
 * @return array
 */
function qsm_question_bank_normalize_headers( $headers ) {
	$map = array();
	foreach ( $headers as $index => $label ) {
		$normalized = qsm_question_bank_normalize_label( $label );
		$map[ $normalized ] = $index;
	}
	return $map;
}

/**
 * Returns normalized label key.
 *
 * @since 10.4.0
 * @param string $label Header value.
 * @return string
 */
function qsm_question_bank_normalize_label( $label ) {
	$label = strtolower( trim( $label ) );
	$label = preg_replace( '/[^a-z0-9]+/', '_', $label );
	return trim( $label, '_' );
}

/**
 * Fetches field value from CSV row.
 *
 * @since 10.4.0
 * @param array  $row        CSV row.
 * @param array  $header_map Header map.
 * @param string $key        Field key.
 * @param mixed  $default    Default fallback.
 * @return string
 */
function qsm_question_bank_get_value( $row, $header_map, $key, $default = '' ) {
	if ( isset( $header_map[ $key ] ) && isset( $row[ $header_map[ $key ] ] ) ) {
		return trim( $row[ $header_map[ $key ] ] );
	}
	return $default;
}

/**
 * Checks if CSV row is empty.
 *
 * @since 10.4.0
 * @param array $row CSV row.
 * @return bool
 */
function qsm_question_bank_row_is_empty( $row ) {
	if ( empty( $row ) ) {
		return true;
	}
	$joined = implode( '', array_map( 'trim', $row ) );
	return '' === $joined;
}

/**
 * Parses boolean-ish values.
 *
 * @since 10.4.0
 * @param string $value   Raw value.
 * @param bool   $default Default fallback.
 * @return bool
 */
function qsm_question_bank_parse_boolean( $value, $default = false ) {
	if ( '' === $value ) {
		return $default;
	}
	$value = strtolower( trim( $value ) );
	if ( in_array( $value, array( '1', 'yes', 'true', 'y', 'required', 'correct' ), true ) ) {
		return true;
	}
	if ( in_array( $value, array( '0', 'no', 'false', 'n', 'optional', 'incorrect' ), true ) ) {
		return false;
	}
	return $default;
}

/**
 * Parses integer values with fallback.
 *
 * @since 10.4.0
 * @param string    $value   Raw value.
 * @param int|mixed $default Default fallback.
 * @return int|mixed
 */
function qsm_question_bank_parse_int( $value, $default = 0 ) {
	if ( '' === $value ) {
		return $default;
	}
	return (int) $value;
}

/**
 * Parses float values with fallback.
 *
 * @since 10.4.0
 * @param string $value Raw value.
 * @return float
 */
function qsm_question_bank_parse_float( $value ) {
	if ( '' === $value ) {
		return 0.0;
	}
	return (float) $value;
}

/**
 * Splits category string into array.
 *
 * @since 10.4.0
 * @param string $value Raw category column.
 * @return array
 */
function qsm_question_bank_parse_categories( $value ) {
	if ( '' === $value ) {
		return array();
	}
	$separators = array( ',', '|', ';' );
	$normalized = str_replace( $separators, ',', $value );
	$parts      = array_map( 'trim', explode( ',', $normalized ) );
	return array_filter( $parts );
}

/**
 * Returns map of available question types.
 *
 * @since 10.4.0
 * @return array
 */
function qsm_question_bank_question_types_map() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	global $mlwQuizMasterNext;
	$cache = array(
		'by_name' => array(),
		'by_slug' => array(),
	);
	if ( ! isset( $mlwQuizMasterNext->pluginHelper ) ) {
		return $cache;
	}
	$types = $mlwQuizMasterNext->pluginHelper->get_question_type_options();
	if ( ! is_array( $types ) ) {
		return $cache;
	}
	foreach ( $types as $type ) {
		$slug = isset( $type['slug'] ) ? (string) $type['slug'] : '';
		$name = isset( $type['name'] ) ? strtolower( $type['name'] ) : '';
		if ( '' !== $slug ) {
			$cache['by_slug'][ $slug ] = true;
		}
		if ( '' !== $name ) {
			$cache['by_name'][ $name ] = $slug;
		}
	}
	return $cache;
}

/**
 * Maps CSV value to valid question type slug.
 *
 * @since 10.4.0
 * @param string $value    Raw CSV type value.
 * @param array  $types_map Prepared map.
 * @return string
 */
function qsm_question_bank_map_question_type( $value, $types_map ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	$lower = strtolower( $value );
	if ( isset( $types_map['by_name'][ $lower ] ) ) {
		return (string) $types_map['by_name'][ $lower ];
	}
	if ( isset( $types_map['by_slug'][ $value ] ) ) {
		return (string) $value;
	}
	if ( is_numeric( $value ) && isset( $types_map['by_slug'][ (string) intval( $value ) ] ) ) {
		return (string) intval( $value );
	}
	return '';
}