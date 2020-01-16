<?php
/**
 * This file handles the "Questions" tab when editing a quiz/survey
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Adds the settings for questions tab to the Quiz Settings page.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_settings_questions_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Questions', 'quiz-master-next' ), 'qsm_options_questions_tab_content' );
}
add_action( 'plugins_loaded', 'qsm_settings_questions_tab', 5 );


/**
 * Adds the content for the options for questions tab.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_options_questions_tab_content() {

	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = intval( $_GET['quiz_id'] );
        $user_id = get_current_user_id();         
	$json_data = array(
		'quizID'     => $quiz_id,
		'answerText' => __( 'Answer', 'quiz-master-next' ),
		'nonce'      => wp_create_nonce( 'wp_rest' ),
		'pages'      => $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() ),
                'qsm_user_ve' => get_user_meta($user_id, 'rich_editing', true)
	);

	// Scripts and styles.
	wp_enqueue_script( 'micromodal_script', plugins_url( '../../js/micromodal.min.js', __FILE__ ) );
	wp_enqueue_script( 'qsm_admin_question_js', plugins_url( '../../js/qsm-admin-question.js', __FILE__ ), array( 'backbone', 'underscore', 'jquery-ui-sortable', 'wp-util', 'micromodal_script', 'qmn_admin_js' ), $mlwQuizMasterNext->version, true );
	wp_localize_script( 'qsm_admin_question_js', 'qsmQuestionSettings', $json_data );
	wp_enqueue_style( 'qsm_admin_question_css', plugins_url( '../../css/qsm-admin-question.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
	wp_enqueue_script( 'math_jax', '//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML' );
	wp_enqueue_editor();
	wp_enqueue_media();

	// Load Question Types.
	$question_types = $mlwQuizMasterNext->pluginHelper->get_question_type_options();

	// Display warning if using competing options.
	$pagination = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'pagination' );
	if ( 0 != $pagination ) {
		?>
		<div class="notice notice-warning">
			<p>This quiz has the "How many questions per page would you like?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to 0.</p>
		</div>
		<?php
	}
	$from_total = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'question_from_total' );
	if ( 0 != $from_total ) {
		?>
		<div class="notice notice-warning">
			<p>This quiz has the "How many questions should be loaded for quiz?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to 0.</p>
		</div>
		<?php
	}
	$randomness = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'randomness_order' );
	if ( 0 != $randomness ) {
		?>
		<div class="notice notice-warning">
			<p>This quiz has the "Are the questions random?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to "No".</p>
		</div>
		<?php
	}
	?>
	<h3>Questions</h3>
	<p>Use this tab to create and modify the different pages of your quiz or survey as well as the questions on each page. Click "Create New Page" to get started! Need more information? Check out the <a href="https://quizandsurveymaster.com/docs/creating-quizzes-and-surveys/adding-and-editing-questions/" target="_blank">documentation for this tab!</a></p>
	<div class="question-controls">
		<div>
			<button class="new-page-button button">Create New Page</button>
			<button class="save-page-button button-primary">Save Questions and Pages</button>
		</div>
		<span>Total Questions: <span id="total-questions"></span></span>
		<p class="search-box">
			<label class="screen-reader-text" for="question_search">Search Questions:</label>
			<input type="search" id="question_search" name="question_search" value="">
			<a href="#" class="button">Search Questions</a>
		</p>
	</div>
	<div class="questions"></div>

	<!-- Popup for question bank -->
	<div class="qsm-popup qsm-popup-slide qsm-popup-bank" id="modal-2" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-2-title">Add Question From Question Bank</h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-2-content">
					<input type="hidden" name="add-question-bank-page" id="add-question-bank-page" value="">
					<div id="question-bank"></div>
				</main>
				<footer class="qsm-popup__footer">
					<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window">Close</button>
				</footer>
			</div>
		</div>
	</div>


	<!-- Popup for editing question -->
	<div class="qsm-popup qsm-popup-slide" id="modal-1" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
                                    <h2 class="qsm-popup__title" id="modal-1-title">Edit Question [ ID: <span id="edit-question-id"></span>  ]</h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-1-content">
					<input type="hidden" name="edit_question_id" id="edit_question_id" value="">
					<div class="qsm-row">
						<label><?php _e( 'Question Type', 'quiz-master-next' ); ?></label>
						<select name="question_type" id="question_type">
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
						<label><?php _e( 'Answers', 'quiz-master-next' ); ?></label>
						<div class="correct-header"><?php _e( 'Correct', 'quiz-master-next' ); ?></div>
						<div class="answers" id="answers">

						</div>
						<a href="#" class="button" id="new-answer-button"><?php _e( 'Add New Answer!', 'quiz-master-next'); ?></a>
                                                <label class="change-answer-editor-label">
                                                    <?php _e( 'Answers Type: ', 'quiz-master-next' ); ?>
                                                    <select id="change-answer-editor">
                                                        <option value="text">Text Answers</option>
                                                        <option value="rich">Rich Answers</option>
                                                    </select>
                                                </label>                             
					</div>
                                        <p id="show-advanced-option">Show advance options &raquo;</p>
                                        <div class="advanced-content" style="display: none;">
                                            <div id="correct_answer_area" class="qsm-row">
						<label><?php _e( 'Correct Answer Info', 'quiz-master-next' ); ?></label>
						<input type="text" name="correct_answer_info" value="" id="correct_answer_info" />
                                            </div>
                                            <div id="hint_area" class="qsm-row">
                                                    <label><?php _e( 'Hint', 'quiz-master-next' ); ?></label>
                                                    <input type="text" name="hint" value="" id="hint"/>
                                            </div>
                                            <div id="comment_area" class="qsm-row">
                                                    <label><?php _e( 'Comment Field', 'quiz-master-next' ); ?></label>
                                                    <select name="comments" id="comments">
                                                            <option value="0"><?php _e('Small Text Field', 'quiz-master-next'); ?></option>
                                                            <option value="2"><?php _e('Large Text Field', 'quiz-master-next'); ?></option>
                                                            <option value="1" selected="selected"><?php _e('None', 'quiz-master-next'); ?></option>
                                                    <select>
                                            </div>
                                            <div id="required_area" class="qsm-row">
                                                    <label><?php _e( 'Required?', 'quiz-master-next' ); ?></label>
                                                    <select name="required" id="required">
                                                            <option value="0" selected="selected"><?php _e( 'Yes', 'quiz-master-next' ); ?></option>
                                                            <option value="1"><?php _e( 'No', 'quiz-master-next' ); ?></option>
                                                    </select>
                                            </div>
                                            <div id="category_area" class="qsm-row">
                                                    <label><?php _e( 'Category', 'quiz-master-next' ); ?></label>
                                                    <div id="categories">
                                                            <input type="radio" name="category" class="category-radio" id="new_category_new" value="new_category"><label for="new_category_new">New: <input type='text' id='new_category' value='' /></label>
                                                    </div>
                                            </div>
                                            <div id="required_area" class="qsm-row">
                                                    <label><?php _e( 'Hide Autofill?', 'quiz-master-next' ); ?></label>
                                                    <select name="hide_autofill" id="hide_autofill">
                                                            <option value="0" selected="selected"><?php _e( 'No', 'quiz-master-next' ); ?></option>
                                                            <option value="1"><?php _e( 'Yes', 'quiz-master-next' ); ?></option>
                                                    </select>
                                            </div>
                                            <div id="required_area" class="qsm-row">
                                                    <label><?php _e( 'Limit Text( Works for text fields )', 'quiz-master-next' ); ?></label>
                                                    <input type="number" name="limit_text" value="" id="limit_text"/>
                                            </div>
                                            <div id="required_area" class="qsm-row">
                                                    <label><?php _e( 'Limit Multiple choice( Works for Multiple Response )', 'quiz-master-next' ); ?></label>
                                                    <input type="number" name="limit_multiple_response" value="" id="limit_multiple_response"/>
                                            </div>
                                            <div id="file-upload-limit" class="qsm-row">
                                                    <label><?php _e( 'File upload limit ( in MB )', 'quiz-master-next' ); ?></label>
                                                    <input type="number" name="file_upload_limit" value="" id="file_upload_limit"/>
                                            </div>
                                            <div id="file-upload-type-div" class="qsm-row">
                                                <label><?php _e( 'Allow File type', 'quiz-master-next' ); ?></label>
                                                <input type="checkbox" name="file_upload_type[]" value="text/plain" /> <?php _e( 'Text File', 'quiz-master-next' ); ?><br/>
                                                <input type="checkbox" name="file_upload_type[]" value="image" /> <?php _e( 'Image', 'quiz-master-next' ); ?><br/>
                                                <input type="checkbox" name="file_upload_type[]" value="application/pdf" /> <?php _e( 'PDF', 'quiz-master-next' ); ?><br/>
                                                <input type="checkbox" name="file_upload_type[]" value="doc" /> <?php _e( 'Doc', 'quiz-master-next' ); ?><br/>
                                                <input type="checkbox" name="file_upload_type[]" value="excel" /> <?php _e( 'Excel', 'quiz-master-next' ); ?><br/>
                                                <input type="checkbox" name="file_upload_type[]" value="video/mp4" /> <?php _e( 'Video', 'quiz-master-next' ); ?>
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

	<!-- View for Page -->
	<script type="text/template" id="tmpl-page">
		<div class="page page-new">
			<div class="page-header">
				<div><span class="dashicons dashicons-move"></span></div>
				<div class="page-header-buttons">
					<a href="#" class="new-question-button button">Create New Question</a>
					<a href="#" class="add-question-bank-button button">Add Question From Question Bank</a>
				</div>
				<div><a href="#" class="delete-page-button"><span class="dashicons dashicons-trash"></span></a></div>
			</div>
		</div>
	</script>

	<!-- View for Question -->
	<script type="text/template" id="tmpl-question">
		<div class="question question-new" data-question-id="{{data.id }}">
			<div class="question-content">
				<div><span class="dashicons dashicons-move"></span></div>
				<div><a href="#" class="edit-question-button"><span class="dashicons dashicons-edit"></span></a></div>
				<div><a href="#" class="duplicate-question-button"><span class="dashicons dashicons-controls-repeat"></span></a></div>
				<div class="question-content-text">{{{data.question}}}</div>
				<div><# if ( 0 !== data.category.length ) { #> Category: {{data.category}} <# } #></div>
				<div><a href="#" class="delete-question-button"><span class="dashicons dashicons-trash"></span></a><div>
			</div>
		</div>
	</script>

	<!-- View for question in question bank -->
	<script type="text/template" id="tmpl-single-question-bank-question">
		<div class="question-bank-question" data-question-id="{{data.id}}" data-category-name="{{data.category}}">
                        <div><p>{{{data.question}}}</p><p style="font-size: 12px;color: gray;font-style: italic;"><b>Quiz Name:</b> {{data.quiz_name}}    <# if ( data.category != '' ) { #> <b>Category:</b> {{data.category}} <# } #></p>
                                </div>
			<div><a href="#" class="import-button button">Add Question</a></div>			
		</div>
	</script>

	<!-- View for single category -->
	<script type="text/template" id="tmpl-single-category">
		<div class="category">
			<input type="radio" name="category" class="category-radio" value="{{data.category}}"><label>{{data.category}}</label>
		</div>
	</script>

	<!-- View for single answer -->
	<script type="text/template" id="tmpl-single-answer">
		<div class="answers-single">
			<div><a href="#" class="delete-answer-button"><span class="dashicons dashicons-trash"></span></a></div>
			<div class="answer-text-div">
                            <# if ( 'rich' == data.answerType ) { #>
                                <textarea id="answer-{{data.question_id}}-{{data.count}}"></textarea>
                            <# } else { #>
                                <input type="text" class="answer-text" value="{{data.answer}}" placeholder="Your answer"/>
                            <# } #>                                                        
                        </div>
			<div><input type="text" class="answer-points" value="{{data.points}}" placeholder="Points"/></div>
			<div><input type="checkbox" class="answer-correct" value="1" <# if ( 1 == data.correct ) { #> checked="checked"/> <# } #></div>
		</div>
	</script>
	<?php
}


add_action( 'wp_ajax_qsm_save_pages', 'qsm_ajax_save_pages' );
//add_action( 'wp_ajax_nopriv_qsm_save_pages', 'qsm_ajax_save_pages' );


/**
 * Saves the pages and order from the Questions tab
 *
 * @since 5.2.0
 */
