<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the add on page that is displayed in the add on settings page
 *
 * @return void
 * @since 4.4.0
 */
function qmn_addons_page() {
	if ( ! current_user_can( 'delete_others_qsm_quizzes' ) ) {
		return;
	}

	global $mlwQuizMasterNext;
	$active_tab = strtolower( str_replace( ' ', '-', isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : __( 'Featured Addons', 'quiz-master-next' ) ) );
	$tab_array  = $mlwQuizMasterNext->pluginHelper->get_addon_tabs();

	$addon_title = '';
	if ( isset( $_GET['tab'] ) && '' !== $_GET['tab'] ) {
		$key         = array_search( sanitize_text_field( wp_unslash( $_GET['tab'] ) ), array_column( $tab_array, 'slug' ), true );
		$addon_title = false !== $key ? $tab_array[ $key ]['title'] : '';
	}
	?>
<div class="wrap qsm-addon-setting-wrap qsm-ui qsm-ui-addons-page">
	<div class="qsm-ui-page-header qsm-ui-text">
		<div>
			<?php if ( '' !== $addon_title ) { ?>
				<a class="qsm-ui-addons-back" href="<?php echo esc_url( admin_url( 'admin.php?page=qmn_addons' ) ); ?>"><?php esc_html_e( 'All extensions', 'quiz-master-next' ); ?></a>
				<h1 class="qsm-ui-h1">
					<?php
					/* translators: %s: add-on name */
					echo esc_html( sprintf( __( 'Manage %s Addon', 'quiz-master-next' ), $addon_title ) );
					?>
				</h1>
				<p class="qsm-ui-subtitle"><?php esc_html_e( 'Settings and license for this add-on.', 'quiz-master-next' ); ?></p>
			<?php } else { ?>
				<h1 class="qsm-ui-h1"><?php esc_html_e( 'Extensions', 'quiz-master-next' ); ?></h1>
				<p class="qsm-ui-subtitle"><?php esc_html_e( 'Add features to QSM, and open the settings of the add-ons you have installed.', 'quiz-master-next' ); ?></p>
			<?php } ?>
		</div>
		<?php if ( '' === $addon_title ) { ?>
			<div class="qsm-ui-page-actions">
				<?php
				echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
					__( 'Get a free add-on', 'quiz-master-next' ),
					array(
						'size' => 'sm',
						'icon' => 'star',
						'href' => admin_url( 'admin.php?page=qsm-free-addon' ),
					)
				);
				?>
			</div>
		<?php } ?>
	</div>
	<hr class="wp-header-end">
	<h2 class="nav-tab-wrapper" style="display: none;">
		<?php
		foreach ( $tab_array as $tab ) {
				$active_class = '';
			if ( $active_tab === $tab['slug'] ) {
				$active_class = ' nav-tab-active';
			}
			echo '<a href="?page=qmn_addons&tab=' . esc_attr( $tab['slug'] ) . '" class="nav-tab' . esc_attr( $active_class ) . '">' . esc_html( $tab['title'] ) . '</a>';
        }
		?>
	</h2>
	<?php // An add-on's own settings and license screens render here and keep their own UI. ?>
	<div class="qsm-ui-addons-content">
		<?php
		foreach ( $tab_array as $tab ) {
			if ( $active_tab === $tab['slug'] ) {
				call_user_func( $tab['function'] );
			}
		}
		?>
	</div>
</div>
<?php
}

