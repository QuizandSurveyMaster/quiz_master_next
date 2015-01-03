<?php
function mlw_options_text_tab()
{
	echo "<li><a href=\"#tabs-2\">Text</a></li>";
}

function mlw_options_text_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Submit saved templates into database
	if ( isset($_POST["save_templates"]) && $_POST["save_templates"] == "confirmation")
	{
		//Variables for save templates form
		$mlw_before_message = htmlspecialchars($_POST["mlw_quiz_before_message"], ENT_QUOTES);
		$mlw_qmn_message_end = htmlspecialchars($_POST["message_end_template"], ENT_QUOTES);
		$mlw_user_tries_text = htmlspecialchars($_POST["mlw_quiz_total_user_tries_text"], ENT_QUOTES);
		$mlw_submit_button_text = htmlspecialchars($_POST["mlw_submitText"], ENT_QUOTES);
		$mlw_name_field_text = htmlspecialchars($_POST["mlw_nameText"], ENT_QUOTES);
		$mlw_business_field_text = htmlspecialchars($_POST["mlw_businessText"], ENT_QUOTES);
		$mlw_email_field_text = htmlspecialchars($_POST["mlw_emailText"], ENT_QUOTES);
		$mlw_phone_field_text = htmlspecialchars($_POST["mlw_phoneText"], ENT_QUOTES);
		$mlw_before_comments = htmlspecialchars($_POST["mlw_quiz_before_comments"], ENT_QUOTES);
		$mlw_comment_field_text = htmlspecialchars($_POST["mlw_commentText"], ENT_QUOTES);
		$mlw_require_log_in_text = htmlspecialchars($_POST["mlw_require_log_in_text"], ENT_QUOTES);
		$mlw_scheduled_timeframe_text = htmlspecialchars($_POST["mlw_scheduled_timeframe_text"], ENT_QUOTES);
		$mlw_limit_total_entries_text = htmlspecialchars($_POST["mlw_limit_total_entries_text"], ENT_QUOTES);
		$mlw_qmn_pagination_field = serialize(array( $_POST["pagination_prev_text"], $_POST["pagination_next_text"] ));
		$qmn_social_media_text = serialize(array('twitter' => $_POST["mlw_quiz_twitter_text_template"], 'facebook' => $_POST["mlw_quiz_facebook_text_template"]));
		$mlw_email_from_text = $_POST["emailFromText"];
		$mlw_question_answer_template = htmlspecialchars($_POST["mlw_quiz_question_answer_template"], ENT_QUOTES);
		$quiz_id = $_POST["quiz_id"];
		
		$update = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET message_before='".$mlw_before_message."', message_comment='".$mlw_before_comments."', message_end_template='".$mlw_qmn_message_end."', comment_field_text='".$mlw_comment_field_text."', email_from_text='".$mlw_email_from_text."', question_answer_template='".$mlw_question_answer_template."', submit_button_text='".$mlw_submit_button_text."', name_field_text='".$mlw_name_field_text."', business_field_text='".$mlw_business_field_text."', email_field_text='".$mlw_email_field_text."', phone_field_text='".$mlw_phone_field_text."', total_user_tries_text='".$mlw_user_tries_text."', social_media_text='".$qmn_social_media_text."', pagination_text='".$mlw_qmn_pagination_field."', require_log_in_text='".$mlw_require_log_in_text."', limit_total_entries_text='".$mlw_limit_total_entries_text."', last_activity='".date("Y-m-d H:i:s")."', scheduled_timeframe_text='".$mlw_scheduled_timeframe_text."' WHERE quiz_id=".$quiz_id;
		$results = $wpdb->query( $update );
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The templates has been updated successfully.', 'success');
		
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Templates Have Been Edited For Quiz Number ".$quiz_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0007.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	
	//Load Pagination Text
    	$mlw_qmn_pagination_text = "";
    	if (is_serialized($mlw_quiz_options->pagination_text) && is_array(@unserialize($mlw_quiz_options->pagination_text))) 
	{
		$mlw_qmn_pagination_text = @unserialize($mlw_quiz_options->pagination_text);
	}
	else
	{
		$mlw_qmn_pagination_text = array('Previous', $mlw_quiz_options->pagination_text);
	}
	
	//Load Social Media Text
	$qmn_social_media_text = "";
	if (is_serialized($mlw_quiz_options->social_media_text) && is_array(@unserialize($mlw_quiz_options->social_media_text))) 
	{
		$qmn_social_media_text = @unserialize($mlw_quiz_options->social_media_text);
	}
	else
	{
		$qmn_social_media_text = array(
        		'twitter' => $mlw_quiz_options->social_media_text,
        		'facebook' => $mlw_quiz_options->social_media_text
        	);
	}
	?>
	<div id="tabs-2" class="mlw_tab_content">
			<h3 style="text-align: center;">Template Variables</h3>
			<table class="form-table">
			<tr>
				<td><strong>%POINT_SCORE%</strong> - Total points user earned when taking quiz</td>
				<td><strong>%AVERAGE_POINT%</strong> - The average amount of points user had per question</td>
			</tr>
	
			<tr>
				<td><strong>%AMOUNT_CORRECT%</strong> - The number of correct answers the user had</td>
				<td><strong>%TOTAL_QUESTIONS%</strong> - The total number of questions in the quiz</td>
			</tr>
			
			<tr>
				<td><strong>%CORRECT_SCORE%</strong> - The percent score for the quiz showing percent of total quetions answered correctly</td>
			</tr>
	
			<tr>
				<td><strong>%USER_NAME%</strong> - The name the user entered before the quiz</td>
				<td><strong>%USER_BUSINESS%</strong> - The business the user entered before the quiz</td>
			</tr>
			
			<tr>
				<td><strong>%USER_PHONE%</strong> - The phone number the user entered before the quiz</td>
				<td><strong>%USER_EMAIL%</strong> - The email the user entered before the quiz</td>
			</tr>
			
			<tr>
				<td><strong>%QUIZ_NAME%</strong> - The name of the quiz</td>
				<td><strong>%QUESTIONS_ANSWERS%</strong> - Shows the question, the answer the user provided, and the correct answer</td>
			</tr>
			
			<tr>
				<td><strong>%COMMENT_SECTION%</strong> - The comments the user entered into comment box if enabled</td>
				<td><strong>%QUESTION%</strong> - The question that the user answered</td>
			</tr>
			
			<tr>
				<td><strong>%USER_ANSWER%</strong> - The answer the user gave for the question</td>
				<td><strong>%CORRECT_ANSWER%</strong> - The correct answer for the question</td>
			</tr>
			
			<tr>
				<td><strong>%USER_COMMENTS%</strong> - The comments the user provided in the comment field for the question</td>
				<td><strong>%CORRECT_ANSWER_INFO%</strong> - Reason why the correct answer is the correct answer</td>
			</tr>
			<tr>
				<td><strong>%TIMER%</strong> - The amount of time user spent of quiz</td>
				<td><strong>%CERTIFICATE_LINK%</strong> - The link to the certificate after completing the quiz</td>
			</tr>
			<tr>
				<td><strong>%CURRENT_DATE%</strong> - The Current Date</td>
			</tr>
			</table>
			<button id="save_template_button" class="button" onclick="javascript: document.quiz_template_form.submit();">Save Templates</button>
			<?php
			echo "<form action='' method='post' name='quiz_template_form'>";
			echo "<input type='hidden' name='save_templates' value='confirmation' />";
			echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
			?>
			<h3 style="text-align: center;">Message Templates</h3>
			<table class="form-table">
				<tr>
					<td width="30%">
						<strong>Message Displayed Before Quiz</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->message_before, ENT_QUOTES), 'mlw_quiz_before_message' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Message Displayed Before Comments Box If Enabled</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->message_comment, ENT_QUOTES), 'mlw_quiz_before_comments' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Message Displayed At End Of Quiz (Leave Blank To Omit Text Section)</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->message_end_template, ENT_QUOTES), 'message_end_template' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Message Displayed If User Has Tried Quiz Too Many Times</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->total_user_tries_text, ENT_QUOTES), 'mlw_quiz_total_user_tries_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Message Displayed If User Is Not Logged In And Quiz Requires Users To Be Logged In</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->require_log_in_text, ENT_QUOTES), 'mlw_require_log_in_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Message Displayed If Date Is Outside Scheduled Timeframe</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->scheduled_timeframe_text, ENT_QUOTES), 'mlw_scheduled_timeframe_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Message Displayed If The Limit Of Total Entries Has Been Reached</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->limit_total_entries_text, ENT_QUOTES), 'mlw_limit_total_entries_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>%QUESTIONS_ANSWERS% Text</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %QUESTION%</p>
						<p style="margin: 2px 0">- %USER_ANSWER%</p>
						<p style="margin: 2px 0">- %CORRECT_ANSWER%</p>
						<p style="margin: 2px 0">- %USER_COMMENTS%</p>
						<p style="margin: 2px 0">- %CORRECT_ANSWER_INFO%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->question_answer_template, ENT_QUOTES), 'mlw_quiz_question_answer_template' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Twitter Sharing Text</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %POINT_SCORE%</p>
						<p style="margin: 2px 0">- %AVERAGE_POINT%</p>
						<p style="margin: 2px 0">- %AMOUNT_CORRECT%</p>
						<p style="margin: 2px 0">- %TOTAL_QUESTIONS%</p>
						<p style="margin: 2px 0">- %CORRECT_SCORE%</p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %TIMER%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($qmn_social_media_text["twitter"], ENT_QUOTES), 'mlw_quiz_twitter_text_template' ); ?></td>
					</td>
				</tr>
				<tr>
					<td width="30%">
						<strong>Facebook Sharing Text</strong>
						<br />
						<p>Allowed Variables: </p>
						<p style="margin: 2px 0">- %POINT_SCORE%</p>
						<p style="margin: 2px 0">- %AVERAGE_POINT%</p>
						<p style="margin: 2px 0">- %AMOUNT_CORRECT%</p>
						<p style="margin: 2px 0">- %TOTAL_QUESTIONS%</p>
						<p style="margin: 2px 0">- %CORRECT_SCORE%</p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %TIMER%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($qmn_social_media_text["facebook"], ENT_QUOTES), 'mlw_quiz_facebook_text_template' ); ?></td>
				</tr>
			</table>
			<h3 style="text-align: center;">Other Templates</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="mlw_submitText">Text for submit button</label></th>
					<td><input name="mlw_submitText" type="text" id="mlw_submitText" value="<?php echo $mlw_quiz_options->submit_button_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_nameText">Text for name field</label></th>
					<td><input name="mlw_nameText" type="text" id="mlw_nameText" value="<?php echo $mlw_quiz_options->name_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_businessText">Text for business field</label></th>
					<td><input name="mlw_businessText" type="text" id="mlw_businessText" value="<?php echo $mlw_quiz_options->business_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_emailText">Text for email field</label></th>
					<td><input name="mlw_emailText" type="text" id="mlw_emailText" value="<?php echo $mlw_quiz_options->email_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_phoneText">Text for phone number field</label></th>
					<td><input name="mlw_phoneText" type="text" id="mlw_phoneText" value="<?php echo $mlw_quiz_options->phone_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_commentText">Text for comments field</label></th>
					<td><input name="mlw_commentText" type="text" id="mlw_commentText" value="<?php echo $mlw_quiz_options->comment_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="pagination_prev_text">Text for previous button</label></th>
					<td><input name="pagination_prev_text" type="text" id="pagination_prev_text" value="<?php echo $mlw_qmn_pagination_text[0]; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="pagination_next_text">Text for next button</label></th>
					<td><input name="pagination_next_text" type="text" id="pagination_next_text" value="<?php echo $mlw_qmn_pagination_text[1]; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="emailFromText">What is the From Name for the email sent to users and admin?</label></th>
					<td><input name="emailFromText" type="text" id="emailFromText" value="<?php echo $mlw_quiz_options->email_from_text; ?>" class="regular-text" /></td>
				</tr>
			</table>
			<button id="save_template_button" class="button" onclick="javascript: document.quiz_template_form.submit();">Save Templates</button>
			<?php echo "</form>"; ?>
  		</div>
	<?php
}
add_action('mlw_qmn_options_tab', 'mlw_options_text_tab');
add_action('mlw_qmn_options_tab_content', 'mlw_options_text_tab_content');
?>
