<?php
/**
 * Template for short answer type question
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/short-answer.php
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

// Get question settings
$required           = isset( $question_settings['required'] ) ? $question_settings['required'] : 0;
$autofill           = isset( $question_settings['autofill'] ) ? $question_settings['autofill'] : 0;
$limit_text         = isset( $question_settings['limit_text'] ) ? $question_settings['limit_text'] : '';
$min_text_length    = isset( $question_settings['min_text_length'] ) ? $question_settings['min_text_length'] : '';
$placeholder_text   = isset( $question_settings['placeholder_text'] ) ? $question_settings['placeholder_text'] : '';
$autofill_att       = $autofill ? "autocomplete='off' " : '';
$min_text_attr      = $min_text_length ? "minlength=" . $min_text_length . "" : '';

// Get required class
$mlw_require_class  = 0 == $required ? 'mlwRequiredText' : '';

// Get question title
$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
$class_object->display_question_title( $question['question_name'], '', $new_question_title, $id );
?>
<input <?php echo esc_attr( $autofill_att ); ?> type="text" class="mlw_answer_open_text <?php echo esc_attr( $mlw_require_class ); ?>" id="question<?php echo esc_attr( $id ); ?>" name="question<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( $min_text_attr ); ?> <?php if ( $limit_text ) : ?>maxlength="<?php echo esc_attr( $limit_text ); ?>"<?php endif; ?> Placeholder="<?php echo esc_attr($placeholder_text); ?>" />
<?php
echo apply_filters( 'qmn_small_open_display_front', '', $id, $question, $answers );