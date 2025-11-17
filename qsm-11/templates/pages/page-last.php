<?php
/**
 * Template for quiz last page
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/page-last.php
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
$page_classes = apply_filters( 'qsm_last_page_classes', array( 'qsm-page' ), $quiz_id );
$page_class = implode( ' ', $page_classes );
$last_page_count = count($object_class_qsm_render_pagination->get_pages_data());

// Hook before last page render
do_action( 'qsm_before_last_page', $quiz_id, $args );
?>

<section class="<?php echo esc_attr( $page_class ); ?>" data-page="<?php echo ++$last_page_count; ?>" data-page-type="last" style="display: none;">
	<div class="quiz_section quiz_end empty_quiz_end">
	
	<?php do_action( 'qsm_before_last_page_content', $quiz_id, $args ); ?>
	
	<?php if ( '' != trim( $message_after ) ) : ?>
		<div class='qsm-after-message mlw_qmn_message_end'>
			<?php echo wp_kses_post( do_shortcode( trim( $message_after ) ) ); ?>
		</div>
	<?php endif; ?>
	
	<?php if ( $show_contact_fields && isset( $object_class_qsm_render_pagination ) ) : ?>
		<div class="qsm-contact-form-wrapper">
			<?php echo $object_class_qsm_render_pagination->render_contact_form(); ?>
		</div>
	<?php endif; ?>
	
	</div>
	
	<?php do_action( 'qsm_after_last_page_content', $quiz_id, $args ); ?>
	
</section>

<?php
// Hook after last page render
do_action( 'qsm_after_last_page', $quiz_id, $args );
?>
