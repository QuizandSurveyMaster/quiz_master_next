<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QMN Quiz Creator Class
 *
 * This class handles quiz creation, update, and deletion from the admin panel
 *
 * The Quiz Creator class handles all the quiz management functions that is done from the admin panel
 *
 * @since 3.7.1
 */
class QMNQuizCreator {



	/**
	 * QMN ID of quiz
	 *
	 * @var   object
	 * @since 3.7.1
	 */
	private $quiz_id;

	/**
	 * If the quiz ID is set, store it as the class quiz ID
	 *
	 * @since 3.7.1
	 */
	public function __construct() {
		if ( isset( $_REQUEST['quiz_id'] ) ) {
			$this->quiz_id = intval( $_REQUEST['quiz_id'] );
		}
	}

	/**
	 * Sets quiz ID
	 *
	 * @since  3.8.1
	 * @param  int $quiz_id The ID of the quiz.
	 * @access public
	 * @return void
	 */
	public function set_id( $quiz_id ) {
		$this->quiz_id = intval( $quiz_id );
	}

	/**
	 * Gets the quiz ID stored (for backwards compatibility)
	 *
	 * @since  5.0.0
	 * @return int|false The ID of the quiz stored or false
	 */
	public function get_id() {
		if ( $this->quiz_id ) {
			return intval( $this->quiz_id );
		} else {
			return false;
		}
	}

