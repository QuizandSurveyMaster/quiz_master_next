<?php
/**
 * Template for fill-in-the-blank type question
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/fill-in-the-blank.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $mlwQuizMasterNext, $allowedposttags;

// Extract variables passed to template
extract( $args );

// Ensure question_settings is an array
if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

$allowedposttags['input']    = array(
    'autocomplete' => true,
    'class'        => true,
    'id'           => true,
    'height'       => true,
    'min'          => true,
    'max'          => true,
    'minlength'    => true,
    'maxlength'    => true,
    'name'         => true,
    'pattern'      => true,
    'placeholder'  => true,
    'readonly'     => true,
    'required'     => true,
    'size'         => true,
    'step'         => true,
    'type'         => true,
    'value'        => true,
    'width'        => true,
);
// Get question settings
$required                    = isset( $question_settings['required'] ) ? $question_settings['required'] : 0;
$autofill                    = isset( $question_settings['autofill'] ) ? $question_settings['autofill'] : 0;
$limit_text                  = isset( $question_settings['limit_text'] ) ? $question_settings['limit_text'] : '';
$min_fill_text               = isset( $question_settings['min_text_length'] ) ? $question_settings['min_text_length'] : '';
$autofill_att                = $autofill ? "autocomplete='off' " : '';
$limit_text_att              = $limit_text ? "maxlength='" . $limit_text . "' " : '';
$min_fill_text_att           = $min_fill_text ? "minlength='" . $min_fill_text . "' " : '';

$mlw_require_class = 0 == $required ? 'mlwRequiredText' : '';
$input_text = '<input ' . $min_fill_text_att . $autofill_att . $limit_text_att . " type='text' class='qmn_fill_blank $mlw_require_class' name='question" . $id . "[]' />";
$input_text = apply_filters( 'qsm_fill_in_blanks_input_after', $input_text, $id, $question, $answers, $mlw_require_class );

// Preserve original question data before processing
$question_text = isset( $question['question_name'] ) ? $question['question_name'] : '';
$processed_question_text = '';

if ( ! empty( $question_text ) ) {
    $processed_question_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( html_entity_decode( $question_text, ENT_HTML5 ), ENT_QUOTES ), "question-description-{$id}", "QSM Questions" );
}

$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';

if ( strpos( $question_text, '%BLANK%' ) !== false ) {
    $processed_question_text = str_replace( '%BLANK%', $input_text, htmlspecialchars_decode( $question_text, ENT_QUOTES ) );
    $class_object->display_question_title( $question_text, 'fill_in_blank', $new_question_title, $id );
    $processed_question_text = do_shortcode( $processed_question_text );
    echo wp_kses_post( $processed_question_text );
} else {
    $class_object->display_question_title( $question_text, 'fill_in_blank', $new_question_title, $id );
}
echo apply_filters( 'qmn_fill_blank_display_front', '', $id, $question, $answers );