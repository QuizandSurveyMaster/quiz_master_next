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
add_action( 'init', 'qsm_options_results_tab', 5 );

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
	$template_from_script = qsm_get_parsing_script_data( 'templates.json' );
	$template_from_script = apply_filters( 'qsm_result_templates_list_before', $template_from_script, $quiz_id );
	$table_name = $wpdb->prefix . 'mlw_quiz_output_templates';
	$temlpate_sql = "SELECT * FROM {$table_name} WHERE template_type='result'";
	$my_result_templates = $wpdb->get_results($temlpate_sql);

	$qsm_dependency_list = qsm_get_dependency_plugin_list();

	$js_data = array(
		'quizID'            => $quiz_id,
		'nonce'             => wp_create_nonce( 'wp_rest' ),
		'rest_user_nonce'   => wp_create_nonce( 'wp_rest_nonce_' . $quiz_id . '_' . $user_id ),
		'my_tmpl_data'      => $my_result_templates,
		'script_tmpl'       => $template_from_script,
		'add_tmpl_nonce'    => wp_create_nonce( 'qsm_add_template' ),
		'remove_tmpl_nonce' => wp_create_nonce( 'qsm_remove_template' ),
		'dependency'        => $qsm_dependency_list,
		'required_addons'   => __('Required Add-ons', 'quiz-master-next'),
		'used_addons'       => __('Addons :', 'quiz-master-next'),
	);
	wp_localize_script( 'qsm_admin_js', 'qsmResultsObject', $js_data );
	do_action( 'qsm_options_results_tab_content_before' );
	?>

