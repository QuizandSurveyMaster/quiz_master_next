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
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Emails', 'quiz-master-next' ), 'qsm_options_emails_tab_content', 'emails' );
}
add_action( 'init', 'qsm_settings_email_tab', 5 );

/**
 * Creates the email content that is displayed on the email tab.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_options_emails_tab_content() {
	global $mlwQuizMasterNext, $wpdb;
	$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$user_id = get_current_user_id();
	$template_from_script = qsm_get_parsing_script_data( 'templates.json' );
	$template_from_script = apply_filters( 'qsm_email_templates_list_before', $template_from_script, $quiz_id );
	$table_name = $wpdb->prefix . 'mlw_quiz_output_templates';
	$temlpate_sql = "SELECT * FROM {$table_name} WHERE template_type='email'";
	$my_email_templates = $wpdb->get_results($temlpate_sql);
	$qsm_dependency_list = qsm_get_dependency_plugin_list();
	$js_data = array(
		'quizID'            => $quiz_id,
		'nonce'             => wp_create_nonce( 'wp_rest' ),
		'qsm_user_ve'       => get_user_meta( $user_id, 'rich_editing', true ),
		'rest_user_nonce'   => wp_create_nonce( 'wp_rest_nonce_' . $quiz_id . '_' . $user_id ),
		'my_tmpl_data'      => $my_email_templates,
		'script_tmpl'       => $template_from_script,
		'add_tmpl_nonce'    => wp_create_nonce( 'qsm_add_template' ),
		'remove_tmpl_nonce' => wp_create_nonce( 'qsm_remove_template' ),
		'dependency'        => $qsm_dependency_list,
		'required_addons'   => __('Required Add-ons', 'quiz-master-next'),
		'used_addons'       => __('Addons :', 'quiz-master-next'),
	);
	wp_localize_script( 'qsm_admin_js', 'qsmEmailsObject', $js_data );
	do_action( 'qsm_options_email_tab_content_before' );
	$quiz_options    = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'quiz_options' );
	if ( isset( $quiz_options['send_email'] ) && 1 != $quiz_options['send_email'] ) {
		?>
		<div class="error notice-large notice-error">
			<p><?php esc_html_e( 'Emails are turned off. Please update ', 'quiz-master-next');?> <a href="<?php echo esc_url( admin_url('admin.php?page=mlw_quiz_options&quiz_id='.$quiz_id.'&tab=options') );?>"> <?php esc_html_e('this setting', 'quiz-master-next');?></a><?php esc_html_e( ' for the emails to work properly.', 'quiz-master-next' ); ?></p>
		</div>
		<?php
	}
	?>

<!-- Emails Section -->
<section class="qsm-quiz-email-tab" style="margin-top: 15px;">
	<div id="qsm_emails">
		<div style="margin-bottom: 30px;margin-top: 35px;" class="qsm-spinner-loader"></div>
	</div>
	<button class="add-new-email button"><?php esc_html_e( 'Add New Email', 'quiz-master-next' ); ?></button>
	<div class="option-page-result-page-tab-footer">
		<div id="footer-bar-notice" class="footer-bar-notice"></div>
		<div class="result-tab-footer-buttons">
			<a class="button-secondary qsm-show-all-variable-text qsm-common-button-styles" href="javascript:void(0)"><?php esc_html_e( 'Insert Template Variables', 'quiz-master-next' ); ?></a>
			<button class="save-emails button-primary qsm-common-button-styles"><?php esc_html_e( 'Save Emails', 'quiz-master-next' ); ?></button>
		</div>
	</div>
</section>
<!-- Templates -->
	<?php add_action( 'admin_footer', 'qsm_options_emails_tab_template' ); ?>
<!--Template popup-->
<div class="qsm-popup qsm-popup-slide" id="show-all-variable" aria-hidden="false">
	<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
		<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-3-title">
			<header class="qsm-popup__header" style="display: block;">
				<h2 class="qsm-popup__title"><?php esc_html_e( 'Template Variables', 'quiz-master-next' ); ?></h2>
				<span class="description">
					<?php esc_html_e( 'Use these dynamic variables to customize your quiz or survey. Just copy and paste one or more variables into the content templates and these will be replaced by actual values when user takes a quiz.', 'quiz-master-next' ); ?>
					<br /><strong><?php esc_html_e( 'Note: ', 'quiz-master-next' ); ?></strong>
					<?php esc_html_e( 'Always use uppercase while using these variables.', 'quiz-master-next' ); ?>
				</span>
			</header>
			<main class="qsm-popup__content" id="show-all-variable-content">
				<?php
				$variable_list                                      = qsm_text_template_variable_list();
				$variable_list = qsm_extra_template_and_leaderboard($variable_list);
				// filter to add or remove variables from variable list for email tab
				$variable_list = apply_filters( 'qsm_text_variable_list_email', $variable_list );

				if ( $variable_list ) {
					// sort $variable list for backward compatibility
					foreach ( $variable_list as $variable_name => $variable_value ) {
						if ( ! is_array( $variable_value ) ) {
							$variable_list['Other Variables'][ $variable_name ] = $variable_value;
						}
					}
					foreach ( $variable_list as $category_name => $category_variables ) {
						// check if the $category_variables is an array for backward compatibility
						if ( is_array( $category_variables ) ) {
							$classname = "qsm-text-template-span ";
							$qsm_badge = "";
							$upgrade_link = "";$variable = "";
							$tooltip = '';
							if ( ( ! class_exists( 'QSM_Extra_Variables' ) ) && ( 'Extra Template Variables' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link('extra-template-variables');
								$classname = "qsm-upgrade-popup-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";

							}
							if ( ( ! class_exists( 'Mlw_Qmn_Al_Widget' ) ) && ( 'Advanced Leaderboard' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link('downloads/advanced-leaderboard/');
								$classname = "qsm-upgrade-popup-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";

							}
							if ( ( ! class_exists( 'QSM_Analysis' ) ) && ( 'Analysis' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link('downloads/results-analysis/');
								$classname = "qsm-upgrade-popup-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";

							}
							if ( ( ! class_exists( 'QSM_Advanced_Assessment' ) ) && ( 'Advanced Assessment' == $category_name ) ) {
								$upgrade_link = qsm_get_plugin_link( 'downloads/advanced-assessment/' );
								$classname = "qsm-upgrade-popup-variable qsm-upgrade-popup-advanced-assessment-variable";
								$qsm_badge = "<a  href =".$upgrade_link." target='_blank' class='qsm-upgrade-popup-badge'>".esc_html__( 'PRO', 'quiz-master-next' )."</a>";
							}
							?>
							<div><h2 class="qsm-upgrade-popup-category-name"><?php echo esc_attr( $category_name ); ?></h2><?php echo  wp_kses_post( $qsm_badge ) ; ?></div>
							<?php
							foreach ( $category_variables as $variable_key => $variable ) {
								?>
								<div class="popup-template-span-wrap">
									<span class="qsm-text-template-span <?php echo esc_attr( $classname );?>">
									<?php if ( false !== strpos( $classname, 'qsm-upgrade-popup-variable') ) {?>
										<span class="button button-default template-variable qsm-tooltips-icon"><?php echo esc_attr( $variable_key ); ?>
											<span class="qsm-tooltips qsm-upgrade-tooltip"><?php echo esc_html__( 'Available in pro', 'quiz-master-next' );?></span>
										</span>
									<?php } else { ?>
										<span class="button button-default template-variable"><?php echo esc_attr( $variable_key ); ?></span>
										<span class='button click-to-copy'><?php esc_html_e('Click to Copy', 'quiz-master-next'); ?></span>
										<span class="temp-var-seperator">
											<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
											<span class="qsm-tooltips"><?php echo esc_attr( $variable ); ?></span>
											</span>
										</span>
									<?php } ?>
									</span>
								</div>
								<?php
							}
						}
					}
				}
				?>
			</main>
			<footer class="qsm-popup__footer" style="text-align: right;">
				<button class="button button-default" data-micromodal-close=""
					aria-label="Close this dialog window"><?php esc_html_e( 'Close [Esc]', 'quiz-master-next' ); ?></button>
			</footer>
		</div>
	</div>
</div>
	<?php
	qsm_result_and_email_popups_for_templates( $template_from_script, $my_email_templates, 'email');
}

/**
 * Adds the email templates that is displayed on the email tab..
 *
 * @since 7.3.5
 */
