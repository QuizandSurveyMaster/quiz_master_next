<?php
/**
 * Automatically deletes quiz results older than the configured number of days.
 *
 * @package QSM
 * @since 11.2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the scheduled clean up of old quiz results.
 *
 * The retention period is stored in the global setting
 * `qmn-settings[auto_delete_results_days]`. A value of 0 (the default) keeps
 * results forever and no cron event is scheduled.
 *
 * @since 11.2.6
 */
class QSM_Results_Cleanup {

	/**
	 * The cron hook used for the daily clean up.
	 *
	 * @since 11.2.6
	 * @var string
	 */
	const CRON_HOOK = 'qsm_delete_old_results';

	/**
	 * Registers the hooks.
	 *
	 * @since 11.2.6
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_schedule' ) );
		add_action( 'update_option_qmn-settings', array( __CLASS__, 'maybe_schedule' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'delete_old_results' ) );
	}

	/**
	 * Returns the configured retention period in days.
	 *
	 * @since 11.2.6
	 * @return int Number of days to keep results for. 0 when the feature is off.
	 */
	public static function get_retention_days() {
		$settings = (array) get_option( 'qmn-settings' );
		$days     = isset( $settings['auto_delete_results_days'] ) ? intval( $settings['auto_delete_results_days'] ) : 0;

		return $days > 0 ? $days : 0;
	}

	/**
	 * Schedules the daily clean up when a retention period is set and removes
	 * the event again when the feature is turned off.
	 *
	 * @since 11.2.6
	 * @return void
	 */
	public static function maybe_schedule() {
		$days      = self::get_retention_days();
		$timestamp = wp_next_scheduled( self::CRON_HOOK );

		if ( $days > 0 && ! $timestamp ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		} elseif ( 0 === $days && $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Removes the scheduled event when the plugin is deactivated.
	 *
	 * @since 11.2.6
	 * @return void
	 */
	public static function on_deactivation() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Deletes the results that are older than the retention period.
	 *
	 * Results are removed in batches so a large table does not time out. When a
	 * full batch is removed another run is queued to drain the rest.
	 *
	 * @since 11.2.6
	 * @return int Number of results deleted in this run.
	 */
	public static function delete_old_results() {
		global $wpdb, $mlwQuizMasterNext;

		$days = self::get_retention_days();
		if ( 0 === $days ) {
			return 0;
		}

		$batch_size = intval( apply_filters( 'qsm_auto_delete_results_batch_size', 500 ) );
		if ( $batch_size < 1 ) {
			return 0;
		}

		$result_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE time_taken_real <> '0000-00-00 00:00:00' AND time_taken_real < DATE_SUB( %s, INTERVAL %d DAY ) LIMIT %d",
				current_time( 'mysql' ),
				$days,
				$batch_size
			)
		);

		if ( empty( $result_ids ) ) {
			return 0;
		}

		$result_ids   = array_map( 'absint', $result_ids );
		$placeholders = implode( ',', array_fill( 0, count( $result_ids ), '%d' ) );

		// The child rows are removed first so nothing is orphaned on installs
		// where the foreign keys could not be created.
		foreach ( array( 'qsm_results_questions', 'qsm_results_meta' ) as $child_table ) {
			$table = $wpdb->prefix . $child_table;
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
				continue;
			}
			$wpdb->query( $wpdb->prepare( "DELETE FROM `$table` WHERE result_id IN ( $placeholders )", $result_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}mlw_results WHERE result_id IN ( $placeholders )", $result_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = intval( $deleted );

		if ( $deleted > 0 && isset( $mlwQuizMasterNext->log_manager ) ) {
			$mlwQuizMasterNext->log_manager->add(
				__( 'Old results deleted', 'quiz-master-next' ),
				sprintf(
					/* translators: %1$d: number of results deleted, %2$d: retention period in days */
					__( '%1$d results older than %2$d days have been deleted.', 'quiz-master-next' ),
					$deleted,
					$days
				),
				0,
				'event'
			);
		}

		// More results are likely waiting, so queue another pass.
		if ( count( $result_ids ) === $batch_size ) {
			wp_schedule_single_event( time() + ( 5 * MINUTE_IN_SECONDS ), self::CRON_HOOK );
		}

		return $deleted;
	}
}
