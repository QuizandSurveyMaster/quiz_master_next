<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
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
			'permission_callback' => function ( WP_REST_Request $request ) {
				if ( ! current_user_can( 'edit_qsm_quizzes' ) ) {
					return false;
				}
				// Security (IDOR): a quiz-scoped read must pass the same per-quiz
				// ownership check the sibling create/save routes on this path use,
				// otherwise any Contributor can read another author's question set.
				// The unscoped listing (no quizID) is filtered to the quizzes the
				// user may edit inside qsm_rest_get_questions().
				$quiz_id = isset( $request['quizID'] ) ? intval( $request['quizID'] ) : 0;
				return 0 === $quiz_id || qsm_current_user_can_edit_quiz( $quiz_id );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/questions/',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'qsm_rest_create_question',
			'permission_callback' => function ( WP_REST_Request $request ) {
				return current_user_can( 'edit_qsm_quizzes' )
					&& qsm_current_user_can_edit_quiz( $request['quizID'] );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/questions/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'qsm_rest_save_question',
			'permission_callback' => function ( WP_REST_Request $request ) {
				return current_user_can( 'edit_qsm_quizzes' )
					&& qsm_current_user_can_edit_quiz( $request['quizID'] );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/questions/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'qsm_rest_get_question',
			'permission_callback' => function () {
				return current_user_can( 'edit_qsm_quizzes' );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/results',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'qsm_rest_get_results',
			'permission_callback' => function ( WP_REST_Request $request ) {
				return current_user_can( 'edit_qsm_quizzes' )
					&& qsm_current_user_can_edit_quiz( $request['id'] );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/results',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'qsm_rest_save_results',
			'permission_callback' => function ( WP_REST_Request $request ) {
				return current_user_can( 'edit_qsm_quizzes' )
					&& qsm_current_user_can_edit_quiz( $request['id'] );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/emails',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'qsm_rest_get_emails',
			'permission_callback' => function ( WP_REST_Request $request ) {
				return current_user_can( 'edit_qsm_quizzes' )
					&& qsm_current_user_can_edit_quiz( $request['id'] );
			},
		)
	);
	register_rest_route(
		'quiz-survey-master/v1',
		'/quizzes/(?P<id>\d+)/emails',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'qsm_rest_save_emails',
			'permission_callback' => function ( WP_REST_Request $request ) {
				return current_user_can( 'edit_qsm_quizzes' ) && qsm_current_user_can_edit_quiz( $request['id'] );
			},
		)
	);
		// Register rest api to get quiz list (admin-only)
		register_rest_route(
			'qsm',
			'/list_quiz',
			array(
				'methods'             => 'GET',
				'callback'            => 'qsm_get_basic_info_quiz',
				'permission_callback' => function () {
					return current_user_can( 'edit_qsm_quizzes' );
				},
			)
		);

		// Register rest api to get result of quiz (admin-only)
		register_rest_route(
			'qsm',
			'/list_results/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => 'qsm_get_result_of_quiz',
				'permission_callback' => function () {
					return current_user_can( 'edit_qsm_quizzes' );
				},
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
					return current_user_can( 'edit_qsm_quizzes' );
				},
			)
		);
		// Get Categories of quiz (admin-only)
		register_rest_route(
			'quiz-survey-master/v1',
			'/quizzes/(?P<id>\d+)/categories',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'qsm_rest_get_categories',
				'permission_callback' => function ( WP_REST_Request $request ) {
					return current_user_can( 'edit_qsm_quizzes' )
						&& qsm_current_user_can_edit_quiz( $request['id'] );
				},
			)
		);
		// Get quizzes list (admin-only REST).
		register_rest_route(
			'quiz-survey-master/v2',
			'/quizzlist/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'qsm_get_quizzes_list',
				'permission_callback' => function () {
					return current_user_can( 'edit_qsm_quizzes' );
				},
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
		$parameters = $request->get_params();
		global $wpdb;
		$quiz_filter = '%';
		if ( ! empty( $parameters['quizID'] ) ) {
			// esc_like(): the quiz id is matched with LIKE, so an unescaped
			// value lets "%" or "1%" widen the filter back to every quiz.
			$quiz_filter = $wpdb->esc_like( sanitize_text_field( wp_unslash( $parameters['quizID'] ) ) );
		}
		// Security (IDOR): the question bank spans every quiz on the site, so
		// the result set — not just the quizID filter — has to be limited to
		// the quizzes the caller may edit. Applied in SQL so the pagination
		// count matches the rows actually returned.
		$access_sql = qsm_quiz_access_sql();
		$category = isset( $parameters['category'] ) ? sanitize_text_field( wp_unslash( $parameters['category'] ) ) : '';
		$search   = isset( $parameters['search'] ) ? sanitize_text_field( wp_unslash( $parameters['search'] ) ) : '';
		$que_type = isset( $parameters['type'] ) ? sanitize_text_field( wp_unslash( $parameters['type'] ) ) : '';
		$enabled  = get_option( 'qsm_multiple_category_enabled' );
		$migrated = false;
		if ( $enabled && 'cancelled' !== $enabled ) {
			$migrated = true;
		}

		$search_sql = '';
		if ( ! empty( $search ) ) {
			$search_sql .= $wpdb->prepare(
				" AND (question_settings LIKE %s OR question_name LIKE %s)",
				'%' . $wpdb->esc_like( $search ) . '%',
				'%' . $wpdb->esc_like( $search ) . '%'
			);
		}
		if ( ! empty( $que_type ) ) {
			$search_sql .= $wpdb->prepare( " AND question_type_new = %s", $que_type );
		}

		$question_ids = array();
		if ( ! empty( $category ) ) {
			if ( $migrated && is_numeric( $category ) ) {
				$query    = $wpdb->prepare( "SELECT DISTINCT question_id FROM {$wpdb->prefix}mlw_question_terms WHERE term_id = %d", $category );
				$term_ids = $wpdb->get_results( $query, 'ARRAY_A' );
				foreach ( $term_ids as $term_id ) {
					$question_ids[] = esc_sql( intval( $term_id['question_id'] ) );
				}
				$question_ids = array_unique( $question_ids );
				$query = "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND question_id IN (" . implode( ',', $question_ids ) . ") AND quiz_id LIKE %s $search_sql$access_sql";
				$query = $wpdb->prepare( $query, $quiz_filter );
			} else {
				$query = $wpdb->prepare( "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND category = %s AND quiz_id LIKE %s $search_sql$access_sql", $category, $quiz_filter );
			}
		} else {
			$query = $wpdb->prepare( "SELECT COUNT(question_id) as total_question FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND quiz_id LIKE %s $search_sql$access_sql", $quiz_filter );
		}
		$total_count_query = $wpdb->get_row( $query, 'ARRAY_A' );
		$total_count = isset( $total_count_query['total_question'] ) ? $total_count_query['total_question'] : 0;

		$settings = (array) get_option( 'qmn-settings' );
		$limit = 20;
		if ( isset( $settings['items_per_page_question_bank'] ) ) {
			$limit = $settings['items_per_page_question_bank'];
		}
		$limit = empty( $limit ) ? 20 : $limit;
		$total_pages = ceil( $total_count / $limit );
		$pageno = isset( $parameters['page'] ) ? intval( $parameters['page'] ) : 1;
		$offset = ( $pageno - 1 ) * $limit;
		$questions = array();
		if ( ! empty( $category ) ) {
			if ( $migrated && is_numeric( $category ) ) {
				$query_result = array();
				foreach ( $question_ids as $question_id ) {
					$query = "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND question_id = %d AND quiz_id LIKE %s $search_sql$access_sql ORDER BY question_order ASC LIMIT %d, %d";
					$query = $wpdb->prepare( $query, $question_id, $quiz_filter, $offset, $limit );
					$question_data = $wpdb->get_row( $query, 'ARRAY_A' );
					if ( ! is_null( $question_data ) ) {
						$query_result[] = $question_data;
					}
				}
				$questions = $query_result;
			} else {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND category = %s AND quiz_id LIKE %s $search_sql$access_sql ORDER BY question_order ASC LIMIT %d, %d", $category, $quiz_filter, $offset, $limit );
				$questions = $wpdb->get_results( $query, 'ARRAY_A' );
			}
		} else {
			$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = 0 AND deleted_question_bank = 0 AND quiz_id LIKE %s $search_sql$access_sql ORDER BY question_order ASC LIMIT %d, %d", $quiz_filter, $offset, $limit );
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
			$categorysArray   = array();
			if ( $migrated ) {
				$categorysArray = QSM_Questions::get_question_categories( $question['question_id'] );
			}

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
			$question['multicategories']   = isset( $question['multicategories'] ) ? maybe_unserialize( $question['multicategories'] ) : array();
			if ( ! is_array( $question['multicategories'] ) ) {
				$question['multicategories'] = array();
			}
			$display_category              = $question['category'];
			if ( $migrated && empty( $display_category ) && ! empty( $categorysArray['category_name'] ) ) {
				$display_category = implode( ',', $categorysArray['category_name'] );
			}
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
				'category'                => $display_category,
				'required'                => isset( $question['settings']['required'] ) ? $question['settings']['required'] : 0,
				'answers'                 => $question['answers'],
				'page'                    => $question['page'],
				'answerEditor'            => isset( $question['settings']['answerEditor'] ) ? $question['settings']['answerEditor'] : 'text',
				'autofill'                => isset( $question['settings']['autofill'] ) ? $question['settings']['autofill'] : 0,
				'case-sensitive'          => isset( $question['settings']['case-sensitive'] ) ? $question['settings']['case-sensitive'] : 0,
				'limit_text'              => isset( $question['settings']['limit_text'] ) ? $question['settings']['limit_text'] : 0,
				'limit_multiple_response' => isset( $question['settings']['limit_multiple_response'] ) ? $question['settings']['limit_multiple_response'] : 0,
				'file_upload_limit'       => isset( $question['settings']['file_upload_limit'] ) ? $question['settings']['file_upload_limit'] : 4,
				'file_upload_type'        => isset( $question['settings']['file_upload_type'] ) ? $question['settings']['file_upload_type'] : 'image,application/pdf',
				'quiz_name'               => isset( $quiz_name['quiz_name'] ) ? $quiz_name['quiz_name'] : '',
				'question_title'          => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
				'linked_question'         => array_filter( isset( $question['linked_question'] ) ? explode(',', $question['linked_question']) : array() ),
				'settings'                => $question['settings'],
				'multicategories'         => $question['multicategories'],
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
		global $wpdb, $mlwQuizMasterNext;
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
				$is_new_format = $mlwQuizMasterNext->pluginHelper->is_new_format_result( $mlw_quiz_info );
				if ( $is_new_format ) {
					// Load answers and meta from new tables
					$mlw_qmn_results_array  = $mlwQuizMasterNext->pluginHelper->get_formated_result_data( $mlw_quiz_info->result_id );
				} else {
					// Load legacy serialized results
					$mlw_qmn_results_array = maybe_unserialize( $mlw_quiz_info->quiz_results );
				}
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
		if ( ! qsm_current_user_can_edit_quiz( $request['id'] ) ) {
			return array(
				'status' => 'error',
				'msg'    => __( 'Unauthorized!', 'quiz-master-next' ),
			);
		}
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
		if ( ! qsm_current_user_can_edit_quiz( $request['id'] ) ) {
			return array(
				'status' => 'error',
				'msg'    => __( 'Unauthorized!', 'quiz-master-next' ),
			);
		}
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
		global $wpdb;
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user ) {
			$question       = QSM_Questions::load_question( $request['id'] );
			// Security (IDOR): the {id} in this route is a QUESTION id, so authorise against
			// the question's OWNING quiz ($question['quiz_id']) — NOT $request['id'] — before
			// disclosing it. The flat-cap permission_callback alone lets any Contributor read
			// another author's question; mirrors the internal checks in the save_* callbacks.
			if ( ! empty( $question ) && ! qsm_current_user_can_edit_quiz( $question['quiz_id'] ) ) {
				return array(
					'status' => 'error',
					'msg'    => __( 'Unauthorized!', 'quiz-master-next' ),
				);
			}
			$categorysArray = QSM_Questions::get_question_categories( $question['question_id'] );
			if ( ! empty( $question ) ) {
				$is_linking = isset( $request['is_linking'] ) ? intval( $request['is_linking'] ) : 0;
				$linked_ids = array();

				if ( isset( $question['linked_question'] ) && '' !== $question['linked_question'] ) {
					$existing_ids = array_map( 'intval', array_filter( array_map( 'trim', explode( ',', $question['linked_question'] ) ) ) );
					if ( ! empty( $existing_ids ) ) {
						$linked_ids = $existing_ids;
					}
				}

				if ( 1 <= $is_linking ) {
					$linked_ids[] = $is_linking;
				}

				$linked_ids = array_values( array_unique( array_filter( $linked_ids ) ) );

				$quiz_name_by_question = array();
				if ( ! empty( $linked_ids ) ) {
					$linked_ids  = array_map( 'intval', $linked_ids );
					$ids_list    = implode( ',', $linked_ids );
					$quiz_results = $wpdb->get_results( "SELECT `quiz_id`, `question_id` FROM `{$wpdb->prefix}mlw_questions` WHERE `question_id` IN (" . $ids_list . ")" );
					foreach ( $quiz_results as $value ) {
						$quiz_name_in_loop        = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $value->quiz_id ), ARRAY_A );
						$quiz_name_in_loop = isset( $quiz_name_in_loop['quiz_name'] ) ? $quiz_name_in_loop['quiz_name'] : '';
						$quiz_name_by_question[] = $quiz_name_in_loop;
					}
				}
				$question['page'] = isset( $question['page'] ) ? $question['page'] : 0;
				$settings         = isset( $question['settings'] ) && is_array( $question['settings'] ) ? $question['settings'] : array();
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
					'required'        => isset( $settings['required'] ) ? $settings['required'] : '',
					'answerEditor'    => isset( $settings['answerEditor'] ) ? $settings['answerEditor'] : '',
					'answers'         => $question['answers'],
					'page'            => $question['page'],
					'question_title'  => isset( $settings['question_title'] ) ? $settings['question_title'] : '',
					'featureImageID'  => isset( $settings['featureImageID'] ) ? $settings['featureImageID'] : '',
					'featureImageSrc' => isset( $settings['featureImageSrc'] ) ? $settings['featureImageSrc'] : '',
					'settings'        => $settings,
					'link_quizzes'    => $quiz_name_by_question,
					'merged_question' => implode( ',', $linked_ids ),
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
				$questions = QSM_Questions::load_questions_by_pages( $quiz_id, 'admin' );
			} else {
				// Security (IDOR): without a quizID this loads every question on the
				// site, across all authors. Restrict it to the quizzes the current
				// user is allowed to edit.
				$questions = qsm_filter_questions_by_quiz_access( QSM_Questions::load_questions( 0, 'admin' ) );
			}
			global $wpdb;
			$stored_quiz_names = $procesed_question_ids = $question_array = array();
			foreach ( $questions as $question ) {
				$quiz_name        = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $question['quiz_id'] ), ARRAY_A );
				$question['page'] = isset( $question['page'] ) ? $question['page'] : 0;
				$categorysArray   = QSM_Questions::get_question_categories( $question['question_id'] );
				$quiz_name = isset( $quiz_name['quiz_name'] ) ? $quiz_name['quiz_name'] : '';
				$quiz_name_by_question = array();
				$procesed_question_ids[] = $question['question_id'];
				$stored_quiz_names[ $question['question_id'] ] = $quiz_name;
				$linked_question_ids = array_filter( array_map( 'intval', isset( $question['linked_question'] ) ? explode(',', $question['linked_question']) : array() ) );
				if ( ! empty($linked_question_ids) ) {
					$quiz_results = $wpdb->get_results( "SELECT `quiz_id`, `question_id` FROM `{$wpdb->prefix}mlw_questions` WHERE `question_id` IN (" . implode( ',', $linked_question_ids ) . ")" );
					foreach ( $quiz_results as $value ) {
						if ( ! in_array($value->question_id, $procesed_question_ids, true) ) {
							$quiz_name_in_loop        = $wpdb->get_row( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $value->quiz_id ), ARRAY_A );
							$quiz_name_in_loop = isset( $quiz_name_in_loop['quiz_name'] ) ? $quiz_name_in_loop['quiz_name'] : '';
							$quiz_name_by_question[] = $quiz_name_in_loop;
							$procesed_question_ids[] = $value->question_id;
							$stored_quiz_names[ $value->question_id ] = $quiz_name_in_loop;
						} else {
							$quiz_name_by_question[] = $stored_quiz_names[ $value->question_id ];
						}
					}
				}
				$quiz_name_by_question = array_diff($quiz_name_by_question, array( $quiz_name )); // remove current quiz id from the list
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
					'file_upload_limit'       => isset( $question['settings']['file_upload_limit'] ) ? $question['settings']['file_upload_limit'] : 4,
					'file_upload_type'        => isset( $question['settings']['file_upload_type'] ) ? $question['settings']['file_upload_type'] : 'image,application/pdf',
					'quiz_name'               => $quiz_name,
					'question_title'          => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
					'featureImageID'          => isset( $question['settings']['featureImageID'] ) ? $question['settings']['featureImageID'] : '',
					'featureImageSrc'         => isset( $question['settings']['featureImageSrc'] ) ? $question['settings']['featureImageSrc'] : '',
					'settings'                => $question['settings'],
					'link_quizzes'            => $quiz_name_by_question,
					'merged_question'         => implode(",", $linked_question_ids),
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
		if ( ! qsm_current_user_can_edit_quiz( $request['quizID'] ) ) {
			return array(
				'status' => 'error',
				'msg'    => __( 'Unauthorized!', 'quiz-master-next' ),
			);
		}
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
					'linked_question' => $request['merged_question'],
					'is_linking'      => isset( $request['is_linking'] ) ? intval( $request['is_linking'] ) : 0,
				);
				$settings       = array(
					'required'       => $request['required'],
					'answerEditor'   => 'text',
					'question_title' => sanitize_text_field( wp_strip_all_tags( html_entity_decode( $request['question_title'] ) ) ),
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
		if ( ! qsm_current_user_can_edit_quiz( $request['quizID'] ) ) {
			return array(
				'status' => 'error',
				'msg'    => __( 'Unauthorized!', 'quiz-master-next' ),
			);
		}
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
					'linked_question' => $request['merged_question'],
				);
				$settings                    = array();
				$settings['answerEditor']    = $request['answerEditor'];
				$settings['question_title']  = sanitize_text_field( wp_strip_all_tags( html_entity_decode( $request['question_title'] ) ) );
				$settings['featureImageID']  = sanitize_text_field( $request['featureImageID'] );
				$settings['featureImageSrc'] = sanitize_text_field( $request['featureImageSrc'] );
				$settings['matchAnswer']     = sanitize_text_field( $request['matchAnswer'] );
				$settings['isPublished']     = sanitize_text_field( $request['is_published'] );
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
 * Resolve a quiz_id to its backing post_id.
 *
 * The 'quiz_id' meta key is not protected (no leading underscore), so any user
 * who can edit a post could otherwise attach it to a post of their own and be
 * mistaken for the quiz's owner. The lookup is therefore constrained to
 * genuine, non-trashed qsm_quiz posts -- the only shape QMNQuizCreator ever
 * creates -- and ordered so that the result is deterministic rather than
 * whatever row the engine happens to yield first.
 *
 * This narrows the forgery surface but does not close it on its own; ownership
 * decisions must go through qsm_current_user_can_edit_quiz(), which prefers the
 * plugin's own mlw_quizzes.quiz_author_id column.
 *
 * @param int $quiz_id The mlw_quizzes.quiz_id value.
 * @return int|false Post ID or false if not found.
 */
