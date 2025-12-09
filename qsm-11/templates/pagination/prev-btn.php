<?php
/**
 * Template for quiz pagination navigation (New System)
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/new-frontend/pagination.php
 *
 * @package QSM
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extract( $args );


if ( apply_filters( 'qsm_show_previous_button', true, $quiz_id, $args ) ) {
    $previous_button_class = apply_filters( 'qsm_previous_button_class', array('qsm-previous-btn', 'qmn_btn', 'qsm-btn', 'qsm-previous', 'mlw_qmn_quiz_link', 'mlw_previous', 'qsm-secondary','qsm-previous-btn-'.esc_attr($quiz_id)), $quiz_id, $args );
    $previous_button_class = implode( ' ', $previous_button_class );
?>
    <a href="javascript:void(0);" 
            class="<?php echo esc_attr( $previous_button_class ); ?>" 
            data-action="previous"
            <?php echo apply_filters( 'qsm_previous_button_attributes', '', $quiz_id, $args ); ?>>
        <?php echo esc_html( apply_filters( 'qsm_previous_button_text', $previous_text, $quiz_id, $args ) ); ?>
    </a>
<?php } ?>
	

