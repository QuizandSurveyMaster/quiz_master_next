<?php
/**
 * Template for polar type question
 *
 * This template can be overridden by copying it to yourpath/polar.php
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

// Get question settings
$required       = isset( $question_settings['required'] ) ? $question_settings['required'] : '';
$answerEditor   = isset( $question_settings['answerEditor'] ) ? $question_settings['answerEditor'] : '';
$image_width    = isset( $question_settings['image_size-width'] ) ? $question_settings['image_size-width'] : '';
$image_height   = isset( $question_settings['image_size-height'] ) ? $question_settings['image_size-height'] : '';
$first_point    = isset( $answers[0][1] ) ? floatval( $answers[0][1] ) : 0;
$second_point   = isset( $answers[1][1] ) ? floatval( $answers[1][1] ) : 0;
$is_reverse     = false;
$check_point    = $second_point;
if ( $first_point > $second_point ) {
    $is_reverse  = true;
    $check_point = $first_point;
}
$total_answer      = count( $answers );
$id                = esc_attr( intval( $id ) );
$answar1           = $first_point;
$answar2           = $second_point;
$slider_data_atts  = '';
$slider_data_atts .= ' data-answer1=' . $answar1 . ' ';
$slider_data_atts .= ' data-answer2=' . $answar2 . ' ';
$slider_data_atts .= ' data-is_reverse=' . intval( $is_reverse ) . ' ';
$slider_data_atts .= ' data-is_required=' . $required . ' ';

$mlw_require_class = $required == 0 ? 'mlwRequiredText mlwRequiredPolar' : '';

$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
qsm_question_title_func( $question['question_name'], '', $new_question_title, $id );
$show = true;
$show = apply_filters( 'qsm_check_advance_polar_show_status', $show, $id );
echo apply_filters( 'qmn_polar_display_front_before', '', $id, $question, $answers );
if ( $show ) {
?>
<span class="mlw_qmn_question question-type-polar-s">
    <div class='left-polar-title'> <?php
    if ( 'image' === $answerEditor ) {
        $size_style = '';
        if ( ! empty($image_width) ) {
            $size_style .= 'width:'.$image_width.'px !important;';
        }
        if ( ! empty($image_height) ) {
            $size_style .= ' height:'.$image_height.'px !important;';
        }
        $left_image = $answers[0][0];
        ?>
        <img class="qsm-polar-img" alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $left_image, ENT_QUOTES ) ) ); ?>"  style="<?php echo esc_attr( $size_style ); ?>"  />
        <span class="qsm_image_caption">
            <?php
            $caption_text = trim( htmlspecialchars_decode( $answers[0][3], ENT_QUOTES ) );
            $caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $id . '-0', 'QSM Answers' );
            echo esc_html( $caption_text );
            ?>
        </span>
        <?php
    } else {
        $left_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answers[0][0], 'answer-' . $id . '-0', "QSM Answers" );
        echo wp_kses_post( do_shortcode( $left_title ) );
    }
    ?> </div>
    <div class='slider-main-wrapper'>
        <input type='hidden' class='qmn_polar <?php echo esc_attr( $mlw_require_class ); ?>' id='question<?php echo esc_attr( $id ); ?>' name='question<?php echo esc_attr( $id ); ?>' value=''/>
        <div id="slider-<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( $slider_data_atts ); ?> ></div>
    </div>
    <div class='right-polar-title'><?php
    if ( 'image' === $answerEditor ) {
        $size_style = '';
        if ( ! empty($image_width) ) {
            $size_style .= 'width:'.$image_width.'px !important;';
        }
        if ( ! empty($image_height) ) {
            $size_style .= ' height:'.$image_height.'px !important;';
        }
        $right_image = $answers[1][0];
        ?>
        <img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $right_image, ENT_QUOTES ) ) ); ?>"  style="<?php echo esc_attr( $size_style ); ?>"  />
        <span class="qsm_image_caption">
            <?php
            $caption_text = trim( htmlspecialchars_decode( $answers[1][3], ENT_QUOTES ) );
            $caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $id . '-1', 'QSM Answers' );
            echo esc_html( $caption_text );
            ?>
        </span>
        <?php
    } else {
        $right_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answers[1][0], 'answer-' . $id . '-1', "QSM Answers" );
        echo wp_kses_post( do_shortcode( $right_title ) );
    }
    ?></div>
</span>
<?php
}
echo apply_filters( 'qmn_polar_display_front', '', $id, $question, $answers );