function qsm_get_post_id_for_quiz( $quiz_id ) {
	global $wpdb;
	$quiz_id = intval( $quiz_id );
	if ( 0 === $quiz_id ) {
		return false;
	}
	$post_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT pm.post_id FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = 'quiz_id' AND pm.meta_value = %d
				AND p.post_type = 'qsm_quiz' AND p.post_status != 'trash'
			ORDER BY p.ID ASC LIMIT 1",
			$quiz_id
		)
	);
	return $post_id ? intval( $post_id ) : false;
}

/**
 * Returns the author recorded against a quiz by the plugin itself.
 *
 * mlw_quizzes.quiz_author_id is written by QMNQuizCreator when a quiz is
 * created or duplicated and is not reachable through the generic WordPress
 * meta APIs, which makes it the trustworthy ownership signal. It is also what
 * QMNPluginHelper::get_quizzes() already filters the admin quiz list on.
 *
 * The column was added in 7.3.8 with no backfill, so quizzes created before
 * that upgrade hold an empty value. Callers must treat 0 as "not recorded"
 * rather than "owned by nobody".
 *
 * @since 11.2.4
 * @param int $quiz_id The mlw_quizzes.quiz_id value.
 * @return int User ID, or 0 when the quiz is unknown or predates the column.
 */
