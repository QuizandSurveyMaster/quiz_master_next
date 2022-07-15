<?php
/**
 * Creates the results page tab when editing quizzes.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Results Page tab to the Quiz Settings page.
 *
 * @since 6.1.0
 */
function qsm_options_results_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Results Pages', 'quiz-master-next' ), 'qsm_options_results_tab_content', 'results-pages' );
}
add_action( 'plugins_loaded', 'qsm_options_results_tab', 5 );

/**
 * Adds the Results page content to the Results tab.
 *
 * @since 6.1.0
 */
function qsm_options_results_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : '';
	$user_id = get_current_user_id();
	$js_data = array(
		'quizID'          => $quiz_id,
		'nonce'           => wp_create_nonce( 'wp_rest' ),
		'rest_user_nonce' => wp_create_nonce( 'wp_rest_nonce_' . $quiz_id . '_' . $user_id ),
	);
	wp_localize_script( 'qsm_admin_js', 'qsmResultsObject', $js_data );
	?>

<!-- Results Page Section -->
<section class="qsm-quiz-result-tab" style="margin-top: 15px;">
	<button class="save-pages button-primary"><?php esc_html_e( 'Save Results Pages', 'quiz-master-next' ); ?></button>
	<button class="add-new-page button"><?php esc_html_e( 'Add New Results Page', 'quiz-master-next' ); ?></button>
	<a class="qsm-show-all-variable-text" href="javascript:void(0)"><?php esc_html_e( 'Insert Template Variables', 'quiz-master-next' ); ?> <span class="dashicons dashicons-upload"></span></a>
	<div id="results-pages">
		<div style="margin-bottom: 30px;margin-top: 35px;" class="qsm-spinner-loader"></div>
	</div>
	<button class="save-pages button-primary"><?php esc_html_e( 'Save Results Pages', 'quiz-master-next' ); ?></button>
	<button class="add-new-page button"><?php esc_html_e( 'Add New Results Page', 'quiz-master-next' ); ?></button>
</section>

