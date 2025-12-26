<?php
/**
 * Template for quiz pagination page count
 *
 * This template can be overridden by copying it to
 * yourtheme/qsm/templates/pagination/page-count.php
 *
 * @package QSM
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extract( $args );

$current_page = intval( $current_page ?? 1 );
$total_pages  = intval( $total_pages ?? 1 );
$quiz_id      = intval( $quiz_id ?? 0 );

$text = sprintf(
	'%d %s %d',
	$current_page,
	esc_html__( 'out of', 'quiz-master-next' ),
	$total_pages
);

/**
 * Filter pagination count text.
 *
 * @param string $text
 * @param int    $current_page
 * @param int    $total_pages
 * @param int    $quiz_id
 * @param array  $args
 */
$text = apply_filters(
	'qsm_total_pages_count',
	$text,
	$current_page,
	$total_pages,
	$quiz_id,
	$args
);
?>

<span class="pages_count page_count_<?php echo esc_attr( $current_page ); ?>">
	<?php echo esc_html( $text ); ?>
</span>
