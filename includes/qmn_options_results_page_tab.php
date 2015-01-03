<?php
function mlw_options_results_tab()
{
	echo "<li><a href=\"#tabs-6\">Results Page</a></li>";
}
function mlw_options_results_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Check to add new results page
	if (isset($_POST["mlw_add_landing_page"]) && $_POST["mlw_add_landing_page"] == "confirmation")
	{
		//Function variables
		$mlw_qmn_landing_id = intval($_POST["mlw_add_landing_quiz_id"]);
		$mlw_qmn_message_after = $wpdb->get_var( $wpdb->prepare( "SELECT message_after FROM ".$wpdb->prefix."mlw_quizzes WHERE quiz_id=%d", $mlw_qmn_landing_id ) );
		//Load message_after and check if it is array already. If not, turn it into one
		if (is_serialized($mlw_qmn_message_after) && is_array(@unserialize($mlw_qmn_message_after))) 
		{
			$mlw_qmn_landing_array = @unserialize($mlw_qmn_message_after);
			$mlw_new_landing_array = array(0, 100, 'Enter Your Text Here');
			array_unshift($mlw_qmn_landing_array , $mlw_new_landing_array);
			$mlw_qmn_landing_array = serialize($mlw_qmn_landing_array);
			
		}
		else
		{
			$mlw_qmn_landing_array = array(array(0, 0, $mlw_qmn_message_after));
			$mlw_new_landing_array = array(0, 100, 'Enter Your Text Here');
			array_unshift($mlw_qmn_landing_array , $mlw_new_landing_array);
			$mlw_qmn_landing_array = serialize($mlw_qmn_landing_array);
		}
		
		//Update message_after with new array then check to see if worked
		$mlw_new_landing_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET message_after=%s, last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_qmn_landing_array, $mlw_qmn_landing_id ) );
		if ($mlw_new_landing_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The results page has been added successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'New Landing Page Has Been Created For Quiz Number ".$mlw_qmn_landing_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0013.', 'error');
		}
	}
	
	//Check to save landing pages
	if (isset($_POST["mlw_save_landing_pages"]) && $_POST["mlw_save_landing_pages"] == "confirmation")
	{
		//Function Variables
		$mlw_qmn_landing_id = intval($_POST["mlw_landing_quiz_id"]);
		$mlw_qmn_landing_total = intval($_POST["mlw_landing_page_total"]);
		
		//Create new array
		$i = 1;
		$mlw_qmn_new_landing_array = array();
		while ($i <= $mlw_qmn_landing_total)
		{
			if ($_POST["message_after_".$i] != "Delete")
			{
				$mlw_qmn_landing_each = array(intval($_POST["message_after_begin_".$i]), intval($_POST["message_after_end_".$i]), htmlspecialchars(stripslashes($_POST["message_after_".$i]), ENT_QUOTES));
				$mlw_qmn_new_landing_array[] = $mlw_qmn_landing_each;
			}
			$i++;
		}
		$mlw_qmn_new_landing_array = serialize($mlw_qmn_new_landing_array);
		$mlw_new_landing_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET message_after='%s', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_qmn_new_landing_array, $mlw_qmn_landing_id ) );
		if ($mlw_new_landing_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The results page has been saved successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Landing Pages Have Been Saved For Quiz Number ".$mlw_qmn_landing_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0014.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	
	//Load Landing Pages
	if (is_serialized($mlw_quiz_options->message_after) && is_array(@unserialize($mlw_quiz_options->message_after))) 
	{
    		$mlw_message_after_array = @unserialize($mlw_quiz_options->message_after);
	}
	else
	{
		$mlw_message_after_array = array(array(0, 0, $mlw_quiz_options->message_after));
	}
	?>
	<div id="tabs-6" class="mlw_tab_content">
		<script>
			function delete_landing(id)
			{
				document.getElementById('message_after_'+id).value = "Delete";
				document.mlw_quiz_save_landing_form.submit();	
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
				
			</tr>
			<tr>
				<td><strong>%TIMER%</strong> - The amount of time user spent of quiz</td>
				<td><strong>%CERTIFICATE_LINK%</strong> - The link to the certificate after completing the quiz</td>
			</tr>
		</table>
		<button id="save_landing_button" class="button" onclick="javascript: document.mlw_quiz_save_landing_form.submit();">Save Results Pages</button>
		<button id="new_landing_button" class="button" onclick="javascript: document.mlw_quiz_add_landing_form.submit();">Add New Results Page</button>
		<form method="post" action="" name="mlw_quiz_save_landing_form" style=" display:inline!important;">
		<table class="widefat">
			<thead>
				<tr>
					<th>ID</th>
					<th>Score Greater Than Or Equal To</th>
					<th>Score Less Than Or Equal To</th>
					<th>Results Page Shown</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$mlw_each_count = 0;
				$alternate = "";
				foreach($mlw_message_after_array as $mlw_each)
				{
					if($alternate) $alternate = "";
					else $alternate = " class=\"alternate\"";
					$mlw_each_count += 1;
					if ($mlw_each[0] == 0 && $mlw_each[1] == 0)
					{
						echo "<tr{$alternate}>";
							echo "<td>";
								echo "Default";
							echo "</td>";
							echo "<td>";
								echo "<input type='hidden' id='message_after_begin_".$mlw_each_count."' name='message_after_begin_".$mlw_each_count."' value='0'/>-";
							echo "</td>";
							echo "<td>";
								echo "<input type='hidden' id='message_after_end_".$mlw_each_count."' name='message_after_end_".$mlw_each_count."' value='0'/>-";
							echo "</td>";
							echo "<td>";
								echo "<textarea cols='80' rows='15' id='message_after_".$mlw_each_count."' name='message_after_".$mlw_each_count."'>".$mlw_each[2]."</textarea>";
							echo "</td>";
						echo "</tr>";
						break;
					}
					else
					{
						echo "<tr{$alternate}>";
							echo "<td>";
								echo $mlw_each_count."<div><span style='color:green;font-size:12px;'><a onclick=\"\$j('#trying_delete_".$mlw_each_count."').show();\">Delete</a></span></div><div style=\"display: none;\" id='trying_delete_".$mlw_each_count."'>Are you sure?<br /><a onclick=\"delete_landing(".$mlw_each_count.")\">Yes</a>|<a onclick=\"\$j('#trying_delete_".$mlw_each_count."').hide();\">No</a></div>";
							echo "</td>";
							echo "<td>";
								echo "<input type='text' id='message_after_begin_".$mlw_each_count."' name='message_after_begin_".$mlw_each_count."' title='What score must the user score better than to see this page' value='".$mlw_each[0]."'/>";
							echo "</td>";
							echo "<td>";
								echo "<input type='text' id='message_after_end_".$mlw_each_count."' name='message_after_end_".$mlw_each_count."' title='What score must the user score worse than to see this page' value='".$mlw_each[1]."' />";
							echo "</td>";
							echo "<td>";
								echo "<textarea cols='80' rows='15' id='message_after_".$mlw_each_count."' title='What text will the user see when reaching this page' name='message_after_".$mlw_each_count."'>".$mlw_each[2]."</textarea>";
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
					<th>Results Page Shown</th>
				</tr>
			</tfoot>
		</table>
		<input type='hidden' name='mlw_save_landing_pages' value='confirmation' />
		<input type='hidden' name='mlw_landing_quiz_id' value='<?php echo $quiz_id; ?>' />
		<input type='hidden' name='mlw_landing_page_total' value='<?php echo $mlw_each_count; ?>' />
		<button id="save_landing_button" class="button" onclick="javascript: document.mlw_quiz_save_landing_form.submit();">Save Results Pages</button>
		</form>
		<form method="post" action="" name="mlw_quiz_add_landing_form" style=" display:inline!important;">
			<input type='hidden' name='mlw_add_landing_page' value='confirmation' />
			<input type='hidden' name='mlw_add_landing_quiz_id' value='<?php echo $quiz_id; ?>' />
			<button id="new_landing_button" class="button" onclick="javascript: document.mlw_quiz_add_landing_form.submit();">Add New Results Page</button>
		</form>
	</div>
	<?php
}
add_action('mlw_qmn_options_tab', 'mlw_options_results_tab');
add_action('mlw_qmn_options_tab_content', 'mlw_options_results_tab_content');
?>
