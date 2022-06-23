<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
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
	$new_question_title = $mlwQuizMasterNext->pluginHelper->get_question_setting( $id, 'question_title' );
	qsm_question_title_func( $question, '', $new_question_title, $id );
	?> <div></div><input type="file" class="mlw_answer_file_upload <?php echo esc_attr( $mlw_require_class ); ?>"/>
		<div style="display: none;" class="loading-uploaded-file"><img alt="<?php echo esc_attr( $new_question_title ); ?>" src="<?php echo esc_url( get_site_url() . '/wp-includes/images/spinner-2x.gif' ); ?>"></div>
		<div style="display: none;" class="remove-uploaded-file"><span class="dashicons dashicons-trash"></span></div>
		<input class="mlw_file_upload_hidden_value" type="hidden" name="question<?php echo esc_attr( $id ); ?>" value="" />
		<span style="display: none;" class='mlw-file-upload-error-msg'></span>
		<input class="mlw_file_upload_hidden_path" type="hidden" value="" />
		<input class="mlw_file_upload_media_id" type="hidden" value="" />
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
	$current_question               = new QSM_Question_Review_File_Upload($id, $question, $answers);
	$user_text_array                = $current_question->get_user_answer();
	$correct_text_array             = $current_question->get_correct_answer();
	$return_array['user_text']      = ! empty( $user_text_array ) ? '<a target="_blank" href="' . $user_text_array['url'] . '">' . __( 'Click here to view', 'quiz-master-next' ) . '</a>' : '' ;
	$return_array['correct_text']   = ! empty( $correct_text_array ) ? implode( '.', $correct_text_array ) : '';
	$return_array['correct']        = $current_question->get_answer_status('url');
	$return_array['points']         = $current_question->get_points();
	$return_array['user_answer']    = $user_text_array;
	$return_array['correct_answer'] = $correct_text_array ;
	/** 
	 * Hook to filter answers array
	 */
	return apply_filters( 'qmn_file_upload_review', $return_array, $answers );
}