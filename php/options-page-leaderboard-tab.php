<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the leaderboard to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_leaderboard_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( "Leaderboard", 'quiz-master-next' ), 'qsm_options_leaderboard_tab_content' );
}
add_action( "plugins_loaded", 'qmn_settings_leaderboard_tab', 5 );


/**
* Adds the leaderboard content to the leaderboard tab.
*
* @return void
* @since 4.4.0
*/
function qsm_options_leaderboard_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;
	?>
	<h3><?php _e( 'Template Variables', 'quiz-master-next' ); ?></h3>
	<table class="form-table">
		<tr>
			<td><strong>%FIRST_PLACE_NAME%</strong> - <?php _e("The name of the user who is in first place", 'quiz-master-next'); ?></td>
			<td><strong>%FIRST_PLACE_SCORE%</strong> - <?php _e("The score from the first place's quiz", 'quiz-master-next'); ?></td>
		</tr>

		<tr>
			<td><strong>%SECOND_PLACE_NAME%</strong> - <?php _e("The name of the user who is in second place", 'quiz-master-next'); ?></td>
			<td><strong>%SECOND_PLACE_SCORE%</strong> - <?php _e("The score from the second place's quiz", 'quiz-master-next'); ?></td>
		</tr>

		<tr>
			<td><strong>%THIRD_PLACE_NAME%</strong> - <?php _e('The name of the user who is in third place', 'quiz-master-next'); ?></td>
			<td><strong>%THIRD_PLACE_SCORE%</strong> - <?php _e("The score from the third place's quiz", 'quiz-master-next'); ?></td>
		</tr>

		<tr>
			<td><strong>%FOURTH_PLACE_NAME%</strong> - <?php _e('The name of the user who is in fourth place', 'quiz-master-next'); ?></td>
			<td><strong>%FOURTH_PLACE_SCORE%</strong> - <?php _e("The score from the fourth place's quiz", 'quiz-master-next'); ?></td>
		</tr>

		<tr>
			<td><strong>%FIFTH_PLACE_NAME%</strong> - <?php _e('The name of the user who is in fifth place', 'quiz-master-next'); ?></td>
			<td><strong>%FIFTH_PLACE_SCORE%</strong> - <?php _e("The score from the fifth place's quiz", 'quiz-master-next'); ?></td>
		</tr>

		<tr>
			<td><strong>%QUIZ_NAME%</strong> - <?php _e("The name of the quiz", 'quiz-master-next'); ?></td>
		</tr>
	</table>
	<?php
	$mlwQuizMasterNext->pluginHelper->generate_settings_section( 'quiz_leaderboards' );
}
?>
