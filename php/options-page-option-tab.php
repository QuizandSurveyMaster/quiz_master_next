<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Options tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_options_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Options", 'quiz-master-next'), 'mlw_options_option_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_options_tab', 5);

/**
* Adds the options content to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function mlw_options_option_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Submit saved options into database
	if ( isset($_POST["save_options"]) && $_POST["save_options"] == "confirmation")
	{
		//Variables for save options form
		$mlw_system = intval($_POST["system"]);
		$mlw_qmn_pagination = intval($_POST["pagination"]);
		$mlw_qmn_social_media = intval($_POST["social_media"]);
		$mlw_qmn_question_numbering = intval($_POST["question_numbering"]);
		$mlw_qmn_timer = intval($_POST["timer_limit"]);
		$mlw_qmn_questions_from_total = intval($_POST["question_from_total"]);
		$mlw_randomness_order = intval($_POST["randomness_order"]);
		$mlw_total_user_tries = intval($_POST["total_user_tries"]);
		$mlw_require_log_in = intval($_POST["require_log_in"]);
		$mlw_limit_total_entries = intval($_POST["limit_total_entries"]);
		$mlw_contact_location = intval($_POST["contact_info_location"]);
		$mlw_user_name = intval($_POST["userName"]);
		$mlw_user_comp = intval($_POST["userComp"]);
		$mlw_user_email = intval($_POST["userEmail"]);
		$mlw_user_phone = intval($_POST["userPhone"]);
		$disable_answer_onselect = intval($_POST["disable_answer_onselect"]);
		$ajax_show_correct = intval($_POST["ajax_show_correct"]);
		$mlw_comment_section = intval($_POST["commentSection"]);
		$mlw_qmn_loggedin_contact = intval($_POST["loggedin_user_contact"]);
		$qmn_scheduled_timeframe = serialize( array(
			'start' => sanitize_text_field( $_POST["scheduled_time_start"] ),
			'end' => sanitize_text_field( $_POST["scheduled_time_end"] )
		));
		$quiz_id = intval( $_POST["quiz_id"] );

		$results = $wpdb->update(
			$wpdb->prefix . "mlw_quizzes",
			array(
			 	'system' => $mlw_system,
				'loggedin_user_contact' => $mlw_qmn_loggedin_contact,
				'contact_info_location' => $mlw_contact_location,
				'user_name' => $mlw_user_name,
				'user_comp' => $mlw_user_comp,
				'user_email' => $mlw_user_email,
				'user_phone' => $mlw_user_phone,
				'comment_section' => $mlw_comment_section,
				'randomness_order' => $mlw_randomness_order,
				'question_from_total' => $mlw_qmn_questions_from_total,
				'total_user_tries' => $mlw_total_user_tries,
				'social_media' => $mlw_qmn_social_media,
				'pagination' => $mlw_qmn_pagination,
				'timer_limit' => $mlw_qmn_timer,
				'question_numbering' => $mlw_qmn_question_numbering,
				'require_log_in' => $mlw_require_log_in,
				'limit_total_entries' => $mlw_limit_total_entries,
				'last_activity' => date("Y-m-d H:i:s"),
				'scheduled_timeframe' => $qmn_scheduled_timeframe,
				'disable_answer_onselect' => $disable_answer_onselect,
				'ajax_show_correct' => $ajax_show_correct
			),
			array( 'quiz_id' => $quiz_id ),
			array(
			 	'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d'
			),
			array( '%d' )
		);
		if ( false != $results ) {
			$mlwQuizMasterNext->alertManager->newAlert(__('The options has been updated successfully.', 'quiz-master-next'), 'success');
			$mlwQuizMasterNext->audit_manager->new_audit( "Options Have Been Edited For Quiz Number $quiz_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0008'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0008", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}

	//Load Scheduled Timeframe
    	$qmn_scheduled_timeframe = "";
	if (is_serialized($mlw_quiz_options->scheduled_timeframe) && is_array(@unserialize($mlw_quiz_options->scheduled_timeframe)))
	{
		$qmn_scheduled_timeframe = @unserialize($mlw_quiz_options->scheduled_timeframe);
	}
	else
	{
		$qmn_scheduled_timeframe = array("start" => '', "end" => '');
	}
	?>
	<div id="tabs-3" class="mlw_tab_content">
		<script>
			jQuery(function() {
    			//jQuery( "#system, #require_log_in, #randomness_order, #loggedin_user_contact, #sendUserEmail, #sendAdminEmail, #contact_info_location, #userName, #userComp, #userEmail, #userPhone, #pagination, #commentSection, #social_media, #question_numbering, #comments" ).buttonset();
    			jQuery( "#scheduled_time_start, #scheduled_time_end" ).datepicker();
			});
		</script>
		<button id="save_options_button" class="button-primary" onclick="javascript: document.quiz_options_form.submit();"><?php _e('Save Options', 'quiz-master-next'); ?></button>
		<?php
		echo "<form action='' method='post' name='quiz_options_form'>";
		echo "<input type='hidden' name='save_options' value='confirmation' />";
		echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
		?>
		<table class="form-table" style="width: 100%;">
			<tr valign="top">
				<th scope="row"><label for="system"><?php _e('Which system is this quiz graded on?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio1" name="system" <?php if ($mlw_quiz_options->system == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio1"><?php _e('Correct/Incorrect', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio2" name="system" <?php if ($mlw_quiz_options->system == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio2"><?php _e('Points', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio3" name="system" <?php if ($mlw_quiz_options->system == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio3"><?php _e('Not Graded', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="require_log_in"><?php _e('Should the user be required to be logged in to take this quiz?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio_login_1" name="require_log_in" <?php if ($mlw_quiz_options->require_log_in == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio_login_1"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio_login_2" name="require_log_in" <?php if ($mlw_quiz_options->require_log_in == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio_login_2"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="pagination"><?php _e('How many questions per page would you like? (Leave 0 for all questions on one page)', 'quiz-master-next'); ?></label></th>
				<td>
					<input type="number" step="1" min="0" max="1000" name="pagination" value="<?php echo $mlw_quiz_options->pagination; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="timer_limit"><?php _e('How many minutes does the user have to finish the quiz? (Leave 0 for no time limit)', 'quiz-master-next'); ?></label></th>
				<td>
				    <input name="timer_limit" type="number" step="1" min="0" id="timer_limit" value="<?php echo $mlw_quiz_options->timer_limit; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="total_user_tries"><?php _e('How many times can a user take this quiz? (Leave 0 for as many times as the user wants to. Currently only works for registered users)', 'quiz-master-next'); ?></label></th>
				<td>
				    <input name="total_user_tries" type="number" step="1" min="0" id="total_user_tries" value="<?php echo $mlw_quiz_options->total_user_tries; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="limit_total_entries"><?php _e('How many total entries can this quiz have? (Leave 0 for unlimited entries', 'quiz-master-next'); ?>)</label></th>
				<td>
				    <input name="limit_total_entries" type="number" step="1" min="0" id="limit_total_entries" value="<?php echo $mlw_quiz_options->limit_total_entries; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="question_from_total"><?php _e('How many questions should be loaded for quiz? (Leave 0 to load all questions)', 'quiz-master-next'); ?></label></th>
				<td>
				    <input name="question_from_total" type="number" step="1" min="0" id="question_from_total" value="<?php echo $mlw_quiz_options->question_from_total; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="scheduled_time_start"><?php _e('What time-frame should the user be able to access the quiz? (Leave blank if the user can access anytime)', 'quiz-master-next'); ?></label></th>
				<td>
				    <input name="scheduled_time_start" placeholder="<?php _e('start date', 'quiz-master-next'); ?>" type="text" id="scheduled_time_start" value="<?php echo $qmn_scheduled_timeframe["start"] ?>" class="regular-text" />
				</td>
				<td>
				    <input name="scheduled_time_end" type="text" placeholder="<?php _e('end date', 'quiz-master-next'); ?>" id="scheduled_time_end" value="<?php echo $qmn_scheduled_timeframe["end"] ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="randomness_order"><?php _e('Are the questions random? (Question Order will not apply if this is yes)', 'quiz-master-next'); ?></label></th>
				<td>
					<input type="radio" id="radio24" name="randomness_order" <?php if ($mlw_quiz_options->randomness_order == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio24"><?php _e('Random Questions', 'quiz-master-next'); ?></label><br>
					<input type="radio" id="randomness2" name="randomness_order" <?php if ($mlw_quiz_options->randomness_order == 2) {echo 'checked="checked"';} ?> value='2' /><label for="randomness2"><?php _e('Random Questions And Answers', 'quiz-master-next'); ?></label><br>
					<input type="radio" id="randomness3" name="randomness_order" <?php checked( $mlw_quiz_options->randomness_order, 3 ); ?>value='3'><label for="randomness3"><?php _e('Random Answers', 'quiz-master-next'); ?></label><br>
				  <input type="radio" id="radio23" name="randomness_order" <?php if ($mlw_quiz_options->randomness_order == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio23"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="contact_info_location"><?php _e('Would you like to ask for the contact information at the beginning or at the end of the quiz?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio25" name="contact_info_location" <?php if ($mlw_quiz_options->contact_info_location == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio25"><?php _e('Beginning', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio26" name="contact_info_location" <?php if ($mlw_quiz_options->contact_info_location == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio26"><?php _e('End', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="loggedin_user_contact"><?php _e('If a logged-in user takes the quiz, would you like them to be able to edit contact information? If set to no, the fields will not show up for logged in users; however, the users information will be saved for the fields.', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio27" name="loggedin_user_contact" <?php if ($mlw_quiz_options->loggedin_user_contact == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio27"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio28" name="loggedin_user_contact" <?php if ($mlw_quiz_options->loggedin_user_contact == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio28"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userName"><?php _e('Should we ask for users name?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio7" name="userName" <?php if ($mlw_quiz_options->user_name == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio7"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio8" name="userName" <?php if ($mlw_quiz_options->user_name == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio8"><?php _e('Require', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio9" name="userName" <?php if ($mlw_quiz_options->user_name == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio9"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userComp"><?php _e('Should we ask for users business?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio10" name="userComp" <?php if ($mlw_quiz_options->user_comp == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio10"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio11" name="userComp" <?php if ($mlw_quiz_options->user_comp == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio11"><?php _e('Require', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio12" name="userComp" <?php if ($mlw_quiz_options->user_comp == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio12"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userEmail"><?php _e('Should we ask for users email?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio13" name="userEmail" <?php if ($mlw_quiz_options->user_email == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio13"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio14" name="userEmail" <?php if ($mlw_quiz_options->user_email == 1) {echo 'checked="checked"';} ?> value='1'/><label for="radio14"><?php _e('Require', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio15" name="userEmail" <?php if ($mlw_quiz_options->user_email == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio15"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userPhone"><?php _e('Should we ask for users phone number?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio16" name="userPhone" <?php if ($mlw_quiz_options->user_phone == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio16"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio17" name="userPhone" <?php if ($mlw_quiz_options->user_phone == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio17"><?php _e('Require', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio18" name="userPhone" <?php if ($mlw_quiz_options->user_phone == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio18"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="commentSection"><?php _e('Would you like a place for the user to enter comments?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="radio21" name="commentSection" <?php if ($mlw_quiz_options->comment_section == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio21"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio22" name="commentSection" <?php if ($mlw_quiz_options->comment_section == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio22"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="question_numbering"><?php _e('Show question number on quiz?', 'quiz-master-next'); ?></label></th>
				<td>
				    <input type="radio" id="question_numbering_radio2" name="question_numbering" <?php if ($mlw_quiz_options->question_numbering == 1) {echo 'checked="checked"';} ?> value='1' /><label for="question_numbering_radio2"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="question_numbering_radio" name="question_numbering" <?php if ($mlw_quiz_options->question_numbering == 0) {echo 'checked="checked"';} ?> value='0' /><label for="question_numbering_radio"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="social_media"><?php _e('Show social media sharing buttons? (Twitter & Facebook)', 'quiz-master-next'); _e('This option is for here only for users of older versions. Please use the new template variables %FACEBOOK_SHARE% %TWITTER_SHARE% on your results pages instead of using this option!', 'quiz-master-next'); ?></label></th>
				<td>
					<input type="radio" id="social_media_radio2" name="social_media" <?php if ($mlw_quiz_options->social_media == 1) {echo 'checked="checked"';} ?> value='1' /><label for="social_media_radio2"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				  <input type="radio" id="social_media_radio" name="social_media" <?php if ($mlw_quiz_options->social_media == 0) {echo 'checked="checked"';} ?> value='0' /><label for="social_media_radio"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="disable_answer_onselect"><?php _e('Disable question once user selects answer? (Currently only work on multiple choice)', 'quiz-master-next'); ?></label></th>
				<td>
					<input type="radio" id="disable_answer_radio2" name="disable_answer_onselect" <?php if ($mlw_quiz_options->disable_answer_onselect == 1) {echo 'checked="checked"';} ?> value='1' /><label for="disable_answer_radio2"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				  <input type="radio" id="disable_answer_radio" name="disable_answer_onselect" <?php if ($mlw_quiz_options->disable_answer_onselect == 0) {echo 'checked="checked"';} ?> value='0' /><label for="disable_answer_radio"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ajax_show_correct"><?php _e('Dynamically add class for incorrect/correct answer after user selects answer? (Currently only works on multiple choice)', 'quiz-master-next'); ?></label></th>
				<td>
					<input type="radio" id="ajax_show_correct_radio2" name="ajax_show_correct" <?php if ($mlw_quiz_options->ajax_show_correct == 1) {echo 'checked="checked"';} ?> value='1' /><label for="ajax_show_correct_radio2"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				  <input type="radio" id="ajax_show_correct_radio" name="ajax_show_correct" <?php if ($mlw_quiz_options->ajax_show_correct == 0) {echo 'checked="checked"';} ?> value='0' /><label for="ajax_show_correct_radio"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
		</table>
		<button id="save_options_button" class="button-primary" onclick="javascript: document.quiz_options_form.submit();"><?php _e('Save Options', 'quiz-master-next'); ?></button>
		</form>
  		</div>
	<?php
}
?>
