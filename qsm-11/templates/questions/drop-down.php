<?php
/**
 * Template for multiple choice type question
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/multiple-choice.php
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

$required = isset( $question_settings['required'] ) ? $question_settings['required'] : 0;
if ( 0 == $required ) {
    $require_class = 'qsmRequiredSelect';
} else {
    $require_class = '';
}
$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
$question_text = isset( $question['question_name'] ) ? $question['question_name'] : '';
$processed_question_text = '';
if ( ! empty( $question_text ) ) {
    $processed_question_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( html_entity_decode( $question_text, ENT_HTML5 ), ENT_QUOTES ), "question-description-{$id}", "QSM Questions" );
}
$processed_question_text = apply_filters( 'qsm_question_title_function_before', $processed_question_text, $answers, $id );
$answer_limit       = isset( $question_settings['answer_limit'] ) ? $question_settings['answer_limit'] : '';
$limited_answers    = ! empty( $answer_limit ) ? $mlwQuizMasterNext->pluginHelper->qsm_get_limited_options( $answers, intval($answer_limit) ) : $answers;
$answers            = isset( $limited_answers['final'] ) ? $limited_answers['final'] : $answers;
$answer_limit_keys  = isset( $limited_answers['answer_limit_keys'] ) ? $limited_answers['answer_limit_keys'] : '';

$class_object->display_question_title( $question_text, '', $new_question_title, $id );

$show = true;

$show = apply_filters( 'qsm_check_show_answer_drop_down', $show, $id, $question, $answers );

if ( $show ) : ?>
<select class="qsm_select qsm_dropdown <?php echo esc_attr( $require_class ); ?>" name="question<?php echo esc_attr( $id ); ?>">
    <option disabled selected value><?php echo esc_html__( 'Please select your answer', 'quiz-master-next' ); ?></option>
    <?php
    if ( is_array( $answers ) ) {
        $mlw_answer_total = 0;
        foreach ( $answers as $answer_index => $answer ) {
            $mlw_answer_total++;
            if ( '' !== $answer[0] ) {
                $answer_text = trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
                $answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer_text, "answer-" . $id . "-" . $answer_index, "QSM Answers" );
                ?>
                <option value="<?php echo esc_attr( $answer_index ); ?>"><?php echo esc_html( $answer_text ); ?></option>
                <?php
            }
        }
    }
    ?>
</select>
<input type="hidden" name="answer_limit_keys_<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $answer_limit_keys ); ?>" />
<?php
endif;
echo apply_filters( 'qmn_drop_down_display_front', '', $id, $question, $answers );