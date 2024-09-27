<?php
/**
 * Creates the view for all tabs for editing the quiz.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function allows for the editing of quiz options.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_generate_quiz_options() {
	global $wpdb;
	global $mlwQuizMasterNext;
	$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$quiz_post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'quiz_id' AND meta_value = %d", $quiz_id ) );
	$post_author = get_post_field( 'post_author', $quiz_post_id, true );
	//user role addon is active
	apply_filters( 'qsm_user_role_permission_page', true );

	// Check if the current user has the capability to edit the quiz.
	if ( ( ! current_user_can( 'edit_qsm_quiz', $quiz_post_id ) || intval($post_author) != get_current_user_id()) && ! current_user_can( 'edit_others_qsm_quizzes' ) ) {
		wp_die( 'You are not allowed to edit this quiz, You need higher permission!' );
		return;
	}

	$quiz_name = '';

	// Gets registered tabs for the options page and set current tab.
	$tab_array  = $mlwQuizMasterNext->pluginHelper->get_settings_tabs();
	$active_tab = strtolower( str_replace( ' ', '-', isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'questions' ) );

	// Prepares quiz.
	$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	if ( isset( $_GET['quiz_id'] ) ) {
		$quiz_name = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
	}
	wp_localize_script( 'qsm_admin_js', 'qsmTextTabObject', array( 'quiz_id' => $quiz_id ) );
	// Edit Quiz Name.
	if ( isset( $_POST['qsm_edit_name_quiz_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_edit_name_quiz_nonce'] ) ), 'qsm_edit_name_quiz' ) ) {
		$quiz_name = isset( $_POST['edit_quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['edit_quiz_name'] ) ) : '';
		$post_id   = isset( $_POST['edit_quiz_post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['edit_quiz_post_id'] ) ) : '';
		$mlwQuizMasterNext->quizCreator->edit_quiz_name( $quiz_id, $quiz_name, $post_id );
	}
	// Update post status
	if ( isset( $_POST['qsm_update_quiz_status_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_update_quiz_status_nonce'] ) ), 'qsm_update_quiz_status' ) ) {
		$quiz_post_id  = isset( $_POST['quiz_post_id'] ) ? intval( $_POST['quiz_post_id'] ) : 0;
		$arg_post_arr  = array(
			'ID'          => $quiz_post_id,
			'post_status' => 'publish',
		);
		$update_status = wp_update_post( $arg_post_arr );
		if ( false !== $update_status ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Quiz status has been updated successfully to publish.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( 'Quiz/Survey Status Has Been Updated', $quiz_id, '' );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'An error occurred while trying to update the status of your quiz or survey. Please try again.', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error when updating quiz status', '', 0, 'error' );
		}
	}
	// Get quiz post based on quiz id
	$args      = array(
		'posts_per_page' => 1,
		'post_type'      => 'qsm_quiz',
		'meta_query'     => array(
			array(
				'key'     => 'quiz_id',
				'value'   => $quiz_id,
				'compare' => '=',
			),
		),
	);
	$the_query = new WP_Query( $args );

	// The Loop
	$post_status = $post_id      = $post_permalink   = $edit_link        = '';
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$post_permalink = get_the_permalink( get_the_ID() );
			$post_status    = get_post_status( get_the_ID() );
			$edit_link      = get_edit_post_link( get_the_ID() );
			$post_id        = get_the_ID();
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	}

	$qsm_core_tabs = array( 'questions', 'contact', 'text', 'options', 'emails', 'results-pages', 'style' );
	$addon_tabs    = 0;
	?>
	<div class="wrap" id="mlw_quiz_wrap">
		<div class='mlw_quiz_options' id="mlw_quiz_options">
			<div class="qsm-quiz-nav-bar">
				<div class="qsm-quiz-heading">
					<span id="qsm_quiz_title" class="qsm_quiz_title"><?php echo wp_kses_post( $quiz_name ); ?></span>
					<a href="javascript:void(0)" title="Edit Name" class="edit-quiz-name">
						<span class="dashicons dashicons-edit"></span>
					</a>
					<?php if ( ('draft' === $post_status || 'pending' === $post_status) && current_user_can( 'publish_qsm_quizzes' ) ) : ?>
						<form method="POST" action="">
							<?php wp_nonce_field( 'qsm_update_quiz_status', 'qsm_update_quiz_status_nonce' ); ?>
							<input type="hidden" name="quiz_post_id" value="<?php echo esc_attr( $post_id ); ?>" />
							<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Publish Quiz', 'quiz-master-next' ); ?>" />
						</form>
					<?php endif; ?>
				</div>
				<div class="qsm-quiz-top-nav-links">
					<a class="qsm-btn-quiz-edit" rel="noopener" target="_blank" href="<?php echo esc_url( $post_permalink ); ?>">
						<span class="dashicons dashicons-external"></span><?php esc_html_e( 'View Quiz', 'quiz-master-next' ); ?>
					</a>
					<a class="qsm-btn-quiz-edit" href="<?php echo esc_url( $edit_link ); ?>">
						<span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'Settings', 'quiz-master-next' ); ?>
					</a>
					<a href="javascript:void(0)" class="qsm-btn-quiz-edit qsm-help-tab-handle" rel="noopener">
						<span class="dashicons dashicons-editor-help"></span><?php esc_html_e( 'Help', 'quiz-master-next' ); ?>
					</a>
					<div class="qsm-help-tab-dropdown-list">
						<h3><?php esc_html_e( 'Useful Resources', 'quiz-master-next' ); ?></h3>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'contact-support', 'quiz_editor', 'help_box', 'quizsurvey-helpbox_support' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><img class="qsm-help-tab-icon" alt="" src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/Support.svg' ) ?>"> <?php esc_html_e( 'Get support', 'quiz-master-next' ); ?></a>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'docs/about-quiz-survey-master/quick-start', 'quiz_editor', 'help_box', 'getting_started' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Getting Started', 'quiz-master-next' ); ?></a>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys', 'quiz_editor', 'help_box', 'quizsurvey-helpbox_create_quiz' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Creating Quizzes', 'quiz-master-next' ); ?></a>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'docs/question-types', 'quiz_editor', 'help_box', 'quizsurvey-helpbox_que_type' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Question Types', 'quiz-master-next' ); ?></a>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'docs/advanced-topics', 'quiz_editor', 'help_box', 'quizsurvey-helpbox_adv_topic' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Advanced Topics', 'quiz-master-next' ); ?></a>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'docs/qsm-themes', 'quiz_editor', 'help_box', 'quizsurvey-helpbox_themes' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'QSM Themes', 'quiz-master-next' ); ?></a>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'docs/add-ons', 'quiz_editor', 'help_box', 'quizsurvey-helpbox_addons' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Add-ons', 'quiz-master-next' ); ?></a>
						<a href="<?php echo esc_url( qsm_get_plugin_link( 'docs', 'quiz_editor', 'help_box', 'quizsurvey-helpbox_all_doc' ) ); ?>" rel="noopener" target="_black" class="qsm-help-tab-item "><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'All Docs', 'quiz-master-next' ); ?></a>
					</div>
				</div>
			</div>
			<div class="qsm-alerts-placeholder"></div>
			<!-- Shows warnings, alerts then tab content -->
			<h1 style="display: none;"><?php echo wp_kses_post( $quiz_name ); ?></h1><!-- Do Not Remove this H1. This is required to display notices. -->
			<?php $mlwQuizMasterNext->alertManager->showWarnings(); ?>
			<div class="qsm-alerts">
				<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
			</div>
			<?php $user_id = get_current_user_id();
			if ( isset( $_POST['nonce_validation_notification'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce_validation_notification'] ) ), 'nonce_validation_notification' ) ) {
				update_user_meta( $user_id, 'nonce_validation_notification' ,true);
			}
				if ( ! get_user_meta( $user_id, 'nonce_validation_notification' ) ) { ?>
				<div class="qsm-nonce-validation">
					<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'php/images/info-yellow.png' ); ?>" alt="information">
					<div class="qsm-nonce-text">
						<span> <strong><?php  echo esc_html__( "Your quiz has been created, you can now start adding questions.", 'quiz-master-next' ) ?></strong></span>
						<br><span> <?php  echo esc_html__( "Please note: If you are using any cache plugins, please remember to exclude your quiz pages from cache to prevent", 'quiz-master-next' ) ?><a><?php  echo esc_html__( " Nonce validation issue.", 'quiz-master-next' ) ?></a></span>
					</div>
					<form method="POST" action="">
						<?php wp_nonce_field( 'nonce_validation_notification', 'nonce_validation_notification' ); ?>
						<button type="submit"  style="float:right;"   id="nonce_validation_notification" class="button-secondary"><?php echo esc_html__( 'Thanks I undertsand!', 'quiz-master-next' ) ?></a>
					</form>
					</div>
					<?php  } ?>
			<?php if ( $quiz_id ) {
				$active_class_aadon = true;
				?>
				<nav class="nav-tab-wrapper">
					<?php
					// Cycles through registered tabs to create navigation.
					foreach ( $tab_array as $tab ) {
						if ( in_array( $tab['slug'], $qsm_core_tabs, true ) ) {
							$active_class = '';
							if ( $active_tab === $tab['slug'] ) {
								$active_class = 'nav-tab-active';
								$active_class_aadon = false;
							}
							?>
							<a href="?page=mlw_quiz_options&quiz_id=<?php echo esc_attr( $quiz_id ); ?>&tab=<?php echo esc_attr( $tab['slug'] ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>"><?php echo wp_kses_post( $tab['title'] ); ?></a>
							<?php
						} else {
							$addon_tabs++;
						}
					}
					if ( 0 < $addon_tabs ) {
						?>
						<div class="qsm-option-tab-dropdown">
							<a href="javascript:void(0)" class="nav-tab <?php echo $active_class_aadon ? 'nav-tab-active' : ''; ?>">
								<!--<img class="qsm-tab-icon" alt="" src="<?php echo esc_url( QSM_PLUGIN_URL.'assets/Puzzle.svg' ); ?>">-->
								<?php esc_html_e( 'Add-ons', 'quiz-master-next' ); ?>
								<span class="dashicons dashicons-arrow-down"></span>
							</a>
							<div class="qsm-option-tab-dropdown-list">
								<?php
									foreach ( $tab_array as $tab ) {
										if ( ! in_array( $tab['slug'], $qsm_core_tabs, true ) ) {
											$active_class = '';
											if ( $active_tab === $tab['slug'] ) {
												$active_class = 'nav-tab-active';
											}
											?>
												<a href="?page=mlw_quiz_options&quiz_id=<?php echo esc_attr( $quiz_id ); ?>&tab=<?php echo esc_attr( $tab['slug'] ); ?>" class="nav-tab qsm-option-tab-dropdown-item <?php echo esc_attr( $active_class ); ?>"><?php echo wp_kses_post( $tab['title'] ); ?></a>
											<?php
										}
									}
								?>
							</div>
						</div>
						<?php
					}
					if ( class_exists( 'QSM_Export_Import' ) ) {
						?><a class="button button-primary qsm-import-export-btn" href="<?php echo esc_url( admin_url() . 'admin.php?page=qmn_addons&tab=export-and-import' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Import & Export', 'quiz-master-next' ); ?></a><?php
					}
					?>
				</nav>
				<div class="qsm_tab_content">
					<?php
					// Cycles through tabs looking for current tab to create tab's content.
					foreach ( $tab_array as $tab ) {
						if ( $active_tab === $tab['slug'] ) {
							call_user_func( $tab['function'] );
						}
					}
					?>
				</div>
				<?php
			} else {
				?>
				<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
					<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
						<strong><?php esc_html_e( 'Error!', 'quiz-master-next' ); ?></strong>
						<?php esc_html_e( 'Please go to the quizzes page and click on the Edit link from the quiz you wish to edit.', 'quiz-master-next' ); ?>
					</p>
				</div>
				<?php
			}
			// Shows ads
			qsm_show_adverts();
			?>
		</div>
		<div class="qsm-popup qsm-popup-slide  qsm-standard-popup" id="modal-3" aria-hidden="false">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-3-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-3-title"><?php esc_html_e( 'Edit Name', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close=""></a>
					</header>
					<main class="qsm-popup__content" id="modal-3-content">
						<form action='' method='post' id="edit-name-form">
							<label><?php esc_html_e( 'Name', 'quiz-master-next' ); ?></label>
							<input type="text" id="edit_quiz_name" class="regular-text" name="edit_quiz_name" value="<?php echo esc_attr( $quiz_name ); ?>" />
							<input type="hidden" id="edit_quiz_id" name="edit_quiz_id" value="<?php echo isset( $_GET['quiz_id'] ) && is_int( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : '0'; ?>" />
							<input type="hidden" id="edit_quiz_post_id" name="edit_quiz_post_id" value="<?php echo get_the_ID(); ?>" />
							<?php wp_nonce_field( 'qsm_edit_name_quiz', 'qsm_edit_name_quiz_nonce' ); ?>
						</form>
					</main>
					<footer class="qsm-popup__footer">
						<button id="edit-name-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Save', 'quiz-master-next' ); ?></button>
						<button class="qsm-popup__btn" data-micromodal-close="" aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
					</footer>
				</div>
			</div>
		</div>
		<!-- set global setting popup start -->
		<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="qsm-global-default-popup" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-1-title"><?php esc_html_e( 'Are you sure?', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="qsm-global-default-popup-content">
						<p>
							<?php esc_html_e( 'Do you want to continue and reset all the settings as per', 'quiz-master-next' ); ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=qmn_global_settings&tab=quiz-default-options' ) );?>" target="_blank"><?php esc_html_e( 'global defaults', 'quiz-master-next' ); ?></a> ?
						</p>
					</main>
					<footer class="qsm-popup__footer">
						<button class="qsm-popup__btn" data-micromodal-close="" aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
						<form action="" method="post">
							<?php wp_nonce_field( 'set_global_default_settings','set_global_default_settings_nonce' ); ?>
							<button name="global_setting" class="button button-primary" type="submit"><?php esc_html_e( 'Continue', 'quiz-master-next' ); ?></button>
						</form>
					</footer>
				</div>
			</div>
		</div>
		<!-- set global setting popup end -->
	</div><!-- Backbone Views -->
	<script type="text/javascript">jQuery(document).ready(function(){jQuery(".qsm-alerts-placeholder").length>0&&jQuery(".qsm-alerts").length>0&&jQuery(".qsm-alerts-placeholder").replaceWith(jQuery(".qsm-alerts"))});</script>
	<?php
	add_action('admin_footer', 'qsm_quiz_options_notice_template');?>
	<!--Div for the upgrade popup of advanced question type -->
	<?php
	$style_tags = array();
		if ( ! class_exists('QSM_Advance_Question') ) {
			qsm_advance_question_type_upgrade_popup();
			$style_tags[] = '.question[data-question-type="15"]';
			$style_tags[] = '.question[data-question-type="16"]';
			$style_tags[] = '.question[data-question-type="17"]';
		}
		if ( ! class_exists('QSM_Advanced_Assessment') ) {
			$qsm_pop_up_arguments = array(
				"id"           => 'modal-advanced-assessment',
				"title"        => __('Advanced Assessment', 'quiz-master-next'),
				"description"  => __('Create assessments with ease using Advanced Assessment. With features like label assignment, personalized results, and insightful data visualization, you can engage your audience effectively.', 'quiz-master-next'),
				"chart_image"  => plugins_url('', dirname(__FILE__)) . '/images/advance-assessment-chart.png',
				"information"  => __( 'Get all our add-ons at a discounted rate with the QSM Addon Bundle and save up to 95% today! Alternatively, you can also purchase the Advanced Assessment Addon separately.', 'quiz-master-next' ),
				"buy_btn_text" => __( 'Buy Quiz Advanced Assessment', 'quiz-master-next' ),
				"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/advanced-assessment', 'quiz-documentation', 'plugin', 'advanced-assessment', 'qsm_plugin_upsell' ),
				"upgrade_link" => qsm_get_plugin_link( 'pricing', 'quiz-documentation', 'plugin', 'advanced-assessment', 'qsm_plugin_upsell' ),
				"addon_link"   => qsm_get_plugin_link( 'downloads/advanced-assessment', 'quiz-documentation', 'plugin', 'advanced-assessment', 'qsm_plugin_upsell' ),
			);
			qsm_admin_upgrade_popup($qsm_pop_up_arguments);
		}
		if ( ! class_exists('QSM_Flashcards') ) {
			$style_tags[] = '.question[data-question-type="18"]';
		}
		if ( ! empty($style_tags) ) {
			?>
			<style type="text/css">
				<?php echo implode( ', ', $style_tags ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
				{border-color: red;}
				</style>
			<?php
		}
	?>
<?php
}

/**
 * Adds the quiz option notice templates to the option tab.
 *
 * @since 7.3.5
 */
function qsm_quiz_options_notice_template() {
	?>
	<!-- View for Notices -->
	<script type="text/template" id="tmpl-notice">
		<div class="notice notice-large notice-{{data.type}}">
			<p>{{data.message}}</p>
		</div>
	</script>
	<?php
}