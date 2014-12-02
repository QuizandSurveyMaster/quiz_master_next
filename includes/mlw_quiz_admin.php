<?php
/*
This page lists all the quizzes currently on the website and allows you to create more quizzes.
*/
/* 
Copyright 2013, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_quiz_admin()
{
	global $wpdb;
	global $mlwQmnAlertManager;
	$table_name = $wpdb->prefix . "mlw_quizzes";

	//Create new quiz
	if ( isset( $_POST["create_quiz"] ) && $_POST["create_quiz"] == "confirmation" )
	{
		$quiz_name = htmlspecialchars($_POST["quiz_name"], ENT_QUOTES);
		//Insert New Quiz Into Table
		$mlw_leaderboard_default = "<h3>Leaderboard for %QUIZ_NAME%</h3>
			1. %FIRST_PLACE_NAME%-%FIRST_PLACE_SCORE%<br />
			2. %SECOND_PLACE_NAME%-%SECOND_PLACE_SCORE%<br />
			3. %THIRD_PLACE_NAME%-%THIRD_PLACE_SCORE%<br />
			4. %FOURTH_PLACE_NAME%-%FOURTH_PLACE_SCORE%<br />
			5. %FIFTH_PLACE_NAME%-%FIFTH_PLACE_SCORE%<br />";
		$mlw_style_default = "
				div.mlw_qmn_quiz input[type=radio],
				div.mlw_qmn_quiz input[type=submit],
				div.mlw_qmn_quiz label {
					cursor: pointer;
				}
				div.mlw_qmn_quiz input:not([type=submit]):focus,
				div.mlw_qmn_quiz textarea:focus {
					background: #eaeaea;
				}
				div.mlw_qmn_quiz {
					text-align: left;
				}
				div.quiz_section {
					
				}
				div.mlw_qmn_timer {
					position:fixed;
					top:200px;
					right:0px;
					width:130px;
					color:#00CCFF;
					border-radius: 15px;
					background:#000000;
					text-align: center;
					padding: 15px 15px 15px 15px
				}
				div.mlw_qmn_quiz input[type=submit],
				a.mlw_qmn_quiz_link
				{
					    border-radius: 4px;
					    position: relative;
					    background-image: linear-gradient(#fff,#dedede);
						background-color: #eee;
						border: #ccc solid 1px;
						color: #333;
						text-shadow: 0 1px 0 rgba(255,255,255,.5);
						box-sizing: border-box;
					    display: inline-block;
					    padding: 5px 5px 5px 5px;
   						margin: auto;
				}";
		$mlw_question_answer_default = "%QUESTION%<br /> Answer Provided: %USER_ANSWER%<br /> Correct Answer: %CORRECT_ANSWER%<br /> Comments Entered: %USER_COMMENTS%<br />";
		$results = $wpdb->insert( 
			$wpdb->prefix . "mlw_quizzes", 
			array( 
				'quiz_name' => $quiz_name, 
				'message_before' => 'Enter your text here',
				'message_after' => 'Enter your text here', 
				'message_comment' => 'Enter your text here',
				'message_end_template' => '', 
				'user_email_template' => 'Enter your text here',
				'admin_email_template' => 'Enter your text here', 
				'submit_button_text' => 'Submit Quiz',
				'name_field_text' => 'Name', 
				'business_field_text' => 'Business',
				'email_field_text' => 'Email', 
				'phone_field_text' => 'Phone Number',
				'comment_field_text' => 'Comments', 
				'email_from_text' => 'Wordpress',
				'question_answer_template' => $mlw_question_answer_default, 
				'leaderboard_template' => $mlw_leaderboard_default,
				'system' => 0, 
				'randomness_order' => 0,
				'loggedin_user_contact' => 0, 
				'show_score' => 0,
				'send_user_email' => 0, 
				'send_admin_email' => 0,
				'contact_info_location' => 0, 
				'user_name' => 0,
				'user_comp' => 0, 
				'user_email' => 0,
				'user_phone' => 0, 
				'admin_email' => get_option( 'admin_email', 'Enter email' ),
				'comment_section' => 0, 
				'question_from_total' => 0,
				'total_user_tries' => 0, 
				'total_user_tries_text' => 'Enter Your Text Here',
				'certificate_template' => 'Enter Your Text Here!', 
				'social_media' => 0,
				'social_media_text' => 'I just scored %CORRECT_SCORE%% on %QUIZ_NAME%!', 
				'pagination' => 0,
				'pagination_text' => 'Next',
				'timer_limit' => 0, 
				'quiz_stye' => $mlw_style_default,
				'question_numbering' => 0, 
				'quiz_settings' => '',
				'theme_selected' => 'default', 
				'last_activity' => date("Y-m-d H:i:s"),
				'require_log_in' => 0,
				'require_log_in_text' => 'Enter Your Text Here',
				'limit_total_entries' => 0,
				'limit_total_entries_text' => 'Enter Your Text Here',
				'quiz_views' => 0, 
				'quiz_taken' => 0,
				'deleted' => 0
			), 
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
				'%d', 
				'%d',
				'%d', 
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
				'%s', 
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d', 
				'%d',
				'%d'
			) 
		);
		if ($results != false)
		{
			$mlwQmnAlertManager->newAlert('Your new quiz has been created successfully. To begin editing your quiz, click the Edit link on the new quiz.', 'success');
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'New Quiz Has Been Created: ".$quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQmnAlertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0001.', 'error');
		}
		
	}

	//Delete quiz
	if (isset( $_POST["delete_quiz"] ) && $_POST["delete_quiz"] == "confirmation")
	{
		
		//Variables from delete question form
		$mlw_quiz_id = $_POST["quiz_id"];
		$quiz_name = $_POST["delete_quiz_name"];
		$quiz_id = $_POST["quiz_id"];
		$update = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET deleted=1 WHERE quiz_id=".$mlw_quiz_id;
		$results = $wpdb->query( $update );
		$update = "UPDATE " . $wpdb->prefix . "mlw_questions" . " SET deleted=1 WHERE quiz_id=".$mlw_quiz_id;
		$delete_question_results = $wpdb->query( $update );
		if ($results != false)
		{
			$mlwQmnAlertManager->newAlert('Your quiz has been deleted successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Quiz Has Been Deleted: ".$quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQmnAlertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0002.', 'error');
		}
		
	}	

	//Edit Quiz Name
	if (isset($_POST["quiz_name_editted"]) && $_POST["quiz_name_editted"] == "confirmation")
	{
		$mlw_edit_quiz_id = $_POST["edit_quiz_id"];
		$mlw_edit_quiz_name = htmlspecialchars($_POST["edit_quiz_name"], ENT_QUOTES);
		$mlw_update_quiz_table = "UPDATE " . $wpdb->prefix . "mlw_quizzes" . " SET quiz_name='".$mlw_edit_quiz_name."' WHERE quiz_id=".$mlw_edit_quiz_id;
		$results = $wpdb->query( $mlw_update_quiz_table );
		if ($results != false)
		{
			$mlwQmnAlertManager->newAlert('Your quiz name has been updated successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Quiz Name Has Been Edited: ".$mlw_edit_quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQmnAlertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0003.', 'error');
		}		
	}
	
	//Duplicate Quiz
	if (isset($_POST["duplicate_quiz"]) && $_POST["duplicate_quiz"] == "confirmation")
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_duplicate_quiz_id = $_POST["duplicate_quiz_id"];
		$mlw_duplicate_quiz_name = htmlspecialchars($_POST["duplicate_new_quiz_name"], ENT_QUOTES);
		$mlw_qmn_duplicate_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_quizzes WHERE quiz_id=%d", $mlw_duplicate_quiz_id ) );
		$results = $wpdb->insert( 
				$table_name, 
				array( 
					'quiz_name' => $mlw_duplicate_quiz_name,
					'message_before' => $mlw_qmn_duplicate_data->message_before,
					'message_after' => $mlw_qmn_duplicate_data->message_after, 
					'message_comment' => $mlw_qmn_duplicate_data->message_comment,
					'message_end_template' => $mlw_qmn_duplicate_data->message_end_template,
					'user_email_template' => $mlw_qmn_duplicate_data->user_email_template, 
					'admin_email_template' => $mlw_qmn_duplicate_data->admin_email_template,
					'submit_button_text' => $mlw_qmn_duplicate_data->submit_button_text,
					'name_field_text' => $mlw_qmn_duplicate_data->name_field_text, 
					'business_field_text' => $mlw_qmn_duplicate_data->business_field_text,
					'email_field_text' => $mlw_qmn_duplicate_data->email_field_text,
					'phone_field_text' => $mlw_qmn_duplicate_data->phone_field_text, 
					'comment_field_text' => $mlw_qmn_duplicate_data->comment_field_text,
					'email_from_text' => $mlw_qmn_duplicate_data->email_from_text,
					'question_answer_template' => $mlw_qmn_duplicate_data->question_answer_template, 
					'leaderboard_template' => $mlw_qmn_duplicate_data->leaderboard_template,
					'system' => $mlw_qmn_duplicate_data->system,
					'randomness_order' => $mlw_qmn_duplicate_data->randomness_order, 
					'loggedin_user_contact' => $mlw_qmn_duplicate_data->loggedin_user_contact,
					'show_score' => $mlw_qmn_duplicate_data->show_score,
					'send_user_email' => $mlw_qmn_duplicate_data->send_user_email, 
					'send_admin_email' => $mlw_qmn_duplicate_data->send_admin_email,
					'contact_info_location' => $mlw_qmn_duplicate_data->contact_info_location,
					'user_name' => $mlw_qmn_duplicate_data->user_name, 
					'user_comp' => $mlw_qmn_duplicate_data->user_comp,
					'user_email' => $mlw_qmn_duplicate_data->user_email,
					'user_phone' => $mlw_qmn_duplicate_data->user_phone, 
					'admin_email' => get_option( 'admin_email', 'Enter email' ),
					'comment_section' => $mlw_qmn_duplicate_data->comment_section,
					'question_from_total' => $mlw_qmn_duplicate_data->question_from_total, 
					'total_user_tries' => $mlw_qmn_duplicate_data->total_user_tries,
					'total_user_tries_text' => $mlw_qmn_duplicate_data->total_user_tries_text,
					'certificate_template' => $mlw_qmn_duplicate_data->certificate_template, 
					'social_media' => $mlw_qmn_duplicate_data->social_media,
					'social_media_text' => $mlw_qmn_duplicate_data->social_media_text,
					'pagination' => $mlw_qmn_duplicate_data->pagination, 
					'pagination_text' => $mlw_qmn_duplicate_data->pagination_text,
					'timer_limit' => $mlw_qmn_duplicate_data->timer_limit,
					'quiz_stye' => $mlw_qmn_duplicate_data->quiz_stye,
					'question_numbering' => $mlw_qmn_duplicate_data->question_numbering,
					'quiz_settings' => $mlw_qmn_duplicate_data->quiz_settings,
					'theme_selected' => $mlw_qmn_duplicate_data->theme_selected,
					'last_activity' => date("Y-m-d H:i:s"),
					'quiz_views' => 0,
					'quiz_taken' => 0, 
					'deleted' => 0
				),
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
					'%d',
					'%d',
					'%d',
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
					'%s',
					'%s',
					'%d',
					'%d',
					'%d',
				)
			);
		$mlw_new_id = $wpdb->insert_id;
		//$results = $wpdb->query( "INSERT INTO ".$table_name." (quiz_id, quiz_name, message_before, message_after, message_comment, message_end_template, user_email_template, admin_email_template, submit_button_text, name_field_text, business_field_text, email_field_text, phone_field_text, comment_field_text, email_from_text, question_answer_template, leaderboard_template, system, randomness_order, loggedin_user_contact, show_score, send_user_email, send_admin_email, contact_info_location, user_name, user_comp, user_email, user_phone, admin_email, comment_section, question_from_total, total_user_tries, total_user_tries_text, certificate_template, social_media, social_media_text, pagination, pagination_text, timer_limit, quiz_stye, question_numbering, quiz_views, quiz_taken, deleted) VALUES (NULL , '".$mlw_duplicate_quiz_name."' , '".$mlw_qmn_duplicate_data->message_before."', '".$mlw_qmn_duplicate_data->message_after."', '".$mlw_qmn_duplicate_data->message_comment."', '".$mlw_qmn_duplicate_data->message_end_template."', '".$mlw_qmn_duplicate_data->user_email_template."', '".$mlw_qmn_duplicate_data->admin_email_template."', '".$mlw_qmn_duplicate_data->submit_button_text."', '".$mlw_qmn_duplicate_data->name_field_text."', '".$mlw_qmn_duplicate_data->business_field_text."', '".$mlw_qmn_duplicate_data->email_field_text."', '".$mlw_qmn_duplicate_data->phone_field_text."', '".$mlw_qmn_duplicate_data->comment_field_text."', '".$mlw_qmn_duplicate_data->email_from_text."', '".$mlw_qmn_duplicate_data->question_answer_template."', '".$mlw_qmn_duplicate_data->leaderboard_template."', ".$mlw_qmn_duplicate_data->system.", ".$mlw_qmn_duplicate_data->randomness_order.", ".$mlw_qmn_duplicate_data->loggedin_user_contact.", ".$mlw_qmn_duplicate_data->show_score.", ".$mlw_qmn_duplicate_data->send_user_email.", ".$mlw_qmn_duplicate_data->send_admin_email.", ".$mlw_qmn_duplicate_data->contact_info_location.", ".$mlw_qmn_duplicate_data->user_name.", ".$mlw_qmn_duplicate_data->user_comp.", ".$mlw_qmn_duplicate_data->user_email.", ".$mlw_qmn_duplicate_data->user_phone.", '".get_option( 'admin_email', 'Enter email' )."', ".$mlw_qmn_duplicate_data->comment_section.", ".$mlw_qmn_duplicate_data->question_from_total.", ".$mlw_qmn_duplicate_data->total_user_tries.", '".$mlw_qmn_duplicate_data->total_user_tries_text."', '".$mlw_qmn_duplicate_data->certificate_template."', ".$mlw_qmn_duplicate_data->social_media.", '".$mlw_qmn_duplicate_data->social_media_text."', ".$mlw_qmn_duplicate_data->pagination.", '".$mlw_qmn_duplicate_data->pagination_text."', ".$mlw_qmn_duplicate_data->timer_limit.", '".$mlw_qmn_duplicate_data->quiz_stye."', ".$mlw_qmn_duplicate_data->question_numbering.", 0, 0, 0)" );
		if ($results != false)
		{
			$mlwQmnAlertManager->newAlert('Your quiz has been duplicated successfully.', 'success');
			$hasDuplicatedQuiz = true;
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'New Quiz Has Been Created: ".$mlw_duplicate_quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQmnAlertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0011.', 'error');
		}
		if (isset($_POST["duplicate_questions"]))
		{
			$table_name = $wpdb->prefix."mlw_questions";
			$mlw_current_questions = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE deleted=0 AND quiz_id=".$mlw_duplicate_quiz_id);
			foreach ($mlw_current_questions as $mlw_question)
			{
				$question_results = $wpdb->insert( 
					$table_name, 
					array( 
						'quiz_id' => $mlw_new_id,
						'question_name' => $mlw_question->question_name,
						'answer_array' => $mlw_question->answer_array, 
						'answer_one' => $mlw_question->answer_one,
						'answer_one_points' => $mlw_question->answer_one_points, 
						'answer_two' => $mlw_question->answer_two,
						'answer_two_points' => $mlw_question->answer_two_points,
						'answer_three' => $mlw_question->answer_three,
						'answer_three_points' => $mlw_question->answer_three_points,
						'answer_four' => $mlw_question->answer_four,
						'answer_four_points' => $mlw_question->answer_four_points,
						'answer_five' => $mlw_question->answer_five,
						'answer_five_points' => $mlw_question->answer_five_points,
						'answer_six' => $mlw_question->answer_six,
						'answer_six_points' => $mlw_question->answer_six_points,
						'correct_answer' => $mlw_question->correct_answer,
						'question_answer_info' => $mlw_question->question_answer_info,
						'comments' => $mlw_question->comments,
						'hints' => $mlw_question->hints,
						'question_order' => $mlw_question->question_order,
						'question_type' => $mlw_question->question_type,
						'question_settings' => $mlw_question->question_settings,
						'deleted' => 0
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
				if ($question_results == false)
				{
					$mlwQmnAlertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0020.', 'error');
				}
			}
		}
	}

	//Retrieve list of quizzes
	global $wpdb;
	$mlw_qmn_table_limit = 10;
	$mlw_qmn_quiz_count = $wpdb->get_var( "SELECT COUNT(quiz_id) FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0'" );
	
	if( isset($_GET{'mlw_quiz_page'} ) )
	{
	   $mlw_qmn_quiz_page = $_GET{'mlw_quiz_page'} + 1;
	   $mlw_qmn_quiz_begin = $mlw_qmn_table_limit * $mlw_qmn_quiz_page ;
	}
	else
	{
	   $mlw_qmn_quiz_page = 0;
	   $mlw_qmn_quiz_begin = 0;
	}
	$mlw_qmn_quiz_left = $mlw_qmn_quiz_count - ($mlw_qmn_quiz_page * $mlw_qmn_table_limit);
	$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT quiz_id, quiz_name, quiz_views, quiz_taken, last_activity 
		FROM " . $wpdb->prefix . "mlw_quizzes WHERE deleted='0' 
		ORDER BY quiz_id DESC LIMIT %d, %d", $mlw_qmn_quiz_begin, $mlw_qmn_table_limit ) );
	?>
	<!-- css -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" />
<script type="text/javascript"
  src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
</script>
	<!-- jquery scripts -->
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	?>
	<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.0/jquery.min.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>-->
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		// increase the default animation speed to exaggerate the effect
		$j.fx.speeds._default = 1000;
		$j(function() {
			$j('#new_quiz_dialog').dialog({
				autoOpen: false,
				show: 'blind',
				width:700,
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
		
			$j('#new_quiz_button').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
			$j('#new_quiz_button_two').click(function() {
				$j('#new_quiz_dialog').dialog('open');
				return false;
		}	);
		});
		function deleteQuiz(id,quizName){
			$j("#delete_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#delete_dialog").dialog('open');
			var idText = document.getElementById("delete_quiz_id");
			var idHidden = document.getElementById("quiz_id");
			var idHiddenName = document.getElementById("delete_quiz_name");
			idText.innerHTML = id;
			idHidden.value = id;
			idHiddenName.value = quizName;
		};
		function editQuizName(id, quizName){
			$j("#edit_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#edit_dialog").dialog('open');
			document.getElementById("edit_quiz_name").value = quizName;
			document.getElementById("edit_quiz_id"). value = id;			
		}
		function duplicateQuiz(id, quizName){
			$j("#duplicate_dialog").dialog({
				autoOpen: false,
				show: 'blind',
				hide: 'explode',
				buttons: {
				Cancel: function() {
					$j(this).dialog('close');
					}
				}
			});
			$j("#duplicate_dialog").dialog('open');
			document.getElementById("duplicate_quiz_name").innerHTML = quizName;
			document.getElementById("duplicate_quiz_id"). value = id;			
		}
	</script>
	<style type="text/css">
	div.mlw_quiz_options input[type='text'] {
		border-color:#000000;
		color:#3300CC; 
		cursor:hand;
		}
	</style>
	<style>
		.linkOptions
		{
			font-size: 14px !important;
		}
		.linkDelete
		{
			color: red !important;
		}
		.linkOptions:hover
		{
			background-color: black;
		}
	</style>
	<div class="wrap">
	<div class='mlw_quiz_options'>
	<h2>Quizzes<a id="new_quiz_button" href="javascript:();" class="add-new-h2">Add New</a></h2>
	<?php $mlwQmnAlertManager->showAlerts(); ?>
	<div class="tablenav top">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $mlw_qmn_quiz_count; ?> quizzes</span>
			<span class="pagination-links">
				<?php
				$mlw_qmn_previous_page = 0;
				$mlw_current_page = $mlw_qmn_quiz_page+1;
				$mlw_total_pages = ceil($mlw_qmn_quiz_count/$mlw_qmn_table_limit);
				if( $mlw_qmn_quiz_page > 0 )
				{
				   	$mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
				   	echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
					echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
				   	if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
				   	{
						echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
				   	}
					else
					{
						echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
				   	}
				}
				else if( $mlw_qmn_quiz_page == 0 )
				{
				   if( $mlw_qmn_quiz_left > $mlw_qmn_table_limit )
				   {
						echo "<a class=\"prev-page disabled\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
						echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
						echo "<a class=\"next-page\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
				   }
				}
				else if( $mlw_qmn_quiz_left < $mlw_qmn_table_limit )
				{
				   $mlw_qmn_previous_page = $mlw_qmn_quiz_page - 2;
				   echo "<a class=\"prev-page\" title=\"Go to the previous page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_previous_page\"><</a>";
					echo "<span class=\"paging-input\">$mlw_current_page of $mlw_total_pages</span>";
					echo "<a class=\"next-page disabled\" title=\"Go to the next page\" href=\"?page=mlw_quiz_admin&&mlw_quiz_page=$mlw_qmn_quiz_page\">></a>";
				}
				?>
			</span>
			<br class="clear">
		</div>
	</div>
	<?php 
	$quotes_list = "";
	$display = "";
	$alternate = "";
	foreach($mlw_quiz_data as $mlw_quiz_info) {
		if($alternate) $alternate = "";
		else $alternate = " class=\"alternate\"";
		$quotes_list .= "<tr{$alternate}>";
		$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_id . "</span></td>";
		$quotes_list .= "<td class='post-title column-title'><span style='font-size:16px;'>" . esc_html($mlw_quiz_info->quiz_name) ." </span><span style='color:green;font-size:12px;'><a onclick=\"editQuizName('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='javascript:();'>(Edit Name)</a></span>";
		$quotes_list .= "<div class=\"row-actions\"><a class='linkOptions' href='admin.php?page=mlw_quiz_options&&quiz_id=".$mlw_quiz_info->quiz_id."'>Edit</a> | <a class='linkOptions' href='admin.php?page=mlw_quiz_results&&quiz_id=".$mlw_quiz_info->quiz_id."'>Results</a> | <a href='javascript:();' class='linkOptions' onclick=\"duplicateQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\">Duplicate</a> | <a class='linkOptions linkDelete' onclick=\"deleteQuiz('".$mlw_quiz_info->quiz_id."','".esc_js($mlw_quiz_info->quiz_name)."')\" href='javascript:();'>Delete</a></div></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>[mlw_quizmaster quiz=".$mlw_quiz_info->quiz_id."]</span></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>[mlw_quizmaster_leaderboard mlw_quiz=".$mlw_quiz_info->quiz_id."]</span></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_views . "</span></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_taken ."</span></td>";
		$quotes_list .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->last_activity ."</span></td>";
		$quotes_list .= "</tr>";
	}
	
	
	
	$display .= "<br />";

	$display .= "<table class=\"widefat\">";
		$display .= "<thead><tr>
			<th>Quiz ID</th>
			<th>Quiz Name</th>
			<th>Quiz Shortcode</th>
			<th>Leaderboard Shortcode</th>
			<th>Quiz Views</th>
			<th>Quiz Taken</th>
			<th>Last Modified</th>
		</tr></thead>";
		$display .= "<tbody id=\"the-list\">{$quotes_list}</tbody>";
		$display .= "<tfoot><tr>
			<th>Quiz ID</th>
			<th>Quiz Name</th>
			<th>Quiz Shortcode</th>
			<th>Leaderboard Shortcode</th>
			<th>Quiz Views</th>
			<th>Quiz Taken</th>
			<th>Last Modified</th>
		</tr></tfoot>";
		$display .= "</table>";
	echo $display;
	?>
	<?php echo mlw_qmn_show_adverts(); ?>
	<!--Dialogs-->
	
	<!--New Quiz Dialog-->
	<div id="new_quiz_dialog" title="Create New Quiz" style="display:none;">
		<?php
		echo "<form action='' method='post'>";
		echo "<input type='hidden' name='create_quiz' value='confirmation' />";
		?>
		<table class="wide" style="text-align: left; white-space: nowrap;">
		<thead>
		
		<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td></td>
		</tr>
			
		<tr valign="top">
		<th scope="row"><h3>Create New Quiz</h3></th>
		<td></td>
		</tr>
		
		<tr valign="top">
		<th scope="row">Quiz Name</th>
		<td>
		<input type="text" name="quiz_name" value="" style="border-color:#000000;
			color:#3300CC; 
			cursor:hand;"/>
		</td>
		</tr>
		</thead>
		</table>
		<?php
		echo "<p class='submit'><input type='submit' class='button-primary' value='Create Quiz' /></p>";
		echo "</form>";
		?>
	</div>
	
	<!--Edit Quiz Name Dialog-->
	<div id="edit_dialog" title="Edit Quiz Name" style="display:none;">
		<h3>Quiz Name:</h3><br />
		<form action='' method='post'>
		<input type="text" id="edit_quiz_name" name="edit_quiz_name" />
		<input type="hidden" id="edit_quiz_id" name="edit_quiz_id" />
		<input type='hidden' name='quiz_name_editted' value='confirmation' />
		<input type="submit" class="button-primary" value="Edit" />
		</form>
	</div>
	
	<!--Duplicate Quiz Dialog-->
	<div id="duplicate_dialog" title="Duplicate Quiz" style="display:none;">
		<h3>Create a new quiz with the same settings as <span id="duplicate_quiz_name"></span>. </h3><br />
		<form action='' method='post'>
			<label for="duplicate_questions">Duplicate questions with quiz</label><input type="checkbox" name="duplicate_questions" id="duplicate_questions"/><br />
			<br />
			<label for="duplicate_new_quiz_name">Name Of New Quiz:</label><input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" /><br />
			<input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
			<input type='hidden' name='duplicate_quiz' value='confirmation' />
			<input type="submit" class="button-primary" value="Duplicate" />
		</form>
	</div>
	
	<!--Delete Quiz Dialog-->
	<div id="delete_dialog" title="Delete Quiz?" style="display:none;">
	<h3><b>Are you sure you want to delete Quiz <span id="delete_quiz_id"></span>?</b></h3>
	<?php
	echo "<form action='' method='post'>";
	echo "<input type='hidden' name='delete_quiz' value='confirmation' />";
	echo "<input type='hidden' id='quiz_id' name='quiz_id' value='' />";
	echo "<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />";
	echo "<p class='submit'><input type='submit' class='button-primary' value='Delete Quiz' /></p>";
	echo "</form>";	
	?>
	</div>
	
	</div>
	</div>
<?php
}
?>