function qsm_get_quiz_author_id( $quiz_id ) {
	global $wpdb;
	$quiz_id = intval( $quiz_id );
	if ( 0 === $quiz_id ) {
		return 0;
	}
	$author_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT quiz_author_id FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d LIMIT 1",
			$quiz_id
		)
	);
	return intval( $author_id );
}

/**
 * Check whether the current user is authorized to edit a given quiz.
 *
 * Authorship is compared explicitly rather than delegated to
 * current_user_can( 'edit_qsm_quiz', $post_id ). That check is NOT
 * authorship-aware: 'edit_qsm_quiz' is granted to every role as a plain
 * primitive capability (see QMNQuizMasterNext::qsm_add_user_capabilities(),
 * where the contributor set is merged into each role), and it is not
 * registered as a meta capability for the qsm_quiz post type. WordPress
 * therefore ignores the $post_id argument and the check returns true for
 * every quiz, which allowed a Contributor to read and write other authors'
 * quizzes.
 *
 * Only edit_others_qsm_quizzes (editor/administrator) may act on a quiz the
 * current user does not own.
 *
 * Ownership itself is read from mlw_quizzes.quiz_author_id where the plugin
 * recorded it. Deriving it from the 'quiz_id' post meta instead is unsafe: the
 * key is unprotected, so a user who can edit any post could attach another
 * author's quiz id to it and be treated as that quiz's owner. The post-author
 * path survives only as a fallback for quizzes created before quiz_author_id
 * existed (7.3.8), where denying outright would lock legitimate owners out of
 * their own quizzes.
 *
 * @since 11.2.4
 * @param int $quiz_id The mlw_quizzes.quiz_id value.
 * @return bool
 */
