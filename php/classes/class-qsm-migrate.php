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
		if ( isset( $_REQUEST['migrate'] ) && 1 == $_REQUEST['migrate'] ) {
//			self::migrate_quizzes();
//			self::migrate_questions();
//			self::migrate_results();
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

		$legacy_quizzes_tbl  = "{$wpdb->prefix}mlw_quizzes";
		$quizzes_tbl         = "{$wpdb->prefix}qsm_quizzes";
		$quizzes             = $wpdb->get_results( "SELECT * FROM `{$legacy_quizzes_tbl}` ORDER BY `quiz_id` ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {
				$quiz_data = array(
					'quiz_id'   => $quiz->quiz_id,
					'name'      => $quiz->quiz_name,
					'system'    => $quiz->quiz_system,
					'views'     => $quiz->quiz_views,
					'taken'     => $quiz->quiz_taken,
					'author_id' => $quiz->quiz_author_id,
					'deleted'   => $quiz->deleted,
					'updated'   => $quiz->last_activity,
					'created'   => $quiz->last_activity,
				);

				$quiz_insert = $wpdb->insert( $quizzes_tbl, $quiz_data );
				if ( $quiz_insert ) {
					$quiz_id         = $wpdb->insert_id;
					$other_data      = array(
						'old_quiz_id'         => $quiz->quiz_id,
						'theme_selected'      => $quiz->theme_selected,
						'quiz_stye'           => $quiz->quiz_stye,
						'user_email_template' => $quiz->user_email_template,
					);
					$quiz_settings   = maybe_unserialize( $quiz->quiz_settings );
					$quiz_options    = isset( $quiz_settings['quiz_options'] ) ? maybe_unserialize( $quiz_settings['quiz_options'] ) : array();
					$quiz_text       = isset( $quiz_settings['quiz_text'] ) ? maybe_unserialize( $quiz_settings['quiz_text'] ) : array();
					if ( ! empty( $quiz_settings ) ) {
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
	}

	/**
	 * Migrate Questions into new database tables.
	 * @since 8.0
	 */
	public static function migrate_questions() {
		global $wpdb;
		/**
		 * Stop the process if database already migrated.
		 */
		if ( '1' == get_option( 'qsm_db_migrated', '0' ) ) {
			return;
		}
		$legacy_question_tbl = "{$wpdb->prefix}mlw_questions";
		$question_tbl        = "{$wpdb->prefix}qsm_questions";
		/**
		 * Get all questions.
		 */
		$all_questions       = $wpdb->get_results( "SELECT * FROM `{$legacy_question_tbl}` ORDER BY `question_id` ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! empty( $all_questions ) ) {
			foreach ( $all_questions as $question ) {
				$settings        = maybe_unserialize( $question->question_settings );
				$question_data   = array(
					'id'                => $question->question_id,
					'quiz_id'           => $question->quiz_id,
					'name'              => $settings['question_title'],
					'description'       => $question->question_name,
					'type'              => $question->question_type_new,
					'order'             => $question->question_order,
					'deleted'           => $question->deleted,
					'deleted_from_bank' => $question->deleted_question_bank,
					'updated'           => date( 'Y-m-d H:i:s' ),
					'created'           => date( 'Y-m-d H:i:s' ),
				);
				$question_insert = $wpdb->insert( $question_tbl, $question_data );
				if ( $question_insert ) {
					$quiz_id     = $question->quiz_id;
					$question_id = $wpdb->insert_id;

					/**
					 * Store Question Settings into meta table
					 */
					$settings['question_answer_info']    = $question->question_answer_info;
					$settings['comments']                = $question->comments;
					$settings['hints']                   = $question->hints;
					if ( ! empty( $settings ) ) {
						foreach ( $settings as $meta_key => $meta_value ) {
							update_qsm_meta( $question_id, $meta_key, $meta_value, 'question' );
						}
					}
					/**
					 * Store Answers into answers table
					 */
					self::migrate_answers( $question->answer_array, $question_id );

					/**
					 * Store Question Category into answers table
					 */
					self::migrate_question_categories( $question_id, $quiz_id );

					/**
					 * Fires once a Question has been saved.
					 */
					do_action( 'qsm_question_saved', $question_id, $question_data, $settings );
				}
			}
		}
	}

	/**
	 * Migrate Answers into new database tables.
	 * @since 8.0
	 */
	public static function migrate_answers( $answers, $question_id ) {
		global $wpdb;
		$answers_tbl = "{$wpdb->prefix}qsm_answers";
		$answers     = maybe_unserialize( $answers );
		if ( ! empty( $answers ) ) {
			foreach ( $answers as $key => $answer ) {
				$answer_data     = array(
					'question_id' => $question_id,
					'answer'      => $answer[0],
					'point_score' => $answer[1],
					'correct'     => $answer[2],
				);
				$answer_insert   = $wpdb->insert( $answers_tbl, $answer_data );
				if ( $answer_insert ) {
					$answer_id = $wpdb->insert_id;
					/**
					 * Fires once a answer has been saved.
					 */
					do_action( 'qsm_answer_saved', $answer_id, $question_id, $answer );
				}
			}
		}
	}

	/**
	 * Migrate Categories into new database tables.
	 * @since 8.0
	 */
	public static function migrate_question_categories( $question_id = 0, $quiz_id = 0 ) {
		global $wpdb;
		$legacy_terms_tbl    = "{$wpdb->prefix}mlw_question_terms";
		$terms_tbl           = "{$wpdb->prefix}qsm_terms";
		$old_terms           = $wpdb->get_results( "SELECT * FROM `{$legacy_terms_tbl}` WHERE `question_id`='{$question_id}' AND `quiz_id`='{$quiz_id}' ORDER BY `id` ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! empty( $old_terms ) ) {
			foreach ( $old_terms as $term ) {
				$term_data   = array(
					'object_id' => $term->question_id,
					'parent_id' => $term->quiz_id,
					'term_id'   => $term->term_id,
					'taxonomy'  => $term->taxonomy,
					'type'      => 'question',
				);
				$term_insert = $wpdb->insert( $terms_tbl, $term_data );
				if ( $term_insert ) {
					$term_id = $wpdb->insert_id;

					/**
					 * Fires once a Question Category has been saved.
					 */
					do_action( 'qsm_question_category_saved', $term->term_id, $term->question_id, $term->quiz_id );
				}
			}
		}
	}

	/**
	 * Migrate Results into new database tables.
	 * @since 8.0
	 */
	public static function migrate_results() {
		global $wpdb;
		/**
		 * Stop the process if database already migrated.
		 */
		if ( '1' == get_option( 'qsm_db_migrated', '0' ) ) {
			return;
		}

		$legacy_quizzes_tbl  = "{$wpdb->prefix}mlw_results";
		$results_tbl         = "{$wpdb->prefix}qsm_results";
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
