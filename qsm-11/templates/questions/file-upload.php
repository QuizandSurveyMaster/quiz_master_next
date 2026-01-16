<?php
/**
 * Template for file upload type question
 *
 * This template can be overridden by copying it to yourpath/file-upload.php
 *
 * @package QSM
 * @version 11.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extract variables passed to template
 *
 * @param array $args Template arguments
 */
extract( $args );

// Ensure question_settings is an array
if ( ! is_array( $question_settings ) ) {
    $question_settings = array();
}

global $mlwQuizMasterNext;
$required       = isset( $question_settings['required'] ) ? $question_settings['required'] : '';
$theme_id       = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme( $quiz_id );
$active_themes  = $mlwQuizMasterNext->theme_settings->get_active_themes();

$is_theme_active = array_filter($active_themes, function( $subarray ) use ( $theme_id ) {
    return $subarray['id'] == $theme_id;
});

$hide = $is_theme_active ? true : false;

$mlw_require_class = $required == 0 ? 'mlwRequiredFileUpload' : '';

$new_question_title = isset( $question_settings['question_title'] ) ? $question_settings['question_title'] : '';
qsm_question_title_func( $question['question_name'], '', $new_question_title, $id );
?> 
<div></div>
<label style="display: none;" for="question<?php echo esc_attr( $id ); ?>"><?php echo esc_attr( "Choose File" ); ?></label>
<input style="display: none;" type="file" name="qsm_file_question<?php echo esc_attr( $id ); ?>" id="question<?php echo esc_attr( $id ); ?>" class="mlw_answer_file_upload <?php echo esc_attr( $mlw_require_class ); ?>"/>
<?php if ( ! $hide ) : ?>
    <div class="qsm-file-upload-container">
        <span class="dashicons dashicons-cloud-upload qsm-file-upload-logo"></span>
        <div class="qsm-file-upload-message">
            <?php esc_html_e( 'Drag and Drop File Here or ', 'quiz-master-next' ); ?>
            <a class="qsm-file-upload-link" href="#">
                <?php esc_html_e( ' Browse', 'quiz-master-next' ); ?>
            </a>
        </div>
        <div class="qsm-file-upload-name"></div>
        <span title="<?php esc_html_e( 'Remove', 'quiz-master-next' ); ?>" style="display: none;"  class="dashicons dashicons-no-alt remove-uploaded-file"></span>
    </div>
<?php endif; ?>
<div class="qsm-file-upload-status"></div>
<span style="display: none;" class='mlw-file-upload-error-msg'></span>
<?php
echo apply_filters( 'qmn_file_upload_display_front', '', $id, $question, $answers );