	/**
	 * Creates a new quiz with the default settings
	 *
	 * @access public
	 * @since  3.7.1
	 * @param  string $quiz_name The name of the new quiz.
	 * @return void
	 */
	public function create_quiz( $quiz_name, $theme_id, $quiz_settings = array() ) {
		global $mlwQuizMasterNext;
		global $wpdb;
		$current_user = wp_get_current_user();
		$results      = $wpdb->insert(
			$wpdb->prefix . 'mlw_quizzes',
			array(
				'quiz_name'                => $quiz_name,
				'message_before'           => __( 'Welcome to your %QUIZ_NAME%', 'quiz-master-next' ),
				'message_after'            => __( 'Thanks for submitting your response! You can edit this message on the "Results Pages" tab. <br>%CONTACT_ALL% <br>%QUESTIONS_ANSWERS%', 'quiz-master-next' ),
				'message_comment'          => __( 'Please fill in the comment box below.', 'quiz-master-next' ),
				'message_end_template'     => '',
				'user_email_template'      => '%QUESTIONS_ANSWERS_EMAIL%',
				'admin_email_template'     => '%QUESTIONS_ANSWERS_EMAIL%',
				'submit_button_text'       => __( 'Submit', 'quiz-master-next' ),
				'name_field_text'          => __( 'Name', 'quiz-master-next' ),
				'business_field_text'      => __( 'Business', 'quiz-master-next' ),
				'email_field_text'         => __( 'Email', 'quiz-master-next' ),
				'phone_field_text'         => __( 'Phone Number', 'quiz-master-next' ),
				'comment_field_text'       => __( 'Comments', 'quiz-master-next' ),
				'email_from_text'          => 'Wordpress',
				'question_answer_template' => '%QUESTION%<br />%USER_ANSWERS_DEFAULT%',
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
				'total_user_tries_text'    => __( 'You have utilized all of your attempts to pass this quiz.', 'quiz-master-next' ),
				'certificate_template'     => '',
				'social_media'             => 0,
				'social_media_text'        => __( 'I just scored %CORRECT_SCORE%% on %QUIZ_NAME%!', 'quiz-master-next' ),
				'pagination'               => 0,
				'pagination_text'          => __( 'Next', 'quiz-master-next' ),
				'timer_limit'              => 0,
				'quiz_stye'                => '',
				'question_numbering'       => 0,
				'quiz_settings'            => maybe_serialize( $quiz_settings ),
				'theme_selected'           => 'primary',
				'last_activity'            => current_time( 'mysql' ),
				'require_log_in'           => 0,
				'require_log_in_text'      => __( 'This quiz is for logged in users only.', 'quiz-master-next' ),
				'limit_total_entries'      => 0,
				'limit_total_entries_text' => __( 'Unfortunately, this quiz has a limited amount of entries it can recieve and has already reached that limit.', 'quiz-master-next' ),
				'scheduled_timeframe'      => '',
				'scheduled_timeframe_text' => '',
				'quiz_views'               => 0,
				'quiz_taken'               => 0,
				'deleted'                  => 0,
				'quiz_author_id'           => $current_user->ID,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
			)
		);
		if ( false !== $results ) {
			$new_quiz     = $wpdb->insert_id;
			$quiz_post    = array(
				'post_title'   => $quiz_name,
				'post_content' => "[mlw_quizmaster quiz=$new_quiz]",
				'post_status'  => 'draft',
				'post_author'  => $current_user->ID,
				'post_type'    => 'qsm_quiz',
			);
			$quiz_post_id = wp_insert_post( $quiz_post );
			add_post_meta( $quiz_post_id, 'quiz_id', $new_quiz );

			// activating selected theme
			$mlwQuizMasterNext->theme_settings->activate_selected_theme( $new_quiz, $theme_id );

			$mlwQuizMasterNext->alertManager->newAlert( __( 'Your new quiz or survey has been created successfully. To begin editing, click the Edit link.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( 'New Quiz/Survey Has Been Created', $new_quiz, '' );

			// Hook called after new quiz or survey has been created. Passes quiz_id to hook
			do_action( 'qmn_quiz_created', $new_quiz );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'There has been an error in this action. Please share this with the developer. Error Code: 0001', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error 0001', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}

	/**
	 * Deletes a quiz with the given quiz_id
	 *
	 * @access public
	 * @since  3.7.1
	 * @return void
	 */
	public function delete_quiz( $quiz_id, $quiz_name ) {
		global $mlwQuizMasterNext;
		global $wpdb;

		$qsm_delete_from_db           = isset( $_POST['qsm_delete_from_db'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['qsm_delete_from_db'] ) );
		$qsm_delete_questions_from_qb = isset( $_POST['qsm_delete_question_from_qb'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['qsm_delete_question_from_qb'] ) );

		$quiz_post_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'quiz_id' AND meta_value = '$quiz_id'" );
		if ( empty( $quiz_post_id ) || ! current_user_can( 'delete_post', $quiz_post_id ) ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Sorry, you are not allowed to delete this quiz.', 'quiz-master-next' ), 'error' );
			return;
		}

		if ( $qsm_delete_from_db ) {
			$qsm_delete = $wpdb->delete(
				$wpdb->prefix . 'mlw_quizzes',
				array( 'quiz_id' => $quiz_id )
			);
			if ( $qsm_delete_questions_from_qb ) {
				$wpdb->delete(
					$wpdb->prefix . 'mlw_quizzes',
					array( 'quiz_id' => $quiz_id )
				);
			}
		} else {
			$qsm_delete = $wpdb->update(
				$wpdb->prefix . 'mlw_quizzes',
				array(
					'deleted' => 1,
				),
				array( 'quiz_id' => $quiz_id ),
				array(
					'%d',
				),
				array( '%d' )
			);
			$deleted    = 0;
			if ( $qsm_delete_questions_from_qb ) {
				$deleted = 1;
				$wpdb->update(
					$wpdb->prefix . 'mlw_questions',
					array(
						'deleted' => $deleted,
					),
					array( 'quiz_id' => $quiz_id ),
					array(
						'%d',
					),
					array( '%d' )
				);
			}
		}

		if ( $qsm_delete && ! empty( $quiz_post_id ) ) {
			wp_trash_post( $quiz_post_id );
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Your quiz or survey has been deleted successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Quiz/Survey Has Been Deleted: $quiz_name", $quiz_id, '' );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'There has been an error in this action. Please share this with the developer. Error Code: 0002', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error 0002', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
		// Hook called after quiz or survey is deleted. Hook passes quiz_id to function
		do_action( 'qmn_quiz_deleted', $quiz_id );
	}

	/**
	 * Edits the name of the quiz with the given ID
	 *
	 * @access public
	 * @since  3.7.1
	 * @param  int    $quiz_id   The ID of the quiz.
	 * @param  string $quiz_name The new name of the quiz.
	 * @return void
	 */
	public function edit_quiz_name( $quiz_id, $quiz_name ) {
		global $mlwQuizMasterNext;
		global $wpdb;
		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array(
				'quiz_name' => $quiz_name,
			),
			array( 'quiz_id' => $quiz_id ),
			array(
				'%s',
			),
			array( '%d' )
		);
		if ( false !== $results ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The name of your quiz or survey has been updated successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( 'Quiz/Survey Name Has Been Edited', $quiz_id, '' );
		} else {
			$error = $wpdb->last_error;
			if ( empty( $error ) ) {
				$error = __( 'Unknown error', 'quiz-master-next' );
			}
			$mlwQuizMasterNext->alertManager->newAlert( __( 'An error occurred while trying to update the name of your quiz or survey. Please try again.', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error when updating quiz name', "Tried {$wpdb->last_query} but got $error", 0, 'error' );
		}

		// Fires when the name of a quiz/survey is edited.
		do_action( 'qsm_quiz_name_edited', $quiz_id, $quiz_name );

		// Legacy code.
		do_action( 'qmn_quiz_name_edited', $quiz_id );
	}

	/**
	 * Duplicates the quiz with the given ID and gives new quiz the given quiz name
	 *
	 * @access public
	 * @since  3.7.1
	 * @return void
	 */
	public function duplicate_quiz( $quiz_id, $quiz_name, $is_duplicating_questions ) {
		global $mlwQuizMasterNext;
		global $wpdb;

		$quiz_post_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'quiz_id' AND meta_value = '$quiz_id'" );
		if ( empty( $quiz_post_id ) || ! current_user_can( 'edit_post', $quiz_post_id ) ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Sorry, you are not allowed to duplicate this quiz.', 'quiz-master-next' ), 'error' );
			return;
		}

		$current_user           = wp_get_current_user();
		$table_name             = $wpdb->prefix . 'mlw_quizzes';
		$logic_table            = $wpdb->prefix . 'mlw_logic';
		$question_term          = $wpdb->prefix . 'mlw_question_terms';
		$logic_table_exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $logic_table ) );
		$question_term_exists   = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $question_term ) );
		$mlw_qmn_duplicate_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE quiz_id=%d", $quiz_id ) );
		$quiz_settings          = maybe_unserialize( $mlw_qmn_duplicate_data->quiz_settings );
		if ( 0 == $is_duplicating_questions ) {
			$quiz_settings['pages'] = '';
		}
		$qsm_create_quiz_system = 0;
		if ( isset( $mlw_qmn_duplicate_data->system ) ) {
			$qsm_create_quiz_system = $mlw_qmn_duplicate_data->system;
		} elseif ( isset( $mlw_qmn_duplicate_data->quiz_system ) ) {
			$qsm_create_quiz_system = $mlw_qmn_duplicate_data->quiz_system;
		}
		$results    = $wpdb->insert(
			$table_name,
			array(
				'quiz_name'                => $quiz_name,
				'message_before'           => $mlw_qmn_duplicate_data->message_before,
				'message_after'            => $mlw_qmn_duplicate_data->message_after,
				'message_comment'          => $mlw_qmn_duplicate_data->message_comment,
				'message_end_template'     => $mlw_qmn_duplicate_data->message_end_template,
				'user_email_template'      => $mlw_qmn_duplicate_data->user_email_template,
				'admin_email_template'     => $mlw_qmn_duplicate_data->admin_email_template,
				'submit_button_text'       => $mlw_qmn_duplicate_data->submit_button_text,
				'name_field_text'          => $mlw_qmn_duplicate_data->name_field_text,
				'business_field_text'      => $mlw_qmn_duplicate_data->business_field_text,
				'email_field_text'         => $mlw_qmn_duplicate_data->email_field_text,
				'phone_field_text'         => $mlw_qmn_duplicate_data->phone_field_text,
				'comment_field_text'       => $mlw_qmn_duplicate_data->comment_field_text,
				'email_from_text'          => $mlw_qmn_duplicate_data->email_from_text,
				'question_answer_template' => $mlw_qmn_duplicate_data->question_answer_template,
				'leaderboard_template'     => $mlw_qmn_duplicate_data->leaderboard_template,
				'quiz_system'              => $qsm_create_quiz_system,
				'randomness_order'         => $mlw_qmn_duplicate_data->randomness_order,
				'loggedin_user_contact'    => $mlw_qmn_duplicate_data->loggedin_user_contact,
				'show_score'               => $mlw_qmn_duplicate_data->show_score,
				'send_user_email'          => $mlw_qmn_duplicate_data->send_user_email,
				'send_admin_email'         => $mlw_qmn_duplicate_data->send_admin_email,
				'contact_info_location'    => $mlw_qmn_duplicate_data->contact_info_location,
				'user_name'                => $mlw_qmn_duplicate_data->user_name,
				'user_comp'                => $mlw_qmn_duplicate_data->user_comp,
				'user_email'               => $mlw_qmn_duplicate_data->user_email,
				'user_phone'               => $mlw_qmn_duplicate_data->user_phone,
				'admin_email'              => get_option( 'admin_email', 'Enter email' ),
				'comment_section'          => $mlw_qmn_duplicate_data->comment_section,
				'question_from_total'      => $mlw_qmn_duplicate_data->question_from_total,
				'total_user_tries'         => $mlw_qmn_duplicate_data->total_user_tries,
				'total_user_tries_text'    => $mlw_qmn_duplicate_data->total_user_tries_text,
				'certificate_template'     => $mlw_qmn_duplicate_data->certificate_template,
				'social_media'             => $mlw_qmn_duplicate_data->social_media,
				'social_media_text'        => $mlw_qmn_duplicate_data->social_media_text,
				'pagination'               => $mlw_qmn_duplicate_data->pagination,
				'pagination_text'          => $mlw_qmn_duplicate_data->pagination_text,
				'timer_limit'              => $mlw_qmn_duplicate_data->timer_limit,
				'quiz_stye'                => $mlw_qmn_duplicate_data->quiz_stye,
				'question_numbering'       => $mlw_qmn_duplicate_data->question_numbering,
				'quiz_settings'            => maybe_serialize( $quiz_settings ),
				'theme_selected'           => $mlw_qmn_duplicate_data->theme_selected,
				'last_activity'            => gmdate( 'Y-m-d H:i:s' ),
				'require_log_in'           => $mlw_qmn_duplicate_data->require_log_in,
				'require_log_in_text'      => $mlw_qmn_duplicate_data->require_log_in_text,
				'limit_total_entries'      => $mlw_qmn_duplicate_data->limit_total_entries,
				'limit_total_entries_text' => $mlw_qmn_duplicate_data->limit_total_entries_text,
				'scheduled_timeframe'      => $mlw_qmn_duplicate_data->scheduled_timeframe,
				'scheduled_timeframe_text' => $mlw_qmn_duplicate_data->scheduled_timeframe_text,
				'quiz_views'               => 0,
				'quiz_taken'               => 0,
				'deleted'                  => 0,
				'quiz_author_id'           => $current_user->ID,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
			)
		);
		$mlw_new_id = $wpdb->insert_id;

		// Update quiz settings
		$update_quiz_settings = maybe_unserialize( $mlw_qmn_duplicate_data->quiz_settings );
		$update_pages         = maybe_unserialize( $update_quiz_settings['pages'] );
		// get logic data from logic table first or else from quiz_settings
		if ( ! is_null( $logic_table_exists ) ) {
			$query       = $wpdb->prepare( "SELECT * FROM $logic_table WHERE quiz_id = %d", $quiz_id );
			$logic_data  = $wpdb->get_results( $query );
			$logic_rules = array();
			if ( ! empty( $logic_data ) ) {
				foreach ( $logic_data as $data ) {
					$logic_rules[] = maybe_unserialize( $data->logic );
				}
			}
		} else {
			$logic_rules = isset( $update_quiz_settings['logic_rules'] ) ? maybe_unserialize( $update_quiz_settings['logic_rules'] ) : array();
		}

		if ( false !== $results ) {
			$current_user = wp_get_current_user();
			$quiz_post    = array(
				'post_title'   => $quiz_name,
				'post_content' => "[mlw_quizmaster quiz=$mlw_new_id]",
				'post_status'  => 'publish',
				'post_author'  => $current_user->ID,
				'post_type'    => 'qsm_quiz',
			);
			$quiz_post_id = wp_insert_post( $quiz_post );
			add_post_meta( $quiz_post_id, 'quiz_id', $mlw_new_id );
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Your quiz or survey has been duplicated successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( 'New Quiz/Survey Has Been Created', $mlw_new_id, '' );
			do_action( 'qmn_quiz_duplicated', $quiz_id, $mlw_new_id );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'There has been an error in this action. Please share this with the developer. Error Code: 0011', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error 0011', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
		if ( $is_duplicating_questions ) {
			$table_name = $wpdb->prefix . 'mlw_questions';
			$questions  = array();
			if ( is_array( $update_pages ) ) {
				foreach ( $update_pages as $ids ) {
					foreach ( $ids as $id ) {
						$questions[] = $id;
					}
				}
			}
			$question_ids          = implode( ',', $questions );
			$mlw_current_questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE question_id IN (%1s)", $question_ids ) );
			foreach ( $mlw_current_questions as $mlw_question ) {
				$question_results = $wpdb->insert(
					$table_name,
					array(
						'quiz_id'              => $mlw_new_id,
						'question_name'        => $mlw_question->question_name,
						'answer_array'         => $mlw_question->answer_array,
						'answer_one'           => $mlw_question->answer_one,
						'answer_one_points'    => $mlw_question->answer_one_points,
						'answer_two'           => $mlw_question->answer_two,
						'answer_two_points'    => $mlw_question->answer_two_points,
						'answer_three'         => $mlw_question->answer_three,
						'answer_three_points'  => $mlw_question->answer_three_points,
						'answer_four'          => $mlw_question->answer_four,
						'answer_four_points'   => $mlw_question->answer_four_points,
						'answer_five'          => $mlw_question->answer_five,
						'answer_five_points'   => $mlw_question->answer_five_points,
						'answer_six'           => $mlw_question->answer_six,
						'answer_six_points'    => $mlw_question->answer_six_points,
						'correct_answer'       => $mlw_question->correct_answer,
						'question_answer_info' => $mlw_question->question_answer_info,
						'comments'             => $mlw_question->comments,
						'hints'                => $mlw_question->hints,
						'question_order'       => $mlw_question->question_order,
						'question_type_new'    => $mlw_question->question_type_new,
						'question_settings'    => $mlw_question->question_settings,
						'category'             => $mlw_question->category,
						'deleted'              => 0,
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%d',
						'%s',
						'%d',
						'%s',
						'%d',
						'%s',
						'%d',
						'%s',
						'%d',
						'%d',
						'%s',
						'%d',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%d',
					)
				);
				foreach ( $update_pages as $pages_key => $pages_value ) {
					foreach ( $pages_value as $pages_k_q => $page_q_id ) {
						if ( $page_q_id === $mlw_question->question_id ) {
							$update_pages[ $pages_key ][ $pages_k_q ] = $wpdb->insert_id;
						}
					}
				}
				// Fixed Rules Questions with new question ids
				if ( $logic_rules ) {
					foreach ( $logic_rules as $logic_key => $logic_value ) {
						foreach ( $logic_value as $logic_cond_k => $logic_cond ) {
							foreach ( $logic_cond as $l_cond_k => $logic_val ) {
								if ( $logic_val['question'] === $mlw_question->question_id ) {
									$logic_rules[ $logic_key ][ $logic_cond_k ][ $l_cond_k ]['question'] = $wpdb->insert_id;
								}
							}
						}
					}
				}
				// Copying categories for multiple categories table
				$new_question_id = $wpdb->insert_id;
				if ( ! is_null( $question_term_exists ) ) {
					$query    = $wpdb->prepare( "SELECT DISTINCT term_id FROM $question_term WHERE question_id = %d AND quiz_id = %d", $mlw_question->question_id, $quiz_id );
					$term_ids = $wpdb->get_results( $query, ARRAY_N );

					if ( ! is_null( $term_ids ) ) {
						foreach ( $term_ids as $term_id ) {
							$wpdb->insert(
								$question_term,
								array(
									'question_id' => $new_question_id,
									'quiz_id'     => $mlw_new_id,
									'term_id'     => $term_id[0],
									'taxonomy'    => 'qsm_category',
								),
								array(
									'%d',
									'%d',
									'%d',
									'%s',
								)
							);
						}
					}
				}

				if ( false === $question_results ) {
					$mlwQuizMasterNext->alertManager->newAlert( __( 'There has been an error in this action. Please share this with the developer. Error Code: 0020', 'quiz-master-next' ), 'error' );
					$mlwQuizMasterNext->log_manager->add( 'Error 0020', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
				}
			}
			$update_quiz_settings['pages'] = maybe_serialize( $update_pages );
			// saves data in logic table first or else in quiz_settings.
			$value_array = array();
			if ( is_array( $logic_rules ) && ! empty( $logic_rules ) ) {
				if ( is_null( $logic_table_exists ) ) {
					$update_quiz_settings['logic_rules'] = maybe_serialize( $logic_rules );
				} else {
					foreach ( $logic_rules as $logic_data ) {
						$data          = array(
							$mlw_new_id,
							maybe_serialize( $logic_data ),
						);
						$value_array[] = stripslashes( $wpdb->prepare( '(%d, %s)', $data ) );
					}
					$values = implode( ',', $value_array );
					$query  = "INSERT INTO $logic_table (quiz_id, logic) VALUES ";
					$query .= $values;
					$saved  = $wpdb->query( $query );
					if ( false !== $saved ) {
						update_option( "logic_rules_quiz_$mlw_new_id", gmdate( time() ) );
						$update_quiz_settings['logic_rules'] = '';
					} else {
						$update_quiz_settings['logic_rules'] = maybe_serialize( $logic_rules );
					}
				}
			}

			$wpdb->update(
				$wpdb->prefix . 'mlw_quizzes',
				array(
					'quiz_settings' => maybe_serialize( $update_quiz_settings ),
				),
				array(
					'quiz_id' => $mlw_new_id,
				)
			);
		}
	}

	/**
	 * Retrieves setting store in quiz_settings
	 *
	 * @deprecated 6.0.3 Use the get_quiz_setting function in the pluginHelper object.
	 * @since      3.8.1
	 * @access     public
	 * @param      string $setting_name The slug of the setting.
	 * @return     string The value of the setting
	 */
	public function get_setting( $setting_name ) {
		global $wpdb;
		$qmn_settings_array = '';
		$qmn_quiz_settings  = $wpdb->get_var( $wpdb->prepare( 'SELECT quiz_settings FROM ' . $wpdb->prefix . 'mlw_quizzes' . ' WHERE quiz_id=%d', $this->quiz_id ) );
		$qmn_settings_array = maybe_unserialize( $qmn_quiz_settings );
		if ( is_array( $qmn_settings_array ) && isset( $qmn_settings_array[ $setting_name ] ) ) {
			return $qmn_settings_array[ $setting_name ];
		} else {
			return '';
		}

	}

	/**
	 * Updates setting stored in quiz_settings
	 *
	 * @deprecated 6.0.3 Use the update_quiz_setting function in the pluginHelper object.
	 * @since      3.8.1
	 * @access     public
	 * @param      string $setting_name  The slug of the setting.
	 * @param      mixed  $setting_value The value for the setting.
	 * @return     bool True if update was successful
	 */
	public function update_setting( $setting_name, $setting_value ) {
		global $wpdb;
		$qmn_quiz_settings  = $wpdb->get_var( $wpdb->prepare( 'SELECT quiz_settings FROM ' . $wpdb->prefix . 'mlw_quizzes' . ' WHERE quiz_id=%d', $this->quiz_id ) );
		$qmn_settings_array = maybe_unserialize( $qmn_quiz_settings );
		if ( ! is_array( $qmn_settings_array ) ) {
			$qmn_settings_array = array();
		}
		$qmn_settings_array[ $setting_name ] = $setting_value;
		$results                             = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array(
				'quiz_settings' => maybe_serialize( $qmn_settings_array ),
			),
			array( 'quiz_id' => $this->quiz_id ),
			array(
				'%s',
			),
			array( '%d' )
		);
		if ( false !== $results ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Deletes setting stored in quiz_settings
	 *
	 * @deprecated 6.0.3
	 * @since      3.8.1
	 * @access     public
	 * @return     void
	 */
	public function delete_setting( $setting_name ) {
		global $wpdb;
		$qmn_quiz_settings  = $wpdb->get_var( $wpdb->prepare( 'SELECT quiz_settings FROM ' . $wpdb->prefix . 'mlw_quizzes' . ' WHERE quiz_id=%d', $this->quiz_id ) );
		$qmn_settings_array = maybe_unserialize( $qmn_quiz_settings );
		if ( is_array( $qmn_settings_array ) && isset( $qmn_settings_array[ $setting_name ] ) ) {
			unset( $qmn_settings_array[ $setting_name ] );
		}
		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array(
				'quiz_settings' => maybe_serialize( $qmn_settings_array ),
			),
			array( 'quiz_id' => $this->quiz_id ),
			array(
				'%s',
			),
			array( '%d' )
		);
	}
}
