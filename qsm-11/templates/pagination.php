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

// Extract variables passed to template
extract( $args );

// Apply filters to allow customization
$pagination_classes = apply_filters( 'qsm_pagination_classes', array( 'qsm-navigation', 'qsm-pagination', 'qmn_pagination', 'border', 'margin-bottom' ), $quiz_id );
$pagination_class = implode( ' ', $pagination_classes );

// Hook before pagination render
do_action( 'qsm_before_pagination_render', $quiz_id, $args );
?>

<div class="<?php echo esc_attr( $pagination_class ); ?>" data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>">
	
	<?php do_action( 'qsm_before_navigation_buttons', $quiz_id, $args ); ?>
	
	<!-- Page Counter -->
	<div class="qsm-page-counter" style="display: none;"></div>
	
	<!-- Previous Button -->
	<?php if ( apply_filters( 'qsm_show_previous_button', true, $quiz_id, $args ) ) : ?>
		<a href="javascript:void(0);" 
				class="qsm-previous-btn qmn_btn qsm-btn qsm-previous mlw_qmn_quiz_link mlw_previous qsm-secondary" 
				data-action="previous"
				<?php echo apply_filters( 'qsm_previous_button_attributes', '', $quiz_id, $args ); ?>>
			<?php echo esc_html( apply_filters( 'qsm_previous_button_text', $previous_text, $quiz_id, $args ) ); ?>
		</a>
	<?php endif; ?>

	<!-- Progress Bar -->
	<?php if ( 1 == intval( $options->progress_bar ) && apply_filters( 'qsm_show_progress_bar', true, $quiz_id, $args ) ) : ?>
		<div class="qsm-progress-bar">
			<div class="qsm-progress-bar-container">
				<div class="qsm-progress-fill"></div>
			</div>
			<div class="qsm-progress-text">0%</div>
		</div>
	<?php endif; ?>
	
	<!-- Start Button -->
	<?php if ( apply_filters( 'qsm_show_start_button', true, $quiz_id, $args ) ) : ?>
		<a href="javascript:void(0);" 
				class="qsm-start-btn qmn_btn qsm-btn mlw_custom_start qsm-start-btn qsm-primary" 
				data-action="start"
				<?php echo apply_filters( 'qsm_start_button_attributes', '', $quiz_id, $args ); ?>>
			<?php echo esc_html( apply_filters( 'qsm_start_button_text', $start_text, $quiz_id, $args ) ); ?>
		</a>
	<?php endif; ?>
	
	<!-- Next Button -->
	<?php if ( apply_filters( 'qsm_show_next_button', true, $quiz_id, $args ) ) : ?>
		<a href="javascript:void(0);" 
				class="qsm-next-btn qmn_btn qsm-btn qsm-next mlw_qmn_quiz_link mlw_next mlw_custom_next qsm-primary" 
				data-action="next"
				<?php echo apply_filters( 'qsm_next_button_attributes', '', $quiz_id, $args ); ?>>
			<?php echo esc_html( apply_filters( 'qsm_next_button_text', $next_text, $quiz_id, $args ) ); ?>
		</a>
	<?php endif; ?>
	
	<!-- Submit Button -->
	<?php if ( apply_filters( 'qsm_show_submit_button', true, $quiz_id, $args ) ) : ?>
		<input type="submit" 
				value="<?php echo esc_html( apply_filters( 'qsm_submit_button_text', $submit_text, $quiz_id, $args ) ); ?>"
				class="qmn_btn qsm-btn qsm-submit-btn qsm-primary" 
				data-action="submit"
				<?php echo apply_filters( 'qsm_submit_button_attributes', '', $quiz_id, $args ); ?>>
	<?php endif; ?>
		
	<?php do_action( 'qsm_after_navigation_buttons', $quiz_id, $args ); ?>
	
</div>

<?php
// Hook after pagination render
do_action( 'qsm_after_pagination_render', $quiz_id, $args );
?>
