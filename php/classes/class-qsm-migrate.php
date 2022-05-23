<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles migration logic
 *
 * @since 7.3.0
 */
class QSM_Migrate {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_cancel_db_upgrade', array( $this, 'cancel_db_upgrade' ) );
		add_action( 'wp_ajax_init_db_upgrade', array( $this, 'init_migration' ) );
	}

	/**
	 * Display admin notice for database migration.
	 */
	public function admin_notices() {
		$db_migrated = get_option( 'qsm_db_migrated', 0 );
		if ( ! $db_migrated ) {
			?>
			<div class="notice notice-info db-upgrade-notice">
				<h3><?php esc_html_e( 'QSM - Database update required', 'quiz-master-next' ); ?></h3>
				<p>
					<?php esc_html_e( 'We need to upgrade your database so that you can enjoy the latest features.', 'quiz-master-next' ); ?><br>
					<?php
					/* translators: %s: HTML tag */
					echo sprintf( esc_html__( 'Please note that this action %1$s can not be %2$s rolled back. We recommend you to take a backup of your current site before proceeding.', 'quiz-master-next' ), '<b>', '</b>' );
					?>
				</p>
				<p class="notice-action-links">
					<a href="javascript:void(0)" class="button cancel-db-upgrade"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></a>
					&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" class="button button-primary init-db-upgrade"><?php esc_html_e( 'Update Database', 'quiz-master-next' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Cancel database migration
	 * @global type $wpdb
	 * @since 8.0
	 */
	public static function cancel_db_upgrade() {
		global $wpdb;
		update_option( 'qsm_db_migrated', 'cancelled' );
		exit;
	}

	/**
	 * Migrate Quizzes into new database tables.
	 * @since 8.0
	 */
	public static function init_migration() {
		$response = array(
			'status'  => true,
			'quiz'    => 0,
			'message' => __( 'Database updated successfully.', 'quiz-master-next' ),
		);
		/**
		 * Check if already migrated.
		 */
		if ( is_qsm_migrated() ) {
			$response = array(
				'status'  => false,
				'quiz'    => 0,
				'message' => __( 'Database is already migrated.', 'quiz-master-next' ),
			);
			return;
		}

		/**
		 * Migrate legacy multiple categories.
		 */
		if ( is_qsm_multiple_category_migrated() ) {
			self::migrate_multiple_categories();
		}

		/**
		 * Migrate Quizzes.
		 */
		$quizzes = self::migrate_quizzes();
		if ( false !== $quizzes ) {
			$response['status']  = true;
			$response['quiz']    = $quizzes;
		}

		/**
		 * Set database migrated flag in option table.
		 */
		update_option( 'qsm_db_migrated', gmdate( time() ) );

		echo wp_json_encode( $response );
		exit;
	}

	/**
	 * Migrate Quizzes into new database tables.
	 * @since 8.0
	 */
	public static function migrate_quizzes() {
		global $wpdb;
		/**
		 * Stop the process if database already migrated.
		 */
		if ( is_qsm_quizzes_migrated() ) {
			return true;
		}
		$legacy_quizzes_tbl  = "{$wpdb->prefix}mlw_quizzes";
		$quizzes_tbl         = "{$wpdb->prefix}qsm_quizzes";
		$quizzes             = $wpdb->get_results( "SELECT * FROM `{$legacy_quizzes_tbl}` ORDER BY `quiz_id` ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_quizzes       = count( $quizzes );
		$total_migrated      = 0;
		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {
				$quiz_settings   = maybe_unserialize( $quiz->quiz_settings );
				$quiz_options    = isset( $quiz_settings['quiz_options'] ) ? maybe_unserialize( $quiz_settings['quiz_options'] ) : array();
				$quiz_data = array(
					'quiz_id'   => $quiz->quiz_id,
					'name'      => $quiz->quiz_name,
					'system'    => $quiz_options['system'],
					'views'     => $quiz->quiz_views,
					'taken'     => $quiz->quiz_taken,
					'author_id' => $quiz->quiz_author_id,
					'deleted'   => $quiz->deleted,
					'updated'   => $quiz->last_activity,
					'created'   => $quiz->last_activity,
				);

				$quiz_insert = $wpdb->insert( $quizzes_tbl, $quiz_data );
				if ( $quiz_insert ) {
					$total_migrated  += 1;
					$quiz_id         = $wpdb->insert_id;
					$other_data      = array(
						'old_quiz_id'         => $quiz->quiz_id,
						'theme_selected'      => $quiz->theme_selected,
						'quiz_stye'           => $quiz->quiz_stye,
						'message_after'       => $quiz->message_after,
						'user_email_template' => $quiz->user_email_template,
					);
					$quiz_text       = isset( $quiz_settings['quiz_text'] ) ? maybe_unserialize( $quiz_settings['quiz_text'] ) : array();
					if ( ! empty( $quiz_settings ) ) {
						/**
						 * Unset unused settings
						 */
						unset( $quiz_options['quiz_name'], $quiz_options['system'] );
						unset( $quiz_settings['quiz_options'], $quiz_settings['quiz_text'] );
						foreach ( $quiz_settings as $key => $value ) {
							$other_data[ $key ] = $value;
						}
					}
					$settings = array_merge( $quiz_options, $quiz_text, $other_data );
					/**
					 * Store Quiz Settings into meta table
					 */
					if ( ! empty( $settings ) ) {
						foreach ( $settings as $meta_key => $meta_value ) {
							$meta_value = maybe_unserialize( $meta_value );
							update_qsm_meta( $quiz_id, $meta_key, $meta_value, 'quiz' );
						}
					}
					/**
					 * Fires once a Question has been saved.
					 */
					do_action( 'qsm_quiz_saved', $quiz_id, $quiz_data, $settings );
				}
			}
		}
		/**
		 * Set option once quizzes are migrated
		 */
		update_option( 'qsm_quizzes_migrated', gmdate( time() ) );
		return $total_migrated;
	}

	public static function migrate_multiple_categories() {
		global $wpdb;
		$new_category    = '';
		$term_id         = 0;
		$values_array    = array();
		$result          = false;
		$category_data   = $wpdb->get_results( "SELECT `question_id`, `quiz_id`, `category` FROM `{$wpdb->prefix}mlw_questions` WHERE category <> '' ORDER BY category" );
		if ( ! empty( $category_data ) ) {
			foreach ( $category_data as $data ) {
				if ( $new_category != $data->category ) {
					$term_data = get_term_by( 'name', $data->category, 'qsm_category' );
					if ( $term_data ) {
						$term_id = $term_data->term_id;
					} else {
						$term_array  = wp_insert_term( $data->category, 'qsm_category' );
						$term_id     = $term_array['term_id'];
					}
				}
				$values_array[] = "($data->question_id, $data->quiz_id, $term_id, 'qsm_category')";
			}
			$values          = join( ',', $values_array );
			$insert_query    = stripslashes( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mlw_question_terms (question_id, quiz_id, term_id, taxonomy) VALUES %1s", $values ) );
			$result          = $wpdb->query( $insert_query );
			if ( $result > 0 ) {
				update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
				$response    = array(
					'status' => true,
					'count'  => $result,
				);
				$update      = "UPDATE {$wpdb->prefix}mlw_questions SET category = '' ";
				$updated     = $wpdb->query( $update );
			} else {
				$response = array( 'status' => false );
			}
		} else {
			$response = array(
				'status' => true,
				'count'  => 0,
			);
			update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
		}
		return $response;
	}

	/**
	 * This function check which regime of category is available and returns category data accordingly
	 *
	 * @param string $name
	 * @return array
	 */
	public function get_category_data( $name ) {
		$enabled     = get_option( 'qsm_multiple_category_enabled' );
		$migrated    = ( $enabled && 'cancelled' !== $enabled ) ? true : false;
		$response    = array( 'migrated' => $migrated );

		if ( $migrated ) {
			$cats    = explode( '_', $name );
			$ids     = array();
			foreach ( $cats as $category ) {
				$category = trim( $category );
				if ( '' !== $category ) {
					$cat_data = get_term_by( 'name', $category, 'qsm_category' );
					if ( $cat_data ) {
						$ids[] = $cat_data->term_id;
					}
				}
			}

			if ( ! empty( $ids ) ) {
				$response['ids'] = $ids;
			} else {
				$response['migrated']    = false;
				$response['name']        = $name;
			}
		} else {
			$response['name'] = $name;
		}

		return $response;
	}

}
