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
	register_rest_route(
		'quiz-survey-master/v1',
		'/questions/',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'qsm_rest_get_questions',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/questions/',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'qsm_rest_create_question',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/questions/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'qsm_rest_save_question',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/questions/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'qsm_rest_get_question',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/results',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'qsm_rest_get_results',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/results',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'qsm_rest_save_results',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/emails',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'qsm_rest_get_emails',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/emails',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'qsm_rest_save_emails',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
		// Register rest api to get quiz list
		register_rest_route(
			'qsm',
			'/list_quiz',
			array(
				'methods'             => 'GET',
				'callback'            => 'qsm_get_basic_info_quiz',
				'permission_callback' => '__return_true',
			)
		);

		// Register rest api to get quiz JSON by quiz_id
		register_rest_route(
			'qsm',
			'/quiz(?:/(?P<quiz_id>\d+))?',
			array(
				'methods'             => 'GET',
				'callback'            => 'qsm_get_quiz_info',
				'permission_callback' => '__return_true',
			)
		);      

		// Register rest api to get quiz result JSON by result_id
		register_rest_route(
			'qsm',
			'/quiz_result(?:/(?P<result_id>\d+))?',
			array(
				'methods'             => 'GET',
				'callback'            => 'qsm_get_quiz_result_info',
				'permission_callback' => '__return_true',
			)
		);

		// Register rest api to get result of quiz
		register_rest_route(
			'qsm',
			'/list_results/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => 'qsm_get_result_of_quiz',
				'permission_callback' => '__return_true',
			)
		);
		// Get questions for question bank
		register_rest_route(
			'quiz-survey-master/v1',
			'/bank_questions/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'qsm_rest_get_bank_questions',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
		// Get Categories of quiz
		register_rest_route(
			'quiz-survey-master/v1',
			'/quizzes/(?P<id>\d+)/categories',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'qsm_rest_get_categories',
				'permission_callback' => '__return_true',
			)
		);
		// Get Categories of quiz
		register_rest_route(
			'quiz-survey-master/v2',
			'/quizzlist/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'qsm_get_quizzes_list',
				'permission_callback' => '__return_true',
			)
		);

}

/**
 * Get questions for question bank
 *
 * @since 6.4.10
 * @param WP_REST_Request $request
 */
