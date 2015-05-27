<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This function adds the certificate tab using our API.
*
* Long Description
*
* @return type description
* @since 4.4.0
*/
function qmn_settings_certificate_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Certificate (Beta)", 'quiz-master-next'), 'mlw_options_certificate_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_certificate_tab', 5);

/**
* Creates the content that is in the certificate tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_certificate_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Saved Certificate Options
	if (isset($_POST["save_certificate_options"]) && $_POST["save_certificate_options"] == "confirmation")
	{
		$mlw_certificate_id = intval($_POST["certificate_quiz_id"]);
		$mlw_certificate_title = $_POST["certificate_title"];
		$mlw_certificate_text = $_POST["certificate_template"];
		$mlw_certificate_logo = $_POST["certificate_logo"];
		$mlw_certificate_background = $_POST["certificate_background"];
		$mlw_enable_certificates = intval($_POST["enableCertificates"]);
		$mlw_certificate = array($mlw_certificate_title, $mlw_certificate_text, $mlw_certificate_logo, $mlw_certificate_background, $mlw_enable_certificates);
		$mlw_certificate_serialized = serialize($mlw_certificate);

		$mlw_certificate_sql_results = $wpdb->query( $wpdb->prepare( "UPDATE " . $wpdb->prefix . "mlw_quizzes SET certificate_template=%s, last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_certificate_serialized, $mlw_certificate_id  ) );


		if ($mlw_certificate_sql_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert(__('The certificate has been updated successfully.', 'quiz-master-next'), 'success');

			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Certificate Options Have Been Edited For Quiz Number ".$mlw_certificate_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0012'), 'error');
		}
	}
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}

	//Load Certificate Options Variables
	if (is_serialized($mlw_quiz_options->certificate_template) && is_array(@unserialize($mlw_quiz_options->certificate_template)))
	{
		$mlw_certificate_options = @unserialize($mlw_quiz_options->certificate_template);
	}
	else
	{
		$mlw_certificate_options = array(__('Enter title here', 'quiz-master-next'), __('Enter text here', 'quiz-master-next'), '', '', 1);
	}
	?>
	<div id="tabs-5" class="mlw_tab_content">
		<h3><?php _e('Quiz Certificate (Beta)', 'quiz-master-next'); ?></h3>
		<p><?php _e('Enter in your text here to fill in the certificate for this quiz. Be sure to enter in the link variable into the templates on the Quiz Text tab so the user can access the certificate.', 'quiz-master-next'); ?></p>
		<p><?php _e('These fields cannot contain HTML.', 'quiz-master-next'); ?></p>
		<button id="save_certificate_button" class="button-primary" onclick="javascript: document.quiz_certificate_options_form.submit();"><?php _e('Save Certificate Options', 'quiz-master-next'); ?></button>
		<?php
			echo "<form action='' method='post' name='quiz_certificate_options_form'>";
			echo "<input type='hidden' name='save_certificate_options' value='confirmation' />";
			echo "<input type='hidden' name='certificate_quiz_id' value='".$quiz_id."' />";
		?>
		<table class="form-table">
			<tr valign="top">
				<td><label for="enableCertificates"><?php _e('Enable Certificates For This Quiz?', 'quiz-master-next'); ?></label></td>
				<td>
				    <input type="radio" id="radio30" name="enableCertificates" <?php if ($mlw_certificate_options[4] == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio30"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				    <input type="radio" id="radio31" name="enableCertificates" <?php if ($mlw_certificate_options[4] == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio31"><?php _e('No', 'quiz-master-next'); ?></label><br>
				</td>
			</tr>
			<tr>
				<td width="30%">
					<strong><?php _e('Certificate Title', 'quiz-master-next'); ?></strong>
				</td>
				<td><textarea cols="80" rows="15" id="certificate_title" name="certificate_title"><?php echo $mlw_certificate_options[0]; ?></textarea>
				</td>
			</tr>
			<tr>
				<td width="30%">
					<strong><?php _e('Message Displayed On Certificate', 'quiz-master-next'); ?></strong>
					<br />
					<p><?php _e('Allowed Variables:', 'quiz-master-next'); ?></p>
					<p style="margin: 2px 0">- %POINT_SCORE%</p>
					<p style="margin: 2px 0">- %AVERAGE_POINT%</p>
					<p style="margin: 2px 0">- %AMOUNT_CORRECT%</p>
					<p style="margin: 2px 0">- %TOTAL_QUESTIONS%</p>
					<p style="margin: 2px 0">- %CORRECT_SCORE%</p>
					<p style="margin: 2px 0">- %QUIZ_NAME%</p>
					<p style="margin: 2px 0">- %USER_NAME%</p>
					<p style="margin: 2px 0">- %USER_BUSINESS%</p>
					<p style="margin: 2px 0">- %USER_PHONE%</p>
					<p style="margin: 2px 0">- %USER_EMAIL%</p>
					<p style="margin: 2px 0">- %CURRENT_DATE%</p>
				</td>
				<td><label for="certificate_template">Allowed tags: &lt;b&gt; - bold, &lt;i&gt;-italics, &lt;u&gt;-underline, &lt;br&gt;-New Line or start a new line by pressing enter</label><textarea cols="80" rows="15" id="certificate_template" name="certificate_template"><?php echo $mlw_certificate_options[1]; ?></textarea>
				</td>
			</tr>
			<tr>
				<td width="30%">
					<strong><?php _e('URL To Logo (Must be JPG, JPEG, PNG or GIF)', 'quiz-master-next'); ?></strong>
				</td>
				<td><textarea cols="80" rows="15" id="certificate_logo" name="certificate_logo"><?php echo $mlw_certificate_options[2]; ?></textarea>
				</td>
			</tr>
			<tr>
				<td width="30%">
					<strong><?php _e('URL To Background Img (Must be JPG, JPEG, PNG or GIF)', 'quiz-master-next'); ?></strong>
				</td>
				<td><textarea cols="80" rows="15" id="certificate_background" name="certificate_background"><?php echo $mlw_certificate_options[3]; ?></textarea>
				</td>
			</tr>
		</table>
		<button id="save_certificate_button" class="button-primary" onclick="javascript: document.quiz_certificate_options_form.submit();"><?php _e('Save Certificate Options', 'quiz-master-next'); ?></button>
		</form>
	</div>
	<?php
}
?>
