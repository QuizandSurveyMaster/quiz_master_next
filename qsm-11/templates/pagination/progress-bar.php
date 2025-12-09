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
if ( 1 == intval( $options->progress_bar ) && apply_filters( 'qsm_show_progress_bar', true, $quiz_id, $args ) ) : ?>
    <div class="qsm-progress-bar qsm-progress-bar-<?php echo esc_attr( $quiz_id ); ?>">
        <div class="qsm-progress-bar-container">
            <div class="qsm-progress-fill"></div>
        </div>
        <div class="qsm-progress-text">0%</div>
    </div>
<?php endif; ?>
	

