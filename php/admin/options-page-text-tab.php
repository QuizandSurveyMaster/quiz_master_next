<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Text tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_text_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Text', 'quiz-master-next' ), 'mlw_options_text_tab_content' );
}
add_action( "plugins_loaded", 'qmn_settings_text_tab', 5 );

/**
* Adds the Text tab content to the tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_text_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;
	wp_enqueue_style( 'qmn_admin_style', plugins_url( '../../css/qsm-admin.css' , __FILE__ ) );
	?>
	<h3 style="text-align: center;"><?php _e("Template Variables", 'quiz-master-next'); ?></h3>
	<div class="template_list_holder">
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
			<span class="template_name">%TIMER%</span> - <?php _e('The amount of time user spent on quiz in seconds', 'quiz-master-next'); ?>
    </div>
    <div class="template_variable">
			<span class="template_name">%TIMER_MINUTES%</span> - <?php _e('The amount of time user spent on quiz in minutes', 'quiz-master-next'); ?>
    </div>
		<div class="template_variable">
			<span class="template_name">%CATEGORY_POINTS_X%</span> - <?php _e('X: Category name - The amount of points a specific category earned.', 'quiz-master-next'); ?>
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
			<span class="template_name">%QUESTION%</span> - <?php _e('The question that the user answered', 'quiz-master-next'); ?>
		</div>
		<div class="template_variable">
			<span class="template_name">%USER_ANSWER%</span> - <?php _e('The answer the user gave for the question', 'quiz-master-next'); ?>
		</div>
		<div class="template_variable">
			<span class="template_name">%CORRECT_ANSWER%</span> - <?php _e('The correct answer for the question', 'quiz-master-next'); ?>
		</div>
		<div class="template_variable">
			<span class="template_name">%USER_COMMENTS%</span> - <?php _e('The comments the user provided in the comment field for the question', 'quiz-master-next'); ?>
		</div>
		<div class="template_variable">
			<span class="template_name">%CORRECT_ANSWER_INFO%</span> - <?php _e('Reason why the correct answer is the correct answer', 'quiz-master-next'); ?>
		</div>
		<div class="template_variable">
			<span class="template_name">%CURRENT_DATE%</span> - <?php _e('The Current Date', 'quiz-master-next'); ?>
		</div>
		<?php do_action('qmn_template_variable_list'); ?>
	</div>
	<?php
	$mlwQuizMasterNext->pluginHelper->generate_settings_section( 'quiz_text' );
}
?>
