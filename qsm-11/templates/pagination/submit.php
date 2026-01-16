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

if ( apply_filters( 'qsm_show_submit_button', true, $quiz_id, $args ) ) : 
    $submit_button_class = apply_filters(
        'qsm_submit_button_class',
        array(
            'qmn_btn',
            'qsm-btn',
            'qsm-submit-btn',
            'qsm-primary',
            'qsm-submit-btn-'.esc_attr($quiz_id)
        ),
        $quiz_id,
        $args
    );
    $submit_button_class = implode( ' ', $submit_button_class );
	?>
    <input 
        type="submit" 
        value="<?php echo esc_html(
            apply_filters(
                'qsm_submit_button_text',
                $submit_text,
                $quiz_id,
                $args
            )
        ); ?>"
        class="<?php echo esc_attr( $submit_button_class ); ?>" 
        data-action="submit"
        <?php echo apply_filters(
            'qsm_submit_button_attributes',
            '',
            $quiz_id,
            $args
        ); ?>>
<?php endif; ?>
	

