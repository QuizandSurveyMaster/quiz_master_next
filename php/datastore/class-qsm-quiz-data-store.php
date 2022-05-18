<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Data store class.
 */
class QSM_Quiz_Data_Store implements QSM_Data_Store_Interface {

	/**
	 * Create an object in the data store.
	 *
	 * @since 8.0
	 * @param $quiz QSM data instance.
	 */
	public function create( &$quiz ) {
		global $wpdb, $mlwQuizMasterNext;
		if ( is_qsm_quizzes_migrated() ) {
			$quiz_data = array(
				'name'      => $quiz->get_name(),
				'system'    => $quiz->get_system(),
				'views'     => $quiz->get_views(),
				'taken'     => $quiz->get_taken(),
				'author_id' => $quiz->get_author_id(),
				'deleted'   => $quiz->is_deleted(),
				'updated'   => gmdate( 'Y-m-d H:i:s' ),
				'created'   => gmdate( 'Y-m-d H:i:s' ),
			);
			$quiz_insert = $wpdb->insert( $wpdb->prefix.'qsm_quizzes', $quiz_data );
			$quiz_meta = $quiz->get_settings();
			
		} else {
			
		}
	}

	/**
	 * Reads an object from the data store.
	 *
	 * @since 8.0
	 * @param $quiz QSM data instance.
	 */
	public function read( &$quiz ) {
		global $wpdb;
		$quiz->set_defaults();
		if ( is_qsm_quizzes_migrated() ) {
			$quiz_obj = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}qsm_quizzes` WHERE `quiz_id`='{$quiz->get_id()}'" );
			if ( $quiz_obj ) {
				foreach ( $quiz_obj as $key => $value ) {
					$quiz->set_field( $key, $value );
				}
				$quiz_meta = get_qsm_meta( $quiz->get_id(), 'quiz' );
				if ( ! empty( $quiz_meta ) ) {
					foreach ( $quiz_meta as $meta_key => $meta_value ) {
						$meta_value = maybe_unserialize( $meta_value );
						$quiz->set_field( $meta_key, $meta_value, 'settings' );
					}
				}
			}
		} else {
			$quiz_obj = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}mlw_quizzes` WHERE `quiz_id`='{$quiz->get_id()}'" );
			if ( $quiz_obj ) {
				$quiz_settings	 = maybe_unserialize( $quiz_obj->quiz_settings );
				$quiz_options	 = isset( $quiz_settings['quiz_options'] ) ? maybe_unserialize( $quiz_settings['quiz_options'] ) : array();
				$quiz_data		 = array(
					'quiz_id'	 => $quiz_obj->quiz_id,
					'name'		 => $quiz_obj->quiz_name,
					'system'	 => $quiz_options['system'],
					'views'		 => $quiz_obj->quiz_views,
					'taken'		 => $quiz_obj->quiz_taken,
					'author_id'	 => $quiz_obj->quiz_author_id,
					'deleted'	 => $quiz_obj->deleted,
					'updated'	 => $quiz_obj->last_activity,
					'created'	 => $quiz_obj->last_activity,
				);
				foreach ( $quiz_data as $key => $value ) {
					$quiz->set_field( $key, $value );
				}
				/**
				 * Prepare Quiz Meta Data
				 */
				$other_data	 = array(
					'theme_selected'		 => $quiz_obj->theme_selected,
					'quiz_stye'				 => $quiz_obj->quiz_stye,
					'user_email_template'	 => $quiz_obj->user_email_template,
				);
				$quiz_text	 = isset( $quiz_settings['quiz_text'] ) ? maybe_unserialize( $quiz_settings['quiz_text'] ) : array();
				if ( ! empty( $quiz_settings ) ) {
					unset( $quiz_settings['quiz_options'], $quiz_settings['quiz_text'] );
					foreach ( $quiz_settings as $key => $value ) {
						$other_data[$key] = maybe_unserialize( $value );
					}
				}
				$quiz_meta = array_merge( $quiz_options, $quiz_text, $other_data );
				if ( ! empty( $quiz_meta ) ) {
					foreach ( $quiz_meta as $meta_key => $meta_value ) {
						$meta_value = maybe_unserialize( $meta_value );
						$quiz->set_field( $meta_key, $meta_value, 'settings' );
					}
				}
			}
		}
	}

	/**
	 * Update an object in the data store.
	 *
	 * @since 8.0
	 * @param $quiz QSM data instance.
	 */
	public function update( &$quiz ) {
		
	}

	/**
	 * Delete an object from the data store.
	 *
	 * @since 8.0
	 * @param $quiz QSM data instance.
	 * @param array $args Array of args to pass to the delete method.
	 */
	public function delete( &$quiz, $args = array() ) {
		global $wpdb, $mlwQuizMasterNext;
		$id		 = $quiz->get_id();
		$args	 = wp_parse_args(
			$args, array(
			'force_delete' => false
			)
		);
		if ( ! $id ) {
			return;
		}

		$table			 = is_qsm_quizzes_migrated() ? $wpdb->prefix . 'qsm_quizzes' : $wpdb->prefix . 'mlw_quizzes';
		$quiz_post_id	 = $wpdb->get_var( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key`= 'quiz_id' AND `meta_value`= '{$id}'" );
		/**
		 * Fires before deleting quiz
		 */
		do_action( 'qsm_before_delete_quiz', $id, $args );
		if ( $args['force_delete'] ) {
			$qsm_delete = $wpdb->delete( $table, array( 'quiz_id' => $id ) );
		} else {
			$qsm_delete = $wpdb->update( $table, array( 'deleted' => 1, ), array( 'quiz_id' => $id ) );
		}

		if ( $qsm_delete && ! empty( $quiz_post_id ) ) {
			if ( is_qsm_quizzes_migrated() ) {
				$wpdb->delete( $wpdb->prefix . 'qsm_meta', array( 'object_id' => $id, 'type' => 'quiz' ) );
			}
			/**
			 * Delete connected quiz post
			 */
			if ( $args['force_delete'] ) {
				wp_delete_post( $quiz_post_id, true );
			} else {
				wp_trash_post( $quiz_post_id );
			}

			$mlwQuizMasterNext->audit_manager->new_audit( "Quiz/Survey Has Been Deleted: {$quiz->get_name()}", $id, '' );
			/**
			 * Fires after quiz is deleted.
			 */
			do_action( 'qsm_quiz_deleted', $id, $args );
			do_action( 'qmn_quiz_deleted', $id );
		} else {
			$mlwQuizMasterNext->log_manager->add( 'Error 0002', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}

	/**
	 * Delete questions of quiz
	 *
	 * @since 8.0
	 * @param $quiz QSM data instance.
	 * @param array $args Array of args to pass to the delete method.
	 */
	public function delete_questions( &$quiz, $force_delete = false ) {
		global $wpdb, $mlwQuizMasterNext;
		$id = $quiz->get_id();
		if ( ! $id ) {
			return;
		}

		$table = $wpdb->prefix . 'mlw_questions';
		/**
		 * Fires before deleting quiz questions
		 */
		do_action( 'qsm_before_delete_quiz_questions', $id, $force_delete );
		if ( $force_delete ) {
			$qsm_delete = $wpdb->delete( $table, array( 'quiz_id' => $id ) );
		} else {
			$qsm_delete = $wpdb->update( $table, array( 'deleted' => 1, ), array( 'quiz_id' => $id ) );
		}

		if ( $qsm_delete && ! empty( $quiz_post_id ) ) {
			if ( is_qsm_quizzes_migrated() ) {
				$wpdb->delete( $wpdb->prefix . 'qsm_meta', array( 'object_id' => $id, 'type' => 'question' ) );
			}

			/**
			 * Fires after question is deleted.
			 */
			do_action( 'qsm_quiz_questions_deleted', $id, $force_delete );
		} else {
			$mlwQuizMasterNext->log_manager->add( 'Error 0002', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}

}