function qsm_rest_get_bank_questions( WP_REST_Request $request ) {
	if ( is_user_logged_in() ) {
		global $wpdb;
		$quiz_filter = '%%';
		if ( ! empty( $_REQUEST['quizID'] ) ) {
			$quiz_filter = sanitize_text_field( wp_unslash( $_REQUEST['quizID'] ) );
		}
		$category = isset( $_REQUEST['category'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['category'] ) ) : '';
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ) : '';
		$enabled  = get_option( 'qsm_multiple_category_enabled' );
		$migrated = false;
		if ( $enabled && 'cancelled' !== $enabled ) {
			$migrated = true;
		}
		if ( ! empty( $category ) ) {
			if ( $migrated && is_numeric( $category ) ) {
				$query        = $wpdb->prepare( "SELECT DISTINCT question_id FROM {$wpdb->prefix}mlw_question_terms WHERE term_id = %d", $category );
				$term_ids     = $wpdb->get_results( $query, 'ARRAY_A' );
				$question_ids = array();
				foreach ( $term_ids as $term_id ) {
					$question_ids[] = esc_sql( intval( $term_id['question_id'] ) );
				}
				$question_ids = array_unique( $question_ids );
				$query        = $wpdb->prepare( "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND question_id IN (%s) AND quiz_id LIKE %s AND question_settings LIKE %s", implode( ',', $question_ids ), $quiz_filter, $search );
			} else {
				$query = $wpdb->prepare( "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND category = %s AND quiz_id LIKE %s AND question_settings LIKE %s", $category, $quiz_filter, '%' . $search . '%' );
			}
		} else {
			$query = $wpdb->prepare( "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank=0 AND quiz_id LIKE %s AND question_settings LIKE %s", $quiz_filter, '%' . $search . '%' );
		}
		$total_count_query = $wpdb->get_row( $query, 'ARRAY_A' );
		$total_count       = isset( $total_count_query['total_question'] ) ? $total_count_query['total_question'] : 0;

		$settings = (array) get_option( 'qmn-settings' );
		$limit    = 20;
		if ( isset( $settings['items_per_page_question_bank'] ) ) {
			$limit = $settings['items_per_page_question_bank'];
		}
		$limit       = empty( $limit ) ? 20 : $limit;
		$total_pages = ceil( $total_count / $limit );
		$pageno      = isset( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : 1;
		$offset      = ( $pageno - 1 ) * $limit;

		if ( ! empty( $category ) ) {
			if ( $migrated && is_numeric( $category ) ) {
				$query        = $wpdb->prepare( "SELECT DISTINCT question_id FROM {$wpdb->prefix}mlw_question_terms WHERE term_id = %d", $category );
				$term_ids     = $wpdb->get_results( $query, 'ARRAY_A' );
				$question_ids = array();
				foreach ( $term_ids as $term_id ) {
					$question_ids[] = esc_sql( intval( $term_id['question_id'] ) );
				}
				$question_ids = array_unique( $question_ids );
				$query_result = array();
				foreach ( $question_ids as $question_id ) {
					$query         = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND question_id = %d AND quiz_id LIKE %s AND question_settings LIKE %s ORDER BY question_order ASC LIMIT %d, %d", $question_id, $quiz_filter, '%' . $search . '%', $offset, $limit );
					$question_data = $wpdb->get_row( $query, 'ARRAY_A' );
					if ( ! is_null( $question_data ) ) {
						$query_result[] = $question_data;
					}
				}
				$questions = $query_result;
			} else {
				$query     = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND category = %s AND quiz_id LIKE %s AND question_settings LIKE %s ORDER BY question_order ASC LIMIT %d, %d", $category, $quiz_filter, '%' . $search . '%', $offset, $limit );
				$questions = $wpdb->get_results( $query, 'ARRAY_A' );
			}
		} else {
			$query     = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND quiz_id LIKE %s AND question_settings LIKE %s ORDER BY question_order ASC LIMIT %d, %d", $quiz_filter, '%' . $search . '%', $offset, $limit );
			$questions = $wpdb->get_results( $query, 'ARRAY_A' );
		}

		$question_array               = array();
		$question_array['search']     = $search;
		$question_array['pagination'] = array(
			'total_pages'  => $total_pages,
			'current_page' => $pageno,
			'category'     => $category,
		);

		$question_array['questions'] = array();
		foreach ( $questions as $question ) {
			$quiz_name        = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $question['quiz_id'] ), ARRAY_A );
			$question['page'] = isset( $question['page'] ) ? (int) $question['page'] : 0;

			$answers = maybe_unserialize( $question['answer_array'] );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
			$question['answers'] = $answers;

			$settings = maybe_unserialize( $question['question_settings'] );
			if ( ! is_array( $settings ) ) {
				$settings = array( 'required' => 1 );
			}
			if ( empty( $settings['question_title'] ) && empty( $question['question_name'] ) ) {
				continue;
			}

			$question['settings']          = $settings;
			$question_data                 = array(
				'id'                      => $question['question_id'],
				'quizID'                  => $question['quiz_id'],
				'type'                    => $question['question_type_new'],
				'question_title'          => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : 0,
				'name'                    => $question['question_name'],
				'answerInfo'              => $question['question_answer_info'],
				'comments'                => $question['comments'],
				'img_width'               => isset( $question['settings']['image_size-width'] ) ? $question['settings']['image_size-width'] : '',
				'img_height'              => isset( $question['settings']['image_size-height'] ) ? $question['settings']['image_size-height'] : '',
				'hint'                    => $question['hints'],
				'category'                => $question['category'],
				'required'                => $question['settings']['required'],
				'answers'                 => $question['answers'],
				'page'                    => $question['page'],
				'answerEditor'            => isset( $question['settings']['answerEditor'] ) ? $question['settings']['answerEditor'] : 'text',
				'autofill'                => isset( $question['settings']['autofill'] ) ? $question['settings']['autofill'] : 0,
				'case-sensitive'          => isset( $question['settings']['case-sensitive'] ) ? $question['settings']['case-sensitive'] : 0,
				'limit_text'              => isset( $question['settings']['limit_text'] ) ? $question['settings']['limit_text'] : 0,
				'limit_multiple_response' => isset( $question['settings']['limit_multiple_response'] ) ? $question['settings']['limit_multiple_response'] : 0,
				'file_upload_limit'       => isset( $question['settings']['file_upload_limit'] ) ? $question['settings']['file_upload_limit'] : 0,
				'file_upload_type'        => isset( $question['settings']['file_upload_type'] ) ? $question['settings']['file_upload_type'] : '',
				'quiz_name'               => isset( $quiz_name['quiz_name'] ) ? $quiz_name['quiz_name'] : '',
				'question_title'          => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
			);
			$question_data                 = apply_filters( 'qsm_rest_api_filter_question_data', $question_data, $question, $request );
			$question_array['questions'][] = $question_data;
		}
		return $question_array;
	} else {
		return array(
			'status' => 'error',
			'msg'    => __( 'User not logged in', 'quiz-master-next' ),
		);
	}
}

