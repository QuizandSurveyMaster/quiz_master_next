<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_action( 'plugins_loaded', 'qmn_question_type_multiple_choice' );

/**
 * Registers the multiple choice type
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_multiple_choice() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Vertical Multiple Choice', 'quiz-master-next' ), 'qmn_multiple_choice_display', true, 'qmn_multiple_choice_review', null, null, 0 );
}

add_action( 'plugins_loaded', 'qmn_question_type_file_upload' );
/**
 * Registers the file upload type
 *
 * @return void
 * @since 6.3.7
 */
function qmn_question_type_file_upload() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'File Upload', 'quiz-master-next' ), 'qmn_file_upload_display', true, 'qmn_file_upload_review', null, null, 11 );
}

/**
 * This function shows the content of the file upload
 *
 * @param $id The ID of the multiple choice question
 * @param $question The question that is being edited.
 * @param @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 6.3.7
 */
function qmn_file_upload_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredFileUpload';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= "<div style=''></div><input type='file' class='mlw_answer_file_upload $mlw_requireClass'/>";
	$question_display  .= "<div style='display: none;' class='loading-uploaded-file'><img src='" . get_site_url() . "/wp-includes/images/spinner-2x.gif'></div>";
	$question_display  .= "<div style='display: none;' class='remove-uploaded-file'><span class='dashicons dashicons-trash'></span></div>";
	$question_display  .= "<input class='mlw_file_upload_hidden_value' type='hidden' name='question" . $id . "' value='' />";
	$question_display  .= "<span style='display: none;' class='mlw-file-upload-error-msg'></span>";
	$question_display  .= "<input class='mlw_file_upload_hidden_path' type='hidden' value='' />";
	return apply_filters( 'qmn_file_upload_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function determines how the file upload will work.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 5.3.7
 */
function qmn_file_upload_review( $id, $question, $answers ) {
		$return_array = array(
			'points'        => 0,
			'correct'       => 'incorrect',
			'user_text'     => '',
			'correct_text'  => '',
			'question_type' => 'file_upload',
		);
		if ( isset( $_POST[ 'question' . $id ] ) ) {
			$decode_user_answer = sanitize_text_field( $_POST[ 'question' . $id ] );
			$mlw_user_answer    = trim( $decode_user_answer );
		} else {
			$mlw_user_answer = ' ';
		}
		$return_array['user_text'] = $mlw_user_answer;
		foreach ( $answers as $answer ) {
			$decode_correct_text          = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$return_array['correct_text'] = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_correct_text ) ) );
			if ( mb_strtoupper( $return_array['user_text'] ) == mb_strtoupper( $return_array['correct_text'] ) ) {
				$return_array['correct'] = 'correct';
				$return_array['points']  = $answer[1];
				break;
			}
		}
		return $return_array;
}

