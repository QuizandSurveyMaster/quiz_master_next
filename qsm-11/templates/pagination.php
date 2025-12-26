<?php
/**
 * Template for quiz pagination navigation (New System)
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/pagination.php
 *
 * NOTE: Elements shown in the pagination header (top of form) are AUTOMATICALLY excluded
 * from this bottom pagination to prevent duplication. See pagination-header.php.
 *
 * Available variables:
 * @var int $quiz_id Quiz ID
 * @var object $options Quiz options
 * @var array $quiz_data Quiz data
 * @var QSM_New_Pagination_Renderer $renderer Renderer instance
 * @var string $previous_text Previous button text
 * @var string $next_text Next button text
 * @var string $start_text Start button text
 * @var string $submit_text Submit button text
 * @var array $args All template arguments
 *
 * @package QSM
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hook before pagination render
do_action( 'qsm_before_pagination_render', $quiz_id, $args );
?>

<div 
	class="<?php echo esc_attr( 
		apply_filters( 
			'qsm_pagination_classes', 
			'qsm-navigation qsm-pagination qmn_pagination border margin-bottom qsm-pagination-'.$quiz_id, 
			$quiz_id, 
			$renderer, 
			$args 
		) 
	); ?>" 
	data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>"
	<?php echo apply_filters( 
			'qsm_pagination_attributes', 
			'', 
			$quiz_id, 
			$renderer, 
			$args 
		); ?>>
	<?php

	// Hook before navigation buttons	
	do_action( 'qsm_before_navigation_buttons', $quiz_id, $args );
	
	/**
	 * Hook: qsm_pagination_content
	 * 
	 * This hook renders all pagination elements in priority order.
	 * Elements are registered with priorities to control order.
	 * 
	 * @param int $quiz_id Quiz ID
	 * @param QSM_New_Pagination_Renderer $renderer Renderer instance
	 * @param array $args Template arguments
	 * 
	 * @since 9.0
	 * 
	 * Hooked functions:
	 * - render_page_counter (Priority: 10)
	 * - render_previous_button (Priority: 20)
	 * - render_next_button (Priority: 30)
	 * - render_progress_bar (Priority: 40)
	 * - render_start_button (Priority: 50)
	 * - render_submit_button (Priority: 60)
	 */
	do_action( 'qsm_pagination_content', $quiz_id, $renderer, $args );
	
	// Hook after navigation buttons
	do_action( 'qsm_after_navigation_buttons', $quiz_id, $args );
	?>
</div>

<?php
// Hook after pagination render
do_action( 'qsm_after_pagination_render', $quiz_id, $args );
?>