function qsm_current_user_can_edit_quiz( $quiz_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	// Users allowed to edit other people's quizzes bypass the ownership test.
	if ( current_user_can( 'edit_others_qsm_quizzes' ) ) {
		return true;
	}

	if ( ! current_user_can( 'edit_qsm_quizzes' ) ) {
		return false;
	}

	$current_user = get_current_user_id();

	// Preferred signal: the author the plugin itself recorded. Not writable
	// through the WordPress meta APIs, so it cannot be forged by a Contributor.
	$quiz_author = qsm_get_quiz_author_id( $quiz_id );
	if ( $quiz_author > 0 ) {
		return $current_user === $quiz_author;
	}

	// Legacy quiz (pre-7.3.8, no recorded author): fall back to the author of
	// the backing qsm_quiz post.
	$post_id = qsm_get_post_id_for_quiz( $quiz_id );
	if ( ! $post_id ) {
		// Ownership cannot be established: fail closed.
		return false;
	}

	$post_author = intval( get_post_field( 'post_author', $post_id ) );

	return $post_author > 0 && $current_user === $post_author;
}

/**
 * Returns the ids of every quiz the current user owns.
 *
 * Resolved in bulk so a listing can be scoped in SQL instead of one ownership
 * lookup per row. Callers must handle the edit_others_qsm_quizzes case
 * themselves: this only ever reports the user's own quizzes.
 *
 * Mirrors qsm_current_user_can_edit_quiz() exactly -- quiz_author_id where the
 * plugin recorded it, the backing qsm_quiz post's author only for quizzes that
 * predate the column. Keeping the two in step matters: a listing that is more
 * generous than the per-quiz gate is the same disclosure by another route.
 *
 * @since 11.2.4
 * @return array Quiz ids (int), empty when the user owns none.
 */