function qsm_options_emails_tab_template() {
	global $wpdb;
	$quiz_id    = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$categories = array();
	$enabled    = get_option( 'qsm_multiple_category_enabled' );
	if ( $enabled && 'cancelled' !== $enabled ) {
		$query = $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}terms WHERE term_id IN ( SELECT DISTINCT term_id FROM {$wpdb->prefix}mlw_question_terms WHERE quiz_id = %d ) ORDER BY name ASC", $quiz_id );
	} else {
		$query = $wpdb->prepare( "SELECT DISTINCT category FROM {$wpdb->prefix}mlw_questions WHERE category <> '' AND quiz_id = %d", $quiz_id );
	}
	$categories = $wpdb->get_results( $query, ARRAY_N );
	?>
<script type="text/template" id="tmpl-email">
	<div class="qsm-email">
		<header class="qsm-email-header">
			<strong><?php esc_html_e( 'Email Template ', 'quiz-master-next' ); ?> {{data.id}}</strong>
			<div class="qsm-template-btn-group">
				<div class="qsm-actions-link-box">
					<a href="javascript:void(0)" class="qsm-settings-box-email-button" title="<?php esc_attr_e( 'Quick Settings', 'quiz-master-next' ); ?>" >
						<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/gear.svg'); ?>" alt="gear.svg"/>
					</a>
					<a href="javascript:void(0)" class="qsm-duplicate-email-template-button" title="<?php esc_attr_e( 'Duplicate Page', 'quiz-master-next' ); ?>" >
						<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/copy.svg'); ?>" alt="copy.svg"/>
					</a>
					<a href="javascript:void(0)" data-template-type="email" class="qsm-insert-page-template-anchor" title="<?php esc_attr_e( 'Add Template', 'quiz-master-next' ); ?>" >
						<div class="qsm-insert-template-wrap">
							<div class="qsm-insert-template-options">
								<label>
									<input type="radio" name="qsm-template-action" value="new" class="qsm-insert-template-action" checked="checked">
									<?php esc_html_e( 'New', 'quiz-master-next' ); ?>
								</label>
								<label>
									<input type="radio" name="qsm-template-action" value="replace" class="qsm-insert-template-action">
									<?php esc_html_e( 'Replace', 'quiz-master-next' ); ?>
								</label>
							</div>
							<div class="qsm-insert-template-container">
								<div class="qsm-insert-template-left">
									<input placeholder="<?php esc_attr_e( 'Type Template name here ', 'quiz-master-next' ); ?>" type="text"  id="qsm-insert-page-template-title-{{data.id}}" class="qsm-insert-page-template-title">
									<div style="display: none;" class="qsm-to-replace-page-template-wrap">
										<select class="qsm-to-replace-page-template"></select>
									</div>
									<p class="qsm-insert-template-response"></p>
								</div>
								<div class="qsm-insert-template-right">
									<button data-id="{{data.id}}" class="qsm-save-page-template-button button"><?php esc_html_e( 'Save', 'quiz-master-next' ); ?></button>
								</div>
							</div>
						</div>
						<img class="qsm-common-svg-image-class " src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/save-3-line.svg'); ?>" alt="save-3-line.svg"/>
					</a>
					<a href="javascript:void(0)" class="qsm-more-settings-box-email-button" title="<?php esc_attr_e( 'More Options', 'quiz-master-next' ); ?>" >
						<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/more-2-fill.svg'); ?>" alt="more-2-fill.svg"/>
					</a>
				</div>
				<div class="qsm-actions-link-box qsm-toggle-action-wrapper">
					<a href="javascript:void(0)" class="qsm-toggle-email-template-button">
						<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/arrow-down-s-line.svg'); ?>" alt="arrow-down-s-line.svg"/>
					</a>
				</div>
				<div class="qsm-more-settings-box-details">
					<?php do_action( 'qsm_email_page_more_settings_box_before' ); ?>
					<a href="javascript:void(0)" data-type="email" class="qsm-view-templates-list" title="<?php esc_attr_e( 'Change Template', 'quiz-master-next' ); ?>" >
					<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/refresh-line.svg'); ?>" alt="refresh-line.svg"/>
					<span><?php esc_html_e( 'Change Template', 'quiz-master-next' ); ?></span>
					</a>
					<a href="javascript:void(0)" class="qsm-delete-email-button" title="<?php esc_attr_e( 'Delete Page', 'quiz-master-next' ); ?>" >
					<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/trash.svg'); ?>" alt="trash.svg"/><span><?php esc_html_e( 'Delete Page ', 'quiz-master-next' ); ?></span>
					</a>
					<?php do_action( 'qsm_email_page_more_settings_box_after' ); ?>
				</div>
				<div class="qsm-settings-box-details">
					<?php do_action( 'qsm_email_page_settings_box_before' ); ?>
					<label class="qsm-template-mark-as-default">
						<input type="checkbox" name="qsm_mark_as_default" value="{{data.id}}" <# if( undefined != data.default_mark && data.default_mark == data.id ) { #> checked <# } #> class="qsm-mark-as-default"/>
						<?php esc_html_e( 'Mark as default', 'quiz-master-next' ); ?>
					</label>
					<?php do_action( 'qsm_email_page_settings_box_after' ); ?>
				</div> <!-- Closing qsm-settings-box-details -->
			<div>
		</header>
		<main class="qsm-email-content">
			<div class="qsm-email-when">
				<div class="email-content-header">
					<h4><?php esc_html_e( 'When..', 'quiz-master-next' ); ?></h4>
					<p><?php esc_html_e( 'Condition for sending email', 'quiz-master-next' ); ?></p>
				</div>
				<div class="qsm-email-when-conditions">
					<!-- Conditions go here. Review template below. -->
				</div>
				<a class="qsm-new-condition qsm-block-btn" href="javascript:void(0);"><span>+</span><?php esc_html_e( ' Add Condition', 'quiz-master-next' ); ?></a>
			</div>
			<div class="email-show" data-email-page="{{ data.id }}">
				<div class="email-content-header">
					<h4><?php esc_html_e( '..Then', 'quiz-master-next' ); ?></h4>
				</div>
				<div class="qsm-email-page-template-options qsm-email-page-then-box-styles">
					<div class="qsm-email-page-template-buttons">
						<button class="button qsm-common-button-styles qsm-start-with-template" data-email-page="{{ data.id }}"><?php esc_html_e( 'Start With a Template', 'quiz-master-next' );?></button>
						<button class="button qsm-common-button-styles qsm-start-with-canvas"><?php esc_html_e( 'Default Template', 'quiz-master-next' );?></button>
					</div>
					<div class="qsm-email-page-template-learn-more">
						<p><a href="<?php echo esc_url( qsm_get_plugin_link('docs/advanced-topics/template-library', 'quiz-options-email-page') );?>" target="_blank" rel="noopener"><?php esc_html_e( "Learn More About Templates", 'quiz-master-next' ); ?></a></p>
					</div>
				</div>
				<div class="qsm-email-page-then-box-styles-wrap">
					<div class="qsm-email-page-common-section qsm-email-page-then-box-styles" >
						<label><?php esc_html_e( 'Who to send the email to? Put %USER_EMAIL% to send to user', 'quiz-master-next' ); ?></label>
						<?php do_action( 'qsm_after_send_email_label' ); ?>
						<input type="email" class="qsm-to-email" value="{{ data.to }}">
						<label class="qsm-email-reply-to">
							<input type="checkbox" class="reply-to" <# if ( "true" == data.replyTo || true == data.replyTo ) { #>checked<# } #>>
							<?php esc_html_e( 'Add user email as Reply-To', 'quiz-master-next' ); ?>
						</label>
						<label><?php esc_html_e( 'Email Subject', 'quiz-master-next' ); ?></label>
						<input type="text" class="qsm-email-subject" value="{{ data.subject }}">
					</div>
					<div class="qsm-email-page-editor-options qsm-email-page-then-box-styles" >
						<label><?php esc_html_e( 'Email Content', 'quiz-master-next' ); ?></label>
						<?php
						do_action( 'qsm_email_page_content_before',  $quiz_id, $categories );
						qsm_extra_shortcode_popup_window_button( $quiz_id, $categories ); ?>
						<textarea id="email-template-{{ data.id }}" class="email-template">
						{{{ data.content.replace(/%([^%]+)%|\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs, function(match, capturedValue) {
							let qsm_varaible_list = qsm_admin_messages.qsm_variables_name;
							for (let qsm_variable in qsm_admin_messages.qsm_variables_name) {
								variable_name = qsm_admin_messages.qsm_variables_name[qsm_variable];
								if( variable_name.includes('%%') ){
									var arrayValues = variable_name.split("%%");
									qsm_varaible_list = jQuery.merge(jQuery.merge([], arrayValues), qsm_varaible_list);
								};
								if( variable_name.includes('_X%') ){
									qsm_varaible_list[qsm_variable] = variable_name.slice(0, -2);
								}
							}
							if (qsm_is_substring_in_array(match, qsm_varaible_list)) {
								return '<qsmvariabletag>' + capturedValue + '</qsmvariabletag>';
							} else if (/\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs.test(match)) {
								return match.replace(/\[qsm[^\]]*\](.*?)\[\/qsm[^\]]*\]/gs, function(innerMatch, emailcontent) {
									const openingTag = innerMatch.match(/\[qsm[^\]]*\]/)[0];
									const closingTag = innerMatch.match(/\[\/qsm[^\]]*\]/)[0];
									return `<qsmextrashortcodetag>${openingTag}</qsmextrashortcodetag>${emailcontent}<qsmextrashortcodetag>${closingTag}</qsmextrashortcodetag>`;
								});
							} else {
								return match;
							}
						}) }}}
						</textarea>
						<div class="qsm-email-page-content-buttons ">
							<button type="button" class="button qsm-slashcommand-variables-button qsm-email-editor-custom-button"><?php esc_html_e('Add Variables', 'quiz-master-next'); ?></button>
							<span class="qsm-insert-template-variable-text"><?php esc_html_e( 'Or, Type', 'quiz-master-next' );?> / <?php esc_html_e( ' to insert template variables', 'quiz-master-next' ); ?></span>
						</div>
						<?php do_action( 'qsm_email_page_content_buttons_after',  $quiz_id, $categories ); ?>
					</div>
					<div class="qsm-email-page-common-section qsm-email-page-then-box-styles">
						<?php do_action( 'qsm_email_page_after',  $quiz_id, $categories ); ?>
					</div>
				</div>
			</div>
		</main>
	</div>
