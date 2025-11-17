<?php
/**
 * Template for quiz first page
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/page-first.php
 *
 * @package QSM
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract variables passed to template
extract( $args );

// Apply filters to allow customization
$page_classes = apply_filters( 'qsm_first_page_classes', array( 'qsm-page', $animation_effect ), $quiz_id );
$page_class = implode( ' ', $page_classes );

// Hook before first page render
do_action( 'qsm_before_first_page', $quiz_id, $args );
?>

<section class="<?php echo esc_attr( $page_class ); ?>" >
	<div class="quiz_section quiz_begin">
		
		<?php do_action( 'qsm_before_first_page_content', $quiz_id, $args ); ?>
		
		<?php if ( ! empty( $message_before ) ) : ?>
			<div class='mlw_qmn_message_before'>
				<?php echo wp_kses_post( do_shortcode( $message_before ) ); ?>
			</div>
		<?php endif; ?>
		
		<?php if ( $show_contact_fields ) : ?>
			<?php echo $object_class_qsm_render_pagination->render_contact_form(); ?>
		<?php endif; ?>
		<?php do_action( 'qsm_after_begin_message', $object_class_qsm_render_pagination->get_quiz_properties('options'), $object_class_qsm_render_pagination->get_quiz_properties('quiz_data') ); ?>
		<?php do_action( 'qsm_after_first_page_content', $quiz_id, $args ); ?>
		
	</div>
</section>

<?php
// Hook after first page render
do_action( 'qsm_after_first_page', $quiz_id, $args );
?>