function qsm_get_editable_quiz_ids() {
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_qsm_quizzes' ) ) {
		return array();
	}

	global $wpdb;

	$current_user  = get_current_user_id();
	$quizzes_table = $wpdb->prefix . 'mlw_quizzes';

	// Two ownership sources in one round trip: the recorded author, and -- for
	// legacy quizzes only -- the author of the backing qsm_quiz post. Deliberately
	// not memoised: qsm_quiz_access_sql() calls this several times per request,
	// but a stale answer in an authorization helper is a worse bug than the
	// query it saves.
	$quiz_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT quiz_id FROM {$quizzes_table} WHERE quiz_author_id = %d
			UNION
			SELECT q.quiz_id FROM {$quizzes_table} q
			INNER JOIN {$wpdb->postmeta} pm ON pm.meta_key = 'quiz_id' AND pm.meta_value = q.quiz_id
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE ( q.quiz_author_id IS NULL OR CAST( q.quiz_author_id AS UNSIGNED ) = 0 )
				AND p.post_type = 'qsm_quiz' AND p.post_status != 'trash'
				AND p.post_author = %d",
			$current_user,
			$current_user
		)
	);

	return array_values( array_unique( array_filter( array_map( 'intval', (array) $quiz_ids ) ) ) );
}

/**
 * Builds the SQL fragment restricting a question query to the quizzes the
 * current user may edit.
 *
 * The PHP-side companion, qsm_filter_questions_by_quiz_access(), drops rows
 * after they are fetched, which is fine for an unpaginated listing but makes a
 * paginated one report a row count it does not return. Constraining the query
 * itself keeps COUNT(), LIMIT and the returned rows consistent.
 *
 * Returns an empty string for users who may edit other people's quizzes, so
 * their queries are unchanged.
 *
 * @since 11.2.4
 * @param string $column The quiz id column to constrain. Must not be user input.
 * @return string A SQL fragment beginning with ' AND ', or '' for no restriction.
 */
