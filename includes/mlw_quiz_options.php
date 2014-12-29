<?php
/*
This page allows for the editing of quizzes selected from the quiz admin page.
*/
/* 
Copyright 2014, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_quiz_options()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}	
	?>
	
	<script type="text/javascript"
	  src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
	</script>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
	<!-- jquery scripts -->
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	?>
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;
		$j(function() {
			$j( "#tabs" ).tabs();
		});
	</script>
	<style>
		.mlw_tab_content
		{
			padding: 20px 20px 20px 20px;
			margin: 20px 20px 20px 20px;
		}
	</style>
	<div class="wrap">
	<div class='mlw_quiz_options'>
	<h2>Quiz Settings For <?php echo $mlw_quiz_options->quiz_name; ?></h2>
	<?php
	ob_start();
	if ($quiz_id != "")
	{
	?>
	<div id="tabs">
		<ul>
			<?php do_action('mlw_qmn_options_tab'); ?>
		</ul>
		<?php do_action('mlw_qmn_options_tab_content'); ?>
  		
	</div>
	<?php
	}
	else
	{
		?>
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> Please go to the quizzes page and click on the Edit link from the quiz you wish to edit.</p
		</div>
		<?php
	}
	$mlw_output = ob_get_contents();
	ob_end_clean();
	$mlwQuizMasterNext->alertManager->showAlerts();
	echo mlw_qmn_show_adverts();
	echo $mlw_output;
	?>
	</div>
	</div>
<?php
}

add_action('mlw_qmn_options_tab', 'mlw_options_questions_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_text_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_option_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_leaderboard_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_certificate_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_emails_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_results_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_styling_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_tools_tab');
add_action('mlw_qmn_options_tab', 'mlw_options_preview_tab');

function mlw_options_questions_tab()
{
	echo "<li><a href=\"#tabs-1\">Questions</a></li>";
}
function mlw_options_text_tab()
{
	echo "<li><a href=\"#tabs-2\">Text</a></li>";
}
function mlw_options_option_tab()
{
	echo "<li><a href=\"#tabs-3\">Options</a></li>";
}
function mlw_options_leaderboard_tab()
{
	echo "<li><a href=\"#tabs-4\">Leaderboard</a></li>";
}
function mlw_options_certificate_tab()
{
	echo "<li><a href=\"#tabs-5\">Certificate (Beta)</a></li>";
}
function mlw_options_emails_tab()
{
	echo "<li><a href=\"#tabs-9\">Emails</a></li>";
}
function mlw_options_results_tab()
{
	echo "<li><a href=\"#tabs-6\">Results Page</a></li>";
}
function mlw_options_styling_tab()
{
	echo "<li><a href=\"#tabs-7\">Styling</a></li>";
}
function mlw_options_tools_tab()
{
	echo "<li><a href=\"#tabs-8\">Tools/Add-Ons</a></li>";
}
function mlw_options_preview_tab()
{
	echo "<li><a href=\"#tabs-preview\">Preview (Beta)</a></li>";
}

add_action('mlw_qmn_options_tab_content', 'mlw_options_questions_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_text_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_option_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_leaderboard_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_certificate_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_emails_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_results_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_styling_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_tools_tab_content');
add_action('mlw_qmn_options_tab_content', 'mlw_options_preview_tab_content');

function mlw_options_questions_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	
	//Edit question
	if ( isset($_POST["edit_question"]) && $_POST["edit_question"] == "confirmation")
	{
		//Variables from edit question form
		$edit_question_name = trim(preg_replace('/\s+/',' ', nl2br(htmlspecialchars($_POST["edit_question_name"], ENT_QUOTES))));
		$edit_question_answer_info = $_POST["edit_correct_answer_info"];
		$mlw_edit_question_id = intval($_POST["edit_question_id"]);
		$mlw_edit_question_type = $_POST["edit_question_type"];
		$edit_comments = htmlspecialchars($_POST["edit_comments"], ENT_QUOTES);
		$edit_hint = htmlspecialchars($_POST["edit_hint"], ENT_QUOTES);
		$edit_question_order = intval($_POST["edit_question_order"]);
		$mlw_edit_answer_total = intval($_POST["question_".$mlw_edit_question_id."_answer_total"]);
		$mlw_row_settings = $wpdb->get_row( $wpdb->prepare( "SELECT question_settings FROM " . $wpdb->prefix . "mlw_questions" . " WHERE question_id=%d", $mlw_edit_question_id ) );
		if (is_serialized($mlw_row_settings->question_settings) && is_array(@unserialize($mlw_row_settings->question_settings))) 
		{
			$mlw_settings = @unserialize($mlw_row_settings->question_settings);
		}
		else
		{
			$mlw_settings = array();
			$mlw_settings['required'] = intval($_POST["edit_required"]);
		}
		if ( !isset($mlw_settings['required']))
		{
			$mlw_settings['required'] = intval($_POST["edit_required"]);	
		}
		$mlw_settings['required'] = intval($_POST["edit_required"]);		
		$mlw_settings = serialize($mlw_settings);
		$i = 1;
		$mlw_qmn_new_answer_array = array();
		while ($i <= $mlw_edit_answer_total)
		{
			if ($_POST["edit_answer_".$i] != "")
			{
				$mlw_qmn_correct = 0;
				if (isset($_POST["edit_answer_".$i."_correct"]) && $_POST["edit_answer_".$i."_correct"] == 1)
				{
					$mlw_qmn_correct = 1;
				}
				$mlw_qmn_answer_each = array(htmlspecialchars(stripslashes($_POST["edit_answer_".$i]), ENT_QUOTES), floatval($_POST["edit_answer_".$i."_points"]), $mlw_qmn_correct);
				$mlw_qmn_new_answer_array[] = $mlw_qmn_answer_each;
			}
			$i++;
		}
		$mlw_qmn_new_answer_array = serialize($mlw_qmn_new_answer_array);
		$quiz_id = $_POST["quiz_id"];
		
		$update = "UPDATE " . $wpdb->prefix . "mlw_questions" . " SET question_name='".$edit_question_name."',answer_array='".$mlw_qmn_new_answer_array."', question_answer_info='".$edit_question_answer_info."', comments='".$edit_comments."', hints='".$edit_hint."', question_order='".$edit_question_order."', question_type='".$mlw_edit_question_type."', question_settings='".$mlw_settings."' WHERE question_id=".$mlw_edit_question_id;
		$results = $wpdb->query( $update );
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The question has been updated successfully.', 'success');
		
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Question Has Been Edited: ".$edit_question_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0004.', 'error');
		}
	}

	//Delete question from quiz
	if ( isset($_POST["delete_question"]) && $_POST["delete_question"] == "confirmation")
	{
		//Variables from delete question form
		$mlw_question_id = intval($_POST["question_id"]);
		$quiz_id = $_POST["quiz_id"];
		
		$update = "UPDATE " . $wpdb->prefix . "mlw_questions" . " SET deleted=1 WHERE question_id=".$mlw_question_id;
		$results = $wpdb->query( $update );
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The question has been deleted successfully.', 'success');
		
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Question Has Been Deleted: ".$mlw_question_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0005.', 'error');
		}
	}
	
	//Duplicate Questions
	if ( isset($_POST["duplicate_question"]) && $_POST["duplicate_question"] == "confirmation")
	{
		//Variables from delete question form
		$mlw_question_id = intval($_POST["duplicate_question_id"]);
		$quiz_id = $_POST["quiz_id"];
		
		$mlw_original = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."mlw_questions WHERE question_id=%d", $mlw_question_id ), ARRAY_A );
		
		$results = $wpdb->insert( 
						$wpdb->prefix."mlw_questions", 
						array( 
							'quiz_id' => $mlw_original['quiz_id'], 
							'question_name' => $mlw_original['question_name'],
							'answer_array' => $mlw_original['answer_array'], 
							'answer_one' => $mlw_original['answer_one'],
							'answer_one_points' => $mlw_original['answer_one_points'], 
							'answer_two' => $mlw_original['answer_two'],
							'answer_two_points' => $mlw_original['answer_two_points'], 
							'answer_three' => $mlw_original['answer_three'],
							'answer_three_points' => $mlw_original['answer_three_points'], 
							'answer_four' => $mlw_original['answer_four'],
							'answer_four_points' => $mlw_original['answer_four_points'], 
							'answer_five' => $mlw_original['answer_five'],
							'answer_five_points' => $mlw_original['answer_five_points'], 
							'answer_six' => $mlw_original['answer_six'],
							'answer_six_points' => $mlw_original['answer_six_points'], 
							'correct_answer' => $mlw_original['correct_answer'],
							'question_answer_info' => $mlw_original['question_answer_info'], 
							'comments' => $mlw_original['comments'],
							'hints' => $mlw_original['hints'], 
							'question_order' => $mlw_original['question_order'],
							'question_type' => $mlw_original['question_type'], 
							'question_settings' => $mlw_original['question_settings'], 
							'deleted' => $mlw_original['deleted']
						), 
						array( 
							'%d', 
							'%s',
							'%s', 
							'%s',
							'%d', 
							'%s',
							'%d', 
							'%s',
							'%d', 
							'%s',
							'%d', 
							'%s',
							'%d', 
							'%s',
							'%d', 
							'%d',
							'%s',
							'%d',
							'%s',
							'%d',
							'%d',
							'%s',
							'%d'
						) 
					);
		
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The question has been duplicated successfully.', 'success');
		
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Question Has Been Duplicated: ".$mlw_question_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0019.', 'error');
		}
	}
	

	//Submit new question into database
	if ( isset($_POST["create_question"]) && $_POST["create_question"] == "confirmation")
	{
		//Variables from new question form
		$question_name = trim(preg_replace('/\s+/',' ', nl2br(htmlspecialchars($_POST["question_name"], ENT_QUOTES))));
		$question_answer_info = $_POST["correct_answer_info"];
		$question_type = $_POST["question_type"];
		$comments = htmlspecialchars($_POST["comments"], ENT_QUOTES);
		$hint = htmlspecialchars($_POST["hint"], ENT_QUOTES);
		$new_question_order = intval($_POST["new_question_order"]);
		$mlw_answer_total = intval($_POST["new_question_answer_total"]);
		$mlw_settings = array();
		$mlw_settings['required'] = intval($_POST["required"]);
		$mlw_settings = serialize($mlw_settings);
		$i = 1;
		$mlw_qmn_new_answer_array = array();
		while ($i <= $mlw_answer_total)
		{
			if ($_POST["answer_".$i] != "")
			{
				$mlw_qmn_correct = 0;
				if (isset($_POST["answer_".$i."_correct"]) && $_POST["answer_".$i."_correct"] == 1)
				{
					$mlw_qmn_correct = 1;
				}
				$mlw_qmn_answer_each = array(htmlspecialchars(stripslashes($_POST["answer_".$i]), ENT_QUOTES), floatval($_POST["answer_".$i."_points"]), $mlw_qmn_correct);
				$mlw_qmn_new_answer_array[] = $mlw_qmn_answer_each;
			}
			$i++;
		}
		$mlw_qmn_new_answer_array = serialize($mlw_qmn_new_answer_array);
		$quiz_id = $_POST["quiz_id"];
		$table_name = $wpdb->prefix . "mlw_questions";
		$insert = "INSERT INTO " . $table_name .
			" (question_id, quiz_id, question_name, answer_array, question_answer_info, comments, hints, question_order, question_type, question_settings, deleted) VALUES (NULL , ".$quiz_id.", '" . $question_name . "' , '".$mlw_qmn_new_answer_array."', '".$question_answer_info."', '".$comments."', '".$hint."', ".$new_question_order.", '".$question_type."', '".$mlw_settings."',  0)";
		$results = $wpdb->query( $insert );
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The question has been created successfully.', 'success');
		
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Question Has Been Added: ".$question_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0006.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}

	$mlw_qmn_table_limit = 10;
	$mlw_qmn_question_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(question_id) FROM " . $wpdb->prefix . "mlw_questions WHERE quiz_id=%d AND deleted='0'", $quiz_id ) );
	
	if( isset($_GET{'mlw_question_page'} ) )
	{
	   $mlw_qmn_question_page = $_GET{'mlw_question_page'} + 1;
	   $mlw_qmn_question_begin = $mlw_qmn_table_limit * $mlw_qmn_question_page ;
	}
	else
	{
	   $mlw_qmn_question_page = 0;
	   $mlw_qmn_question_begin = 0;
	}
	$mlw_qmn_question_left = $mlw_qmn_question_count - ($mlw_qmn_question_page * $mlw_qmn_table_limit);
	
	$mlw_question_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_questions WHERE quiz_id=%d AND deleted='0' 
		ORDER BY question_order ASC LIMIT %d, %d", $quiz_id, $mlw_qmn_question_begin, $mlw_qmn_table_limit ) );
		
	//Load and prepare answer arrays
	$mlw_qmn_answer_arrays = array();
	foreach($mlw_question_data as $mlw_question_info) {
		if (is_serialized($mlw_question_info->answer_array) && is_array(@unserialize($mlw_question_info->answer_array))) 
		{
			$mlw_qmn_answer_array_each = @unserialize($mlw_question_info->answer_array);
			$mlw_qmn_answer_arrays[$mlw_question_info->question_id] = $mlw_qmn_answer_array_each;
		}
		else
		{
			$mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
			$mlw_answer_array_correct[$mlw_question_info->correct_answer-1] = 1;
			$mlw_qmn_answer_arrays[$mlw_question_info->question_id] = array(
				array($mlw_question_info->answer_one, $mlw_question_info->answer_one_points, $mlw_answer_array_correct[0]),
				array($mlw_question_info->answer_two, $mlw_question_info->answer_two_points, $mlw_answer_array_correct[1]),
				array($mlw_question_info->answer_three, $mlw_question_info->answer_three_points, $mlw_answer_array_correct[2]),
				array($mlw_question_info->answer_four, $mlw_question_info->answer_four_points, $mlw_answer_array_correct[3]),
				array($mlw_question_info->answer_five, $mlw_question_info->answer_five_points, $mlw_answer_array_correct[4]),
				array($mlw_question_info->answer_six, $mlw_question_info->answer_six_points, $mlw_answer_array_correct[5]));
		}
	}
	$is_new_quiz = $wpdb->num_rows;
	?>
	<div id="tabs-1" class="mlw_tab_content">
		<script>
			jQuery(function() {
				jQuery('#new_question_dialog').dialog({
					autoOpen: false,
					show: 'blind',
					width:800,
					hide: 'explode',
					buttons: {
					Cancel: function() {
						jQuery(this).dialog('close');
						}
					}
				});
			
				jQuery('#new_question_button').click(function() {
					jQuery('#new_question_dialog').dialog('open');
					document.getElementById("question_name").focus();
					return false;
			}	);
				jQuery('#new_question_button_two').click(function() {
					jQuery('#new_question_dialog').dialog('open');
					document.getElementById("question_name").focus();
					return false;
			}	);
			});
			function deleteQuestion(id){
				jQuery("#delete_dialog").dialog({
					autoOpen: false,
					show: 'blind',
					hide: 'explode',
					buttons: {
					Cancel: function() {
						jQuery(this).dialog('close');
						}
					}
				});
				jQuery("#delete_dialog").dialog('open');
				var idText = document.getElementById("delete_question_id");
				var idHidden = document.getElementById("question_id");
				idText.innerHTML = id;
				idHidden.value = id;		
			};
			function duplicateQuestion(id){
				jQuery("#duplicate_dialog").dialog({
					autoOpen: false,
					show: 'blind',
					hide: 'explode',
					buttons: {
					Cancel: function() {
						jQuery(this).dialog('close');
						}
					}
				});
				jQuery("#duplicate_dialog").dialog('open');
				var idHidden = document.getElementById("duplicate_question_id");
				idHidden.value = id;		
			};
			function editQuestion(id){
				jQuery("#edit_question_dialog_"+id).dialog({
					autoOpen: false,
					show: 'blind',
					width:800,
					hide: 'explode',
					buttons: {
					Cancel: function() {
						jQuery(this).dialog('close');
						}
					}
				});
				jQuery("#edit_question_dialog_"+id).dialog('open');
			};
			function mlw_add_new_question(id)
			{
				var total_answers = parseFloat(document.getElementById("question_"+id+"_answer_total").value);
				total_answers = total_answers + 1;
				document.getElementById("question_"+id+"_answer_total").value = total_answers;
				jQuery("#question_"+id+"_answers").append("<tr valign='top'><td><span style='font-weight:bold;'>Answer "+total_answers+"</span></td><td><input type='text' name='edit_answer_"+total_answers+"' id='edit_answer_"+total_answers+"' style='border-color:#000000;color:#3300CC;cursor:hand;width: 250px;'/></td><td><input type='text' name='edit_answer_"+total_answers+"_points' id='edit_answer_"+total_answers+"_points' value=0 style='border-color:#000000;color:#3300CC; cursor:hand;'/></td><td><input type='checkbox' id='edit_answer_"+total_answers+"_correct' name='edit_answer_"+total_answers+"_correct' value=1 /></td></tr>");
			}
			function mlw_add_answer_to_new_question()
			{
				var total_answers = parseFloat(document.getElementById("new_question_answer_total").value);
				total_answers = total_answers + 1;
				document.getElementById("new_question_answer_total").value = total_answers;
				jQuery("#new_question_answers").append("<tr valign='top'><td><span style='font-weight:bold;'>Answer "+total_answers+"</span></td><td><input type='text' name='answer_"+total_answers+"' id='answer_"+total_answers+"' style='border-color:#000000;color:#3300CC;cursor:hand;width: 250px;'/></td><td><input type='text' name='answer_"+total_answers+"_points' id='answer_"+total_answers+"_points' value=0 style='border-color:#000000;color:#3300CC; cursor:hand;'/></td><td><input type='checkbox' id='answer_"+total_answers+"_correct' name='answer_"+total_answers+"_correct' value=1 /></td></tr>");
			}
		</script>
		<style>
			.linkOptions
			{
				color: #0074a2 !important;
				font-size: 14px !important;
			}
			.linkDelete
			{
				color: red !important;
				font-size: 14px !important;
			}
			.linkOptions:hover,
			.linkDelete:hover
			{
				background-color: black;
			}
		</style>
		<button class="button" id="new_question_button_two">Add Question</button>
		<br />
		<?php
		$question_list = "";
		$display = "";
		$alternate = "";
		foreach($mlw_question_data as $mlw_question_info) {
			if (is_serialized($mlw_question_info->question_settings) && is_array(@unserialize($mlw_question_info->question_settings))) 
			{
				$mlw_question_settings = @unserialize($mlw_question_info->question_settings);
			}
			else
			{
				$mlw_question_settings = array();
				$mlw_question_settings['required'] = 1;
			}
			$mlw_question_type_text = "";
			switch ($mlw_question_info->question_type) {
				case 0:
					$mlw_question_type_text = "Multiple Choice";
					break;
				case 1:
					$mlw_question_type_text = "Horizontal Multiple Choice";
					break;
				case 2:
					$mlw_question_type_text = "Drop Down";
					break;
				case 3:
					$mlw_question_type_text = "Small Open Answer";
					break;
				case 4:
					$mlw_question_type_text = "Multiple Response";
					break;
				case 5:
					$mlw_question_type_text = "Large Open Answer";
					break;
				case 6:
					$mlw_question_type_text = "Text Block";
					break;
				case 7:
					$mlw_question_type_text = "Number";
					break;
				case 8:
					$mlw_question_type_text = "Accept";
					break;
				case 9:
					$mlw_question_type_text = "Captcha";
					break;
				case 10:
					$mlw_question_type_text = "Horizontal Multiple Response";
					break;
				default:
					$mlw_question_type_text = "Error Code ";
			}
			if($alternate) $alternate = "";
			else $alternate = " class=\"alternate\"";
			$question_list .= "<tr{$alternate}>";
			$question_list .= "<td><span style='font-size:16px;'>" . $mlw_question_info->question_order . "</span></td>";
			$question_list .= "<td><span style='font-size:16px;'>" . $mlw_question_type_text . "</span></td>";
			$question_list .= "<td class='post-title column-title'><span style='font-size:16px;'>" . $mlw_question_info->question_name ."</span><div class='row-actions'><a class='linkOptions' onclick=\"editQuestion('".$mlw_question_info->question_id."')\" href='#'>Edit</a> | <a class='linkOptions' onclick=\"duplicateQuestion('".$mlw_question_info->question_id."')\" href='#'>Duplicate</a>| <a class='linkDelete' onclick=\"deleteQuestion('".$mlw_question_info->question_id."')\" href='#'>Delete</a></div></td>";
			$question_list .= "</tr>";
			
			
			$mlw_question_answer_array = $mlw_qmn_answer_arrays[$mlw_question_info->question_id];
			?>
			<div id="edit_question_dialog_<?php echo $mlw_question_info->question_id; ?>" title="Edit Question" style="display:none;">
			<?php
			echo "<form action='' method='post'>";
			echo "<input type='hidden' name='edit_question' value='confirmation' />";
			echo "<input type='hidden' id='edit_question_id' name='edit_question_id' value='".$mlw_question_info->question_id."' />";
			echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
			?>
			<table class="wide" style="text-align: left; white-space: nowrap;" id="question_<?php echo $mlw_question_info->question_id; ?>_answers" name="question_<?php echo $mlw_question_info->question_id; ?>_answers">
			<tr>
			<td><span style='font-weight:bold;'>Question</span></td>
			<td colspan="3">
				<textarea name="edit_question_name" id="edit_question_name" style="width: 500px; height: 150px;"><?php echo htmlspecialchars_decode($mlw_question_info->question_name, ENT_QUOTES); ?></textarea>
			</td>
			</tr>
			<tr valign="top">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			</tr>
			<tr valign="top">
			<td>&nbsp;</td>
			<td><span style='font-weight:bold;'>Answers</span></td>
			<td><span style='font-weight:bold;'>Points Worth</span></td>
			<td><span style='font-weight:bold;'>Correct Answer</span></td>
			</tr>
			<?php
			$mlw_answer_total = 0;
			foreach($mlw_question_answer_array as $mlw_question_answer_each)
			{
				$mlw_answer_total = $mlw_answer_total + 1;
				?>
				<tr valign="top">
				<td><span style='font-weight:bold;'>Answer <?php echo $mlw_answer_total; ?></span></td>
				<td>
				<input type="text" name="edit_answer_<?php echo $mlw_answer_total; ?>" id="edit_answer_<?php echo $mlw_answer_total; ?>" value="<?php echo esc_attr(htmlspecialchars_decode($mlw_question_answer_each[0], ENT_QUOTES)); ?>" style="border-color:#000000;
					color:#3300CC; 
					cursor:hand;
					width: 250px;"/>
				</td>
				<td>
				<input type="text" name="edit_answer_<?php echo $mlw_answer_total; ?>_points" id="edit_answer_<?php echo $mlw_answer_total; ?>_points" value="<?php echo $mlw_question_answer_each[1]; ?>" style="border-color:#000000;
					color:#3300CC; 
					cursor:hand;"/>
				</td>
				<td><input type="checkbox" id="edit_answer_<?php echo $mlw_answer_total; ?>_correct" name="edit_answer_<?php echo $mlw_answer_total; ?>_correct" <?php if ($mlw_question_answer_each[2] == 1) { echo 'checked="checked"'; } ?> value=1 /></td>
				</tr>			
				<?php
			}
			?>
			</table>
			<a href="#" class="button" id="new_answer_button" onclick="mlw_add_new_question(<?php echo $mlw_question_info->question_id; ?>);">Add New Answer!</a>
			<br />
			<br />
			<table class="wide" style="text-align: left; white-space: nowrap;">
			<tr>
				<td><span style='font-weight:bold;'>Correct Answer Info:</span></td>
				<td colspan="3"><input type="text" name="edit_correct_answer_info" id="edit_correct_answer_info" style="border-color:#000000;
				color:#3300CC; 
				cursor:hand;
				width:550px;" value="<?php echo esc_attr(htmlspecialchars_decode($mlw_question_info->question_answer_info, ENT_QUOTES)); ?>"/></td>
			</tr>
			<tr valign="top">
			<td><span style='font-weight:bold;'>Hint</span></td>
			<td colspan="3">
			<input type="text" name="edit_hint" id="edit_hint" style="border-color:#000000;
				color:#3300CC; 
				cursor:hand;
				width:550px;" value="<?php echo htmlspecialchars_decode($mlw_question_info->hints, ENT_QUOTES); ?>"/>
			</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr valign="top">
			<td><span style='font-weight:bold;'>Question Type</span></td>
			<td colspan="3">
				<select name="edit_question_type">
					<option value="0" <?php if ($mlw_question_info->question_type == 0) { echo 'selected="selected"'; } ?>>Normal Multiple Choice (Vertical Radio)</option>
					<option value="1" <?php if ($mlw_question_info->question_type == 1) { echo 'selected="selected"'; } ?>>Horizontal Multiple Choice (Horizontal Radio)</option>
					<option value="2" <?php if ($mlw_question_info->question_type == 2) { echo 'selected="selected"'; } ?>>Drop Down (Select)</option>
					<option value="3" <?php if ($mlw_question_info->question_type == 3) { echo 'selected="selected"'; } ?>>Open Answer (Text Input)</option>
					<option value="5" <?php if ($mlw_question_info->question_type == 5) { echo 'selected="selected"'; } ?>>Open Answer (Large Text Input)</option>
					<option value="4" <?php if ($mlw_question_info->question_type == 4) { echo 'selected="selected"'; } ?>>Multiple Response (Checkbox)</option>
					<option value="10" <?php if ($mlw_question_info->question_type == 10) { echo 'selected="selected"'; } ?>>Horizontal Multiple Response (Checkbox)</option>
					<option value="6" <?php if ($mlw_question_info->question_type == 6) { echo 'selected="selected"'; } ?>>Text Block</option>
					<option value="7" <?php if ($mlw_question_info->question_type == 7) { echo 'selected="selected"'; } ?>>Number</option>
					<option value="8" <?php if ($mlw_question_info->question_type == 8) { echo 'selected="selected"'; } ?>>Accept</option>
					<option value="9" <?php if ($mlw_question_info->question_type == 9) { echo 'selected="selected"'; } ?>>Captcha</option>
				</select>
			</div></td>
			</tr>
			<tr valign="top">
			<td><span style='font-weight:bold;'>Comment Field</span></td>
			<td colspan="3">
				<input type="radio" id="<?php echo $mlw_question_info->question_id; ?>_editCommentRadio1" name="edit_comments" value=0 <?php if ($mlw_question_info->comments == 0) { echo 'checked="checked"'; } ?>/><label for="<?php echo $mlw_question_info->question_id; ?>_editCommentRadio1">Small Text Field</label>
				<input type="radio" id="<?php echo $mlw_question_info->question_id; ?>_editCommentRadio3" name="edit_comments" value=2 <?php if ($mlw_question_info->comments == 2) { echo 'checked="checked"'; } ?>/><label for="<?php echo $mlw_question_info->question_id; ?>_editCommentRadio3">Large Text Field</label>
				<input type="radio" id="<?php echo $mlw_question_info->question_id; ?>_editCommentRadio2" name="edit_comments" value=1 <?php if ($mlw_question_info->comments == 1) { echo 'checked="checked"'; } ?>/><label for="<?php echo $mlw_question_info->question_id; ?>_editCommentRadio2">None</label>
			</td>
			</tr>
			<tr valign="top">
			<td><span style='font-weight:bold;'>Question Order</span></td>
			<td>
			<input type="number" step="1" min="1" name="edit_question_order" value="<?php echo $mlw_question_info->question_order; ?>" id="edit_question_order" style="border-color:#000000;
				color:#3300CC; 
				cursor:hand;"/>
			</td>
			</tr>
			<tr valign="top">
			<td><span style='font-weight:bold;'>Required?</span></td>
			<td colspan="3">
				<select name="edit_required">
					<option value="0" <?php if ($mlw_question_settings['required'] == 0) { echo 'selected="selected"'; } ?>>Yes</option>
					<option value="1" <?php if ($mlw_question_settings['required'] == 1) { echo 'selected="selected"'; } ?>>No</option>
				</select>
			</div></td>
			</tr>
			</table>
			<p> *Required currently only works on open answer, number, and captcha question types</p>
			<input type="hidden" name="question_<?php echo $mlw_question_info->question_id; ?>_answer_total" id="question_<?php echo $mlw_question_info->question_id; ?>_answer_total" value="<?php echo $mlw_answer_total; ?>" />
			<p class='submit'><input type='submit' class='button-primary' value='Edit Question' /></p>
			</form>
			</div>	
			
			<?php
		}
		
		if( $mlw_qmn_question_page > 0 )
		{
			$mlw_qmn_previous_page = $mlw_qmn_question_page - 2;
			$display .= "<a id=\"prev_page\" href=\"?page=mlw_quiz_options&&mlw_question_page=$mlw_qmn_previous_page&&quiz_id=$quiz_id\">Previous 10 Questions</a>";
			if( $mlw_qmn_question_left > $mlw_qmn_table_limit )
			{
				$display .= "<a id=\"next_page\" href=\"?page=mlw_quiz_options&&mlw_question_page=$mlw_qmn_question_page&&quiz_id=$quiz_id\">Next 10 Questions</a>";
			}
		}
		else if( $mlw_qmn_question_page == 0 )
		{
		   if( $mlw_qmn_question_left > $mlw_qmn_table_limit )
		   {
				$display .= "<a id=\"next_page\" href=\"?page=mlw_quiz_options&&mlw_question_page=$mlw_qmn_question_page&&quiz_id=$quiz_id\">Next 10 Questions</a>";
		   }
		}
		else if( $mlw_qmn_question_left < $mlw_qmn_table_limit )
		{
		   $mlw_qmn_previous_page = $mlw_qmn_question_page - 2;
		   $display .= "<a id=\"prev_page\" href=\"?page=mlw_quiz_options&&mlw_question_page=$mlw_qmn_previous_page&&quiz_id=$quiz_id\">Previous 10 Questions</a>";
		}

		$display .= "<table class=\"widefat\">";
			$display .= "<thead><tr>
				<th>Question Order</th>
				<th>Question Type</th>
				<th>Question</th>
			</tr></thead>";
			$display .= "<tbody id=\"the-list\">{$question_list}</tbody>";
			$display .= "<tfoot><tr>
				<th>Question Order</th>
				<th>Question Type</th>
				<th>Question</th>
			</tr></tfoot>";
			$display .= "</table>";
		echo $display;
		?>
		<button class="button" id="new_question_button">Add Question</button>
		<div id="new_question_dialog" title="Create New Question" style="display:none;">
		
		<?php
		echo "<form action='' method='post'>";
		echo "<input type='hidden' name='create_question' value='confirmation' />";
		echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
		?>		
		<table class="wide" style="text-align: left; white-space: nowrap;" id="new_question_answers" name="new_question_answers">
		<tr>
		<td><span style='font-weight:bold;'>Question</span></td>
		<td colspan="3">
			<textarea name="question_name" id="question_name" style="width: 500px; height: 150px;"></textarea>
		</td>
		</tr>
		<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
		<tr valign="top">
		<td>&nbsp;</td>
		<td><span style='font-weight:bold;'>Answers</span></td>
		<td><span style='font-weight:bold;'>Points Worth</span></td>
		<td><span style='font-weight:bold;'>Correct Answer</span></td>
		</tr>
		<?php
		$mlw_answer_total = 0;
		$mlw_answer_total = $mlw_answer_total + 1;
		?>
		<tr valign="top">
		<td><span style='font-weight:bold;'>Answer <?php echo $mlw_answer_total; ?></span></td>
		<td>
		<input type="text" name="answer_<?php echo $mlw_answer_total; ?>" id="answer_<?php echo $mlw_answer_total; ?>" value="" style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;
			width: 250px;"/>
		</td>
		<td>
		<input type="text" name="answer_<?php echo $mlw_answer_total; ?>_points" id="answer_<?php echo $mlw_answer_total; ?>_points" value=0 style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;"/>
		</td>
		<td><input type="checkbox" id="answer_<?php echo $mlw_answer_total; ?>_correct" name="answer_<?php echo $mlw_answer_total; ?>_correct" checked="checked" value=1 /></td>
		</tr>
		</table>
		<a href="#" class="button" id="new_answer_button" onclick="mlw_add_answer_to_new_question();">Add New Answer!</a>
		<br />
		<br />
		<table class="wide" style="text-align: left; white-space: nowrap;">
		<tr>
			<td><span style='font-weight:bold;'>Correct Answer Info</span></td>
			<td colspan="3"><input type="text" name="correct_answer_info" value="" id="correct_answer_info" style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;
			width:550px;"/></td>
		</tr>
		<tr valign="top">
		<td><span style='font-weight:bold;'>Hint</span></td>
		<td colspan="3">
		<input type="text" name="hint" value="" id="hint" style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;
			width:550px;"/>
		</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr valign="top">
		<td><span style='font-weight:bold;'>Question Type</span></td>
		<td colspan="3">
			<select name="question_type">
				<option value="0" selected="selected">Normal Multiple Choice (Vertical Radio)</option>
				<option value="1">Horizontal Multiple Choice (Horizontal Radio)</option>
				<option value="2">Drop Down (Select)</option>
				<option value="3">Open Answer (Text Input)</option>
				<option value="5">Open Answer (Large Text Input)</option>
				<option value="4">Multiple Response (Checkbox)</option>
				<option value="10">Horizontal Multiple Response (Checkbox)</option>
				<option value="6">Text Block</option>
				<option value="7">Number</option>
				<option value="8">Accept</option>
				<option value="9">Captcha</option>
			</select>
		</div></td>
		</tr>
		<tr valign="top">
		<td><span style='font-weight:bold;'>Comment Field</span></td>
		<td colspan="3"><div id="comments">
			<input type="radio" id="commentsRadio1" name="comments" value=0 /><label for="commentsRadio1">Small Text Field</label>
			<input type="radio" id="commentsRadio3" name="comments" value=2 /><label for="commentsRadio3">Large Text Field</label>
			<input type="radio" id="commentsRadio2" name="comments" checked="checked" value=1 /><label for="commentsRadio2">None</label>
		</div></td>
		</tr>
		<tr valign="top">
		<td><span style='font-weight:bold;'>Question Order</span></td>
		<td>
		<input type="number" step="1" min="1" name="new_question_order" value="<?php echo $mlw_qmn_question_count+1; ?>" id="new_question_order" style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;"/>
		</td>
		</tr>
		<tr valign="top">
		<td><span style='font-weight:bold;'>Required?</span></td>
		<td colspan="3">
			<select name="required">
				<option value="0" selected="selected">Yes</option>
				<option value="1">No</option>
			</select>
		</div></td>
		</tr>
		</table>
		<p> *Required currently only works on open answer, number, and captcha question types</p>
		<input type="hidden" name="new_question_answer_total" id="new_question_answer_total" value="<?php echo $mlw_answer_total; ?>" />
		<?php
		echo "<p class='submit'><input type='submit' class='button-primary' value='Create Question' /></p>";
		echo "</form>";
		?>
		</div>
		<!--Dialogs-->
		<div id="delete_dialog" title="Delete Question?" style="display:none;">
			<h3><b>Are you sure you want to delete Question <span id="delete_question_id"></span>?</b></h3>
			<?php
			echo "<form action='' method='post'>";
			echo "<input type='hidden' name='delete_question' value='confirmation' />";
			echo "<input type='hidden' id='question_id' name='question_id' value='' />";
			echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
			echo "<p class='submit'><input type='submit' class='button-primary' value='Delete Question' /></p>";
			echo "</form>";	
			?>
		</div>
		
		<div id="duplicate_dialog" title="Duplicate Question?" style="display:none;">
			<h3><b>Are you sure you want to duplicate this Question?</b></h3>
			<?php
			echo "<form action='' method='post'>";
			echo "<input type='hidden' name='duplicate_question' value='confirmation' />";
			echo "<input type='hidden' id='duplicate_question_id' name='duplicate_question_id' value='' />";
			echo "<input type='hidden' name='quiz_id' value='".$quiz_id."' />";
			echo "<p class='submit'><input type='submit' class='button-primary' value='Duplicate Question' /></p>";
			echo "</form>";	
			?>
		</div>
	</div>
	<?php
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

function mlw_options_leaderboard_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	///Submit saved leaderboard template into database
	if ( isset($_POST["save_leaderboard_options"]) && $_POST["save_leaderboard_options"] == "confirmation")
	{
		///Variables for save leaderboard options form
		$mlw_leaderboard_template = $_POST["mlw_quiz_leaderboard_template"];
		$mlw_leaderboard_quiz_id = $_POST["leaderboard_quiz_id"];
		$update = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET leaderboard_template='".$mlw_leaderboard_template."', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=".$mlw_leaderboard_quiz_id;
		$results = $wpdb->query( $update );
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The leaderboards has been updated successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Leaderboard Options Have Been Edited For Quiz Number ".$mlw_leaderboard_quiz_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0009.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	?>
	<div id="tabs-4" class="mlw_tab_content">
		<h3>Template Variables</h3>
		<table class="form-table">
			<tr>
				<td><strong>%FIRST_PLACE_NAME%</strong> - The name of the user who is in first place</td>
				<td><strong>%FIRST_PLACE_SCORE%</strong> - The score from the first place's quiz</td>
			</tr>
		
			<tr>
				<td><strong>%SECOND_PLACE_NAME%</strong> - The name of the user who is in second place</td>
				<td><strong>%SECOND_PLACE_SCORE%</strong> - The score from the second place's quiz</td>
			</tr>
		
			<tr>
				<td><strong>%THIRD_PLACE_NAME%</strong> - The name of the user who is in third place</td>
				<td><strong>%THIRD_PLACE_SCORE%</strong> - The score from the third place's quiz</td>
			</tr>
			
			<tr>
				<td><strong>%FOURTH_PLACE_NAME%</strong> - The name of the user who is in fourth place</td>
				<td><strong>%FOURTH_PLACE_SCORE%</strong> - The score from the fourth place's quiz</td>
			</tr>
			
			<tr>
				<td><strong>%FIFTH_PLACE_NAME%</strong> - The name of the user who is in fifth place</td>
				<td><strong>%FIFTH_PLACE_SCORE%</strong> - The score from the fifth place's quiz</td>
			</tr>
			
			<tr>
				<td><strong>%QUIZ_NAME%</strong> - The name of the quiz</td>
			</tr>
		</table>
		<button id="save_template_button" class="button" onclick="javascript: document.quiz_leaderboard_options_form.submit();">Save Leaderboard Options</button>
		<?php
			echo "<form action='' method='post' name='quiz_leaderboard_options_form'>";
			echo "<input type='hidden' name='save_leaderboard_options' value='confirmation' />";
			echo "<input type='hidden' name='leaderboard_quiz_id' value='".$quiz_id."' />";
		?>
    	<table class="form-table">
			<tr>
				<td width="30%">
					<strong>Leaderboard Template</strong>
					<br />
					<p>Allowed Variables: </p>
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
		<button id="save_template_button" class="button" onclick="javascript: document.quiz_leaderboard_options_form.submit();">Save Leaderboard Options</button>
		</form>
	</div>
	<?php
}

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
			$mlwQuizMasterNext->alertManager->newAlert('The certificate has been updated successfully.', 'success');
			
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
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0012.', 'error');
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
		$mlw_certificate_options = array('Enter title here', 'Enter text here', '', '', 1);	
	}
	?>
	<div id="tabs-5" class="mlw_tab_content">
		<script>
			jQuery(function() {
  				jQuery( "#enableCertificates" ).buttonset();
			});
		</script>
		<h3>Quiz Certificate (Beta)</h3>
		<p>Enter in your text here to fill in the certificate for this quiz. Be sure to enter in the link variable into the templates on the Quiz Text tab so the user can access the certificate.</p>
		<p>These fields cannot contain HTML.</p>
		<button id="save_certificate_button" class="button" onclick="javascript: document.quiz_certificate_options_form.submit();">Save Certificate Options</button>
		<?php
			echo "<form action='' method='post' name='quiz_certificate_options_form'>";
			echo "<input type='hidden' name='save_certificate_options' value='confirmation' />";
			echo "<input type='hidden' name='certificate_quiz_id' value='".$quiz_id."' />";
		?>
		<table class="form-table">
			<tr valign="top">
				<td><label for="enableCertificates">Enable Certificates For This Quiz?</label></td>
				<td><div id="enableCertificates">
				    <input type="radio" id="radio30" name="enableCertificates" <?php if ($mlw_certificate_options[4] == 0) {echo 'checked="checked"';} ?> value='0' /><label for="radio30">Yes</label>
				    <input type="radio" id="radio31" name="enableCertificates" <?php if ($mlw_certificate_options[4] == 1) {echo 'checked="checked"';} ?> value='1' /><label for="radio31">No</label>
				</div></td>
			</tr>
			<tr>
				<td width="30%">
					<strong>Certificate Title</strong>
				</td>
				<td><textarea cols="80" rows="15" id="certificate_title" name="certificate_title"><?php echo $mlw_certificate_options[0]; ?></textarea>
				</td>			
			</tr>			
			<tr>
				<td width="30%">
					<strong>Message Displayed On Certificate</strong>
					<br />
					<p>Allowed Variables: </p>
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
					<strong>URL To Logo (Must be JPG, JPEG, PNG or GIF)</strong>
				</td>
				<td><textarea cols="80" rows="15" id="certificate_logo" name="certificate_logo"><?php echo $mlw_certificate_options[2]; ?></textarea>
				</td>			
			</tr>
			<tr>
				<td width="30%">
					<strong>URL To Background Img (Must be JPG, JPEG, PNG or GIF)</strong>
				</td>
				<td><textarea cols="80" rows="15" id="certificate_background" name="certificate_background"><?php echo $mlw_certificate_options[3]; ?></textarea>
				</td>			
			</tr>
		</table>
		<button id="save_certificate_button" class="button" onclick="javascript: document.quiz_certificate_options_form.submit();">Save Certificate Options</button>
		</form>
	</div>
	<?php
}

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

function mlw_options_styling_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	if (isset($_POST["save_style_options"]) && $_POST["save_style_options"] == "confirmation")
	{
		//Function Variables
		$mlw_qmn_style_id = intval($_POST["style_quiz_id"]);
		$mlw_qmn_theme = $_POST["save_quiz_theme"];
		$mlw_qmn_style = htmlspecialchars(stripslashes($_POST["quiz_css"]), ENT_QUOTES);
		
		//Save the new css
		$mlw_save_stle_results = $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."mlw_quizzes SET quiz_stye='%s', theme_selected='%s', last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=%d", $mlw_qmn_style, $mlw_qmn_theme, $mlw_qmn_style_id ) );
		if ($mlw_save_stle_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The style has been saved successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Styles Have Been Saved For Quiz Number ".$mlw_qmn_style_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0015.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	?>
	<div id="tabs-7" class="mlw_tab_content">
		<script>
			function mlw_qmn_theme(theme)
			{
				document.getElementById('save_quiz_theme').value = theme;
				jQuery("div.mlw_qmn_themeBlockActive").toggleClass("mlw_qmn_themeBlockActive");
				jQuery("#mlw_qmn_theme_block_"+theme).toggleClass("mlw_qmn_themeBlockActive");
				
			}
		</script>
		<?php
			echo "<form action='' method='post' name='quiz_style_form'>";
			echo "<input type='hidden' name='save_style_options' value='confirmation' />";
			echo "<input type='hidden' name='style_quiz_id' value='".$quiz_id."' />";
			echo "<input type='hidden' name='save_quiz_theme' id='save_quiz_theme' value='".$mlw_quiz_options->theme_selected."' />";
		?>
		<h3>Quiz Styles</h3>
		<p>Choose your style:</p>
		<style>
			div.mlw_qmn_themeBlock
			{
				cursor: pointer;
				position: relative;
				height: 100px;
				width: 100px;
				background-color: #eee;
				color: blue;
				border: #ccc solid 1px;
				border-radius: 4px;
				padding: 5px 5px 5px 5px;
				display: inline-block;
				box-sizing: border-box;
				margin: auto;
			}
			div.mlw_qmn_themeBlockActive
			{
				background-color: yellow;
			}
		</style>
		<div onclick="mlw_qmn_theme('default');" id="mlw_qmn_theme_block_default" class="mlw_qmn_themeBlock <?php if ($mlw_quiz_options->theme_selected == 'default') {echo 'mlw_qmn_themeBlockActive';} ?>">Custom</div>
		<?php do_action('mlw_qmn_quiz_themes'); ?>
		<script>
			mlw_qmn_theme('<?php echo $mlw_quiz_options->theme_selected; ?>');			
		</script>
		<br /><br />
		<button id="save_styles_button" class="button" onclick="javascript: document.quiz_style_form.submit();">Save Quiz Style</button>
		<hr />
		<h3>Custom Theme CSS</h3>
		<p>Entire quiz is a div with class 'mlw_qmn_quiz'</p>
		<p>Each page of the quiz is div with class 'quiz_section'</p>
		<p>Message before quiz text is a span with class 'mlw_qmn_message_before'</p>
		<p>The text for each question is wrapped in class 'mlw_qmn_question'</p>
		<p>Each comment field for the questions is wrapped in class 'mlw_qmn_question_comment'</p>
		<p>Label text for comment section is wrapped in class 'mlw_qmn_comment_section_text'</p>
		<p>The message displayed at end of quiz is a span with class 'mlw_qmn_message_end'</p>
		<p>Each button shown for pagination (i.e Next/Previous) is wrapped in class 'mlw_qmn_quiz_link'</p>
		<p>Timer is wrapped in class 'mlw_qmn_timer'</p>
		<p>Each horizontal multiple response is wrapped in a span with class 'mlw_horizontal_multiple'</p>
		<button id="save_styles_button" class="button" onclick="javascript: document.quiz_style_form.submit();">Save Quiz Style</button>

		<table class="form-table">
			<tr>
				<td width="66%"><textarea style="width: 100%; height: 100%;" id="quiz_css" name="quiz_css"><?php echo $mlw_quiz_options->quiz_stye; ?></textarea>
				</td>	
				<td width="30%">
					<strong>Default:</strong><br />
					div.mlw_qmn_quiz input[type=radio],<br />
					div.mlw_qmn_quiz input[type=submit],<br />
					div.mlw_qmn_quiz label {<br />
						cursor: pointer;<br />
					}<br />
					div.mlw_qmn_quiz input:not([type=submit]):focus,<br />
					div.mlw_qmn_quiz textarea:focus {<br />
						background: #eaeaea;<br />
					}<br />
					div.mlw_qmn_quiz {<br />
						text-align: left;<br />
					}<br />
					div.quiz_section {<br />
						<br />
					}<br />
					div.mlw_qmn_timer {<br />
						position:fixed;<br />
						top:200px;<br />
						right:0px;<br />
						width:130px;<br />
						color:#00CCFF;<br />
						border-radius: 15px;<br />
						background:#000000;<br />
						text-align: center;<br />
						padding: 15px 15px 15px 15px<br />
					}<br />
					div.mlw_qmn_quiz input[type=submit],<br />
					a.mlw_qmn_quiz_link<br />
					{<br />
						    border-radius: 4px;<br />
						    position: relative;<br />
						    background-image: linear-gradient(#fff,#dedede);<br />
							background-color: #eee;<br />
							border: #ccc solid 1px;<br />
							color: #333;<br />
							text-shadow: 0 1px 0 rgba(255,255,255,.5);<br />
							box-sizing: border-box;<br />
						    display: inline-block;<br />
						    padding: 5px 5px 5px 5px;<br />
	   						margin: auto;<br />
					}<br />
				</td>
			</tr>
		</table>
		<button id="save_styles_button" class="button" onclick="javascript: document.quiz_style_form.submit();">Save Quiz Style</button>
		</form>
	</div>
	<?php
}

function mlw_options_tools_tab_content()
{
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Update Quiz Table
	if (isset($_POST["mlw_reset_quiz_stats"]) && $_POST["mlw_reset_quiz_stats"] == "confirmation")
	{
		//Variables from reset stats form
		$mlw_reset_stats_quiz_id = $_POST["mlw_reset_quiz_id"];
		$mlw_reset_update_sql = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET quiz_views=0, quiz_taken=0, last_activity='".date("Y-m-d H:i:s")."' WHERE quiz_id=".$mlw_reset_stats_quiz_id;
		$mlw_reset_sql_results = $wpdb->query( $mlw_reset_update_sql );
		if ($mlw_reset_sql_results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('The stats has been reset successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Quiz Stats Have Been Reset For Quiz Number ".$mlw_leaderboard_quiz_id."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );	
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0010.', 'error');
		}
	}
	
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}
	?>
	<div id="tabs-8" class="mlw_tab_content">
	<script>
	jQuery(function() {
			jQuery('#mlw_reset_stats_dialog').dialog({
				autoOpen: false,
				show: 'blind',
				width:700,
				hide: 'explode',
				buttons: {
				Ok: function() {
					jQuery(this).dialog('close');
					}
				}
			});
		
			jQuery('#mlw_reset_stats_button').click(function() {
				jQuery('#mlw_reset_stats_dialog').dialog('open');
				return false;
		}	);
		});
	</script>
		<p>Use this button to reset all the stats collected for this quiz (Quiz Views and Times Quiz Has Been Taken). </p>
		<button class="button" id="mlw_reset_stats_button">Reset Quiz Views And Taken Stats</button>
		<?php do_action('mlw_qmn_quiz_tools'); ?>
		<div id="mlw_reset_stats_dialog" title="Reset Stats For This Quiz" style="display:none;">
		<p>Are you sure you want to reset the stats to 0? All views and taken stats for this quiz will be reset. This is permanent and cannot be undone.</p>
		<?php
			echo "<form action='' method='post'>";
			echo "<input type='hidden' name='mlw_reset_quiz_stats' value='confirmation' />";
			echo "<input type='hidden' name='mlw_reset_quiz_id' value='".$quiz_id."' />";
			echo "<p class='submit'><input type='submit' class='button-primary' value='Reset All Stats For Quiz' /></p>";
			echo "</form>";
		?>
		</div>		
	</div>
	<?php
}

function mlw_options_preview_tab_content()
{
	?>
	<div id="tabs-preview" class="mlw_tab_content">
		<?php
		echo do_shortcode( '[mlw_quizmaster quiz='.intval($_GET["quiz_id"]).']' );
		?>
	</div>
	<?php
}
?>
