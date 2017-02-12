<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This function adds the certificate tab using our API.
*
* @return type description
* @since 4.4.0
*/
function qmn_settings_certificate_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( "Certificate (Beta)", 'quiz-master-next' ), 'qsm_options_certificate_tab_content' );
}
add_action( "plugins_loaded", 'qmn_settings_certificate_tab', 5 );

/**
* Creates the content that is in the certificate tab.
*
* @return void
* @since 4.4.0
*/
function qsm_options_certificate_tab_content() {

	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET["quiz_id"];
	//Saved Certificate Options
	if (isset($_POST["save_certificate_options"]) && $_POST["save_certificate_options"] == "confirmation")
	{
		$mlw_certificate_id = intval($_POST["certificate_quiz_id"]);
		$mlw_certificate_title = sanitize_text_field( stripslashes( $_POST["certificate_title"] ) );
		$mlw_certificate_text = wp_kses_post(  stripslashes( $_POST["certificate_template"] ) );
		$mlw_certificate_logo = esc_url_raw( $_POST["certificate_logo"] );
		$mlw_certificate_background = esc_url_raw( $_POST["certificate_background"] );
		$mlw_enable_certificates = intval($_POST["enableCertificates"]);
		$mlw_certificate = array(
			$mlw_certificate_title,
			$mlw_certificate_text,
			$mlw_certificate_logo,
			$mlw_certificate_background,
			$mlw_enable_certificates
		);
		$mlw_certificate_serialized = serialize($mlw_certificate);

		$update_results = $wpdb->update(
			$wpdb->prefix . "mlw_quizzes",
			array(
				'certificate_template' => $mlw_certificate_serialized,
				'last_activity' => date("Y-m-d H:i:s")
			),
			array( 'quiz_id' => $mlw_certificate_id ),
			array(
				'%s',
				'%s'
			),
			array( '%d' )
		);

		if ( $update_results ) {

			$mlwQuizMasterNext->alertManager->newAlert(__('The certificate has been updated successfully.', 'quiz-master-next'), 'success');
			$mlwQuizMasterNext->audit_manager->new_audit( "Certificate Options Have Been Edited For Quiz Number $mlw_certificate_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0012'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0012", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}

	//Load Certificate Options Variables
	if (is_serialized($mlw_quiz_options->certificate_template) && is_array(@unserialize($mlw_quiz_options->certificate_template)))
	{
		$mlw_certificate_options = @unserialize($mlw_quiz_options->certificate_template);
	}
	else
	{
		$mlw_certificate_options = array(__('Enter title here', 'quiz-master-next'), __('Enter text here', 'quiz-master-next'), '', '', 1);
	}

	?>
	<div id="tabs-5" class="mlw_tab_content">
		<h3><?php _e('Quiz Certificate (Beta)', 'quiz-master-next'); ?></h3>
		<div style="background:#fff;border-left: 4px solid #fff;padding: 1px 12px;margin: 5px 0 15px;border-left-color: #dc3232;">
			<p style="font-weight:bold;">Warning: This feature is being removed from the core version. Please use our new free Certificate addon. You can read more about this change in <a href="http://quizandsurveymaster.com/certificate-feature-moved/?utm_source=certificate_removal_notice&utm_medium=plugin&utm_campaign=qsm_plugin" target="_blank">our post about the certificate being moved.</a></p>
		</div>
		<p><?php _e('Enter in your text here to fill in the certificate for this quiz. Be sure to enter in the link variable into the templates on the Emails and Results Page tabs so the user can access the certificate.', 'quiz-master-next'); ?></p>
		<p><?php _e('These fields cannot contain HTML.', 'quiz-master-next'); ?></p>
		<button id="save_certificate_button" class="button-primary" onclick="javascript: document.quiz_certificate_options_form.submit();"><?php _e('Save Certificate Options', 'quiz-master-next'); ?></button>
		<?php
			echo "<form action='' method='post' name='quiz_certificate_options_form'>";
			echo "<input type='hidden' name='save_certificate_options' value='confirmation' />";
			echo "<input type='hidden' name='certificate_quiz_id' value='".$quiz_id."' />";
		?>
		<table class="form-table">

		</table>
		<button id="save_certificate_button" class="button-primary" onclick="javascript: document.quiz_certificate_options_form.submit();"><?php _e('Save Certificate Options', 'quiz-master-next'); ?></button>
		</form>
	</div>
	<?php
	$mlwQuizMasterNext->pluginHelper->generate_settings_section( 'quiz_certificate' );
}
?>