/**
 * Get the result of quiz from quiz id
 *
 * @since 6.3.5
 * @param WP_REST_Request $request
 */
function qsm_get_result_of_quiz( WP_REST_Request $request ) {
	$quiz_id = isset( $request['id'] ) ? $request['id'] : 0;
	if ( $quiz_id > 0 ) {
		global $wpdb;
		$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted='0' AND quiz_id = %d LIMIT 0,40", $quiz_id ) );
		if ( $mlw_quiz_data ) {
			$result_data = array();
			foreach ( $mlw_quiz_data as $mlw_quiz_info ) {
				$form_type = isset( $mlw_quiz_info->form_type ) ? $mlw_quiz_info->form_type : 0;
				if ( 1 === intval( $form_type ) || '2' === intval( $form_type ) ) {
					$quotes_list = '' . __( 'Not Graded', 'quiz-master-next' ) . '';
				} else {
					if ( 0 === intval( $mlw_quiz_info->quiz_system ) ) {
						$quotes_list = '' . $mlw_quiz_info->correct . ' out of ' . $mlw_quiz_info->total . ' or ' . $mlw_quiz_info->correct_score . '%';
					}
					if ( 1 === intval( $mlw_quiz_info->quiz_system ) ) {
						$quotes_list = '' . $mlw_quiz_info->point_score . ' Points';
					}
					if ( 3 === intval( $mlw_quiz_info->quiz_system ) ) {
						$quotes_list = '' . $mlw_quiz_info->correct . ' out of ' . $mlw_quiz_info->total . ' or ' . $mlw_quiz_info->correct_score . '%<br/>';
						$quotes_list = '' . $mlw_quiz_info->point_score . ' Points';
					}
				}
				// Time to complete
				$mlw_complete_time     = '';
				$mlw_qmn_results_array = maybe_unserialize( $mlw_quiz_info->quiz_results );
				if ( is_array( $mlw_qmn_results_array ) ) {
						$mlw_complete_hours = floor( $mlw_qmn_results_array[0] / 3600 );
					if ( $mlw_complete_hours > 0 ) {
							$mlw_complete_time .= "$mlw_complete_hours hours ";
					}
						$mlw_complete_minutes = floor( ( $mlw_qmn_results_array[0] % 3600 ) / 60 );
					if ( $mlw_complete_minutes > 0 ) {
							$mlw_complete_time .= "$mlw_complete_minutes minutes ";
					}
						$mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
						$mlw_complete_time   .= "$mlw_complete_seconds seconds";
				}
				// Time taken
				$date          = gmdate( get_option( 'date_format' ), strtotime( $mlw_quiz_info->time_taken ) );
				$time          = gmdate( 'h:i:s A', strtotime( $mlw_quiz_info->time_taken ) );
				$result_data[] = array(
					'score'            => $quotes_list,
					'time_to_complete' => $mlw_complete_time,
					'time_taken'       => $date . ' ' . $time,
				);
			}
			exit;
		} else {
			return rest_ensure_response( 'No record found.' );
		}
	} else {
		return rest_ensure_response( 'Quiz id is missing.' );
	}
}

