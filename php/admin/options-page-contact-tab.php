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
	<div class="qsm-sub-tab-menu" style="display: inline-block;width: 100%;">
		<ul class="subsubsub">
			<li>
				<a href="javascript:void(0)" data-id="contact_form_setup" class="current quiz_style_tab"><?php esc_html_e( 'Setup', 'quiz-master-next' ); ?></a>
			</li>
			<li>
				<a href="javascript:void(0)" data-id="contact_form_option" class="quiz_style_tab"><?php esc_html_e( 'Options', 'quiz-master-next' ); ?></a>
			</li>
		</ul>
	</div>
	<div id="contact_form_setup" class="quiz_style_tab_content">
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
		</div>
	</div>
	<div id="contact_form_option" class="quiz_style_tab_content" style="display:none">
		<table id="contactformsettings" class="form-table" style="width: 100%;">
			<tbody>
				<tr valign="top">
					<th scope="row" class="qsm-opt-tr">
						<label for="contact_info_location"><?php esc_html_e( 'Contact form position', 'quiz-master-next' ); ?></label>
						<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
							<span class="qsm-tooltips"><?php esc_html_e( 'The form can be configured in Contact tab', 'quiz-master-next' ); ?></span>
						</span>
					</th>
					<td>
						<fieldset class="buttonset buttonset-hide" data-hide="1">
							<label for="contact_info_location-0">
								<input type="radio" id="contact_info_location-0" name="contact_info_location" value="0" <?php checked( $quiz_options['contact_info_location'], '0', true )?>>
								<?php esc_html_e( 'Show before quiz begins', 'quiz-master-next' ); ?>
							</label>
							<label for="contact_info_location-1">
								<input type="radio" id="contact_info_location-1" name="contact_info_location" value="1" <?php checked( $quiz_options['contact_info_location'], '1', true )?>>
								<?php esc_html_e( 'Show after the quiz ends', 'quiz-master-next' ); ?>
							</label>
							<?php do_action( 'qsm_contact_form_location_after', $quiz_options ); ?>
						</fieldset>
						<span class="qsm-opt-desc"><?php esc_html_e( 'Select where to display the contact form', 'quiz-master-next' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="qsm-opt-tr">
						<label for="loggedin_user_contact"><?php esc_html_e( 'Hide contact form to logged in users', 'quiz-master-next' ); ?></label>
						<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
							<span class="qsm-tooltips"><?php esc_html_e( 'The information will still get saved if this option is disabled', 'quiz-master-next' ); ?></span>
						</span>
					</th>
					<td>
						<fieldset class="buttonset buttonset-hide" data-hide="1">
							<label for="loggedin_user_contact-0">
								<input type="radio" id="loggedin_user_contact-1" name="loggedin_user_contact" value="1" <?php checked( $quiz_options['loggedin_user_contact'], '1', true )?>>
								<?php esc_html_e( 'Yes', 'quiz-master-next' ); ?>
							</label>
							<label for="loggedin_user_contact-1">
								<input type="radio" id="loggedin_user_contact-0" name="loggedin_user_contact" value="0" <?php checked( $quiz_options['loggedin_user_contact'], '0', true )?>>
								<?php esc_html_e( 'No', 'quiz-master-next' ); ?>
							</label>
						</fieldset>
						<span class="qsm-opt-desc"><?php esc_html_e( 'Logged in users can edit their contact information', 'quiz-master-next' ); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="qsm-opt-tr">
						<label for="contact_disable_autofill"><?php esc_html_e( 'Disable auto fill for contact input', 'quiz-master-next' ); ?></label>
					</th>
					<td>
						<fieldset class="buttonset buttonset-hide" data-hide="1">
							<label for="contact_disable_autofill-1">
								<input type="radio" id="contact_disable_autofill-1" name="contact_disable_autofill" value="1" <?php checked( $quiz_options['contact_disable_autofill'], '1', true )?>>
								<?php esc_html_e( 'Yes', 'quiz-master-next' ); ?>
							</label>
							<label for="contact_disable_autofill-0">
								<input type="radio" id="contact_disable_autofill-0" name="contact_disable_autofill" value="0" <?php checked( $quiz_options['contact_disable_autofill'], '0', true )?>>
								<?php esc_html_e( 'No', 'quiz-master-next' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="qsm-opt-tr">
						<label for="disable_first_page"><?php esc_html_e( 'Disable first page on quiz', 'quiz-master-next' ); ?></label>
					</th>
					<td>
						<fieldset class="buttonset buttonset-hide" data-hide="1">
							<label for="disable_first_page-1">
								<input type="radio" id="disable_first_page-1" name="disable_first_page" value="1" <?php checked( $quiz_options['disable_first_page'], '1', true )?>>
								<?php esc_html_e( 'Yes', 'quiz-master-next' ); ?>
							</label>
							<label for="disable_first_page-0">
								<input type="radio" id="disable_first_page-0" name="disable_first_page" value="0" <?php checked( $quiz_options['disable_first_page'], '0', true )?>>
								<?php esc_html_e( 'No', 'quiz-master-next' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="contact-page-tab-footer">
		<div class="footer-bar-notice"></div>
    	<a class="save-contact button-primary"><?php esc_html_e( 'Save Contact Form', 'quiz-master-next' ); ?></a>
	</div>
	<?php
	do_action( 'qsm_contact_form_settings_after' );
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
	$quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce-contact-save-' . $quiz_id . '-' . $user_id ) && isset( $_POST['show'] ) ) {
		update_user_meta( $user_id, 'qsm_show_disabled_contact_fields', sanitize_text_field( wp_unslash( $_POST['show'] ) ) );
	}
	// Sends posted form data to Contact Manager to sanitize and save.
	wp_send_json_success();
}

function qsm_options_contact_tab_template() {
	?>
	<!-- View for question in question bank -->
	<script type="text/template" id="tmpl-qsm-contact-form-field">
		<div class="qsm-contact-form-field new">
			<div class="field-required-flag">*</div>
			<div class="qsm-contact-form-group sortable-handle">
				<a href="javascript:void(0)" class="move-field" title="<?php esc_html_e('Move', 'quiz-master-next');?>"><span class="dashicons dashicons-move"></span></a>
			</div>
			<div class="qsm-contact-form-group contact-form-inputs">
				<label class="qsm-contact-form-label"><?php esc_html_e('Type', 'quiz-master-next');?></label>
				<select class="qsm-contact-form-control type-control" <# if ( "true" == data.is_default || true == data.is_default ) { #>disabled<# } #>>
					<option value="none" <# if (data.type == '') { #>selected<# } #> ><?php esc_html_e('Select a type...', 'quiz-master-next');?></option>
					<option value="text" <# if (data.type == 'text') { #>selected<# } #> ><?php esc_html_e('Text', 'quiz-master-next');?></option>
					<option value="email" <# if (data.type == 'email') { #>selected<# } #> ><?php esc_html_e('Email', 'quiz-master-next');?></option>
					<option value="url" <# if (data.type == 'url') { #>selected<# } #> ><?php esc_html_e('URL', 'quiz-master-next');?></option>
					<option value="number" <# if (data.type == 'number') { #>selected<# } #> ><?php esc_html_e('Number', 'quiz-master-next');?></option>
					<option value="date" <# if (data.type == 'date') { #>selected<# } #> ><?php esc_html_e('Date', 'quiz-master-next');?></option>
					<option value="checkbox" <# if (data.type == 'checkbox') { #>selected<# } #> ><?php esc_html_e('Checkbox', 'quiz-master-next');?></option>
					<option value="radio" <# if (data.type == 'radio') { #>selected<# } #> ><?php esc_html_e('Radio', 'quiz-master-next');?></option>
					<option value="select" <# if (data.type == 'select') { #>selected<# } #> ><?php esc_html_e('Select', 'quiz-master-next');?></option>
					<?php do_action('qsm_extra_contact_form_field_type'); ?>
				</select>
			</div>
			<div class="qsm-contact-form-group contact-form-inputs">
				<label class="qsm-contact-form-label"><?php esc_html_e('Label', 'quiz-master-next');?></label>
				<input type="text" class="qsm-contact-form-control label-control" value="{{data.label}}">
				<input type="hidden" class="use-control" value="{{data.use}}">
			</div>
			<div class="qsm-contact-form-group contact-form-actions">
				<div class="qsm-actions-link-box contact-form-actions-box">
					<a href="javascript:void(0)" class="settings-field" title="<?php esc_html_e('Settings', 'quiz-master-next');?>"><span class="dashicons dashicons-admin-generic"></span></a>
					<a href="javascript:void(0)" class="copy-field" title="<?php esc_html_e('Duplicate', 'quiz-master-next');?>"><span class="dashicons dashicons-admin-page"></span></a>
					<a href="javascript:void(0)" class="delete-field <# if ( "true" == data.is_default || true == data.is_default ) { #>disabled<# } #>" title="<?php esc_html_e('Delete', 'quiz-master-next');?>"><span class="dashicons dashicons-trash"></span></a>
				</div>
			</div>
			<div class="qsm-contact-form-group contact-form-switch">
				<label class="qsm-switch" title="<?php esc_html_e('Enable / Disable Field', 'quiz-master-next');?>"><input type="checkbox" class="enable-control" <# if ( "true" == data.enable || true == data.enable ) { #>checked<# } #> ><span class="switch-slider"></span></label>
			</div>
			<div class="qsm-contact-form-field-settings arrow-left" style="display:none;">
				<h3><?php esc_html_e('Settings', 'quiz-master-next');?></h3>
				<div class="qsm-contact-form-group qsm-required-option">
					<label class="qsm-contact-form-label"><input type="checkbox" name="required" class="qsm-required-control" <# if ( "true" == data.required || true == data.required ) { #>checked<# } #> ><span><?php esc_html_e('Required?', 'quiz-master-next');?></span></label>
				</div>
				<div class="qsm-contact-form-group qsm-hide-label-option">
					<label class="qsm-contact-form-label"><input type="checkbox" name="hide_label" class="qsm-hide-label-control" <# if ( "true" == data.hide_label || true == data.hide_label ) { #>checked<# } #> ><span><?php esc_html_e('Hide Label?', 'quiz-master-next');?></span></label>
				</div>
				<div class="qsm-contact-form-group qsm-use-default-option">
					<label class="qsm-contact-form-label"><input type="checkbox" name="use_default_option" class="qsm-use-default-control" <# if ( "true" == data.use_default_option || true == data.use_default_option ) { #>checked<# } #> ><span><?php esc_html_e('Make the first option default selection?', 'quiz-master-next');?></span></label>
				</div>
				<div class="qsm-contact-form-group qsm-placeholder-option">
					<label class="qsm-contact-form-label"><?php esc_html_e('Placeholder', 'quiz-master-next');?></label>
					<input type="text" class="qsm-contact-form-control" name="placeholder" value="{{data.placeholder}}">
				</div>
				<div class="qsm-contact-form-group qsm-field-options">
					<label class="qsm-contact-form-label"><?php esc_html_e('Options', 'quiz-master-next');?></label>
					<textarea title="<?php esc_html_e('Use comma seperated values.', 'quiz-master-next');?>" class="qsm-contact-form-control" placeholder="<?php esc_html_e('Option-1, Option-2, Option-3', 'quiz-master-next');?>" name="options" cols="30" rows="5">{{data.options}}</textarea>
				</div>
				<div class="qsm-contact-form-group qsm-min-max-option">
					<label class="qsm-contact-form-label"><?php esc_html_e('Min Length', 'quiz-master-next');?></label>
					<input type="number" class="qsm-contact-form-control" name="minlength" value="{{data.minlength}}">
				</div>
				<div class="qsm-contact-form-group qsm-min-max-option">
					<label class="qsm-contact-form-label"><?php esc_html_e('Max Length', 'quiz-master-next');?></label>
					<input type="number" class="qsm-contact-form-control" name="maxlength" value="{{data.maxlength}}">
				</div>
				<div class="qsm-contact-form-group qsm-email-option">
					<label class="qsm-contact-form-label"><?php esc_html_e('Allow Domains', 'quiz-master-next');?></label>
					<textarea class="qsm-contact-form-control" name="allowdomains">{{data.allowdomains}}</textarea>
					<em><?php esc_html_e('Leave blank to allow all domains. ', 'quiz-master-next');?></em><br/>
					<em><?php esc_html_e('Comma separated list of domains. (i.e. example.com,abc.com)', 'quiz-master-next');?></em>
				</div>
				<div class="qsm-contact-form-group qsm-email-option">
					<label class="qsm-contact-form-label"><?php esc_html_e('Block Domains', 'quiz-master-next');?></label>
					<textarea class="qsm-contact-form-control" name="blockdomains">{{data.blockdomains}}</textarea>
					<em><?php esc_html_e('Comma separated list of domains. (i.e. example.com,abc.com)', 'quiz-master-next');?></em>
				</div>
			</div>
		</div>
	</script>
	<?php
}
