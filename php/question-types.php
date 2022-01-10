<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//
include 'question-types/qsm-question-type-multiple-choice.php';
add_action( 'plugins_loaded', 'qmn_question_type_multiple_choice' );
//
include 'question-types/qsm-question-type-multiple-choice-horizontal.php';
add_action( 'plugins_loaded', 'qmn_question_type_horizontal_multiple_choice' );
//
include 'question-types/qsm-question-type-multiple-response.php';
add_action( 'plugins_loaded', 'qmn_question_type_multiple_response' );
//
include 'question-types/qsm-question-type-multiple-response-horizontal.php';
add_action( 'plugins_loaded', 'qmn_question_type_horizontal_multiple_response' );
//
include 'question-types/qsm-question-type-dropdown.php';
add_action( 'plugins_loaded', 'qmn_question_type_drop_down' );
//
include 'question-types/qsm-question-type-fill-in-the-blanks.php';
add_action( 'plugins_loaded', 'qmn_question_type_fill_blank' );
//
include 'question-types/qsm-question-type-file-upload.php';
add_action( 'plugins_loaded', 'qmn_question_type_file_upload' );
//
include 'question-types/qsm-question-type-date.php';
add_action( 'plugins_loaded', 'qmn_question_type_date' );
//
include 'question-types/qsm-question-type-short-answer.php';
add_action( 'plugins_loaded', 'qmn_question_type_small_open' );
//
include 'question-types/qsm-question-type-paragraph.php';
add_action( 'plugins_loaded', 'qmn_question_type_large_open' );
//
include 'question-types/qsm-question-type-text-or-html.php';
add_action( 'plugins_loaded', 'qmn_question_type_text_block' );
//
include 'question-types/qsm-question-type-number.php';
add_action( 'plugins_loaded', 'qmn_question_type_number' );
//
include 'question-types/qsm-question-type-polar.php';
add_action( 'plugins_loaded', 'qmn_question_type_polar' );
//
include 'question-types/qsm-question-type-opt-in.php';
add_action( 'plugins_loaded', 'qmn_question_type_accept' );
//
include 'question-types/qsm-question-type-captcha.php';
add_action( 'plugins_loaded', 'qmn_question_type_captcha' );

/**
 * Registers the multiple choice type
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_multiple_choice() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Vertical Multiple Choice', 'quiz-master-next' ), 'qmn_multiple_choice_display', true, 'qmn_multiple_choice_review', null, null, 0 );
}

/**
 * Registers the file upload type
 *
 * @return void
 * @since  6.3.7
 */
function qmn_question_type_file_upload() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'File Upload', 'quiz-master-next' ), 'qmn_file_upload_display', true, 'qmn_file_upload_review', null, null, 11 );
}

/**
 * Registers the date type
 *
 * @return void
 * @since  6.3.7
 */
function qmn_question_type_date() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Date', 'quiz-master-next' ), 'qmn_date_display', true, 'qmn_date_review', null, null, 12 );
}

/**
 * This function registers the horizontal multiple choice type.
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_horizontal_multiple_choice() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Horizontal Multiple Choice', 'quiz-master-next' ), 'qmn_horizontal_multiple_choice_display', true, 'qmn_horizontal_multiple_choice_review', null, null, 1 );
}

/**
 * This function registers the drop down question type
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_drop_down() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Drop Down', 'quiz-master-next' ), 'qmn_drop_down_display', true, 'qmn_drop_down_review', null, null, 2 );
}

/**
 * This function registers the small open question type
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_small_open() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Short Answer', 'quiz-master-next' ), 'qmn_small_open_display', true, 'qmn_small_open_review', null, null, 3 );
}

/**
 * This function registers the multiple response question type
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_multiple_response() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Multiple Response', 'quiz-master-next' ), 'qmn_multiple_response_display', true, 'qmn_multiple_response_review', null, null, 4 );
}

/**
 * This function registers the large open question type.
 *
 * @since 4.4.0
 */
function qmn_question_type_large_open() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Paragraph', 'quiz-master-next' ), 'qmn_large_open_display', true, 'qmn_large_open_review', null, null, 5 );
}