<!-- Results Page Section -->
<section class="qsm-quiz-result-tab" style="margin-top: 15px;">
	<div id="results-pages">
		<div style="margin-bottom: 30px;margin-top: 35px;" class="qsm-spinner-loader"></div>
	</div>
	<button class="add-new-page button"><?php esc_html_e( 'Add New Results Page', 'quiz-master-next' ); ?></button>
	<div class="option-page-result-page-tab-footer">
		<div id="footer-bar-notice" class="footer-bar-notice"></div>
		<div class="result-tab-footer-buttons">
			<a class="button-secondary qsm-show-all-variable-text qsm-common-button-styles" href="javascript:void(0)"><?php esc_html_e( 'Insert Template Variables', 'quiz-master-next' ); ?></a>
			<button class="save-pages button-primary qsm-common-button-styles"><?php esc_html_e( 'Save Results Pages', 'quiz-master-next' ); ?></button>
		</div>
	</div>
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
				$variable_list = qsm_extra_template_and_leaderboard($variable_list);
				if ( ! class_exists('QSM_Analysis') ) {
					$template_array = array(
						'%QSM_PIECHART_RESULT_X%'    => __( 'X: Question ID, Display the answers piechart.', 'quiz-master-next' ),
						'%QSM_BARCHART_RESULT_X%'    => __( 'X: Question ID, Display the answers barchart.', 'quiz-master-next' ),
						'%CATEGORY_POINTS_PIECHART%' => __( 'Display the point based category piechart.', 'quiz-master-next' ),
						'%CATEGORY_POINTS_BARCHART%' => __( 'Display the point based category barchart.', 'quiz-master-next' ),
					);
					$analysis = array(
						'Analysis' => $template_array,
					);
					$variable_list = array_merge( $variable_list, $analysis );
				}
				if ( ! class_exists('QSM_Advanced_Assessment') ) {
					$template_array = array(
						'%ANSWER_LABEL_POINTS%'   => __( 'The amount of points of all labels earned.', 'quiz-master-next' ),
						'%ANSWER_LABEL_POINTS_X%' => __( 'X: Answer label slug - The amount of points a specific label earned.', 'quiz-master-next' ),
						'%ANSWER_LABEL_COUNTS%'   => __( 'The amount of counts of all labels earned.', 'quiz-master-next' ),
						'%ANSWER_LABEL_COUNTS_X%' => __( 'X: Answer label slug - The amount of counts a specific label earned.', 'quiz-master-next' ),
					);
					if ( ! empty( $_GET['tab'] ) && 'results-pages' === $_GET['tab'] ) {
						$template_array['%ANSWER_LABEL_POINTS_PIE_CHART%'] = __( 'Display piechart based on points.', 'quiz-master-next' );
						$template_array['%ANSWER_LABEL_COUNTS_PIE_CHART%'] = __( 'Display piechart based on counts.', 'quiz-master-next' );
					}
					$advanced_assessment = array(
						'Advanced Assessment' => $template_array,
					);
					$variable_list = array_merge( $variable_list, $advanced_assessment );
				}
				if ( ! class_exists('QSM_Exporting') ) {
					$template_array = array(
						'%PDF_BUTTON%' => __( 'Displays download button on the results page.', 'quiz-master-next' ),
					);
					$download_results = array(
						'Export Results' => $template_array,
					);
					$variable_list = array_merge($variable_list, $download_results);
				}
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
							$upgrade_link = "";
							$classname = "";
							$qsm_badge = "";
							if ( ( ! class_exists( 'QSM_Extra_Variables' ) ) && ( 'Extra Template Variables' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link('extra-template-variables');
								$classname = "qsm-upgrade-popup-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";

							}
							if ( ( ! class_exists( 'Mlw_Qmn_Al_Widget' ) ) && ( 'Advanced Leaderboard' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link('downloads/advanced-leaderboard/');
								$classname = "qsm-upgrade-popup-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";

							}
							if ( ( ! class_exists( 'QSM_Exporting' ) ) && ( 'Export Results' == $category_name) ) {
								$upgrade_link = qsm_get_plugin_link('downloads/export-results/');
								$classname = "qsm-upgrade-popup-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";

							}
							if ( ( ! class_exists( 'QSM_Analysis' ) ) && ( 'Analysis' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link('downloads/results-analysis/');
								$classname = "qsm-upgrade-popup-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";

							}
							if ( ( ! class_exists( 'QSM_Advanced_Assessment' ) ) && ( 'Advanced Assessment' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link( 'downloads/advanced-assessment/' );
								$classname = "qsm-upgrade-popup-variable qsm-upgrade-popup-advanced-assessment-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";
							}
							?>
							<div><h2 class="qsm-upgrade-popup-category-name"><?php echo esc_attr( $category_name );?></h2><?php echo  wp_kses_post( $qsm_badge ) ; ?></div>
							<?php
							foreach ( $category_variables as $variable_key => $variable ) {
								?>
								<div class="popup-template-span-wrap">
									<span class="qsm-text-template-span <?php echo esc_attr( $classname );?>">
									<?php if ( false !== strpos( $classname, 'qsm-upgrade-popup-variable') ) {?>
										<span class="button button-default template-variable qsm-tooltips-icon"><?php echo esc_attr( $variable_key ); ?>
											<span class="qsm-tooltips qsm-upgrade-tooltip"><?php echo esc_html__( 'Available in pro', 'quiz-master-next' );?></span>
										</span>
										<?php } else { ?>
										<span class="button button-default template-variable"><?php echo esc_attr( $variable_key ); ?></span>
											<span class='button click-to-copy'><?php esc_html_e('Click to Copy', 'quiz-master-next'); ?></span>
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
qsm_result_and_email_popups_for_templates( $template_from_script, $my_result_templates, 'result');
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
		$query = $wpdb->prepare( "SELECT name, term_id FROM {$wpdb->prefix}terms WHERE term_id IN ( SELECT DISTINCT term_id FROM {$wpdb->prefix}mlw_question_terms WHERE quiz_id = %d ) ORDER BY name ASC", $quiz_id );
	} else {
		$query = $wpdb->prepare( "SELECT DISTINCT category FROM {$wpdb->prefix}mlw_questions WHERE category <> '' AND quiz_id = %d", $quiz_id );
	}
	$categories = $wpdb->get_results( $query, ARRAY_N );
	qsm_webhooks_popup_window_section();
	?>
	<script type="text/template" id="tmpl-results-page">
		<div class="results-page">
				<header class="results-page-header">
					<strong><?php esc_html_e( 'Result Page ', 'quiz-master-next' ); ?> {{data.id}}</strong>
					<div class="qsm-template-btn-group">
						<div class="qsm-actions-link-box">
							<?php do_action( 'qsm_add_action_links_before' ); ?>
							<a href="javascript:void(0)" class="qsm-settings-box-result-button" title="<?php esc_attr_e( 'Quick Settings', 'quiz-master-next' ); ?>" >
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/gear.svg'); ?>" alt="gear.svg"/>
							</a>
							<a href="javascript:void(0)" class="qsm-duplicate-result-page-button" title="<?php esc_attr_e( 'Duplicate Page', 'quiz-master-next' ); ?>" >
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/copy.svg'); ?>" alt="copy.svg"/>
							</a>
							<a href="javascript:void(0)" data-template-type="result" class="qsm-insert-page-template-anchor" title="<?php esc_attr_e( 'Add Template', 'quiz-master-next' ); ?>" >
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
											<input placeholder="<?php esc_attr_e( 'Type Template name here ', 'quiz-master-next' ); ?>" type="text" id="qsm-insert-page-template-title-{{data.id}}" class="qsm-insert-page-template-title">
											<div style="display: none;" class="qsm-to-replace-page-template-wrap">
												<select class="qsm-to-replace-page-template"></select>
											</div>
											<p class="qsm-insert-template-response"></p>
										</div>
										<div class="qsm-insert-template-right">
											<button data-id="{{data.id}}" class="qsm-save-page-template-button button"><?php esc_html_e( 'Save', 'quiz-master-next' ); ?></button>
										</div>
									</div>
								</div>
								<img class="qsm-common-svg-image-class " src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/save-3-line.svg'); ?>" alt="save-3-line.svg"/>
							</a>
							<a href="javascript:void(0)" class="qsm-more-settings-box-result-button" title="<?php esc_attr_e( 'More Options', 'quiz-master-next' ); ?>" >
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/more-2-fill.svg'); ?>" alt="more-2-fill.svg"/>
							</a>
						</div> <!-- Closing qsm-actions-link-box -->
						<div class="qsm-actions-link-box qsm-toggle-action-wrapper">
							<a href="javascript:void(0)" class="qsm-toggle-result-page-button" title="<?php esc_attr_e( 'Toggle', 'quiz-master-next' ); ?>" >
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/arrow-down-s-line.svg'); ?>" alt="arrow-down-s-line.svg"/>
							</a>
						</div>
						<div class="qsm-more-settings-box-details">
							<?php do_action( 'qsm_result_page_more_settings_box_before' ); ?>
							<a href="javascript:void(0)" data-type="result" class="qsm-view-templates-list" title="<?php esc_attr_e( 'Change Template', 'quiz-master-next' ); ?>" >
							<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/refresh-line.svg'); ?>" alt="refresh-line.svg"/>
							<span><?php esc_html_e( 'Change Template', 'quiz-master-next' ); ?></span>
							</a>
							<a href="javascript:void(0)" class="qsm-delete-result-button" title="<?php esc_attr_e( 'Delete Page', 'quiz-master-next' ); ?>" >
							<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/trash.svg'); ?>" alt="trash.svg"/><span><?php esc_html_e( 'Delete Page ', 'quiz-master-next' ); ?></span>
							</a>
							<?php do_action( 'qsm_result_page_more_settings_box_after' ); ?>
						</div>
						<div class="qsm-settings-box-details">
							<?php do_action( 'qsm_result_page_settings_box_before' ); ?>
							<label class="qsm-template-mark-as-default">
								<input type="checkbox" name="qsm_mark_as_default" value="{{data.id}}" <# if( undefined != data.default_mark && data.default_mark == data.id ) { #> checked <# } #> class="qsm-mark-as-default"/>
								<?php esc_html_e( 'Mark as default', 'quiz-master-next' ); ?>
							</label>
							<?php do_action( 'qsm_result_page_settings_box_after' ); ?>
						</div> <!-- Closing qsm-settings-box-details -->
					</div> <!-- Closing qsm-template-btn-group -->
				</header>
				<main class="results-page-content">
					<div class="results-page-when">
						<div class="results-page-content-header">
							<h4><?php esc_html_e( 'When..', 'quiz-master-next' ); ?></h4>
							<p><?php esc_html_e( 'Following conditions are met', 'quiz-master-next' ); ?></p>
						</div>
						<div class="results-page-when-conditions">
							<!-- Conditions go here. Review template below. -->
						</div>
						<a class="qsm-new-condition qsm-block-btn" href="javascript:void(0);"><span>+</span><?php esc_html_e( ' Add Condition', 'quiz-master-next' ); ?></a>
						<?php do_action( 'qsm_result_page_condition_after',  $quiz_id, $categories ); ?>
					</div>
					<div class="results-page-show" data-result-page="{{ data.id }}">
						<div class="results-page-content-header">
							<h4><?php esc_html_e( '..Then', 'quiz-master-next' ); ?></h4>
						</div>
						<?php do_action( 'qsm_result_page_view_options_before',  $quiz_id, $categories ); ?>
						<div class="qsm-edit-result-view-options" >
							<div class="qsm-edit-result-input-option">
								<input type="radio" name="qsm_then_show_result_option_{{ data.id }}" id="qsm-then-show-result-{{ data.id }}" class="qsm-then-show-result" value="1" checked>
								<label for="qsm-then-show-result-{{ data.id }}"><?php esc_html_e( 'Show following page', 'quiz-master-next' ); ?></label>
							</div>
							<div class="qsm-edit-result-input-option">
								<input placeholder="<?php esc_attr_e( 'http://example.com/', 'quiz-master-next' ); ?>" type="radio" name="qsm_then_show_result_option_{{ data.id }}" id="qsm-then-redirect-to-url-{{ data.id }}" class="qsm-then-redirect-to-url"  value="2">
								<label for="qsm-then-redirect-to-url-{{ data.id }}"><?php esc_html_e( 'Redirect URL', 'quiz-master-next' ); ?></label>
							</div>
						</div>
						<div class="qsm-result-page-then-box-styles-wrap">
							<div class="qsm-result-page-template-options qsm-result-page-then-box-styles" >
								<div class="qsm-result-page-template-buttons">
									<button class="button qsm-common-button-styles qsm-start-with-template" ><?php esc_html_e( 'Start With a Template', 'quiz-master-next' );?></button>
									<button class="button qsm-common-button-styles qsm-start-with-canvas"><?php esc_html_e( 'Default Template', 'quiz-master-next' );?></button>
								</div>
								<div class="qsm-result-page-template-learn-more">
									<p><a href="<?php echo esc_url( qsm_get_plugin_link('docs/advanced-topics/template-library', 'quiz-options-result-page') );?>" target="_blank" rel="noopener"><?php esc_html_e( "Learn More About Templates", 'quiz-master-next' ); ?></a></p>
								</div>
							</div>
							<div class="qsm-result-page-editor-options qsm-result-page-then-box-styles">
								<?php
									do_action( 'qsm_result_page_content_before',  $quiz_id, $categories );
									qsm_extra_shortcode_popup_window_button( $quiz_id, $categories );
								?>
								<textarea id="results-page-{{ data.id }}" class="results-page-template">
								{{{ data.page.replace(/%([^%]+)%|\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs, function(match, capturedValue) {
									let qsm_varaible_list = qsm_admin_messages.qsm_variables_name;
									for (let qsm_variable in qsm_varaible_list) {
										variable_name = qsm_varaible_list[qsm_variable];
										if( variable_name.includes('%%') ){
											var arrayValues = variable_name.split("%%");
											qsm_varaible_list = jQuery.merge(jQuery.merge([], arrayValues), qsm_varaible_list);
										}
										if( variable_name.includes('_X%') ){
											qsm_varaible_list[qsm_variable] = variable_name.slice(0, -2);
										}
									}
									if (qsm_is_substring_in_array(match, qsm_varaible_list)) {
										return '<qsmvariabletag>' + capturedValue + '</qsmvariabletag>';
									} else if (/\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs.test(match)) {
										return match.replace(/\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs, function(innerMatch, content) {
											const openingTag = innerMatch.match(/\[qsm[^\]]*\]/)[0];
											const closingTag = innerMatch.match(/\[\/qsm[^\]]*\]/)[0];
											return `<qsmextrashortcodetag>${openingTag}</qsmextrashortcodetag>${content}<qsmextrashortcodetag>${closingTag}</qsmextrashortcodetag>`;
										});
									} else {
										return match;
									}
								}) }}}
								</textarea>
								<div class="qsm-result-page-content-buttons">
									<button type="button" class="button qsm-slashcommand-variables-button qsm-result-editor-custom-button"><?php esc_html_e('Add Variables', 'quiz-master-next'); ?></button>
									<span class="qsm-insert-template-variable-text"><?php esc_html_e( 'Or, Type', 'quiz-master-next' );?> / <?php esc_html_e( ' to insert template variables', 'quiz-master-next' ); ?></span>
								</div>
								<?php do_action( 'qsm_result_page_content_buttons_after',  $quiz_id, $categories ); ?>
							</div>
							<div class="qsm-result-page-redirect-options qsm-result-page-then-box-styles">
								<p class="qsm-result-redirect-text"><?php esc_html_e( 'Redirecting the user by entering the URL below:', 'quiz-master-next' ); ?></p>
								<input type="text"  placeholder="<?php esc_attr_e( 'http://example.com/', 'quiz-master-next' ); ?>" class="results-page-redirect" value="<# if ( data.redirect && 'undefined' !==  data.redirect && 'false' !== data.redirect ) { #>{{ data.redirect }}<# } #>">
							</div>
							<div class="qsm-result-page-common-section qsm-result-page-then-box-styles">
								<?php do_action( 'qsm_result_page_before_redirect_input',  $quiz_id, $categories ); ?>
								<!-- NOTE: Previously redirect input displayed here -->
								<?php do_action( 'qsm_result_page_after',  $quiz_id, $categories );
									if ( ! class_exists('QSM_Webhooks') ) { ?>
										<div class="qsm-webhooks-pricing-popup qsm-webhooks-upgrade-button">
											<div class="qsm-webhooks-upgrade-upper">
												<label class="qsm-webhooks-label"><?php esc_html_e( 'Assign Webhook', 'quiz-master-next' ); ?></label>
												<select class="qsm-webhooks-select">
													<option><?php esc_html_e('Select Webhook', 'quiz-master-next'); ?></option>
												</select>
											</div>
											<a href="javascript:void(0)" class="qsm-webhooks-upgrade-link">
											<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/Lock.png' ); ?>" alt="Lock.png"><span class="qsm-webhooks-anchor-text"><?php esc_html_e( 'Upgrade', 'quiz-master-next' ); ?></span>
											</a>
										</div>
									<?php }
								?>
							</div>
						</div>
					</div>
				</main>
			</div>
		</script>

	<script type="text/template" id="tmpl-results-page-condition">
		<div class="results-page-condition">
			<div class="qsm-condition-collection-wrap">
				<p><?php echo esc_html__( 'Condition ', 'quiz-master-next' ); ?> <span class="qsm-condition-collection-count"></span></p>
			</div>
			<div class="qsm-result-condition-mode qsm-result-condition-container">
				<div class="results-page-condition-category-container qsm-result-condition-container-inner">
					<label class="qsm-result-condition-title"><?php esc_html_e( 'Select Mode', 'quiz-master-next' ); ?></label>
					<select class="results-page-condition-category">
						<option value="quiz" <# if (data.category == 'quiz' || data.category == '') { #>selected<# } #>><?php esc_html_e( 'Quiz', 'quiz-master-next' ); ?></option>
						<?php if ( ! empty( $categories ) ) {
							$category_names = array_map(function( $category ) {
								return $category[0];
							}, $categories);
						?>
						<#
						let categories = '<?php echo wp_json_encode($category_names); ?>';
						let categories_array = JSON.parse(categories);
						#>
							<option value="category" <# if (data.category == 'category' || jQuery.inArray(data.category, categories_array) !== -1 ) { #>selected<# } #>><?php esc_html_e( 'Category', 'quiz-master-next' ); ?></option>
						<?php } else { ?>
							<option disabled value=""><?php esc_html_e( 'No Categories Available', 'quiz-master-next' ); ?></option>
						<?php }
						if ( ! class_exists( 'QSM_Advanced_Assessment' ) ) { ?>
							<option value="option-pro"><?php esc_html_e( 'Option (pro)', 'quiz-master-next' ); ?></option>
							<option value="label-pro"><?php esc_html_e( 'Label (pro)', 'quiz-master-next' ); ?></option>
						<?php }
						do_action( 'qsm_results_page_condition_category' ); ?>
					</select>
				</div>
				<div class="results-page-extra-condition-category-container qsm-result-condition-container-inner">
					<label class="qsm-result-condition-title"><?php esc_html_e( 'Select', 'quiz-master-next' ); ?> <span class="qsm-extra-condition-label"><?php esc_html_e( 'Category', 'quiz-master-next' ); ?></span></label>
					<select class="results-page-extra-condition-category">
						<?php if ( ! empty( $categories ) ) { ?>
							<?php foreach ( $categories as $cat ) { ?>
							<option class="qsm-condition-category" value="<?php echo esc_attr( ! empty( $cat[1] ) ? 'qsm-cat-' . $cat[1] : $cat[0] ); ?>" <# if (data.category == '<?php echo esc_attr( $cat[0] ); ?>' || data.extra_condition == '<?php echo esc_attr( ! empty( $cat[1] ) ? 'qsm-cat-' . $cat[1] : $cat[0] ); ?>') { #>selected<# } #>><?php echo esc_attr( $cat[0] ); ?></option>
							<?php } ?>
						<?php } else { ?>
							<option class="qsm-condition-category" value="" disabled><?php esc_html_e( 'No Categories Available', 'quiz-master-next' ); ?></option>
						<?php } ?>
						<?php do_action( 'qsm_results_page_extra_condition_category' ); ?>
					</select>
				</div>
				<button class="delete-condition-button">
					<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/trash.svg'); ?>" alt="trash.svg"/>
				</button>
			</div>
			<div class="qsm-result-condition-container">
				<div class="results-page-condition-criteria-container qsm-result-condition-container-inner">
					<label class="qsm-result-condition-title"><?php esc_html_e( 'Calculation Type', 'quiz-master-next' ); ?></label>
					<select class="results-page-condition-criteria">
						<option value="points" class="qsm-points-criteria" <# if (data.criteria == 'points' || data.category == 'points') { #>selected<# } #>><?php esc_html_e( 'Total points', 'quiz-master-next' ); ?></option>
						<option value="score" class="qsm-score-criteria" <# if (data.criteria == 'score' || data.category == 'score') { #>selected<# } #>><?php esc_html_e( 'Correct percentage', 'quiz-master-next' ); ?></option>
						<?php do_action( 'qsm_results_page_condition_criteria' ); ?>
					</select>
				</div>
				<?php do_action( 'qsm_results_page_extra_condition_fields' ); ?>
				<div class="results-page-condition-operator-container qsm-result-condition-container-inner">
					<label class="qsm-result-condition-title"><?php esc_html_e( 'Select Condition', 'quiz-master-next' ); ?></label>
					<select class="results-page-condition-operator">
						<option class="default_operator" value="equal" <# if (data.operator == 'equal') { #>selected<# } #>><?php esc_html_e( 'is equal to', 'quiz-master-next' ); ?></option>
						<option class="default_operator" value="not-equal" <# if (data.operator == 'not-equal') { #>selected<# } #>><?php esc_html_e( 'is not equal to', 'quiz-master-next' ); ?></option>
						<option class="default_operator" value="greater-equal" <# if (data.operator == 'greater-equal') { #>selected<# } #>><?php esc_html_e( 'is greater than or equal to', 'quiz-master-next' ); ?></option>
						<option class="default_operator" value="greater" <# if (data.operator == 'greater') { #>selected<# } #>><?php esc_html_e( 'is greater than', 'quiz-master-next' ); ?></option>
						<option class="default_operator" value="less-equal" <# if (data.operator == 'less-equal') { #>selected<# } #>><?php esc_html_e( 'is less than or equal to', 'quiz-master-next' ); ?></option>
						<option class="default_operator" value="less" <# if (data.operator == 'less') { #>selected<# } #>><?php esc_html_e( 'is less than', 'quiz-master-next' ); ?></option>
						<?php do_action( 'qsm_results_page_condition_operator' ); ?>
					</select>
				</div>
				<div class="condition-default-value-container qsm-result-condition-container-inner">
					<label class="qsm-result-condition-title"><?php esc_html_e( 'Value', 'quiz-master-next' ); ?></label>
					<input type="text" class="results-page-condition-value condition-default-value" value="{{ data.value }}">
				</div>
				<?php do_action( 'qsm_results_page_condition_value' ); ?>
			</div>
		</div>
	</script>
<?php
qsm_result_and_email_row_templates();
} ?>