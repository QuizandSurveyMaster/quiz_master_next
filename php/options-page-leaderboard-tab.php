<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the leaderboard to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_leaderboard_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Leaderboard", 'quiz-master-next'), 'mlw_options_leaderboard_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_leaderboard_tab', 5);


/**
* Adds the leaderboard content to the leaderboard tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_leaderboard_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = intval( $_GET["quiz_id"] );
	///Submit saved leaderboard template into database
	if ( isset($_POST["save_leaderboard_options"]) && $_POST["save_leaderboard_options"] == "confirmation")
	{
		///Variables for save leaderboard options form
		$mlw_leaderboard_template = wp_kses_post( $_POST["mlw_quiz_leaderboard_template"] );
		$mlw_leaderboard_quiz_id = intval( $_POST["leaderboard_quiz_id"] );
		$results = $wpdb->update(
			$wpdb->prefix . "mlw_quizzes",
			array(
				'leaderboard_template' => $mlw_leaderboard_template,
				'last_activity' => date("Y-m-d H:i:s")
			),
			array( 'quiz_id' => $mlw_leaderboard_quiz_id ),
			array(
				'%s',
				'%s'
			),
			array( '%d' )
		);
		if ( $results ) {
			$mlwQuizMasterNext->alertManager->newAlert(__('The leaderboards has been updated successfully.', 'quiz-master-next'), 'success');
			$mlwQuizMasterNext->audit_manager->new_audit( "Leaderboard Options Have Been Edited For Quiz Number $mlw_leaderboard_quiz_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0009'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0009", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	?>
	<div id="tabs-4" class="mlw_tab_content">
		<h3><?php _e('Template Variables', 'quiz-master-next'); ?></h3>
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
		<button id="save_template_button" class="button-primary" onclick="javascript: document.quiz_leaderboard_options_form.submit();"><?php _e("Save Leaderboard Options", 'quiz-master-next'); ?></button>
		<?php
			echo "<form action='' method='post' name='quiz_leaderboard_options_form'>";
			echo "<input type='hidden' name='save_leaderboard_options' value='confirmation' />";
			echo "<input type='hidden' name='leaderboard_quiz_id' value='".$quiz_id."' />";
		?>
    	<table class="form-table">
			<tr>
				<td width="30%">
					<strong><?php _e("Leaderboard Template", 'quiz-master-next'); ?></strong>
					<br />
					<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
					<p style="margin: 2px 0">- %QUIZ_NAME%</p>
					<p style="margin: 2px 0">- %FIRST_PLACE_NAME%</p>
					<p style="margin: 2px 0">- %FIRST_PLACE_SCORE%</p>
					<p style="margin: 2px 0">- %SECOND_PLACE_NAME%</p>
					<p style="margin: 2px 0">- %SECOND_PLACE_SCORE%</p>
					<p style="margin: 2px 0">- %THIRD_PLACE_NAME%</p>
					<p style="margin: 2px 0">- %THIRD_PLACE_SCORE%</p>
					<p style="margin: 2px 0">- %FOURTH_PLACE_NAME%</p>
					<p style="margin: 2px 0">- %FOURTH_PLACE_SCORE%</p>
					<p style="margin: 2px 0">- %FIFTH_PLACE_NAME%</p>
					<p style="margin: 2px 0">- %FIFTH_PLACE_SCORE%</p>
				</td>
				<td><textarea cols="80" rows="15" id="mlw_quiz_leaderboard_template" name="mlw_quiz_leaderboard_template"><?php echo $mlw_quiz_options->leaderboard_template; ?></textarea>
				</td>
			</tr>
		</table>
		<button id="save_template_button" class="button-primary" onclick="javascript: document.quiz_leaderboard_options_form.submit();"><?php _e("Save Leaderboard Options", 'quiz-master-next'); ?></button>
		</form>
	</div>
	<?php
}
?>
