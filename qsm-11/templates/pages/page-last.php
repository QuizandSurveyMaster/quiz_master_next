<?php
/**
 * Template for quiz last page
 *
 * This template can be overridden by copying it to yourtheme/qsm/templates/frontend/page-last.php
 *
 * @package QSM
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $mlwQuizMasterNext;
$options = $renderer->get_quiz_properties('options');
$quiz_data = $renderer->get_quiz_properties('quiz_data');
$last_page_count = count($renderer->get_quiz_properties('pages'));

$last_page_class = 'qsm-page';
$last_page_class = 0 == $options->comment_section && "" !== $options->comment_section ? $last_page_class . ' qsm-quiz-comment-section slide' : $last_page_class;

// Hook before last page render
do_action( 'qsm_before_last_page', $quiz_id, $args );
?>

<section 
	class="<?php echo esc_attr( apply_filters( 'qsm_last_page_classes', $last_page_class, $quiz_id, $args ) ); ?>" 
	data-page="<?php echo ++$last_page_count; ?>" 
	data-page-type="last" 
	style="display: none;"
>
	<div class="quiz_section quiz_end empty_quiz_end">
	
	<?php do_action( 'qsm_before_last_page_content', $quiz_id, $args ); ?>
	
	<?php if ( '' != trim( $message_after ) ) : ?>
		<div class='qsm-after-message mlw_qmn_message_end'>
			<?php echo wp_kses_post( do_shortcode( trim( $message_after ) ) ); ?>
		</div>
	<?php endif; ?>
	
	<?php if ( $show_contact_fields && isset( $renderer ) ) : ?>
		<div class="qsm-contact-form-wrapper">
			<?php echo $renderer->render_contact_form(); ?>
		</div>
	<?php endif; ?>

	<?php echo apply_filters( 'qmn_before_comment_section', '', $options, $quiz_data ); ?>
	
	<?php if ( 0 == $options->comment_section && "" !== $options->comment_section ) : ?>
		<?php
		$message_comments = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->message_comment, ENT_QUOTES ), "quiz_message_comment-{$options->quiz_id}" );
		$message_comments = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_comments ), $quiz_data );
		?>
		<label for="mlwQuizComments" class="mlw_qmn_comment_section_text"><?php echo wp_kses_post( do_shortcode( $message_comments ) ); ?></label><br />
		<textarea cols="60" rows="10" id="mlwQuizComments" name="mlwQuizComments" class="qmn_comment_section"></textarea>
	<?php endif; ?>
	
	<?php echo apply_filters( 'qmn_after_comment_section', '', $options, $quiz_data ); ?>
	
	</div>
	
	<?php do_action( 'qsm_after_last_page_content', $quiz_id, $args ); ?>
	<?php do_action( 'mlw_qmn_end_quiz_section' ); ?>
</section>

<?php
// Hook after last page render
do_action( 'qsm_after_last_page', $quiz_id, $args );
?>
