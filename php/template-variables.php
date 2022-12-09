<?php
/**
 * This file contains all the variables that are in the plugin. It registers them and then makes them available for use.
 *
 * This plugin also contains the social media variables and all of there uses.
 *
 * @since 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Template variable files
require 'template-variables/qsm-tempvar-question-answers.php';
// Template variable files for backward compatibility ( @since QSM 7.3.8 )
require 'backward-compatibility/qsm-backward-compatibility-template-variables.php';

add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_all_contact_fields_variable', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_contact_field_variable', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qmn_variable_category_points', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qmn_variable_average_category_points', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qmn_variable_category_score', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qmn_variable_category_average_score', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qmn_variable_category_average_points', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_point_score', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_average_point', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_amount_correct', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_amount_incorrect', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_total_questions', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_correct_score', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_quiz_name', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_quiz_links', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_name', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_business', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_phone', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_email', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_question_answers', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_comments', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_timer', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_timer_minutes', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_timer_seconds', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_date', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_finished_time', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_date_taken', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_social_share', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_variable_result_id', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_variable_single_question_answer', 20, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_variable_single_answer', 20, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_variable_total_possible_points', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_variable_total_attempted_questions', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_full_name', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_variable_poll_result', 10, 3 );
add_filter( 'qmn_end_results', 'qsm_variable_poll_result', 10, 3 );
add_filter( 'mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_quiz_name', 10, 2 );
add_filter( 'mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_quiz_links', 10, 2 );
add_filter( 'mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_date', 10, 2 );
add_filter( 'mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_current_user', 10, 2 );
add_filter( 'mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_social_share', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_variable_minimum_points', 10, 2 );

/**
 * Changed the display structure to new structure.
 *
 * @since 6.4.11
 * @param string $content
 * @param array  $mlw_quiz_array
 * Show particular question answer.
 */
function qsm_variable_single_question_answer( $content, $mlw_quiz_array ) {
	$quiz_id = is_object( $mlw_quiz_array ) ? $mlw_quiz_array->quiz_id : $mlw_quiz_array['quiz_id'];
	while ( false !== strpos( $content, '%QUESTION_ANSWER_' ) ) {
		$question_id = mlw_qmn_get_string_between( $content, '%QUESTION_ANSWER_', '%' );
		if ( 'X' === $question_id ) {
			$content = str_replace( '%QUESTION_ANSWER_' . $question_id . '%', '', $content );
			return $content;
		} elseif ( 'CORRECT' === $question_id || 'INCORRECT' === $question_id || 'GROUP' === $question_id ) {
			return $content;
		}
		$question_answers_array = isset( $mlw_quiz_array['question_answers_array'] ) ? $mlw_quiz_array['question_answers_array'] : array();
		$key                    = array_search( $question_id, array_column( $question_answers_array, 'id' ), true );
		if ( isset( $question_answers_array[ $key ] ) ) {
			global $mlwQuizMasterNext;
			$answer = $question_answers_array[ $key ];
			if ( isset( $mlw_quiz_array['email_processed'] ) && 'yes' === $mlw_quiz_array['email_processed'] ) {
				$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'question_answer_email_template', '%QUESTION%<br/>Answer Provided: %USER_ANSWER%<br/>Correct Answer: %CORRECT_ANSWER%<br/>Comments Entered: %USER_COMMENTS%' );
				if ( isset( $mlw_quiz_array['quiz_settings'] ) && ! empty( $mlw_quiz_array['quiz_settings'] ) ) {
					$quiz_text_settings           = isset( $mlw_quiz_array['quiz_settings']['quiz_text'] ) ? qmn_sanitize_input_data( $mlw_quiz_array['quiz_settings']['quiz_text'], true ) : array();
					$qmn_question_answer_template = isset( $quiz_text_settings['question_answer_email_template'] ) ? apply_filters( 'qsm_section_setting_text', $quiz_text_settings['question_answer_email_template'] ) : $qmn_question_answer_template;
				}
				$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_question_answer_template, "quiz_question_answer_email_template-{$quiz_id}" );
			} else {
				$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'question_answer_template', '%QUESTION%<br/>%USER_ANSWERS_DEFAULT%' );
				if ( isset( $mlw_quiz_array['quiz_settings'] ) && ! empty( $mlw_quiz_array['quiz_settings'] ) ) {
					$quiz_text_settings           = isset( $mlw_quiz_array['quiz_settings']['quiz_text'] ) ? qmn_sanitize_input_data( $mlw_quiz_array['quiz_settings']['quiz_text'], true ) : array();
					$qmn_question_answer_template = isset( $quiz_text_settings['question_answer_template'] ) ? apply_filters( 'qsm_section_setting_text', $quiz_text_settings['question_answer_template'] ) : $qmn_question_answer_template;
				}
				$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_question_answer_template, "quiz_question_answer_template-{$quiz_id}" );
			}
			$mlw_question_answer_display = htmlspecialchars_decode( $qmn_question_answer_template, ENT_QUOTES );
			$questions                   = QSM_Questions::load_questions_by_pages( $mlw_quiz_array['quiz_id'] );
			$qmn_questions               = array();
			foreach ( $questions as $question ) {
				$qmn_questions[ $question['question_id'] ] = $question['question_answer_info'];
			}
			$total_question_cnt = 2;
			$qsm_question_cnt   = 1;
			$display            = qsm_questions_answers_shortcode_to_text( $mlw_quiz_array, $mlw_question_answer_display, $questions, $qmn_questions, $answer, $qsm_question_cnt, $total_question_cnt );
			$content            = str_replace( '%QUESTION_ANSWER_' . $question_id . '%', $display, $content );
		}
	}
	return $content;
}
/**
 * Changed the display structure to new structure.
 *
 * @since 8.0.3
 * @param string $content
 * @param array  $mlw_quiz_array
 * Show particular  answer.
 */

function qsm_variable_single_answer( $content, $mlw_quiz_array ) {
	global $mlwQuizMasterNext,$wpdb;
	$quiz_id = is_object( $mlw_quiz_array ) ? $mlw_quiz_array->quiz_id : $mlw_quiz_array['quiz_id'];
	while ( false !== strpos( $content, '%ANSWER_' ) ) {
		$question_id = mlw_qmn_get_string_between( $content, '%ANSWER_', '%' );
		$question_answers_array = isset( $mlw_quiz_array['question_answers_array'] ) ? $mlw_quiz_array['question_answers_array'] : array();
		$key                    = array_search( $question_id, array_column( $question_answers_array, 'id' ), true );
		if ( isset( $question_answers_array[ $key ] ) ) {
			$answers = $question_answers_array[ $key ];
			$ser_answer             = $wpdb->get_row( $wpdb->prepare( "SELECT question_settings FROM {$wpdb->prefix}mlw_questions WHERE question_id = %d", $question_id ), ARRAY_A );
		    $question_settings      = qmn_sanitize_input_data( $ser_answer['question_settings'] );
			$answerstr              = "";
			if ( isset($answers['user_answer']) ) {
				if ( 13 === intval( $answers['question_type'] ) ) {
					$answerstr .= $answers['points'];
				}elseif ( 'rich' === $question_settings['answerEditor'] ) {
					foreach ( $answers['user_answer'] as $answer ) {
					$answerstr .= htmlspecialchars_decode($answer);
					}
				}
				elseif ( 'image' === $question_settings['answerEditor'] ) {
					foreach ( $answers['user_answer'] as $answer ) {
					$answerstr .= '<span class="qmn_image_option" ><img src="' . htmlspecialchars_decode($answer, ENT_QUOTES ) . '"/></span>';
					}
				}else {
					$answerstr .= implode(",",$answers['user_answer']);
				}


				$content = str_replace( '%ANSWER_' . $question_id . '%',$answerstr , $content );

		}
	}
	}
return $content;
}
/**
 * Replace total_possible_points variable with actual points
 *
 * @since 7.0.2
 *
 * @param  string $content
 * @param  array  $mlw_quiz_array
 * @return string $content
 */
function qsm_variable_total_possible_points( $content, $mlw_quiz_array ) {
	if ( isset( $mlw_quiz_array['total_possible_points'] ) ) {
		$content = str_replace( '%MAXIMUM_POINTS%', $mlw_quiz_array['total_possible_points'], $content );
	}
	return $content;
}

/**
 * Replace total_possible_points variable with actual points
 *
 * @since 7.0.2
 *
 * @param  string $content
 * @param  array  $mlw_quiz_array
 * @return string $content
 */
function qsm_variable_total_attempted_questions( $content, $mlw_quiz_array ) {
	$total_attempted_questions = isset( $mlw_quiz_array['total_attempted_questions'] ) ? $mlw_quiz_array['total_attempted_questions'] : 0;
	$content                   = str_replace( '%AMOUNT_ATTEMPTED%', $total_attempted_questions, $content );
	return $content;
}

/**
 * Show poll result
 *
 * @param string $content
 * @param array  $mlw_quiz_array
 */