/**
 * This function shows the content of the multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_multiple_choice_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$required           = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredRadio';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$question_display .= qsm_question_title_func( $question, 'multiple_choice', $new_question_title );
	$question_display .= "<div class='qmn_radio_answers $mlw_requireClass'>";
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				if ( $answerEditor === 'rich' ) {
					$question_display .= "<div class='qmn_mc_answer_wrap' id='question$id-$mlw_answer_total'>";
				} else {
					$question_display .= "<div class='qmn_mc_answer_wrap' id='question" . $id . '-' . str_replace( ' ', '-', esc_attr( $answer[0] ) ) . "'>";
				}
				$question_display .= "<input type='radio' class='qmn_quiz_radio' name='question" . $id . "' id='question" . $id . '_' . $mlw_answer_total . "' value='" . $answer[0] . "' />";
				$question_display .= "<label for='question" . $id . '_' . $mlw_answer_total . "'>" . trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) . '</label>';
				$question_display .= '</div>';
			}
		}
		$question_display .= "<input type='radio' style='display: none;' name='question" . $id . "' id='question" . $id . "_none' checked='checked' value='' />";
	}
	$question_display .= '</div>';
	return apply_filters( 'qmn_multiple_choice_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function determines how the multiple choice question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_multiple_choice_review( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	$answerEditor = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$mlw_user_answer = $_POST[ 'question' . $id ];
		$mlw_user_answer = trim( stripslashes( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = stripslashes( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) );
	$rich_text_comapre         = preg_replace( "/\s+|\n+|\r/", ' ', htmlentities( $mlw_user_answer ) );
	$correct_text              = array();
	foreach ( $answers as $answer ) {
		if ( $answerEditor === 'rich' ) {
			$answer_option    = htmlspecialchars_decode( $answer[0], ENT_QUOTES );
			$sinel_answer_cmp = preg_replace( "/\s+|\n+|\r/", ' ', htmlentities( $answer_option ) );
			if ( $rich_text_comapre == $sinel_answer_cmp ) {
				$return_array['points']    = $answer[1];
				$return_array['user_text'] = $answer[0];
				if ( $answer[2] == 1 ) {
					$return_array['correct'] = 'correct';
				}
			}
			if ( $answer[2] == 1 ) {
				$correct_text[] = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			}
		} else {
			$mlw_user_answer = '';
			if ( isset( $_POST[ 'question' . $id ] ) ) {
				$mlw_user_answer = $_POST[ 'question' . $id ];
				$mlw_user_answer = trim( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) );
				$mlw_user_answer = str_replace( '\\', '', $mlw_user_answer );
			}
			$single_answer = trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$single_answer = str_replace( '\\', '', $single_answer );
			if ( $mlw_user_answer == $single_answer ) {
				$return_array['points']    = $answer[1];
				$return_array['user_text'] = $answer[0];
				if ( $answer[2] == 1 ) {
					$return_array['correct'] = 'correct';
				}
			}
			if ( $answer[2] == 1 ) {
				$correct_text[] = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			}
		}
	}
	$return_array['correct_text'] = implode( '.', $correct_text );
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_date' );
/**
 * Registers the date type
 *
 * @return void
 * @since 6.3.7
 */
function qmn_question_type_date() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Date', 'quiz-master-next' ), 'qmn_date_display', true, 'qmn_date_review', null, null, 12 );
}

/**
 * This function shows the content of the date field
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 6.3.7
 */
