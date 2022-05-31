<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * This function adds the contact tab using our API.
 *
 * @return type description
 * @since 4.7.0
 */
function qsm_settings_contact_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( "Contact", 'quiz-master-next' ), 'qsm_options_contact_tab_content', 'contact' );
}

add_action( "plugins_loaded", 'qsm_settings_contact_tab', 5 );

/**
 * Adds the content for the options for contact tab.
 *
 * @return void
 * @since 4.7.0
 */
function qsm_options_contact_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;

	$quiz_id         = isset( $_GET["quiz_id"] ) ? intval( $_GET["quiz_id"] ) : 0;
	$user_id         = get_current_user_id();
	$contact_form    = QSM_Contact_Manager::load_fields( 'edit' );
	wp_localize_script( 'qsm_admin_js', 'qsmContactObject', array(
		'contactForm' => $contact_form,
		'quizID'      => $quiz_id,
		'saveNonce'   => wp_create_nonce( 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ),
	) );
	?>
	<div class="contact-message"></div>
	<div class="qsm-tab-header">
		<h2><?php esc_html_e( 'Contact Fields', 'quiz-master-next' ); ?></h2>
		<a class="save-contact button-primary"><?php esc_html_e( 'Save Fields', 'quiz-master-next' ); ?></a>
		<div class="clear clearfix"></div>
	</div>
	<div class="contact-form"></div>
	<div class="qsm-tab-footer">
		<a class="add-contact-field button">+ <?php esc_html_e( 'Add New Field', 'quiz-master-next' ); ?></a>
		<a class="save-contact button-primary"><?php esc_html_e( 'Save Fields', 'quiz-master-next' ); ?></a>
		<div class="clear clearfix"></div>
	</div>
	<?php
	add_action( 'admin_footer', 'qsm_options_contact_tab_template' );
}

add_action( 'wp_ajax_qsm_save_contact', 'qsm_contact_form_admin_ajax' );

/**
 * Saves the contact form from the quiz settings tab
 *
 * @since 0.1.0
 * @return void
 */
function qsm_contact_form_admin_ajax() {
	global $wpdb, $mlwQuizMasterNext;
	$quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
	$user_id = get_current_user_id();
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ) ) {
		die( 'Busted!' );
	}

	$results = $data     = array();
	if ( isset( $_POST['contact_form'] ) ) {
		$data = qsm_sanitize_rec_array( wp_unslash( $_POST['contact_form'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	// Sends posted form data to Contact Manager to sanitize and save.
	$results['status'] = QSM_Contact_Manager::save_fields( $quiz_id, $data );
	echo wp_json_encode( $results );
	die();
}

function qsm_options_contact_tab_template() {
	?>
	<!-- View for question in question bank -->
	<script type="text/template" id="tmpl-contact-form-field">
		<div class="contact-form-field new">
			<div class="field-required-flag">*</div>
			<div class="contact-form-group sortable-handle">
				<a href="javascript:void(0)" class="move-field" title="<?php _e('Move', 'quiz-master-next');?>"><span class="dashicons dashicons-move"></span></a>
			</div>
			<div class="contact-form-group contact-form-inputs">
				<label class="contact-form-label"><?php _e('Type', 'quiz-master-next');?></label>
				<select class="contact-form-control type-control" <# if ( "true" == data.is_default || true == data.is_default ) { #>disabled<# } #>>
					<option value="none" <# if (data.type == '') { #>selected<# } #> ><?php _e('Select a type...', 'quiz-master-next');?></option>
					<option value="text" <# if (data.type == 'text') { #>selected<# } #> ><?php _e('Text', 'quiz-master-next');?></option>
					<option value="email" <# if (data.type == 'email') { #>selected<# } #> ><?php _e('Email', 'quiz-master-next');?></option>
					<option value="url" <# if (data.type == 'url') { #>selected<# } #> ><?php _e('URL', 'quiz-master-next');?></option>
					<option value="number" <# if (data.type == 'number') { #>selected<# } #> ><?php _e('Number', 'quiz-master-next');?></option>
					<option value="date" <# if (data.type == 'date') { #>selected<# } #> ><?php _e('Date', 'quiz-master-next');?></option>
					<option value="checkbox" <# if (data.type == 'checkbox') { #>selected<# } #> ><?php _e('Checkbox', 'quiz-master-next');?></option>
				</select>
			</div>
			<div class="contact-form-group contact-form-inputs">
				<label class="contact-form-label"><?php _e('Label', 'quiz-master-next');?></label>
				<input type="text" class="contact-form-control label-control" value="{{data.label}}">
				<input type="hidden" class="use-control" value="{{data.use}}">
			</div>
			<div class="contact-form-group contact-form-actions">
				<div class="contact-form-actions-box">
					<a href="javascript:void(0)" class="settings-field" title="<?php _e('Settings', 'quiz-master-next');?>"><span class="dashicons dashicons-admin-generic"></span></a>
					<a href="javascript:void(0)" class="copy-field" title="<?php _e('Duplicate', 'quiz-master-next');?>"><span class="dashicons dashicons-admin-page"></span></a>
					<a href="javascript:void(0)" class="delete-field <# if ( "true" == data.is_default || true == data.is_default ) { #>disabled<# } #>" title="<?php _e('Delete', 'quiz-master-next');?>"><span class="dashicons dashicons-trash"></span></a>
				</div>
			</div>
			<div class="contact-form-group contact-form-switch">
				<label class="endable-disable-switch" title="<?php _e('Enable / Disable Field', 'quiz-master-next');?>"><input type="checkbox" class="enable-control" <# if ( "true" == data.enable || true == data.enable ) { #>checked<# } #> ><span class="switch-slider"></span></label>
			</div>
			<div class="contact-form-field-settings arrow-left" style="display:none;">
				<h3><?php _e('Settings', 'quiz-master-next');?></h3>
				<div class="contact-form-group required-option">
					<label class="contact-form-label"><input type="checkbox" name="required" class="required-control" <# if ( "true" == data.required || true == data.required ) { #>checked<# } #> ><span><?php _e('Required?', 'quiz-master-next');?></span></label>
				</div>
				<div class="contact-form-group min-max-option">
					<label class="contact-form-label"><?php _e('Min Length', 'quiz-master-next');?></label>
					<input type="number" class="contact-form-control" name="minlength" value="{{data.minlength}}">
				</div>
				<div class="contact-form-group min-max-option">
					<label class="contact-form-label"><?php _e('Max Length', 'quiz-master-next');?></label>
					<input type="number" class="contact-form-control" name="maxlength" value="{{data.maxlength}}">
				</div>
				<div class="contact-form-group email-option">
					<label class="contact-form-label"><?php _e('Allow Domains', 'quiz-master-next');?></label>
					<textarea class="contact-form-control" name="allowdomains">{{data.allowdomains}}</textarea>
					<em><?php _e('Leave blank to allow all domains. ', 'quiz-master-next');?></em><br/>
					<em><?php _e('Comma separated list of domains. (i.e. example.com,abc.com)', 'quiz-master-next');?></em>
				</div>
			</div>
		</div>
	</script>
	<?php
}
		