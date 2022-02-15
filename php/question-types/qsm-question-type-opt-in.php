<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function displays the accept question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_accept_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredAccept';
	} else {
		$mlw_require_class = '';
	}
	?>
	<div class="qmn_accept_answers">
		<input type="checkbox" id="mlwAcceptance" class="<?php echo esc_attr( $mlw_require_class ); ?>" />
		<label class="qsm-input-label" for="mlwAcceptance"><span class="qmn_accept_text"><?php echo wp_kses_post( do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) ); ?></span></label>
	</div>
	<?php
	echo apply_filters( 'qmn_accept_display_front', '', $id, $question, $answers );
}