function qmn_date_display( $id, $question, $answers ) {
	 $question_display = '';
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredDate';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= "<input type='date' class='mlw_answer_date $mlw_requireClass' name='question" . $id . "' id='question" . $id . "' value=''/>";
	// $question_display .= "<script>jQuery(document).ready(function () { jQuery('#question".$id."').datepicker();  });</script>";
	return apply_filters( 'qmn_date_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function reviews the date type.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 6.3.7
 */
function qmn_date_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$decode_user_answer = sanitize_textarea_field( strval( stripslashes( htmlspecialchars_decode( $_POST[ 'question' . $id ], ENT_QUOTES ) ) ) );
		$mlw_user_answer    = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_answer ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $mlw_user_answer;
	foreach ( $answers as $answer ) {
		$decode_correct_text          = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		$return_array['correct_text'] = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_correct_text ) ) );
		if ( mb_strtoupper( $return_array['user_text'] ) == mb_strtoupper( $return_array['correct_text'] ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_horizontal_multiple_choice' );

/**
 * This function registers the horizontal multiple choice type.
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_horizontal_multiple_choice() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Horizontal Multiple Choice', 'quiz-master-next' ), 'qmn_horizontal_multiple_choice_display', true, 'qmn_horizontal_multiple_choice_review', null, null, 1 );
}

/**
 * This function shows the content of the horizontal multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_horizontal_multiple_choice_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredRadio';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, 'horizontal_multiple_choice', $new_question_title );
	$question_display  .= "<div class='qmn_radio_answers $mlw_requireClass'>";
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				$question_display .= "<span class='mlw_horizontal_choice'><input type='radio' class='qmn_quiz_radio' name='question" . $id . "' id='question" . $id . '_' . $mlw_answer_total . "' value='" . $answer[0] . "' /><label for='question" . $id . '_' . $mlw_answer_total . "'>" . trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) . '</label></span>';
			}
		}
		$question_display .= "<input type='radio' style='display: none;' name='question" . $id . "' id='question" . $id . "_none' checked='checked' value='' />";
	}
	$question_display .= '</div>';

	return apply_filters( 'qmn_horizontal_multiple_choice_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function determines how the question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_horizontal_multiple_choice_review( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$answerEditor = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$mlw_user_answer = $_POST[ 'question' . $id ];
		$mlw_user_answer = trim( stripslashes( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$rich_text_comapre = preg_replace( "/\s+|\n+|\r/", ' ', htmlentities( $mlw_user_answer ) );
	$correct_text      = array();
	foreach ( $answers as $answer ) {
		if ( $answerEditor === 'rich' ) {
			$answer_option    = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$sinel_answer_cmp = preg_replace( "/\s+|\n+|\r/", ' ', htmlentities( $answer_option ) );
			if ( $rich_text_comapre == $sinel_answer_cmp ) {
				$return_array['points']    = $answer[1];
				$return_array['user_text'] = $answer[0];
				if ( $answer[2] == 1 ) {
					$return_array['correct'] = 'correct';
				}
			}
			if ( $answer[2] == 1 ) {
				$correct_text[] = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			}
		} else {
			$mlw_user_answer = '';
			if ( isset( $_POST[ 'question' . $id ] ) ) {
				$mlw_user_answer = $_POST[ 'question' . $id ];
				$mlw_user_answer = trim( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) );
				$mlw_user_answer = str_replace( '\\', '', $mlw_user_answer );
			}
			$single_answer = trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$single_answer = str_replace( '\\', '', $single_answer );
			if ( $mlw_user_answer == $single_answer ) {
				$return_array['points']    = $answer[1];
				$return_array['user_text'] = $answer[0];
				if ( $answer[2] == 1 ) {
					$return_array['correct'] = 'correct';
				}
			}
			if ( $answer[2] == 1 ) {
				$correct_text[] = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			}
		}
	}
	$return_array['correct_text'] = implode( '.', $correct_text );
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_drop_down' );

/**
 * This function registers the drop down question type
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_drop_down() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Drop Down', 'quiz-master-next' ), 'qmn_drop_down_display', true, 'qmn_drop_down_review', null, null, 2 );
}

/**
 * This function shows the content of the multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_drop_down_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
			$require_class = 'qsmRequiredSelect';
	} else {
			$require_class = '';
	}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= "<select class='qsm_select $require_class' name='question" . $id . "'>";
	$question_display  .= "<option value=''>" . __( 'Please select your answer', 'quiz-master-next' ) . '</option>';
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				$question_display .= "<option value='" . esc_attr( $answer[0] ) . "'>" . htmlspecialchars_decode( $answer[0], ENT_QUOTES ) . '</option>';
			}
		}
	}
	$question_display .= '</select>';
	return apply_filters( 'qmn_drop_down_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function determines how the question is graded
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_drop_down_review( $id, $question, $answers ) {
	 global $mlwQuizMasterNext;
	$answerEditor = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$mlw_user_answer = $_POST[ 'question' . $id ];
		$mlw_user_answer = trim( stripslashes( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	foreach ( $answers as $answer ) {
		$answers_loop = trim( stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) );
		if ( $mlw_user_answer == $answers_loop ) {
			$return_array['points']    = $answer[1];
			$return_array['user_text'] = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			if ( $answer[2] == 1 ) {
				$return_array['correct'] = 'correct';
			}
		}
		if ( $answer[2] == 1 ) {
			$return_array['correct_text'] = htmlspecialchars_decode( $answer[0], ENT_QUOTES );
		}
	}
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_small_open' );

/**
 * This function registers the small open question type
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_small_open() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Short Answer', 'quiz-master-next' ), 'qmn_small_open_display', true, 'qmn_small_open_review', null, null, 3 );
}

/**
 * This function shows the content of the small open answer question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_small_open_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$autofill       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'autofill' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$autofill_att   = $autofill ? "autocomplete='off' " : '';
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredText';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= '<input ' . $autofill_att . $limit_text_att . " type='text' class='mlw_answer_open_text $mlw_requireClass' name='question" . $id . "' />";
	return apply_filters( 'qmn_small_open_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function reviews the small open answer.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_small_open_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$decode_user_answer = sanitize_textarea_field( strval( stripslashes( htmlspecialchars_decode( $_POST[ 'question' . $id ], ENT_QUOTES ) ) ) );
		$mlw_user_answer    = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_answer ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $mlw_user_answer;
	foreach ( $answers as $answer ) {
		$decode_correct_text          = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		$return_array['correct_text'] = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_correct_text ) ) );
		if ( mb_strtoupper( $return_array['user_text'] ) == mb_strtoupper( $return_array['correct_text'] ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_multiple_response' );

/**
 * This function registers the multiple response question type
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_multiple_response() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Multiple Response', 'quiz-master-next' ), 'qmn_multiple_response_display', true, 'qmn_multiple_response_review', null, null, 4 );
}

/**
 * This function shows the content of the multiple response question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_multiple_response_display( $id, $question, $answers ) {
	$question_display = $limit_mr_text = '';
	global $mlwQuizMasterNext;
	$required                = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$limit_multiple_response = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_multiple_response' );
	if ( $limit_multiple_response > 0 ) {
		$limit_mr_text = 'onchange="qsmCheckMR(this,' . $limit_multiple_response . ')"';
	}
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredCheck';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= "<div class='qmn_check_answers $mlw_requireClass'>";
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				$question_display .= '<div class="qsm_check_answer">';
				$question_display .= "<input type='hidden' name='question" . $id . "' value='This value does not matter' />";
				$question_display .= "<input type='checkbox' " . $limit_mr_text . " name='question" . $id . '_' . $mlw_answer_total . "' id='question" . $id . '_' . $mlw_answer_total . "' value='" . esc_attr( $answer[0] ) . "' /> <label for='question" . $id . '_' . $mlw_answer_total . "'>" . trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) . '</label>';
				$question_display .= '</div>';
			}
		}
	}
	$question_display .= '</div>';
	return apply_filters( 'qmn_multiple_response_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function determines how the multiple response is graded,
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_multiple_response_review( $id, $question, $answers ) {
	$return_array  = array(
		'points'            => 0,
		'correct'           => 'incorrect',
		'user_text'         => '',
		'correct_text'      => '',
		'user_compare_text' => '',
	);
	$user_correct  = 0;
	$total_correct = 0;
	$total_answers = count( $answers );
	$correct_text  = array();
	foreach ( $answers as $answer ) {
		for ( $i = 1; $i <= $total_answers; $i++ ) {
			if ( isset( $_POST[ 'question' . $id . '_' . $i ] ) && sanitize_textarea_field( htmlspecialchars( stripslashes( $_POST[ 'question' . $id . '_' . $i ] ), ENT_QUOTES ) ) == esc_attr( $answer[0] ) ) {
				$return_array['points']            += $answer[1];
				$return_array['user_text']         .= htmlspecialchars_decode( $answer[0], ENT_QUOTES ) . '.';
				$return_array['user_compare_text'] .= sanitize_textarea_field( strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) . '=====';
				if ( $answer[2] == 1 ) {
					$user_correct += 1;
				} else {
					$user_correct = -1;
				}
			}
		}
		if ( $answer[2] == 1 ) {
			$correct_text[] = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$total_correct++;
		}
	}
	if ( $user_correct == $total_correct ) {
		$return_array['correct'] = 'correct';
	}
	$return_array['correct_text'] = implode( '.', $correct_text );
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_large_open' );

/**
 * This function registers the large open question type.
 *
 * @since 4.4.0
 */
function qmn_question_type_large_open() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Paragraph', 'quiz-master-next' ), 'qmn_large_open_display', true, 'qmn_large_open_review', null, null, 5 );
}

/**
 * This function displays the content of the large open question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_large_open_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required   = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$limit_text = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredText';
	} else {
		$mlw_requireClass = '';}
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= "<textarea class='mlw_answer_open_text {$mlw_requireClass}' {$limit_text_att} cols='70' rows='5' name='question{$id}' /></textarea>";
	return apply_filters( 'qmn_large_open_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function determines how the large open question is graded
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_large_open_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$decode_user_answer = sanitize_textarea_field( strval( stripslashes( htmlspecialchars_decode( $_POST[ 'question' . $id ], ENT_QUOTES ) ) ) );
		$mlw_user_answer    = trim(str_replace(' ','',preg_replace('/\s\s+/', '', $decode_user_answer )));
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $decode_user_answer;
	foreach ( $answers as $answer ) {
		$return_array['correct_text'] = $decode_correct_text = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		$decode_correct_text = trim(str_replace(' ','', preg_replace('/\s\s+/', '', $decode_correct_text )));
		if ( mb_strtoupper( $mlw_user_answer ) == mb_strtoupper( $decode_correct_text ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_text_block' );

/**
 * This function registers the text block question type
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_text_block() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
		),
		'information'  => '',
		'extra_inputs' => array(),
		'function'     => '',
	);
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Text/HTML Section', 'quiz-master-next' ), 'qmn_text_block_display', false, null, $edit_args, null, 6 );
}


/**
 * This function displays the contents of the text block question type.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_text_block_display( $id, $question, $answers ) {
	$question_display  = '';
	$question_display .= do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) );
	return $question_display;
}

add_action( 'plugins_loaded', 'qmn_question_type_number' );

/**
 * This function registers the number question type
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_number() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Number', 'quiz-master-next' ), 'qmn_number_display', true, 'qmn_number_review', null, null, 7 );
}


/**
 * This function shows the content of the multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_number_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' oninput='javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);'" : '';
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredNumber';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= "<input type='number' $limit_text_att class='mlw_answer_number $mlw_requireClass' name='question" . $id . "' />";
	return apply_filters( 'qmn_number_display_front', $question_display, $id, $question, $answers );
}


/**
 * This function determines how the number question type is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_number_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$mlw_user_answer = sanitize_textarea_field( strval( stripslashes( htmlspecialchars_decode( $_POST[ 'question' . $id ], ENT_QUOTES ) ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $mlw_user_answer;
	foreach ( $answers as $answer ) {
		$return_array['correct_text'] = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		if ( strtoupper( $return_array['user_text'] ) == strtoupper( $return_array['correct_text'] ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_accept' );

/**
 * This function registers the accept question type.
 *
 * @return void Description
 * @since 4.4.0
 */
function qmn_question_type_accept() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
			'required',
		),
		'information'  => '',
		'extra_inputs' => array(),
		'function'     => '',
	);
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Opt-in', 'quiz-master-next' ), 'qmn_accept_display', false, null, $edit_args, null, 8 );
}

