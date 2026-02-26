<?php
/**
 * Template for date type question
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/date.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $mlwQuizMasterNext, $qmn_total_questions;

// Extract variables passed to template
extract( $args );

// Ensure question_settings is an array
if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

// Get question settings
$required = isset( $question_settings['required'] ) ? $question_settings['required'] : 0;
$mlw_require_class = 0 == $required ? 'mlwRequiredDate' : '';
$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
$class_object->display_question_title( $question['question_name'], '', $new_question_title, $id );
?>
<input type="date" class="mlw_answer_date <?php echo esc_attr( $mlw_require_class ); ?>" name="question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ); ?>" value="" />
<?php
echo apply_filters( 'qmn_date_display_front', '', $id, $question, $answers );