/**
 * Displays the contents of the featured add ons page.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_generate_featured_addons() {
	global $mlwQuizMasterNext;
	wp_localize_script( 'qsm_admin_js', 'qsmAdminObject', array( 'saveNonce' => wp_create_nonce( 'ajax-nonce-sendy-save' ) ) );
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_addon_tabs();
	$view_details = __( 'View Details', 'quiz-master-next' );
	$all_addons = qsm_get_widget_data( 'all_addons' );

	?>
	<?php
	$installed = array();
	foreach ( (array) $tab_array as $tab ) {
		if ( 'Featured Addons' !== trim( $tab['title'] ) ) {
			$installed[] = $tab;
		}
	}
	// qsm-admin.js switches the two panels on [data-section] inside .custom-addon-upper.
	?>
	<div class="custom-addon-upper qsm-ui-addons-switch">
		<div class="qsm-ui-tabs qsm-ui-tabs-pill" role="tablist" aria-label="<?php esc_attr_e( 'Add-ons', 'quiz-master-next' ); ?>">
			<a href="javascript:void(0)" role="tab" class="qsm-ui-tab current" aria-selected="true" aria-controls="qsm_add_addons" data-section="all"><?php esc_html_e( 'All Addons', 'quiz-master-next' ); ?> <span id="qsm_total_count_text" class="qsm-ui-count count"><?php echo esc_html( max( 0, count( (array) $all_addons ) - 6 ) ); ?></span></a>
			<a href="javascript:void(0)" role="tab" class="qsm-ui-tab" aria-selected="false" aria-controls="qsm_installed_addons" data-section="install"><?php esc_html_e( 'Installed', 'quiz-master-next' ); ?> <span class="qsm-ui-count count"><?php echo esc_html( count( $installed ) ); ?></span></a>
		</div>
	</div>
	<div id="qsm_installed_addons" class="qsm-active-addons qsm-primary-acnhor" role="tabpanel" style="display: none;">
		<?php
		echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'title'       => __( 'Installed Addons', 'quiz-master-next' ),
				'description' => __( 'Open an add-on to change its settings or license.', 'quiz-master-next' ),
				'class'       => 'qsm-ui-addons-installed',
			)
		);
		if ( $installed ) {
			?>
			<ul class="installed_addons_wrapper qsm-ui-addons-list">
				<?php foreach ( $installed as $tab ) { ?>
					<li class="installed_addon">
						<span class="installed_addon_name"><?php echo wp_kses_post( $tab['title'] ); ?></span>
						<span class="installed_addon_link">
							<?php
							echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
								__( 'Settings', 'quiz-master-next' ),
								array(
									'size'  => 'sm',
									'icon'  => 'settings',
									'href'  => admin_url( 'admin.php?page=qmn_addons&tab=' . $tab['slug'] ),
									'attrs' => array(
										'target' => '_blank',
										'rel'    => 'noopener',
									),
								)
							);
							?>
						</span>
					</li>
				<?php } ?>
			</ul>
			<?php
		} else {
			echo QSM_Admin_UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				array(
					'icon'    => 'bolt',
					'title'   => __( 'No add-ons installed yet', 'quiz-master-next' ),
					'text'    => __( 'You have currently not installed any addons. Explore our addons repository with 40+ addons to make your quiz even better.', 'quiz-master-next' ),
					'actions' => QSM_Admin_UI::button(
						__( 'Explore Addons', 'quiz-master-next' ),
						array(
							'variant' => 'primary',
							'size'    => 'sm',
							'class'   => 'custom-explore-addon',
						)
					),
				)
			);
		}
		echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		?>
	</div>
	<div id="qsm_add_addons" class="qsm-primary-acnhor qsm-ui-addons-browse" role="tabpanel">
		<div class="qsm-ui-addons-grid" id="qsm_popular_addons">
			<?php
			if ( $all_addons ) {
				foreach ( $all_addons as $single_arr ) {
					if ( ! empty( $single_arr['tags'] ) && in_array( 831, array_column( $single_arr['tags'], 'term_id' ), true ) || in_array( $single_arr['id'], array( 557086, 551029, 551027, 547794, 302299, 302297, 300658, 300513 ), true ) ) {
						continue;
					}
					$addon_link = qsm_get_utm_link( $single_arr['link'], 'addon_setting', 'popular_addon', 'addon-settings_' . sanitize_title( $single_arr['name'] ) );
					?>
					<div class="qsm-ui-card qsm-ui-addon-card">
						<a class="qsm-ui-addon-card-image" href="<?php echo esc_url( $addon_link ); ?>" target="_blank" rel="noopener" tabindex="-1" aria-hidden="true">
							<img alt="" src="<?php echo esc_url( $single_arr['img'] ); ?>" loading="lazy">
						</a>
						<div class="qsm-ui-card-body qsm-ui-text">
							<h3 class="qsm-ui-sr"><?php echo esc_html( $single_arr['name'] ); ?></h3>
							<p><?php echo esc_html( $single_arr['description'] ); ?></p>
						</div>
						<div class="qsm-ui-card-foot">
							<?php
							echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
								$view_details,
								array(
									'size'  => 'sm',
									'icon'  => 'ext',
									'href'  => $addon_link,
									'class' => 'custom-addon-link',
									'attrs' => array(
										'target'     => '_blank',
										'rel'        => 'noopener',
										/* translators: %s: add-on name */
										'aria-label' => sprintf( __( 'View details: %s', 'quiz-master-next' ), $single_arr['name'] ),
									),
								)
							);
							?>
						</div>
					</div>
					<?php
				}
			}
			?>
		</div>
		<?php
		$bundles = qsm_get_widget_data( 'bundles' );
		if ( $bundles ) {
			?>
			<aside class="qsm-ui-bundles" aria-labelledby="qsm-ui-bundles-title">
				<h2 class="qsm-ui-h3 qsm-ui-bundles-title" id="qsm-ui-bundles-title"><?php echo QSM_Admin_UI::icon( 'star', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?><?php esc_html_e( 'Save with our bundles', 'quiz-master-next' ); ?></h2>
				<?php
				// $count keeps each bundle's icon colour (qsm-bundles-dynamic-N).
				$count = 0;
				foreach ( $bundles as $bundles_arr ) {
					$bundle_link = qsm_get_utm_link( 'https://quizandsurveymaster.com/pricing/', 'addon_setting', 'bundles', 'addon-settings_' . sanitize_title( $bundles_arr['name'] ) );
					?>
					<div class="qsm-ui-card qsm-ui-bundle-card">
						<div class="qsm-ui-card-body qsm-ui-text">
							<div class="qsm-ui-bundle-head">
								<span class="qsm-ui-bundle-icon qsm-bundles-icon qsm-bundles-dynamic-<?php echo esc_attr( $count ); ?>">
									<?php if ( ! empty( $bundles_arr['icon'] ) ) { ?>
										<img alt="" src="<?php echo esc_url( $bundles_arr['icon'] ); ?>">
									<?php } ?>
								</span>
								<h3 class="qsm-ui-h3"><?php echo wp_kses_post( $bundles_arr['name'] ); ?></h3>
							</div>
							<p><?php echo wp_kses_post( $bundles_arr['desc'] ); ?></p>
						</div>
						<div class="qsm-ui-card-foot">
							<?php
							echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
								__( 'Get now', 'quiz-master-next' ),
								array(
									'variant' => 'primary',
									'size'    => 'sm',
									'href'    => $bundle_link,
									'class'   => 'custom-addon-getnow',
									'attrs'   => array(
										'target'     => '_blank',
										'rel'        => 'noopener',
										/* translators: %s: bundle name */
										'aria-label' => sprintf( __( 'Get now: %s', 'quiz-master-next' ), wp_strip_all_tags( $bundles_arr['name'] ) ),
									),
								)
							);
							?>
						</div>
					</div>
					<?php
					++$count;
				}
				?>
			</aside>
		<?php } ?>
	</div>
