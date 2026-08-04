<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/**
 * This function displays the contents of the text block question type.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_text_block_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
    $new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
    qsm_question_title_func( $question, '', $new_question_title, $id );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output of a QSM extensibility filter; hooked add-ons return intended HTML/form markup, core default is an empty string
	echo apply_filters( 'qmn_text_block_display_front', '', $id, $question, $answers );
}