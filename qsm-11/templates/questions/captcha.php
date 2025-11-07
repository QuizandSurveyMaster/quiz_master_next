<?php
/**
 * Template for captcha type question
 *
 * This template can be overridden by copying it to yourpath/captcha.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extract( $args );

if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

$required = isset( $question_settings['required'] ) ? $question_settings['required'] : '';
$mlw_require_class = $required == 0 ? 'mlwRequiredCaptcha' : '';
$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
?>
<span class="mlw_qmn_question">
<?php qsm_question_title_func( $question['question_name'], '', $new_question_title, $id ); ?>
</span>
<div class="mlw_captchaWrap">
    <canvas alt="" id="mlw_captcha" class="mlw_captcha" width="100" height="50"></canvas>
</div>
<input type="text" class="mlw_answer_open_text <?php echo esc_attr( $mlw_require_class ); ?>" id="mlw_captcha_text" name="mlw_user_captcha"/>
<input type="hidden" name="mlw_code_captcha" id="mlw_code_captcha" value="none" />
<?php
echo apply_filters( 'qmn_captcha_display_front', '', $id, $question, $answers );