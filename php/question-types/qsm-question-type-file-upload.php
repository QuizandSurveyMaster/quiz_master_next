<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* 
*
* 
*/

/**
 * This function shows the content of the file upload
 *
 * @param $id       The ID of the multiple choice question
 * @param $question The question that is being edited.
 * @param @answers The array that contains the answers to the question.
 *
 * @since 6.3.7
 */
function qmn_file_upload_display( $id, $question, $answers ) {
	global $mlwQuizMasterNext;
	$required = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'required' );
	if ( 0 == $required ) {
		$mlw_require_class = 'mlwRequiredFileUpload';
	} else {
		$mlw_require_class = '';
	}
	// $question_title = apply_filters('the_content', $question);
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?> <div></div><input type="file" class="mlw_answer_file_upload <?php echo esc_attr( $mlw_require_class ); ?>"/>
		<div style="display: none;" class="loading-uploaded-file"><img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( get_site_url() . '/wp-includes/images/spinner-2x.gif' ); ?>"></div>
		<div style="display: none;" class="remove-uploaded-file"><span class="dashicons dashicons-trash"></span></div>
		<input class="mlw_file_upload_hidden_value" type="hidden" name="question<?php echo esc_attr( $id ); ?>" value="" />
		<span style="display: none;" class='mlw-file-upload-error-msg'></span>
		<input class="mlw_file_upload_hidden_path" type="hidden" value="" />
		<?php
		echo apply_filters( 'qmn_file_upload_display_front', '', $id, $question, $answers );
}

/**
 * This function determines how the file upload will work.
 *
 * @params $id The ID of the multiple choice question
 * @params $question The question that is being edited.
 * @params @answers The array that contains the answers to the question.
 * @return $return_array Returns the graded question to the results page
 * @since  5.3.7
 */
function qmn_file_upload_review( $id, $question, $answers ) {
	$return_array = array(
		'points'        => 0,
		'correct'       => 'incorrect',
		'user_text'     => '',
		'correct_text'  => '',
		'question_type' => 'file_upload',
	);
	//
	if ( isset( $_POST[ 'question' . $id ] ) ) {
		$decode_user_answer = sanitize_text_field( wp_unslash( $_POST[ 'question' . $id ] ) );
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