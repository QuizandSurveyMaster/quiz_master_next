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
	global $wpdb, $mlwQuizMasterNext;
	$quiz_id         = isset( $_GET["quiz_id"] ) ? intval( $_GET["quiz_id"] ) : 0;
	$user_id         = get_current_user_id();
	$show_fields     = get_user_meta( $user_id, 'qsm_show_disabled_contact_fields', true );
	$show_fields     = empty( $show_fields ) ? true : $show_fields;
	$contact_form    = QSM_Contact_Manager::load_fields( 'edit' );
	$quiz_options    = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'quiz_options' );
	wp_localize_script( 'qsm_admin_js', 'qsmContactObject', array(
		'contactForm' => $contact_form,
		'quizID'      => $quiz_id,
		'saveNonce'   => wp_create_nonce( 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ),
	) );
	?>
	<div class="contact-message"></div>
	<h2 class="qsm-page-subheading" style="font-weight: 500;"><?php esc_html_e( 'Setup Contact Form', 'quiz-master-next' ); ?></h2>
	<div id="poststuff" class="contact-form-builder-wrap">
		<div class="contact-tab-content">
			<label class="hide-control">
				<input type="checkbox" class="show-disabled-fields" <?php echo ('true' == $show_fields && true == $show_fields) ? 'checked' : ''; ?> >
				<span><?php esc_html_e( 'Show Disabled Fields', 'quiz-master-next' ); ?></span>
			</label>
			<div class="contact-form"></div>
			<a class="add-contact-field qsm-dashed-btn qsm-block-btn">+ <?php esc_html_e( 'Add New Field', 'quiz-master-next' ); ?></a>
		</div>
		<div class="contact-tab-sidebar">
			<div id="contactformsettings" class="postbox ">
				<div class="postbox-header">
					<h2 class="hndle"><?php esc_html_e( 'Form Options', 'quiz-master-next' ); ?></h2>
				</div>
				<div class="inside">
					<div class="inside-row">
						<label><input type="checkbox" name="contact_info_location" value="1" <?php checked( $quiz_options['contact_info_location'], '1', true )?>><span><?php esc_html_e('Show contact form after the quiz', 'quiz-master-next');?></span></label>
					</div>
					<div class="inside-row">
						<label><input type="checkbox" name="loggedin_user_contact" value="0" <?php checked( $quiz_options['loggedin_user_contact'], 0, true )?>><span><?php esc_html_e('Show contact form to logged in users', 'quiz-master-next');?></span></label>
					</div>
					<div class="inside-row">
						<label><input type="checkbox" name="disable_first_page" value="1" <?php checked( $quiz_options['disable_first_page'], '1', true )?>><span><?php esc_html_e('Disable first page of quiz', 'quiz-master-next');?></span></label>
					</div>
					<div class="inside-row">
						<label><input type="checkbox" name="contact_disable_autofill" value="1" <?php checked( $quiz_options['contact_disable_autofill'], '1', true )?>><span><?php esc_html_e('Disable autofill entries', 'quiz-master-next');?></span></label>
					</div>
				</div>
			</div>
			<a class="save-contact button-primary qsm-block-btn" style="padding: 4px;"><?php esc_html_e( 'Save Form', 'quiz-master-next' ); ?></a>
		</div>
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
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( intval( $quiz_id ) );
	$user_id = get_current_user_id();
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ) ) {
		die( 'Busted!' );
	}

	$results = $data     = array();
	if ( isset( $_POST['contact_form'] ) ) {
		$data = qsm_sanitize_rec_array( wp_unslash( $_POST['contact_form'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
	/**
	 * Store contact form related quiz options.
	 */
	if ( isset( $_POST['settings'] ) ) {
		$quiz_options    = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'quiz_options' );
		$settings        = qsm_sanitize_rec_array( wp_unslash( $_POST['settings'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( $settings as $key => $val ) {
			$quiz_options[ $key ] = $val;
		}
		if ( isset($settings['loggedin_user_contact']) ) {
			$quiz_options['loggedin_user_contact'] = (1 == $settings['loggedin_user_contact']) ? 0 : 1;
		}
		$mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'quiz_options', $quiz_options );
	}

	// Sends posted form data to Contact Manager to sanitize and save.
	$results['status'] = QSM_Contact_Manager::save_fields( $quiz_id, $data );
	echo wp_json_encode( $results );
	die();
}

add_action( 'wp_ajax_qsm_show_disabled_contact_fields', 'qsm_show_disabled_contact_fields' );
function qsm_show_disabled_contact_fields() {
	global $wpdb, $mlwQuizMasterNext;
	$user_id = get_current_user_id();
	if ( isset( $_POST['show'] ) ) {
		update_user_meta( $user_id, 'qsm_show_disabled_contact_fields', sanitize_text_field( wp_unslash( $_POST['show'] ) ) );
	}
	// Sends posted form data to Contact Manager to sanitize and save.
	echo '1';
	die();
}

function qsm_options_contact_tab_template() {
	?>
	<!-- View for question in question bank -->
	<script type="text/template" id="tmpl-contact-form-field">
		<div class="contact-form-field new">
			<div class="field-required-flag">*</div>
			<div class="contact-form-group sortable-handle">
				<a href="javascript:void(0)" class="move-field" title="<?php esc_html_e('Move', 'quiz-master-next');?>"><span class="dashicons dashicons-move"></span></a>
			</div>
			<div class="contact-form-group contact-form-inputs">
				<label class="contact-form-label"><?php esc_html_e('Type', 'quiz-master-next');?></label>
				<select class="contact-form-control type-control" <# if ( "true" == data.is_default || true == data.is_default ) { #>disabled<# } #>>
					<option value="none" <# if (data.type == '') { #>selected<# } #> ><?php esc_html_e('Select a type...', 'quiz-master-next');?></option>
					<option value="text" <# if (data.type == 'text') { #>selected<# } #> ><?php esc_html_e('Text', 'quiz-master-next');?></option>
					<option value="email" <# if (data.type == 'email') { #>selected<# } #> ><?php esc_html_e('Email', 'quiz-master-next');?></option>
					<option value="url" <# if (data.type == 'url') { #>selected<# } #> ><?php esc_html_e('URL', 'quiz-master-next');?></option>
					<option value="number" <# if (data.type == 'number') { #>selected<# } #> ><?php esc_html_e('Number', 'quiz-master-next');?></option>
					<option value="date" <# if (data.type == 'date') { #>selected<# } #> ><?php esc_html_e('Date', 'quiz-master-next');?></option>
					<option value="checkbox" <# if (data.type == 'checkbox') { #>selected<# } #> ><?php esc_html_e('Checkbox', 'quiz-master-next');?></option>
					<?php do_action('qsm_extra_contact_form_field_type'); ?>	
				</select>
			</div>
			<div class="contact-form-group contact-form-inputs">
				<label class="contact-form-label"><?php esc_html_e('Label', 'quiz-master-next');?></label>
				<input type="text" class="contact-form-control label-control" value="{{data.label}}">
				<input type="hidden" class="use-control" value="{{data.use}}">
			</div>
			<div class="contact-form-group contact-form-actions">
				<div class="qsm-actions-link-box contact-form-actions-box">
					<a href="javascript:void(0)" class="settings-field" title="<?php esc_html_e('Settings', 'quiz-master-next');?>"><span class="dashicons dashicons-admin-generic"></span></a>
					<a href="javascript:void(0)" class="copy-field" title="<?php esc_html_e('Duplicate', 'quiz-master-next');?>"><span class="dashicons dashicons-admin-page"></span></a>
					<a href="javascript:void(0)" class="delete-field <# if ( "true" == data.is_default || true == data.is_default ) { #>disabled<# } #>" title="<?php esc_html_e('Delete', 'quiz-master-next');?>"><span class="dashicons dashicons-trash"></span></a>
				</div>
			</div>
			<div class="contact-form-group contact-form-switch">
				<label class="qsm-switch" title="<?php esc_html_e('Enable / Disable Field', 'quiz-master-next');?>"><input type="checkbox" class="enable-control" <# if ( "true" == data.enable || true == data.enable ) { #>checked<# } #> ><span class="switch-slider"></span></label>
			</div>
			<div class="contact-form-field-settings arrow-left" style="display:none;">
				<h3><?php esc_html_e('Settings', 'quiz-master-next');?></h3>
				<div class="contact-form-group required-option">
					<label class="contact-form-label"><input type="checkbox" name="required" class="required-control" <# if ( "true" == data.required || true == data.required ) { #>checked<# } #> ><span><?php esc_html_e('Required?', 'quiz-master-next');?></span></label>
				</div>
				<div class="contact-form-group min-max-option">
					<label class="contact-form-label"><?php esc_html_e('Min Length', 'quiz-master-next');?></label>
					<input type="number" class="contact-form-control" name="minlength" value="{{data.minlength}}">
				</div>
				<div class="contact-form-group min-max-option">
					<label class="contact-form-label"><?php esc_html_e('Max Length', 'quiz-master-next');?></label>
					<input type="number" class="contact-form-control" name="maxlength" value="{{data.maxlength}}">
				</div>
				<div class="contact-form-group email-option">
					<label class="contact-form-label"><?php esc_html_e('Allow Domains', 'quiz-master-next');?></label>
					<textarea class="contact-form-control" name="allowdomains">{{data.allowdomains}}</textarea>
					<em><?php esc_html_e('Leave blank to allow all domains. ', 'quiz-master-next');?></em><br/>
					<em><?php esc_html_e('Comma separated list of domains. (i.e. example.com,abc.com)', 'quiz-master-next');?></em>
				</div>
			</div>
		</div>
	</script>
	<?php
}
		