/**
 * Get the list of quizes
 *
 * @since 6.3.5
 * @param WP_REST_Request $request
 */
function qsm_get_basic_info_quiz( WP_REST_Request $request ) {
	global $mlwQuizMasterNext;
	$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes();
	if ( $quizzes ) {
		$quiz_data = array();
		foreach ( $quizzes as $quiz ) {
			$quiz_data[] = array(
				'quiz_name'     => $quiz->quiz_name,
				'last_activity' => $quiz->last_activity,
				'quiz_views'    => $quiz->quiz_views,
				'quiz_taken'    => $quiz->quiz_taken,
			);
		}
		return rest_ensure_response( $quiz_data );
	} else {
		return rest_ensure_response( 'No quiz found.' );
	}
}

/**
 * Get the quiz result by result id
 *
 * @since 8.2.2
 * @param WP_REST_Request $request
 */

function qsm_get_quiz_result_info( WP_REST_Request $request ) {
	
    if ( $request->get_param('result_id') ) {
		global $wpdb;
		$results_table_name              = $wpdb->prefix . 'mlw_results';
		$results_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $request->get_param('result_id') ) );
        if ( $results_data ) {
			$results_data->quiz_results = maybe_unserialize($results_data->quiz_results);
			$request = array(
                'success' => true,
                'data'    => $results_data,
            );
        } else {
			$request = array(
				'success' => false,
				'message' => __('Quiz result not found', 'quiz-master-next'),
			);
		}       
    } else {
		global $wpdb;
		$limit = $request->get_param('limit');
		$quiz_id = $request->get_param('quiz_id');
		$name = $request->get_param('name');
		$email = $request->get_param('email');
		$from_date = $request->get_param('time_taken_real');
		$order = $request->get_param('order');

		$query = "SELECT * FROM {$wpdb->prefix}mlw_results WHERE 1=1";
		if ( empty($limit) ) {
			$limit = 10;
		}
		$limit = empty($limit) ? 10 : $limit;
		$order = empty($order) ? 'ASC' : $order;
		
		if ( ! empty($quiz_id) ) {
			$query .= $wpdb->prepare(" AND quiz_id = %s", $quiz_id);
        }

		if ( ! empty($name) ) {
			$username  = '%' . esc_sql( $wpdb->esc_like( $name ) ) . '%';
			$query .= $wpdb->prepare(" AND name LIKE %s", $username);
        }

		if ( ! empty($email) ) {
			$useremail  = '%' . esc_sql( $wpdb->esc_like( $email ) ) . '%';
			$query .= $wpdb->prepare(" AND email LIKE %s", $useremail);
        }

		if ( ! empty($from_date) ) {
			$query .= $wpdb->prepare( " AND time_taken_real >= %s", $from_date );
        }

		$results = $wpdb->get_results($query .= " ORDER BY result_id {$order} LIMIT {$limit}");
		
		if ( $results ) {
			$data = [];
			foreach ( $results as $key => $value ) {
				$value->quiz_results = maybe_unserialize($value->quiz_results);
				$data[] = $value;
			}
			$response = array(
				'count'   => count($data),
				'success' => true,
				'data'    => $data,
			);
		} else {
			$response = array(
				'success' => false,
				'message' => __('No result data available', 'quiz-master-next'),
			);
		}
	}
	return rest_ensure_response($response);
}

/**
 * Get the quiz by quiz id
 *
 * @since 8.2.2
 * @param WP_REST_Request $request
 * @param $quiz_id, $limit, $quiz_name, $from_date
 */

