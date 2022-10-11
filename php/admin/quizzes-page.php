<?php
/**
 * This file handles the contents on the "Quizzes/Surveys" page.
 *
 * @package QSM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'QSMQuizList' ) ) {

	class QSMQuizList {

		/**
		 * Main Construct Function
		 *
		 * Call functions within class
		 *
		 * @since 3.6.1
		 * @uses MLWQuizMasterNext::load_dependencies() Loads required filed
		 * @uses MLWQuizMasterNext::add_hooks() Adds actions to hooks and filters
		 * @return void
		 */
		public function __construct() {
			$this->add_hooks();
		}

		/**
		 * Add Hooks
		 *
		 * Adds functions to relavent hooks and filters
		 *
		 * @since 3.6.1
		 * @return void
		 */
		private function add_hooks() {
			add_action( 'admin_notices', array( $this, 'qsm_quiz_list_header' ), 0 );
			add_action( 'load-edit.php', array( $this, 'qsm_process_post_row_actions' ), -99 );
			add_filter( 'get_edit_post_link', array( $this, 'qsm_get_edit_post_link' ), 10, 2 );
			add_filter( 'views_edit-qsm_quiz', array( $this, 'qsm_quiz_list_views_edit' ) );
			add_filter( 'manage_qsm_quiz_posts_columns', array( $this, 'qsm_set_custom_edit_qsm_quiz_columns' ) );
			add_action( 'manage_qsm_quiz_posts_custom_column', array( $this, 'qsm_custom_qsm_quiz_columns' ), 99, 2 );
			add_filter( 'post_row_actions', array( $this, 'qsm_post_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-qsm_quiz', array( $this, 'qsm_quiz_edit_bulk_actions' ), 9999, 1 );
			add_filter( 'handle_bulk_actions-edit-qsm_quiz', array( $this, 'qsm_quiz_bulk_action_handler' ), 10, 3 );
			add_action( 'manage_posts_extra_tablenav', array( $this, 'qsm_manage_posts_extra_tablenav' ), 99, 1 );
			add_action( 'admin_footer', array( $this, 'qsm_admin_footer_text' ) );
		}

		/**
		 * Filter views links for quiz post type
		 * @param type $views
		 * @return type
		 */
		public function qsm_quiz_list_views_edit( $views ) {
			unset( $views['trash'] );
			return $views;
		}

		/**
		 * Add the custom columns to the quiz post type:
		 * @param array $columns
		 * @return type
		 */
		public function qsm_set_custom_edit_qsm_quiz_columns( $columns ) {
			if ( isset( $_REQUEST['post_status'] ) && 'trash' == $_REQUEST['post_status'] ) {
				return $columns;
			}
			$sort_link = admin_url('edit.php?post_type=qsm_quiz&orderby=post_modified&order=asc');
			if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && "post_modified" === $_GET['orderby'] && "asc" === $_GET['order'] ) {
				$sort_link = admin_url('edit.php?post_type=qsm_quiz&orderby=post_modified&order=desc');
			}
			unset( $columns['author'] );
			unset( $columns['comments'] );
			unset( $columns['date'] );
			$columns['shortcode']        = __( 'Shortcode', 'quiz-master-next' );
			$columns['total_questions']  = __( 'No. of Questions', 'quiz-master-next' );
			$columns['views']            = __( 'Views', 'quiz-master-next' );
			$columns['participants']     = __( 'Participants', 'quiz-master-next' );
			$columns['post_modified']        = "<a href='" . $sort_link . "'>" . __( 'Last Modified', 'quiz-master-next')  . "</a>";
			return $columns;
		}

		/**
		 * Add the data to the custom columns for the quiz post type:
		 * @param type $column
		 * @param type $post_id
		 */
		public function qsm_custom_qsm_quiz_columns( $column, $post_id ) {
			global $wpdb, $mlwQuizMasterNext;
			$quiz_id = get_post_meta( $post_id, 'quiz_id', true );
			switch ( $column ) {
				case 'shortcode':
					$shortcode_links = '<a href="#" class="qsm-list-shortcode-view"><span class="dashicons dashicons-welcome-view-site"></span></a>';
					$shortcode_links .= '<div class="sc-content sc-embed">[qsm quiz=' . $quiz_id . ']</div>';
					$shortcode_links .= '<div class="sc-content sc-link">[qsm_link id=' . $quiz_id . ']' . __( 'Click here', 'quiz-master-next' ) . '[/qsm_link]</div>';
					echo wp_kses_post( $shortcode_links );
					break;

				case 'total_questions':
					$total_questions = $mlwQuizMasterNext->pluginHelper->get_questions_count( $quiz_id );
					echo esc_attr( $total_questions );
					break;

				case 'views':
					$quiz_views  = $wpdb->get_var( "SELECT `quiz_views` FROM `{$wpdb->prefix}mlw_quizzes` WHERE `quiz_id` = '{$quiz_id}'" );
					$views_html  = $quiz_views;
					$views_html  .= '<div class="row-actions">';
					$views_html  .= '<a class="qsm-action-link qsm-action-link-reset" href="#" data-id="' . esc_attr( $quiz_id ) . '">' . __( 'Reset', 'quiz-master-next' ) . '</a>';
					$views_html  .= '</div>';
					echo wp_kses_post( $views_html );
					break;

				case 'participants':
					$quiz_results_count  = $wpdb->get_var( "SELECT COUNT(result_id) FROM `{$wpdb->prefix}mlw_results` WHERE `deleted`= 0 AND `quiz_id`= '{$quiz_id}'" );
					$participants        = '<span class="column-comments">';
					$participants        .= '<span class="post-com-count post-com-count-approved">';
					$participants        .= '<span class="comment-count-approved" aria-hidden="true">' . $quiz_results_count . '</span>';
					$participants        .= '<span class="screen-reader-text">' . $quiz_results_count . __( 'Participants', 'quiz-master-next' ) . '</span>';
					$participants        .= '</span>';
					$participants        .= '</span>';
					echo wp_kses_post( $participants );
					break;

				case 'post_modified':
					$activity_date   = get_the_modified_date( get_option( 'date_format' ),$post_id );
					$activity_time   = get_the_modified_date( 'h:i:s A', $post_id );
					echo wp_kses_post( '<abbr title="' . $activity_date . ' ' . $activity_time . '">' . $activity_date . '<br/> ' . $activity_time . '</abbr>' );
					break;
				default:
					break;
			}
		}

		public function qsm_get_edit_post_link( $link, $post_id ) {
			global $wpdb, $pagenow, $mlwQuizMasterNext;
			if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'qsm_quiz' == $_GET['post_type'] ) {
				$quiz_id = get_post_meta( $post_id, 'quiz_id', true );
				$link    = admin_url( 'admin.php?page=mlw_quiz_options&quiz_id=' . $quiz_id );
			}
			return $link;
		}

		/**
		 * Add action links for each quiz post type
		 * @param array $actions
		 * @param type $post
		 * @return string
		 */
		public function qsm_post_row_actions( $actions, $post ) {
			$post_status = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : 'all';
			if ( 'qsm_quiz' == $post->post_type && 'trash' != $post_status ) {
				$quiz_id = get_post_meta( $post->ID, 'quiz_id', true );
				if ( ! empty( $quiz_id ) ) {
					$actions = array(
						'edit'         => '<a class="qsm-action-link" href="admin.php?page=mlw_quiz_options&quiz_id=' . esc_attr( $quiz_id ) . '">' . esc_html__( 'Edit', 'quiz-master-next' ) . '</a>',
						'duplicate'    => '<a class="qsm-action-link qsm-action-link-duplicate" href="#" data-id="' . esc_attr( $quiz_id ) . '">' . esc_html__( 'Duplicate', 'quiz-master-next' ) . '</a>',
						'delete'       => '<a class="qsm-action-link qsm-action-link-delete" href="#" data-id="' . esc_attr( $quiz_id ) . '" data-name="' . esc_attr( $post->post_title ) . '">' . esc_html__( 'Delete', 'quiz-master-next' ) . '</a>',
						'view_results' => '<a class="qsm-action-link" href="admin.php?page=mlw_quiz_results&quiz_id=' . esc_attr( $quiz_id ) . '">' . esc_html__( 'View Results', 'quiz-master-next' ) . '</a>',
						'view'         => '<a class="qsm-action-link" target="_blank" rel="noopener" href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html__( 'Preview', 'quiz-master-next' ) . '</a>',
					);
				}
			}
			return $actions;
		}

		public function qsm_quiz_edit_bulk_actions( $bulk_actions ) {
			unset( $bulk_actions['edit'] );
			unset( $bulk_actions['trash'] );
			$bulk_actions['delete_pr'] = __( 'Delete Permanently', 'quiz-master-next' );
			return $bulk_actions;
		}

		/**
		 * Delete Bulk Quiz
		 * @param type $redirect_to
		 * @param type $doaction
		 * @param type $post_ids
		 * @return type
		 */
		public function qsm_quiz_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
			global $mlwQuizMasterNext;
			if ( 'delete_pr' == $doaction && ! empty( $post_ids ) ) {
				$_POST['qsm_delete_from_db']             = isset( $_REQUEST['qsm_delete_from_db'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['qsm_delete_from_db'] ) ) : 0;
				$_POST['qsm_delete_question_from_qb']    = isset( $_REQUEST['qsm_delete_question_from_qb'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['qsm_delete_question_from_qb'] ) ) : 0;
				foreach ( $post_ids as $post_id ) {
					$quiz_id     = get_post_meta( $post_id, 'quiz_id', true );
					do_action( 'qsm_before_delete_quiz', $quiz_id );
					$quiz_name   = get_the_title( $post_id );
					$mlwQuizMasterNext->quizCreator->delete_quiz( $quiz_id, $quiz_name );
				}
				$QSMAlertManager = $mlwQuizMasterNext->alertManager->alerts;
				setcookie( 'QSMAlertManager', wp_json_encode( $QSMAlertManager ), time() + 86400, COOKIEPATH, COOKIE_DOMAIN );
				$redirect_to     = add_query_arg( 'quiz_bulk_delete', count( $post_ids ), $redirect_to );
			}
			return $redirect_to;
		}

		/**
		 * Handle custom link actions.
		 * @global type $wpdb
		 * @global type $mlwQuizMasterNext
		 */
		public function qsm_process_post_row_actions() {
			global $wpdb, $pagenow, $mlwQuizMasterNext;
			if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'qsm_quiz' == $_GET['post_type'] ) {
				// Delete quiz.
				if ( isset( $_POST['qsm_delete_quiz_nonce'], $_POST['delete_quiz_id'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_delete_quiz_nonce'] ) ), 'qsm_delete_quiz' ) ) {
					$quiz_id     = sanitize_text_field( wp_unslash( $_POST['delete_quiz_id'] ) );
					$quiz_id     = intval( str_replace( 'QID', '', $quiz_id ) );
					do_action( 'qsm_before_delete_quiz', $quiz_id );
					$quiz_name   = isset( $_POST['delete_quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_quiz_name'] ) ) : '';
					$mlwQuizMasterNext->quizCreator->delete_quiz( $quiz_id, $quiz_name );
				}

				// Duplicate Quiz.
				if ( isset( $_POST['qsm_duplicate_quiz_nonce'], $_POST['duplicate_quiz_id'], $_POST['duplicate_new_quiz_name'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_duplicate_quiz_nonce'] ) ), 'qsm_duplicate_quiz' ) ) {
					$quiz_id     = sanitize_text_field( wp_unslash( $_POST['duplicate_quiz_id'] ) );
					$quiz_id     = intval( str_replace( 'QID', '', $quiz_id ) );
					$quiz_name   = isset( $_POST['duplicate_new_quiz_name'] ) ? htmlspecialchars( sanitize_text_field( wp_unslash( $_POST['duplicate_new_quiz_name'] ) ), ENT_QUOTES ) : '';
					$mlwQuizMasterNext->quizCreator->duplicate_quiz( $quiz_id, $quiz_name, isset( $_POST['duplicate_questions'] ) ? sanitize_text_field( wp_unslash( $_POST['duplicate_questions'] ) ) : 0  );
				}

				// Resets stats for a quiz.
				if ( isset( $_POST['qsm_reset_stats_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_reset_stats_nonce'] ) ), 'qsm_reset_stats' ) ) {
					$quiz_id         = isset( $_POST['reset_quiz_id'] ) ? intval( $_POST['reset_quiz_id'] ) : '';
					$quiz_post_id    = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'quiz_id' AND meta_value = '$quiz_id'" );
					if ( empty( $quiz_post_id ) || ! current_user_can( 'edit_post', $quiz_post_id ) ) {
						$mlwQuizMasterNext->alertManager->newAlert( __( 'Sorry, you are not allowed to reset this quiz.', 'quiz-master-next' ), 'error' );
					} else {
						$results = $wpdb->update( $wpdb->prefix . 'mlw_quizzes', array(
							'quiz_views'    => 0,
							'quiz_taken'    => 0,
							'last_activity' => gmdate( 'Y-m-d H:i:s' ),
						), array( 'quiz_id' => $quiz_id ), array( '%d', '%d', '%s' ), array( '%d' )
						);
						if ( false !== $results ) {
							$mlwQuizMasterNext->alertManager->newAlert( __( 'The stats has been reset successfully.', 'quiz-master-next' ), 'success' );
							$mlwQuizMasterNext->audit_manager->new_audit( 'Quiz Stats Have Been Reset', $quiz_id, '' );
						} else {
							$mlwQuizMasterNext->alertManager->newAlert( __( 'Error trying to reset stats. Please try again.', 'quiz-master-next' ), 'error' );
							$mlwQuizMasterNext->log_manager->add( 'Error resetting stats', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
						}
					}
				}
				$QSMAlertManager = $mlwQuizMasterNext->alertManager->alerts;
				setcookie( 'QSMAlertManager', wp_json_encode( $QSMAlertManager ), time() + 86400, COOKIEPATH, COOKIE_DOMAIN );
			}
		}

		public function qsm_manage_posts_extra_tablenav( $which ) {
			global $wpdb, $pagenow, $mlwQuizMasterNext;
			if ( 'top' != $which ) {
				return;
			}
			if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'qsm_quiz' == $_GET['post_type'] ) {
				if ( class_exists( 'QSM_Export_Import' ) ) {
					?><a class="button button-primary" href="<?php echo esc_url( admin_url() . 'admin.php?page=qmn_addons&tab=export-and-import' ); ?>" style="position: relative;top: 0px;" target="_blank" rel="noopener"><?php esc_html_e( 'Import & Export', 'quiz-master-next' ); ?></a><?php
				} else {
					?><a id="show_import_export_popup" href="#" style="position: relative;top: 0px;" class="add-new-h2 button-primary"><?php esc_html_e( 'Import & Export', 'quiz-master-next' ); ?></a><?php
				}
			}
		}

		/**
		 * Add List Page Title row
		 * @global type $wpdb
		 * @global type $pagenow
		 * @global type $mlwQuizMasterNext
		 */
		public function qsm_quiz_list_header() {
			global $wpdb, $pagenow, $mlwQuizMasterNext;
			if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'qsm_quiz' == $_GET['post_type'] ) {
				?>
				<div class="wrap qsm-quizes-page">
					<h1>
						<?php esc_html_e( 'Quizzes & Surveys', 'quiz-master-next' ); ?>
						<a id="new_quiz_button" href="#" class="add-new-h2"><?php esc_html_e( 'Add New', 'quiz-master-next' ); ?></a>
					</h1>
					<?php
					if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
						?>
						<div class="qsm-info-box">
							<p><?php esc_html_e( 'Your site is using PHP version', 'quiz-master-next' ); ?>
								<?php echo esc_html( PHP_VERSION ); ?>!
								<?php esc_html_e( 'Starting in QSM 6.0, your version of PHP will no longer be supported.', 'quiz-master-next' ); ?>
								<a href="<?php echo esc_url( qsm_get_plugin_link('increased-minimum-php-version-qsm-6-0', 'quiz-list-page', 'minimum-php-notice') );?>" target="_blank" rel="noopener"><?php esc_html_e( "Click here to learn more about QSM's minimum PHP version change.", 'quiz-master-next' ); ?></a>
							</p>
						</div>
						<?php
					}
					if ( isset( $_COOKIE['QSMAlertManager'] ) && ! empty( $_COOKIE['QSMAlertManager'] ) ) {
						$mlwQuizMasterNext->alertManager->alerts = json_decode( sanitize_text_field( wp_unslash( $_COOKIE['QSMAlertManager'] ) ), true );
						unset( $_COOKIE['QSMAlertManager'] );
					}
					$mlwQuizMasterNext->alertManager->showAlerts();
					?>
				</div>
				<div class="clear"></div>
				<style type="text/css">.post-type-qsm_quiz .wrap .wp-heading-inline, .post-type-qsm_quiz .wrap .wp-heading-inline+.page-title-action {display: none !important;}</style>
				<?php
			}
		}

		/**
		 * Add popups in admin footer
		 * @global type $mlwQuizMasterNext
		 */
		public function qsm_admin_footer_text() {
			global $mlwQuizMasterNext;
			if ( isset( $_REQUEST['post_type'] ) && 'qsm_quiz' == $_REQUEST['post_type'] ) {
				?>
				<!-- Popup for resetting stats -->
				<div class="qsm-popup qsm-popup-slide" id="modal-1" aria-hidden="true">
					<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
						<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
							<header class="qsm-popup__header">
								<h2 class="qsm-popup__title" id="modal-1-title">
									<?php esc_html_e( 'Reset stats for this quiz?', 'quiz-master-next' ); ?></h2>
								<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
							</header>
							<main class="qsm-popup__content" id="modal-1-content">
								<p><?php esc_html_e( 'Are you sure you want to reset the stats to 0? All views and taken stats for this quiz will be reset. This is permanent and cannot be undone.', 'quiz-master-next' ); ?>
								</p>
								<form action="" method="post" id="reset_quiz_form">
									<?php wp_nonce_field( 'qsm_reset_stats', 'qsm_reset_stats_nonce' ); ?>
									<input type="hidden" id="reset_quiz_id" name="reset_quiz_id" value="0" />
								</form>
							</main>
							<footer class="qsm-popup__footer">
								<button id="reset-stats-button"
									class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Reset All Stats For Quiz', 'quiz-master-next' ); ?></button>
								<button class="qsm-popup__btn" data-micromodal-close
									aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
							</footer>
						</div>
					</div>
				</div>
				<!-- Popup for new quiz -->
				<?php qsm_create_new_quiz_wizard(); ?>
				<!-- Popup for duplicate quiz -->
				<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="modal-4" aria-hidden="true">
					<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
						<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-4-title">
							<header class="qsm-popup__header">
								<h2 class="qsm-popup__title" id="modal-4-title"><?php esc_html_e( 'Duplicate', 'quiz-master-next' ); ?></h2>
								<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
							</header>
							<main class="qsm-popup__content" id="modal-4-content">
								<form action='' method='post' id="duplicate-quiz-form">
									<label for="duplicate_questions"><?php esc_html_e( 'Duplicate questions also?', 'quiz-master-next' ); ?></label>
									<input type="checkbox" name="duplicate_questions" id="duplicate_questions" /><br />
									<br />
									<label for="duplicate_new_quiz_name"><?php esc_html_e( 'Name Of New Quiz Or Survey:', 'quiz-master-next' ); ?></label>
									<input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" />
									<input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
									<?php wp_nonce_field( 'qsm_duplicate_quiz', 'qsm_duplicate_quiz_nonce' ); ?>
								</form>
							</main>
							<footer class="qsm-popup__footer qsm-popup__footer_with_btns">
								<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
								<button id="duplicate-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Duplicate', 'quiz-master-next' ); ?></button>
							</footer>
						</div>
					</div>
				</div>
				<!-- Popup for delete quiz -->
				<div class="qsm-popup qsm-popup-slide qsm-standard-popup" id="modal-5" aria-hidden="true">
					<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
						<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
							<header class="qsm-popup__header">
								<h2 class="qsm-popup__title" id="modal-5-title"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></h2>
								<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
							</header>
							<main class="qsm-popup__content" id="modal-5-content">
								<form action='' method='post' id="delete-quiz-form" style="display:flex; flex-direction:column;">
									<h3 style="margin-top: 0;"><strong><?php esc_html_e( 'Are you sure you want to delete this quiz or survey?', 'quiz-master-next' ); ?></strong></h3>
									<label>
										<input type="checkbox" value="1" name="qsm_delete_question_from_qb" />
										<?php esc_html_e( 'Delete question from question bank?', 'quiz-master-next' ); ?>
									</label>
									<label>
										<input type="checkbox" name="qsm_delete_from_db" value="1"/>
										<?php esc_html_e( 'Delete items from database?', 'quiz-master-next' ); ?>
									</label>
									<?php wp_nonce_field( 'qsm_delete_quiz', 'qsm_delete_quiz_nonce' ); ?>
									<input type='hidden' id='delete_quiz_id' name='delete_quiz_id' value='' />
									<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />
								</form>
							</main>
							<footer class="qsm-popup__footer qsm-popup__footer_with_btns">
								<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
								<button id="delete-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></button>
							</footer>
						</div>
					</div>
				</div>
				<!-- Popup for bulk delete quiz -->
				<div class="qsm-popup qsm-popup-slide" id="modal-bulk-delete" aria-hidden="true">
					<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
						<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
							<header class="qsm-popup__header">
								<h2 class="qsm-popup__title" id="modal-5-title"><?php esc_html_e( 'Bulk Delete', 'quiz-master-next' ); ?></h2>
								<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
							</header>
							<main class="qsm-popup__content" id="modal-5-content">
								<form action='' method='post' id="bult-delete-quiz-form" style="display:flex; flex-direction:column;">
									<h3><strong><?php esc_html_e( 'Are you sure you want to delete selected quiz or survey?', 'quiz-master-next' ); ?></strong>
									</h3>
									<label>
										<input type="checkbox" name="qsm_delete_question_from_qb" />
										<?php esc_html_e( 'Delete question from question bank?', 'quiz-master-next' ); ?>
									</label>
									<label>
										<input type="checkbox" name="qsm_delete_from_db" />
										<?php esc_html_e( 'Delete items from database?', 'quiz-master-next' ); ?>
									</label>
								</form>
							</main>
							<footer class="qsm-popup__footer">
								<button id="bulk-delete-quiz-button"
									class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></button>
								<button class="qsm-popup__btn" data-micromodal-close
									aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
							</footer>
						</div>
					</div>
				</div>
				<!-- Popup for export import upsell -->
				<?php
				if ( ! class_exists( 'QSM_Export_Import' ) ) {
					$qsm_pop_up_arguments = array(
						"id"           => 'modal-export-import',
						"title"        => __('Export & Import', 'quiz-master-next'),
						"description"  => __('Wondering how to import quizzes or survey data from one website and export it to another? Easily export and import data with this premium add-on.', 'quiz-master-next'),
						"chart_image"  => plugins_url('', dirname(__FILE__)) . '/images/export_import_chart.png',
						"information"  => __('QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today OR you can buy Export & Import Addon separately.', 'quiz-master-next'),
						"buy_btn_text" => __('Buy Export & Import Addon', 'quiz-master-next'),
						"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/export-import/', 'qsm_list', 'importexport_button', 'import-export-upsell_read_documentation', 'qsm_plugin_upsell' ),
						"upgrade_link" => qsm_get_plugin_link( 'pricing', 'qsm_list', 'importexport_button', 'import-export-upsell_upgrade', 'qsm_plugin_upsell' ),
						"addon_link"   => qsm_get_plugin_link( 'downloads/export-import', 'qsm_list', 'importexport_button', 'import-export-upsell_buy_addon', 'qsm_plugin_upsell' ),
					);
					qsm_admin_upgrade_popup($qsm_pop_up_arguments);
				}
				?>
				<!-- Popup for delete quiz -->
				<div class="qsm-popup qsm-popup-slide" id="modal-6" aria-hidden="true">
					<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
						<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
							<header class="qsm-popup__header">
								<h2 class="qsm-popup__title" id="modal-5-title"><?php esc_html_e( 'Shortcode', 'quiz-master-next' ); ?></h2>
								<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
							</header>
							<main class="qsm-popup__content" id="modal-5-content">
								<div class="qsm-row" style="margin-bottom: 30px;">
									<lable><?php esc_html_e( 'Embed Shortcode', 'quiz-master-next' ); ?></lable>
									<input type="text" value="" id="sc-shortcode-model-text" style="width: 72%;padding: 5px;">
									<button class="button button-primary" id="sc-copy-shortcode"><span
											class="dashicons dashicons-admin-page"></span></button>
								</div>
								<div class="qsm-row">
									<lable><?php esc_html_e( 'Link Shortcode', 'quiz-master-next' ); ?></lable>
									<input type="text" value="" id="sc-shortcode-model-text-link" style="width: 72%;padding: 5px;">
									<button class="button button-primary" id="sc-copy-shortcode-link"><span
											class="dashicons dashicons-admin-page"></span></button>
								</div>
							</main>
						</div>
					</div>
				</div>

				<!-- Templates -->
				<script type="text/template" id="tmpl-no-quiz">
					<div class="qsm-no-quiz-wrapper">
						<span class="dashicons dashicons-format-chat"></span>
						<h2><?php esc_html_e( 'You do not have any quizzes or surveys yet', 'quiz-master-next' ); ?></h2>
						<div class="buttons">
							<a class="button button-primary button-hero qsm-wizard-noquiz" href="#"><?php esc_html_e( 'Create New Quiz/Survey', 'quiz-master-next' ); ?></a>
							<a class="button button-secondary button-hero" href="<?php echo esc_url( qsm_get_plugin_link('docs', 'quiz-list-page') );?>" target="_blank"><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Read Documentation', 'quiz-master-next' ); ?></a>
						</div>
						<h3><?php esc_html_e( 'or watch the below video to get started', 'quiz-master-next' ); ?></h3>
						<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/coE5W_WB-48" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</script>
				<?php
			}
		}



	}

}
global $QSMQuizList;
$QSMQuizList = new QSMQuizList();
