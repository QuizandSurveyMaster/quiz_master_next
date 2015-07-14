<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the settings for questions tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_questions_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs(__("Questions", 'quiz-master-next'), 'mlw_options_questions_tab_content');
}
add_action("plugins_loaded", 'qmn_settings_questions_tab', 5);


/**
* Adds the content for the options for questions tab.
*
* @return void
* @since 4.4.0
*/
function mlw_options_questions_tab_content()
{
	?>
	<script>
		var answer_text = '<?php _e('Answer', 'quiz-master-next'); ?>';
	</script>
	<?php
	wp_enqueue_script('qmn_admin_question_js', plugins_url( '../js/admin_question.js' , __FILE__ ));
	wp_enqueue_script( 'math_jax', '//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];

	//Re-ordering questions
	if (isset($_POST['qmn_question_order_nonce']) && wp_verify_nonce( $_POST['qmn_question_order_nonce'], 'qmn_question_order')) {
		$list_of_questions = explode( ',', $_POST["save_question_order_input"] );
		$question_order = 0;
		$success = true;
		foreach( $list_of_questions as $id ) {
			$question_order++;
			$results = $wpdb->update(
				$wpdb->prefix . "mlw_questions",
				array(
					'question_order' => $question_order
				),
				array( 'question_id' => explode( '_', $id )[1] ),
				array(
					'%d'
				),
				array( '%d' )
			);
			if ( $results ) {
				$success = false;
			}
		}
		if ( ! $success ) {
			$mlwQuizMasterNext->alertManager->newAlert(__('The question order has been updated successfully.', 'quiz-master-next'), 'success');

			//Insert Action Into Audit Trail
			global $current_user;
			get_currentuserinfo();
			$results = $wpdb->insert(
			$wpdb->prefix . "mlw_qm_audit_trail",
				array(
					'action_user' => $current_user->display_name,
					'action' => "Question Order Has Been Updated On Quiz: $quiz_id",
					'time' => date("h:i:s A m/d/Y")
				),
				array(
					'%s',
					'%s',
					'%s'
				)
			);
		}
	}

	//Edit question
	if ( isset($_POST["question_submission"]) && $_POST["question_submission"] == "edit_question")
	{
		//Variables from edit question form
		$edit_question_name = trim(preg_replace('/\s+/',' ', nl2br(htmlspecialchars(stripslashes($_POST["question_name"]), ENT_QUOTES))));
		$edit_question_answer_info = htmlspecialchars(stripslashes($_POST["correct_answer_info"]), ENT_QUOTES);
		$mlw_edit_question_id = intval($_POST["question_id"]);
		$mlw_edit_question_type = $_POST["question_type"];
		$edit_comments = htmlspecialchars($_POST["comments"], ENT_QUOTES);
		$edit_hint = htmlspecialchars($_POST["hint"], ENT_QUOTES);
		$edit_question_order = intval($_POST["new_question_order"]);
		$mlw_edit_answer_total = intval($_POST["new_question_answer_total"]);

		if (isset($_POST["new_category"]))
		{
			$qmn_edit_category = $_POST["new_category"];
			if ($qmn_edit_category == 'new_category')
			{
				$qmn_edit_category = stripslashes($_POST["new_new_category"]);
			}
		}
		else
		{
			$qmn_edit_category = '';
		}
		$mlw_row_settings = $wpdb->get_row( $wpdb->prepare( "SELECT question_settings FROM " . $wpdb->prefix . "mlw_questions" . " WHERE question_id=%d", $mlw_edit_question_id ) );
		if (is_serialized($mlw_row_settings->question_settings) && is_array(@unserialize($mlw_row_settings->question_settings)))
		{
			$mlw_settings = @unserialize($mlw_row_settings->question_settings);
		}
		else
		{
			$mlw_settings = array();
			$mlw_settings['required'] = intval($_POST["required"]);
		}
		if ( !isset($mlw_settings['required']))
		{
			$mlw_settings['required'] = intval($_POST["required"]);
		}
		$mlw_settings['required'] = intval($_POST["required"]);
		$mlw_settings = serialize($mlw_settings);
		$i = 1;
		$mlw_qmn_new_answer_array = array();
		while ($i <= $mlw_edit_answer_total)
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

		$results = $wpdb->update(
			$wpdb->prefix . "mlw_questions",
			array(
				'question_name' => $edit_question_name,
				'answer_array' => $mlw_qmn_new_answer_array,
				'question_answer_info' => $edit_question_answer_info,
				'comments' => $edit_comments,
				'hints' => $edit_hint,
				'question_order' => $edit_question_order,
				'question_type_new' => $mlw_edit_question_type,
				'question_settings' => $mlw_settings,
				'category' => $qmn_edit_category
			),
			array( 'question_id' => $mlw_edit_question_id ),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s'
			),
			array( '%d' )
		);
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert(__('The question has been updated successfully.', 'quiz-master-next'), 'success');

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
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0004'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0004", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}
	//Delete question from quiz
	if ( isset($_POST["delete_question"]) && $_POST["delete_question"] == "confirmation")
	{
		//Variables from delete question form
		$mlw_question_id = intval($_POST["delete_question_id"]);
		$quiz_id = $_POST["quiz_id"];

		$update = "UPDATE " . $wpdb->prefix . "mlw_questions" . " SET deleted=1 WHERE question_id=".$mlw_question_id;
		$results = $wpdb->query( $update );
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert(__('The question has been deleted successfully.', 'quiz-master-next'), 'success');

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
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0005'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0005", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
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
							'question_type_new' => $mlw_original['question_type_new'],
							'question_settings' => $mlw_original['question_settings'],
							'category' => $mlw_original['category'],
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
							'%s',
							'%s',
							'%s',
							'%d'
						)
					);

		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert(__('The question has been duplicated successfully.', 'quiz-master-next'), 'success');

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
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0019'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 00019", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	//Submit new question into database
	if ( isset($_POST["question_submission"]) && $_POST["question_submission"] == "new_question")
	{
		//Variables from new question form
		$question_name = trim(preg_replace('/\s+/',' ', nl2br(htmlspecialchars(stripslashes($_POST["question_name"]), ENT_QUOTES))));
		$question_answer_info = htmlspecialchars(stripslashes($_POST["correct_answer_info"]), ENT_QUOTES);
		$question_type = $_POST["question_type"];
		$comments = htmlspecialchars($_POST["comments"], ENT_QUOTES);
		$hint = htmlspecialchars($_POST["hint"], ENT_QUOTES);
		$new_question_order = intval($_POST["new_question_order"]);
		$mlw_answer_total = intval($_POST["new_question_answer_total"]);

		if (isset($_POST['new_category']))
		{
			$qmn_category = $_POST["new_category"];
			if ($qmn_category == 'new_category')
			{
				$qmn_category = stripslashes($_POST["new_new_category"]);
			}
		}
		else
		{
			$qmn_category = '';
		}
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
		$results = $wpdb->insert(
						$wpdb->prefix."mlw_questions",
						array(
							'quiz_id' => $quiz_id,
							'question_name' => $question_name,
							'answer_array' => $mlw_qmn_new_answer_array,
							'question_answer_info' => $question_answer_info,
							'comments' => $comments,
							'hints' => $hint,
							'question_order' => $new_question_order,
							'question_type_new' => $question_type,
							'question_settings' => $mlw_settings,
							'category' => $qmn_category,
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
							'%s',
							'%s',
							'%d'
						)
					);
		if ($results != false)
		{
			$mlwQuizMasterNext->alertManager->newAlert(__('The question has been created successfully.', 'quiz-master-next'), 'success');

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
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0006'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0006", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}


	
	//Load questions
	$questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_questions WHERE quiz_id=%d AND deleted='0'
		ORDER BY question_order ASC", $quiz_id ) );
	$answers = array();
	foreach($questions as $mlw_question_info) {
		if (is_serialized($mlw_question_info->answer_array) && is_array(@unserialize($mlw_question_info->answer_array)))
		{
			$mlw_qmn_answer_array_each = @unserialize($mlw_question_info->answer_array);
			$answers[$mlw_question_info->question_id] = $mlw_qmn_answer_array_each;
		}
		else
		{
			$mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
			$mlw_answer_array_correct[$mlw_question_info->correct_answer-1] = 1;
			$answers[$mlw_question_info->question_id] = array(
				array($mlw_question_info->answer_one, $mlw_question_info->answer_one_points, $mlw_answer_array_correct[0]),
				array($mlw_question_info->answer_two, $mlw_question_info->answer_two_points, $mlw_answer_array_correct[1]),
				array($mlw_question_info->answer_three, $mlw_question_info->answer_three_points, $mlw_answer_array_correct[2]),
				array($mlw_question_info->answer_four, $mlw_question_info->answer_four_points, $mlw_answer_array_correct[3]),
				array($mlw_question_info->answer_five, $mlw_question_info->answer_five_points, $mlw_answer_array_correct[4]),
				array($mlw_question_info->answer_six, $mlw_question_info->answer_six_points, $mlw_answer_array_correct[5]));
		}
	}

	//Load Question Types
	$qmn_question_types = $mlwQuizMasterNext->pluginHelper->get_question_type_options();
	
	
	//Load question type edit fields and convert to JavaScript
	$qmn_question_type_fields = $mlwQuizMasterNext->pluginHelper->get_question_type_edit_fields();
	echo "<script>
		var qmn_question_type_fields = JSON.parse(".json_encode($qmn_question_type_fields).");
	</script>";

	echo "<script>
	var questions_list = [";
	foreach($questions as $question) {

		//Load Required
		if (is_serialized($mlw_question_info->question_settings) && is_array(@unserialize($mlw_question_info->question_settings)))
		{
			$mlw_question_settings = @unserialize($mlw_question_info->question_settings);
		}
		else
		{
			$mlw_question_settings = array();
			$mlw_question_settings['required'] = 1;
		}

		//Load Answers
		$answer_string = "";
		foreach($answers[$question->question_id] as $answer_single) {
			$answer_string .= "{answer: '".esc_js( str_replace('\\', '\\\\', $answer_single[0] ) )."',points: ".$answer_single[1].",correct: ".$answer_single[2]."},";
		}

		//Load Type
		$type_slug = $question->question_type_new;
		$type_name = $question->question_type_new;
		foreach($qmn_question_types as $type)
		{
			if ($type["slug"] == $question->question_type_new)
			{
				$type_name = $type["name"];
			}
		}

		//Parse Javascript Object
		echo "{
			id: ".$question->question_id.",
		  question: '".esc_js( str_replace('\\', '\\\\', $question->question_name ) )."',
		  answers: [".$answer_string."],
		  correct_info: '".esc_js( $question->question_answer_info )."',
		  hint: '".esc_js($question->hints, ENT_QUOTES)."',
		  type: '".$question->question_type_new."',
			type_name: '".$type_name."',
			comment: ".$question->comments.",
		  order: ".$question->question_order.",
		  required: ".$mlw_question_settings['required'].",
		  category: '".esc_js($question->category)."'
		},";
	}

	echo "];
	</script>";

	//Load Categories
	$qmn_quiz_categories = $wpdb->get_results( $wpdb->prepare( "SELECT category FROM " . $wpdb->prefix . "mlw_questions WHERE quiz_id=%d AND deleted='0'
		GROUP BY category", $quiz_id ) );

	$is_new_quiz = $wpdb->num_rows;
	?>
		<style>
			.edit_link,
			.duplicate_link {
				color: #0074a2 !important;
				font-size: 14px !important;
			}
			.delete_link {
				color: red !important;
				font-size: 14px !important;
			}
			.edit_link:hover,
			.duplicate_link:hover,
			.delete_link:hover {
				background-color: black;
			}
		</style>
		<button class="add-new-h2" id="new_question_button"><?php _e('Add Question', 'quiz-master-next'); ?></button>
		<button class="add-new-h2" id="save_question_order"><?php _e('Save Question Order', 'quiz-master-next'); ?></button>
		<form style="display:none;" action="" method="post" name="save_question_order_form" id="save_question_order_form">
			<input type="hidden" name="save_question_order_input" id="save_question_order_input" value="" />
			<?php wp_nonce_field('qmn_question_order','qmn_question_order_nonce'); ?>
		</form>
		<br />
		<p class="search-box">
			<label class="screen-reader-text" for="question_search">Search Questions:</label>
			<input type="search" id="question_search" name="question_search" value="">
			<a href="#" class="button">Search Questions</a>
		</p>
		<div class="tablenav top">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo sprintf(_n('One question', '%s questions', count($questions), 'quiz-master-next'), number_format_i18n(count($questions))); ?></span>
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e('Question Order', 'quiz-master-next'); ?></th>
					<th><?php _e('Question Type', 'quiz-master-next'); ?></th>
					<th><?php _e('Category', 'quiz-master-next'); ?></th>
					<th><?php _e('Question', 'quiz-master-next'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e('Question Order', 'quiz-master-next'); ?></th>
					<th><?php _e('Question Type', 'quiz-master-next'); ?></th>
					<th><?php _e('Category', 'quiz-master-next'); ?></th>
					<th><?php _e('Question', 'quiz-master-next'); ?></th>
				</tr>
			</tfoot>
			<tbody id="the-list">
			</tbody>
		</table>

		<style>
			.row:after,
			.answers:after,
			.answers_single:after {
				clear:both;
				display:table;
				content: " ";
			}
			.row {
				margin-bottom: 15px;
			}
			.row .option_label {
				width:15%;
				float:left;
				font-weight: bold;
			}
			.row .option_input {
				width:84%;
				float:left;
			}
			.question_form {

			}
			.question_form fieldset {
				margin: 20px 0px;
				background: #fff;
				padding: 5px;
			}
			.question_form legend {
				font-size: 16px;
				font-weight: bold;
				background: #f1f1f1;
				padding: 5px;
			}
			.answer_number,
			.answer_text,
			.answer_points,
			.answer_correct {
				font-weight: bold;
				float:left;
				margin: 1%;
			}
			.answer_number {
				width:10%;
			}
			.answer_text {
				width:60%;
			}
			.answer_points {
				width:10%;
			}
			.answer_correct {
				width:10%;
			}
			.answer_input {
				width: 100%;
			}
			.question_area_header_text {
				padding: 40px 0 0 !important;
			}
		</style>

		<div class="question_area" id="question_area">
			<h2 class="question_area_header_text">Add New Question</h2>
			<form action="" method="post" class="question_form">
				<fieldset>
					<legend>Question Type</legend>
					<div class="row">
						<label class="option_label"><?php _e('Question Type', 'quiz-master-next'); ?></label>
						<select class="option_input" name="question_type" id="question_type">
							<?php
							foreach($qmn_question_types as $type)
							{
								echo "<option value='".$type['slug']."'>".$type['name']."</option>";
							}
							?>
						</select>
					</div>
				</fieldset>
				<fieldset>
					<legend>Question And Answers</legend>
					<p id="question_type_info"></p>
					<?php wp_editor( '', "question_name" ); ?>

					<div id="answer_area">
						<div class="answer_headers">
							<div class="answer_number">&nbsp;</div>
							<div class="answer_text"><?php _e('Answers', 'quiz-master-next'); ?></div>
							<div class="answer_points"><?php _e('Points Worth', 'quiz-master-next'); ?></div>
							<div class="answer_correct"><?php _e('Correct Answer', 'quiz-master-next'); ?></div>
						</div>
						<div class="answers" id="answers">

						</div>
						<a href="#" class="button" id="new_answer_button"><?php _e('Add New Answer!', 'quiz-master-next'); ?></a>
					</div>
				</fieldset>
				<fieldset>
					<legend>Question Options</legend>
					<div id="correct_answer_area" class="row">
						<label class="option_label"><?php _e('Correct Answer Info', 'quiz-master-next'); ?></label>
						<input class="option_input" type="text" name="correct_answer_info" value="" id="correct_answer_info" />
					</div>

					<div id="hint_area" class="row">
						<label class="option_label"><?php _e('Hint', 'quiz-master-next'); ?></label>
						<input class="option_input" type="text" name="hint" value="" id="hint"/>
					</div>

					<div id="comment_area" class="row">
						<label class="option_label"><?php _e('Comment Field', 'quiz-master-next'); ?></label>
						<div class="option_input">
							<input type="radio" class="comments_radio" id="commentsRadio1" name="comments" value="0" /><label for="commentsRadio1"><?php _e('Small Text Field', 'quiz-master-next'); ?></label><br>
							<input type="radio" class="comments_radio" id="commentsRadio3" name="comments" value="2" /><label for="commentsRadio3"><?php _e('Large Text Field', 'quiz-master-next'); ?></label><br>
							<input type="radio" class="comments_radio" id="commentsRadio2" name="comments" checked="checked" value="1" /><label for="commentsRadio2"><?php _e('None', 'quiz-master-next'); ?></label><br>
						</div>
					</div>

					<div class="row">
						<label class="option_label"><?php _e('Question Order', 'quiz-master-next'); ?></label>
						<input class="option_input" type="number" step="1" min="1" name="new_question_order" value="<?php echo count($questions)+1; ?>" id="new_question_order"/>
					</div>

					<div id="required_area" class="row">
						<label class="option_label"><?php _e('Required?', 'quiz-master-next'); ?></label>
						<select class="option_input" name="required" id="required">
							<option value="0" selected="selected"><?php _e('Yes', 'quiz-master-next'); ?></option>
							<option value="1"><?php _e('No', 'quiz-master-next'); ?></option>
						</select>
					</div>

					<div id="category_area" class="row">
						<label class="option_label"><?php _e('Category', 'quiz-master-next'); ?></label>
						<div class="option_input">
							<?php
							foreach($qmn_quiz_categories as $category)
							{
								if ($category->category != '')
								{
									?>
									<input type="radio" class="category_radio" name="new_category" id="new_category<?php echo esc_attr($category->category); ?>" value="<?php echo esc_attr($category->category); ?>">
									<label for="new_category<?php echo esc_attr($category->category); ?>"><?php echo $category->category; ?></label>
									<br />
									<?php
								}
							}
							?>
							<input type="radio" name="new_category" id="new_category_new" value="new_category"><label for="new_category_new">New: <input type='text' name='new_new_category' value='' /></label>
						</div>
					</div>
				</fieldset>
				<input type="hidden" name="new_question_answer_total" id="new_question_answer_total" value="0" />
				<input type="hidden" id="question_submission" name="question_submission" value="new_question" />
				<input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>" />
				<input type="hidden" name="question_id" id="question_id" value="0" />
				<input type='submit' class='button-primary' value='<?php _e('Create Question', 'quiz-master-next'); ?>' />
			</form>
		</div>
		<!--Dialogs-->
		<div id="delete_dialog" title="Delete Question?" style="display:none;">
			<h3><b><?php _e('Are you sure you want to delete this question?', 'quiz-master-next'); ?></b></h3>
			<form action='' method='post'>
				<input type='hidden' name='delete_question' value='confirmation' />
				<input type='hidden' id='delete_question_id' name='delete_question_id' value='' />
				<input type='hidden' name='quiz_id' value='<?php echo $quiz_id; ?>' />
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e('Delete Question', 'quiz-master-next'); ?>' /></p>
			</form>
		</div>

		<div id="duplicate_dialog" title="Duplicate Question?" style="display:none;">
			<h3><b><?php _e('Are you sure you want to duplicate this question?', 'quiz-master-next'); ?></b></h3>
			<form action='' method='post'>
				<input type='hidden' name='duplicate_question' value='confirmation' />
				<input type='hidden' id='duplicate_question_id' name='duplicate_question_id' value='' />
				<input type='hidden' name='quiz_id' value='<?php echo $quiz_id; ?>' />
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e ('Duplicate Question', 'quiz-master-next'); ?>' /></p>
			</form>
		</div>
	<?php
}
?>