function qsm_variable_poll_result( $content, $mlw_quiz_array ) {
	$quiz_id = is_object( $mlw_quiz_array ) ? $mlw_quiz_array->quiz_id : $mlw_quiz_array['quiz_id'];
	while ( false !== strpos( $content, '%POLL_RESULTS_' ) ) {
		$question_id = mlw_qmn_get_string_between( $content, '%POLL_RESULTS_', '%' );
		if ( 'X' === $question_id ) {
			$content = str_replace( '%POLL_RESULTS_' . $question_id . '%', '', $content );
			return $content;
		}
		global $wpdb;
		$total_query            = $wpdb->get_row( $wpdb->prepare( "SELECT count(*) AS total_count FROM {$wpdb->prefix}mlw_results WHERE quiz_id = %d", $quiz_id ), ARRAY_A );
		$total_result           = $total_query['total_count'];
		$ser_answer             = $wpdb->get_row( $wpdb->prepare( "SELECT answer_array,question_settings FROM {$wpdb->prefix}mlw_questions WHERE question_id = %d", $question_id ), ARRAY_A );
		$ser_answer_arry        = qmn_sanitize_input_data( $ser_answer['answer_array'] );
		$question_settings      = qmn_sanitize_input_data( $ser_answer['question_settings'] );
		$ser_answer_arry_change = array_filter( array_merge( array( 0 ), $ser_answer_arry ) );
		$total_quiz_results     = $wpdb->get_results( $wpdb->prepare( "SELECT quiz_results FROM {$wpdb->prefix}mlw_results WHERE quiz_id = %d", $quiz_id ), ARRAY_A );
		$answer_array           = array();
		if ( $total_quiz_results ) {
			foreach ( $total_quiz_results as $key => $value ) {
				$userdb = qmn_sanitize_input_data( $value['quiz_results'] );
				if ( ! empty( $userdb ) ) {
					$key            = array_search( $question_id, array_column( $userdb[1], 'id' ), true );
					$answer_array[] = isset( $userdb[1][ $key ] ) ? $userdb[1][ $key ][1] : '';
				}
			}
		}
		$vals = array_count_values( $answer_array );
		$str  = '';
		if ( $vals ) {
			$str .= '<h4>' . __( 'Poll Result', 'quiz-master-next' ) . ':</h4>';
			foreach ( $vals as $answer_str => $answer_count ) {
				if ( '' !== $answer_str && qsm_find_key_from_array( $answer_str, $ser_answer_arry_change ) ) {
					$percentage = number_format( $answer_count / $total_result * 100, 2 );
					$answer_str = qsm_answers_type_evaluated( $answer_str, $question_settings );
					$str       .= $answer_str . ' : ' . $percentage . '%<br/>';
					$str       .= '<progress value="' . $percentage . '" max="100">' . $percentage . ' %</progress><br/>';
				}
			}
		}
		$content = str_replace( '%POLL_RESULTS_' . $question_id . '%', $str, $content );
	}
	return $content;
}
/**
 * Show Answer type evaluated
 *
 * @param string $answer
 * @param array  $question_settings
 */
function qsm_answers_type_evaluated( $answer, $question_settings ) {
	if ( 'rich' === $question_settings['answerEditor'] ) {
		$answer = htmlspecialchars_decode( $answer );
	} elseif ( 'image' === $question_settings['answerEditor'] ) {
		$answer = '<span class="qmn_image_option" ><img src="' . htmlspecialchars_decode( $answer, ENT_QUOTES ) . '"/></span>';
	}
	return $answer;
}
function mlw_qmn_get_string_between( $string, $start, $end ) {
	$string = ' ' . $string;
	$ini    = strpos( $string, $start );
	if ( 0 == $ini ) {
		return '';
	}

	$ini += strlen( $start );
	$len  = strpos( $string, $end, $ini ) - $ini;
	return substr( $string, $ini, $len );
}

function qsm_find_key_from_array( $search_value, $array ) {
	if ( $array ) {
		$search_value = htmlspecialchars_decode( $search_value, ENT_QUOTES );
		foreach ( $array as $key => $value ) {
			$value_val = $value[0];
			if ( $value_val == $search_value ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Adds Social sharing links
 */
function mlw_qmn_variable_social_share( $content, $mlw_quiz_array ) {
	global $wpdb, $mlwQuizMasterNext;
	$page_link = qsm_get_post_id_from_quiz_id( $mlw_quiz_array['quiz_id'] );

	if ( false !== strpos( $content, '%FACEBOOK_SHARE%' ) ) {
		$settings        = (array) get_option( 'qmn-settings' );
		$facebook_app_id = '594986844960937';
		if ( isset( $settings['facebook_app_id'] ) ) {
			$facebook_app_id = esc_js( $settings['facebook_app_id'] );
		}
		$url            = qsm_get_post_id_from_quiz_id( $mlw_quiz_array['quiz_id'] );
		$page_link      = $url . '?result_id=' . '%FB_RESULT_ID%';
		$sharing        = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'facebook_sharing_text', '%QUIZ_NAME%' );
		$sharing        = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $sharing, "quiz_facebook_sharing_text-{$mlw_quiz_array['quiz_id']}" );
		$sharing        = apply_filters( 'mlw_qmn_template_variable_results_page', $sharing, $mlw_quiz_array );
		$fb_image       = plugins_url( '', dirname( __FILE__ ) ) . '/assets/facebook.png';
		$social_display = "<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('facebook', '" . esc_js( $sharing ) . "', '" . esc_js( $mlw_quiz_array['quiz_name'] ) . "', '$facebook_app_id', '$page_link');\"><img src='" . $fb_image . "' alt='" . __( 'Facebbok Share', 'quiz-master-next' ) . "' /></a>";
		$content        = str_replace( '%FACEBOOK_SHARE%', $social_display, $content );
	}
	if ( false !== strpos( $content, '%TWITTER_SHARE%' ) ) {
		$tw_image       = plugins_url( '', dirname( __FILE__ ) ) . '/assets/twitter.png';
		$sharing        = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'twitter_sharing_text', '%QUIZ_NAME%' );
		$sharing        = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $sharing, "quiz_twitter_sharing_text-{$mlw_quiz_array['quiz_id']}" );
		$sharing        = apply_filters( 'mlw_qmn_template_variable_results_page', $sharing, $mlw_quiz_array );
		$social_display = "<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('twitter', '" . esc_js( $sharing ) . "', '" . esc_js( $mlw_quiz_array['quiz_name'] ) . "');\"><img src='" . $tw_image . "' alt='" . __( 'Twitter Share', 'quiz-master-next' ) . "' /></a>";
		$content        = str_replace( '%TWITTER_SHARE%', $social_display, $content );
	}
	return $content;
}

/**
 * Adds result id using the %RESULT_ID% variable
 */
function qsm_variable_result_id( $content, $mlw_quiz_array ) {
	while ( false !== strpos( $content, '%RESULT_ID%' ) ) {
		global $wpdb;
		$get_last_id = $wpdb->get_row( "SELECT result_id FROM {$wpdb->prefix}mlw_results ORDER BY result_id DESC", ARRAY_A );
		$content     = str_replace( '%RESULT_ID%', $get_last_id['result_id'], $content );
	}
	return $content;
}

