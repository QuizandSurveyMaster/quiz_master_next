<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once 'question-types/qsm-question-type-multiple-choice.php';
include_once 'question-types/qsm-question-type-multiple-response.php';
include_once 'question-types/qsm-question-type-multiple-choice-horizontal.php';
include_once 'question-types/qsm-question-type-multiple-response-horizontal.php';
include_once 'question-types/qsm-question-type-dropdown.php';
include_once 'question-types/qsm-question-type-polar.php';
include_once 'question-types/qsm-question-type-opt-in.php';
include_once 'question-types/qsm-question-type-date.php';
include_once 'question-types/qsm-question-type-number.php';
include_once 'question-types/qsm-question-type-short-answer.php';
include_once 'question-types/qsm-question-type-paragraph.php';
include_once 'question-types/qsm-question-type-fill-in-the-blanks.php';
include_once 'question-types/qsm-question-type-file-upload.php';
include_once 'question-types/qsm-question-type-text-or-html.php';
include_once 'question-types/qsm-question-type-captcha.php';
include_once 'question-types/qsm-question-title.php';
include_once 'classes/question-types/class-question-review.php';
include_once 'classes/question-types/class-question-review-text.php';
include_once 'classes/question-types/class-question-review-fill-in-blanks.php';
include_once 'classes/question-types/class-question-review-file-upload.php';
include_once 'classes/question-types/class-question-review-choice.php';

add_action( 'plugins_loaded', 'qmn_question_type_multiple_choice' );
add_action( 'plugins_loaded', 'qmn_question_type_multiple_response' );
add_action( 'plugins_loaded', 'qmn_question_type_horizontal_multiple_choice' );
add_action( 'plugins_loaded', 'qmn_question_type_horizontal_multiple_response' );
add_action( 'plugins_loaded', 'qmn_question_type_drop_down' );
add_action( 'plugins_loaded', 'qmn_question_type_polar' );
add_action( 'plugins_loaded', 'qmn_question_type_accept' );
add_action( 'plugins_loaded', 'qmn_question_type_date' );
add_action( 'plugins_loaded', 'qmn_question_type_number' );
add_action( 'plugins_loaded', 'qmn_question_type_small_open' );
add_action( 'plugins_loaded', 'qmn_question_type_large_open' );
add_action( 'plugins_loaded', 'qmn_question_type_fill_blank' );
add_action( 'plugins_loaded', 'qmn_question_type_file_upload' );
add_action( 'plugins_loaded', 'qmn_question_type_text_block' );
add_action( 'plugins_loaded', 'qmn_question_type_captcha' );

/**
 * Registers the multiple choice type
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_multiple_choice() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Multiple Choice', 'quiz-master-next' ), 'qmn_multiple_choice_display', true, 'qmn_multiple_choice_review', null, null, 0 );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 0, 'input_field', 'radio' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 0, 'category', 'Choice' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 4, 'input_field', 'checkbox' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 4, 'category', 'Choice' );
}

/**
 * This function registers the horizontal multiple choice type.
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_horizontal_multiple_choice() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Multiple Choice (Horizontal)', 'quiz-master-next' ), 'qmn_horizontal_multiple_choice_display', true, 'qmn_horizontal_multiple_choice_review', null, null, 1 );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 1, 'input_field', 'radio' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 1, 'category', 'Choice' );
}

/**
 * This function registers the horizontal multiple response question
 *
 * @return void
 * @since  4.4.0
 */
function qmn_question_type_horizontal_multiple_response() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Multiple Response (Horizontal)', 'quiz-master-next' ), 'qmn_horizontal_multiple_response_display', true, 'qmn_horizontal_multiple_response_review', null, null, 10 );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 10, 'input_field', 'checkbox' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 10, 'category', 'Choice' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 2, 'input_field', 'select' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 2, 'category', 'Choice' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 13, 'input_field', 'slider' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 13, 'category', 'Choice' );
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
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Opt-in', 'quiz-master-next' ), 'qmn_accept_display', false, 'qmn_opt_in_review', $edit_args, null, 8 );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 8, 'input_field', 'Checkbox' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 8, 'category', 'Choice' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 12, 'input_field', 'text' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 12, 'category', 'Number' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 7, 'input_field', 'text' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 7, 'category', 'Number' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 3, 'input_field', 'text' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 3, 'category', 'Text' );
}

/**
 * This function registers the large open question type.
 *
 * @since 4.4.0
 */
function qmn_question_type_large_open() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Paragraph', 'quiz-master-next' ), 'qmn_large_open_display', true, 'qmn_large_open_review', null, null, 5 );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 5, 'input_field', 'text_area' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 5, 'category', 'Text' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 14, 'input_field', 'text' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 14, 'category', 'Others' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 11, 'input_field', 'attachment' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 11, 'category', 'Others' );
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
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Text/HTML Section', 'quiz-master-next' ), 'qmn_text_block_display', false, 'qsm_text_html_review', $edit_args, null, 6 );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 6, 'input_field', 'NA' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 6, 'category', 'Others' );
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
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 9, 'input_field', 'NA' );
	$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 9, 'category', 'Others' );
}


add_action( 'plugins_loaded', 'qmn_extra_question_types' );
function qmn_extra_question_types() {
	global $mlwQuizMasterNext;
	if ( ! class_exists( 'QSM_Advance_Question' ) ) {
		$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Matching Pairs', 'quiz-master-next' ), '-', false, null, null, null, 15 );
		$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 15, 'category', 'Advanced (PRO)' );
		$mlwQuizMasterNext->pluginHelper->register_question_type(__('Radio Grid', 'quiz-master-next'), '-', false, null, null, null, 16);
		$mlwQuizMasterNext->pluginHelper->set_question_type_meta( 16, 'category', 'Advanced (PRO)' );
		$mlwQuizMasterNext->pluginHelper->register_question_type(__('Checkbox Grid', 'quiz-master-next'), '-', false, null, null, null, 17);
		$mlwQuizMasterNext->pluginHelper->set_question_type_meta(17, 'category', 'Advanced (PRO)');
	}
}