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
 *
 * @since 6.3.7
 */
function qmn_file_upload_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredFileUpload';
	} else {
		$mlw_requireClass = '';}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?> <div></div><input type="file" class="mlw_answer_file_upload <?php echo esc_attr( $mlw_requireClass ); ?>"/>
		<div style="display: none;" class="loading-uploaded-file"><img alt="<?php echo esc_attr( $new_question_title ); ?>" src=" <?php esc_url( get_site_url() . '/wp-includes/images/spinner-2x.gif'); ?>"></div>
		<div style="display: none;" class="remove-uploaded-file"><span class="dashicons dashicons-trash"></span></div>
		<input class="mlw_file_upload_hidden_value" type="hidden" name="question<?php echo esc_attr( $id ); ?>" value="" />
		<span style="display: none;" class='mlw-file-upload-error-msg'></span>
		<input class="mlw_file_upload_hidden_path" type="hidden" value="" /> <?php
	echo apply_filters( 'qmn_file_upload_display_front', '', $id, $question, $answers );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_file_upload_review', $return_array, $answers );
}

/**
 * This function shows the content of the multiple choice question.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 *
 * @since 4.4.0
 */
function qmn_multiple_choice_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	$required           = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredRadio';
	} else {
		$mlw_requireClass = '';
	}
	// $question_title = apply_filters('the_content', $question);
	qsm_question_title_func( $question, 'multiple_choice', $new_question_title, $id );
	?> <div class='qmn_radio_answers <?php echo esc_attr( $mlw_requireClass ); ?>'> <?php
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer_index => $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				$answer_class = apply_filters('qsm_answer_wrapper_class', '', $answer, $id);
				if ( $answerEditor === 'rich' ) {
					?> <div class='qmn_mc_answer_wrap <?php echo esc_attr( $answer_class ); ?>' id='question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?>'> <?php
				} elseif ( $answerEditor === 'image' ) {
					?> <div class='qmn_mc_answer_wrap qmn_image_option <?php echo esc_attr( $answer_class ); ?>' id='question<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $mlw_answer_total ); ?>'> <?php
				} else {
					?> <div class="qmn_mc_answer_wrap <?php echo esc_attr( $answer_class ); ?>" id="<?php echo esc_attr( 'question' . $id . '-' . str_replace( ' ', '-', $answer[0] ) ); ?> "> <?php
				}
				?> <input type='radio' class='qmn_quiz_radio' name="<?php echo esc_attr('question' . $id  ); ?>" id="<?php echo esc_attr('question'. $id . '_' . $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer[0] ); ?>" /> <?php
				?> <label for="<?php echo esc_attr( 'question'. $id . '_' . $mlw_answer_total ); ?>"> <?php
				if ( $answerEditor === 'image' ) {
					?> 	<img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>" />
						<span class="qsm_image_caption"><?php esc_html_e( trim( htmlspecialchars_decode( $answer[3], ENT_QUOTES ) ) ); ?></span><?php
				} else {
					echo wp_kses( trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ),  wp_kses_allowed_html( 'post' ) );
				}
				?> </label> <?php
				echo apply_filters( 'qsm_multiple_choice_display_loop', ' ', $id, $question, $answers );
				?> </div> <?php
			}
		}
		?> <input type="radio" style="display: none;" name="<?php echo esc_attr( 'question' . $id ) ; ?>" id="<?php echo esc_attr( 'question' . $id . '_none' ); ?>" checked="checked" value="" /> <?php
	}
	?> </div> <?php
	echo apply_filters( 'qmn_multiple_choice_display_front', '', $id, $question, $answers );
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
		$mlw_user_answer = sanitize_text_field( $_POST[ 'question' . $id ] );
		$mlw_user_answer = trim( stripslashes( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = stripslashes( htmlspecialchars_decode( $mlw_user_answer, ENT_QUOTES ) );
	$rich_text_comapre         = preg_replace( "/\s+|\n+|\r/", ' ', htmlentities( $mlw_user_answer ) );
	$correct_text              = array();
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
				$mlw_user_answer = sanitize_text_field( $_POST[ 'question' . $id ] );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_multiple_choice_review', $return_array, $answers );
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
 *
 * @since 6.3.7
 */
function qmn_date_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredDate';
	} else {
		$mlw_requireClass = '';}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?> <input type="date" class="mlw_answer_date <?php echo esc_attr( $mlw_requireClass ); ?>" name="question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ); ?>" value="" /> <?php
	echo apply_filters( 'qmn_date_display_front', '', $id, $question, $answers );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_date_review', $return_array, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_horizontal_multiple_choice_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredRadio';
	} else {
		$mlw_requireClass = '';
	}
	$answerEditor = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, 'horizontal_multiple_choice', $new_question_title, $id );
	?> <div class="qmn_radio_answers qmn_radio_horizontal_answers <?php echo esc_attr( $mlw_requireClass ); ?>"> <?php
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer_index => $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				$answer_class = apply_filters('qsm_answer_wrapper_class', '', $answer, $id);
				?> <span class="mlw_horizontal_choice <?php echo esc_attr( $answer_class ); ?>"><input type="radio" class="qmn_quiz_radio" name="question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ). '_' .esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer[0] ); ?>" /><label for="question<?php echo esc_attr( $id ). '_' .esc_attr( $mlw_answer_total ); ?>"><?php
				if ( $answerEditor === 'image' ) {
					?> <img alt="<?php echo esc_attr( $new_question_title ); ?>" src=" <?php esc_url_e( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>" /> <?php
				} else {
					echo wp_kses( trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ), wp_kses_allowed_html( 'post' ) );
				}
				?> </label> <?php
				echo apply_filters( 'qsm_multiple_choice_horizontal_display_loop', '', $id, $question, $answers );
				?> </span> <?php
			}
		}
		echo apply_filters( 'qmn_horizontal_multiple_choice_question_display', '', $id, $question, $answers );
		?> <input type="radio" style="display: none;" name="question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ); ?>_none" checked="checked" value="" /> <?php
	}
	?> </div> <?php

	echo apply_filters( 'qmn_horizontal_multiple_choice_display_front', '', $id, $question, $answers );
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
		$mlw_user_answer = sanitize_text_field( $_POST[ 'question' . $id ] );
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
				$mlw_user_answer = sanitize_text_field( $_POST[ 'question' . $id ] );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_horizontal_multiple_choice_review', $return_array, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_drop_down_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$require_class = 'qsmRequiredSelect';
	} else {
		$require_class = '';
	}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?><select class="qsm_select <?php echo esc_attr( $require_class );?>" name="question<?php echo esc_attr( $id ); ?>"><?php
	?><option value=""><?php echo esc_html__( 'Please select your answer', 'quiz-master-next' );?></option><?php
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				?><option value="<?php echo esc_attr( $answer[0] ); ?>"><?php  esc_html_e( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ); ?></option><?php
			}
		}
	}
	?> </select> <?php
	echo apply_filters( 'qmn_drop_down_display_front', '', $id, $question, $answers );
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
		$mlw_user_answer = sanitize_text_field( $_POST[ 'question' . $id ] );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_drop_down_review', $return_array, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_small_open_display( $id, $question, $answers ) {
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
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?><input <?php echo esc_attr( $autofill_att . $limit_text_att ); ?> type="text" class="mlw_answer_open_text <?php echo esc_attr( $mlw_requireClass ); ?>" name="question<?php echo esc_attr( $id ); ?>" /><?php
	echo apply_filters( 'qmn_small_open_display_front', '', $id, $question, $answers );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_small_open_review', $return_array, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_multiple_response_display( $id, $question, $answers ) {
	$limit_mr_text = '';
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
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?><div class="qmn_check_answers <?php echo esc_attr( $mlw_requireClass ); ?>"><?php
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				?><div class="qsm_check_answer">
					<input type="hidden" name="question<?php echo esc_attr( $id ); ?>" value="This value does not matter" />
					<input type="checkbox" <?php echo esc_attr( $limit_mr_text ); ?> name="question<?php echo esc_attr( $id ). '_' . esc_attr( $mlw_answer_total ); ?>" id="question<?php echo esc_attr( $id ). '_' . esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer[0] ); ?> " /> <label for="question<?php echo esc_attr( $id ). '_' . esc_attr( $mlw_answer_total ); ?>"><?php
				if ( $answerEditor === 'image' ) {
					?><img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php esc_url_e( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>" /><?php
				} else {
					echo wp_kses( trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ), wp_kses_allowed_html( 'post' ) );
				}
				?></label>
				</div><?php
			}
		}
	}
	?></div><?php
	echo apply_filters( 'qmn_multiple_response_display_front', '', $id, $question, $answers );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_multiple_response_review', $return_array, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_large_open_display( $id, $question, $answers ) {
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
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?><textarea class="mlw_answer_open_text <?php echo esc_attr( $mlw_requireClass ); ?>" <?php echo esc_attr( $limit_text_att ); ?> cols="70" rows="5" name="question<?php echo esc_attr( $id ); ?>" /></textarea><?php
	echo apply_filters( 'qmn_large_open_display_front', '', $id, $question, $answers );
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
		$mlw_user_answer    = trim( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $decode_user_answer ) ) );
	} else {
		$mlw_user_answer = ' ';
	}
	$return_array['user_text'] = $decode_user_answer;
	foreach ( $answers as $answer ) {
		$return_array['correct_text'] = $decode_correct_text = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
		$decode_correct_text          = trim( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $decode_correct_text ) ) );
		if ( mb_strtoupper( $mlw_user_answer ) == mb_strtoupper( $decode_correct_text ) ) {
			$return_array['correct'] = 'correct';
			$return_array['points']  = $answer[1];
			break;
		}
	}
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_large_open_review', $return_array, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_text_block_display( $id, $question, $answers ) {
	echo wp_kses ( do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ), wp_kses_allowed_html( 'post' ) );
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
 *
 * @since 4.4.0
 */
