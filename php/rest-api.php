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
        //Register rest api to get quiz list
        register_rest_route('qsm', '/list_quiz', array(
            'methods' => 'GET',
            'callback' => 'qsm_get_basic_info_quiz',
        ));
        //Register rest api to get result of quiz
        register_rest_route('qsm', '/list_results/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => 'qsm_get_result_of_quiz',
        ));
}

/**
 * Get the result of quiz from quiz id
 * 
 * @since 6.3.5
 * @param WP_REST_Request $request
 */
function qsm_get_result_of_quiz( WP_REST_Request $request ){    
    $quiz_id = isset($request['id']) ? $request['id'] : 0;    
    if($quiz_id > 0){
        global $wpdb;
        $mlw_quiz_data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted='0' AND quiz_id = $quiz_id LIMIT 0,40" );
        if($mlw_quiz_data){
            $result_data = array();
            foreach ($mlw_quiz_data as $mlw_quiz_info) {
                if ( $mlw_quiz_info->quiz_system == 0 ) {
                    $quotes_list = "" . $mlw_quiz_info->correct ." out of ".$mlw_quiz_info->total." or ".$mlw_quiz_info->correct_score."%";
                }
                if ( $mlw_quiz_info->quiz_system == 1 ) {
                    $quotes_list = "" . $mlw_quiz_info->point_score . " Points";
                }
                if ( $mlw_quiz_info->quiz_system == 2 ) {
                    $quotes_list = "".__('Not Graded','quiz-master-next' )."";
                }
                //Time to complete
                $mlw_complete_time = '';
                $mlw_qmn_results_array = @unserialize($mlw_quiz_info->quiz_results);
                if ( is_array( $mlw_qmn_results_array ) ) {
                        $mlw_complete_hours = floor($mlw_qmn_results_array[0] / 3600);
                        if ( $mlw_complete_hours > 0 ) {
                                $mlw_complete_time .= "$mlw_complete_hours hours ";
                        }
                        $mlw_complete_minutes = floor(($mlw_qmn_results_array[0] % 3600) / 60);
                        if ( $mlw_complete_minutes > 0 ) {
                                $mlw_complete_time .= "$mlw_complete_minutes minutes ";
                        }
                        $mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
                        $mlw_complete_time .=  "$mlw_complete_seconds seconds";
                }
                //Time taken
                $date = date_i18n( get_option( 'date_format' ), strtotime( $mlw_quiz_info->time_taken ) );
                $time = date( "h:i:s A", strtotime( $mlw_quiz_info->time_taken ) );
                $result_data[] = array(
                    'score' => $quotes_list,
                    'time_to_complete' => $mlw_complete_time,
                    'time_taken' => $date . ' ' .$time,
                );
            }
            print_r($result_data);
            exit;
        }else{
            return rest_ensure_response('No record found.');
        }
    }else{
        return rest_ensure_response('Quiz id is missing.');
    }
}

/**
 * Get the list of quizes
 * @since 6.3.5
 * @param WP_REST_Request $request
 */
function qsm_get_basic_info_quiz( WP_REST_Request $request ){
    global $mlwQuizMasterNext;
    $quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes();
    if($quizzes){
        $quiz_data = array();
        foreach ($quizzes as $quiz) {
            $quiz_data[] = array(
                'quiz_name' => $quiz->quiz_name,
                'last_activity' => $quiz->last_activity,
                'quiz_views' => $quiz->quiz_views,
                'quiz_taken' => $quiz->quiz_taken,
            );
        }
        return rest_ensure_response($quiz_data);
    }else{
        return rest_ensure_response('No quiz found.');
    }
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
			if ( ! isset( $request['emails'] ) || ! is_array( $request['emails'] ) ) {
				$request['emails'] = array();
			}
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
 * @since 6.2.0
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
 * @since 6.2.0
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array The results from saving the pages.
 */
function qsm_rest_save_results( WP_REST_Request $request ) {
	// Makes sure user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			if ( ! isset( $request['pages'] ) || ! is_array( $request['pages'] ) ) {
				$request['pages'] = array();
			}
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
                        global $wpdb;
                        $quiz_table = $wpdb->prefix . 'mlw_quizzes';
			$question_array = array();
			foreach ( $questions as $question ) {
                                $quiz_name = $wpdb->get_row('SELECT quiz_name FROM '. $quiz_table . ' WHERE quiz_id = ' . $question['quiz_id'], ARRAY_A );
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
                                        'answerEditor'   => isset($question['settings']['answerEditor']) ? $question['settings']['answerEditor'] : 'text',
                                        'autofill'   => isset($question['settings']['autofill']) ? $question['settings']['autofill'] : 0,
                                        'limit_text'   => isset($question['settings']['limit_text']) ? $question['settings']['limit_text'] : 0,
                                        'limit_multiple_response'   => isset($question['settings']['limit_multiple_response']) ? $question['settings']['limit_multiple_response'] : 0,
                                        'file_upload_limit'   => isset($question['settings']['file_upload_limit']) ? $question['settings']['file_upload_limit'] : 0,
                                        'file_upload_type'   => isset($question['settings']['file_upload_type']) ? $question['settings']['file_upload_type'] : '',
                                        'quiz_name'   => isset($quiz_name['quiz_name']) ? $quiz_name['quiz_name'] : '',
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
                                        'answerEditor' => 'text'
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
                                        'answerEditor' => $request['answerEditor'],
                                        'autofill' => $request['autofill'],
                                        'limit_text' => $request['limit_text'],
                                        'limit_multiple_response' => $request['limit_multiple_response'],
                                        'file_upload_limit' => $request['file_upload_limit'],
                                        'file_upload_type' => $request['file_upload_type'],
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
