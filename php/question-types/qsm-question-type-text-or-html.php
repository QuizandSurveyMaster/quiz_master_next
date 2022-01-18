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
	echo wp_kses_post( do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
}