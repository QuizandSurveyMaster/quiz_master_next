<?php
/**
 * File that contains class for questions.
 *
 * @package QSM
 */

/**
 * Class that handles all creating, saving, and deleting of questions.
 *
 * @since 5.2.0
 */
class QSM_Questions {

	/**
	 * Loads single question using question ID
	 *
	 * @since 5.2.0
	 * @param int $question_id The ID of the question.
	 * @return array The data for the question.
	 */
	public static function load_question( $question_id ) {
		global $wpdb;
		$question_id = intval( $question_id );
		$question = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE question_id = %d LIMIT 1", $question_id ), 'ARRAY_A' );
		if ( ! is_null( $question ) ) {
			// Prepare answers.
			$answers = maybe_unserialize( $question['answer_array'] );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
			$question['answers'] = $answers;

			$settings = maybe_unserialize( $question['question_settings'] );
			if ( ! is_array( $settings ) ) {
				$settings = array( 'required' => 1 );
			}
			$question['settings'] = $settings;

			return apply_filters('qsm_load_question',$question, $question_id);
		}
		return array();
	}

	/**
	 * Loads questions for a quiz using the new page system
	 *
	 * @since 5.2.0
	 * @param int $quiz_id The ID of the quiz.
	 * @return array The array of questions.
	 */
	public static function load_questions_by_pages( $quiz_id ) {

		// Prepares our variables.
		global $wpdb;
		global $mlwQuizMasterNext;
		$quiz_id      = intval( $quiz_id );
		$question_ids = array();
		$questions    = array();
		$page_for_ids = array();

		// Gets the pages for the quiz.
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
		$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );

		// Get all question IDs needed.
		$total_pages = count( $pages );
		for ( $i = 0; $i < $total_pages; $i++ ) {
			foreach ( $pages[ $i ] as $question ) {
				$question_id                  = intval( $question );
				$question_ids[]               = $question_id;
				$page_for_ids[ $question_id ] = $i;
			}
		}

