<?php
/**
 * Handles the functions/views for the "Style" tab when editing a quiz or survey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Style tab to the Quiz Settings page.
 *
 * @return void
 * @since 6.0.2
 */
function qsm_settings_style_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Style', 'quiz-master-next' ), 'qsm_options_styling_tab_content' );
}
add_action( 'plugins_loaded', 'qsm_settings_style_tab', 5 );

/**
 * Adds the Style tab content to the tab.
 *
 * @return void
 * @since 6.0.2
 */
function qsm_options_styling_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;

	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );

	$quiz_id = intval( $_GET['quiz_id'] );
	if ( isset( $_POST['qsm_style_tab_nonce'] ) && wp_verify_nonce( $_POST['qsm_style_tab_nonce'], 'qsm_style_tab_nonce_action' ) && isset( $_POST['save_style_options'] ) && 'confirmation' == $_POST['save_style_options'] ) {

		$style_quiz_id = intval( $_POST['style_quiz_id'] );
		$quiz_theme = sanitize_text_field( $_POST['save_quiz_theme'] );
		$quiz_style = sanitize_textarea_field( htmlspecialchars( stripslashes( $_POST['quiz_css'] ), ENT_QUOTES ) );

		// Saves the new css.
		$results = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mlw_quizzes SET quiz_stye=%s, theme_selected=%s, last_activity=%s WHERE quiz_id=%d", $quiz_style, $quiz_theme, date( 'Y-m-d H:i:s' ), $style_quiz_id ) );
		if ( false !== $results ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The style has been saved successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Styles Have Been Saved For Quiz Number $style_quiz_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Error occured when trying to save the styles. Please try again.', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error saving styles', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}

	if ( isset( $_GET['quiz_id'] ) ) {
		$mlw_quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
	}
	$registered_templates = $mlwQuizMasterNext->pluginHelper->get_quiz_templates();
	?>
	<script>
		function mlw_qmn_theme(theme)
		{
			document.getElementById('save_quiz_theme').value = theme;
			jQuery("div.mlw_qmn_themeBlockActive").toggleClass("mlw_qmn_themeBlockActive");
			jQuery("#mlw_qmn_theme_block_"+theme).toggleClass("mlw_qmn_themeBlockActive");

		}
	</script>
	<form action='' method='post' name='quiz_style_form'>
		<input type='hidden' name='save_style_options' value='confirmation' />
		<input type='hidden' name='style_quiz_id' value='<?php echo esc_attr( $quiz_id ); ?>' />
		<input type='hidden' name='save_quiz_theme' id='save_quiz_theme' value='<?php echo esc_attr( $mlw_quiz_options->theme_selected ); ?>' />
                <h3 style="display: none;"><?php _e( 'Quiz Styles', 'quiz-master-next' ); ?></h3>
		<p><?php _e( 'Choose your style:', 'quiz-master-next' ); ?></p>
		<style>
			div.mlw_qmn_themeBlockActive {
				background-color: yellow;
			}
		</style>
		<div class="qsm-styles">
			<?php
			foreach ( $registered_templates as $slug => $template ) {
				?>
				<div onclick="mlw_qmn_theme('<?php echo $slug; ?>');" id="mlw_qmn_theme_block_<?php echo $slug; ?>" class="qsm-info-widget <?php if ($mlw_quiz_options->theme_selected == $slug) {echo 'mlw_qmn_themeBlockActive';} ?>"><?php echo $template["name"]; ?></div>
				<?php
			}
			?>
			<div onclick="mlw_qmn_theme('default');" id="mlw_qmn_theme_block_default" class="qsm-info-widget <?php if ($mlw_quiz_options->theme_selected == 'default') {echo 'mlw_qmn_themeBlockActive';} ?>"><?php _e('Custom', 'quiz-master-next'); ?></div>
			<script>
				mlw_qmn_theme('<?php echo $mlw_quiz_options->theme_selected; ?>');
			</script>
		</div>
		<button id="save_styles_button" class="button-primary"><?php _e('Save Quiz Style', 'quiz-master-next'); ?></button>
		<hr />
		<h3><?php _e('Custom Style CSS', 'quiz-master-next'); ?></h3>
		<p><?php _e('For help and guidance along with a list of different classes used in this plugin, please visit the following link:', 'quiz-master-next'); ?>
		<a target="_blank" href="https://quizandsurveymaster.com/docs/advanced-topics/editing-design-styles-css/">CSS in QSM</a></p>
		<table class="form-table">
			<tr>
				<td><textarea style="width: 100%; height: 700px;" id="quiz_css" name="quiz_css"><?php echo $mlw_quiz_options->quiz_stye; ?></textarea></td>
			</tr>
		</table>
                <?php wp_nonce_field( 'qsm_style_tab_nonce_action', 'qsm_style_tab_nonce' ); ?>
		<button id="save_styles_button" class="button-primary"><?php _e('Save Quiz Style', 'quiz-master-next'); ?></button>
	</form>
	<?php
}
?>
