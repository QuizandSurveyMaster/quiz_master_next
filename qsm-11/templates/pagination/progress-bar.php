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

// Determine render mode for QSM-11 progress bar: auto|svg|simple (filterable)
$progress_mode = apply_filters( 'qsm_progressbar_render_mode', 'auto', $quiz_id, $options );
?>
<div 
    id="qsm_progress_bar_<?php echo esc_attr( $quiz_id ); ?>" 
    class="qsm-progress-bar qsm-progress-bar-<?php echo esc_attr( $quiz_id ); ?>" 
    data-progress-mode="<?php echo esc_attr( $progress_mode ); ?>">
    <div class="progressbar-text"></div>
</div>
