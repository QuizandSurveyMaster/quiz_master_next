<?php
/**
 * QMN Quiz Creator Class
 *
 * This class handles quiz creation, update, and deletion from the admin panel
 *
 * The Quiz Creator class handles all the quiz management functions that is done from the admin panel
 *
 * @since 3.7.1
 */
class QMNQuizCreator
{
	/**
	 * QMN ID of quiz
	 *
	 * @var object
	 * @since 3.7.1
	 */
	private $quiz_id;
	
	/**
	 * If the quiz ID is set, store it as the class quiz ID
	 *
	 * @since 3.7.1
	 */
	public function __construct()
	{
		if (isset($_GET["quiz_id"]))
		{
			$this->quiz_id = intval($_GET["quiz_id"]);
		}
	}
	
	/**
	 * Creates a new quiz with the default settings
	 *
	 * @access public
	 * @since 3.7.1
	 * @return void
	 */
	public function create_quiz($quiz_name)
	{
		global $mlwQuizMasterNext;
		global $wpdb;
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
			$mlwQuizMasterNext->alertManager->newAlert('Your new quiz has been created successfully. To begin editing your quiz, click the Edit link on the new quiz.', 'success');
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
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0001.', 'error');
		}
	}
	
	/**
	 * Deletes a quiz with the given quiz_id
	 *
	 * @access public
	 * @since 3.7.1
	 * @return void
	 */
	 public function delete_quiz($quiz_id, $quiz_name)
	 {
	 	global $mlwQuizMasterNext;
		global $wpdb;
	 	$results = $wpdb->update( 
 			$wpdb->prefix . "mlw_quizzes", 
 			array( 
 				'deleted' => 1 
 			), 
 			array( 'quiz_id' => $quiz_id ), 
 			array( 
 				'%d'
 			), 
 			array( '%d' ) 
 		);
 		$delete_question_results = $wpdb->update( 
 			$wpdb->prefix . "mlw_questions", 
 			array( 
 				'deleted' => 1 
 			), 
 			array( 'quiz_id' => $quiz_id ), 
 			array( 
 				'%d'
 			), 
 			array( '%d' ) 
 		);
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('Your quiz has been deleted successfully.', 'success');
			
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
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0002.', 'error');
		}
	 }
	 
	 /**
	 * Edits the name of the quiz with the given ID
	 *
	 * @access public
	 * @since 3.7.1
	 * @return void
	 */
	 public function edit_quiz_name($quiz_id, $quiz_name)
	 {
	 	global $mlwQuizMasterNext;
		global $wpdb;
		$results = $wpdb->update( 
 			$wpdb->prefix . "mlw_quizzes", 
 			array( 
 				'quiz_name' => $quiz_name 
 			), 
 			array( 'quiz_id' => $quiz_id ), 
 			array( 
 				'%s'
 			), 
 			array( '%d' ) 
 		);
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('Your quiz name has been updated successfully.', 'success');
			
			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$table_name = $wpdb->prefix . "mlw_qm_audit_trail";
			$insert = "INSERT INTO " . $table_name .
				"(trail_id, action_user, action, time) " .
				"VALUES (NULL , '" . $current_user->display_name . "' , 'Quiz Name Has Been Edited: ".$quiz_name."' , '" . date("h:i:s A m/d/Y") . "')";
			$results = $wpdb->query( $insert );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0003.', 'error');
		}
	 }
	 
	 /**
	 * Duplicates the quiz with the given ID and gives new quiz the given quiz name
	 *
	 * @access public
	 * @since 3.7.1
	 * @return void
	 */
	 public function duplicate_quiz($quiz_id, $quiz_name, $is_duplicating_questions)
	 {
	 	global $mlwQuizMasterNext;
		global $wpdb;
		
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_qmn_duplicate_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE quiz_id=%d", $quiz_id ) );
		$results = $wpdb->insert( 
				$table_name, 
				array( 
					'quiz_name' => $quiz_name,
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
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert('Your quiz has been duplicated successfully.', 'success');
			
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
			$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0011.', 'error');
		}
		if ($is_duplicating_questions)
		{
			$table_name = $wpdb->prefix."mlw_questions";
			$mlw_current_questions = $wpdb->get_results("SELECT * FROM $table_name WHERE deleted=0 AND quiz_id=".$quiz_id);
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
					$mlwQuizMasterNext->alertManager->newAlert('There has been an error in this action. Please share this with the developer. Error Code: 0020.', 'error');
				}
			}
		}
	 }
}
?>