function qsm_ajax_save_pages() {
	global $mlwQuizMasterNext;
	$json = array(
		'status' => 'error',
	);
	$quiz_id = intval( $_POST['quiz_id'] );
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
	$response = $mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'pages', $_POST['pages'] );
	if ( $response ) {
		$json['status'] = 'success';
	}
	echo wp_json_encode( $json );
	wp_die();
}

add_action( 'wp_ajax_qsm_load_all_quiz_questions', 'qsm_load_all_quiz_questions_ajax' );
//add_action( 'wp_ajax_nopriv_qsm_load_all_quiz_questions', 'qsm_load_all_quiz_questions_ajax' );

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
	$questions = $wpdb->get_results( "SELECT question_id, question_name FROM {$wpdb->prefix}mlw_questions WHERE deleted = '0' ORDER BY question_id DESC" );

	// Creates question array.
	$question_json = array();
	foreach ( $questions as $question ) {
		$question_json[] = array(
			'id'       => $question->question_id,
			'question' => $question->question_name,
		);
	}

	// Echos JSON and dies.
	echo wp_json_encode( $question_json );
	wp_die();
}

add_action( 'wp_ajax_qsm_send_data_sendy', 'qsm_send_data_sendy' );
//add_action( 'wp_ajax_nopriv_qsm_send_data_sendy', 'qsm_send_data_sendy' );

/**
 * @version 6.3.2
 * Send data to sendy
 */
function qsm_send_data_sendy(){
    $sendy_url = 'http://sendy.expresstech.io';
    $list = '4v8zvoyXyTHSS80jeavOpg';
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    //subscribe
    $postdata = http_build_query(
        array(
        'name' => $name,
        'email' => $email,
        'list' => $list,
        'boolean' => 'true'
        )
    );
    $opts = array('http' => array('method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata));
    $context  = stream_context_create($opts);    
    $result = wp_remote_post($sendy_url.'/subscribe', [ 'body' => array(
        'name' => $name,
        'email' => $email,
        'list' => $list,
        'boolean' => 'true'
        ) ] );
    echo isset($result['response']) && isset($result['response']['code']) && $result['response']['code'] == 200 ? $result['body'] : '';
    exit;
}
?>