function qsm_get_quiz_info( WP_REST_Request $request ) {

    if ( $request->get_param('quiz_id') ) {
        global $mlwQuizMasterNext;
        $quiz_data = $mlwQuizMasterNext->pluginHelper->prepare_quiz($request->get_param('quiz_id'));
        if ( $quiz_data ) {
            $qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
            $apiFormatArray = qsm_convert_to_api_format($qmn_quiz_options);
            $response = array(
                'success' => true,
                'data'    => $apiFormatArray,
            );
        } else {
			$response = array(
				'success' => false,
				'message' => __('Quiz not found', 'quiz-master-next'),
			);
        }
    } else {
		global $wpdb;
		$limit = $request->get_param('limit');
		$quiz_name = $request->get_param('quiz_name');
		$from_date = $request->get_param('from_date');
		$query = "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE 1=1";
		if ( empty($limit) ) {
			$limit = 10;
		}

		if ( ! empty($quiz_name) ) {
			$qnsearch  = '%' . esc_sql( $wpdb->esc_like( $quiz_name ) ) . '%';
			$query .= $wpdb->prepare(" AND quiz_name LIKE %s", $qnsearch);
        }

		if ( ! empty($from_date) ) {
			$query .= $wpdb->prepare( " AND last_activity >= %s", $from_date );
        }

		$results = $wpdb->get_results($query .= " LIMIT {$limit}");
		$data = [];
		foreach ( $results as $key => $value ) {
			$apiFormatArray = qsm_convert_to_api_format($value);
			$data[] = $apiFormatArray;
		}

		if ( $results ) {
			$response = array(
				'success' => true,
				'data'    => $data,
			);
		} else {
			$response = array(
				'success' => false,
				'message' => __('No quiz data available', 'quiz-master-next'),
			);
		}
    }
	return rest_ensure_response($response);
}

function qsm_convert_to_api_format( $inputObject ) {

	$apiFormat = [];

	foreach ( $inputObject as $key => $value ) {
		if ( $key === 'message_after' || $key === 'user_email_template' || $key === 'quiz_settings' ) {
			$apiFormat[ $key ] = maybe_unserialize($value);
			if ( $key === 'quiz_settings' ) {
				$apiFormat[ $key ] = qsm_unserialize_to_api_format($apiFormat[ $key ]); 
			}
		} elseif ( is_array($value) || is_object($value) ) {
			$apiFormat[ $key ] = qsm_convert_to_api_format($value); 
		} else {
			$apiFormat[ $key ] = $value;
		}
	}

	return $apiFormat;
}

function qsm_unserialize_to_api_format( $data ) {
	$result = array();

	if ( is_serialized($data) ) {
		return maybe_unserialize($data);
	}

	foreach ( $data as $key => $value ) {
		if ( is_serialized($value) ) {
			$result[ $key ] = qsm_unserialize_recursive_loop($value);
		} else {
			$result[ $key ] = $value;
		}
	}

	return $result;
}