function qmn_number_display( $id, $question, $answers ) {
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
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?><input type="number" <?php echo esc_attr( $limit_text_att ); ?> class="mlw_answer_number <?php echo esc_attr( $mlw_requireClass ); ?>" name="question<?php echo esc_attr( $id ); ?>" /><?php
	echo apply_filters( 'qmn_number_display_front', '', $id, $question, $answers );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_number_review', $return_array, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_accept_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredAccept';
	} else {
		$mlw_requireClass = '';}
	?><div class="qmn_accept_answers">
		<input type="checkbox" id="mlwAcceptance" class="<?php echo esc_attr( $mlw_requireClass ); ?>" />
		<label for="mlwAcceptance"><span class="qmn_accept_text"><?php echo wp_kses( do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ), wp_kses_allowed_html( 'post' ) ); ?></span></label>
	</div><?php
	echo apply_filters( 'qmn_accept_display_front', '', $id, $question, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_captcha_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredCaptcha';
	} else {
		$mlw_requireClass = '';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	?>
	<span class="mlw_qmn_question">
		<?php qsm_question_title_func( $question, '', $new_question_title, $id ); ?>
	</span>
	<div class="mlw_captchaWrap">
		<canvas alt="" id="mlw_captcha" class="mlw_captcha" width="100" height="50"></canvas>
	</div>
	<input type="text" class="mlw_answer_open_text <?php echo esc_attr( $mlw_requireClass ); ?>" id="mlw_captcha_text" name="mlw_user_captcha"/>
	<input type="hidden" name="mlw_code_captcha" id="mlw_code_captcha" value="none" /><?php
	echo apply_filters( 'qmn_captcha_display_front', '', $id, $question, $answers );
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
 *
 * @since 4.4.0
 */
function qmn_horizontal_multiple_response_display( $id, $question, $answers ) {
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
	$answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'answerEditor' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?><div class="qmn_check_answers qmn_multiple_horizontal_check <?php echo esc_attr( $mlw_requireClass ); ?>"><?php
	if ( is_array( $answers ) ) {
		$mlw_answer_total = 0;
		foreach ( $answers as $answer ) {
			$mlw_answer_total++;
			if ( $answer[0] != '' ) {
				?><input type="hidden" name="question<?php echo esc_attr( $id ); ?> " value="This value does not matter" />
				<span class="mlw_horizontal_multiple"><input type="checkbox" <?php echo esc_attr( $limit_mr_text ); ?> name="question<?php echo esc_attr( $id ). '_' . esc_attr( $mlw_answer_total ); ?>" id="question<?php echo esc_attr( $id ). '_' . esc_attr( $mlw_answer_total ); ?>" value="<?php echo esc_attr( $answer[0] ); ?>" /> <label for="question<?php echo esc_attr( $id ). '_' . esc_attr( $mlw_answer_total ); ?>"><?php
				if ( $answerEditor === 'image' ) {
					?><img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php esc_url_e( trim( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ); ?>" /><?php
				} else {
					echo wp_kses( trim( do_shortcode( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) ) ), wp_kses_allowed_html( 'post' ) );
				}
				?></label></span><?php
			}
		}
	}
	?></div><?php
	echo apply_filters( 'qmn_horizontal_multiple_response_display_front', '', $id, $question, $answers );
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_horizontal_multiple_response_review', $return_array, $answers );
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
 * @since 4.4.0
 */
function qmn_fill_blank_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$autofill       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'autofill' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$autofill_att   = $autofill ? "autocomplete='off' " : '';
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredText';
	} else {
		$mlw_requireClass = '';
	}
	$input_text = '<input ' . $autofill_att . $limit_text_att . " type='text' class='qmn_fill_blank $mlw_requireClass' name='question" . $id . "[]' />";
	if ( strpos( $question, '%BLANK%' ) !== false ) {
		$question = str_replace( '%BLANK%', $input_text, do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	echo apply_filters( 'qmn_fill_blank_display_front', '', $id, $question, $answers );
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
	global $mlwQuizMasterNext;
	$match_answer = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'matchAnswer' );
	$return_array = array(
		'points'            => 0,
		'correct'           => 'incorrect',
		'user_text'         => '',
		'correct_text'      => '',
		'user_compare_text' => '',
	);
	if ( strpos( $question, '%BLANK%' ) !== false || strpos( $question, '%blank%' ) !== false ) {
		$return_array['question_text'] = str_replace( array( '%BLANK%', '%blank%' ), array( '__________', '__________' ), do_shortcode( htmlspecialchars_decode( $question, ENT_QUOTES ) ) );
	}
	$correct_text=$user_input = $user_text = array();
	if ( isset( $_POST[ 'question' . $id ] ) && ! empty( $_POST[ 'question' . $id ] ) ) {
		foreach ( $_POST[ 'question' . $id ] as $input ) {
			$decode_user_answer = sanitize_textarea_field( strval( stripslashes( htmlspecialchars_decode( $input, ENT_QUOTES ) ) ) );
			$mlw_user_answer    = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_answer ) ) );
				$user_input[] = mb_strtoupper( $mlw_user_answer );
				$user_text[]  = $mlw_user_answer;
		}
	}

	$total_correct = $user_correct = 0;
	if ( $match_answer == 'sequence' ) {
		foreach ( $answers as $key => $answer ) {
			$decode_user_text = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$decode_user_text = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_text ) ) );
			if ( mb_strtoupper( $decode_user_text ) == $user_input[ $key ] ) {
				$return_array['points'] += $answer[1];
				$user_correct += 1;
			}
			$total_correct++;
			$correct_text[]=$answers[$key][0];
		}
		$return_array['correct_text'] = implode( '.', $correct_text );
		$return_array['user_text'] = implode( '.', $user_text );
		$return_array['user_compare_text'] = implode( '=====', $user_text );
		if($total_correct == $user_correct){
			$return_array['correct']   = 'correct';
		}
	} else {
		$answers_array = array();
		$correct = true;
		foreach($answers as $answer){
			$decode_user_text = strval( htmlspecialchars_decode( $answer[0], ENT_QUOTES ) );
			$decode_user_text = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', $decode_user_text ) ) );
			$answers_array[] = mb_strtoupper( $decode_user_text );
		}
		$total_user_input = sizeof($user_input);
		$total_option = sizeof($answers);
		if($total_user_input < $total_option){
			foreach($user_input as $k => $input){
				$key = array_search( $input, $answers_array );
				if($key !== false){
					$return_array['points'] += $answers[$key][1];
				} else {
					$correct = false;
				}
				$correct_text[]=$answers[$key][0];
			}
		} else {
			foreach($answers_array as $k => $answer){
				$key = array_search( $answer, $user_input );
				if($key !== false){
					$return_array['points'] += $answers[$k][1];
				} else {
					$correct = false;
				}
				$correct_text[]=$answers[$k][0];
			}
		}
		if($correct){
			$return_array['correct']   = 'correct';
		}
		$return_array['user_text'] = implode( '.', $user_text );
		$return_array['correct_text'] = implode( '.', $correct_text );
		$return_array['user_compare_text'] = implode( '=====', $user_text );
	}

	/**
	 * Hook to filter answers array
	 */

	return apply_filters( 'qmn_fill_blank_review', $return_array, $answers );
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
 * @since 6.4.1
 */
