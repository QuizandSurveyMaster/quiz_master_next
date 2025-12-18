<?php
/**
 * Template for opt-in type question
 *
 * This template can be overridden by copying it to yourpath/opt-in.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract args
extract( $args );

// Ensure question_settings is an array
if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

global $mlwQuizMasterNext;

// Get required status
$required = isset( $question_settings['required'] ) ? $question_settings['required'] : '';
$mlw_require_class = $required == 0 ? 'mlwRequiredAccept' : '';
?>
<div class="qmn_accept_answers">
    <input type="checkbox" id="mlwAcceptance<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( 'question' . $id ); ?>" class="<?php echo esc_attr( $mlw_require_class ); ?>" />
    <label class="qsm-input-label" for="mlwAcceptance<?php echo esc_attr( $id ); ?>">
        <span class="qmn_accept_text">
        <?php
            $question = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
            $question = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $question, ENT_QUOTES ), "question-description-{$id}", 'QSM Questions' );
            echo wp_kses_post( do_shortcode( $question ) );
        ?>
        </span>
    </label>
</div>
<?php
echo apply_filters( 'qmn_accept_display_front', '', $id, $question, $answers );