/**
 * This function registers the text block question type
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_text_block() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
		),
		'information'  => '',
		'extra_inputs' => array(),
		'function'     => '',
	);
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Text/HTML Section', 'quiz-master-next' ), 'qmn_text_block_display', false, null, $edit_args, null, 6 );
}


/**
 * This function registers the number question type
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_number() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Number', 'quiz-master-next' ), 'qmn_number_display', true, 'qmn_number_review', null, null, 7 );
}

/**
 * This function registers the accept question type.
 *
 * @return void Description
 * @since  4.4.0
 */
function qmn_question_type_accept() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
			'required',
		),
		'information'  => '',
		'extra_inputs' => array(),
		'function'     => '',
	);
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Opt-in', 'quiz-master-next' ), 'qmn_accept_display', false, null, $edit_args, null, 8 );
}

/**
 * This function registers the captcha question
 *
 * @since 4.4.0
 */
function qmn_question_type_captcha() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
			'required',
		),
		'information'  => '',
		'extra_inputs' => array(),
		'function'     => '',
	);
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Captcha', 'quiz-master-next' ), 'qmn_captcha_display', false, null, $edit_args, null, 9 );
}

/**
 * This function registers the horizontal multiple response question
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_horizontal_multiple_response() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Horizontal Multiple Response', 'quiz-master-next' ), 'qmn_horizontal_multiple_response_display', true, 'qmn_horizontal_multiple_response_review', null, null, 10 );
}

/**
 * This function registers the fill in the blank question type
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_fill_blank() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
			'answer',
			'hint',
			'correct_info',
			'comments',
			'category',
			'required',
		),
		'information'  => __( 'For fill in the blank types, use %BLANK% to represent where to put the text box in your text.', 'quiz-master-next' ),
		'extra_inputs' => array(),
		'function'     => '',
	);

	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Fill In The Blank', 'quiz-master-next' ), 'qmn_fill_blank_display', true, 'qmn_fill_blank_review', $edit_args, null, 14 );

}

/**
 * This function registers the polar question type
 *
 * @return void
 * @since  6.4.1
 */
function qmn_question_type_polar() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Polar', 'quiz-master-next' ), 'qmn_polar_display', true, 'qmn_polar_review', null, null, 13 );
}

function qsm_question_title_func( $question, $question_type = '', $new_question_title = '', $question_id = 0 ) {
	// $question_title = apply_filters('the_content', $question);
	$question_title = $question;
	global $wp_embed, $mlwQuizMasterNext;
	$question_title    = $wp_embed->run_shortcode( $question_title );
	$question_title    = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $question_title );
	$polar_extra_class = '';
	if ( 'polar' === $question_type ) {
		$polar_extra_class = 'question-type-polar-s';
	}
	$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
	$deselect_answer  = '';
	if ( isset( $qmn_quiz_options->enable_deselect_option ) && 1 == $qmn_quiz_options->enable_deselect_option && ( 'multiple_choice' === $question_type || 'horizontal_multiple_choice' === $question_type ) ) {
		$deselect_answer = '<a href="#" class="qsm-deselect-answer">Deselect Answer</a>';
	}

	if ( $question_id ) {
		$featureImageID = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'featureImageID' );
		if ( $featureImageID ) {
			?>
			<div class="qsm-featured-image"><?php echo wp_get_attachment_image( $featureImageID, apply_filters( 'qsm_filter_feature_image_size', 'full', $question_id ) ); ?></div>
			<?php
		}
	}
	if ( '' !== $new_question_title ) {
		?>
		<div class='mlw_qmn_new_question'><?php echo esc_html( htmlspecialchars_decode( $new_question_title, ENT_QUOTES ) ); ?> </div>
		<?php
		$polar_extra_class .= ' qsm_remove_bold';
	}

	?>
	<div class='mlw_qmn_question <?php echo esc_attr( $polar_extra_class ); ?>' >
	<?php echo do_shortcode( htmlspecialchars_decode( $question_title, ENT_QUOTES ) . $deselect_answer ); ?>
	</div>
	<?php
}