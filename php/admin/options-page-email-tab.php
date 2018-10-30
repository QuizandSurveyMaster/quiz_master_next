<?php
/**
 * Creates the emails page tab when editing quizzes.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the email tab in the Quiz Settings Page
 *
 * @return void
 * @since 6.1.0
 */
function qsm_settings_email_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Emails', 'quiz-master-next' ), 'qsm_options_emails_tab_content' );
}
add_action( 'plugins_loaded', 'qsm_settings_email_tab', 5 );

/**
 * Creates the email content that is displayed on the email tab.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_options_emails_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = $_GET['quiz_id'];

	//Check to save email templates
	if (isset($_POST["mlw_save_email_template"]) && $_POST["mlw_save_email_template"] == "confirmation")
	{
		//Function Variables
		$mlw_qmn_email_id = intval($_POST["mlw_email_quiz_id"]);
		$mlw_qmn_email_template_total = intval($_POST["mlw_email_template_total"]);
		$mlw_qmn_email_admin_total = intval($_POST["mlw_email_admin_total"]);
		$mlw_send_user_email = intval( $_POST["sendUserEmail"] );
		$mlw_send_admin_email = intval( $_POST["sendAdminEmail"] );
		$mlw_admin_email = sanitize_text_field( $_POST["adminEmail"] );
		$mlw_email_from_text = sanitize_text_field( $_POST["emailFromText"] );
		$from_address = sanitize_text_field( $_POST["emailFromAddress"] );
		$reply_to_user = sanitize_text_field( $_POST["replyToUser"] );

		//from email array
		$from_email_array = array(
			'from_name' => $mlw_email_from_text,
			'from_email' => $from_address,
			'reply_to' => $reply_to_user
		);

		//Create new array
		$i = 1;
		$mlw_qmn_new_email_array = array();
		while ( $i <= $mlw_qmn_email_template_total ) {
			if ( $_POST["user_email_".$i] != "Delete" ) {
				$mlw_qmn_email_each = array(intval($_POST["user_email_begin_".$i]), intval($_POST["user_email_end_".$i]), htmlspecialchars(stripslashes($_POST["user_email_".$i]), ENT_QUOTES), htmlspecialchars(stripslashes($_POST["user_email_subject_".$i]), ENT_QUOTES));
				$mlw_qmn_new_email_array[] = $mlw_qmn_email_each;
			}
			$i++;
		}

		//Create new array
		$i = 1;
		$mlw_qmn_new_admin_array = array();
		while ($i <= $mlw_qmn_email_admin_total)
		{
			if ($_POST["admin_email_".$i] != "Delete")
			{
				$mlw_qmn_email_each = array(
					"begin_score" => intval($_POST["admin_email_begin_".$i]),
					"end_score" => intval($_POST["admin_email_end_".$i]),
					"message" => htmlspecialchars(stripslashes($_POST["admin_email_".$i]), ENT_QUOTES),
					"subject" => htmlspecialchars(stripslashes($_POST["admin_email_subject_".$i]), ENT_QUOTES)
				);
				$mlw_qmn_new_admin_array[] = $mlw_qmn_email_each;
			}
			$i++;
		}

		$from_email_array = serialize( $from_email_array );
		$mlw_qmn_new_email_array = serialize($mlw_qmn_new_email_array);
		$mlw_qmn_new_admin_array = serialize($mlw_qmn_new_admin_array);

		$mlw_new_email_results = $wpdb->update(
			$wpdb->prefix . "mlw_quizzes",
			array(
				'send_user_email' => $mlw_send_user_email,
				'send_admin_email' => $mlw_send_admin_email,
				'admin_email' => $mlw_admin_email,
				'email_from_text' => $from_email_array,
				'user_email_template' => $mlw_qmn_new_email_array,
				'admin_email_template' => $mlw_qmn_new_admin_array,
				'last_activity' => date("Y-m-d H:i:s")
			),
			array( 'quiz_id' => $mlw_qmn_email_id ),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			),
			array( '%d' )
		);
		if ( false != $mlw_new_email_results ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The email has been updated successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Email Templates Have Been Saved For Quiz Number $mlw_qmn_email_id" );
		}
		else
		{
			$mlwQuizMasterNext->alertManager->newAlert(sprintf(__('There has been an error in this action. Please share this with the developer. Error Code: %s', 'quiz-master-next'), '0017'), 'error');
			$mlwQuizMasterNext->log_manager->add("Error 0017", $wpdb->last_error.' from '.$wpdb->last_query, 0, 'error');
		}
	}

	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}

	//Load from email array
	$from_email_array = maybe_unserialize( $mlw_quiz_options->email_from_text );
	if ( ! isset( $from_email_array["from_email"] ) ) {
		$from_email_array = array(
			'from_name' => $mlw_quiz_options->email_from_text,
			'from_email' => $mlw_quiz_options->admin_email,
			'reply_to' => 1
		);
	}
	$js_data = array(
		'quizID' => $quiz_id,
		'nonce'  => wp_create_nonce( 'wp_rest' ),
	);
	wp_enqueue_script( 'qsm_emails_admin_script', plugins_url( '../../js/qsm-admin-emails.js', __FILE__ ), array( 'jquery-ui-sortable' ), $mlwQuizMasterNext->version );
	wp_localize_script( 'qsm_emails_admin_script', 'qsmEmailsObject', $js_data );
	?>
	<h2><?php esc_html_e( 'Emails', 'quiz-master-next' ); ?></h2>
	<p>Need assistance with this tab? <a href="https://docs.quizandsurveymaster.com/article/17-sending-emails" target="_blank">Check out the documentation</a> for this tab!</p>

	<!-- Template Variable Section -->
	<section>
		<h3 style="text-align: center;"><?php _e('Template Variables', 'quiz-master-next'); ?></h3>
		<div class="template_list_holder">
			<div class="template_variable">
				<span class="template_name">%CONTACT_X%</span> - <?php _e( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CONTACT_ALL%</span> - <?php _e( 'List user values for all contact fields', 'quiz-master-next' ); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%POINT_SCORE%</span> - <?php _e('Score for the quiz when using points', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%AVERAGE_POINT%</span> - <?php _e('The average amount of points user had per question', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%AMOUNT_CORRECT%</span> - <?php _e('The number of correct answers the user had', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%TOTAL_QUESTIONS%</span> - <?php _e('The total number of questions in the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CORRECT_SCORE%</span> - <?php _e('Score for the quiz when using correct answers', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_NAME%</span> - <?php _e('The name the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_BUSINESS%</span> - <?php _e('The business the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_PHONE%</span> - <?php _e('The phone number the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%USER_EMAIL%</span> - <?php _e('The email the user entered before the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%QUIZ_NAME%</span> - <?php _e('The name of the quiz', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%QUESTIONS_ANSWERS%</span> - <?php _e('Shows the question, the answer the user provided, and the correct answer', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%COMMENT_SECTION%</span> - <?php _e('The comments the user entered into comment box if enabled', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%TIMER%</span> - <?php _e('The amount of time user spent on quiz in seconds', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%TIMER_MINUTES%</span> - <?php _e('The amount of time user spent on quiz in minutes', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_POINTS%%/CATEGORY_POINTS%</span> - <?php _e('The amount of points a specific category earned.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<spane class="template_name">%AVERAGE_CATEGORY_POINTS%%/AVERAGE_CATEGORY_POINTS%</span> - <?php _e('The average amount of points a specific category earned.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_SCORE%%/CATEGORY_SCORE%</span> - <?php _e('The score a specific category earned.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_AVERAGE_POINTS%</span> - <?php _e('The average points from all categories.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CATEGORY_AVERAGE_SCORE%</span> - <?php _e('The average score from all categories.', 'quiz-master-next'); ?>
			</div>
			<div class="template_variable">
				<span class="template_name">%CURRENT_DATE%</span> - <?php _e('The Current Date', 'quiz-master-next'); ?>
			</div>
			<?php do_action('qmn_template_variable_list'); ?>
		</div>
		<div style="clear:both;"></div>
	</section>

	<button id="save_email_button" class="button-primary" onclick="javascript: document.mlw_quiz_save_email_form.submit();"><?php _e('Save Email Templates And Settings', 'quiz-master-next'); ?></button>
	<form method="post" action="" name="mlw_quiz_save_email_form">
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="emailFromText"><?php _e("What is the From Name for the email sent to users and admin?", 'quiz-master-next'); ?></label></th>
			<td><input name="emailFromText" type="text" id="emailFromText" value="<?php echo $from_email_array["from_name"]; ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="emailFromAddress"><?php _e("What is the From Email address for the email sent to users and admin?", 'quiz-master-next'); ?></label></th>
			<td><input name="emailFromAddress" type="text" id="emailFromAddress" value="<?php echo $from_email_array["from_email"]; ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="replyToUser"><?php _e('Add user\'s email as Reply-To on admin email?', 'quiz-master-next'); ?></label></th>
			<td>
				<input type="radio" id="radio19" name="replyToUser" <?php checked( $from_email_array["reply_to"], 0 ); ?> value='0' /><label for="radio19"><?php _e('Yes', 'quiz-master-next'); ?></label><br>
				<input type="radio" id="radio20" name="replyToUser" <?php checked( $from_email_array["reply_to"], 1 ); ?> value='1' /><label for="radio20"><?php _e('No', 'quiz-master-next'); ?></label><br>
			</td>
		</tr>
		</table>
	</form>

	<!-- Emails Section -->
	<section>
		<h3>Your Emails</h3>
		<button class="save-emails button-primary"><?php esc_html_e( 'Save Emails', 'quiz-master-next' ); ?></button>
		<button class="add-new-email button"><?php esc_html_e( 'Add New Email', 'quiz-master-next' ); ?></button>
		<div id="emails"></div>
		<button class="save-emails button-primary"><?php esc_html_e( 'Save Emails', 'quiz-master-next' ); ?></button>
		<button class="add-new-email button"><?php esc_html_e( 'Add New Email', 'quiz-master-next' ); ?></button>
	</section>

	<!-- Templates -->
	<script type="text/template" id="tmpl-email">
		<div class="email">
			<header class="email-header">
				<div><button class="delete-email-button"><span class="dashicons dashicons-trash"></span></button></div>
			</header>
			<main class="email-content">
				<div class="email-when">
					<div class="email-content-header">
						<h4>When...</h4>
						<p>Set conditions for when this page should be shown. Leave empty to set this as the default page.</p>
					</div>
					<div class="email-when-conditions">
						<!-- Conditions go here. Review template below. -->
					</div>
					<button class="new-condition button"><?php esc_html_e( 'Add additional condition', 'quiz-master-next' ); ?></button>
				</div>
				<div class="email-show">
					<div class="email-content-header">
						<h4>...Show</h4>
						<p>Create the results page that should be shown when the conditions are met.</p>
					</div>
					<textarea class="email-template">{{{ data.page }}}</textarea>
				</div>
			</main>
		</div>
	</script>

	<script type="text/template" id="tmpl-email-condition">
		<div class="email-condition">
			<button class="delete-condition-button"><span class="dashicons dashicons-trash"></span></button>
			<select class="email-condition-criteria">
				<option value="points" <# if (data.criteria == 'points') { #>selected<# } #>>Total points earned</option>
				<option value="score" <# if (data.criteria == 'score') { #>selected<# } #>>Correct score percentage</option>
				<?php do_action( 'qsm_results_page_condition_criteria' ); ?>
			</select>
			<select class="email-condition-operator">
				<option value="equal" <# if (data.operator == 'equal') { #>selected<# } #>>is equal to</option>
				<option value="not-equal" <# if (data.operator == 'not-equal') { #>selected<# } #>>is not equal to</option>
				<option value="greater-equal" <# if (data.operator == 'greater-equal') { #>selected<# } #>>is greater than or equal to</option>
				<option value="greater" <# if (data.operator == 'greater') { #>selected<# } #>>is greater than</option>
				<option value="less-equal" <# if (data.operator == 'less-equal') { #>selected<# } #>>is less than or equal to</option>
				<option value="less" <# if (data.operator == 'less') { #>selected<# } #>>is less than</option>
				<?php do_action( 'qsm_results_page_condition_operator' ); ?>
			</select>
			<input type="text" class="email-condition-value" value="{{ data.value }}">
		</div>
	</script>
	<?php
}
?>