<!-- Templates -->
<?php add_action('admin_footer', 'qsm_options_results_tab_template'); ?>
<!--Template popup-->
<div class="qsm-popup qsm-popup-slide" id="show-all-variable" aria-hidden="false">
	<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
		<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-3-title">
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
				$variable_list                             = qsm_text_template_variable_list();
				$variable_list['Core']['%POLL_RESULTS_X%'] = __( 'X = Question ID Note: only supported for multiple choice answers', 'quiz-master-next' );
				$variable_list['Core']['%RESULT_ID%']      = __( 'Show result id', 'quiz-master-next' );
				//filter to add or remove variables from variable list for pdf tab
				$variable_list = apply_filters( 'qsm_text_variable_list_result', $variable_list );
				if ( $variable_list ) {
					//sort $variable list for backward compatibility
					foreach ( $variable_list as $variable_name => $variable_value ) {
						if ( ! is_array( $variable_value ) ) {
							$variable_list['Other Variables'][ $variable_name ] = $variable_value ;
						}
					}
					foreach ( $variable_list as $category_name => $category_variables ) {
						//check if the $category_variables is an array for backward compatibility
						if ( is_array( $category_variables ) ) {
							?>
							<div><h2><?php echo esc_attr( $category_name );?></h2></div>
							<?php
							foreach ( $category_variables as $variable_key => $variable ) {
								?>
								<div class="popup-template-span-wrap">
									<span class="qsm-text-template-span">
										<span class="button button-default template-variable"><?php echo esc_attr( $variable_key ); ?></span>
										<span class="button click-to-copy">Click to Copy</span>
										<span class="temp-var-seperator">
											<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
												<span class="qsm-tooltips"><?php echo esc_attr( $variable ); ?></span>
											</span>
										</span>
									</span>
								</div>
							<?php
							}
						}
					}
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
/**
 * Adds the Results page templates to the Results tab.
 *
 * @since 7.3.5
 */
function qsm_options_results_tab_template(){
	global $wpdb;
	$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : '';
	$categories = array();
	$enabled    = get_option( 'qsm_multiple_category_enabled' );
	if ( $enabled && 'cancelled' !== $enabled ) {
		$query = $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}terms WHERE term_id IN ( SELECT DISTINCT term_id FROM {$wpdb->prefix}mlw_question_terms WHERE quiz_id = %d ) ORDER BY name ASC", $quiz_id );
	} else {
		$query = $wpdb->prepare( "SELECT DISTINCT category FROM {$wpdb->prefix}mlw_questions WHERE category <> '' AND quiz_id = %d", $quiz_id );
	}
	$categories = $wpdb->get_results( $query, ARRAY_N );
	?>
	<script type="text/template" id="tmpl-results-page">
		<div class="results-page">
				<header class="results-page-header">
					<div><button class="delete-page-button"><span class="dashicons dashicons-trash"></span></button></div>
				</header>
				<main class="results-page-content">
					<div class="results-page-when">
						<div class="results-page-content-header">
							<h4><?php esc_html_e( 'When...', 'quiz-master-next' ); ?></h4>
							<p><?php esc_html_e( 'Set conditions for when this page should be shown. Leave empty to set this as the default page.', 'quiz-master-next' ); ?></p>
						</div>
						<div class="results-page-when-conditions">
							<!-- Conditions go here. Review template below. -->
						</div>
						<button class="new-condition button"><?php esc_html_e( 'Add additional condition', 'quiz-master-next' ); ?></button>
					</div>
					<div class="results-page-show">
						<div class="results-page-content-header">
							<h4><?php esc_html_e( '...Show', 'quiz-master-next' ); ?></h4>
							<p><?php esc_html_e( 'Create the results page that should be shown when the conditions are met.', 'quiz-master-next' ); ?></p>
						</div>
						<textarea id="results-page-{{ data.id }}" class="results-page-template">{{{ data.page }}}</textarea>
						<p><?php esc_html_e( 'Or, redirect the user by entering the URL below:', 'quiz-master-next' ); ?></p>
						<input type="text" class="results-page-redirect" value="<# if ( data.redirect ) { #>{{ data.redirect }}<# } #>">
					</div>
				</main>
			</div>
		</script>

	<script type="text/template" id="tmpl-results-page-condition">
		<div class="results-page-condition">
				<button class="delete-condition-button"><span class="dashicons dashicons-trash"></span></button>
				<select class="results-page-condition-category">
					<option value="" <# if (data.category == '') { #>selected<# } #>><?php esc_html_e( 'Quiz', 'quiz-master-next' ); ?></option>
					<option value="" disabled><?php esc_html_e( '---Select Category---', 'quiz-master-next' ); ?></option>
					<?php if ( ! empty( $categories ) ) { ?>
						<?php foreach ( $categories as $cat ) { ?>
						<option value="<?php echo esc_attr( $cat[0] ); ?>" <# if (data.category == '<?php echo esc_attr( $cat[0] ); ?>') { #>selected<# } #>><?php echo esc_attr( $cat[0] ); ?></option>
						<?php } ?>
					<?php } else { ?>
						<option value="" disabled><?php esc_html_e( 'No Categories Available', 'quiz-master-next' ); ?></option>
					<?php } ?>
				</select>

				<select class="results-page-condition-criteria">
					<option value="points" <# if (data.criteria == 'points') { #>selected<# } #>><?php esc_html_e( 'Total points earned', 'quiz-master-next' ); ?></option>
					<option value="score" <# if (data.criteria == 'score') { #>selected<# } #>><?php esc_html_e( 'Correct score percentage', 'quiz-master-next' ); ?></option>
					<?php do_action( 'qsm_results_page_condition_criteria' ); ?>
				</select>
				<?php do_action( 'qsm_results_page_extra_condition_fields' ); ?>
				<select class="results-page-condition-operator">
					<option class="default_operator" value="equal" <# if (data.operator == 'equal') { #>selected<# } #>><?php esc_html_e( 'is equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="not-equal" <# if (data.operator == 'not-equal') { #>selected<# } #>><?php esc_html_e( 'is not equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="greater-equal" <# if (data.operator == 'greater-equal') { #>selected<# } #>><?php esc_html_e( 'is greater than or equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="greater" <# if (data.operator == 'greater') { #>selected<# } #>><?php esc_html_e( 'is greater than', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="less-equal" <# if (data.operator == 'less-equal') { #>selected<# } #>><?php esc_html_e( 'is less than or equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="less" <# if (data.operator == 'less') { #>selected<# } #>><?php esc_html_e( 'is less than', 'quiz-master-next' ); ?></option>
					<?php do_action( 'qsm_results_page_condition_operator' ); ?>
				</select>
				<input type="text" class="results-page-condition-value condition-default-value" value="{{ data.value }}">
				<?php do_action( 'qsm_results_page_condition_value' ); ?>
			</div>
		</script>
	<?php
}
?>