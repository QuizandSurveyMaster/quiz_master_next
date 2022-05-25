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
			$quiz_data   = array(
				'name'      => $quiz->get_name(),
				'system'    => $quiz->get_system(),
				'views'     => $quiz->get_views(),
				'taken'     => $quiz->get_taken(),
				'author_id' => $quiz->get_author_id(),
				'deleted'   => $quiz->is_deleted(),
				'updated'   => gmdate( 'Y-m-d H:i:s' ),
				'created'   => gmdate( 'Y-m-d H:i:s' ),
			);
			$quiz_insert = $wpdb->insert( $wpdb->prefix . 'qsm_quizzes', $quiz_data );
			if ( $quiz_insert ) {
				$quiz_id     = $wpdb->insert_id;
				$quiz_meta   = array_merge( $quiz->get_settings(), QMNPluginHelper::get_default_texts() );

				$quiz->set_id( $quiz_id );
				$quiz->set_field( 'settings', $quiz_meta );
				$this->save_quiz_post_type( $quiz );
				$this->update_quiz_meta( $quiz, true );

				/**
				 * Activate quiz theme
				 */
				$mlwQuizMasterNext->theme_settings->activate_selected_theme( $quiz->get_id(), $quiz->get_field( 'theme_id', 'settings' ) );

				/**
				 * Fires after quiz is created
				 */
				do_action( 'qsm_quiz_created', $quiz->get_id(), $quiz );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error 0001', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
			}
		} else {
			$this->legacy_create( $quiz );
		}
	}

	/**
	 * Update an object in the data store.
	 *
	 * @since 8.0
	 * @param $quiz QSM data instance.
	 */
	public function update( &$quiz ) {
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
			);
			$wpdb->update( $wpdb->prefix . 'qsm_quizzes', $quiz_data, array( 'quiz_id', $quiz->get_id() ) );

			/**
			 * Update Quiz Meta
			 */
			$this->update_quiz_meta( $quiz );

			/**
			 * Fires after quiz is updated
			 */
			do_action( 'qsm_quiz_updated', $quiz->get_id(), $quiz );
		} else {
			$this->legacy_update( $quiz );
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
				$quiz_settings   = maybe_unserialize( $quiz_obj->quiz_settings );
				$quiz_options    = isset( $quiz_settings['quiz_options'] ) ? maybe_unserialize( $quiz_settings['quiz_options'] ) : array();
				$quiz_data       = array(
					'quiz_id'         => $quiz_obj->quiz_id,
					'name'            => $quiz_obj->quiz_name,
					'system'          => $quiz_options['system'],
					'views'           => $quiz_obj->quiz_views,
					'taken'           => $quiz_obj->quiz_taken,
					'author_id'       => $quiz_obj->quiz_author_id,
					'deleted'         => $quiz_obj->deleted,
					'updated'         => $quiz_obj->last_activity,
					'created'         => $quiz_obj->last_activity,
					'legacy_settings' => $quiz_settings,
				);
				foreach ( $quiz_data as $key => $value ) {
					$quiz->set_field( $key, $value );
				}
				/**
				 * Prepare Quiz Meta Data
				 */
				$other_data  = array(
					'theme_selected'      => $quiz_obj->theme_selected,
					'quiz_stye'           => $quiz_obj->quiz_stye,
					'message_after'       => $quiz_obj->message_after,
					'user_email_template' => $quiz_obj->user_email_template,
				);
				$quiz_text   = isset( $quiz_settings['quiz_text'] ) ? maybe_unserialize( $quiz_settings['quiz_text'] ) : array();
				if ( ! empty( $quiz_settings ) ) {
					unset( $quiz_settings['quiz_options'], $quiz_settings['quiz_text'] );
					foreach ( $quiz_settings as $key => $value ) {
						$other_data[ $key ] = maybe_unserialize( $value );
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
	 * Delete an object from the data store.
	 *
	 * @since 8.0
	 * @param $quiz QSM data instance.
	 * @param array $args Array of args to pass to the delete method.
	 */
	public function delete( &$quiz, $args = array() ) {
		global $wpdb, $mlwQuizMasterNext;
		$id      = $quiz->get_id();
		$args    = wp_parse_args(
			$args, array(
				'force_delete' => false,
			)
		);
		if ( ! $id ) {
			return;
		}

		$table           = is_qsm_quizzes_migrated() ? $wpdb->prefix . 'qsm_quizzes' : $wpdb->prefix . 'mlw_quizzes';
		$quiz_post_id    = $wpdb->get_var( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key`= 'quiz_id' AND `meta_value`= '{$id}'" );
		/**
		 * Fires before deleting quiz
		 */
		do_action( 'qsm_before_delete_quiz', $id, $args );
		if ( $args['force_delete'] ) {
			$qsm_delete = $wpdb->delete( $table, array( 'quiz_id' => $id ) );
		} else {
			$qsm_delete = $wpdb->update( $table, array( 'deleted' => 1 ), array( 'quiz_id' => $id ) );
		}

		if ( $qsm_delete && ! empty( $quiz_post_id ) ) {
			if ( is_qsm_quizzes_migrated() ) {
				$wpdb->delete( $wpdb->prefix . 'qsm_meta', array(
					'object_id' => $id,
					'type'      => 'quiz',
				) );
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
			$qsm_delete = $wpdb->update( $table, array( 'deleted' => 1 ), array( 'quiz_id' => $id ) );
		}

		if ( $qsm_delete && ! empty( $quiz_post_id ) ) {
			if ( is_qsm_quizzes_migrated() ) {
				$wpdb->delete( $wpdb->prefix . 'qsm_meta', array(
					'object_id' => $id,
					'type'      => 'question',
				) );
			}

			/**
			 * Fires after question is deleted.
			 */
			do_action( 'qsm_quiz_questions_deleted', $id, $force_delete );
		} else {
			$mlwQuizMasterNext->log_manager->add( 'Error 0002', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}

	/**
	 * Create quiz post
	 * @global type $wpdb
	 * @param type $quiz
	 * @return post ID
	 */
	protected function save_quiz_post_type( &$quiz ) {
		global $wpdb;
		$quiz_post_id = $wpdb->get_var( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key`= 'quiz_id' AND `meta_value`= '{$quiz->get_id()}'" );
		if ( ! $quiz_post_id ) {
			$quiz_post       = array(
				'post_title'   => $quiz->get_name(),
				'post_content' => "[mlw_quizmaster quiz={$quiz->get_id()}]",
				'post_status'  => 'draft',
				'post_author'  => $quiz->get_author_id(),
				'post_type'    => 'qsm_quiz',
			);
			$quiz_post_id    = wp_insert_post( $quiz_post );
			add_post_meta( $quiz_post_id, 'quiz_id', $quiz->get_id() );
		} else {
			$quiz_post       = array(
				'ID'         => $quiz_post_id,
				'post_title' => $quiz->get_name(),
				'post_type'  => 'qsm_quiz',
			);
			$quiz_post_id    = wp_update_post( $quiz_post );
		}

		return $quiz_post_id;
	}

	/**
	 * Helper method that updates all the meta for an object based on it's settings in the class.
	 *
	 * @since 8.0
	 * @param QSM_Quiz $quiz QSM data instance.
	 */
	protected function update_quiz_meta( &$quiz, $force = false ) {
		$quiz_meta       = $quiz->get_settings();
		$changes         = $quiz->get_changes();
		$changed_metas   = array_key_exists( 'settings', $changes ) ? $changes['settings'] : array();
		if ( ! empty( $quiz_meta ) ) {
			foreach ( $quiz_meta as $meta_key => $meta_value ) {
				if ( $force || array_key_exists( $meta_key, $changed_metas ) ) {
					$meta_value = maybe_unserialize( $meta_value );
					update_qsm_meta( $quiz->get_id(), $meta_key, $meta_value, 'quiz' );
				}
			}
		}
	}
	
	/**
	 * Create quiz in old database table.
	 *
	 * @param $quiz QSM data instance.
	 */
	protected function legacy_create( &$quiz ) {
		global $wpdb, $mlwQuizMasterNext;
		$default_texts   = QMNPluginHelper::get_default_texts();
		$quiz_settings   = array(
			'quiz_options' => $quiz->get_settings(),
			'quiz_text'    => $default_texts,
		);
		/**
		 * Preapare Quiz Data
		 */
		$quiz_data       = array(
			'quiz_name'                => $quiz->get_name(),
			'message_before'           => isset( $default_texts['message_before'] ) ? $default_texts['message_before'] : __( 'Welcome to your %QUIZ_NAME%', 'quiz-master-next' ),
			'message_after'            => $quiz->get_field( 'message_after', 'settings' ),
			'message_comment'          => isset( $default_texts['message_comment'] ) ? $default_texts['message_comment'] : __( 'Please fill in the comment box below.', 'quiz-master-next' ),
			'message_end_template'     => '',
			'user_email_template'      => $quiz->get_field( 'user_email_template', 'settings' ),
			'admin_email_template'     => $quiz->get_field( 'user_email_template', 'settings' ),
			'submit_button_text'       => isset( $default_texts['submit_button_text'] ) ? $default_texts['submit_button_text'] : __( 'Submit', 'quiz-master-next' ),
			'name_field_text'          => isset( $default_texts['name_field_text'] ) ? $default_texts['name_field_text'] : __( 'Name', 'quiz-master-next' ),
			'business_field_text'      => isset( $default_texts['business_field_text'] ) ? $default_texts['business_field_text'] : __( 'Business', 'quiz-master-next' ),
			'email_field_text'         => isset( $default_texts['email_field_text'] ) ? $default_texts['email_field_text'] : __( 'Email', 'quiz-master-next' ),
			'phone_field_text'         => isset( $default_texts['phone_field_text'] ) ? $default_texts['phone_field_text'] : __( 'Phone Number', 'quiz-master-next' ),
			'comment_field_text'       => isset( $default_texts['comment_field_text'] ) ? $default_texts['comment_field_text'] : __( 'Comments', 'quiz-master-next' ),
			'email_from_text'          => 'Wordpress',
			'question_answer_template' => isset( $default_texts['question_answer_template'] ) ? $default_texts['question_answer_template'] : '%QUESTION%<br />%USER_ANSWERS_DEFAULT%',
			'leaderboard_template'     => '',
			'quiz_system'              => 0,
			'randomness_order'         => 0,
			'loggedin_user_contact'    => 0,
			'show_score'               => 0,
			'send_user_email'          => 0,
			'send_admin_email'         => 0,
			'contact_info_location'    => 0,
			'user_name'                => 2,
			'user_comp'                => 2,
			'user_email'               => 2,
			'user_phone'               => 2,
			'admin_email'              => get_option( 'admin_email', 'Enter email' ),
			'comment_section'          => 1,
			'question_from_total'      => 0,
			'total_user_tries'         => 0,
			'total_user_tries_text'    => isset( $default_texts['total_user_tries_text'] ) ? $default_texts['total_user_tries_text'] : __( 'You have utilized all of your attempts to pass this quiz.', 'quiz-master-next' ),
			'certificate_template'     => '',
			'social_media'             => 0,
			'social_media_text'        => '',
			'pagination'               => 0,
			'pagination_text'          => isset( $default_texts['next_button_text'] ) ? $default_texts['next_button_text'] : __( 'Next', 'quiz-master-next' ),
			'timer_limit'              => 0,
			'quiz_stye'                => '',
			'question_numbering'       => 0,
			'quiz_settings'            => maybe_serialize( $quiz_settings ),
			'theme_selected'           => 'primary',
			'last_activity'            => current_time( 'mysql' ),
			'require_log_in'           => 0,
			'require_log_in_text'      => isset( $default_texts['require_log_in_text'] ) ? $default_texts['require_log_in_text'] : __( 'This quiz is for logged in users only.', 'quiz-master-next' ),
			'limit_total_entries'      => 0,
			'limit_total_entries_text' => isset( $default_texts['limit_total_entries_text'] ) ? $default_texts['limit_total_entries_text'] : __( 'Unfortunately, this quiz has a limited amount of entries it can recieve and has already reached that limit.', 'quiz-master-next' ),
			'scheduled_timeframe'      => '',
			'scheduled_timeframe_text' => '',
			'quiz_views'               => 0,
			'quiz_taken'               => 0,
			'deleted'                  => 0,
			'quiz_author_id'           => $quiz->get_author_id(),
		);
		$results         = $wpdb->insert( $wpdb->prefix . 'mlw_quizzes', $quiz_data );
		if ( $results ) {
			$quiz_id = $wpdb->insert_id;
			$quiz->set_id( $quiz_id );
			$this->save_quiz_post_type( $quiz );
			
			/**
			 * Activate quiz theme
			 */
			$mlwQuizMasterNext->theme_settings->activate_selected_theme( $quiz_id, $quiz->get_field( 'theme_id', 'settings' ) );
			
			/**
			 * Fires after quiz is created
			 */
			do_action( 'qsm_quiz_created', $quiz->get_id(), $quiz );
		} else {
			$mlwQuizMasterNext->log_manager->add( 'Error 0001', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}
	
	/**
	 * Update quiz in old database table.
	 *
	 * @param $quiz QSM data instance.
	 */
	protected function legacy_update( &$quiz ) {
		global $wpdb, $mlwQuizMasterNext;
		$new_settings    = $quiz->get_settings();
		$quiz_settings   = $quiz->get_legacy_settings();
		$quiz_options    = isset( $quiz_settings['quiz_options'] ) ? maybe_unserialize( $quiz_settings['quiz_options'] ) : array();
		$quiz_text       = isset( $quiz_settings['quiz_text'] ) ? maybe_unserialize( $quiz_settings['quiz_text'] ) : array();
		
		/**
		 * Prepare quiz setting array
		 */
		foreach ( $new_settings as $key => $value ) {
			if ( array_key_exists( $key, $quiz_options ) ) {
				$quiz_settings['quiz_options'][ $key ] = $value;
			} elseif ( array_key_exists( $key, $quiz_text ) ) {
				$quiz_settings['quiz_text'][ $key ] = $value;
			} else {
				$quiz_settings[ $key ] = $value;
			}
		}

		$quiz_data = array(
			'quiz_name'           => $quiz->get_name(),
			'quiz_views'          => $quiz->get_views(),
			'quiz_taken'          => $quiz->get_taken(),
			'message_after'       => $quiz->get_field( 'message_after', 'settings' ),
			'user_email_template' => $quiz->get_field( 'user_email_template', 'settings' ),
			'quiz_stye'           => $quiz->get_field( 'quiz_stye', 'settings' ),
			'theme_selected'      => $quiz->get_field( 'theme_selected', 'settings', 'primary' ),
			'quiz_settings'       => maybe_serialize( $quiz_settings ),
			'deleted'             => $quiz->is_deleted(),
			'last_activity'       => current_time( 'mysql' ),
		);
		$wpdb->update( $wpdb->prefix . 'mlw_quizzes', $quiz_data, array( 'quiz_id', $quiz->get_id() ) );
		
		/**
		 * Fires after quiz is updated
		 */
		do_action( 'qsm_quiz_updated', $quiz->get_id(), $quiz );
	}

}
