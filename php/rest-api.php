<?php
/**
 * This file handles all of the current REST API endpoints
 *
 * @since 5.2.0
 * @package QSM
 */

add_action( 'rest_api_init', 'qsm_register_rest_routes' );

/**
 * Registers REST API endpoints
 *
 * @since 5.2.0
 */
function qsm_register_rest_routes() {
	register_rest_route( 'quiz-survey-master/v1', '/questions/', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_questions',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/questions/', array(
		'methods'  => WP_REST_Server::CREATABLE,
		'callback' => 'qsm_rest_create_question',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/questions/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::EDITABLE,
		'callback' => 'qsm_rest_save_question',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/questions/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_question',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/results', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_results',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/results', array(
		'methods'  => WP_REST_Server::EDITABLE,
		'callback' => 'qsm_rest_save_results',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/emails', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_emails',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/emails', array(
		'methods'  => WP_REST_Server::EDITABLE,
		'callback' => 'qsm_rest_save_emails',
	) );
}

/**
 * Gets emails for a quiz.
 *
 * @since 6.2.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array The emails for the quiz.
 */
function qsm_rest_get_emails( WP_REST_Request $request ) {
	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$emails = QSM_Emails::load_emails( $request['id'] );
			if ( false === $emails || ! is_array( $emails ) ) {
				$emails = array();
			}
			return $emails;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}

/**
 * Saves emails for a quiz.
 *
 * @since 6.2.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array The status of saving the emails.
 */
function qsm_rest_save_emails( WP_REST_Request $request ) {
	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$result = QSM_Emails::save_emails( $request['id'], $request['emails'] );
			return array(
				'status' => $result,
			);
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}

/**
 * Gets results pages for a quiz.
 *
 * @since 6.1.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array The pages for the quiz.
 */
function qsm_rest_get_results( WP_REST_Request $request ) {
	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$pages = QSM_Results_Pages::load_pages( $request['id'] );
			if ( false === $pages || ! is_array( $pages ) ) {
				$pages = array();
			}
			return $pages;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}

/**
 * Gets results pages for a quiz.
 *
 * @since 6.1.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array The results from saving the pages.
 */
function qsm_rest_save_results( WP_REST_Request $request ) {
	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$result = QSM_Results_Pages::save_pages( $request['id'], $request['pages'] );
			return array(
				'status' => $result,
			);
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}

/**
 * Gets a single questions
 *
 * @since 5.2.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array Something.
 */
function qsm_rest_get_question( WP_REST_Request $request ) {
	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$question = QSM_Questions::load_question( $request['id'] );
			if ( ! empty( $question ) ) {
				$question['page']  = isset( $question['page'] ) ? $question['page'] : 0;
				$question = array(
					'id'         => $question['question_id'],
					'quizID'     => $question['quiz_id'],
					'type'       => $question['question_type_new'],
					'name'       => $question['question_name'],
					'answerInfo' => $question['question_answer_info'],
					'comments'   => $question['comments'],
					'hint'       => $question['hints'],
					'category'   => $question['category'],
					'required'   => $question['settings']['required'],
					'answers'    => $question['answers'],
					'page'       => $question['page'],
				);
			}
			return $question;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}

/**
 * Gets all questions
 *
 * @since 5.2.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array Something.
 */
function qsm_rest_get_questions( WP_REST_Request $request ) {
	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$quiz_id = isset( $request['quizID'] ) ? intval( $request['quizID'] ) : 0;
			if ( 0 !== $quiz_id ) {
				$questions = QSM_Questions::load_questions_by_pages( $quiz_id );
			} else {
				$questions = QSM_Questions::load_questions( 0 );
			}

			$question_array = array();
			foreach ( $questions as $question ) {
				$question['page']  = isset( $question['page'] ) ? $question['page'] : 0;
				$question_array[] = array(
					'id'         => $question['question_id'],
					'quizID'     => $question['quiz_id'],
					'type'       => $question['question_type_new'],
					'name'       => $question['question_name'],
					'answerInfo' => $question['question_answer_info'],
					'comments'   => $question['comments'],
					'hint'       => $question['hints'],
					'category'   => $question['category'],
					'required'   => $question['settings']['required'],
					'answers'    => $question['answers'],
					'page'       => $question['page'],
				);
			}
			return $question_array;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}

/**
 * REST API endpoint function for creating questions
 *
 * @since 5.2.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array An array that contains the key 'id' for the new question.
 */
function qsm_rest_create_question( WP_REST_Request $request ) {

	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			try {
				$data = array(
					'quiz_id'     => $request['quizID'],
					'type'        => $request['type'],
					'name'        => $request['name'],
					'answer_info' => $request['answerInfo'],
					'comments'    => $request['comments'],
					'hint'        => $request['hint'],
					'order'       => 1,
					'category'    => $request['category'],
				);
				$settings = array(
					'required' => $request['required'],
				);
				$intial_answers = $request['answers'];
				$answers = array();
				if ( is_array( $intial_answers ) ) {
					$answers = $intial_answers;
				}
				$question_id = QSM_Questions::create_question( $data, $answers, $settings );
				return array(
					'status' => 'success',
					'id'     => $question_id,
				);
			} catch ( Exception $e ) {
				$msg = $e->getMessage();
				return array(
					'status' => 'error',
					'msg'    => "There was an error when creating your question. Please try again. Error from WordPress: $msg",
				);
			}
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}

/**
 * REST API endpoint function for saving questions
 *
 * @since 5.2.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array An array that contains the key 'id' for the new question.
 */
function qsm_rest_save_question( WP_REST_Request $request ) {

	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			try {
				$id = intval( $request['id'] );
				$data = array(
					'quiz_id'     => $request['quizID'],
					'type'        => $request['type'],
					'name'        => $request['name'],
					'answer_info' => $request['answerInfo'],
					'comments'    => $request['comments'],
					'hint'        => $request['hint'],
					'order'       => 1,
					'category'    => $request['category'],
				);
				$settings = array(
					'required' => $request['required'],
				);
				$intial_answers = $request['answers'];
				$answers = array();
				if ( is_array( $intial_answers ) ) {
					$answers = $intial_answers;
				}
				$question_id = QSM_Questions::save_question( $id, $data, $answers, $settings );
				return array(
					'status' => 'success',
				);
			} catch ( Exception $e ) {
				$msg = $e->getMessage();
				return array(
					'status' => 'error',
					'msg'    => "There was an error when creating your question. Please try again. Error from WordPress: $msg",
				);
			}
		}
	}
	return array(
		'status' => 'error',
		'msg'    => 'User not logged in',
	);
}
