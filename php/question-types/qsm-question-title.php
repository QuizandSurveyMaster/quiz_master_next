<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function qsm_question_title_func( $question, $question_type = '', $new_question_title = '', $question_id = 0 ) {
	$question_title = $question;
	global $wp_embed, $mlwQuizMasterNext;
	$question_title    = $wp_embed->run_shortcode( $question_title );
	$question_title    = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $question_title );
	$polar_extra_class = '';
	if ( 'polar' === $question_type ) {
		$polar_extra_class = 'question-type-polar-s';
	}
	$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
	$deselect_answer  = '';
	if ( isset( $qmn_quiz_options->enable_deselect_option ) && 1 == $qmn_quiz_options->enable_deselect_option && ( 'multiple_choice' === $question_type || 'horizontal_multiple_choice' === $question_type ) ) {
		$deselect_answer = '<a href="javascript:void(0)" class="qsm-deselect-answer">'.__( 'Deselect Answer', 'quiz-master-next' ).'</a>';
	}

	if ( $question_id ) {
		$featureImageID = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'featureImageID' );
		if ( $featureImageID ) {
			?>
			<div class="qsm-featured-image"><?php echo wp_get_attachment_image( $featureImageID, apply_filters( 'qsm_filter_feature_image_size', 'full', $question_id ) ); ?></div>
			<?php
		}
	}
	if ( '' !== $new_question_title ) {
		$new_question_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $new_question_title, ENT_QUOTES ), "Question-{$question_id}", "QSM Questions");
		?>
		<div class='mlw_qmn_new_question'><?php echo esc_html( $new_question_title ); ?> </div>
		<?php
		$polar_extra_class .= ' qsm_remove_bold';
	}
	if ( ! empty( $question_title ) ) {
		$question_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $question_title, ENT_QUOTES ), "question-description-{$question_id}", "QSM Questions" );
	}
	?>
	<div class='mlw_qmn_question <?php echo esc_attr( $polar_extra_class ); ?>' >
	<?php do_action('qsm_before_question_title',$question, $question_type, $new_question_title, $question_id ); ?>
	<p><?php echo do_shortcode( wp_kses_post( $question_title . $deselect_answer ) ); ?></p>
	</div>
	<?php
}