function qsm_unserialize_recursive_loop( $value ) {
	$unserializedValue = maybe_unserialize($value);

	if ( is_array($unserializedValue) ) {
		foreach ( $unserializedValue as $innerKey => $innerValue ) {
			$unserializedValue[ $innerKey ] = qsm_unserialize_recursive_loop($innerValue);
		}
	}

	return $unserializedValue;
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
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
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
		$stop         = qsm_verify_rest_user_nonce( $request['id'], $current_user->ID, $request['rest_nonce'] );
		if ( ! $stop ) {
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
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
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
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
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
		$stop         = qsm_verify_rest_user_nonce( $request['id'], $current_user->ID, $request['rest_nonce'] );
		if ( ! $stop ) {
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
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
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
			$question       = QSM_Questions::load_question( $request['id'] );
			$categorysArray = QSM_Questions::get_question_categories( $question['question_id'] );
			if ( ! empty( $question ) ) {
				$question['page'] = isset( $question['page'] ) ? $question['page'] : 0;
				$question         = array(
					'id'              => $question['question_id'],
					'quizID'          => $question['quiz_id'],
					'type'            => $question['question_type_new'],
					'name'            => $question['question_name'],
					'answerInfo'      => $question['question_answer_info'],
					'comments'        => $question['comments'],
					'hint'            => $question['hints'],
					'category'        => ( isset( $categorysArray['category_name'] ) && ! empty( $categorysArray['category_name'] ) ? implode( ',', $categorysArray['category_name'] ) : '' ),
					'multicategories' => $question['multicategories'],
					'required'        => $question['settings']['required'],
					'answerEditor'    => $question['settings']['answerEditor'],
					'answers'         => $question['answers'],
					'page'            => $question['page'],
					'question_title'  => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
				);
			}
			return $question;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
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
				$quiz_name        = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $question['quiz_id'] ), ARRAY_A );
				$question['page'] = isset( $question['page'] ) ? $question['page'] : 0;
				$categorysArray   = QSM_Questions::get_question_categories( $question['question_id'] );

				$question_data    = array(
					'id'                      => $question['question_id'],
					'quizID'                  => $question['quiz_id'],
					'type'                    => $question['question_type_new'],
					'name'                    => $question['question_name'],
					'answerInfo'              => htmlspecialchars_decode( $question['question_answer_info'], ENT_QUOTES ),
					'comments'                => $question['comments'],
					'hint'                    => $question['hints'],
					'category'                => ( isset( $categorysArray['category_name'] ) && ! empty( $categorysArray['category_name'] ) ? implode( ',', $categorysArray['category_name'] ) : '' ),
					'multicategories'         => $question['multicategories'],
					'required'                => $question['settings']['required'],
					'answers'                 => $question['answers'],
					'page'                    => $question['page'],
					'img_width'               => isset( $question['settings']['image_size-width'] ) ? $question['settings']['image_size-width'] : '',
					'img_height'              => isset( $question['settings']['image_size-height'] ) ? $question['settings']['image_size-height'] : '',
					'answerEditor'            => isset( $question['settings']['answerEditor'] ) ? $question['settings']['answerEditor'] : 'text',
					'autofill'                => isset( $question['settings']['autofill'] ) ? $question['settings']['autofill'] : 0,
					'case_sensitive'          => isset( $question['settings']['case_sensitive'] ) ? $question['settings']['case_sensitive'] : 0,
					'limit_text'              => isset( $question['settings']['limit_text'] ) ? $question['settings']['limit_text'] : 0,
					'limit_multiple_response' => isset( $question['settings']['limit_multiple_response'] ) ? $question['settings']['limit_multiple_response'] : 0,
					'file_upload_limit'       => isset( $question['settings']['file_upload_limit'] ) ? $question['settings']['file_upload_limit'] : 0,
					'file_upload_type'        => isset( $question['settings']['file_upload_type'] ) ? $question['settings']['file_upload_type'] : '',
					'quiz_name'               => isset( $quiz_name['quiz_name'] ) ? $quiz_name['quiz_name'] : '',
					'question_title'          => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
					'featureImageID'          => isset( $question['settings']['featureImageID'] ) ? $question['settings']['featureImageID'] : '',
					'featureImageSrc'         => isset( $question['settings']['featureImageSrc'] ) ? $question['settings']['featureImageSrc'] : '',
					'settings'                => $question['settings'],
				);
				$question_data    = apply_filters( 'qsm_rest_api_filter_question_data', $question_data, $question, $request );
				$question_array[] = $question_data;
			}
			return $question_array;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
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
		global $wpdb;
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			try {
				$data           = array(
					'quiz_id'         => $request['quizID'],
					'type'            => $request['type'],
					'name'            => $request['name'],
					'answer_info'     => $request['answerInfo'],
					'comments'        => $request['comments'],
					'hint'            => $request['hint'],
					'order'           => 1,
					'category'        => $request['category'],
					'multicategories' => $request['multicategories'],
				);
				$settings       = array(
					'required'       => $request['required'],
					'answerEditor'   => 'text',
					'question_title' => $request['question_title'],
				);
				$intial_answers = $request['answers'];
				$answers        = array();
				if ( is_array( $intial_answers ) ) {
					$answers = $intial_answers;
				}
				if ( ! empty( $request['question_id'] ) ) {
					$settings = $wpdb->get_var( $wpdb->prepare( 'SELECT question_settings FROM ' . $wpdb->prefix . 'mlw_questions WHERE question_id=%d', $request['question_id'] ) );
					$settings = maybe_unserialize( $settings );
				}
				$question_id = QSM_Questions::create_question( $data, $answers, $settings );

				do_action( 'qsm_saved_question_data', $question_id, $request );

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
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
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
		$stop         = qsm_verify_rest_user_nonce( $request['quizID'], $current_user->ID, $request['rest_nonce'] );
		if ( ! $stop ) {
			try {
				$id                          = intval( $request['id'] );
				$data                        = array(
					'quiz_id'         => $request['quizID'],
					'type'            => $request['type'],
					'name'            => $request['name'],
					'answer_info'     => $request['answerInfo'],
					'comments'        => $request['comments'],
					'hint'            => preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $request['hint'] ),
					'order'           => 1,
					'category'        => $request['category'],
					'multicategories' => $request['multicategories'],
				);
				$settings                    = array();
				$settings['answerEditor']    = $request['answerEditor'];
				$settings['question_title']  = sanitize_text_field( wp_strip_all_tags( html_entity_decode( $request['question_title'] ) ) );
				$settings['featureImageID']  = sanitize_text_field( $request['featureImageID'] );
				$settings['featureImageSrc'] = sanitize_text_field( $request['featureImageSrc'] );
				$settings['matchAnswer']     = sanitize_text_field( $request['matchAnswer'] );
				if ( isset( $request['other_settings'] ) && is_array( $request['other_settings'] ) ) {
					foreach ( $request['other_settings'] as $setting_key => $setting_value ) {
						$settings[ $setting_key ] = $setting_value;
					}
				}
				$intial_answers = $request['answers'];
				$answers        = array();
				if ( is_array( $intial_answers ) ) {
					if ( 8 == $request['type'] ) {
						$answers = array(
							array(
								'0' => $request['name'],
								'1' => 0,
								'2' => 1,
							),
						);
					} else {
						$answers = $intial_answers;
					}
				}
				$question_id = QSM_Questions::save_question( $id, $data, $answers, $settings );
				do_action( 'qsm_saved_question_data', $question_id, $request );
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
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
	);
}

