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
		add_action( 'wp_ajax_enable_multiple_categories', array( $this, 'enable_multiple_categories' ) );
		
		if (isset($_REQUEST['migrate']) && 1 == $_REQUEST['migrate']) {
			//self::migrate_quizzes();
		}
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
		if ( '1' == get_option( 'qsm_db_migrated', '0' ) ) {
			return;
		}

		$quizzes_tbl = "{$wpdb->prefix}qsm_quizzes";
		$meta_tbl	 = "{$wpdb->prefix}qsm_meta";
		$quizzes	 = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}mlw_quizzes` ORDER BY `quiz_id` ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		
		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {
				$new_data = array(
					'quiz_id'	 => $quiz->quiz_id,
					'name'		 => $quiz->quiz_name,
					'system'	 => $quiz->quiz_system,
					'views'		 => $quiz->quiz_views,
					'taken'		 => $quiz->quiz_taken,
					'author_id'	 => $quiz->quiz_author_id,
					'deleted'	 => $quiz->deleted,
					'updated'	 => $quiz->last_activity,
					'created'	 => $quiz->last_activity
				);
				
				$quiz_insert = $wpdb->insert($quizzes_tbl, $new_data);
				if ($quiz_insert) {
					$quiz_id = $wpdb->insert_id;
					$other_data = array(
						'theme_selected' => $quiz->theme_selected,
						'quiz_stye' => $quiz->quiz_stye,
						'user_email_template' => $quiz->user_email_template
					);
					$quiz_settings	 = maybe_unserialize( $quiz->quiz_settings );
					$quiz_options	 = isset($quiz_settings['quiz_options']) ? maybe_unserialize( $quiz_settings['quiz_options'] ) : array();
					$quiz_text		 = isset($quiz_settings['quiz_text']) ? maybe_unserialize( $quiz_settings['quiz_text'] ) : array();
					if (! empty($quiz_settings)) {
						unset($quiz_settings['quiz_options'], $quiz_settings['quiz_text']);
						foreach ($quiz_settings as $key => $settings) {
							$other_data[$key] = $settings;
						}
					}
					$meta_data		 = array_merge( $quiz_options, $quiz_text, $other_data );
					if ( ! empty( $meta_data ) ) {
						foreach ( $meta_data as $meta_key => $meta_value ) {
							update_qsm_meta($quiz_id, $meta_key, $meta_value, 'quiz');
						}
					}
				}
			}
		}
	}

	/**
	 * Migrate Questions into new database tables.
	 * @since 8.0
	 */
	public static function migrate_questions() {
		global $wpdb;
		$question_tbl	 = "{$wpdb->prefix}qsm_questions";
		$meta_tbl		 = "{$wpdb->prefix}qsm_meta";
		$answers_tbl	 = "{$wpdb->prefix}qsm_answers";
		/**
		 * Get all questions.
		 */
		$all_questions		 = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}mlw_questions`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! empty( $all_questions ) ) {
			foreach ( $all_questions as $question ) {
				$new_data = array(
					
				);
				echo "<pre>";
				print_r($question);
				exit;
			}
		}
	}

	/**
	 * This function enables multiple categories feature in QSM
	 *
	 * @since 7.3.0
	 * @return void
	 */
	public function enable_multiple_categories() {
		global $wpdb;
		global $mlwQuizMasterNext;
		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';
		switch ( $value ) {
			case 'enable':
				$new_category	 = '';
				$term_id		 = 0;
				$values_array	 = array();
				$result			 = false;
				$category_data	 = $wpdb->get_results( "SELECT question_id, quiz_id, category FROM {$wpdb->prefix}mlw_questions WHERE category <> '' ORDER BY category" );
				if ( ! empty( $category_data ) ) {
					foreach ( $category_data as $data ) {
						if ( $new_category != $data->category ) {
							$term_data = get_term_by( 'name', $data->category, 'qsm_category' );
							if ( $term_data ) {
								$term_id = $term_data->term_id;
							} else {
								$term_array	 = wp_insert_term( $data->category, 'qsm_category' );
								$term_id	 = $term_array['term_id'];
							}
						}
						$values_array[] = "($data->question_id, $data->quiz_id, $term_id, 'qsm_category')";
					}
					$values			 = join( ',', $values_array );
					$insert_query	 = stripslashes( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mlw_question_terms (question_id, quiz_id, term_id, taxonomy) VALUES %1s", $values ) );
					$result			 = $wpdb->query( $insert_query );
					if ( $result > 0 ) {
						update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
						$response	 = array(
							'status' => true,
							'count'	 => $result,
						);
						$update		 = "UPDATE {$wpdb->prefix}mlw_questions SET category = '' ";
						$updated	 = $wpdb->query( $update );
					} else {
						$response = array(
							'status' => false,
						);
					}
				} else {
					$response = array(
						'status' => true,
						'count'	 => 0,
					);
					update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
				}
				echo wp_json_encode( $response );
				break;

			case 'cancel':
				update_option( 'qsm_multiple_category_enabled', 'cancelled' );
				return true;
				break;
		}
		exit;
	}

	/**
	 * This function check which regime of category is available and returns category data accordingly
	 *
	 * @param string $name
	 * @return array
	 */
	public function get_category_data( $name ) {
		$enabled	 = get_option( 'qsm_multiple_category_enabled' );
		$migrated	 = ( $enabled && 'cancelled' !== $enabled ) ? true : false;
		$response	 = array( 'migrated' => $migrated );

		if ( $migrated ) {
			$cats	 = explode( '_', $name );
			$ids	 = array();
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
				$response['migrated']	 = false;
				$response['name']		 = $name;
			}
		} else {
			$response['name'] = $name;
		}

		return $response;
	}

}