function mlw_qmn_variable_point_score( $content, $mlw_quiz_array ) {
	$content = str_replace( '%POINT_SCORE%', ( isset( $mlw_quiz_array['total_points'] ) ? $mlw_quiz_array['total_points'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_average_point( $content, $mlw_quiz_array ) {
	$question_total = 0;
	if ( isset( $mlw_quiz_array['question_answers_array'] ) ) {
		foreach ( $mlw_quiz_array['question_answers_array'] as $single_question ) {
			if ( '11' !== $single_question['question_type'] ) {
				$question_total++;
			}
		}
	}
	if ( isset( $mlw_quiz_array['total_questions'] ) && 0 != $mlw_quiz_array['total_questions'] && 0 != $question_total ) {
		if ( qsm_is_allow_score_roundoff() ) {
			$mlw_average_points = round( $mlw_quiz_array['total_points'] / $question_total );
		} else {
			$mlw_average_points = round( $mlw_quiz_array['total_points'] / $question_total, 2 );
		}
	} else {
		$mlw_average_points = 0;
	}
	$content = str_replace( '%AVERAGE_POINT%', $mlw_average_points, $content );
	return $content;
}

function mlw_qmn_variable_amount_correct( $content, $mlw_quiz_array ) {
	$content = str_replace( '%AMOUNT_CORRECT%', ( isset( $mlw_quiz_array['total_correct'] ) ? $mlw_quiz_array['total_correct'] : '' ), $content );
	return $content;
}

/**
 * Return total incorrect amount
 *
 * @since  7.0.3
 * @param  string $content
 * @param  array  $mlw_quiz_array
 * @return string
 */
function mlw_qmn_variable_amount_incorrect( $content, $mlw_quiz_array ) {
	if ( false !== strpos( $content, '%AMOUNT_INCORRECT%' ) ) {
		$total_attempted_question = $mlw_quiz_array['total_attempted_questions'];
		$total_correct            = $mlw_quiz_array['total_correct'];
		$total_incorrect          = $total_attempted_question - $total_correct;
		$content                  = str_replace( '%AMOUNT_INCORRECT%', max( $total_incorrect, 0 ), $content );
	}
	return $content;
}

function mlw_qmn_variable_total_questions( $content, $mlw_quiz_array ) {
	$content = str_replace( '%TOTAL_QUESTIONS%', ( isset( $mlw_quiz_array['total_questions'] ) ? $mlw_quiz_array['total_questions'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_correct_score( $content, $mlw_quiz_array ) {
	$content = str_replace( '%CORRECT_SCORE%', ( isset( $mlw_quiz_array['total_score'] ) ? round( $mlw_quiz_array['total_score'] ) : '' ), $content );
	return $content;
}

function mlw_qmn_variable_quiz_name( $content, $mlw_quiz_array ) {
	$content = str_replace( '%QUIZ_NAME%', ( isset( $mlw_quiz_array['quiz_name'] ) ? $mlw_quiz_array['quiz_name'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_quiz_links( $content, $mlw_quiz_array ) {
	global $wpdb;
	$quiz_link = qsm_get_post_id_from_quiz_id( $mlw_quiz_array['quiz_id'] );
	if ( false !== strpos( $content, '%QUIZ_LINK%' ) ) {
		$content = str_replace( '%QUIZ_LINK%', $quiz_link, $content );
	}
	if ( false !== strpos( $content, '%RESULT_LINK%' ) ) {
		$result_link = $quiz_link;
		if ( isset( $mlw_quiz_array['result_id'] ) ) {
			$unique_id   = $wpdb->get_var( $wpdb->prepare( "SELECT unique_id FROM {$wpdb->prefix}mlw_results WHERE quiz_id=%d AND result_id=%d", $mlw_quiz_array['quiz_id'], $mlw_quiz_array['result_id'] ) );
			$result_link = add_query_arg( 'result_id', $unique_id, $quiz_link );
		}
		$content = str_replace( '%RESULT_LINK%', $result_link, $content );
	}
	return $content;
}

function mlw_qmn_variable_user_name( $content, $mlw_quiz_array ) {
	$content = str_replace( '%USER_NAME%', ( isset( $mlw_quiz_array['user_name'] ) ? $mlw_quiz_array['user_name'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_current_user( $content, $mlw_quiz_array ) {
	$current_user = wp_get_current_user();
	$content      = str_replace( '%USER_NAME%', $current_user->display_name, $content );
	return $content;
}
/**
 * Returns full name of user
 *
 * @since  7.1.11
 * @param  string $content
 * @param  array  $mlw_quiz_array
 * @return string
 */
function mlw_qmn_variable_user_full_name( $content, $mlw_quiz_array ) {
	if ( false !== strpos( $content, '%FULL_NAME%' ) ) {
		$full_name       = '';
		$user_id         = isset( $mlw_quiz_array['user_id'] ) ? $mlw_quiz_array['user_id'] : 0;
		$current_user_id = get_current_user_id();
		if ( is_admin() && $user_id != $current_user_id ) {
			$current_user_id = $user_id;
		}

		$user = get_user_by( 'ID', $current_user_id );
		if ( $user ) {
			$firstname   = get_user_meta( $user->ID, 'first_name', true );
			$lastname    = get_user_meta( $user->ID, 'last_name', true );
			if ( ! empty( $firstname ) && ! empty( $lastname ) ) {
				$full_name = $firstname . ' ' . $lastname;
			} else {
				$full_name = $current_user->display_name;
			}
		}

		$content = str_replace( '%FULL_NAME%', ( isset( $full_name ) ? $full_name : '' ), $content );
	}
	return $content;
}

function mlw_qmn_variable_user_business( $content, $mlw_quiz_array ) {
	$content = str_replace( '%USER_BUSINESS%', ( isset( $mlw_quiz_array['user_business'] ) ? $mlw_quiz_array['user_business'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_user_phone( $content, $mlw_quiz_array ) {
	$content = str_replace( '%USER_PHONE%', ( isset( $mlw_quiz_array['user_phone'] ) ? $mlw_quiz_array['user_phone'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_user_email( $content, $mlw_quiz_array ) {
	$content = str_replace( '%USER_EMAIL%', ( isset( $mlw_quiz_array['user_email'] ) ? $mlw_quiz_array['user_email'] : '' ), $content );
	return $content;
}

/**
 * Returns user value for supplied contact field
 *
 * @since  5.0.0
 * @return string The HTML for the content
 */
function qsm_contact_field_variable( $content, $results_array ) {
	preg_match_all( '~%CONTACT_(.*?)%~i', $content, $matches );
	for ( $i = 0; $i < count( $matches[0] ); $i++ ) {
		$content = str_replace( '%CONTACT_' . $matches[1][ $i ] . '%', $results_array['contact'][ $matches[1][ $i ] - 1 ]['value'], $content );
	}
	return $content;
}

/**
 * Returns user values for all contact fields
 *
 * @since  5.0.0
 * @return string The HTML for the content
 */
function qsm_all_contact_fields_variable( $content, $results ) {
	$return = '';
	if ( isset( $results['contact'] ) && ( is_array( $results['contact'] ) || is_object( $results['contact'] ) ) ) {
		for ( $i = 0; $i < count( $results['contact'] ); $i++ ) {
			$return .= $results['contact'][ $i ]['label'] . ': ' . $results['contact'][ $i ]['value'] . '<br>';
		}
	}
	$content = str_replace( '%CONTACT_ALL%', $return, $content );
	return $content;
}

/**
 * Converts the %QUESTIONS_ANSWERS% into the template
 *
 * @param string $content        The content to be checked for the template
 * @param array  $mlw_quiz_array The array for the response data
 */
function mlw_qmn_variable_question_answers( $content, $mlw_quiz_array ) {
	global $mlwQuizMasterNext;
	$quiz_id = is_object( $mlw_quiz_array ) ? $mlw_quiz_array->quiz_id : $mlw_quiz_array['quiz_id'];
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
	$logic_rules      = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'logic_rules' );
	$logic_rules      = qmn_sanitize_input_data( $logic_rules );
	$hidden_questions = isset( $mlw_quiz_array['hidden_questions'] ) ? $mlw_quiz_array['hidden_questions'] : array();
	if ( empty( $hidden_questions ) ) {
		$hidden_questions = isset( $mlw_quiz_array['results']['hidden_questions'] ) ? $mlw_quiz_array['results']['hidden_questions'] : array();
	}
	// Checks if the variable is present in the content.
	while ( strpos( $content, '%QUESTIONS_ANSWERS%' ) !== false || strpos( $content, '%QUESTIONS_ANSWERS_EMAIL%' ) !== false ) {
		global $wpdb;
		$display = '';
		if ( strpos( $content, '%QUESTIONS_ANSWERS_EMAIL%' ) !== false ) {
			$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'question_answer_email_template', '%QUESTION%<br/>Answer Provided: %USER_ANSWER%<br/>Correct Answer: %CORRECT_ANSWER%<br/>Comments Entered: %USER_COMMENTS%' );
			if ( isset( $mlw_quiz_array['quiz_settings'] ) && ! empty( $mlw_quiz_array['quiz_settings'] ) ) {
				$quiz_text_settings           = isset( $mlw_quiz_array['quiz_settings']['quiz_text'] ) ? qmn_sanitize_input_data( $mlw_quiz_array['quiz_settings']['quiz_text'], true ) : array();
				$qmn_question_answer_template = isset( $quiz_text_settings['question_answer_email_template'] ) ? apply_filters( 'qsm_section_setting_text', $quiz_text_settings['question_answer_email_template'] ) : $qmn_question_answer_template;
			}
			$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_question_answer_template, "quiz_question_answer_email_template-{$quiz_id}" );
		} else {
			$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'question_answer_template', '%QUESTION%<br/>%USER_ANSWERS_DEFAULT%' );
			if ( isset( $mlw_quiz_array['quiz_settings'] ) && ! empty( $mlw_quiz_array['quiz_settings'] ) ) {
				$quiz_text_settings           = isset( $mlw_quiz_array['quiz_settings']['quiz_text'] ) ? qmn_sanitize_input_data( $mlw_quiz_array['quiz_settings']['quiz_text'], true ) : array();
				$qmn_question_answer_template = isset( $quiz_text_settings['question_answer_template'] ) ? apply_filters( 'qsm_section_setting_text', $quiz_text_settings['question_answer_template'] ) : $qmn_question_answer_template;
			}
			$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_question_answer_template, "quiz_question_answer_template-{$quiz_id}" );
		}
		$questions     = QSM_Questions::load_questions_by_pages( $mlw_quiz_array['quiz_id'] );
		$qmn_questions = array();
		foreach ( $questions as $question ) {
			$qmn_questions[ $question['question_id'] ] = $question['question_answer_info'];
		}

		// Cycles through each answer in the responses.
		$total_question_cnt = count( $mlw_quiz_array['question_answers_array'] );
		$qsm_question_cnt   = 1;
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( in_array( $answer['id'], $hidden_questions, true ) ) {
				continue;
			}
			$display .= qsm_questions_answers_shortcode_to_text( $mlw_quiz_array, $qmn_question_answer_template, $questions, $qmn_questions, $answer, $qsm_question_cnt, $total_question_cnt );
			$qsm_question_cnt++;
		}
		$display = "<div class='qsm_questions_answers_section'>{$display}</div>";
		$content = str_replace( '%QUESTIONS_ANSWERS%', $display, $content );
		$content = str_replace( '%QUESTIONS_ANSWERS_EMAIL%', $display, $content );
	}
	return $content;
}

function mlw_qmn_variable_comments( $content, $mlw_quiz_array ) {
	$content = str_replace( '%COMMENT_SECTION%', ( isset( $mlw_quiz_array['comments'] ) ? $mlw_quiz_array['comments'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_timer( $content, $mlw_quiz_array ) {
	$content = str_replace( '%TIMER%', ( isset( $mlw_quiz_array['timer'] ) ? $mlw_quiz_array['timer'] : '' ), $content );
	return $content;
}

function mlw_qmn_variable_timer_minutes( $content, $mlw_quiz_array ) {
	$mlw_minutes = ( isset( $mlw_quiz_array['timer'] ) ? floor( $mlw_quiz_array['timer'] / 60 ) : '' );
	$content     = str_replace( '%TIMER_MINUTES%', $mlw_minutes, $content );
	return $content;
}

function mlw_qmn_variable_timer_seconds( $content, $mlw_quiz_array ) {
	$mlw_minutes = ( isset( $mlw_quiz_array['timer'] ) ? (int) ( $mlw_quiz_array['timer'] % 60 ) : '' );
	$content     = str_replace( '%TIMER_SECONDS%', $mlw_minutes, $content );
	return $content;
}

/**
 * Replaces the variable %CURRENT_DATE% and displays the current date
 *
 * @param  string $content The contents of the results page
 * @param  array  $results The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function mlw_qmn_variable_date( $content, $results ) {
	$date    = date_i18n( get_option( 'date_format' ), current_time( 'h:i:s A m/d/Y' ) );
	$content = str_replace( '%CURRENT_DATE%', $date, $content );
	return $content;
}

/**
 * Replaces the variable %FINISHED_TAKEN% and displays the current date
 *
 * @param  string $content The contents of the results page
 * @param  array  $results The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function mlw_qmn_variable_finished_time( $content, $mlw_quiz_array ) {
	$date    = date_i18n( get_option( 'time_format' ), $mlw_quiz_array['time_taken'] );
	$content = str_replace( '%TIME_FINISHED%', $date, $content );
	return $content;
}

/**
 * Replaces the variable %DATE_TAKEN% and returns the date the user submitted his or her responses
 *
 * @param  string $content The contents of the results page
 * @param  array  $results The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function mlw_qmn_variable_date_taken( $content, $mlw_quiz_array ) {
	global $mlwQuizMasterNext;

	$date                = '';
	$quiz_options        = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
	$qsm_quiz_settings   = maybe_unserialize( $quiz_options->quiz_settings );
	$qsm_quiz_options    = maybe_unserialize( $qsm_quiz_settings['quiz_options'] );
	$qsm_global_settings = get_option( 'qsm-quiz-settings' );

	// check if preferred date format is set at quiz level or plugin level. Default to WP date format otherwise
	if ( isset( $qsm_quiz_options['preferred_date_format'] ) ) {
		$preferred_date_format = $qsm_quiz_options['preferred_date_format'];
	} elseif ( isset( $qsm_global_settings['preferred_date_format'] ) ) {
		$preferred_date_format = isset( $qsm_global_settings['preferred_date_format'] );
	} else {
		$preferred_date_format = get_option( 'date_format' );
	}
	// filter date format
	if ( isset( $mlw_quiz_array['time_taken'] ) ) {
		$date = date_i18n( $preferred_date_format, strtotime( $mlw_quiz_array['time_taken'] ) );
	}
	$content = str_replace( '%DATE_TAKEN%', $date, $content );

	return $content;
}

/*
 *    Replaces variable %CATEGORY_POINTS% with the points for that category
 *
 * Filter function that replaces variable %CATEGORY_POINTS% with the points from the category inside the variable tags. i.e. %CATEGORY_POINTS%category 1%/CATEGORY_POINTS%
 *
 * @since 4.0.0
 * @param string $content The contents of the results page
 * @param array $mlw_quiz_array The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function qmn_variable_category_points( $content, $mlw_quiz_array ) {
	$return_points = 0;
	while ( false !== strpos( $content, '%CATEGORY_POINTS_' ) ) {
		$total_questions = 0;
		$return_points   = 0;
		$category_name   = mlw_qmn_get_string_between( $content, '%CATEGORY_POINTS_', '%' );
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( is_array( $answer['multicategories'] ) ) {
				foreach ( $answer['multicategories'] as $category ) {
					$category_name_object = get_term_by( 'id', $category, 'qsm_category' );
					if ( $category_name_object->name == $category_name && '11' !== $answer['question_type'] ) {
						$total_questions += 1;
						$return_points   += $answer['points'];
					}
				}
			}
		}
		if ( 0 != $total_questions ) {
			$return_points = strval( $return_points );
		} else {
			$return_points = 0;
		}
		$return_points = apply_filters( 'qsm_category_points', $return_points, $category_name, $mlw_quiz_array );
		$content       = str_replace( '%CATEGORY_POINTS_' . $category_name . '%', $return_points, $content );
	}
	return $content;
}

/*
 *    Replaces variable %CATEGORY_POINTS% with the average points for that category
 *
 * Filter function that replaces variable %CATEGORY_POINTS% with the average points from the category inside the variable tags. i.e. %CATEGORY_POINTS%category 1%/CATEGORY_POINTS%
 *
 * @since 4.0.0
 * @param string $content The contents of the results page
 * @param array $mlw_quiz_array The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function qmn_variable_average_category_points( $content, $mlw_quiz_array ) {
	$return_points = 0;
	while ( false !== strpos( $content, '%AVERAGE_CATEGORY_POINTS_' ) ) {
		$return_points   = 0;
		$total_questions = 0;
		$category_name   = mlw_qmn_get_string_between( $content, '%AVERAGE_CATEGORY_POINTS_', '%' );
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( is_array( $answer['multicategories'] ) ) {
				foreach ( $answer['multicategories'] as $category ) {
					$category_name_object      = get_term_by( 'id', $category, 'qsm_category' );
					$category_name_of_question = ( ! empty( $category_name_object->name ) ? $category_name_object->name : '' );
					if ( $category_name_object->name == $category_name && '11' !== $answer['question_type'] ) {
						$total_questions += 1;
						$return_points   += $answer['points'];
					}
				}
			}
		}
		if ( 0 != $total_questions ) {
			if ( qsm_is_allow_score_roundoff() ) {
				$return_points = round( $return_points / $total_questions );
			} else {
				$return_points = round( $return_points / $total_questions, 2 );
			}
		} else {
			$return_points = 0;
		}
		$content = str_replace( '%AVERAGE_CATEGORY_POINTS_' . $category_name . '%', $return_points, $content );
	}
	return $content;
}

/*
 *    Replaces variable %CATEGORY_SCORE% with the score for that category
 *
 * Filter function that replaces variable %CATEGORY_SCORE% with the score from the category inside the variable tags. i.e. %CATEGORY_SCORE%category 1%/CATEGORY_SCORE%
 *
 * @since 4.0.0
 * @param string $content The contents of the results page
 * @param array $mlw_quiz_array The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function qmn_variable_category_score( $content, $mlw_quiz_array ) {
	global $mlwQuizMasterNext;
	$return_score    = 0;
	$total_questions = 0;
	$amount_correct  = 0;

	while ( false !== strpos( $content, '%CATEGORY_SCORE_' ) ) {
		$return_score    = 0;
		$total_questions = 0;
		$amount_correct  = 0;
		$category_name   = mlw_qmn_get_string_between( $content, '%CATEGORY_SCORE_', '%' );
		$category_data   = $mlwQuizMasterNext->migrationHelper->get_category_data( $category_name );

		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( $category_data['migrated'] ) {
				if ( is_array( $answer['multicategories'] ) ) {
					foreach ( $answer['multicategories'] as $category ) {
						$category_name_object = get_term_by( 'id', $category, 'qsm_category' );
						if ( $category_name_object->name == $category_name && '11' !== $answer['question_type'] ) {
							$total_questions += 1;
							if ( 'correct' == $answer['correct'] ) {
								$amount_correct += 1;
							}
						}
					}
				}
			} else {
				if ( $answer['category'] == $category_name ) {
					$total_questions += 1;
					if ( 'correct' == $answer['correct'] ) {
						$amount_correct += 1;
					}
				}
			}
		}

		if ( 0 != $total_questions ) {
			if ( qsm_is_allow_score_roundoff() ) {
				$return_score = round( ( ( $amount_correct / $total_questions ) * 100 ) );
			} else {
				$return_score = round( ( ( $amount_correct / $total_questions ) * 100 ), 2 );
			}
		} else {
			$return_score = 0;
		}

		$content = str_replace( '%CATEGORY_SCORE_' . $category_name . '%', $return_score, $content );
	}
	return $content;
}

/*
 *    Replaces variable %CATEGORY_AVERAGE_SCORE% with the average score for all categories
 *
 * Filter function that replaces variable %CATEGORY_AVERAGE_SCORE% with the score from all categories.
 *
 * @since 4.0.0
 * @param string $content The contents of the results page
 * @param array $mlw_quiz_array The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function qmn_variable_category_average_score( $content, $mlw_quiz_array ) {
	$return_score     = 0;
	$total_categories = 0;
	$total_score      = 0;
	$category_scores  = array();
	while ( strpos( $content, '%CATEGORY_AVERAGE_SCORE%' ) !== false ) {
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( ! isset( $category_scores[ $answer['category'] ]['total_questions'] ) ) {
				$category_scores[ $answer['category'] ]['total_questions'] = 0;
			}
			if ( ! isset( $category_scores[ $answer['category'] ]['amount_correct'] ) ) {
				$category_scores[ $answer['category'] ]['amount_correct'] = 0;
			}
			$category_scores[ $answer['category'] ]['total_questions'] += 1;
			if ( 'correct' === $answer['correct'] ) {
				$category_scores[ $answer['category'] ]['amount_correct'] += 1;
			}
		}
		foreach ( $category_scores as $category ) {
			$total_score      += $category['amount_correct'] / $category['total_questions'];
			$total_categories += 1;
		}
		if ( 0 != $total_categories ) {
			if ( qsm_is_allow_score_roundoff() ) {
				$return_score = round( ( ( $total_score / $total_categories ) * 100 ) );
			} else {
				$return_score = round( ( ( $total_score / $total_categories ) * 100 ), 2 );
			}
		} else {
			$return_score = 0;
		}
		$content = str_replace( '%CATEGORY_AVERAGE_SCORE%', $return_score, $content );
	}
	return $content;
}

/*
 *    Replaces variable %CATEGORY_AVERAGE_POINTS% with the average points for all categories
 *
 * Filter function that replaces variable %CATEGORY_AVERAGE_POINTS% with the points from all categories.
 *
 * @since 4.0.0
 * @param string $content The contents of the results page
 * @param array $mlw_quiz_array The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function qmn_variable_category_average_points( $content, $mlw_quiz_array ) {
	$return_score     = 0;
	$total_categories = 0;
	$total_points     = 0;
	$category_scores  = array();
	while ( strpos( $content, '%CATEGORY_AVERAGE_POINTS%' ) !== false ) {
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( ! isset( $category_scores[ $answer['category'] ]['points'] ) ) {
				$category_scores[ $answer['category'] ]['points'] = 0;
			}
			$category_scores[ $answer['category'] ]['points'] += $answer['points'];
		}
		foreach ( $category_scores as $category ) {
			$total_points     += $category['points'];
			$total_categories += 1;
		}
		$return_score = $total_points / $total_categories;
		$content      = str_replace( '%CATEGORY_AVERAGE_POINTS%', $return_score, $content );
	}
	return $content;
}

add_filter( 'qmn_end_results', 'qsm_end_results_rank', 9999, 3 );
function qsm_end_results_rank( $result_display, $qmn_quiz_options, $qmn_array_for_variables ) {
	while ( strpos( $result_display, '%RANK%' ) !== false ) {
		global $wpdb;
		$mlw_quiz_id     = $qmn_array_for_variables['quiz_id'];
		$mlw_result_id   = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(result_id) FROM {$wpdb->prefix}mlw_results WHERE quiz_id=%d AND deleted=0", $mlw_quiz_id ) );
		$mlw_result_data = $wpdb->get_results( $wpdb->prepare( "SELECT result_id, correct_score, point_score, quiz_results FROM {$wpdb->prefix}mlw_results WHERE quiz_id=%d AND deleted=0", $mlw_quiz_id ) );
		if ( ! empty( $mlw_result_data ) ) {
			foreach ( $mlw_result_data as $key => $mlw_eaches ) {
				$time_taken            = 0;
				$mlw_qmn_results_array = qmn_sanitize_input_data( $mlw_eaches->quiz_results );
				if ( is_array( $mlw_qmn_results_array ) ) {
					$time_taken = $mlw_qmn_results_array[0];
					if ( isset( $mlw_qmn_results_array['timer_ms'] ) && $mlw_qmn_results_array['timer_ms'] > 0 ) {
						$time_taken = $mlw_qmn_results_array['timer_ms'];
					} else {
						$time_taken = ( $time_taken * 1000 );
					}
				}
				$mlw_result_data[ $key ]->total_time_taken = $time_taken;
			}
			array_multisort( array_column( $mlw_result_data, 'correct_score' ), SORT_DESC, array_column( $mlw_result_data, 'total_time_taken' ), SORT_ASC, $mlw_result_data );
			/**
			 * Find Rank
			 */
			$rank = 0;
			foreach ( $mlw_result_data as $mlw_eaches ) {
				$rank++;
				if ( $mlw_eaches->result_id == $mlw_result_id ) {
					$mlw_rank = $rank;
				}
			}
		}
		$result_display = str_replace( '%RANK%', $mlw_rank, $result_display );
	}
	return $result_display;
}

/**
 * Add iFrame to allowed wp_kses_post tags
 *
 * @since 7.0.0
 *
 * @param array  $tags    Allowed tags, attributes, and/or entities.
 * @param string $context Context to judge allowed tags by. Allowed values are 'post'.
 *
 * @return array
 */
function qsm_custom_wpkses_post_tags( $tags, $context ) {
	if ( 'post' === $context ) {
		$tags['iframe'] = array(
			'src'             => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
		);
		$tags['video']  = array(
			'width'  => true,
			'height' => true,
		);
		$tags['source'] = array(
			'src'  => true,
			'type' => true,
		);
	}

	return $tags;
}

add_filter( 'wp_kses_allowed_html', 'qsm_custom_wpkses_post_tags', 10, 2 );

/**
 * Function will convert all the QUESIONS_ANSWERS variable into appropriate data
 *
 * @since 7.1.3
 *
 * @param  array  $mlw_quiz_array
 * @param  string $qmn_question_answer_template
 * @param  string $answer
 * @param  int    $qsm_question_cnt
 * @param  int    $total_question_cnt
 * @return string
 */
function qsm_questions_answers_shortcode_to_text( $mlw_quiz_array, $qmn_question_answer_template, $questions, $qmn_questions, $answer, $qsm_question_cnt, $total_question_cnt ) {
	global $mlwQuizMasterNext;
	$question_types = $mlwQuizMasterNext->pluginHelper->get_question_type_options();
	$quiz_options  = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
	if ( isset($answer['question_type']) && ( ( 0 == $quiz_options->show_optin && 8 == $answer['question_type'] ) || ( 0 == $quiz_options->show_text_html && 6 == $answer['question_type'] ) ) ) {
		return '';
	}

	$use_custom_default_template = array();
	foreach ( $question_types as $type ) {
		if ( isset( $type['options']['use_custom_default_template'] ) && $type['options']['use_custom_default_template'] ) {
			$use_custom_default_template[] = $type['slug'];
		}
	}
	$use_custom_user_answer_template = array();
	foreach ( $question_types as $type ) {
		if ( isset( $type['options']['use_custom_user_answer_template'] ) && $type['options']['use_custom_user_answer_template'] ) {
			$use_custom_user_answer_template[] = $type['slug'];
		}
	}
	$use_custom_correct_answer_template = array();
	foreach ( $question_types as $type ) {
		if ( isset( $type['options']['use_custom_correct_answer_template'] ) && $type['options']['use_custom_correct_answer_template'] ) {
			$use_custom_correct_answer_template[] = $type['slug'];
		}
	}

	if ( is_admin() && isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) == 'qsm_quiz_result_details' ) {
		$user_answer_class     = '';
		$question_answer_class = '';
		if ( isset( $mlw_quiz_array['form_type'] ) && 0 == $mlw_quiz_array['form_type'] ) {
			if ( 0 == $mlw_quiz_array['quiz_system'] || 3 == $mlw_quiz_array['quiz_system'] ) {
				if ( 'correct' === $answer['correct'] ) {
					$user_answer_class     = 'qmn_user_correct_answer';
					$question_answer_class = 'qmn_question_answer_correct';
				} else {
					$user_answer_class     = 'qmn_user_incorrect_answer';
					$question_answer_class = 'qmn_question_answer_incorrect';
				}
			}
		}
	} else {
		if ( isset( $mlw_quiz_array['form_type'] ) && '1' === $mlw_quiz_array['form_type'] || '2' === $mlw_quiz_array['form_type'] ) {
			$user_answer_class     = 'qmn_user_correct_answer';
			$question_answer_class = 'qmn_question_answer_correct';
		} else {
			if ( 'correct' === $answer['correct'] ) {
				$user_answer_class     = 'qmn_user_correct_answer qsm-text-correct-option qsm-text-user-correct-answer';
				$question_answer_class = 'qmn_question_answer_correct';
			} else {
				$user_answer_class     = 'qmn_user_incorrect_answer';
				$question_answer_class = 'qmn_question_answer_incorrect';
			}
		}
	}
	$open_span_tag                 = '<span class="' . $user_answer_class . '">';
	$mlw_question_answer_display   = htmlspecialchars_decode( $qmn_question_answer_template, ENT_QUOTES );
	$disable_description_on_result = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'disable_description_on_result' );
	// Get question setting
	$question_settings    = isset( $questions[ $answer['id'] ]['settings'] ) ? $questions[ $answer['id'] ]['settings'] : array();
	$question_title       = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer['question_title'], "Question-{$answer['id']}", 'QSM Questions' );
	$question_description = '';
	if ( ! empty( $answer[0] ) ) {
		$question_description = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer[0], "question-description-{$answer['id']}", 'QSM Questions' );
	}
	if ( isset( $answer['question_title'] ) && '' !== $answer['question_title'] ) {
		$add_br = '';
		if ( '' !== $answer[0] ) {
			$add_br = '<br/>';
		}
		if ( 1 == $disable_description_on_result ) {
			$mlw_question_answer_display = str_replace( '%QUESTION%', '<b>' . $question_title . '</b>', $mlw_question_answer_display );
		} else {
			$mlw_question_answer_display = str_replace( '%QUESTION%', '<b>' . $question_title . '</b>' . $add_br . $question_description . $add_br, $mlw_question_answer_display );
		}
	} else {
		$mlw_question_answer_display = str_replace( '%QUESTION%', '<b>' . $question_description . '</b>', $mlw_question_answer_display );
	}
	$mlw_question_answer_display = qsm_varibale_question_title_func( $mlw_question_answer_display, $answer['question_type'] );
	$extra_border_bottom_class   = '';
	$remove_border               = true;
	if ( 6 == $answer['question_type'] ) {
		$mlw_question_answer_display = str_replace( '%USER_ANSWERS_DEFAULT%', '', $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%USER_ANSWER%', '', $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER%', '', $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%USER_COMMENTS%', '', $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER_INFO%', '', $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%QUESTION_POINT_SCORE%', '', $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%QUESTION_MAX_POINTS%', '', $mlw_question_answer_display );
	}
	if ( strpos( $mlw_question_answer_display, '%USER_ANSWERS_DEFAULT%' ) !== false ) {
		$remove_border             = false;
		$question_with_answer_text = '';
		$extra_border_bottom_class = 'qsm-add-border-bottom';
		$question_with_text_input  = array(
			3,
			12,
			5,
			7,
		// 14,
		);
		$form_type   = isset( $mlw_quiz_array['form_type'] ) ? $mlw_quiz_array['form_type'] : 0;
		$quiz_system = isset( $mlw_quiz_array['quiz_system'] ) ? $mlw_quiz_array['quiz_system'] : 0;
		if ( isset( $answer['id'] ) && isset( $questions[ $answer['id'] ] ) && ! empty( $questions[ $answer['id'] ] ) ) {
			$total_answers             = isset( $questions[ $answer['id'] ]['answers'] ) ? $questions[ $answer['id'] ]['answers'] : array();
			if ( ! empty($_COOKIE[ 'answer_ids_'.$answer['id'] ]) ) {
				$answer_ids = explode( ',', sanitize_text_field( wp_unslash( $_COOKIE[ 'answer_ids_'.$answer['id'] ] ) ) );
				$answers_random = array();
				foreach ( $answer_ids as $key ) {
					$answers_random[ $key ] = $total_answers[ $key ];
				}
				$total_answers = $answers_random;
				setcookie('answer_ids_'.$answer['id'], "", time() - 36000, "/");
			}
			if ( $total_answers ) {
				if ( isset( $answer['question_type'] ) && in_array( intval( $answer['question_type'] ), $question_with_text_input, true ) ) {
					$do_show_wrong       = true;
					$user_given_answer   = '' === $answer[1] ? __( 'No answer provided', 'quiz-master-next' ) : htmlentities( $answer[1] );
					foreach ( $total_answers as $single_answer_key => $single_answer ) {
						$current_answer_zero = trim( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ) );
						if ( 0 == $form_type && ( 0 == $quiz_system || 3 == $quiz_system ) ) {
							if ( isset( $single_answer[2] ) && 1 == $single_answer[2] ) {
								if ( 5 == $answer['question_type'] ) {
									$user_given_answer   = trim( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $user_given_answer ) ) );
									$current_answer_zero = trim( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $current_answer_zero ) ) );
								}

								if ( 'correct' === $answer['correct'] ) {
									$question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answer">' . $user_given_answer . '</span>';
									$do_show_wrong              = false;
									break;
								}
							}
						} else {
							if ( isset( $single_answer[2] ) && 'correct' === $answer['correct'] ) {
								$question_with_answer_text .= '<span class="qsm-text-correct-option">' . $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ) . '</span>';
								$do_show_wrong              = false;
								break;
							}
						}
					}
					if ( $do_show_wrong ) {
						if ( 0 == $form_type && ( 0 == $quiz_system || 3 == $quiz_system ) ) {
							$question_with_answer_text .= '<span class="qsm-text-wrong-option">' . $user_given_answer . '</span>';
							foreach ( $total_answers as $single_answer ) {
								if ( isset( $single_answer[2] ) && 1 == $single_answer[2] ) {
									$question_with_answer_text .= '<span class="qsm-text-correct-option">' . $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ) . '</span>';
									break;
								}
							}
						} else {
							$question_with_answer_text .= '<span class="qsm-text-simple-option">' . $user_given_answer . '</span>';
						}
					}
				} elseif ( isset( $answer['question_type'] ) && 11 == $answer['question_type'] ) {
					$file_extension = substr( $answer[1], -4 );
					if ( '.jpg' === $file_extension || '.jpeg' === $file_extension || '.png' === $file_extension || '.gif' === $file_extension ) {
						$question_with_answer_text .= "$open_span_tag<img src='$answer[1]'/></span>";
					} else {
						$question_with_answer_text .= "$open_span_tag" . trim( htmlspecialchars_decode( $answer[1], ENT_QUOTES ) ) . '</span>';
					}
				} elseif ( isset( $answer['question_type'] ) && 14 == $answer['question_type'] ) {
					$match_answer          = $mlwQuizMasterNext->pluginHelper->get_question_setting( $answer['id'], 'matchAnswer' );
					$new_array_user_answer = isset( $answer['user_compare_text'] ) ? explode( '=====', $answer['user_compare_text'] ) : array();

					if ( 'sequence' === $match_answer ) {
						foreach ( $total_answers as $key => $single_answer ) {
							$show_user_answer = $new_array_user_answer[ $key ];

							$is_answer_correct = 0;
							if ( isset($answer['case_sensitive']) && 1 === intval( $answer['case_sensitive'] ) ) {
								$decode_show_user_answer   = htmlspecialchars_decode( $show_user_answer, ENT_QUOTES );
								$decode_single_user_answer = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' );
							} else {
								$decode_show_user_answer   = htmlspecialchars_decode( mb_strtoupper( $show_user_answer ), ENT_QUOTES );
								$decode_single_user_answer = mb_strtoupper( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ) );
							}

							if ( $decode_show_user_answer == $decode_single_user_answer ) {
								$is_answer_correct = 1;
							}
							$index = $key + 1;
							if ( $is_answer_correct ) {
								$question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answer">(' . $index . ') ' . $show_user_answer . '</span>';
							} else {
								if ( '' === $show_user_answer ) {
									$show_user_answer = 'No answer provided';
								}

								$question_with_answer_text .= '<span class="qsm-text-wrong-option">(' . $index . ') ' . $show_user_answer . '</span>';
								$question_with_answer_text .= '<span class="qsm-text-correct-option">(' . $index . ') ' . strval( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ) ) . '</span>';
							}
						}
					} else {
						$options        = array();
						foreach ( $total_answers as $key => $single_answer ) {
							if ( isset($answer['case_sensitive']) && 1 === intval( $answer['case_sensitive'] ) ) {
								$options[] = htmlspecialchars_decode( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ), ENT_QUOTES );
							} else {
								$options[] = mb_strtoupper( htmlspecialchars_decode( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $single_answer[0], 'answer-' . $single_answer[0], 'QSM Answers' ), ENT_QUOTES ) );
							}
						}

						if ( sizeof( $new_array_user_answer ) < sizeof( $total_answers ) ) {
							foreach ( $new_array_user_answer as $show_user_answer ) {
								if ( isset($answer['case_sensitive']) && 1 === intval( $answer['case_sensitive'] ) ) {
									$key = array_search(  $show_user_answer , $options ,true);
								}else {
									$key = array_search( mb_strtoupper( $show_user_answer ), $options ,true);
								}
								if ( false !== $key ) {
									$question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answer">' . htmlspecialchars_decode( $show_user_answer, ENT_QUOTES ) . '</span>';
								} else {
									if ( '' === $show_user_answer ) {
										$show_user_answer = 'No answer provided';
									}
									$question_with_answer_text .= '<span class="qsm-text-wrong-option">' . $show_user_answer . '</span>';
								}
							}
						} else {
							foreach ( $new_array_user_answer as $show_user_answer ) {
								if ( isset($answer['case_sensitive']) && 1 === intval( $answer['case_sensitive'] ) ) {
									$key = array_search(  $show_user_answer , $options,true );
								}else {
									$key = array_search( mb_strtoupper( $show_user_answer ), $options,true );
								}

								if ( false !== $key ) {
									$question_with_answer_text .= '<span class="qsm-text-correct-option qsm-text-user-correct-answer">' . $show_user_answer . '</span>';
								} else {

									if ( '' === $show_user_answer ) {
										$show_user_answer = 'No answer provided';
									}
									$question_with_answer_text .= '<span class="qsm-text-wrong-option">' . $show_user_answer . '</span>';
								}
							}
						}
					}
				} else {
					if ( 0 == $form_type && ( 0 == $quiz_system || 3 == $quiz_system ) ) {
						if ( isset( $answer['question_type'] ) && ( 4 == $answer['question_type'] || 10 == $answer['question_type'] ) ) {
							if ( isset( $answer['user_answer'] ) && isset( $answer['correct_answer'] ) ) {
								$question_with_answer_text .= qsm_tempvar_qa_text_qt_choice( $total_answers, $answer, $quiz_system, $question_settings, $form_type );
							} else {
								$question_with_answer_text .= qsm_bckcmp_tempvar_qa_text_qt_multi_choice_correct( $total_answers, $answer, $question_settings );
							}
						} elseif ( 13 == $answer['question_type'] ) {
							$questionid                 = $questions[ $answer['id'] ]['question_id'];
							$question_with_answer_text .= qmn_polar_display_on_resultspage( $questionid, $questions, $total_answers, $answer );
							$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER%', '', $mlw_question_answer_display );
							$mlw_question_answer_display = str_replace( '%USER_ANSWER%', $answer['points'], $mlw_question_answer_display );
						} elseif ( in_array( $answer['question_type'], $use_custom_default_template, true ) ) {
							$question_with_answer_text .= apply_filters( 'qsm_result_page_custom_default_template', $total_answers, $questions, $answer );
						} else {
							if ( isset( $answer['user_answer'] ) && isset( $answer['correct_answer'] ) ) {
								$question_with_answer_text .= qsm_tempvar_qa_text_qt_choice( $total_answers, $answer, $quiz_system, $question_settings, $form_type );
							} else {
								$question_with_answer_text .= qsm_bckcmp_tempvar_qa_text_qt_single_choice_correct( $total_answers, $answer, $question_settings );
							}
						}
					} else {
						if ( isset( $answer['question_type'] ) && ( 4 == $answer['question_type'] || 10 == $answer['question_type'] ) ) {
							if ( isset( $answer['user_answer'] ) && isset( $answer['correct_answer'] ) ) {
								$question_with_answer_text .= qsm_tempvar_qa_text_qt_choice( $total_answers, $answer, $quiz_system, $question_settings, $form_type );
							} else {
								$question_with_answer_text .= qsm_bckcmp_tempvar_qa_text_qt_multi_choice_points( $total_answers, $answer, $question_settings );
							}
						} elseif ( 13 == $answer['question_type'] ) {
							$questionid                 = $questions[ $answer['id'] ]['question_id'];
							$question_with_answer_text .= qmn_polar_display_on_resultspage( $questionid, $questions, $total_answers, $answer );
							$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER%', '', $mlw_question_answer_display );
							$mlw_question_answer_display = str_replace( '%USER_ANSWER%', $answer['points'], $mlw_question_answer_display );
						} elseif ( in_array( $answer['question_type'], $use_custom_default_template, true ) ) {
							$question_type              = $answer['question_type'];
							$question_with_answer_text .= apply_filters( 'qsm_result_page_custom_default_template', $total_answers, $questions, $answer );
						} else {
							if ( isset( $answer['user_answer'] ) && isset( $answer['correct_answer'] ) ) {
								$question_with_answer_text .= qsm_tempvar_qa_text_qt_choice( $total_answers, $answer, $quiz_system, $question_settings, $form_type );
							} else {
								$question_with_answer_text .= qsm_bckcmp_tempvar_qa_text_qt_single_choice_points( $total_answers, $answer, $question_settings );
							}
						}
					}
				}
			} else {
				if ( isset( $answer['question_type'] ) && 11 == $answer['question_type'] ) {
					$file_extension = substr( $answer[1], -4 );
					if ( '.jpg' === $file_extension || '.jpeg' === $file_extension || '.png' === $file_extension || '.gif' === $file_extension ) {
						$question_with_answer_text .= "$open_span_tag<img src='$answer[1]'/></span>";
					} else {
						$question_with_answer_text .= "$open_span_tag" . trim( htmlspecialchars_decode( $answer[1], ENT_QUOTES ) ) . '</span>';
					}
				} else {
					$question_with_answer_text .= '<span class="qsm-user-answer-text">' . preg_replace( "/[\n\r]+/", '', nl2br( htmlspecialchars_decode( $answer[1], ENT_QUOTES ) ) ) . '</span>';
				}
			}
		}
		$mlw_question_answer_display = str_replace( '%USER_ANSWERS_DEFAULT%', do_shortcode( $question_with_answer_text ), $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%USER_ANSWER%', do_shortcode( $question_with_answer_text ), $mlw_question_answer_display );
	}
	$close_span_with_br = '</span><br/>';
	if ( isset( $answer['question_type'] ) && 11 == $answer['question_type'] ) {
		$file_extension = substr( $answer[1], -4 );
		if ( '.jpg' === $file_extension || '.jpeg' === $file_extension || '.png' === $file_extension || '.gif' === $file_extension ) {
			$mlw_question_answer_display = str_replace( '%USER_ANSWER%', "$open_span_tag<img src='$answer[1]'/>$close_span_with_br", $mlw_question_answer_display );
		} else {
			$mlw_question_answer_display = str_replace( '%USER_ANSWER%', "$open_span_tag" . trim( htmlspecialchars_decode( $answer[1], ENT_QUOTES ) ) . $close_span_with_br, $mlw_question_answer_display );
		}
	} elseif ( 13 == $answer['question_type'] ) {
		$mlw_question_answer_display = str_replace( '%USER_ANSWER%', $answer['points'], $mlw_question_answer_display );
		$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER%', '', $mlw_question_answer_display );
	} elseif ( in_array( $answer['question_type'], $use_custom_user_answer_template, true ) ) {
		$qsm_user_ans                = apply_filters( 'qsm_result_page_custom_user_answer_template', $questions, $answer );
		$mlw_question_answer_display = str_replace( '%USER_ANSWER%', $qsm_user_ans, $mlw_question_answer_display );
	} else {
		$total_answers           = isset( $questions[ $answer['id'] ]['answers'] ) ? $questions[ $answer['id'] ]['answers'] : array();
		foreach ( $total_answers as $key => $single_answer ) {
			if ( ! empty($single_answer[3]) ) {
				$image_list[ $single_answer[3] ] = $single_answer[0];
			}
		}
		$user_answer_new         = $answer[1];
		$is_choice_question_type = 0 == $answer['question_type'] || 1 == $answer['question_type'] || 2 == $answer['question_type'] || 4 == $answer['question_type'] || 10 == $answer['question_type'];
		if ( $is_choice_question_type && '' === $answer[1] ) {
			$user_answer_new = __( 'No Answer Provided', 'quiz-master-next' );
		}
		if ( isset( $question_settings['answerEditor'] ) && 'image' === $question_settings['answerEditor'] && '' !== $user_answer_new ) {
			$size_style = '';
			if ( ! empty($question_settings['image_size-width']) ) {
				$size_style .= 'width:'.$question_settings['image_size-width'].'px !important;';
			}
			if ( ! empty($question_settings['image_size-height']) ) {
				$size_style .= ' height:'.$question_settings['image_size-height'].'px !important;';
			}
			$image_url                   = htmlspecialchars_decode( $user_answer_new, ENT_QUOTES );
			$caption = "";
			if ( ! empty($single_answer[3]) ) {
				$caption_name = array_search($image_url, $image_list, true);
				$caption = '<span class="qsm_image_result_caption">'.$caption_name.'</span>';
			}
			$mlw_question_answer_display = str_replace( '%USER_ANSWER%', "$open_span_tag<img src='$image_url'  style='" . esc_attr( $size_style ) . "'/>$close_span_with_br$caption ", $mlw_question_answer_display );


		} elseif ( 5 == $answer['question_type'] || 3 == $answer['question_type'] ) {
			$mlw_question_answer_display = str_replace( '%USER_ANSWER%', "$open_span_tag" . nl2br( htmlspecialchars_decode( $user_answer_new, ENT_QUOTES ) ) . $close_span_with_br, $mlw_question_answer_display );
		} else {
			$mlw_question_answer_display = str_replace( '%USER_ANSWER%', "$open_span_tag" . do_shortcode( $user_answer_new ) . $close_span_with_br, $mlw_question_answer_display );
		}
}
	$answer_2 = ! empty( $answer[2] ) ? $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answer[2], 'answer-' . $answer[2], 'QSM Answers' ) : 'NA';
	if ( in_array( $answer['question_type'], $use_custom_correct_answer_template, true ) ) {
		$qsm_correct_ans             = apply_filters( 'qsm_result_page_custom_correct_answer_template', $questions, $answer );
		$qsm_correct_ans             = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qsm_correct_ans, 'answer-' . $qsm_correct_ans, 'QSM Answers' );
		$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER%', $qsm_correct_ans, $mlw_question_answer_display );
	} elseif ( isset( $question_settings['answerEditor'] ) && 'image' === $question_settings['answerEditor'] && 'NA' !== $answer_2 ) {
		$total_answers             = isset( $questions[ $answer['id'] ]['answers'] ) ? $questions[ $answer['id'] ]['answers'] : array();
		foreach ( $total_answers as $key => $single_answer ) {
			$image_list[ $single_answer[3] ] = $single_answer[0];
		}

		$image_url                   = htmlspecialchars_decode( $answer_2, ENT_QUOTES );
		$caption = "";
		if ( ! empty($single_answer[3]) ) {
			$caption_name = array_search($image_url, $image_list, true);
			$caption = '<span class="qsm_image_result_caption">'.$caption_name.'</span>';

		}
		$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER%', '<br/><img src="' . $image_url . '"/>'.$caption, $mlw_question_answer_display );

	} else {
		$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER%', '' . do_shortcode( $answer_2 ) . '<br/>', $mlw_question_answer_display );
	}
	$answer_3                    = ! empty( $answer[3] ) ? $answer[3] : 'NA';
	$mlw_question_answer_display = str_replace( '%USER_COMMENTS%', $answer_3, $mlw_question_answer_display );
	$answer_4                    = ! empty( $qmn_questions[ $answer['id'] ] ) ? $qmn_questions[ $answer['id'] ] : 'NA';
	$answer_4                    = 6 == $answer['question_type'] ? "" : $answer_4 ;
	$mlw_question_answer_display = str_replace( '%CORRECT_ANSWER_INFO%', htmlspecialchars_decode( $answer_4, ENT_QUOTES ), $mlw_question_answer_display );
	// Point score of the particular question.
	$question_point              = isset( $answer['points'] ) ? $answer['points'] : '0';
	$mlw_question_answer_display = str_replace( '%QUESTION_POINT_SCORE%', htmlspecialchars_decode( $question_point, ENT_QUOTES ), $mlw_question_answer_display );

	$question_max_point          = ( isset( $questions[ $answer['id'] ] ) ? qsm_get_question_maximum_points( $questions[ $answer['id'] ] ) : 0 );
	$mlw_question_answer_display = str_replace( '%QUESTION_MAX_POINTS%', $question_max_point, $mlw_question_answer_display );

	$mlw_question_answer_display = do_shortcode( wp_kses_post( $mlw_question_answer_display ) );

	if ( $total_question_cnt == $qsm_question_cnt && false == $remove_border ) {
		$extra_border_bottom_class = 'qsm-remove-border-bottom';
	}
	$mlw_question_answer_display = apply_filters( 'qsm_question_answers_template_variable', $mlw_question_answer_display, $mlw_quiz_array, $answer );
	$question_obj                = ( isset( $questions[ $answer['id'] ] ) ? $questions[ $answer['id'] ] : null );
	$display                     = "<div class='qmn_question_answer $extra_border_bottom_class $question_answer_class'>" . apply_filters( 'qmn_variable_question_answers', $mlw_question_answer_display, $mlw_quiz_array, $question_obj ) . '</div>';

	return $display;
}

function qsm_get_question_maximum_points( $question = array() ) {
	$question_max_point = 0;
	if ( ! empty( $question ) && isset( $question['answers'] ) ) {
		$answer_points = array( 0 );
		foreach ( $question['answers'] as $ans ) {
			if ( isset( $ans[1] ) ) {
				$answer_points[] = intval( $ans[1] );
			}
		}
		$question_max_point = max( $answer_points );
		global $mlwQuizMasterNext;
		$question_types  = $mlwQuizMasterNext->pluginHelper->get_question_type_options();
		$multiple_choise = array();
		foreach ( $question_types as $type ) {
			if ( isset( $type['options']['multiple_choise'] ) && $type['options']['multiple_choise'] ) {
				$multiple_choise[] = $type['slug'];
			}
		}
		if ( 4 == $question['question_type_new'] || 10 == $question['question_type_new'] || in_array( $question['question_type_new'], $multiple_choise, true ) ) {
			$limit_multiple_response = ( isset( $question['settings']['limit_multiple_response'] ) ) ? intval( $question['settings']['limit_multiple_response'] ) : 0;
			if ( $limit_multiple_response > 0 && count( $answer_points ) > $limit_multiple_response ) {
				rsort( $answer_points );
				$answer_points = array_slice( $answer_points, 0, $limit_multiple_response, true );
			}
			$question_max_point = array_sum( $answer_points );
			$question_max_point = apply_filters( 'qsm_question_max_point', $question_max_point, $answer_points );
		}
	}
	return $question_max_point;
}
/**
 * check is allow round off
 *
 * @since 7.1.10
 */
function qsm_is_allow_score_roundoff() {
	global $mlwQuizMasterNext;
	$score_roundoff = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'score_roundoff' );
	$form_type      = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'form_type' );
	$system         = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'system' );

	// check if  quiz type Quiz and Geading system Correct/Incorrect Or Both Type
	if ( $score_roundoff && 0 == $form_type && ( 0 == $system || 3 == $system ) ) {
		return 1;
	} else {
		return 0;
	}
}
/**
 * Display Polor Question on Result page
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @params @answer The array that contains the answers choose by user.
 * @return $question_display Returns the content of the question
 * @since  7.1.10
 */
