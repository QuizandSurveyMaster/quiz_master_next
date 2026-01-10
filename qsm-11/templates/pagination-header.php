<?php
/**
 * Template for quiz pagination header (top of form)
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/pagination-header.php
 *
 * Use the 'qsm_pagination_header_elements' filter to specify which pagination elements
 * should appear at the top. Elements shown here are AUTOMATICALLY removed from the bottom
 * pagination to prevent duplication.
 *
 * Available variables:
 * @var int $quiz_id Quiz ID
 * @var object $options Quiz options
 * @var array $quiz_data Quiz data
 * @var QSM_New_Pagination_Renderer $renderer Renderer instance
 * @var array $args All template arguments
 *
 * @package QSM
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hook before pagination header render
do_action( 'qsm_before_pagination_header_render', $quiz_id, $args );

// Check if any elements are registered for header
if ( ! has_action( 'qsm_pagination_header_content' ) ) {
	return;
}
?>

<div class="<?php echo esc_attr( apply_filters( 'qsm_pagination_header_classes', 'qsm-pagination-header qsm-navigation-header', $quiz_id, $renderer, $args ) ); ?>" 
     data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>"
     <?php echo apply_filters( 'qsm_pagination_header_attributes', '', $quiz_id, $renderer, $args ); ?>>
	<?php

	// Hook before pagination header content	
	do_action('qsm_before_pagination_header_content', $quiz_id, $renderer, $args);
	
	/**
	 * Hook: qsm_pagination_header_content
	 *
	 * This hook renders pagination elements at the TOP of the form.
	 * Use this to display navigation elements before quiz content.
	 *
	 * @param int $quiz_id Quiz ID
	 * @param QSM_New_Pagination_Renderer $renderer Renderer instance
	 * @param array $args Template arguments
	 *
	 * @since 9.0
	 *
	 * Hooked functions can be registered dynamically:
	 * - render_page_counter (Priority: 10)
	 * - render_previous_button (Priority: 20)
	 * - render_next_button (Priority: 30)
	 * - render_progress_bar (Priority: 40)
	 *
	 * Use filter 'qsm_pagination_header_elements' to control which elements appear here
	 */
	do_action( 'qsm_pagination_header_content', $quiz_id, $renderer, $args );
	
	// Hook after pagination header content	
	do_action('qsm_after_pagination_header_content', $quiz_id, $renderer, $args);
	?>
</div>

<?php
// Hook after pagination header render
do_action( 'qsm_after_pagination_header_render', $quiz_id, $args );
?>
