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
                'permission_callback' => '__return_true',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/questions/', array(
		'methods'  => WP_REST_Server::CREATABLE,
		'callback' => 'qsm_rest_create_question',
                'permission_callback' => function () {
                    return current_user_can( 'edit_posts' );
                }
	) );
	register_rest_route( 'quiz-survey-master/v1', '/questions/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::EDITABLE,
		'callback' => 'qsm_rest_save_question',
                'permission_callback' => '__return_true',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/questions/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_question',
                'permission_callback' => '__return_true',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/results', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_results',
                'permission_callback' => '__return_true',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/results', array(
		'methods'  => WP_REST_Server::EDITABLE,
		'callback' => 'qsm_rest_save_results',
                'permission_callback' => '__return_true',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/emails', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_emails',
                'permission_callback' => '__return_true',
	) );
	register_rest_route( 'quiz-survey-master/v1', '/quizzes/(?P<id>\d+)/emails', array(
		'methods'  => WP_REST_Server::EDITABLE,
		'callback' => 'qsm_rest_save_emails',
                'permission_callback' => function () {
                    return current_user_can( 'edit_posts' );
                }
	) );
        //Register rest api to get quiz list
        register_rest_route('qsm', '/list_quiz', array(
            'methods' => 'GET',
            'callback' => 'qsm_get_basic_info_quiz',
            'permission_callback' => '__return_true',
        ));
        //Register rest api to get result of quiz
        register_rest_route('qsm', '/list_results/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => 'qsm_get_result_of_quiz',
            'permission_callback' => '__return_true',
        ));
        //Get questions for question bank
        register_rest_route( 'quiz-survey-master/v1', '/bank_questions/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'qsm_rest_get_bank_questions',
                'permission_callback' => function () {
                    return current_user_can( 'edit_posts' );
                }
	) );
}

/**
 * Get questions for question bank
 * @since 6.4.10
 * @param WP_REST_Request $request
 */
