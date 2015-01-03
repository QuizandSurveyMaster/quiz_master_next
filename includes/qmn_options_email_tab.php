<?php
function qmn_settings_email_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs("Emails", 'mlw_options_emails_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_email_tab');

function mlw_options_emails_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Check to add new user email template
	if (isset($_POST["mlw_add_email_page"]) && $_POST["mlw_add_email_page"] == "confirmation")
	{
		//Function variables
		$mlw_qmn_add_email_id = intval($_POST["mlw_add_email_quiz_id"]);
		$mlw_qmn_user_email = $wpdb->get_var( $wpdb->prepare( "SELECT user_email_template FROM ".$wpdb->prefix."mlw_quizzes WHERE quiz_id=%d", $mlw_qmn_add_email_id ) );
	
		//Load user email and check if it is array already. If not, turn it into one
		if (is_serialized($mlw_qmn_user_email) && is_array(@unserialize($mlw_qmn_user_email))) 
		{
			$mlw_qmn_email_array = @unserialize($mlw_qmn_user_email);
			$mlw_new_landing_array = array(0, 100, 'Enter Your Text Here', 'Quiz Results For %QUIZ_NAME%');
			array_unshift($mlw_qmn_email_array , $mlw_new_landing_array);
			$mlw_qmn_email_array = serialize($mlw_qmn_email_array);
			
		}
		else
		{
			$mlw_qmn_email_array = array(array(0, 0, $mlw_qmn_user_email, 'Quiz Results For %QUIZ_NAME%'));
			$mlw_new_landing_array = array(0, 100, 'Enter Your Text Here', 'Quiz Results For %QUIZ_NAME%');
			array_unshift($mlw_qmn_email_array , $mlw_new_landing_array);
			$mlw_qmn_email_array = serialize($mlw_qmn_email_array);
		}
		//Update email template with new array then check to see if worked
		$mlw_new_email_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET user_email_template='%s', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_qmn_email_array, $mlw_qmn_add_email_id ) );
		if ($mlw_new_email_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The email has been added successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'New User Email Has Been Created For Quiz Number ".$mlw_qmn_add_email_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0016.', 'error');
		}
	}
	
	//Check to add new admin email template
	if (isset($_POST["mlw_add_admin_email_page"]) && $_POST["mlw_add_admin_email_page"] == "confirmation")
	{
		//Function variables
		$mlw_qmn_add_email_id = intval($_POST["mlw_add_admin_email_quiz_id"]);
		$mlw_qmn_admin_email = $wpdb->get_var( $wpdb->prepare( "SELECT admin_email_template FROM ".$wpdb->prefix."mlw_quizzes WHERE quiz_id=%d", $mlw_qmn_add_email_id ) );
	
		//Load user email and check if it is array already. If not, turn it into one
		if (is_serialized($mlw_qmn_admin_email) && is_array(@unserialize($mlw_qmn_admin_email))) 
		{
			$mlw_qmn_email_array = @unserialize($mlw_qmn_admin_email);
			$mlw_new_landing_array = array(
				"begin_score" => 0,
				"end_score" => 100,
				"message" => 'Enter Your Text Here',
				"subject" => 'Quiz Results For %QUIZ_NAME%'
			);
			array_unshift($mlw_qmn_email_array , $mlw_new_landing_array);
			$mlw_qmn_email_array = serialize($mlw_qmn_email_array);
			
		}
		else
		{
			$mlw_qmn_email_array = array(array(
				"begin_score" => 0,
				"end_score" => 0,
				"message" => $mlw_qmn_admin_email,
				"subject" => 'Quiz Results For %QUIZ_NAME%'
			));
			$mlw_new_landing_array = array(
				"begin_score" => 0,
				"end_score" => 100,
				"message" => 'Enter Your Text Here',
				"subject" => 'Quiz Results For %QUIZ_NAME%'
			);
			array_unshift($mlw_qmn_email_array , $mlw_new_landing_array);
			$mlw_qmn_email_array = serialize($mlw_qmn_email_array);
		}
		//Update email template with new array then check to see if worked
		$mlw_new_email_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET admin_email_template='%s', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_qmn_email_array, $mlw_qmn_add_email_id ) );
		if ($mlw_new_email_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The email has been added successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'New Admin Email Has Been Created For Quiz Number ".$mlw_qmn_add_email_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0016.', 'error');
		}
	}
	
	//Check to save email templates
	if (isset($_POST["mlw_save_email_template"]) && $_POST["mlw_save_email_template"] == "confirmation")
	{
		//Function Variables
		$mlw_qmn_email_id = intval($_POST["mlw_email_quiz_id"]);
		$mlw_qmn_email_template_total = intval($_POST["mlw_email_template_total"]);
		$mlw_qmn_email_admin_total = intval($_POST["mlw_email_admin_total"]);
		$mlw_send_user_email = $_POST["sendUserEmail"];
		$mlw_send_admin_email = $_POST["sendAdminEmail"];
		$mlw_admin_email = $_POST["adminEmail"];
		
		//Create new array
		$i = 1;
		$mlw_qmn_new_email_array = array();
		while ($i <= $mlw_qmn_email_template_total)
		{
			if ($_POST["user_email_".$i] != "Delete")
			{
				$mlw_qmn_email_each = array(intval($_POST["user_email_begin_".$i]), intval($_POST["user_email_end_".$i]), htmlspecialchars(stripslashes($_POST["user_email_".$i]), ENT_QUOTES), htmlspecialchars(stripslashes($_POST["user_email_subject_".$i]), ENT_QUOTES));
				$mlw_qmn_new_email_array[] = $mlw_qmn_email_each;
			}
			$i++;
		}
		
		//Create new array
		$i = 1;
		$mlw_qmn_new_admin_array = array();
		while ($i <= $mlw_qmn_email_admin_total)
		{
			if ($_POST["admin_email_".$i] != "Delete")
			{
				$mlw_qmn_email_each = array(
					"begin_score" => intval($_POST["admin_email_begin_".$i]), 
					"end_score" => intval($_POST["admin_email_end_".$i]), 
					"message" => htmlspecialchars(stripslashes($_POST["admin_email_".$i]), ENT_QUOTES), 
					"subject" => htmlspecialchars(stripslashes($_POST["admin_email_subject_".$i]), ENT_QUOTES)
				);
				$mlw_qmn_new_admin_array[] = $mlw_qmn_email_each;
			}
			$i++;
		}
		$mlw_qmn_new_email_array = serialize($mlw_qmn_new_email_array);
		$mlw_qmn_new_admin_array = serialize($mlw_qmn_new_admin_array);
		$mlw_new_email_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET send_user_email='%s', send_admin_email='%s', admin_email='%s', user_email_template='%s', admin_email_template='%s', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_send_user_email, $mlw_send_admin_email, $mlw_admin_email, $mlw_qmn_new_email_array, $mlw_qmn_new_admin_array, $mlw_qmn_email_id ) );
		if ($mlw_new_email_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The email has been updated successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Email Templates Have Been Saved For Quiz Number ".$mlw_qmn_email_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0017.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	
	//Load User Email Templates
	if (is_serialized($mlw_quiz_options->user_email_template) && is_array(@unserialize($mlw_quiz_options->user_email_template))) 
	{
		$mlw_qmn_user_email_array = @unserialize($mlw_quiz_options->user_email_template);
	}
	else
	{
		 $mlw_qmn_user_email_array = array(array(0, 0, $mlw_quiz_options->user_email_template, 'Quiz Results For %QUIZ_NAME%'));
	}
	
	//Load Admin Email Templates
	if (is_serialized($mlw_quiz_options->admin_email_template) && is_array(@unserialize($mlw_quiz_options->admin_email_template))) 
	{
		$mlw_qmn_admin_email_array = @unserialize($mlw_quiz_options->admin_email_template);
	}
	else
	{
		 $mlw_qmn_admin_email_array = array(array(
			"begin_score" => 0, 
			"end_score" => 0, 
			"message" => $mlw_quiz_options->admin_email_template, 
			"subject" => 'Quiz Results For %QUIZ_NAME%'
		 ));
	}
	?>
	
	<div id="tabs-9" class="mlw_tab_content">
	<script>
		function delete_email(id)
		{
			document.getElementById('user_email_'+id).value = "Delete";
			document.mlw_quiz_save_email_form.submit();	
		}
		function delete_admin_email(id)
		{
			document.getElementById('admin_email_'+id).value = "Delete";
			document.mlw_quiz_save_email_form.submit();	
		}
	</script>
		<h3>Template Variables</h3>
		<table class="form-table">
			<tr>
				<td><strong>%POINT_SCORE%</strong> - Score for the quiz when using points</td>
				<td><strong>%AVERAGE_POINT%</strong> - The average amount of points user had per question</td>
			</tr>
	
			<tr>
				<td><strong>%AMOUNT_CORRECT%</strong> - The number of correct answers the user had</td>
				<td><strong>%TOTAL_QUESTIONS%</strong> - The total number of questions in the quiz</td>
			</tr>
			
			<tr>
				<td><strong>%CORRECT_SCORE%</strong> - Score for the quiz when using correct answers</td>
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
				<td><strong>%TIMER%</strong> - The amount of time user spent of quiz</td>
			</tr>
		</table>
		<br />
		<br />
		<form method="post" action="" name="mlw_quiz_add_email_form">
			<input type='hidden' name='mlw_add_email_page' value='confirmation' />
			<input type='hidden' name='mlw_add_email_quiz_id' value='<?php echo $quiz_id; ?>' />
		</form>
		<form method="post" action="" name="mlw_quiz_add_admin_email_form">
			<input type='hidden' name='mlw_add_admin_email_page' value='confirmation' />
			<input type='hidden' name='mlw_add_admin_email_quiz_id' value='<?php echo $quiz_id; ?>' />
		</form>
		<button id="save_email_button" class="button" onclick="javascript: document.mlw_quiz_save_email_form.submit();">Save Email Templates And Settings</button>
		<form method="post" action="" name="mlw_quiz_save_email_form">
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="sendUserEmail">Send user email upon completion?</label></th>
				<td><div id="sendUserEmail">
				    <input type="radio" id="radio5" name="sendUserEmail" <?php if ($mlw_quiz_options->send_user_email == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio5">Yes</label>
				    <input type="radio" id="radio6" name="sendUserEmail" <?php if ($mlw_quiz_options->send_user_email == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio6">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="sendAdminEmail">Send admin email upon completion?</label></th>
				<td><div id="sendAdminEmail">
				    <input type="radio" id="radio19" name="sendAdminEmail" <?php if ($mlw_quiz_options->send_admin_email == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio19">Yes</label>
				    <input type="radio" id="radio20" name="sendAdminEmail" <?php if ($mlw_quiz_options->send_admin_email == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio20">No</label>
				</div></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="adminEmail">What emails should we send the admin email to? Separate emails with a comma.</label></th>
				<td><input name="adminEmail" type="text" id="adminEmail" value="<?php echo $mlw_quiz_options->admin_email; ?>" class="regular-text" /></td>
			</tr>
			</table>
			<br />
			<br />
			<h3>Email Sent To User</h3>		
			<a id="new_email_button_top" class="button" href="#" onclick="javascript: document.mlw_quiz_add_email_form.submit();">Add New User Email</a>
			<table class="widefat">
				<thead>
					<tr>
						<th>ID</th>
						<th>Score Greater Than Or Equal To</th>
						<th>Score Less Than Or Equal To</th>
						<th>Subject</th>
						<th>Email To Send</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$mlw_each_count = 0;
					$alternate = "";
					foreach($mlw_qmn_user_email_array as $mlw_each)
					{
						if($alternate) $alternate = "";
						else $alternate = " class=\"alternate\"";
						$mlw_each_count += 1;
						if (!isset($mlw_each[3]))
						{
							$mlw_each[3] = "Quiz Results For %QUIZ_NAME%";
						}
						if ($mlw_each[0] == 0 && $mlw_each[1] == 0)
						{
							echo "<tr{$alternate}>";
								echo "<td>";
									echo "Default";
								echo "</td>";
								echo "<td>";
									echo "<input type='hidden' id='user_email_begin_".$mlw_each_count."' name='user_email_begin_".$mlw_each_count."' value='0'/>-";
								echo "</td>";
								echo "<td>";
									echo "<input type='hidden' id='user_email_end_".$mlw_each_count."' name='user_email_end_".$mlw_each_count."' value='0'/>-";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='user_email_subject_".$mlw_each_count."' name='user_email_subject_".$mlw_each_count."' value='".$mlw_each[3]."' />";
								echo "</td>";
								echo "<td>";
									echo "<textarea cols='80' rows='15' id='user_email_".$mlw_each_count."' name='user_email_".$mlw_each_count."'>".$mlw_each[2]."</textarea>";
								echo "</td>";
							echo "</tr>";
							break;
						}
						else
						{
							echo "<tr{$alternate}>";
								echo "<td>";
									echo $mlw_each_count."<div><span style='color:green;font-size:12px;'><a onclick=\"\$j('#trying_delete_email_".$mlw_each_count."').show();\">Delete</a></span></div><div style=\"display: none;\" id='trying_delete_email_".$mlw_each_count."'>Are you sure?<br /><a onclick=\"delete_email(".$mlw_each_count.")\">Yes</a>|<a onclick=\"\$j('#trying_delete_email_".$mlw_each_count."').hide();\">No</a></div>";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='user_email_begin_".$mlw_each_count."' name='user_email_begin_".$mlw_each_count."' title='What score must the user score better than to see this page' value='".$mlw_each[0]."'/>";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='user_email_end_".$mlw_each_count."' name='user_email_end_".$mlw_each_count."' title='What score must the user score worse than to see this page' value='".$mlw_each[1]."' />";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='user_email_subject_".$mlw_each_count."' name='user_email_subject_".$mlw_each_count."' value='".$mlw_each[3]."' />";
								echo "</td>";
								echo "<td>";
									echo "<textarea cols='80' rows='15' id='user_email_".$mlw_each_count."' title='What email will the user be sent' name='user_email_".$mlw_each_count."'>".$mlw_each[2]."</textarea>";
								echo "</td>";
							echo "</tr>";
						}
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th>ID</th>
						<th>Score Greater Than Or Equal To</th>
						<th>Score Less Than Or Equal To</th>
						<th>Subject</th>
						<th>Email To Send</th>
					</tr>
				</tfoot>
			</table>
			<a id="new_email_button_bottom" class="button" href="#" onclick="javascript: document.mlw_quiz_add_email_form.submit();">Add New User Email</a>
			<input type='hidden' name='mlw_save_email_template' value='confirmation' />
			<input type='hidden' name='mlw_email_quiz_id' value='<?php echo $quiz_id; ?>' />
			<input type='hidden' name='mlw_email_template_total' value='<?php echo $mlw_each_count; ?>' />
			<br />
			<br />
			<br />
			<br />
			<h3>Email Sent To Admin</h3>
			<a id="new_admin_email_button_top" class="button" href="#" onclick="javascript: document.mlw_quiz_add_admin_email_form.submit();">Add New Admin Email</a>
			<table class="widefat">
				<thead>
					<tr>
						<th>ID</th>
						<th>Score Greater Than Or Equal To</th>
						<th>Score Less Than Or Equal To</th>
						<th>Subject</th>
						<th>Email To Send</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$mlw_admin_count = 0;
					$alternate = "";
					foreach($mlw_qmn_admin_email_array as $mlw_each)
					{
						if($alternate) $alternate = "";
						else $alternate = " class=\"alternate\"";
						$mlw_admin_count += 1;
						if (!isset($mlw_each["subject"]))
						{
							$mlw_each[3] = "Quiz Results For %QUIZ_NAME%";
						}
						if ($mlw_each["begin_score"] == 0 && $mlw_each["end_score"] == 0)
						{
							echo "<tr{$alternate}>";
								echo "<td>";
									echo "Default";
								echo "</td>";
								echo "<td>";
									echo "<input type='hidden' id='admin_email_begin_".$mlw_admin_count."' name='admin_email_begin_".$mlw_admin_count."' value='0'/>-";
								echo "</td>";
								echo "<td>";
									echo "<input type='hidden' id='admin_email_end_".$mlw_admin_count."' name='admin_email_end_".$mlw_admin_count."' value='0'/>-";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='admin_email_subject_".$mlw_admin_count."' name='admin_email_subject_".$mlw_admin_count."' value='".$mlw_each["subject"]."' />";
								echo "</td>";
								echo "<td>";
									echo "<textarea cols='80' rows='15' id='admin_email_".$mlw_admin_count."' name='admin_email_".$mlw_admin_count."'>".$mlw_each["message"]."</textarea>";
								echo "</td>";
							echo "</tr>";
							break;
						}
						else
						{
							echo "<tr{$alternate}>";
								echo "<td>";
									echo $mlw_admin_count."<div><span style='color:green;font-size:12px;'><a onclick=\"\$j('#trying_delete_admin_email_".$mlw_admin_count."').show();\">Delete</a></span></div><div style=\"display: none;\" id='trying_delete_admin_email_".$mlw_admin_count."'>Are you sure?<br /><a onclick=\"delete_admin_email(".$mlw_admin_count.")\">Yes</a>|<a onclick=\"\$j('#trying_delete_admin_email_".$mlw_admin_count."').hide();\">No</a></div>";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='admin_email_begin_".$mlw_admin_count."' name='admin_email_begin_".$mlw_admin_count."' title='What score must the user score better than to see this page' value='".$mlw_each["begin_score"]."'/>";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='admin_email_end_".$mlw_admin_count."' name='admin_email_end_".$mlw_admin_count."' title='What score must the user score worse than to see this page' value='".$mlw_each["end_score"]."' />";
								echo "</td>";
								echo "<td>";
									echo "<input type='text' id='admin_email_subject_".$mlw_admin_count."' name='admin_email_subject_".$mlw_admin_count."' value='".$mlw_each["subject"]."' />";
								echo "</td>";
								echo "<td>";
									echo "<textarea cols='80' rows='15' id='admin_email_".$mlw_admin_count."' title='What email will the user be sent' name='admin_email_".$mlw_admin_count."'>".$mlw_each["message"]."</textarea>";
								echo "</td>";
							echo "</tr>";
						}
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th>ID</th>
						<th>Score Greater Than Or Equal To</th>
						<th>Score Less Than Or Equal To</th>
						<th>Subject</th>
						<th>Email To Send</th>
					</tr>
				</tfoot>
			</table>
			<a id="new_admin_email_button_bottom" class="button" href="#" onclick="javascript: document.mlw_quiz_add_admin_email_form.submit();">Add New Admin Email</a>
			<input type='hidden' name='mlw_email_admin_total' value='<?php echo $mlw_admin_count; ?>' />
		</form>
		<br />
		<br />
		<button id="save_email_button" class="button" onclick="javascript: document.mlw_quiz_save_email_form.submit();">Save Email Templates And Settings</button>
	</div>
	<?php
}
?>
