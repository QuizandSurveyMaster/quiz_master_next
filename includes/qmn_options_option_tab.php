<?php
function qmn_settings_options_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs("Options", 'mlw_options_option_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_options_tab');

function mlw_options_option_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Submit saved options into database
	if ( isset($_POST["save_options"]) && $_POST["save_options"] == "confirmation")
	{
		//Variables for save options form
		$mlw_system = $_POST["system"];
		$mlw_qmn_pagination = intval($_POST["pagination"]);
		$mlw_qmn_social_media = intval($_POST["social_media"]);
		$mlw_qmn_question_numbering = intval($_POST["question_numbering"]);
		$mlw_qmn_timer = intval($_POST["timer_limit"]);
		$mlw_qmn_questions_from_total = $_POST["question_from_total"];
		$mlw_randomness_order = $_POST["randomness_order"];
		$mlw_total_user_tries = intval($_POST["total_user_tries"]);
		$mlw_require_log_in = $_POST["require_log_in"];
		$mlw_limit_total_entries = $_POST["limit_total_entries"];
		$mlw_contact_location = $_POST["contact_info_location"];
		$mlw_user_name = $_POST["userName"];
		$mlw_user_comp = $_POST["userComp"];
		$mlw_user_email = $_POST["userEmail"];
		$mlw_user_phone = $_POST["userPhone"];
		$mlw_comment_section = $_POST["commentSection"];
		$mlw_qmn_loggedin_contact = $_POST["loggedin_user_contact"];
		$qmn_scheduled_timeframe = serialize(array("start" => $_POST["scheduled_time_start"], "end" => $_POST["scheduled_time_end"]));
		$quiz_id = $_POST["quiz_id"];
		
		$update = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET system='".$mlw_system."', loggedin_user_contact='".$mlw_qmn_loggedin_contact."', contact_info_location=".$mlw_contact_location.", user_name='".$mlw_user_name."', user_comp='".$mlw_user_comp."', user_email='".$mlw_user_email."', user_phone='".$mlw_user_phone."', comment_section='".$mlw_comment_section."', randomness_order='".$mlw_randomness_order."', question_from_total=".$mlw_qmn_questions_from_total.", total_user_tries=".$mlw_total_user_tries.", social_media=".$mlw_qmn_social_media.", pagination=".$mlw_qmn_pagination.", timer_limit=".$mlw_qmn_timer.", question_numbering=".$mlw_qmn_question_numbering.", require_log_in=".$mlw_require_log_in.", limit_total_entries=".$mlw_limit_total_entries.", last_activity='".date("Y-m-d H:i:s")."', scheduled_timeframe='".$qmn_scheduled_timeframe."' WHERE quiz_id=".$quiz_id;
		$results = $wpdb->query( $update );
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The options has been updated successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Options Have Been Edited For Quiz Number ".$quiz_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0008.', 'error');
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
    			jQuery( "#system, #require_log_in, #randomness_order, #loggedin_user_contact, #sendUserEmail, #sendAdminEmail, #contact_info_location, #userName, #userComp, #userEmail, #userPhone, #pagination, #commentSection, #social_media, #question_numbering, #comments" ).buttonset();
    			jQuery( "#scheduled_time_start, #scheduled_time_end" ).datepicker();
			});
		</script>
		<button id="save_options_button" class="button" onclick="javascript: document.quiz_options_form.submit();">Save Options</button>
		<?php
		echo "<form action='' method='post' name='quiz_options_form'>";
		echo "<input type='hidden' name='save_options' value='confirmation' />";
		echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
		?>
		<table class="form-table" style="width: 100%;">
			<tr valign="top">
				<th scope="row"><label for="system">Which system is this quiz graded on?</label></th>
				<td><div id="system">
				    <input type="radio" id="radio1" name="system" <?php if ($mlw_quiz_options->system == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio1">Correct/Incorrect</label>
				    <input type="radio" id="radio2" name="system" <?php if ($mlw_quiz_options->system == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio2">Points</label>
				    <input type="radio" id="radio3" name="system" <?php if ($mlw_quiz_options->system == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio3">Not Graded</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="require_log_in">Should the user be required to be logged in to take this quiz?</label></th>
				<td><div id="require_log_in">
				    <input type="radio" id="radio_login_1" name="require_log_in" <?php if ($mlw_quiz_options->require_log_in == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio_login_1">Yes</label>
				    <input type="radio" id="radio_login_2" name="require_log_in" <?php if ($mlw_quiz_options->require_log_in == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio_login_2">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="pagination">How many questions per page would you like? (Leave 0 for all questions on one page)</label></th>
				<td>
					<input type="number" step="1" min="0" max="1000" name="pagination" value="<?php echo $mlw_quiz_options->pagination; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="timer_limit">How many minutes does the user have to finish the quiz? (Leave 0 for no time limit)</label></th>
				<td>
				    <input name="timer_limit" type="number" step="1" min="0" id="timer_limit" value="<?php echo $mlw_quiz_options->timer_limit; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="total_user_tries">How many times can a user take this quiz? (Leave 0 for as many times as the user wants to. Currently only works for registered users)</label></th>
				<td>
				    <input name="total_user_tries" type="number" step="1" min="0" id="total_user_tries" value="<?php echo $mlw_quiz_options->total_user_tries; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="limit_total_entries">How many total entries can this quiz have? (Leave 0 for unlimited entries)</label></th>
				<td>
				    <input name="limit_total_entries" type="number" step="1" min="0" id="limit_total_entries" value="<?php echo $mlw_quiz_options->limit_total_entries; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="question_from_total">How many questions should be loaded for quiz? (Leave 0 to load all questions)</label></th>
				<td>
				    <input name="question_from_total" type="number" step="1" min="0" id="question_from_total" value="<?php echo $mlw_quiz_options->question_from_total; ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="scheduled_time_start">What time-frame should the user be able to access the quiz? (Leave blank if the user can access anytime)</label></th>
				<td>
				    <input name="scheduled_time_start" placeholder="start date" type="text" id="scheduled_time_start" value="<?php echo $qmn_scheduled_timeframe["start"] ?>" class="regular-text" />
				</td>
				<td>
				    <input name="scheduled_time_end" type="text" placeholder="end date" id="scheduled_time_end" value="<?php echo $qmn_scheduled_timeframe["end"] ?>" class="regular-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="randomness_order">Are the questions random? (Question Order will not apply if this is yes)</label></th>
				<td><div id="randomness_order">
					<input type="radio" id="radio24" name="randomness_order" <?php if ($mlw_quiz_options->randomness_order == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio24">Random Questions</label>
					<input type="radio" id="randomness2" name="randomness_order" <?php if ($mlw_quiz_options->randomness_order == 2) {echo 'checked="checked"';} ?> value='2' /><label for="randomness2">Random Questions And Answers</label>
				    <input type="radio" id="radio23" name="randomness_order" <?php if ($mlw_quiz_options->randomness_order == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio23">No</label>
				</div></td>
			</tr>			
			<tr valign="top">
				<th scope="row"><label for="contact_info_location">Would you like to ask for the contact information at the beginning or at the end of the quiz?</label></th>
				<td><div id="contact_info_location">
				    <input type="radio" id="radio25" name="contact_info_location" <?php if ($mlw_quiz_options->contact_info_location == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio25">Beginning</label>
				    <input type="radio" id="radio26" name="contact_info_location" <?php if ($mlw_quiz_options->contact_info_location == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio26">End</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="loggedin_user_contact">If a logged-in user takes the quiz, would you like them to be able to edit contact information? If set to no, the fields will not show up for logged in users; however, the users information will be saved for the fields.</label></th>
				<td><div id="loggedin_user_contact">
				    <input type="radio" id="radio27" name="loggedin_user_contact" <?php if ($mlw_quiz_options->loggedin_user_contact == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio27">Yes</label>
				    <input type="radio" id="radio28" name="loggedin_user_contact" <?php if ($mlw_quiz_options->loggedin_user_contact == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio28">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userName">Should we ask for user's name?</label></th>
				<td><div id="userName">
				    <input type="radio" id="radio7" name="userName" <?php if ($mlw_quiz_options->user_name == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio7">Yes</label>
				    <input type="radio" id="radio8" name="userName" <?php if ($mlw_quiz_options->user_name == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio8">Require</label>
				    <input type="radio" id="radio9" name="userName" <?php if ($mlw_quiz_options->user_name == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio9">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userComp">Should we ask for user's business?</label></th>
				<td><div id="userComp">
				    <input type="radio" id="radio10" name="userComp" <?php if ($mlw_quiz_options->user_comp == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio10">Yes</label>
				    <input type="radio" id="radio11" name="userComp" <?php if ($mlw_quiz_options->user_comp == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio11">Require</label>
				    <input type="radio" id="radio12" name="userComp" <?php if ($mlw_quiz_options->user_comp == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio12">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userEmail">Should we ask for user's email?</label></th>
				<td><div id="userEmail">
				    <input type="radio" id="radio13" name="userEmail" <?php if ($mlw_quiz_options->user_email == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio13">Yes</label>
				    <input type="radio" id="radio14" name="userEmail" <?php if ($mlw_quiz_options->user_email == 1) {echo 'checked="checked"';} ?> value='1'/><label for="radio14">Require</label>
				    <input type="radio" id="radio15" name="userEmail" <?php if ($mlw_quiz_options->user_email == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio15">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="userPhone">Should we ask for user's phone number?</label></th>
				<td><div id="userPhone">
				    <input type="radio" id="radio16" name="userPhone" <?php if ($mlw_quiz_options->user_phone == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio16">Yes</label>
				    <input type="radio" id="radio17" name="userPhone" <?php if ($mlw_quiz_options->user_phone == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio17">Require</label>
				    <input type="radio" id="radio18" name="userPhone" <?php if ($mlw_quiz_options->user_phone == 2) {echo 'checked="checked"';} ?> value='2' /><label for="radio18">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="commentSection">Would you like a place for the user to enter comments?</label></th>
				<td><div id="commentSection">
				    <input type="radio" id="radio21" name="commentSection" <?php if ($mlw_quiz_options->comment_section == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio21">Yes</label>
				    <input type="radio" id="radio22" name="commentSection" <?php if ($mlw_quiz_options->comment_section == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio22">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="question_numbering">Show question number on quiz?</label></th>
				<td><div id="question_numbering">
				    <input type="radio" id="question_numbering_radio2" name="question_numbering" <?php if ($mlw_quiz_options->question_numbering == 1) {echo 'checked="checked"';} ?> value='1' /><label for="question_numbering_radio2">Yes</label>
				    <input type="radio" id="question_numbering_radio" name="question_numbering" <?php if ($mlw_quiz_options->question_numbering == 0) {echo 'checked="checked"';} ?> value='0' /><label for="question_numbering_radio">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="social_media">Show social media sharing buttons? (Twitter & Facebook)</label></th>
				<td><div id="social_media">
					<input type="radio" id="social_media_radio2" name="social_media" <?php if ($mlw_quiz_options->social_media == 1) {echo 'checked="checked"';} ?> value='1' /><label for="social_media_radio2">Yes</label>
				    <input type="radio" id="social_media_radio" name="social_media" <?php if ($mlw_quiz_options->social_media == 0) {echo 'checked="checked"';} ?> value='0' /><label for="social_media_radio">No</label>				    
				</div></td>
			</tr>
		</table>
		<button id="save_options_button" class="button" onclick="javascript: document.quiz_options_form.submit();">Save Options</button>
		<?php echo "</form>"; ?>
  		</div>
	<?php
}
?>