/**
 * This function displays the accept question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_accept_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredAccept';
	} else {
		$mlw_requireClass = '';}
	$question_display .= "<div class='qmn_accept_answers'>";
	$question_display .= "<input type='checkbox' id='mlwAcceptance' class='$mlw_requireClass ' />";
	$question_display .= "<label for='mlwAcceptance'><span class='qmn_accept_text'>" . do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) . '</span></label>';
	$question_display .= '</div>';
	return apply_filters( 'qmn_accept_display_front', $question_display, $id, $question, $answers );
}

add_action( 'plugins_loaded', 'qmn_question_type_captcha' );

/**
 * This function registers the captcha question
 *
 * @since 4.4.0
 */
function qmn_question_type_captcha() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
			'required',
		),
		'information'  => '',
		'extra_inputs' => array(),
		'function'     => '',
	);
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Captcha', 'quiz-master-next' ), 'qmn_captcha_display', false, null, $edit_args, null, 9 );
}


/**
 * This function displays the captcha question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_captcha_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredCaptcha';
	} else {
		$mlw_requireClass = '';}
	$question_display  .= "<div class='mlw_captchaWrap'>";
	$question_display  .= "<canvas alt='' id='mlw_captcha' class='mlw_captcha' width='100' height='50'></canvas>";
	$question_display  .= '</div>';
	$question_display  .= "<span class='mlw_qmn_question'>";
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_title     = qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= $question_title . '</span>';
	$question_display  .= "<input type='text' class='mlw_answer_open_text $mlw_requireClass' id='mlw_captcha_text' name='mlw_user_captcha'/>";
	$question_display  .= "<input type='hidden' name='mlw_code_captcha' id='mlw_code_captcha' value='none' />";
	$question_display  .= "<script>
  var mlw_code = '';
  var mlw_chars = '0123456789ABCDEFGHIJKL!@#$%^&*()MNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
  var mlw_code_length = 5;
  for (var i=0; i<mlw_code_length; i++) {
          var rnum = Math.floor(Math.random() * mlw_chars.length);
          mlw_code += mlw_chars.substring(rnum,rnum+1);
      }
      var mlw_captchaCTX = document.getElementById('mlw_captcha').getContext('2d');
      mlw_captchaCTX.font = 'normal 24px Verdana';
      mlw_captchaCTX.strokeStyle = '#000000';
      mlw_captchaCTX.clearRect(0,0,100,50);
      mlw_captchaCTX.strokeText(mlw_code,10,30,70);
      mlw_captchaCTX.textBaseline = 'middle';
      document.getElementById('mlw_code_captcha').value = mlw_code;
      </script>
      ";
	return apply_filters( 'qmn_captcha_display_front', $question_display, $id, $question, $answers );
}

add_action( 'plugins_loaded', 'qmn_question_type_horizontal_multiple_response' );

/**
 * This function registers the horizontal multiple response question
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_horizontal_multiple_response() {
	 global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Horizontal Multiple Response', 'quiz-master-next' ), 'qmn_horizontal_multiple_response_display', true, 'qmn_horizontal_multiple_response_review', null, null, 10 );
}


/**
 * This function displays the content of the multiple response question type
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Contains all the content of the question
 * @since 4.4.0
 */