</script>

<script type="text/template" id="tmpl-email-condition">
	<div class="email-condition">
		<div class="qsm-condition-collection-wrap">
			<p><?php echo esc_html__( 'Condition ', 'quiz-master-next' ); ?> <span class="qsm-condition-collection-count"></span></p>
		</div>
		<div class="qsm-email-condition-mode">
			<div class="email-condition-category-container qsm-email-condition-container-inner">
				<label class="qsm-email-condition-title"><?php esc_html_e( 'Select Mode', 'quiz-master-next' ); ?></label>
				<select class="email-condition-category">
					<option value="quiz" <# if (data.category == 'quiz' || data.category == '') { #>selected<# } #>><?php esc_html_e( 'Quiz', 'quiz-master-next' ); ?></option>
						<?php if ( ! empty( $categories ) ) {
							$category_names = array_map(function( $category ) {
								return $category[0];
							}, $categories);
						?>
						<#
						let categories = '<?php echo wp_json_encode($category_names); ?>';
						let categories_array = JSON.parse(categories);
						#>
							<option value="category" <# if (data.category == 'category' || jQuery.inArray(data.category, categories_array) !== -1 ) { #>selected<# } #>><?php esc_html_e( 'Category', 'quiz-master-next' ); ?></option>
						<?php } else { ?>
							<option disabled value=""><?php esc_html_e( 'No Categories Available', 'quiz-master-next' ); ?></option>
						<?php }
						if ( ! class_exists( 'QSM_Advanced_Assessment' ) ) { ?>
							<option value="option-pro"><?php esc_html_e( 'Option (pro)', 'quiz-master-next' ); ?></option>
							<option value="label-pro"><?php esc_html_e( 'Label (pro)', 'quiz-master-next' ); ?></option>
						<?php }
						do_action( 'qsm_email_page_condition_category' ); ?>
				</select>
			</div>
			<div class="email-extra-condition-category-container qsm-email-condition-container-inner">
				<label class="qsm-email-condition-title"><?php esc_html_e( 'Select', 'quiz-master-next' ); ?> <span class="qsm-extra-condition-label"><?php esc_html_e( 'Category', 'quiz-master-next' ); ?></span></label>
				<select class="email-extra-condition-category">
					<?php if ( ! empty( $categories ) ) { ?>
						<?php foreach ( $categories as $cat ) { ?>
						<option class="qsm-condition-category" value="<?php echo esc_attr( ! empty( $cat[1] ) ? 'qsm-cat-' . $cat[1] : $cat[0] ); ?>" <# if (data.category == '<?php echo esc_attr( $cat[0] ); ?>' || data.extra_condition == '<?php echo esc_attr( ! empty( $cat[1] ) ? 'qsm-cat-' . $cat[1] : $cat[0] ); ?>') { #>selected<# } #>><?php echo esc_attr( $cat[0] ); ?></option>
						<?php } ?>
					<?php } else { ?>
						<option class="qsm-condition-category" value="" disabled><?php esc_html_e( 'No Categories Available', 'quiz-master-next' ); ?></option>
					<?php } ?>
					<?php do_action( 'qsm_email_extra_condition_category' ); ?>
				</select>
			</div>
			<button class="delete-condition-button">
				<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/trash.svg'); ?>" alt="trash.svg"/>
			</button>
		</div>
		<div class="qsm-email-condition-container">
			<div class="email-condition-criteria-container qsm-email-condition-container-inner">
				<label class="qsm-email-condition-title"><?php esc_html_e( 'Calculation Type', 'quiz-master-next' ); ?></label>
				<select class="email-condition-criteria">
					<option value="points" class="qsm-points-criteria" <# if (data.criteria == 'points') { #>selected<# } #>><?php esc_html_e( 'Total points', 'quiz-master-next' ); ?></option>
					<option value="score" class="qsm-score-criteria" <# if (data.criteria == 'score') { #>selected<# } #>><?php esc_html_e( 'Correct percentage', 'quiz-master-next' ); ?></option>
					<?php do_action( 'qsm_email_condition_criteria' ); ?>
				</select>
			</div>
			<?php do_action( 'qsm_email_extra_condition_fields' ); ?>
			<div class="email-condition-operator-container qsm-email-condition-container-inner">
				<label class="qsm-email-condition-title"><?php esc_html_e( 'Select Condition', 'quiz-master-next' ); ?></label>
				<select class="email-condition-operator">
					<option class="default_operator" value="equal" <# if (data.operator == 'equal') { #>selected<# } #>><?php esc_html_e( 'is equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="not-equal" <# if (data.operator == 'not-equal') { #>selected<# } #>><?php esc_html_e( 'is not equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="greater-equal" <# if (data.operator == 'greater-equal') { #>selected<# } #>><?php esc_html_e( 'is greater than or equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="greater" <# if (data.operator == 'greater') { #>selected<# } #>><?php esc_html_e( 'is greater than', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="less-equal" <# if (data.operator == 'less-equal') { #>selected<# } #>><?php esc_html_e( 'is less than or equal to', 'quiz-master-next' ); ?></option>
					<option class="default_operator" value="less" <# if (data.operator == 'less') { #>selected<# } #>><?php esc_html_e( 'is less than', 'quiz-master-next' ); ?></option>
					<?php do_action( 'qsm_email_condition_operator' ); ?>
				</select>
			</div>
			<div class="condition-default-value-container qsm-email-condition-container-inner">
				<label class="qsm-email-condition-title"><?php esc_html_e( 'Value', 'quiz-master-next' ); ?></label>
				<input type="text" class="email-condition-value condition-default-value" value="{{ data.value }}">
			</div>
			<?php do_action( 'qsm_email_condition_value' ); ?>
		</div>
	</div>
</script>

	<?php
	qsm_result_and_email_row_templates();
}
?>