		// If we have any question IDs, get the questions.
		if ( count( $question_ids ) > 0 ) {

			$question_sql = implode( ', ', $question_ids );

			// Get all questions.
			$question_array = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE question_id IN ($question_sql)", 'ARRAY_A' );

			// Loop through questions and prepare serialized data.
			foreach ( $question_array as $question ) {

				// Prepare answers.
				$answers = maybe_unserialize( $question['answer_array'] );
				if ( ! is_array( $answers ) ) {
					$answers = array();
				}
				$question['answers'] = $answers;

				// Prepares settings.
				$settings = maybe_unserialize( $question['question_settings'] );
				if ( ! is_array( $settings ) ) {
					$settings = array( 'required' => 1 );
				}
				$question['settings'] = $settings;

				// Get the page.
				$question_id      = intval( $question['question_id'] );
				$question['page'] = intval( $page_for_ids[ $question_id ] );

				$questions[ $question_id ] = $question;
			}
		} else {
			// If we do not have pages on this quiz yet, use older load_questions and add page to them.
			$questions = self::load_questions( $quiz_id );
			foreach ( $questions as $key => $question ) {
				$questions[ $key ]['page'] = isset( $question['page'] ) ? $question['page'] : 0;
			}
		}
		return apply_filters('qsm_load_questions_by_pages',$questions,$quiz_id);
	}

	/**
	 * Loads questions for a quiz
	 *
	 * @since 5.2.0
	 * @param int $quiz_id The ID of the quiz.
	 * @return array The array of questions.
	 */
	public static function load_questions( $quiz_id ) {

		global $wpdb;
		$question_array = array();

		// Get all questions.
		if ( 0 !== $quiz_id ) {
			$quiz_id = intval( $quiz_id );
			$questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE quiz_id=%d AND deleted='0' ORDER BY question_order ASC", $quiz_id ), 'ARRAY_A' );
		} else {
			$questions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted='0' ORDER BY question_order ASC", 'ARRAY_A' );
		}

		// Loop through questions and prepare serialized data.
		foreach ( $questions as $question ) {

			// Prepare answers.
			$answers = maybe_unserialize( $question['answer_array'] );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
			$question['answers'] = $answers;

			$settings = maybe_unserialize( $question['question_settings'] );
			if ( ! is_array( $settings ) ) {
				$settings = array( 'required' => 1 );
			}
			$question['settings'] = $settings;

			$question_array[ $question['question_id'] ] = $question;
		}
		return apply_filters('qsm_load_questions',$question_array,$quiz_id);
	}

	/**
	 * Creates a new question
	 *
	 * @since 5.2.0
	 * @param array $data The question data.
	 * @param array $answers The answers for the question.
	 * @param array $settings Any settings for the question.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return int The ID of the question that was created.
	 */
	public static function create_question( $data, $answers = array(), $settings = array() ) {
		return self::create_save_question( $data, $answers, $settings );
	}

	/**
	 * Saves a question
	 *
	 * @since 5.2.0
	 * @param int   $question_id The ID of the question to be saved.
	 * @param array $data The question data.
	 * @param array $answers The answers for the question.
	 * @param array $settings Any settings for the question.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return int The ID of the question that was saved.
	 */
	public static function save_question( $question_id, $data, $answers = array(), $settings = array() ) {
		$data['ID'] = intval( $question_id );
		return self::create_save_question( $data, $answers, $settings, false );
	}

	/**
	 * Deletes a question
	 *
	 * @since 5.2.0
	 * @param int $question_id The ID for the question.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return bool True if successful
	 */
	public static function delete_question( $question_id ) {
		global $wpdb;
		
		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_questions',
			array(
				'deleted' => 1,
			),
			array( 'question_id' => intval( $question_id ) ),
			array(
				'%d',
			),
			array( '%d' )
		);

		if ( false === $results ) {
			$msg = $wpdb->last_error . ' from ' . $wpdb->last_query;
			$mlwQuizMasterNext->log_manager->add( 'Error when deleting question', $msg, 0, 'error' );
			throw new Exception( $msg );
		}

		return true;
	}

	/**
	 * Creates or saves a question
	 *
	 * This is used internally. Use create_question or save_question instead.
	 *
	 * @since 5.2.0
	 * @param array $data The question data.
	 * @param array $answers The answers for the question.
	 * @param array $settings Any settings for the question.
	 * @param bool  $is_creating True if question is being created, false if being saved.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return int The ID of the question that was created/saved.
	 */
	private static function create_save_question( $data, $answers, $settings, $is_creating = true ) {
		global $wpdb;

		// Prepare defaults and parse.
		$defaults = array(
			'quiz_id'     => 0,
			'type'        => '0',
			'name'        => '',
			'answer_info' => '',
			'comments'    => '1',
			'hint'        => '',
			'order'       => 1,
			'category'    => '',
		);
		$data = wp_parse_args( $data, $defaults );

		$defaults = array(
			'required' => 1,
		);
		$settings = wp_parse_args( $settings, $defaults );

		foreach ( $answers as $key => $answer ) {
			$answers[ $key ] = array(
				htmlspecialchars( $answer[0], ENT_QUOTES ),
				floatval( $answer[1] ),
				intval( $answer[2] ),
			);
		}

		$values = array(
			'quiz_id'              => intval( $data['quiz_id'] ),
			'question_name'        => trim( preg_replace( '/\s+/', ' ', htmlspecialchars( nl2br( wp_kses_post( $data['name'] ) ), ENT_QUOTES ) ) ),
			'answer_array'         => serialize( $answers ),
			'question_answer_info' => htmlspecialchars( $data['answer_info'], ENT_QUOTES ),
			'comments'             => htmlspecialchars( $data['comments'], ENT_QUOTES ),
			'hints'                => htmlspecialchars( $data['hint'], ENT_QUOTES ),
			'question_order'       => intval( $data['order'] ),
			'question_type_new'    => sanitize_text_field( $data['type'] ),
			'question_settings'    => serialize( $settings ),
			'category'             => sanitize_text_field( $data['category'] ),
			'deleted'              => 0,
		);

		$types = array(
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
		);

		if ( $is_creating ) {
			$results = $wpdb->insert(
				$wpdb->prefix . 'mlw_questions',
				$values,
				$types
			);
		} else {
			$results = $wpdb->update(
				$wpdb->prefix . 'mlw_questions',
				$values,
				array( 'question_id' => intval( $data['ID'] ) ),
				$types,
				array( '%d' )
			);
		}

		if ( false === $results ) {
			$msg = $wpdb->last_error . ' from ' . $wpdb->last_query;
			$mlwQuizMasterNext->log_manager->add( 'Error when creating/saving question', $msg, 0, 'error' );
			throw new Exception( $msg );
		}

		if ( $is_creating ) {
			return $wpdb->insert_id;
		} else {
			return $data['ID'];
		}
	}
}