function qmn_horizontal_multiple_response_display( $id, $question, $answers ) {
	 $question_display = '';
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredCheck';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$limit_multiple_response = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_multiple_response' );
	$limit_mr_text           = '';
	if ( $limit_multiple_response > 0 ) {
		$limit_mr_text = 'onchange="qsmCheckMR(this,' . $limit_multiple_response . ')"';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	$question_display  .= "<div class='qmn_check_answers $mlw_requireClass'>";
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				$question_display .= "<input type='hidden' name='question" . $id . "' value='This value does not matter' />";
				$question_display .= "<span class='mlw_horizontal_multiple'><input type='checkbox' " . $limit_mr_text . " name='question" . $id . '_' . $mlw_answer_total . "' id='question" . $id . '_' . $mlw_answer_total . "' value='" . esc_attr( $answer[0] ) . "' /> <label for='question" . $id . '_' . $mlw_answer_total . "'>" . trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) . '&nbsp;</label></span>';
			}
		}
	}
	$question_display .= '</div>';
	return apply_filters( 'qmn_horizontal_multiple_response_display_front', $question_display, $id, $question, $answers );
}


/**
 * This function determines how the multiple response is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the Results page
 * @since 4.4.0
 */
function qmn_horizontal_multiple_response_review( $id, $question, $answers ) {
	$return_array  = array(
		'points'            => 0,
		'correct'           => 'incorrect',
		'user_text'         => '',
		'correct_text'      => '',
		'user_compare_text' => '',
	);
	$user_correct  = 0;
	$total_correct = 0;
	$total_answers = count( $answers );
	$correct_text  = array();
	foreach ( $answers as $answer ) {
		for ( $i = 1; $i <= $total_answers; $i++ ) {
			if ( isset( $_POST[ 'question' . $id . '_' . $i ] ) && sanitize_textarea_field( htmlspecialchars( stripslashes( $_POST[ 'question' . $id . '_' . $i ] ), ENT_QUOTES ) ) == esc_attr( $answer[0] ) ) {
				$return_array['points']            += $answer[1];
				$return_array['user_text']         .= strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) . '.';
				$return_array['user_compare_text'] .= sanitize_textarea_field( strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ) . '=====';
				if ( $answer[2] == 1 ) {
					$user_correct += 1;
				} else {
					$user_correct = -1;
				}
			}
		}
		if ( $answer[2] == 1 ) {
			$correct_text[] = stripslashes( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$total_correct++;
		}
	}
	if ( $user_correct == $total_correct ) {
		$return_array['correct'] = 'correct';
	}
	$return_array['correct_text'] = implode( '.', $correct_text );
	return $return_array;
}

