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
	 * Loads questions for a quiz
	 *
	 * @since 5.2.0
	 * @param int $quiz_id The ID of the quiz.
	 * @return array The array of questions.
	 */
	public static function load_questions( $quiz_id ) {
		global $wpdb;

		// Get all questions.
		$questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE quiz_id=%d AND deleted='0' ORDER BY question_order ASC", $quiz_id ), 'ARRAY_A' );

		// Loop through questions and prepare serialized data.
		foreach ( $questions as $question ) {

			// Prepare answers.
			$answers = maybe_unserialize( $question['answer_array'] );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
			$questions['answers'] = $answers;

			$settings = maybe_serialize( $question['question_settings'] );
			if ( ! is_array( $settings ) ) {
				$settings = array( 'required' => 1 );
			}
			$questions['settings'] = $settings;
		}
		return $questions;
	}

	/**
	 * Creates a new question
	 *
	 * @since 5.2.0
	 * @param array $data The question data.
	 * @param array $answers The answers for the question.
	 * @param array $settings Any settings for the question.
	 * @throws Exception Throws exception if wpdb query results in error.
	 * @return bool True is successful, false if not.
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
	 * @return bool True is successful, false if not.
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
	 * @return bool True is successful, false if not.
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

		$values = array(
			'quiz_id'              => intval( $data['quiz_id'] ),
			'question_name'        => trim( preg_replace( '/\s+/', ' ', htmlspecialchars( nl2br( wp_kses_post( stripslashes( $data['name'] ) ) ), ENT_QUOTES ) ) ),
			'answer_array'         => serialize( $answers ),
			'question_answer_info' => htmlspecialchars( stripslashes( $data['answer_info'] ), ENT_QUOTES ),
			'comments'             => htmlspecialchars( stripslashes( $data['comments'] ), ENT_QUOTES ),
			'hints'                => htmlspecialchars( stripslashes( $data['hint'] ), ENT_QUOTES ),
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
			$mlwQuizMasterNext->log_manager->add( 'Error when creating question', $msg, 0, 'error' );
			throw new Exception( $msg );
		}

		return true;
	}
}
