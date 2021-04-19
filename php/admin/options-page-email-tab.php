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
	$quiz_id = intval( $_GET['quiz_id'] );
        $user_id = get_current_user_id();
	$js_data = array(
		'quizID' => $quiz_id,
		'nonce'  => wp_create_nonce( 'wp_rest' ),
                'qsm_user_ve' => get_user_meta($user_id, 'rich_editing', true),
	);
	wp_enqueue_script( 'qsm_emails_admin_script', plugins_url( '../../js/qsm-admin-emails.js', __FILE__ ), array( 'jquery-ui-sortable', 'qmn_admin_js' ), $mlwQuizMasterNext->version );
	wp_localize_script( 'qsm_emails_admin_script', 'qsmEmailsObject', $js_data );
	wp_enqueue_editor();
	wp_enqueue_media();
	?>        
	
	<!-- Emails Section -->
        <section class="qsm-quiz-email-tab" style="margin-top: 15px;">		
		<button class="save-emails button-primary"><?php esc_html_e( 'Save Emails', 'quiz-master-next' ); ?></button>
		<button class="add-new-email button"><?php esc_html_e( 'Add New Email', 'quiz-master-next' ); ?></button>                
                <a style="float: right;" class="qsm-show-all-variable-text" href="#"><?php _e('Insert Template Variables', 'quiz-master-next'); ?> <span class="dashicons dashicons-upload"></span></a>
                <a style="margin: 0 10px; float: right;" href="https://quizandsurveymaster.com/docs/v7/emails-tab/" target="_blank"><?php _e('View Documentation', 'quiz-master-next'); ?></a>
		<div id="qsm_emails"><div style="margin-bottom: 30px;margin-top: 35px;" class="qsm-spinner-loader"></div></div>
		<button class="save-emails button-primary"><?php esc_html_e( 'Save Emails', 'quiz-master-next' ); ?></button>
		<button class="add-new-email button"><?php esc_html_e( 'Add New Email', 'quiz-master-next' ); ?></button>
                <div class="qsm-alerts" style="margin-top: 20px;">
                    <?php
                    $mlwQuizMasterNext->alertManager->showAlerts();
                    ?>
                </div>
	</section>

	<!-- Templates -->
	<script type="text/template" id="tmpl-email">
		<div class="qsm-email">
			<header class="qsm-email-header">
				<div><button class="delete-email-button"><span class="dashicons dashicons-trash"></span></button></div>
			</header>
			<main class="qsm-email-content">
				<div class="email-when">
					<div class="email-content-header">
						<h4><?php esc_html_e('When...', 'quiz-master-next') ?></h4>
						<p><?php esc_html_e('Set conditions for when this email should be sent. Leave empty to set this as an email that is always sent.', 'quiz-master-next'); ?></p>
					</div>
					<div class="email-when-conditions">
						<!-- Conditions go here. Review template below. -->
					</div>
					<button class="new-condition button"><?php esc_html_e( 'Add additional condition', 'quiz-master-next' ); ?></button>
				</div>
				<div class="email-show">
					<div class="email-content-header">
						<h4><?php esc_html_e('...Send', 'quiz-master-next'); ?></h4>
						<p><?php esc_html_e('Create the email that should be sent when the conditions are met.', 'quiz-master-next'); ?></p>
					</div>
					<label><?php esc_html_e('Who to send the email to? Put %USER_EMAIL% to send to user', 'quiz-master-next'); ?></label>
					<input type="email" class="to-email" value="{{ data.to }}">
					<label><?php esc_html_e('Email Subject', 'quiz-master-next'); ?></label>
					<input type="text" class="subject" value="{{ data.subject }}">
					<label><?php esc_html_e('Email Content', 'quiz-master-next'); ?></label>
					<textarea id="email-template-{{ data.id }}" class="email-template">{{{ data.content }}}</textarea>
					<label><input type="checkbox" class="reply-to" <# if ( "true" == data.replyTo || true == data.replyTo ) { #>checked<# } #>>Add user email as Reply-To</label>
				</div>
			</main>
		</div>
	</script>

	<script type="text/template" id="tmpl-email-condition">
		<div class="email-condition">
			<button class="delete-condition-button"><span class="dashicons dashicons-trash"></span></button>
			<select class="email-condition-criteria">
				<option value="points" <# if (data.criteria == 'points') { #>selected<# } #>><?php _e('Total points earned', 'quiz-master-next'); ?></option>
				<option value="score" <# if (data.criteria == 'score') { #>selected<# } #>><?php _e('Correct score percentage', 'quiz-master-next'); ?></option>
				<?php do_action( 'qsm_email_condition_criteria' ); ?>
			</select>
			<?php do_action('qsm_email_extra_condition_fields'); ?>
			<select class="email-condition-operator">
				<option class="default_operator" value="equal" <# if (data.operator == 'equal') { #>selected<# } #>><?php _e('is equal to', 'quiz-master-next'); ?></option>
				<option class="default_operator" value="not-equal" <# if (data.operator == 'not-equal') { #>selected<# } #>><?php _e('is not equal to', 'quiz-master-next'); ?></option>
				<option class="default_operator" value="greater-equal" <# if (data.operator == 'greater-equal') { #>selected<# } #>><?php _e('is greater than or equal to', 'quiz-master-next'); ?></option>
				<option class="default_operator" value="greater" <# if (data.operator == 'greater') { #>selected<# } #>><?php _e('is greater than', 'quiz-master-next'); ?></option>
				<option class="default_operator" value="less-equal" <# if (data.operator == 'less-equal') { #>selected<# } #>><?php _e('is less than or equal to', 'quiz-master-next'); ?></option>
				<option class="default_operator" value="less" <# if (data.operator == 'less') { #>selected<# } #>><?php _e('is less than', 'quiz-master-next'); ?></option>
				<?php do_action( 'qsm_email_condition_operator' ); ?>
			</select>
			<input type="text" class="email-condition-value condition-default-value" value="{{ data.value }}">
			<?php do_action('qsm_email_condition_value'); ?>
		</div>
	</script>
        <!--Template popup-->
        <div class="qsm-popup qsm-popup-slide" id="show-all-variable" aria-hidden="false">
            <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
                <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-3-title">
                    <header class="qsm-popup__header" style="display: block;">
                            <h2 class="qsm-popup__title"><?php _e('Template Variables', 'quiz-master-next'); ?></h2>                            
                            <span class="description">
                                <?php _e('Use these dynamic variables to customize your quiz or survey. Just copy and paste one or more variables into the content templates and these will be replaced by actual values when user takes a quiz.', 'quiz-master-next'); ?>
                                <br/><b><?php _e('Note: ', 'quiz-master-next'); ?></b>
                                <?php _e('Always use uppercase while using these variables.', 'quiz-master-next'); ?>
                            </span>
                    </header>
                    <main class="qsm-popup__content" id="show-all-variable-content">
                        <?php
                        $variable_list = qsm_text_template_variable_list();
                        $email_exta_variable = array(
                            '%CONTACT_X%' => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),
                            '%CONTACT_ALL%' => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),
                            '%QUESTION_ANSWER_X%' => __('X = Question ID. It will show result of particular question.', 'quiz-master-next'),
                            '%QUESTIONS_ANSWERS_EMAIL%' => __('Shows the question, the answer provided by user, and the correct answer', 'quiz-master-next')
                        );  
                        $variable_list = array_merge($email_exta_variable, $variable_list);
                        $variable_list['%AVERAGE_CATEGORY_POINTS_X%'] = __('X: Category name - The average amount of points a specific category earned.', 'quiz-master-next');
                        unset($variable_list['%QUESTION%']);
                        unset($variable_list['%USER_ANSWER%']);
                        unset($variable_list['%USER_ANSWERS_DEFAULT%']);
                        unset($variable_list['%QUESTION_POINT_SCORE%']);
                        unset($variable_list['%CORRECT_ANSWER%']);
                        unset($variable_list['%USER_COMMENTS%']);
                        unset($variable_list['%CORRECT_ANSWER_INFO%']);
                        unset($variable_list['%QUESTIONS_ANSWERS%']);
                        unset($variable_list['%FACEBOOK_SHARE%']);
                        unset($variable_list['%TWITTER_SHARE%']);
                        if( $variable_list ){
                            foreach ( $variable_list as $key => $s_variable ) { ?>
                                <div class="popup-template-span-wrap">
                                    <span class="qsm-text-template-span">
                                        <button class="button button-default"><?php echo $key; ?></button>                                    
                                        <span class="dashicons dashicons-editor-help qsm-tooltips-icon">
                                            <span class="qsm-tooltips"><?php echo $s_variable; ?></span>
                                        </span>                                    
                                    </span>
                                </div>
                            <?php                     
                            }
                        }
                        ?>
                    </main>
                    <footer class="qsm-popup__footer" style="text-align: right;">                            
                            <button class="button button-default" data-micromodal-close="" aria-label="Close this dialog window"><?php _e('Close [Esc]', 'quiz-master-next'); ?></button>
                    </footer>
                </div>
            </div>
        </div>
	<?php
}
?>
