<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
/**
 * This function displays the captcha question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_captcha_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredCaptcha';
	} else {
		$mlw_require_class = '';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	?>
	<span class="mlw_qmn_question">
	<?php qsm_question_title_func( $question, '', $new_question_title, $id ); ?>
	</span>
	<div class="mlw_captchaWrap">
		<canvas alt="" id="mlw_captcha" class="mlw_captcha" width="100" height="50"></canvas>
	</div>
	<input type="text" class="mlw_answer_open_text <?php echo esc_attr( $mlw_require_class ); ?>" id="mlw_captcha_text" name="mlw_user_captcha"/>
	<input type="hidden" name="mlw_code_captcha" id="mlw_code_captcha" value="none" />
	<?php
	echo apply_filters( 'qmn_captcha_display_front', '', $id, $question, $answers );
}