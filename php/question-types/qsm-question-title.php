<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function qsm_question_title_func( $question, $question_type = '', $new_question_title = '', $question_id = 0 ) {
	$question_title = $question;
	global $wp_embed, $mlwQuizMasterNext;
	$question_title    = $wp_embed->run_shortcode( $question_title );
	$question_title    = preg_replace( '/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $question_title );
	$title_extra_classes = '';
	if ( 'polar' === $question_type ) {
		$title_extra_classes .= ' question-type-polar-s';
	}
	if ( 'fill_in_blank' === $question_type ) {
		$title_extra_classes .= ' qsm-align-fill-in-blanks';
	}
	$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
	$deselect_answer  = '';
	if ( isset( $qmn_quiz_options->enable_deselect_option ) && 1 == $qmn_quiz_options->enable_deselect_option && ( 'multiple_choice' === $question_type || 'horizontal_multiple_choice' === $question_type ) ) {
		$default_texts = QMNPluginHelper::get_default_texts();
		$deselect_answer_text = ! empty( $qmn_quiz_options->deselect_answer_text ) ? $qmn_quiz_options->deselect_answer_text : $default_texts['deselect_answer_text'];
		$deselect_answer = '<a href="javascript:void(0)" class="qsm-deselect-answer">'. $mlwQuizMasterNext->pluginHelper->qsm_language_support( $deselect_answer_text, "deselect_answer_text-{$qmn_quiz_options->quiz_id}" ) .'</a>';
	}
	do_action('qsm_question_title_function_before',$question, $question_type, $new_question_title, $question_id );
	if ( '' !== $new_question_title ) {
		$new_question_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $new_question_title, ENT_QUOTES ), "Question-{$question_id}", "QSM Questions");
		$new_question_title = apply_filters( 'qsm_question_title_before', $new_question_title, $question_type, $question_id );
		if ( in_array( intval( get_question_type( $question_id ) ), [ 12, 7, 3, 5 ], true ) ) {
		?>
		<div class='mlw_qmn_new_question'><label class="qsm-question-title-label" for="question<?php echo esc_attr( $question_id ); ?>"><?php echo esc_html( $new_question_title ); ?> </label></div>
		<?php
		} else {
		?>
		<div class='mlw_qmn_new_question'><?php echo esc_html( $new_question_title ); ?> </div>
		<?php
		}
		$title_extra_classes .= ' qsm_remove_bold';
	}
	if ( $question_id ) {
		$featureImageID = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'featureImageID' );
		if ( $featureImageID ) {
			?>
			<div class="qsm-featured-image"><?php echo wp_get_attachment_image( $featureImageID, apply_filters( 'qsm_filter_feature_image_size', 'full', $question_id ) ); ?></div>
			<?php
		}
	}
	if ( ! empty( $question_title ) && 'fill_in_blank' !== $question_type ) {
		$question_title = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( html_entity_decode( $question_title, ENT_HTML5 ), ENT_QUOTES ), "question-description-{$question_id}", "QSM Questions" );
	}
	?>
	<div class='mlw_qmn_question <?php echo esc_attr( $title_extra_classes ); ?>' >
	<?php do_action('qsm_before_question_title',$question, $question_type, $new_question_title, $question_id );
		$allow_html = wp_kses_allowed_html('post');
		$allow_html['input']['autocomplete'] = 1;
		$allow_html['input']['name'] = 1;
		$allow_html['input']['class'] = 1;
		$allow_html['input']['id'] = 1;
		$allow_html['input']['maxlength'] = 1;
		$allow_html = apply_filters( 'qsm_allow_html_question_title_after', $allow_html, $question_id );
		$pattern = '/<code>(.*?)<\/code>/s';
		$question_description = preg_replace_callback($pattern, function ( $matches ) {
			return preg_replace([ '/<(?!(\/?code|br)[ >])/', '/>(?!(\/?code|br)[ \/>])/' ], [ '&lt;', '&gt;' ], $matches[0]);
		}, $question_title);
		$question_description = str_replace([ 'code&gt;', 'br /&gt;' ],[ 'code/>', 'br />' ], $question_description );
		$question_description = apply_filters( 'qsm_question_description_before', $question_description, $question_type, $question_id );
	?>
	<p><?php echo do_shortcode( wp_kses( $question_description . $deselect_answer, $allow_html ) ); ?></p>
	</div>
	<?php
	do_action('qsm_question_title_func_after',$question, $question_type, $new_question_title, $question_id );
}

function get_question_type( $question_id = 0 ) {
	global $wpdb;
	$question_type_new = $wpdb->get_var( $wpdb->prepare( "SELECT `question_type_new` FROM `{$wpdb->prefix}mlw_questions` WHERE `question_id`=%d", $question_id ) );
	if ( empty( $question_type_new ) ) {
		return false;
	}
	return $question_type_new;
}