<?php
}

/**
 * This function registers the feature add ons tab.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_featured_addons_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_addon_settings_tab( __( 'Featured Addons', 'quiz-master-next' ), 'qsm_generate_featured_addons' );
}

add_action( 'init', 'qsm_featured_addons_tab' );

/**
 * @version 3.2.0
 * Display get a free addon page
 */
function qsm_display_optin_page() {
	global $mlwQuizMasterNext;
	?>
<div class="wrap qsm-free-addon-wrap qsm-ui qsm-ui-free-addon-page">
	<div class="qsm-ui-page-header qsm-ui-text">
		<div>
			<h1 class="qsm-ui-h1"><?php esc_html_e( 'Get Your Free Extension!', 'quiz-master-next' ); ?></h1>
			<p class="qsm-ui-subtitle"><?php esc_html_e( 'Subscribe to the QSM newsletter and get one add-on free.', 'quiz-master-next' ); ?></p>
		</div>
	</div>
	<hr class="wp-header-end">

	<div class="qsm-free-addon-text qsm-ui-card qsm-ui-free-addon-intro"><div class="qsm-ui-card-body qsm-ui-text">
		<p><?php esc_html_e( 'Wanna get more out of Quiz and Survey Master, but not yet ready to spend the cash? Get one free addon today!', 'quiz-master-next' ); ?></p>
		<p>
		<?php
		esc_html_e(
			'Getting your addon is dead simple: just subscribe to our newsletter and then you will get the free addon by e-mail. We will not spam you. We usually send out newsletters to talk about new features in ',
			'quiz-master-next'
		);
		?>
		<b><?php esc_html_e( 'Quiz and Survey Master', 'quiz-master-next' ); ?></b>,
		<?php
		esc_html_e(
			' let you know when new or updated addons are being released and provide informative articles that show you how to use ',
			'quiz-master-next'
		);
		?>
		<b><?php esc_html_e( 'Quiz and Survey Master', 'quiz-master-next' ); ?></b>
		<?php esc_html_e( ' to its full potential. ', 'quiz-master-next' ); ?>
		<a href="<?php echo esc_url( qsm_get_plugin_link( 'privacy-policy', 'qsm-addons-page' ) ); ?>" target="_blank">
			<?php esc_html_e( 'View our privacy policy', 'quiz-master-next' ); ?>
		</a>
	</p>
	</div></div>

	<div class="changelog">


		<div id="wpas-mailchimp-signup-form-wrapper">
			<div id="status"></div>
			<!-- Begin Sendinblue Form -->

			<!-- START - We recommend to place the below code where you want the form in your website html  -->
			<div class="sib-form qsm-ui-sib" style="text-align: center;">
				<div id="sib-form-container" class="sib-form-container">
					<div id="error-message" class="sib-form-message-panel qsm-ui-sib-msg is-error"
						style="font-size:16px; text-align:left; font-family:Helvetica, sans-serif; color:#661d1d; background-color:#ffeded; border-radius:3px; border-width:px; border-color:#ff4949;max-width:540px;">
						<div class="sib-form-message-panel__text sib-form-message-panel__text--center">
							<svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
								<path
									d="M256 40c118.621 0 216 96.075 216 216 0 119.291-96.61 216-216 216-119.244 0-216-96.562-216-216 0-119.203 96.602-216 216-216m0-32C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm-11.49 120h22.979c6.823 0 12.274 5.682 11.99 12.5l-7 168c-.268 6.428-5.556 11.5-11.99 11.5h-8.979c-6.433 0-11.722-5.073-11.99-11.5l-7-168c-.283-6.818 5.167-12.5 11.99-12.5zM256 340c-15.464 0-28 12.536-28 28s12.536 28 28 28 28-12.536 28-28-12.536-28-28-28z" />
							</svg>
							<span class="sib-form-message-panel__inner-text">
								<?php esc_html_e( 'Your subscription could not be saved. Please try again.', 'quiz-master-next' ); ?>
							</span>
						</div>
					</div>
					<div></div>
					<div id="success-message" class="sib-form-message-panel qsm-ui-sib-msg is-success"
						style="font-size:16px; text-align:left; font-family:Helvetica, sans-serif; color:#085229; background-color:#e7faf0; border-radius:3px; border-width:px; border-color:#13ce66;max-width:540px;">
						<div class="sib-form-message-panel__text sib-form-message-panel__text--center">
							<svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
								<path
									d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 464c-118.664 0-216-96.055-216-216 0-118.663 96.055-216 216-216 118.664 0 216 96.055 216 216 0 118.663-96.055 216-216 216zm141.63-274.961L217.15 376.071c-4.705 4.667-12.303 4.637-16.97-.068l-85.878-86.572c-4.667-4.705-4.637-12.303.068-16.97l8.52-8.451c4.705-4.667 12.303-4.637 16.97.068l68.976 69.533 163.441-162.13c4.705-4.667 12.303-4.637 16.97.068l8.451 8.52c4.668 4.705 4.637 12.303-.068 16.97z" />
							</svg>
							<span class="sib-form-message-panel__inner-text">
								<?php esc_html_e( 'Your subscription has been successful.', 'quiz-master-next' ); ?>
							</span>
						</div>
					</div>
					<div></div>
					<div id="sib-container" class="sib-container--large sib-container--vertical qsm-ui-sib-box"
						style="text-align:center; background-color:rgba(255,255,255,1); max-width:540px; border-radius:3px; border-width:1px; border-color:#C0CCD9; border-style:solid;">
						<form id="sib-form" method="POST"
							action="https://cddf18fd.sibforms.com/serve/MUIEAO9t8eOB2GOqY73EWqFatPi328RiosfYMKieZ_8IxVL2jyEazmQ9LlkDj6pYrTlvB7JBsx3su8WdK5A4l445X0P-0r0Qf82LWXLSFa3yK0YZuypiIxy8hZfBXClZMANBeEVpBkswLw0RxDt2uWrN7B7zHTFXWY0W4mftpWo3Nqen7SQW1L9DYnXrex6lyw5EfHvZ3ZwsU6Xp"
							data-type="subscription">
							<div style="padding: 16px 0;">
								<div class="sib-input sib-form-block">
									<div class="form__entry entry_block">
										<div class="form__label-row ">
											<label class="entry__label qsm-ui-sib-label"
												style="font-size:16px; text-align:left; font-weight:700; font-family:Helvetica, sans-serif; color:#3c4858; border-width:px;"
												for="EMAIL" data-required="*">
												<?php esc_html_e( 'Enter your email address to subscribe', 'quiz-master-next' ); ?>
											</label>

											<div class="entry__field">
												<input class="input qsm-ui-sib-input" type="text" id="EMAIL" name="EMAIL"
													autocomplete="off" placeholder="EMAIL" data-required="true"
													required />
											</div>
										</div>

										<label class="entry__error entry__error--primary"
											style="font-size:16px; text-align:left; font-family:Helvetica, sans-serif; color:#661d1d; background-color:#ffeded; border-radius:3px; border-width:px; border-color:#ff4949;">
										</label>
										<label class="entry__specification qsm-ui-sib-help"
											style="font-size:12px; text-align:left; font-family:Helvetica, sans-serif; color:#8390A4; border-width:px;">
											<?php esc_html_e( 'Provide your email address to subscribe. For e.g abc@xyz.com', 'quiz-master-next' ); ?>
										</label>
									</div>
								</div>
							</div>
							<div style="padding: 16px 0;">
								<div class="sib-form-block" style="text-align: left">
									<button class="sib-form-block__button sib-form-block__button-with-loader qsm-ui-sib-button"
										style="font-size:16px; text-align:left; font-weight:700; font-family:Helvetica, sans-serif; color:#FFFFFF; background-color:#3E4857; border-radius:3px; border-width:0px;"
										form="sib-form" type="submit">
										<svg class="icon clickable__icon progress-indicator__icon sib-hide-loader-icon"
											viewBox="0 0 512 512">
											<path
												d="M460.116 373.846l-20.823-12.022c-5.541-3.199-7.54-10.159-4.663-15.874 30.137-59.886 28.343-131.652-5.386-189.946-33.641-58.394-94.896-95.833-161.827-99.676C261.028 55.961 256 50.751 256 44.352V20.309c0-6.904 5.808-12.337 12.703-11.982 83.556 4.306 160.163 50.864 202.11 123.677 42.063 72.696 44.079 162.316 6.031 236.832-3.14 6.148-10.75 8.461-16.728 5.01z" />
										</svg>
										<?php esc_html_e( 'SUBSCRIBE', 'quiz-master-next' ); ?>
									</button>
								</div>
							</div>
							<div style="padding: 16px 0;">
								<div class="sib-form-block"
									style="font-size:14px; text-align:center; font-family:Helvetica, sans-serif; color:#333; background-color:transparent; border-width:px;">
									<div class="sib-text-form-block">
										<p>
											<a class="qsm-ui-sib-link" href="https://quizandsurveymaster.com/add-ons-terms-and-conditions/" rel="noopener" target="_blank"><?php esc_html_e( 'Terms & Privacy policy', 'quiz-master-next' ); ?></a>
										</p>
									</div>
								</div>
							</div>

							<input type="text" name="email_address_check" value="" class="input--hidden">
							<input type="hidden" name="locale" value="en">
						</form>
					</div>
				</div>
			</div>
			<!-- END - We recommend to place the below code where you want the form in your website html  -->
			<!-- End Sendinblue Form -->
		</div>
	</div>

</div>
<?php
}
function qsm_admin_get_free_addon_page_scripts_style( $hook ) {
	global $mlwQuizMasterNext;
	if ( 'qsm_page_qsm-free-addon' === $hook ) {

		wp_enqueue_style( 'qsm_sendinblue_component_form',  QSM_PLUGIN_CSS_URL.'/sendinblue-component.css', array(), $mlwQuizMasterNext->version);
		wp_enqueue_style( 'qsm_sendinblue_component_clickable',  QSM_PLUGIN_CSS_URL.'/sendinblue-component-clickable.css', array(), $mlwQuizMasterNext->version);
		wp_enqueue_style( 'qsm_sendinblue_progress_indicator',  QSM_PLUGIN_CSS_URL.'/sendinblue-progress-indicator.css', array(), $mlwQuizMasterNext->version);
		wp_enqueue_style( 'qsm_sibforms_css',  QSM_PLUGIN_CSS_URL.'/sib-styles.css', array(), $mlwQuizMasterNext->version );
		wp_enqueue_script( 'qsm_sibforms_js',  QSM_PLUGIN_JS_URL.'/sibforms-main.js', array(), $mlwQuizMasterNext->version,true);
		$google_recaptcha_js_url = 'https://www.google.com/recaptcha/api.js?hl=en';
		wp_enqueue_script( 'qsm_google_recaptcha',  $google_recaptcha_js_url, array(), $mlwQuizMasterNext->version, true);

		$qsm_sibforms_inline_js = 'window.REQUIRED_CODE_ERROR_MESSAGE = "Please choose a country code";

		window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE =
			"The information provided is invalid. Please review the field format and try again.";

		window.REQUIRED_ERROR_MESSAGE = "This field cannot be left blank. ";

		window.GENERIC_INVALID_MESSAGE =
			"The information provided is invalid. Please review the field format and try again.";




		window.translation = {
			common: {
				selectedList: "{quantity} list selected",
				selectedLists: "{quantity} lists selected"
			}
		};

		var AUTOHIDE = Boolean(0);';
		wp_add_inline_script('jquery', $qsm_sibforms_inline_js);

		$qsm_sibforms_inline_css = '@font-face {
			font-display: block;
			font-family: Roboto;
			src: url(https://assets.sendinblue.com/font/Roboto/Latin/normal/normal/7529907e9eaf8ebb5220c5f9850e3811.woff2) format("woff2"), url(https://assets.sendinblue.com/font/Roboto/Latin/normal/normal/25c678feafdc175a70922a116c9be3e7.woff) format("woff")
		}

		@font-face {
			font-display: fallback;
			font-family: Roboto;
			font-weight: 600;
			src: url(https://assets.sendinblue.com/font/Roboto/Latin/medium/normal/6e9caeeafb1f3491be3e32744bc30440.woff2) format("woff2"), url(https://assets.sendinblue.com/font/Roboto/Latin/medium/normal/71501f0d8d5aa95960f6475d5487d4c2.woff) format("woff")
		}

		@font-face {
			font-display: fallback;
			font-family: Roboto;
			font-weight: 700;
			src: url(https://assets.sendinblue.com/font/Roboto/Latin/bold/normal/3ef7cf158f310cf752d5ad08cd0e7e60.woff2) format("woff2"), url(https://assets.sendinblue.com/font/Roboto/Latin/bold/normal/ece3a1d82f18b60bcce0211725c476aa.woff) format("woff")
		}

		#sib-container input:-ms-input-placeholder {
			text-align: left;
			font-family: "Helvetica", sans-serif;
			color: #c0ccda;
			border-width: px;
		}

		#sib-container input::placeholder {
			text-align: left;
			font-family: "Helvetica", sans-serif;
			color: #c0ccda;
			border-width: px;
		}';
		wp_add_inline_style( 'qsm_sibforms_css', $qsm_sibforms_inline_css );

	}
}
add_action( 'admin_enqueue_scripts', 'qsm_admin_get_free_addon_page_scripts_style', 20 );

?>