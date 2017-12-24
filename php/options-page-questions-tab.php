<?php
/**
 * This file handles the "Questions" tab when editing a quiz/survey
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds the settings for questions tab to the Quiz Settings page.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_settings_questions_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( "Questions", 'quiz-master-next' ), 'qsm_options_questions_tab_content' );
}
add_action( "plugins_loaded", 'qsm_settings_questions_tab', 5 );


/**
 * Adds the content for the options for questions tab.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_options_questions_tab_content() {

	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = intval( $_GET["quiz_id"] );

	$json_data = array(
		'quizID'     => $quiz_id,
		'answerText' => __( 'Answer', 'quiz-master-next' ),
		'nonce' => wp_create_nonce( 'wp_rest' ),
	);

	// Scripts and styles.
	wp_enqueue_script( 'micromodal_script', plugins_url( '../js/micromodal.min.js' , __FILE__ ) );
	wp_enqueue_script( 'qsm_admin_question_js', plugins_url( '../js/qsm-admin-question.js', __FILE__ ), array( 'underscore', 'jquery-ui-sortable', 'wp-util', 'micromodal_script' ), $mlwQuizMasterNext->version, true );
	wp_localize_script( 'qsm_admin_question_js', 'qsmQuestionSettings', $json_data );
	wp_enqueue_style( 'qsm_admin_question_css', plugins_url( '../css/qsm-admin-question.css', __FILE__ ) );
	wp_enqueue_script( 'math_jax', '//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML' );
	wp_enqueue_editor();

	// Load Question Types.
	$question_types = $mlwQuizMasterNext->pluginHelper->get_question_type_options();
	?>
	<div class="questions-message"></div>
	<a href="#" class="button-primary">Save Questions</a>
	<div class="questions">

	</div>
	<a href="#" class="new-page-button button">Create New Page</a>
	<a href="#" class="button-primary">Save Questions</a>

	<!-- Popup for editing question -->
	<div class="qsm-popup qsm-popup-slide" id="modal-1" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-1-title">Edit Question</h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-1-content">
					<div class="qsm-row">
						<select class="option_input" name="question_type" id="question_type">
							<?php
							foreach ( $question_types as $type ) {
								echo "<option value='{$type['slug']}'>{$type['name']}</option>";
							}
							?>
						</select>
					</div>
					<p id="question_type_info"></p>
					<div class="qsm-row">
						<textarea id="question-text"></textarea>
					</div>
					<div class="qsm-row">
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
					<div id="correct_answer_area" class="qsm-row">
						<label class="option_label"><?php _e('Correct Answer Info', 'quiz-master-next'); ?></label>
						<input class="option_input" type="text" name="correct_answer_info" value="" id="correct_answer_info" />
					</div>
					<div id="hint_area" class="qsm-row">
						<label class="option_label"><?php _e('Hint', 'quiz-master-next'); ?></label>
						<input class="option_input" type="text" name="hint" value="" id="hint"/>
					</div>
					<div id="comment_area" class="qsm-row">
						<label class="option_label"><?php _e('Comment Field', 'quiz-master-next'); ?></label>
						<div class="option_input">
							<input type="radio" class="comments_radio" id="commentsRadio1" name="comments" value="0" /><label for="commentsRadio1"><?php _e('Small Text Field', 'quiz-master-next'); ?></label><br>
							<input type="radio" class="comments_radio" id="commentsRadio3" name="comments" value="2" /><label for="commentsRadio3"><?php _e('Large Text Field', 'quiz-master-next'); ?></label><br>
							<input type="radio" class="comments_radio" id="commentsRadio2" name="comments" checked="checked" value="1" /><label for="commentsRadio2"><?php _e('None', 'quiz-master-next'); ?></label><br>
						</div>
					</div>
					<div id="required_area" class="qsm-row">
						<label class="option_label"><?php _e('Required?', 'quiz-master-next'); ?></label>
						<select class="option_input" name="required" id="required">
							<option value="0" selected="selected"><?php _e('Yes', 'quiz-master-next'); ?></option>
							<option value="1"><?php _e('No', 'quiz-master-next'); ?></option>
						</select>
					</div>
					<div id="category_area" class="qsm-row">
						<label class="option_label"><?php _e('Category', 'quiz-master-next'); ?></label>
						<div class="option_input">
							<input type="radio" name="new_category" id="new_category_new" value="new_category"><label for="new_category_new">New: <input type='text' name='new_new_category' value='' /></label>
						</div>
					</div>
				</main>
				<footer class="qsm-popup__footer">
					<button id="save-popup-button" class="qsm-popup__btn qsm-popup__btn-primary">Save Question</button>
					<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window">Cancel</button>
				</footer>
			</div>
		</div>
	</div>

	<!--Views-->

	<!-- View for Notices -->
	<script type="text/template" id="tmpl-notice">
		<div class="notice notice-{{data.type}}">
			<p>{{data.msg}}</p>
		</div>
	</script>

	<!-- View for Page -->
	<script type="text/template" id="tmpl-page">
		<div class="page page-new">
			<a href="#" class="new-question-button button">Create New Question</a>
		</div>
	</script>

	<!-- View for Question -->
	<script type="text/template" id="tmpl-question">
		<div class="question question-new">
			{{data.id }} | {{data.question}} | {{data.type}} | {{data.category}} | 
		</div>
	</script>

	<!-- View for single answer -->
	<script type="text/template" id="tmpl-single-answer">
		<div class="answers_single">
			<div class="answer_number"><button class="button delete_answer">Delete</button> '+answer_text+'</div>'+
			<div class="answer_text"><input type="text" class="answer_input" name="answer_'+total_answers+'" id="answer_'+total_answers+'" value="'+answer+'" /></div>'+
			<div class="answer_points"><input type="text" class="answer_input" name="answer_'+total_answers+'_points" id="answer_'+total_answers+'_points" value="'+points+'" /></div>'+
			<div class="answer_correct"><input type="checkbox" id="answer_'+total_answers+'_correct" name="answer_'+total_answers+'_correct"'+correct_text+' value=1 /></div>'+
		</div>
	</script>
	<?php
}

add_action( 'wp_ajax_qsm_load_all_quiz_questions', 'qsm_load_all_quiz_questions_ajax' );
add_action( 'wp_ajax_nopriv_qsm_load_all_quiz_questions', 'qsm_load_all_quiz_questions_ajax' );

/**
 * Loads all the questions and echos out JSON
 *
 * @since 0.1.0
 * @return void
 */
function qsm_load_all_quiz_questions_ajax() {
	global $wpdb;
	global $mlwQuizMasterNext;

	// Loads questions.
	$questions = $wpdb->get_results( "SELECT {$wpdb->prefix}mlw_questions.question_id, {$wpdb->prefix}mlw_questions.question_name, {$wpdb->prefix}mlw_quizzes.quiz_name FROM {$wpdb->prefix}mlw_questions
		LEFT JOIN {$wpdb->prefix}mlw_quizzes ON {$wpdb->prefix}mlw_questions.quiz_id={$wpdb->prefix}mlw_quizzes.quiz_id WHERE {$wpdb->prefix}mlw_questions.deleted='0' ORDER BY {$wpdb->prefix}mlw_questions.question_id DESC" );

	// Creates question array.
	$question_json = array();
	foreach ( $questions as $question ) {
		$question_json[] = array(
			'id'       => $question->question_id,
			'question' => $question->question_name,
			'quiz'     => $question->quiz_name
		);
	}

	// Echos JSON and dies.
	echo json_encode( $question_json );
	die();
}

?>