function qsm_quiz_access_sql( $column = 'quiz_id' ) {
	if ( current_user_can( 'edit_others_qsm_quizzes' ) ) {
		return '';
	}

	$quiz_ids = qsm_get_editable_quiz_ids();
	if ( empty( $quiz_ids ) ) {
		// Owns nothing: match no rows rather than every row.
		return ' AND 1 = 0';
	}

	return " AND $column IN (" . implode( ',', $quiz_ids ) . ')';
}

/**
 * Filters a question list down to the quizzes the current user may edit.
 *
 * Used by the unscoped listings, which load questions across every quiz on the
 * site and would otherwise disclose other authors' questions to any user
 * holding the flat edit_qsm_quizzes capability.
 *
 * Ownership is resolved once per quiz, so the cost is one lookup per distinct
 * quiz rather than per question. Users who may edit other people's quizzes get
 * the unfiltered list. Prefer qsm_quiz_access_sql() where the query is
 * paginated, so the row count stays consistent with the rows returned.
 *
 * @since 11.2.4
 * @param array $questions Questions keyed by question id, each with a quiz_id.
 * @return array The questions belonging to quizzes the user may edit.
 */
function qsm_filter_questions_by_quiz_access( $questions ) {
	if ( ! is_array( $questions ) || empty( $questions ) ) {
		return array();
	}

	if ( current_user_can( 'edit_others_qsm_quizzes' ) ) {
		return $questions;
	}

	$can_edit = array();
	foreach ( $questions as $key => $question ) {
		$quiz_id = isset( $question['quiz_id'] ) ? intval( $question['quiz_id'] ) : 0;
		if ( ! isset( $can_edit[ $quiz_id ] ) ) {
			$can_edit[ $quiz_id ] = qsm_current_user_can_edit_quiz( $quiz_id );
		}
		if ( ! $can_edit[ $quiz_id ] ) {
			unset( $questions[ $key ] );
		}
	}

	return $questions;
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