function qmn_polar_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	$autofill       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'autofill' );
	$limit_text     = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'limit_text' );
	$autofill_att   = $autofill ? "autocomplete='off' " : '';
	$limit_text_att = $limit_text ? "maxlength='" . $limit_text . "' " : '';
	$input_text     = '';
	$first_point    = isset( $answers[0][1] ) ? intval( $answers[0][1] ) : 0;
	$second_point   = isset( $answers[1][1] ) ? intval( $answers[1][1] ) : 0;
	$is_reverse     = false;
	$check_point    = $second_point;
	if ( $first_point > $second_point ) {
		$is_reverse     = true;
		$check_point    = $first_point;
	}
	$total_answer = count( $answers );
	$id = esc_attr( intval( $id ) );
	$answar1 = $first_point;
	$answar2 = $second_point;
	$slider_data_atts ='';
	$slider_data_atts.=' data-answer1='.$answar1.' ';
	$slider_data_atts.=' data-answer2='.$answar2.' ';
	$slider_data_atts.=' data-is_reverse='.intval($is_reverse).' ';
	$slider_data_atts.=' data-is_required='.$required .' ';
	if ( $required == 0 ) {
		$mlw_requireClass = 'mlwRequiredText';
	} else {
		$mlw_requireClass = '';
	}
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );

	?>
	<span class="mlw_qmn_question question-type-polar-s">
		<div class='left-polar-title'> <?php echo esc_html($answers[0][0]); ?> </div>
		<div class='slider-main-wrapper'>
			<input type='hidden' class='qmn_polar <?php echo esc_attr($mlw_requireClass);?>' id='question<?php echo esc_attr($id);?>' name='question<?php echo esc_attr($id);?>' value=''/>
			<div id="slider-<?php echo esc_attr($id);?>" <?php echo esc_attr($slider_data_atts);?> ></div>
		</div>
		<div class='right-polar-title'><?php echo esc_html($answers[1][0]); ?></div>
	</span>
	<?php
	
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
	/**
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_polar_review', $return_array, $answers );
}

function qsm_question_title_func( $question, $question_type = '', $new_question_title = '', $question_id = 0 ) {
	// $question_title = apply_filters('the_content', $question);
	$question_title = $question;
	global $wp_embed, $mlwQuizMasterNext;
	$question_title    = $wp_embed->run_shortcode( $question_title );
	$question_title    = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $question_title );
	$polar_extra_class = '';
	if ( $question_type == 'polar' ) {
		$polar_extra_class = 'question-type-polar-s';
	}
	$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
	$deselect_answer  = '';
	if ( isset( $qmn_quiz_options->enable_deselect_option ) && $qmn_quiz_options->enable_deselect_option == 1 && ( $question_type == 'multiple_choice' || $question_type == 'horizontal_multiple_choice' ) ) {
		$deselect_answer = '<a href="#" class="qsm-deselect-answer">Deselect Answer</a>';
	}

	if ( $question_id ) {
		$featureImageID = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'featureImageID' );
		if ( $featureImageID ) {
			?> <div class="qsm-featured-image"><?php echo wp_get_attachment_image( $featureImageID, apply_filters( 'qsm_filter_feature_image_size', 'full', $question_id ) ) ; ?></div><?php
		}
	}
	if ( $new_question_title != '' ) {
		?><div class='mlw_qmn_new_question'><?php esc_html_e( sanitize_text_field( htmlspecialchars_decode( $new_question_title, ENT_QUOTES ) ) );?> </div><?php
		$polar_extra_class .= ' qsm_remove_bold';
	}

	?> <div class='mlw_qmn_question <?php echo esc_attr( $polar_extra_class ); ?>' ><?php echo do_shortcode( htmlspecialchars_decode( $question_title, ENT_QUOTES ) ) . esc_attr(  $deselect_answer ) ;?></div><?php
}
?>