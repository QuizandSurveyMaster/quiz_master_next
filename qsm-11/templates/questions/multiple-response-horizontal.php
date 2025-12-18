<?php
/**
 * Template for multiple response horizontal type question
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/multiple-response-horizontal.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract variables passed to template
extract( $args );

// Ensure question_settings is an array
if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

global $mlwQuizMasterNext;
$required   = isset( $question_settings['required'] ) ? $question_settings['required'] : 0;
$mlw_class = '';
if ( 0 == $required ) {
    $mlw_class = 'mlwRequiredRadio';
}
$mlw_class = apply_filters( 'qsm_horizontal_multiple_response_classes', $mlw_class, $id );
$limit_multiple_response = isset( $question_settings['limit_multiple_response'] ) ? $question_settings['limit_multiple_response'] : 0;
$limit_mr_text           = '';
if ( $limit_multiple_response > 0 ) {
    $limit_mr_text = 'onchange=qsmCheckMR(this,' . $limit_multiple_response . ')';
}
$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
$answerEditor       = isset( $question_settings['answerEditor'] ) ? $question_settings['answerEditor'] : '';
$image_width        = isset( $question_settings['image_size-width'] ) ? $question_settings['image_size-width'] : '';
$image_height       = isset( $question_settings['image_size-height'] ) ? $question_settings['image_size-height'] : '';
$answer_limit       = isset( $question_settings['answer_limit'] ) ? $question_settings['answer_limit'] : '';
$limited_answers    = ! empty( $answer_limit ) ? $mlwQuizMasterNext->pluginHelper->qsm_get_limited_options( $answers, intval($answer_limit) ) : $answers;
$answers            = isset( $limited_answers['final'] ) ? $limited_answers['final'] : $answers;
$answer_limit_keys  = isset( $limited_answers['answer_limit_keys'] ) ? $limited_answers['answer_limit_keys'] : '';
$class_object->display_question_title( $question['question_name'], '', $new_question_title, $id );
?>
<fieldset>
    <legend></legend>
    <div class="qmn_check_answers qmn_multiple_horizontal_check <?php echo esc_attr( $mlw_class ); ?>">
        <?php
        if ( is_array( $answers ) ) {
            $mlw_answer_total = 0;
            foreach ( $answers as $answer_index => $answer ) {
                $add_label  = apply_filters( 'qsm_question_addlabel',$answer_index,$answer,count($answers));
                $add_label_value = isset($add_label[ $answer_index ]) ? $add_label[ $answer_index ] : '';
                $mrq_checkbox_class = '';
                if ( empty( $add_label_value ) ) {
                    $mrq_checkbox_class = "mrq_checkbox_class";
                }
                $mlw_answer_total++;
                if ( '' !== $answer[0] ) {
                    $answer_class = apply_filters( 'qsm_answer_wrapper_class', '', $answer, $id );
                    $answer_class .= 'image' === $answerEditor ? ' qmn_image_option' : '';
                    ?>
                <span class="mlw_horizontal_multiple <?php echo esc_attr( $answer_class.' '.$mrq_checkbox_class ); ?>">
                    <input type="checkbox" class="qsm-multiple-response-input" <?php echo esc_attr( $limit_mr_text ); ?> name="question<?php echo esc_attr( $id ) . '[]'; ?>" id="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer_index ); ?>" />
                    <label class="qsm-input-label" for="question<?php echo esc_attr( $id ) . '_' . esc_attr( $mlw_answer_total ); ?>">
                        <?php
                        if ( 'image' === $answerEditor ) {
                            $size_style = '';
                            if ( ! empty($image_width) ) {
                                $size_style .= 'width:'.$image_width.'px !important;';
                            }
                            if ( ! empty($image_height) ) {
                                $size_style .= ' height:'.$image_height.'px !important;';
                            }
                            ?>
                            <img class="qsm-multiple-response-horizontal-img" alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>"  style="<?php echo esc_attr( $size_style ); ?>"  />
                            <span class="qsm_image_caption">
                                <?php
                                $caption_text = trim( htmlspecialchars_decode( $answer[3], ENT_QUOTES ) );
                                $caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $caption_text, 'QSM Answers' );
                                echo wp_kses_post( $add_label_value )." ".esc_html( $caption_text );
                                ?>
                            </span>
                            <?php
                        } else {
                            $answer_text = trim( htmlspecialchars_decode( $add_label_value." ".$answer[0], ENT_QUOTES ) );
                            $answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer_text, 'answer-' . $answer_text, 'QSM Answers' );
                            echo wp_kses_post( do_shortcode( $answer_text ) );
                        }
                        ?>
                    </label>
                    <?php
                        echo apply_filters( 'qsm_multiple_response_horizontal_display_loop', '', $id, $question, $answer, $mlw_answer_total);
                    ?>
                </span>
                    <?php
                }
            }
        }
        ?>
    </div>
</fieldset>
<input type="hidden" name="answer_limit_keys_<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $answer_limit_keys ); ?>" />
<?php
echo apply_filters( 'qmn_horizontal_multiple_response_display_front', '', $id, $question, $answers );