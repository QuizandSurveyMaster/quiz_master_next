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
	$table_name = $wpdb->prefix . 'mlw_quiz_output_templates';
	$temlate_sql = "SELECT unique_id, template_content FROM {$table_name}";
	$mlw_quiz_output_templates_results = $wpdb->get_results($temlate_sql);
	$unique_ids = array_column($mlw_quiz_output_templates_results, 'unique_id');
	$template_contents = array_column($mlw_quiz_output_templates_results, 'template_content');
	// Combine into an associative array where unique_id is the key
	$my_tmpl_data = array_combine($unique_ids, $template_contents);

	// Use array_column to extract the unique_id column
	$existing_unique_ids = array_column($mlw_quiz_output_templates_results, 'unique_id');
	$qsm_quiz_output_templates_results = [
		(object) [
			'id'               => 1,
			'unique_id'        => 'UID101',
			'template_name'    => 'Template 1',
			'template_type'    => 'result',
			'template_content' => 'Your score: %POINT_SCORE% out of %MAXIMUM_POINTS%. You scored %AMOUNT_CORRECT% correct answers out of %TOTAL_QUESTIONS%.',
			'is_free'          => true,
		],
		(object) [
			'id'               => 2,
			'unique_id'        => 'UID102',
			'template_name'    => 'Template 2',
			'template_type'    => 'result',
			'template_content' => 'Congratulations %USER_NAME%! You answered %AMOUNT_CORRECT% out of %TOTAL_QUESTIONS% correctly. Your result: %POINT_SCORE%. 
			[qsm_conditions type="points" condition="equal" value="10"]You scored exactly 10 points! Well done![/qsm_conditions]
			[qsm_conditions type="points" condition="greater" value="20"]Excellent! You scored more than 20 points![/qsm_conditions]',
			'is_free'          => false,
		],
		(object) [
			'id'               => 3,
			'unique_id'        => 'UID103',
			'template_name'    => 'Template 3',
			'template_type'    => 'result',
			'template_content' => 'Great job, %USER_NAME%! You scored %POINT_SCORE%/%MAXIMUM_POINTS%. %AMOUNT_CORRECT% correct answers out of %TOTAL_QUESTIONS%.',
			'is_free'          => true,
		],
		(object) [
			'id'               => 4,
			'unique_id'        => 'UID104',
			'template_name'    => 'Template 4',
			'template_type'    => 'result',
			'template_content' => 'Quiz complete! %USER_NAME%, your score is %POINT_SCORE%/%MAXIMUM_POINTS%. %AMOUNT_CORRECT% correct answers!
			[qsm_conditions type="points" condition="lessthan" value="5"]Oops! You scored less than 5 points. Better luck next time![/qsm_conditions]',
			'is_free'          => false,
		],
		(object) [
			'id'               => 5,
			'unique_id'        => 'UID105',
			'template_name'    => 'Template 5',
			'template_type'    => 'result',
			'template_content' => 'Your quiz result: %POINT_SCORE% out of %MAXIMUM_POINTS%. Correct answers: %AMOUNT_CORRECT%.',
			'is_free'          => true,
		],
		(object) [
			'id'               => 6,
			'unique_id'        => 'UID106',
			'template_name'    => 'Template 6',
			'template_type'    => 'result',
			'template_content' => 'Well done, %USER_NAME%! You scored %POINT_SCORE%/%MAXIMUM_POINTS%. Correct answers: %AMOUNT_CORRECT%.',
			'is_free'          => false,
		],
		(object) [
			'id'               => 7,
			'unique_id'        => 'UID107',
			'template_name'    => 'Template 7',
			'template_type'    => 'result',
			'template_content' => 'Awesome, %USER_NAME%! You scored %POINT_SCORE%/%MAXIMUM_POINTS%. %AMOUNT_CORRECT% answers correct out of %TOTAL_QUESTIONS%. 
			[qsm_conditions type="points" condition="greater" value="15"]Excellent! You scored over 15 points![/qsm_conditions]',
			'is_free'          => true,
		],
	];  
	
	$new_templates_uid = array_column($qsm_quiz_output_templates_results, 'unique_id');
	$new_template_contents = array_column($qsm_quiz_output_templates_results, 'template_content');
	// Combine into an associative array where unique_id is the key
	$new_tmpl_data = array_combine($new_templates_uid, $new_template_contents);

	// Filter the incoming data to only include templates that have not been inserted
	$new_templates = array_filter($qsm_quiz_output_templates_results, function( $template ) use ( $existing_unique_ids ) {
		return ! in_array($template->unique_id, $existing_unique_ids);
	});

	$js_data = array(
		'quizID'          => $quiz_id,
		'nonce'           => wp_create_nonce( 'wp_rest' ),
		'rest_user_nonce' => wp_create_nonce( 'wp_rest_nonce_' . $quiz_id . '_' . $user_id ),
		'my_tmpl_data'    => $my_tmpl_data,
		'new_tmpl_data'   => $new_tmpl_data,
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
		<div class="footer-bar-notice"></div>
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

<div class="qsm-popup qsm-popup-slide" id="qsm-result-page-templates" aria-hidden="false" style="display:none;">
	<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
		<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="qsm-result-page-templates-title">
			<header class="qsm-popup__header">
				<div class="qsm-result-page-template-header-left">
					<img class="qsm-result-page-template-header-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/icon-200x200.png'); ?>" alt="icon-200x200.png"/>
					<h2 class="qsm-popup__title" id="qsm-result-page-templates-title">
						<?php esc_html_e( 'Templates', 'qsm-webhooks' ); ?>
					</h2>
				</div>	
				<div class="qsm-result-page-template-header-right">
					<div class="qsm-result-page-template-header">
						<div class="qsm-result-page-template-header-tabs">
							<a class="qsm-result-page-tmpl-header-links active" data-tab="page" href="javascript:void(0)"><?php esc_html_e( 'QSM Templates', 'quiz-master-next' ); ?></a>
							<a class="qsm-result-page-tmpl-header-links" data-tab="my" href="javascript:void(0)"><?php esc_html_e( 'My Templates', 'quiz-master-next' ); ?></a>
						</div>
					</div>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</div>	
			</header>
			<main class="qsm-popup__content" id="qsm-result-page-templates-content" data-result-page="">
			<div class="qsm-result-page-template-container qsm-result-page-template-common">
				<?php
					foreach ( $new_templates as $row ) { 
						if ( 'result' == $row->template_type ) {
							?>
							<div class="qsm-result-page-template-card <?php echo $row->is_free ? 'qsm-result-page-template-pro' : ''; ?>" data-unique-id="<?php echo esc_html($row->unique_id); ?>" >
								<div class="qsm-result-page-template-card-content">
									<div class="qsm-result-page-template-card-buttons">
										<button class="qsm-result-page-template-preview-button button">
											<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/eye-line-blue.png'); ?>" alt="eye-line-blue.png" />
											<?php esc_html_e( 'Preview', 'quiz-master-next' ); ?>
										</button>
										<button class="qsm-result-page-template-insert-button button" data-unique-id="<?php echo esc_html($row->unique_id); ?>">
											<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/download-line-blue.png'); ?>" alt="download-line-blue.png" />
											<?php esc_html_e( 'Insert', 'quiz-master-next' ); ?>
										</button>
									</div>
								</div>
								<p class="qsm-result-page-template-template-name"><?php echo esc_html($row->template_name); ?></p>
							</div>
							<?php
						}
					}
				?>
			</div>
			<div class="qsm-result-my-template-container qsm-result-page-template-common">
				<?php
				$table_name = $wpdb->prefix . 'mlw_quiz_output_templates';
				$sql = "SELECT * FROM {$table_name}";
				$mlw_quiz_output_templates_results = $wpdb->get_results($sql);
				if ( $mlw_quiz_output_templates_results ) { 
					foreach ( $mlw_quiz_output_templates_results as $row ) { 
						if ( 'result' == $row->template_type ) {
							?>
							<div class="qsm-result-page-template-card" data-id="<?php echo esc_html($row->id); ?>">
								<div class="qsm-result-page-template-card-content">
									<div class="qsm-result-page-template-card-buttons">
										<button class="qsm-result-page-template-preview-button button">
											<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/eye-line-blue.png'); ?>" alt="eye-line-blue.png" />
											<?php esc_html_e( 'Preview', 'quiz-master-next' ); ?>
										</button>
										<button class="qsm-result-page-template-use-button button" data-unique-id="<?php echo esc_html($row->unique_id); ?>">
										<?php esc_html_e( 'Use Template', 'quiz-master-next' ); ?>
										</button>
									</div>
								</div>
								<p class="qsm-result-page-template-template-name"><?php echo esc_html($row->template_name); ?></p>
							</div>
							<?php
						}
					}
				} ?>
			</div>
			</main>
		</div>
	</div>
</div>


<div class="qsm-popup qsm-popup-slide" id="qsm-preview-result-page-templates" aria-hidden="false" style="display:none;">
	<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
		<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="qsm-preview-result-page-templates-title">
			<header class="qsm-popup__header">
				<h2 class="qsm-popup__title" id="qsm-preview-result-page-templates-title">
					<?php esc_html_e( 'Template Preview', 'qsm-webhooks' ); ?>
				</h2>
				<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
			</header>
			<main class="qsm-popup__content" id="qsm-preview-result-page-templates-content">
				<div class="qsm-preview-result-page-template-container ">
					<img class="qsm-preview-template-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/screenshot-default-theme.png'); ?>" alt="screenshot-default-theme.png"/>
				</div>
			</main>
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
		$query = $wpdb->prepare( "SELECT name, term_id FROM {$wpdb->prefix}terms WHERE term_id IN ( SELECT DISTINCT term_id FROM {$wpdb->prefix}mlw_question_terms WHERE quiz_id = %d ) ORDER BY name ASC", $quiz_id );
	} else {
		$query = $wpdb->prepare( "SELECT DISTINCT category FROM {$wpdb->prefix}mlw_questions WHERE category <> '' AND quiz_id = %d", $quiz_id );
	}
	$categories = $wpdb->get_results( $query, ARRAY_N );
	?>
	<script type="text/template" id="tmpl-results-page">
		<div class="results-page">
				<header class="results-page-header">
					<strong><?php esc_html_e( 'Result Page ', 'quiz-master-next' ); ?> {{data.id}}</strong>
					<div class="qsm-template-btn-group">
						<div class="qsm-actions-link-box">
							<?php do_action( 'qsm_add_action_links_before' ); ?>
							<a href="javascript:void(0)" class="qsm-delete-result-button">
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/trash.svg'); ?>" alt="trash.svg"/>
							</a>
							<a href="javascript:void(0)" class="qsm-settings-box-result-button">
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/gear.svg'); ?>" alt="gear.svg"/>
							</a>
							<a href="javascript:void(0)" class="qsm-duplicate-result-page-button">
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/copy.svg'); ?>" alt="copy.svg"/>
							</a>
							<a href="javascript:void(0)" class="qsm-toggle-result-page-button">
								<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/arrow-down-s-line.svg'); ?>" alt="arrow-down-s-line.svg"/>
							</a>
						</div> <!-- Closing qsm-actions-link-box -->
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
							<p><?php esc_html_e( 'Condition for displaying result', 'quiz-master-next' ); ?></p>
						</div>
						<div class="results-page-when-conditions">
							<!-- Conditions go here. Review template below. -->
						</div>
						<a class="qsm-new-condition qsm-block-btn" href="javascript:void(0);"><span>+</span><?php esc_html_e( ' Add Condition', 'quiz-master-next' ); ?></a>
						<?php do_action( 'qsm_result_page_condition_after',  $quiz_id, $categories ); ?>
					</div>
					<div class="results-page-show">
						<div class="results-page-content-header">
							<h4><?php esc_html_e( '..Then', 'quiz-master-next' ); ?></h4>
						</div>
						<div class="qsm-result-page-common-section"><?php do_action( 'qsm_result_page_view_options_before',  $quiz_id, $categories ); ?></div>
						<div class="qsm-edit-result-view-options" data-result-page="{{ data.id }}" >
							<div class="qsm-edit-result-input-option">
								<input type="radio" name="qsm_then_show_result_option_{{ data.id }}" id="qsm-then-show-result-{{ data.id }}" class="qsm-then-show-result" value="1" checked>
								<label for="qsm-then-show-result-{{ data.id }}"><?php esc_html_e( 'Show following page', 'quiz-master-next' ); ?></label>
							</div>
							<div class="qsm-edit-result-input-option">
								<input type="radio" name="qsm_then_show_result_option_{{ data.id }}" id="qsm-then-redirect-to-url-{{ data.id }}" class="qsm-then-redirect-to-url"  value="2">
								<label for="qsm-then-redirect-to-url-{{ data.id }}"><?php esc_html_e( 'Redirect URL', 'quiz-master-next' ); ?></label>
							</div>
						</div>
						
						<div class="qsm-result-page-template-options qsm-result-page-then-box-styles">
							<div class="qsm-result-page-template-buttons">
								<button class="button qsm-common-button-styles qsm-start-with-template" data-result-page="{{ data.id }}"><?php esc_html_e( 'Start with a Template', 'quiz-master-next' );?></button>
								<button class="button qsm-common-button-styles qsm-start-with-canvas"><?php esc_html_e( 'Blank Canvas', 'quiz-master-next' );?></button>
							</div>
							<div class="qsm-result-page-template-learn-more">
								<p><?php esc_html_e( 'Learn to know more about the QSM Premade library? ', 'quiz-master-next' );?>
									<a href="javascript:void(0)" target="_blank"><?php esc_html_e( 'Learn more', 'quiz-master-next' );?></a>
								</p>
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
							<input type="text" class="results-page-redirect" value="<# if ( data.redirect && 'undefined' !==  data.redirect && 'false' !== data.redirect ) { #>{{ data.redirect }}<# } #>">
						</div>
						<div class="qsm-result-page-common-section qsm-result-page-then-box-styles">
							<?php do_action( 'qsm_result_page_before_redirect_input',  $quiz_id, $categories ); ?>
							<!-- NOTE: Previously redirect input displayed here -->
							<?php do_action( 'qsm_result_page_after',  $quiz_id, $categories ); ?>
						</div>
					</div>
				</main>
			</div>
		</script>

	<script type="text/template" id="tmpl-results-page-condition">
		<div class="results-page-condition">
			<div class="qsm-condition-collection-wrap">
				<p><?php echo esc_html( 'Condition ', 'quiz-master-next' ); ?> <span class="qsm-condition-collection-count"></span></p>
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
<?php } ?>