add_action( 'plugins_loaded', 'qmn_question_type_fill_blank' );

/**
 * This function registers the fill in the blank question type
 *
 * @return void
 * @since 4.4.0
 */
function qmn_question_type_fill_blank() {
	global $mlwQuizMasterNext;
	$edit_args = array(
		'inputs'       => array(
			'question',
			'answer',
			'hint',
			'correct_info',
			'comments',
			'category',
			'required',
		),
		'information'  => __( 'For fill in the blank types, use %BLANK% to represent where to put the text box in your text.', 'quiz-master-next' ),
		'extra_inputs' => array(),
		'function'     => '',
	);

	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Fill In The Blank', 'quiz-master-next' ), 'qmn_fill_blank_display', true, 'qmn_fill_blank_review', $edit_args, null, 14 );

}


/**
 * This function displays the fill in the blank question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Returns the content of the question
 * @since 4.4.0
 */
function qmn_fill_blank_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$autofill       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'autofill' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$autofill_att   = $autofill ? "autocomplete='off' " : '';
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredText';
	} else {
		$mlw_requireClass = '';}
	$input_text = '<input ' . $autofill_att . $limit_text_att . " type='text' class='qmn_fill_blank $mlw_requireClass' name='question" . $id . "' />";
	if ( strpos( $question, '%BLANK%' ) !== false ) {
		$question = str_replace( '%BLANK%', $input_text, do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
		// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_display  .= qsm_question_title_func( $question, '', $new_question_title );
	return apply_filters( 'qmn_fill_blank_display_front', $question_display, $id, $question, $answers );
}


/**
 * This function determines how the fill in the blank question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 4.4.0
 */
function qmn_fill_blank_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( strpos( $question, '%BLANK%' ) !== false || strpos( $question, '%blank%' ) !== false ) {
		$return_array['question_text'] = str_replace( array( '%BLANK%', '%blank%' ), array( '__________', '__________' ), do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$decode_user_answer = sanitize_textarea_field( strval( stripslashes( htmlspecialchars_decode( $_POST[ 'question' . $id ], ENT_QUOTES ) ) ) );
		$mlw_user_answer    = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_answer ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $mlw_user_answer;
	foreach ( $answers as $answer ) {
		$decode_correct_text          = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		$return_array['correct_text'] = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_correct_text ) ) );
		if ( mb_strtoupper( $return_array['user_text'] ) == mb_strtoupper( $return_array['correct_text'] ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	return $return_array;
}


// Start polar question
add_action( 'plugins_loaded', 'qmn_question_type_polar' );

/**
 * This function registers the fill in the blank question type
 *
 * @return void
 * @since 6.4.1
 */
function qmn_question_type_polar() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_question_type( __( 'Polar', 'quiz-master-next' ), 'qmn_polar_display', true, 'qmn_polar_review', null, null, 13 );
}

/**
 * This function displays the fill in the blank question
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $question_display Returns the content of the question
 * @since 6.4.1
 */
function qmn_polar_display( $id, $question, $answers ) {
	$question_display = '';
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$autofill       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'autofill' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$autofill_att   = $autofill ? "autocomplete='off' " : '';
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	$input_text     = '';
	$first_point    = isset( $answers[0][1] ) ? $answers[0][1] : 0;
	$second_point   = isset( $answers[1][1] ) ? $answers[1][1] : 0;
	$is_reverse     = false;
	$check_point    = $second_point;
	$font_weight_lc = 'right-polar-title';
	$font_weight_rc = 'left-polar-title';
	if ( $first_point > $second_point ) {
		$is_reverse     = true;
		$check_point    = $first_point;
		$font_weight_lc = 'left-polar-title';
		$font_weight_rc = 'right-polar-title';
	}
	$total_answer = count( $answers );
	?>
		<script type="text/javascript">
			(function($) {
				$(document).ready(function() {                    
					$('#slider-' + '<?php echo $id; ?>').slider({
						 <?php if ( $total_answer == 2 && $is_reverse ) { ?>
								max: <?php echo $answers[0][1]; ?>,
								min: <?php echo $answers[1][1]; ?>,
								isRTL: true,
						<?php } else { ?>
								min: <?php echo $answers[0][1]; ?>,
								max: <?php echo $answers[1][1]; ?>,
						<?php } ?>
							step: 1,
							value: <?php echo $check_point / 2; ?>,
							change: function( event, ui ) {
								$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.qmn_polar').val(ui.value);
								if(ui.value == <?php echo $answers[0][1]; ?>){
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.left-polar-title').css('font-weight','900');
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.right-polar-title').css('font-weight','100');
								} else if(ui.value == <?php echo $answers[1][1]; ?>){
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.left-polar-title').css('font-weight','100');
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.right-polar-title').css('font-weight','900');
								} else if(ui.value == <?php echo $check_point / 2; ?>){
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.left-polar-title').css('font-weight','400');
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.right-polar-title').css('font-weight','400');
								} else if(ui.value > <?php echo $check_point / 2; ?>){
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.<?php echo $font_weight_rc; ?>').css('font-weight','400');
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.<?php echo $font_weight_lc; ?>').css('font-weight','600');
								} else if(ui.value < <?php echo $check_point / 2; ?>){
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.<?php echo $font_weight_rc; ?>').css('font-weight','600');
									$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.<?php echo $font_weight_lc; ?>').css('font-weight','400');
								}
							},
							create: function( event, ui ) {
								$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.left-polar-title').css('font-weight','400');
								$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.right-polar-title').css('font-weight','400');
								$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').find('.qmn_polar').val(<?php echo $check_point / 2; ?>);
							}    
					});
					var maxHeight = Math.max.apply(null, $(".question-section-id-<?php echo $id; ?> .question-type-polar-s > div").map(function (){
						return $(this).height();
					}).get());
					$('.question-section-id-<?php echo $id; ?> .question-type-polar-s').height(maxHeight);
				});
			})(jQuery);               
		</script>
	<?php
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredText';
	} else {
		$mlw_requireClass = '';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	$question_title     = qsm_question_title_func( $question, '', $new_question_title );
	// $question_title = "<div class='mlw_qmn_question polar-question-title'>". do_shortcode(htmlspecialchars_decode($question, ENT_QUOTES)) ."</div>";
	$input_text .= "<div class='left-polar-title'>" . $answers[0][0] . '</div>';
	$input_text .= "<div class='slider-main-wrapper'><input type='hidden' class='qmn_polar $mlw_requireClass' id='question" . $id . "' name='question" . $id . "' />";
	$input_text .= '<div id="slider-' . $id . '"></div></div>';
	$input_text .= "<div class='right-polar-title'>" . $answers[1][0] . '</div>';
	/*
	if (strpos($question, '%POLAR_SLIDER%') !== false) {
		$question = str_replace("%POLAR_SLIDER%", $input_text, do_shortcode(htmlspecialchars_decode($question, ENT_QUOTES)));
	}*/
	$question = $input_text;
	// $question_title = apply_filters('the_content', $question);
	$question_display .= $question_title . "<span class='mlw_qmn_question question-type-polar-s'>" . do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) . '</span>';

	return apply_filters( 'qmn_polar_display_front', $question_display, $id, $question, $answers );
}

