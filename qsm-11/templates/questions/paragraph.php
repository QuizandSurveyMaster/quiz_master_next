<?php
/**
 * Template for paragraph type question
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/paragraph.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $mlwQuizMasterNext;

// Extract variables passed to template
extract( $args );

// Ensure question_settings is an array
if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

$required   = isset( $question_settings['required'] ) ? $question_settings['required'] : 0;
$limit_text = isset( $question_settings['limit_text'] ) ? $question_settings['limit_text'] : '';
$min_length = isset( $question_settings['min_text_length'] ) ? $question_settings['min_text_length'] : '';
$placeholder_text = isset( $question_settings['placeholder_text'] ) ? $question_settings['placeholder_text'] : '';
$min_length_attr    = $min_length ? "minlength=" . $min_length . "" : '';
if ( 0 == $required ) {
    $mlw_require_class = 'mlwRequiredText';
} else {
    $mlw_require_class = '';
}
$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
$class_object->display_question_title( $question['question_name'], '', $new_question_title, $id );
?>
<textarea class="mlw_answer_open_text <?php echo esc_attr( $mlw_require_class ); ?>" cols="70" rows="5" id="question<?php echo esc_attr( $id ); ?>" name="question<?php echo esc_attr( $id ); ?>" <?php if ( $limit_text ) : ?>maxlength="<?php echo esc_attr( $limit_text ); ?>"<?php endif; ?> <?php echo esc_attr( $min_length_attr ); ?> placeholder="<?php echo esc_attr( $placeholder_text ); ?>" /></textarea>
<?php
echo apply_filters( 'qmn_large_open_display_front', '', $id, $question, $answers );