/**
 * Gets categories for a quiz.
 *
 * @since 7.2.1
 * @param WP_REST_Request $request The request sent from WP REST API.
 * @return array Categories for the quiz.
 */
function qsm_rest_get_categories( WP_REST_Request $request ) {
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$categories = array();
			$quiz_id    = isset( $request['id'] ) ? intval( $request['id'] ) : 0;
			if ( 0 !== $quiz_id ) {
				$categories = QSM_Questions::get_quiz_categories( $quiz_id );
			}
			return $categories;
		}
	}
	return array(
		'status' => 'error',
		'msg'    => __( 'User not logged in', 'quiz-master-next' ),
	);
}

/**
 * Verify user nonce and if error occurs it will return array
 */
function qsm_verify_rest_user_nonce( $id, $user_id, $rest_nonce ) {
	// Makes sure user is logged in.
	$nonce = 'wp_rest_nonce_' . $id . '_' . $user_id;
	if ( ! wp_verify_nonce( $rest_nonce, $nonce ) ) {
		return array(
			'status' => 'error',
			'msg'    => __( 'Unauthorized!', 'quiz-master-next' ),
		);
	}
	return false;
}

/**
 * Get the quizzes list
 *
 * @since 7.3.6
 * @return array
 */
function qsm_get_quizzes_list() {
	global $wpdb;
	$quizzes         = $wpdb->get_results( "SELECT quiz_id, quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE deleted='0'" );
	$qsm_quiz_list[] = array(
		'label' => __( 'Select the quiz', 'quiz-master-next' ),
		'value' => '',
	);
	if ( $quizzes ) {
		foreach ( $quizzes as $quiz ) {
				$qsm_quiz_list[] = array(
					'label' => $quiz->quiz_name,
					'value' => $quiz->quiz_id,
				);
		}
	}
	return $qsm_quiz_list;
}
