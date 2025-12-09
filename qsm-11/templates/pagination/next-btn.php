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

if ( apply_filters( 'qsm_show_next_button', true, $quiz_id, $args ) ) : 
    $next_button_class = apply_filters( 'qsm_next_button_class', array('qsm-next-btn', 'qmn_btn', 'qsm-btn', 'qsm-next', 'mlw_qmn_quiz_link', 'mlw_next', 'mlw_custom_next', 'qsm-primary', 'qsm-next-btn-'.esc_attr($quiz_id)), $quiz_id, $args );
    $next_button_class = implode( ' ', $next_button_class );
?>
    <a href="javascript:void(0);" 
            class="<?php echo esc_attr( $next_button_class ); ?>" 
            data-action="next"
            <?php echo apply_filters( 'qsm_next_button_attributes', '', $quiz_id, $args ); ?>>
        <?php echo esc_html( apply_filters( 'qsm_next_button_text', $next_text, $quiz_id, $args ) ); ?>
    </a>
<?php endif; ?>
	

