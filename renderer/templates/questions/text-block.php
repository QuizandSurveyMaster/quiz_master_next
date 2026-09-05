<?php
/**
 * Template for text or html type question
 *
 * This template can be overridden by copying it to yourpath/text-block.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Ensure question_settings is an array
if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';

qsm_question_title_func( $question['question_name'], '', $new_question_title, $id );

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output of a QSM extensibility filter; hooked add-ons return intended HTML/form markup, core default is an empty string
echo apply_filters( 'qmn_text_block_display_front', '', $id, $question, $answers );