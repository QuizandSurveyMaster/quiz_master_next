<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Text tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_text_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Text", 'quiz-master-next'), 'mlw_options_text_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_text_tab', 5);

/**
* Adds the Text tab content to the tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_text_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = intval($_GET["quiz_id"]);
	//Submit saved templates into database
	if ( isset($_POST["save_templates"]) && $_POST["save_templates"] == "confirmation")
	{
		//Variables for save templates form
		$mlw_before_message = htmlspecialchars( stripslashes( $_POST["mlw_quiz_before_message"] ), ENT_QUOTES);
		$mlw_qmn_message_end = htmlspecialchars( stripslashes( $_POST["message_end_template"] ), ENT_QUOTES);
		$mlw_user_tries_text = htmlspecialchars( stripslashes( $_POST["mlw_quiz_total_user_tries_text"] ), ENT_QUOTES);
		$mlw_submit_button_text = sanitize_text_field( stripslashes( $_POST["mlw_submitText"] ) );
		$mlw_name_field_text = sanitize_text_field( stripslashes( $_POST["mlw_nameText"] ) );
		$mlw_business_field_text = sanitize_text_field( stripslashes( $_POST["mlw_businessText"] ) );
		$mlw_email_field_text = sanitize_text_field( stripslashes( $_POST["mlw_emailText"] ) );
		$mlw_phone_field_text = sanitize_text_field( stripslashes( $_POST["mlw_phoneText"] ) );
		$mlw_before_comments = htmlspecialchars(stripslashes( $_POST["mlw_quiz_before_comments"] ), ENT_QUOTES);
		$mlw_comment_field_text = htmlspecialchars(stripslashes( $_POST["mlw_commentText"] ), ENT_QUOTES);
		$mlw_require_log_in_text = htmlspecialchars(stripslashes( $_POST["mlw_require_log_in_text"] ), ENT_QUOTES);
		$mlw_scheduled_timeframe_text = htmlspecialchars(stripslashes( $_POST["mlw_scheduled_timeframe_text"] ), ENT_QUOTES);
		$mlw_limit_total_entries_text = htmlspecialchars(stripslashes( $_POST["mlw_limit_total_entries_text"] ), ENT_QUOTES);
		$mlw_qmn_pagination_field = serialize( array(
			sanitize_text_field( stripslashes( $_POST["pagination_prev_text"] ) ),
			sanitize_text_field( stripslashes( $_POST["pagination_next_text"] ) )
		));
		$qmn_social_media_text = serialize( array(
			'twitter' => wp_kses_post( stripslashes( $_POST["mlw_quiz_twitter_text_template"] ) ),
			'facebook' => wp_kses_post( stripslashes( $_POST["mlw_quiz_facebook_text_template"] ) )
		));

		$mlw_question_answer_template = htmlspecialchars(stripslashes( $_POST["mlw_quiz_question_answer_template"] ), ENT_QUOTES);
		$quiz_id = intval($_POST["quiz_id"]);

		$results = $wpdb->update(
			$wpdb->prefix . "mlw_quizzes",
			array(
				'message_before' => $mlw_before_message,
				'message_comment' => $mlw_before_comments,
				'message_end_template' => $mlw_qmn_message_end,
				'comment_field_text' => $mlw_comment_field_text,
				'question_answer_template' => $mlw_question_answer_template,
				'submit_button_text' => $mlw_submit_button_text,
				'name_field_text' => $mlw_name_field_text,
				'business_field_text' => $mlw_business_field_text,
				'email_field_text' => $mlw_email_field_text,
				'phone_field_text' => $mlw_phone_field_text,
				'total_user_tries_text' => $mlw_user_tries_text,
				'social_media_text' => $qmn_social_media_text,
				'pagination_text' => $mlw_qmn_pagination_field,
				'require_log_in_text' => $mlw_require_log_in_text,
				'limit_total_entries_text' => $mlw_limit_total_entries_text,
				'last_activity' => date("Y-m-d H:i:s"),
				'scheduled_timeframe_text' => $mlw_scheduled_timeframe_text
			),
			array( 'quiz_id' => $quiz_id ),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			),
			array( '%d' )
		);
		if ( false != $results ) {
			$mlwQuizMasterNext->alertManager->newAlert(__('The templates has been updated successfully.', 'quiz-master-next'), 'success');
			$mlwQuizMasterNext->audit_manager->new_audit( "Templates Have Been Edited For Quiz Number $quiz_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0007'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0007", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $quiz_id));
	}

	//Load Pagination Text
    	$mlw_qmn_pagination_text = "";
    	if (is_serialized($mlw_quiz_options->pagination_text) && is_array(@unserialize($mlw_quiz_options->pagination_text)))
	{
		$mlw_qmn_pagination_text = @unserialize($mlw_quiz_options->pagination_text);
	}
	else
	{
		$mlw_qmn_pagination_text = array(__('Previous', 'quiz-master-next'), $mlw_quiz_options->pagination_text);
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
	wp_enqueue_style( 'qmn_admin_style', plugins_url( '../css/qmn_admin.css' , __FILE__ ) );
	?>
	<div id="tabs-2" class="mlw_tab_content">
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
					<span class="template_name">%CERTIFICATE_LINK%</span> - <?php _e('The link to the certificate after completing the quiz', 'quiz-master-next'); ?>
				</div>
				<div class="template_variable">
					<span class="template_name">%CATEGORY_POINTS%%/CATEGORY_POINTS%</span> - <?php _e('The amount of points a specific category earned.', 'quiz-master-next'); ?>
				</div>
				<div class="template_variable">
					<span class="template_name">%CATEGORY_SCORE%%/CATEGORY_SCORE%</span> - <?php _e('The score a specific category earned.', 'quiz-master-next'); ?>
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
			<div style="clear:both;"></div>
			<br>
			<button id="save_template_button" class="button-primary" onclick="javascript: document.quiz_template_form.submit();"><?php _e("Save Templates", 'quiz-master-next'); ?></button>
			<?php
			echo "<form action='' method='post' name='quiz_template_form'>";
			echo "<input type='hidden' name='save_templates' value='confirmation' />";
			echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
			?>
			<h3 style="text-align: center;"><?php _e("Message Templates", 'quiz-master-next'); ?></h3>
			<table class="form-table">
				<tr>
					<td width="30%">
						<strong><?php _e("Message Displayed Before Quiz", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->message_before, ENT_QUOTES), 'mlw_quiz_before_message' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong><?php _e("Message Displayed Before Comments Box If Enabled", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->message_comment, ENT_QUOTES), 'mlw_quiz_before_comments' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong><?php _e("Message Displayed At End Of Quiz (Leave Blank To Omit Text Section)", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->message_end_template, ENT_QUOTES), 'message_end_template' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong><?php _e("Message Displayed If User Has Tried Quiz Too Many Times", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->total_user_tries_text, ENT_QUOTES), 'mlw_quiz_total_user_tries_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong><?php _e("Message Displayed If User Is Not Logged In And Quiz Requires Users To Be Logged In", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->require_log_in_text, ENT_QUOTES), 'mlw_require_log_in_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong><?php _e("Message Displayed If Date Is Outside Scheduled Timeframe", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->scheduled_timeframe_text, ENT_QUOTES), 'mlw_scheduled_timeframe_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong><?php _e("Message Displayed If The Limit Of Total Entries Has Been Reached", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
						<p style="margin: 2px 0">- %QUIZ_NAME%</p>
						<p style="margin: 2px 0">- %CURRENT_DATE%</p>
					</td>
					<td><?php wp_editor( htmlspecialchars_decode($mlw_quiz_options->limit_total_entries_text, ENT_QUOTES), 'mlw_limit_total_entries_text' ); ?></td>
				</tr>
				<tr>
					<td width="30%">
						<strong><?php _e("%QUESTIONS_ANSWERS% Text", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
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
						<strong><?php _e("Twitter Sharing Text", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
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
						<strong><?php _e("Facebook Sharing Text", 'quiz-master-next'); ?></strong>
						<br />
						<p><?php _e("Allowed Variables:", 'quiz-master-next'); ?></p>
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
			<h3 style="text-align: center;"><?php _e("Other Templates", 'quiz-master-next'); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="mlw_submitText"><?php _e("Text for submit button", 'quiz-master-next'); ?></label></th>
					<td><input name="mlw_submitText" type="text" id="mlw_submitText" value="<?php echo $mlw_quiz_options->submit_button_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_nameText"><?php _e("Text for name field", 'quiz-master-next'); ?></label></th>
					<td><input name="mlw_nameText" type="text" id="mlw_nameText" value="<?php echo $mlw_quiz_options->name_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_businessText"><?php _e("Text for business field", 'quiz-master-next'); ?></label></th>
					<td><input name="mlw_businessText" type="text" id="mlw_businessText" value="<?php echo $mlw_quiz_options->business_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_emailText"><?php _e("Text for email field", 'quiz-master-next'); ?></label></th>
					<td><input name="mlw_emailText" type="text" id="mlw_emailText" value="<?php echo $mlw_quiz_options->email_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_phoneText"><?php _e("Text for phone number field", 'quiz-master-next'); ?></label></th>
					<td><input name="mlw_phoneText" type="text" id="mlw_phoneText" value="<?php echo $mlw_quiz_options->phone_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mlw_commentText"><?php _e("Text for comments field", 'quiz-master-next'); ?></label></th>
					<td><input name="mlw_commentText" type="text" id="mlw_commentText" value="<?php echo $mlw_quiz_options->comment_field_text; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="pagination_prev_text"><?php _e("Text for previous button", 'quiz-master-next'); ?></label></th>
					<td><input name="pagination_prev_text" type="text" id="pagination_prev_text" value="<?php echo $mlw_qmn_pagination_text[0]; ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="pagination_next_text"><?php _e("Text for next button", 'quiz-master-next'); ?></label></th>
					<td><input name="pagination_next_text" type="text" id="pagination_next_text" value="<?php echo $mlw_qmn_pagination_text[1]; ?>" class="regular-text" /></td>
				</tr>
			</table>
			<button id="save_template_button" class="button-primary" onclick="javascript: document.quiz_template_form.submit();"><?php _e("Save Templates", 'quiz-master-next'); ?></button>
			<?php echo "</form>"; ?>
  		</div>
	<?php
}
?>