/**
 * This function determines how the fill in the blank question is graded.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since 6.4.1
 */
function qmn_polar_review( $id, $question, $answers ) {
	$return_array = array(
		'points'       => 0,
		'correct'      => 'incorrect',
		'user_text'    => '',
		'correct_text' => '',
	);
	if ( strpos( $question, '%POLAR_SLIDER%' ) !== false || strpos( $question, '%polar_slider%' ) !== false ) {
		$return_array['question_text'] = str_replace( array( '%POLAR_SLIDER%', '%polar_slider%' ), array( '__________', '__________' ), do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$decode_user_answer = sanitize_textarea_field( $_POST[ 'question' . $id ] );
		$mlw_user_answer    = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_answer ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $mlw_user_answer;
	$return_array['points']    = $mlw_user_answer;
	foreach ( $answers as $answer ) {
		$decode_correct_text          = $answer[1];
		$return_array['correct_text'] = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_correct_text ) ) );
		if ( $return_array['user_text'] == trim( $decode_correct_text ) && isset( $answer[2] ) && $answer[2] == 1 ) {
			$return_array['correct'] = 'correct';
			break;
		}
	}
	return $return_array;
}

function qsm_question_title_func( $question, $question_type = '', $new_question_title = '' ) {
	// $question_title = apply_filters('the_content', $question);
	$question_title = $question;
	global $wp_embed,$mlwQuizMasterNext;
	$question_title    = $wp_embed->run_shortcode( $question_title );
	$question_title    = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $question_title );
	$polar_extra_class = '';
	$question_display  = '';
	if ( $question_type == 'polar' ) {
		$polar_extra_class = 'question-type-polar-s';
	}
	$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
	$deselect_answer  = '';
	if ( isset( $qmn_quiz_options->enable_deselect_option ) && $qmn_quiz_options->enable_deselect_option == 1 && ( $question_type == 'multiple_choice' || $question_type == 'horizontal_multiple_choice' ) ) {
		$deselect_answer = '<a href="#" class="qsm-deselect-answer">Deselect Answer</a>';
	}

	if ( $new_question_title != '' ) {
		$question_display  .= "<span class='mlw_qmn_new_question'>" . sanitize_text_field( htmlspecialchars_decode( $new_question_title, ENT_QUOTES ) ) . '</span>';
		$polar_extra_class .= ' qsm_remove_bold';
	}

	$question_display .= "<span class='mlw_qmn_question {$polar_extra_class}' >" . do_shortcode( htmlspecialchars_decode( $question_title, ENT_QUOTES )) . $deselect_answer . '</span>';
	return $question_display;
}
?>