function qmn_polar_display_on_resultspage( $id, $question, $answers, $answer ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$answerEditor   = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$image_width = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-width' );
	$image_height = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'image_size-height' );
	$input_text   = '';
	$first_point  = isset( $answers[0][1] ) ? intval( $answers[0][1] ) : 0;
	$second_point = isset( $answers[1][1] ) ? intval( $answers[1][1] ) : 0;
	$mid_point    = ( $second_point - $first_point ) / 2;
	$is_reverse   = false;
	if ( $first_point > $second_point ) {
		$is_reverse = true;
		$mid_point  = ( $first_point - $second_point ) / 2;
	}
	$total_answer      = count( $answers );
	$id                = esc_attr( intval( $id ) );
	$answar1           = $first_point;
	$answar2           = $second_point;
	$user_answer       = $answer[1];
	$slider_date_atts  = '';
	$slider_date_atts .= ' data-answer1="' . $answar1 . '" ';
	$slider_date_atts .= ' data-answer2="' . $answar2 . '" ';
	$slider_date_atts .= ' data-is_reverse="' . intval( $is_reverse ) . '" ';
	$slider_date_atts .= ' data-answer_value="' . $user_answer . '" ';
	$mlw_require_class = '';
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredText';
	}
	if ( $answer['points'] == $answar1 ) {
		$left_polar_title_style  = "style='font-weight:900;'";
		$right_polar_title_style = "style='font-weight:100;'";
	} elseif ( $answer['points'] == $answar2 ) {
		$left_polar_title_style  = "style='font-weight:100;'";
		$right_polar_title_style = "style='font-weight:900;'";
	} elseif ( $answer['points'] == $mid_point ) {
		$left_polar_title_style  = "style='font-weight:600;'";
		$right_polar_title_style = "style='font-weight:600;'";
	} elseif ( $answer['points'] < $mid_point ) {
		$left_polar_title_style  = "style='font-weight:600;'";
		$right_polar_title_style = "style='font-weight:400;'";
	} elseif ( $answer['points'] > $mid_point ) {
		$left_polar_title_style  = "style='font-weight:400;'";
		$right_polar_title_style = "style='font-weight:600;'";
	}
	if ( $is_reverse ) {
		if ( $answer['points'] < $mid_point ) {
			$left_polar_title_style  = "style='font-weight:400;'";
			$right_polar_title_style = "style='font-weight:600;'";
		} elseif ( $answer['points'] > $mid_point ) {
			$left_polar_title_style  = "style='font-weight:600;'";
			$right_polar_title_style = "style='font-weight:400;'";
		}
	}

	$input_text       .= "<div class='left-polar-title' $left_polar_title_style >";
	if ( 'image' === $answerEditor ) {
		$size_style = '';
		if ( ! empty($image_width) ) {
			$size_style .= 'width:'.$image_width.'px !important;';
		}
		if ( ! empty($image_height) ) {
			$size_style .= ' height:'.$image_height.'px !important;';
		}
		$left_image = $answers[0][0];
		$caption_text = trim( htmlspecialchars_decode( $answers[0][3], ENT_QUOTES ) );
		$caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $caption_text, 'QSM Answers' );
		$input_text .= '<img src="'.esc_url( trim( htmlspecialchars_decode( $left_image, ENT_QUOTES ) ) ).'"  style="'.esc_attr( $size_style ).'"  />';
		$input_text .= '<span class="qsm_image_caption">'.esc_html( $caption_text ).'</span>';
	} else {
		$left_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answers[0][0], "answer-" . $answers[0][0], "QSM Answers" );
		$input_text       .= do_shortcode( wp_kses_post( $left_title ) );
	}

	$input_text       .= "</div>";
	$input_text       .= "<div class='slider-main-wrapper'><input type='hidden' class='qmn_polar $mlw_require_class' id='question" . esc_attr( $id ) . "' name='question" . esc_attr( $id ) . "' />";
	$input_text       .= '<div id="slider-' . esc_attr( $id ) . '"' . $slider_date_atts . '></div></div>';
	$input_text       .= "<div class='right-polar-title' $right_polar_title_style>";
	if ( 'image' === $answerEditor ) {
		$size_style = '';
		if ( ! empty($image_width) ) {
			$size_style .= 'width:'.$image_width.'px !important;';
		}
		if ( ! empty($image_height) ) {
			$size_style .= ' height:'.$image_height.'px !important;';
		}
		$right_image = $answers[1][0];
		$caption_text = trim( htmlspecialchars_decode( $answers[1][3], ENT_QUOTES ) );
		$caption_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $caption_text, 'caption-' . $caption_text, 'QSM Answers' );
		$input_text .= '<img src="'.esc_url( trim( htmlspecialchars_decode( $right_image, ENT_QUOTES ) ) ).'"  style="'.esc_attr( $size_style ).'"  />';
		$input_text .= '<span class="qsm_image_caption">'.esc_html( $caption_text ).'</span>';
	} else {
		$right_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $answers[1][0], "answer-" . $answers[0][0], "QSM Answers" );
		$input_text       .= do_shortcode( wp_kses_post( $right_title ) );
	}
	$input_text       .= "</div>";
	$question          = $input_text;
	$question_display .= "<span class='mlw_qmn_question mlw-qmn-question-result-$id question-type-polar-s'>" . do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) . '</span>';
	return apply_filters( 'qmn_polar_display_front', $question_display, $id, $question, $answers );
}