function qsm_rest_get_bank_questions( WP_REST_Request $request ) {
    if (is_user_logged_in()) {
        global $wpdb;
        $category = isset($_REQUEST['category']) ? sanitize_text_field($_REQUEST['category']) : '';
        
        if (!empty($category)) {
            $query = $wpdb->prepare( "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted=0 AND deleted_question_bank=0 AND category=%s", $category );
        } else {
            $query = "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted=0 AND deleted_question_bank=0";    
        }

        $total_count_query = $wpdb->get_row( $query, 'ARRAY_A' );
        $total_count = isset($total_count_query['total_question']) ? $total_count_query['total_question'] : 0;

        $settings   = (array) get_option( 'qmn-settings' );
        $limit = 20;
        if ( isset( $settings['items_per_page_question_bank'] ) ) {
            $limit = $settings['items_per_page_question_bank'];
        }
        $limit = $limit == '' || $limit == 0 ? 20 : $limit;
        $total_pages = ceil($total_count / $limit);
        $pageno = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
        $offset = ($pageno-1) * $limit;
        
        if (!empty($category)) {
            $query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND category = %s ORDER BY question_order ASC LIMIT %d, %d", $category, $offset, $limit );
        } else {
            $query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 ORDER BY question_order ASC LIMIT %d, %d", $offset, $limit );
        }

        $questions = $wpdb->get_results( $query, 'ARRAY_A' );
        $question_array = array();        
        $question_array['pagination'] = array(
                'total_pages' => $total_pages,
                'current_page' => $pageno,
                'category' => $category
        );        
        
        $question_array['questions'] = array();
        foreach ( $questions as $question ) {
                $quiz_name = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d",  $question['quiz_id'] ), ARRAY_A );
                $question['page']  = isset( $question['page'] ) ? (int) $question['page'] : 0;
                
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
                
                $question_data = array(
                        'id'         => $question['question_id'],
                        'quizID'     => $question['quiz_id'],
                        'type'       => $question['question_type_new'],
                        'question_title' => isset($question['settings']['question_title']) ? $question['settings']['question_title'] : 0,
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
                        'question_title'   => isset($question['settings']['question_title']) ? $question['settings']['question_title'] : '',
                );
				$question_data = apply_filters('qsm_rest_api_filter_question_data', $question_data, $question, $request);
				$question_array['questions'][] = $question_data;
        }        
        return $question_array;
    }else{
        return array(
            'status' => 'error',
            'msg'    => __('User not logged in', 'quiz-master-next'),
		);
    }
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
        $mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted='0' AND quiz_id = %d LIMIT 0,40", $quiz_id ) );
        if($mlw_quiz_data){
            $result_data = array();
            foreach ($mlw_quiz_data as $mlw_quiz_info) {
                $form_type = isset( $mlw_quiz_info->form_type ) ? $mlw_quiz_info->form_type : 0;
                if( $form_type == 1 || $form_type == 2 ){
                    $quotes_list = "".__('Not Graded','quiz-master-next' )."";
                }else{
                    if ( $mlw_quiz_info->quiz_system == 0 ) {
                        $quotes_list = "" . $mlw_quiz_info->correct ." out of ".$mlw_quiz_info->total." or ".$mlw_quiz_info->correct_score."%";
                    }
                    if ( $mlw_quiz_info->quiz_system == 1 ) {
                        $quotes_list = "" . $mlw_quiz_info->point_score . " Points";
                    }
                    if ( $mlw_quiz_info->quiz_system == 3 ) {
                        $quotes_list = "" . $mlw_quiz_info->correct ." out of ".$mlw_quiz_info->total." or ".$mlw_quiz_info->correct_score."%<br/>";
                        $quotes_list = "" . $mlw_quiz_info->point_score . " Points";
                    }
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
		'msg'    => __('User not logged in', 'quiz-master-next'),
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
		'msg'    => __('User not logged in', 'quiz-master-next'),
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
		'msg'    => __('User not logged in', 'quiz-master-next'),
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
		'msg'    => __('User not logged in', 'quiz-master-next'),
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
                                        'question_title'   => isset($question['settings']['question_title']) ? $question['settings']['question_title'] : '',
				);
			}
			return $question;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => __('User not logged in', 'quiz-master-next'),
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
			$question_array = array();
			foreach ( $questions as $question ) {                                
                $quiz_name = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $question['quiz_id'] ), ARRAY_A );
				$question['page']  = isset( $question['page'] ) ? $question['page'] : 0;
				$question_data = array(
					'id'         => $question['question_id'],
					'quizID'     => $question['quiz_id'],
					'type'       => $question['question_type_new'],
					'name'       => $question['question_name'],
					'answerInfo' => htmlspecialchars_decode( $question['question_answer_info'], ENT_QUOTES ),
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
                                        'question_title'   => isset($question['settings']['question_title']) ? $question['settings']['question_title'] : '',
                                        'settings' => $question['settings']
				);
				$question_data = apply_filters('qsm_rest_api_filter_question_data', $question_data, $question, $request);
				$question_array[] = $question_data;
			}                        
			return $question_array;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => __('User not logged in', 'quiz-master-next'),
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
                                        'answerEditor' => 'text',
                                        'question_title' => $request['name']
				);
				$intial_answers = $request['answers'];
				$answers = array();
				if ( is_array( $intial_answers ) ) {
					$answers = $intial_answers;
				}
				$question_id = QSM_Questions::create_question( $data, $answers, $settings );

				do_action('qsm_saved_question_data', $question_id, $request);

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
		'msg'    => __('User not logged in', 'quiz-master-next'),
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
                                $settings = array();
                                $settings['answerEditor'] = $request['answerEditor'];
                                $settings['question_title'] = sanitize_text_field( $request['question_title'] );
                                if( isset($request['other_settings']) && is_array($request['other_settings']) ){
                                    foreach ($request['other_settings'] as $setting_key => $setting_value) {
                                        $settings[$setting_key] = $setting_value;
                                    }
                                }
                                /* Old code
				$settings = array(
					'required' => $request['required'],                                        
                                        'autofill' => $request['autofill'],
                                        'limit_text' => $request['limit_text'],
                                        'limit_multiple_response' => $request['limit_multiple_response'],
                                        'file_upload_limit' => $request['file_upload_limit'],
                                        'file_upload_type' => $request['file_upload_type'],
                                        'question_title' => $request['question_title'],
                                        'answerEditor' => $request['answerEditor'],
				); */
				$intial_answers = $request['answers'];
				$answers = array();
				if ( is_array( $intial_answers ) ) {
					$answers = $intial_answers;
				}
				$question_id = QSM_Questions::save_question( $id, $data, $answers, $settings );

				do_action('qsm_saved_question_data', $question_id, $request);

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
		'msg'    => __('User not logged in', 'quiz-master-next'),
	);
}
