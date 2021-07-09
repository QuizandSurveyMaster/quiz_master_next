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
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}

	global $mlwQuizMasterNext;
	$active_tab = strtolower( str_replace( ' ', '-', isset( $_GET['tab'] ) ? $_GET['tab'] : __( 'Featured Addons', 'quiz-master-next' ) ) );
	$tab_array  = $mlwQuizMasterNext->pluginHelper->get_addon_tabs();
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
	wp_style_add_data( 'qsm_admin_style', 'rtl', 'replace' );
	?>
<div class="wrap qsm-addon-setting-wrap">
	<h2 style="margin-bottom: 20px;">
		<?php _e( 'QSM Addon Settings', 'quiz-master-next' ); ?>
		<?php
		if ( isset( $_GET['tab'] ) && $_GET['tab'] != '' ) {
			?>
		<a class="button button-default" href="?page=qmn_addons"><span style="margin-top: 4px;"
				class="dashicons dashicons-arrow-left-alt"></span>
			<?php _e( 'Back to list', 'quiz-master-next' ); ?></a>
		<?php
		}
		?>
	</h2>
	<h2 class="nav-tab-wrapper" style="display: none;">
		<?php
		foreach ( $tab_array as $tab ) {
				$active_class = '';
			if ( $active_tab == $tab['slug'] ) {
				$active_class = 'nav-tab-active';
			}
			echo "<a href=\"?page=qmn_addons&tab={$tab['slug']}\" class=\"nav-tab $active_class\">{$tab['title']}</a>";
		}
		?>
	</h2>
	<div>
		<?php
		foreach ( $tab_array as $tab ) {
			if ( $active_tab == $tab['slug'] ) {
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
	wp_enqueue_script( 'qsm_admin_script', plugins_url( '../../js/admin.js', __FILE__ ), array( 'jquery' ), $mlwQuizMasterNext->version );
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_addon_tabs();
	?>
<div class="qsm-addon-browse-addons">
	<div class="qsm-addon-anchor-left">
		<div class="qsm-install-addon">
			<a class="active" href="#qsm_installed_addons"><?php _e( 'Installed Addons', 'quiz-master-next' ); ?></a>
			<a href="#qsm_add_addons"><?php _e( 'Add Addons', 'quiz-master-next' ); ?> <span
					class="dashicons dashicons-arrow-right-alt2"></span></a>
		</div>
		<div class="qsm-add-addon" style="display: none;">
			<a class="active" href="#qsm_popular_addons"><?php _e( 'Popular', 'quiz-master-next' ); ?></a>
			<a href="#qsm_onsale_addons"><?php _e( 'On Sale', 'quiz-master-next' ); ?></a>
			<a href="#qsm_new_addons"><?php _e( 'Recently Updated', 'quiz-master-next' ); ?></a>
		</div>
	</div>
	<div class="qsm-addon-list-right">
		<span><?php _e( '40+ addons available', 'quiz-master-next' ); ?></span>
		<a rel="noopener" style="text-decoration: none; font-size: 15px;"
			href="http://quizandsurveymaster.com/addons/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
			target="_blank"><?php _e( 'Browse All Addons', 'quiz-master-next' ); ?></a>
	</div>
</div>
<div id="qsm_installed_addons" class="qsm-active-addons qsm-primary-acnhor">
	<h2 class="installed_title"><?php _e( 'Installed Addons', 'quiz-master-next' ); ?></h2>
	<?php
	if ( $tab_array && count( $tab_array ) > 1 ) {
		?>
	<div class="installed_addons_wrapper">
		<?php
		foreach ( $tab_array as $tab ) {
			if ( trim( $tab['title'] ) == 'Featured Addons' ) {
							continue;
			}
			?>
		<div class="installed_addon">
			<span class="installed_addon_name"><?php echo $tab['title']; ?></span>
			<span class="installed_addon_link">
				<a class="button button-default" href="?page=qmn_addons&tab=<?php echo $tab['slug']; ?>"><span
						class="dashicons dashicons-admin-generic"></span>
					<?php _e( 'Settings', 'quiz-master-next' ); ?></a>
			</span>
		</div>
		<?php } ?>
	</div>
	<?php
	} else {
		?>
	<div class="no_addons_installed">
		<div>
			<?php
			_e( 'You have currently not installed any addons. Explore our addons repository with 40+ addons to make your quiz even better.', 'quiz-master-next' );
		?>
		</div>
		<a class="button button-primary button-hero load-quiz-wizard hide-if-no-customize"
			href="#qsm_add_addons"><?php _e('Explore Addons', 'quiz-master-next');?></a>
	</div>
	<?php
	}
	?>
</div>
<div id="qsm_add_addons" class="qsm-primary-acnhor" style="display: none;">
	<div class="qsm-quiz-page-addon qsm-addon-page-list">
		<?php
		$popular_addons = qsm_get_widget_data( 'popular_products' );
		if ( empty( $popular_addons ) ) {
			$qsm_admin_dd   = qsm_fetch_data_from_script();
			$popular_addons = isset( $qsm_admin_dd['popular_products'] ) ? $qsm_admin_dd['popular_products'] : array();
		}
		?>
		<div class="qsm_popular_addons" id="qsm_popular_addons">
			<div class="popuar-addon-ul">
				<?php
				if ( $popular_addons ) {
					foreach ( $popular_addons as $key => $single_arr ) {
						?>
				<div>
					<div class="addon-itd-wrap">
						<div class="addon-image" style="background-image: url('<?php echo $single_arr['img']; ?>')">
						</div>
						<div class="addon-title-descption">
							<a class="addon-title" href="<?php echo $single_arr['link']; ?>" target="_blank">
								<?php echo $single_arr['name']; ?>
							</a>
							<span class="description">
								<?php echo wp_trim_words( $single_arr['description'], 8 ); ?>
							</span>
							<?php
							if ( str_word_count( $single_arr['description'] ) > 9 ) {
								echo '<a class="read-more" href="' . $single_arr['link'] . '">' . __( 'Show more', 'quiz-master-next' ) . '</a>';
							}
							?>
						</div>
					</div>
					<div class="addon-price">
						<button
							class="button button-primary addon-price-btn"><?php _e( 'Price: ', 'quiz-master-next' ); ?>$<?php echo array_values( $single_arr['price'] )[0]; ?></button>
						<a class="button button-primary addon-get-link" rel="noopener"
							href="<?php echo $single_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
							target="_blank"><?php _e( 'Get This Addon', 'quiz-master-next' ); ?> <span
								class="dashicons dashicons-arrow-right-alt2"></span></a>
					</div>
				</div>
				<?php
					}
				}
				?>
			</div>
		</div>
		<div class="qsm_popular_addons" id="qsm_onsale_addons" style="display: none;">
			<?php
			$qsm_onsale_addons = qsm_get_widget_data( 'on_sale_products' );
			if ( empty( $qsm_onsale_addons ) ) {
				$qsm_admin_dd      = qsm_fetch_data_from_script();
				$qsm_onsale_addons = isset( $qsm_admin_dd['on_sale_products'] ) ? $qsm_admin_dd['on_sale_products'] : array();
			}
			?>
			<div class="popuar-addon-ul">
				<?php
				if ( $qsm_onsale_addons ) {
					foreach ( $qsm_onsale_addons as $key => $single_arr ) {
						?>
				<div>
					<div class="addon-itd-wrap">
						<div class="addon-image" style="background-image: url('<?php echo $single_arr['img']; ?>')">
						</div>
						<div class="addon-title-descption">
							<a class="addon-title" href="<?php echo $single_arr['link']; ?>" target="_blank">
								<?php echo $single_arr['name']; ?>
							</a>
							<span class="description">
								<?php echo wp_trim_words( $single_arr['description'], 8 ); ?>
							</span>
							<?php
							if ( str_word_count( $single_arr['description'] ) > 9 ) {
								echo '<a class="read-more" href="' . $single_arr['link'] . '">' . __( 'Show more', 'quiz-master-next' ) . '</a>';
							}
							?>
						</div>
					</div>
					<div class="addon-price">
						<button
							class="button button-primary addon-price-btn"><?php _e( 'Price: ', 'quiz-master-next' ); ?>$<?php echo array_values( $single_arr['price'] )[0]; ?></button>
						<a class="button button-primary addon-get-link" rel="noopener"
							href="<?php echo $single_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
							target="_blank"><?php _e( 'Get This Addon', 'quiz-master-next' ); ?> <span
								class="dashicons dashicons-arrow-right-alt2"></span></a>
					</div>
				</div>
				<?php
					}
				}
				?>
			</div>
		</div>
		<div class="qsm_popular_addons" id="qsm_new_addons" style="display: none;">
			<?php
			$new_addons = qsm_get_widget_data( 'new_addons' );
			if ( empty( $popular_addons ) ) {
				$qsm_admin_dd = qsm_fetch_data_from_script();
				$new_addons   = isset( $qsm_admin_dd['new_addons'] ) ? $qsm_admin_dd['new_addons'] : array();
			}
			?>
			<div class="popuar-addon-ul">
				<?php
				if ( $new_addons ) {
					foreach ( $new_addons as $key => $single_arr ) {
						if ( trim( $single_arr['name'] ) == 'Starter Bundle' || trim( $single_arr['name'] ) == 'Premium Bundle' ) {
							continue;
						}
						?>
				<div>
					<div class="addon-itd-wrap">
						<div class="addon-image" style="background-image: url('<?php echo $single_arr['img']; ?>')">
						</div>
						<div class="addon-title-descption">
							<a class="addon-title" href="<?php echo $single_arr['link']; ?>" target="_blank">
								<?php echo $single_arr['name']; ?>
							</a>
							<span class="description">
								<?php echo wp_trim_words( $single_arr['description'], 8 ); ?>
							</span>
							<?php
							if ( str_word_count( $single_arr['description'] ) > 9 ) {
								echo '<a class="read-more" href="' . $single_arr['link'] . '">' . __( 'Show more', 'quiz-master-next' ) . '</a>';
							}
							?>
						</div>
					</div>
					<div class="addon-price">
						<button
							class="button button-primary addon-price-btn"><?php _e( 'Price: ', 'quiz-master-next' ); ?>$<?php echo array_values( $single_arr['price'] )[0]; ?></button>
						<a class="button button-primary addon-get-link" rel="noopener"
							href="<?php echo $single_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
							target="_blank"><?php _e( 'Get This Addon', 'quiz-master-next' ); ?> <span
								class="dashicons dashicons-arrow-right-alt2"></span></a>
					</div>
				</div>
				<?php
					}
				}
				?>
			</div>
		</div>
	</div>
	<div class="qsm-addon-news-ads">
		<?php
		$bundles = qsm_get_widget_data( 'bundles' );
		if ( empty( $bundles ) ) {
			$qsm_admin_dd = qsm_fetch_data_from_script();
			$bundles      = isset( $qsm_admin_dd['bundles'] ) ? $qsm_admin_dd['bundles'] : array();
		}
		?>
		<?php
		if ( $bundles ) {
			?>
		<h3 class="qsm-news-ads-title"><?php _e( 'SAVE WITH OUR BUNDLES', 'quiz-master-next' ); ?></h3>
		<?php
			foreach ( $bundles as $key => $bundles_arr ) {
				?>
		<div class="qsm-info-widget">
			<div class="bundle-icon">
				<?php
				if ( ! empty( $bundles_arr['icon'] ) ) {
					echo '<img src="' . $bundles_arr['icon'] . '" />';
				}
				?>
			</div>
			<h3><?php echo $bundles_arr['name']; ?></h3>
			<p><?php echo $bundles_arr['desc']; ?></p>
			<a href="<?php echo $bundles_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
				target="_blank" class="button button-primary addon-bundle-btn" rel="noopener">
				<?php echo _e( 'Get now', 'quiz-master-next' ); ?>
				$<?php echo array_values( $bundles_arr['price'] )[0]; ?>
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</a>
		</div>
		<?php
			}
		}
		?>
	</div>
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

add_action( 'plugins_loaded', 'qsm_featured_addons_tab' );

/**
 * @version 3.2.0
 * Display get a free addon page
 */
function qsm_display_optin_page() {
	 global $mlwQuizMasterNext;
	wp_enqueue_script( 'qsm_admin_script', plugins_url( '../../js/admin.js', __FILE__ ), array( 'jquery' ), $mlwQuizMasterNext->version );
	wp_localize_script( 'qsm_admin_script', 'qsmAdminObject', array( 'saveNonce' => wp_create_nonce( 'ajax-nonce-sendy-save' ) ) );
	?>
<div class="wrap about-wrap">

	<h1><?php esc_html_e( 'Get Your Free Addon!', 'quiz-master-next' ); ?></h1>

	<div class="about-text">
		<?php esc_html_e( 'Wanna get more out of Quiz and Survey Master, but not yet ready to spend the cash? Get one free addon today!', 'quiz-master-next' ); ?>
	</div>

	<div class="changelog">

		<div class="row">
			<!-- <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<div class="about-body">
						<img src="" alt="Improved Custom Fields">
					</div>
				</div> -->
		</div>

		<p><?php echo __( 'Getting your addon is dead simple: just subscribe to our newsletter and then you will get the free addon by e-mail. We will not spam you. We usually send out newsletters to talk about new features in ', 'quiz-master-next' ) . '<b>' . __( 'Quiz and Survey Master', 'quiz-master-next' ) . '</b>,' . __( ' let you know when new or updated addons are being released and provide informative articles that show you how to use ', 'quiz-master-next' ) . '<b>' . __( 'Quiz and Survey Master ', 'quiz-master-next' ) . '</b>' . __( 'to its full potential. ', 'quiz-master-next' ) . '<a href="https://quizandsurveymaster.com/privacy-policy/" target="_blank">' . __( 'View our privacy policy', 'quiz-master-next' ) . '</a>'; ?>
		</p>

		<div id="wpas-mailchimp-signup-form-wrapper">
			<div id="status"></div>
			<!-- Begin Sendinblue Form -->
			<!-- START - We recommend to place the below code in head tag of your website html  -->
			<style>
			@font-face {
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
			}
			</style>
			<link rel="stylesheet" href="https://assets.sendinblue.com/component/form/2ef8d8058c0694a305b0.css">
			<link rel="stylesheet" href="https://assets.sendinblue.com/component/clickable/b056d6397f4ba3108595.css">
			<link rel="stylesheet"
				href="https://assets.sendinblue.com/component/progress-indicator/f86d65a4a9331c5e2851.css">
			<link rel="stylesheet" href="https://sibforms.com/forms/end-form/build/sib-styles.css">
			<!--  END - We recommend to place the above code in head tag of your website html -->

			<!-- START - We recommend to place the below code where you want the form in your website html  -->
			<div class="sib-form" style="text-align: center;">
				<div id="sib-form-container" class="sib-form-container">
					<div id="error-message" class="sib-form-message-panel"
						style="font-size:16px; text-align:left; font-family:Helvetica, sans-serif; color:#661d1d; background-color:#ffeded; border-radius:3px; border-width:px; border-color:#ff4949;max-width:540px;">
						<div class="sib-form-message-panel__text sib-form-message-panel__text--center">
							<svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
								<path
									d="M256 40c118.621 0 216 96.075 216 216 0 119.291-96.61 216-216 216-119.244 0-216-96.562-216-216 0-119.203 96.602-216 216-216m0-32C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm-11.49 120h22.979c6.823 0 12.274 5.682 11.99 12.5l-7 168c-.268 6.428-5.556 11.5-11.99 11.5h-8.979c-6.433 0-11.722-5.073-11.99-11.5l-7-168c-.283-6.818 5.167-12.5 11.99-12.5zM256 340c-15.464 0-28 12.536-28 28s12.536 28 28 28 28-12.536 28-28-12.536-28-28-28z" />
							</svg>
							<span class="sib-form-message-panel__inner-text">
								<?php _e( 'Your subscription could not be saved. Please try again.', 'quiz-master-next' ); ?>
							</span>
						</div>
					</div>
					<div></div>
					<div id="success-message" class="sib-form-message-panel"
						style="font-size:16px; text-align:left; font-family:Helvetica, sans-serif; color:#085229; background-color:#e7faf0; border-radius:3px; border-width:px; border-color:#13ce66;max-width:540px;">
						<div class="sib-form-message-panel__text sib-form-message-panel__text--center">
							<svg viewBox="0 0 512 512" class="sib-icon sib-notification__icon">
								<path
									d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 464c-118.664 0-216-96.055-216-216 0-118.663 96.055-216 216-216 118.664 0 216 96.055 216 216 0 118.663-96.055 216-216 216zm141.63-274.961L217.15 376.071c-4.705 4.667-12.303 4.637-16.97-.068l-85.878-86.572c-4.667-4.705-4.637-12.303.068-16.97l8.52-8.451c4.705-4.667 12.303-4.637 16.97.068l68.976 69.533 163.441-162.13c4.705-4.667 12.303-4.637 16.97.068l8.451 8.52c4.668 4.705 4.637 12.303-.068 16.97z" />
							</svg>
							<span class="sib-form-message-panel__inner-text">
								<?php _e( 'Your subscription has been successful.', 'quiz-master-next' ); ?>
							</span>
						</div>
					</div>
					<div></div>
					<div id="sib-container" class="sib-container--large sib-container--vertical"
						style="text-align:center; background-color:rgba(255,255,255,1); max-width:540px; border-radius:3px; border-width:1px; border-color:#C0CCD9; border-style:solid;">
						<form id="sib-form" method="POST"
							action="https://cddf18fd.sibforms.com/serve/MUIEAO9t8eOB2GOqY73EWqFatPi328RiosfYMKieZ_8IxVL2jyEazmQ9LlkDj6pYrTlvB7JBsx3su8WdK5A4l445X0P-0r0Qf82LWXLSFa3yK0YZuypiIxy8hZfBXClZMANBeEVpBkswLw0RxDt2uWrN7B7zHTFXWY0W4mftpWo3Nqen7SQW1L9DYnXrex6lyw5EfHvZ3ZwsU6Xp"
							data-type="subscription">
							<div style="padding: 16px 0;">
								<div class="sib-input sib-form-block">
									<div class="form__entry entry_block">
										<div class="form__label-row ">
											<label class="entry__label"
												style="font-size:16px; text-align:left; font-weight:700; font-family:Helvetica, sans-serif; color:#3c4858; border-width:px;"
												for="EMAIL" data-required="*">
												<?php _e( 'Enter your email address to subscribe', 'quiz-master-next' ); ?>
											</label>

											<div class="entry__field">
												<input class="input" type="text" id="EMAIL" name="EMAIL"
													autocomplete="off" placeholder="EMAIL" data-required="true"
													required />
											</div>
										</div>

										<label class="entry__error entry__error--primary"
											style="font-size:16px; text-align:left; font-family:Helvetica, sans-serif; color:#661d1d; background-color:#ffeded; border-radius:3px; border-width:px; border-color:#ff4949;">
										</label>
										<label class="entry__specification"
											style="font-size:12px; text-align:left; font-family:Helvetica, sans-serif; color:#8390A4; border-width:px;">
											<?php _e( 'Provide your email address to subscribe. For e.g abc@xyz.com', 'quiz-master-next' ); ?>
										</label>
									</div>
								</div>
							</div>
							<div style="padding: 16px 0;">
								<div class="sib-form-block" style="text-align: left">
									<button class="sib-form-block__button sib-form-block__button-with-loader"
										style="font-size:16px; text-align:left; font-weight:700; font-family:Helvetica, sans-serif; color:#FFFFFF; background-color:#3E4857; border-radius:3px; border-width:0px;"
										form="sib-form" type="submit">
										<svg class="icon clickable__icon progress-indicator__icon sib-hide-loader-icon"
											viewBox="0 0 512 512">
											<path
												d="M460.116 373.846l-20.823-12.022c-5.541-3.199-7.54-10.159-4.663-15.874 30.137-59.886 28.343-131.652-5.386-189.946-33.641-58.394-94.896-95.833-161.827-99.676C261.028 55.961 256 50.751 256 44.352V20.309c0-6.904 5.808-12.337 12.703-11.982 83.556 4.306 160.163 50.864 202.11 123.677 42.063 72.696 44.079 162.316 6.031 236.832-3.14 6.148-10.75 8.461-16.728 5.01z" />
										</svg>
										<?php _e( 'SUBSCRIBE', 'quiz-master-next' ); ?>
									</button>
								</div>
							</div>
							<div style="padding: 16px 0;">
								<div class="sib-form-block"
									style="font-size:14px; text-align:center; font-family:Helvetica, sans-serif; color:#333; background-color:transparent; border-width:px;">
									<div class="sib-text-form-block">
										<p>
											<a href="https://sendinblue.com" rel="noopener"
												target="_blank"><?php _e( 'Terms & Privacy policy', 'quiz-master-next' ); ?></a>
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

			<!-- START - We recommend to place the below code in footer or bottom of your website html  -->
			<script>
			window.REQUIRED_CODE_ERROR_MESSAGE = 'Please choose a country code';

			window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE =
				"The information provided is invalid. Please review the field format and try again.";

			window.REQUIRED_ERROR_MESSAGE = "This field cannot be left blank. ";

			window.GENERIC_INVALID_MESSAGE =
				"The information provided is invalid. Please review the field format and try again.";




			window.translation = {
				common: {
					selectedList: '{quantity} list selected',
					selectedLists: '{quantity} lists selected'
				}
			};

			var AUTOHIDE = Boolean(0);
			</script>
			<script src="https://sibforms.com/forms/end-form/build/main.js">
			</script>
			<script src="https://www.google.com/recaptcha/api.js?hl=en"></script>
			<!-- END - We recommend to place the above code in footer or bottom of your website html  -->
			<!-- End Sendinblue Form -->
		</div>
	</div>

</div>
<?php
}
?>