/**
 * Display Polor Question on Result page
 *
 * @params $data Questions Data
 * @params $strip Can Stripable ot not?
 * @return $question_display Returns the content of the question
 * @since  7.3.3
 */
function qmn_sanitize_input_data( $data, $strip = false ) {
	if ( $strip ) {
		$data = stripslashes( $data );
	}
	return maybe_unserialize( $data );
}

/**
 * Replace minimum points variable with actual miniumum points
 *
 * @since 7.0.2
 *
 * @param  string $content
 * @param  array  $mlw_quiz_array
 * @return string $content
 */
function qsm_variable_minimum_points( $content, $mlw_quiz_array ) {
	if ( isset( $mlw_quiz_array['minimum_possible_points'] ) ) {
		$content = str_replace( '%MINIMUM_POINTS%', $mlw_quiz_array['minimum_possible_points'], $content );
	}
	return $content;
}

/**
 * Replace question title variable with actual question title
 *
 * @since 7.3.6
 *
 * @param  string $question
 * @param  string $question_type
 * @param  string $new_question_title
 * @param  int    $question_id
 * @return string $question_display
 */
function qsm_varibale_question_title_func( $question, $question_type = '', $new_question_title = '', $question_id = 0 ) {
	$question_title = $question;
	global $wp_embed, $mlwQuizMasterNext, $qmn_total_questions;
	$question_title    = $wp_embed->run_shortcode( $question_title );
	$question_title    = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $question_title );
	$polar_extra_class = '';
	$question_display  = '';

	$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();

	$question_numbering   = '';
	if ( isset( $qmn_quiz_options->question_numbering ) && 1 == $qmn_quiz_options->question_numbering && 6 != $question_type ) {
		$qmn_total_questions += 1;
		$question_numbering = '<span class="mlw_qmn_question_number">' . esc_html( $qmn_total_questions ) . '.&nbsp;</span>';
	}

	if ( $question_id ) {
		$featureImageID = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'featureImageID' );
		if ( $featureImageID ) {
			$question_display .= '<div class="qsm-featured-image">' . wp_get_attachment_image( $featureImageID, apply_filters( 'qsm_filter_feature_image_size', 'full', $question_id ) ) . '</div>';
		}
	}
	if ( '' !== $new_question_title ) {
		$new_question_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $new_question_title, ENT_QUOTES ), "Question-{$question_id}", 'QSM Questions' );
		$question_display  .= "<div class='mlw_qmn_new_question'>" . $new_question_title . '</div>';
		$polar_extra_class .= ' qsm_remove_bold';
	}
	$question_display .= "<div class='mlw_qmn_question' >" . $question_numbering . do_shortcode( htmlspecialchars_decode( $question_title, ENT_QUOTES ) ) . '</div>';
	return $question_display;
}
