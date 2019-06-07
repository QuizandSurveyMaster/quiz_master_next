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
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Results Pages', 'quiz-master-next' ), 'qsm_options_results_tab_content' );
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
	$quiz_id = intval( $_GET['quiz_id'] );
	$js_data = array(
		'quizID' => $quiz_id,
		'nonce'  => wp_create_nonce( 'wp_rest' ),
	);
	wp_enqueue_script( 'qsm_results_admin_script', plugins_url( '../../js/qsm-admin-results.js', __FILE__ ), array( 'jquery-ui-sortable', 'qmn_admin_js' ), $mlwQuizMasterNext->version );
	wp_localize_script( 'qsm_results_admin_script', 'qsmResultsObject', $js_data );
	wp_enqueue_editor();
	wp_enqueue_media();
	?>
	<h2><?php esc_html_e( 'Results Pages', 'quiz-master-next' ); ?></h2>
	<p>Need assistance with this tab? <a href="https://docs.quizandsurveymaster.com/article/25-setting-up-results-pages-and-thank-you-pages" target="_blank">Check out the documentation</a> for this tab!</p>

	<!-- Template Variables Section -->
	<section>
		<h3 style="text-align: center;"><?php esc_html_e( 'Template Variables', 'quiz-master-next' ); ?></h3>
		<div class="template_list_holder">
			<div class="template_variable">
				<span class="template_name">%CONTACT_X%</span> - <?php _e( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CONTACT_ALL%</span> - <?php _e( 'List user values for all contact fields', 'quiz-master-next' ); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%POINT_SCORE%</span> - <?php _e('Score for the quiz when using points', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%AVERAGE_POINT%</span> - <?php _e('The average amount of points user had per question', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%AMOUNT_CORRECT%</span> - <?php _e('The number of correct answers the user had', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%TOTAL_QUESTIONS%</span> - <?php _e('The total number of questions in the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CORRECT_SCORE%</span> - <?php _e('Score for the quiz when using correct answers', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_NAME%</span> - <?php _e('The name the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_BUSINESS%</span> - <?php _e('The business the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_PHONE%</span> - <?php _e('The phone number the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_EMAIL%</span> - <?php _e('The email the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%QUIZ_NAME%</span> - <?php _e('The name of the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%QUESTIONS_ANSWERS%</span> - <?php _e('Shows the question, the answer the user provided, and the correct answer', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%COMMENT_SECTION%</span> - <?php _e('The comments the user entered into comment box if enabled', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%TIMER_MINUTES%</span> - <?php _e('The amount of time user spent taking quiz in minutes', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%TIMER%</span> - <?php _e('The amount of time user spent taking quiz in seconds', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_POINTS_X%</span> - <?php _e('X: Category name - The amount of points a specific category earned.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%AVERAGE_CATEGORY_POINTS_X%</span> - <?php _e('X: Category name - The average amount of points a specific category earned.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_SCORE_X%</span> - <?php _e('X: Category name - The score a specific category earned.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_AVERAGE_POINTS%</span> - <?php _e('The average points from all categories.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_AVERAGE_SCORE%</span> - <?php _e('The average score from all categories.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%FACEBOOK_SHARE%</span> - <?php _e('Displays button to share on Facebook.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%TWITTER_SHARE%</span> - <?php _e('Displays button to share on Twitter.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%POLL_RESULTS_X%</span> - <?php _e('X = Question ID Note: only supported for multiple choice answers', 'quiz-master-next'); ?>
			</div>
                        <div class="template_variable">
				<span class="template_name">%RESULT_ID%</span> - <?php _e('Show result id', 'quiz-master-next'); ?>
			</div>
			<?php do_action('qmn_template_variable_list'); ?>
		</div>
		<div style="clear:both;"></div>
	</section>

	<!-- Results Page Section -->
	<section>
		<h3>Your Pages</h3>
		<button class="save-pages button-primary"><?php esc_html_e( 'Save Results Pages', 'quiz-master-next' ); ?></button>
                <span class="spinner" style="float: none;"></span>
		<button class="add-new-page button"><?php esc_html_e( 'Add New Results Page', 'quiz-master-next' ); ?></button>
		<div id="results-pages"></div>
		<button class="save-pages button-primary"><?php esc_html_e( 'Save Results Pages', 'quiz-master-next' ); ?></button>
                <span class="spinner" style="float: none;"></span>
		<button class="add-new-page button"><?php esc_html_e( 'Add New Results Page', 'quiz-master-next' ); ?></button>
	</section>

	<!-- Templates -->
	<script type="text/template" id="tmpl-results-page">
		<div class="results-page">
			<header class="results-page-header">
				<div><button class="delete-page-button"><span class="dashicons dashicons-trash"></span></button></div>
			</header>
			<main class="results-page-content">
				<div class="results-page-when">
					<div class="results-page-content-header">
						<h4>When...</h4>
						<p>Set conditions for when this page should be shown. Leave empty to set this as the default page.</p>
					</div>
					<div class="results-page-when-conditions">
						<!-- Conditions go here. Review template below. -->
					</div>
					<button class="new-condition button"><?php esc_html_e( 'Add additional condition', 'quiz-master-next' ); ?></button>
				</div>
				<div class="results-page-show">
					<div class="results-page-content-header">
						<h4>...Show</h4>
						<p>Create the results page that should be shown when the conditions are met.</p>
					</div>
					<textarea id="results-page-{{ data.id }}" class="results-page-template">{{{ data.page }}}</textarea>
					<p>Or, redirect the user by entering the URL below:</p>
					<input type="text" class="results-page-redirect" value="<# if ( data.redirect ) { #>{{ data.redirect }}<# } #>">
				</div>
			</main>
		</div>
	</script>

	<script type="text/template" id="tmpl-results-page-condition">
		<div class="results-page-condition">
			<button class="delete-condition-button"><span class="dashicons dashicons-trash"></span></button>
			<select class="results-page-condition-criteria">
				<option value="points" <# if (data.criteria == 'points') { #>selected<# } #>>Total points earned</option>
				<option value="score" <# if (data.criteria == 'score') { #>selected<# } #>>Correct score percentage</option>
				<?php do_action( 'qsm_results_page_condition_criteria' ); ?>
			</select>
			<select class="results-page-condition-operator">
				<option value="equal" <# if (data.operator == 'equal') { #>selected<# } #>>is equal to</option>
				<option value="not-equal" <# if (data.operator == 'not-equal') { #>selected<# } #>>is not equal to</option>
				<option value="greater-equal" <# if (data.operator == 'greater-equal') { #>selected<# } #>>is greater than or equal to</option>
				<option value="greater" <# if (data.operator == 'greater') { #>selected<# } #>>is greater than</option>
				<option value="less-equal" <# if (data.operator == 'less-equal') { #>selected<# } #>>is less than or equal to</option>
				<option value="less" <# if (data.operator == 'less') { #>selected<# } #>>is less than</option>
				<?php do_action( 'qsm_results_page_condition_operator' ); ?>
			</select>
			<input type="text" class="results-page-condition-value" value="{{ data.value }}">
		</div>
	</script>
	<?php
}
?>
