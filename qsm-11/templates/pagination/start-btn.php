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

if ( apply_filters( 'qsm_show_start_button', true, $quiz_id, $args ) ) : 
    $start_button_class = apply_filters( 'qsm_start_button_class', array('qsm-start-btn', 'qmn_btn', 'qsm-btn', 'mlw_custom_start', 'qsm-primary', 'qsm-start-btn-'.$quiz_id), $quiz_id, $args );
    $start_button_class = implode( ' ', $start_button_class );
?>
    <a href="javascript:void(0);" 
            class="<?php echo esc_attr( $start_button_class ); ?>" 
            data-action="start"
            <?php echo apply_filters( 'qsm_start_button_attributes', '', $quiz_id, $args ); ?>>
        <?php echo esc_html( apply_filters( 'qsm_start_button_text', $start_text, $quiz_id, $args ) ); ?>
    </a>
<?php endif; ?>
	

