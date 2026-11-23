<?php
/**
 * Generates the content for the tools page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'QSM_Database_Migration' ) ) {
	include_once QSM_PLUGIN_PATH . 'php/admin/class-qsm-database-migration.php';
}

/**
 * Required addons & versions for migration.
 *
 * @return array
 */
function qsm_migration_get_required_addons() {
	$addons_data = qsm_get_parsing_script_data( 'required-to-migration-updated.json' );

	if ( ! $addons_data ) {
		return array();
	}

	$migration_addons = array();
	foreach ( $addons_data as $addon ) {
		$migration_addons[] = array(
			'name'                   => $addon['name'],
			'path'                   => $addon['path'],
			'required_addon_version' => $addon['required_version'],
		);
	}

	return $migration_addons;
}

/**
 * Get plugin path by plugin name.
 *
 * @param string $plugin_name
 * @param array  $installed_plugins
 * @return string
 */
function qsm_get_plugin_path_by_name( $plugin_name, $installed_plugins ) {
	foreach ( $installed_plugins as $path => $plugin ) {
		if ( isset( $plugin['Name'] ) && $plugin['Name'] === $plugin_name ) {
			return $path;
		}
	}
	return '';
}

/**
 * Evaluate addon compatibility for migration.
 *
 * @param array|null $required_addons
 * @return array
 */
function qsm_migration_evaluate_addon_requirements( $required_addons = null ) {
	if ( null === $required_addons ) {
		$required_addons = qsm_migration_get_required_addons();
	}

	if ( empty( $required_addons ) || ! is_array( $required_addons ) ) {
		return array(
			'allowed' => true,
			'addons'  => array(),
		);
	}

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$installed_plugins  = get_plugins();
	$evaluated_addons   = array();
	$allowed            = true;
	$blocked_addon_name = '';

	foreach ( $required_addons as $addon ) {
		$name = isset( $addon['name'] ) ? (string) $addon['name'] : '';

		$path = '';
		if ( ! empty( $addon['path'] ) ) {
			$path = (string) $addon['path'];
		} elseif ( ! empty( $name ) ) {
			$path = qsm_get_plugin_path_by_name( $name, $installed_plugins );
		}

		$is_installed       = false;
		$installed_version = '';

		if ( ! empty( $path ) && isset( $installed_plugins[ $path ] ) ) {
			$is_installed       = true;
			$installed_version = isset( $installed_plugins[ $path ]['Version'] )
				? (string) $installed_plugins[ $path ]['Version']
				: '';
		}

		$is_compatible = true;
		if (
			$is_installed &&
			isset( $addon['required_addon_version'] ) &&
			'' !== $addon['required_addon_version']
		) {
			$is_compatible = version_compare(
				$installed_version,
				$addon['required_addon_version'],
				'>='
			);
		}

		if ( ! $is_compatible && $allowed ) {
			$allowed            = false;
			$blocked_addon_name = $name;
		}

		$evaluated_addons[] = array(
			'name'                   => $name,
			'path'                   => $path,
			'required_addon_version' => isset( $addon['required_addon_version'] ) ? $addon['required_addon_version'] : '',
			'is_installed'           => $is_installed,
			'installed_version'      => $installed_version,
			'is_compatible'          => $is_compatible,
		);
	}

	$result = array(
		'allowed' => $allowed,
		'addons'  => $evaluated_addons,
	);

	if ( ! $allowed ) {
		$result['message'] = __(
			'Please update the addons listed below to continue with the migration.',
			'quiz-master-next'
		);
	}

	return $result;
}

/**
 * Admin page UI and script enqueue + localization.
 *
 * @return void
 */
function qsm_migration_database_callback() {
	global $mlwQuizMasterNext;

	// The screen uses the admin UI kit; css/qsm-database-migration.css (the old look) is no longer loaded here.
	wp_enqueue_script(
		'qsm-database-migration',
		QSM_PLUGIN_JS_URL . '/qsm-database-migration-script.js',
		array( 'jquery' ),
		$mlwQuizMasterNext->version,
		true
	);
	$compatibility = qsm_migration_evaluate_addon_requirements();

	wp_localize_script( 'qsm-database-migration', 'qsmMigrationData', array(
		'ajax_url'            => admin_url( 'admin-ajax.php' ),
		'nonce'               => wp_create_nonce( 'qsm_migration_nonce' ),
		'confirmMessage'      => __( 'Are you sure? After migration, any quiz results submitted on QSM 11 won’t be accessible if you downgrade to an earlier version. Your existing data will remain safe, but it’s recommended to take a database backup before proceeding.', 'quiz-master-next' ),
		'startMessage'        => __( 'Migration started...', 'quiz-master-next' ),
		'processingMessage'   => __( 'Migration in progress...', 'quiz-master-next' ),
		'successMessage'      => __( 'Migration completed successfully!', 'quiz-master-next' ),
		'errorMessage'        => __( 'An error occurred during migration.', 'quiz-master-next' ),
		'warningMessage'      => __( 'Before starting migration, please create a database backup.', 'quiz-master-next' ),
		'finalizingMigration' => __( 'Finalizing migration, retrying failed results...', 'quiz-master-next' ),
		'blockedMessage'      => ! empty( $compatibility['message'] ) ? wp_kses_post( $compatibility['message'] ) : '',
		'labelTotalRecords'   => __( 'Total Results to Migrate:', 'quiz-master-next' ),
		'labelProcessed'      => __( 'Results Processed:', 'quiz-master-next' ),
		'labelInserted'       => __( 'Total Results Migrated:', 'quiz-master-next' ),
		'labelFailed'         => __( 'Total Results Failed:', 'quiz-master-next' ),
		'labelErrorNote'      => __( 'Migration stopped due to an error. Check browser console and server logs for details.', 'quiz-master-next' ),
	) );
	?>

	<?php
	$compatibility_addons = array();
	if ( ! empty( $compatibility['addons'] ) && is_array( $compatibility['addons'] ) ) {
		foreach ( $compatibility['addons'] as $addon ) {
			if ( empty( $addon['is_installed'] ) ) {
				continue;
			}
			$compatibility_addons[] = $addon;
		}
	}
	$has_block = empty( $compatibility['allowed'] );
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'       => __( 'Database Migration', 'quiz-master-next' ),
			'description' => __( 'Move quiz results to the new results tables.', 'quiz-master-next' ),
			'class'       => 'qsm-ui-tools-card qsm-ui-migration-card',
		)
	);
	?>
	<form id="qsm-database-migration-form" class="qsm-database-migration-form qsm-ui-text qsm-ui-stack">
		<?php
		echo QSM_Admin_UI::notice( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			esc_html__( 'Your data will remain safe during migration. However, we strongly recommend taking a full database backup as a precaution to prevent any potential data loss.', 'quiz-master-next' ),
			array(
				'type'  => 'warning',
				'title' => __( 'Before You Continue', 'quiz-master-next' ),
			)
		);

		if ( ! empty( $compatibility_addons ) ) {
			?>
			<div class="qsm-migration-addon-compatibility qsm-ui-stack">
				<?php
				if ( $has_block && ! empty( $compatibility['message'] ) ) {
					echo QSM_Admin_UI::notice( wp_kses_post( $compatibility['message'] ), array( 'type' => 'danger' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				}
				?>
				<div class="qsm-ui-table-wrap">
					<table class="qsm-ui-table qsm-migration-addon-compatibility-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Addon Name', 'quiz-master-next' ); ?></th>
								<th><?php esc_html_e( 'Required Version', 'quiz-master-next' ); ?></th>
								<th><?php esc_html_e( 'Installed Version', 'quiz-master-next' ); ?></th>
								<th><?php esc_html_e( 'Status', 'quiz-master-next' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $compatibility_addons as $addon ) :
								$name              = isset( $addon['name'] ) ? $addon['name'] : '';
								$required_version  = isset( $addon['required_addon_version'] ) ? $addon['required_addon_version'] : '';
								$installed_version = isset( $addon['installed_version'] ) && $addon['installed_version']
									? $addon['installed_version']
									: esc_html__( 'Not Installed', 'quiz-master-next' );
								$is_compatible     = ! empty( $addon['is_compatible'] );
								?>
								<tr class="<?php echo $is_compatible ? 'qsm-addon-compatible' : 'qsm-addon-not-compatible'; ?>">
									<td class="qsm-ui-cell-title"><?php echo esc_html( $name ); ?></td>
									<td><?php echo esc_html( $required_version ); ?></td>
									<td><?php echo esc_html( $installed_version ); ?></td>
									<td>
										<?php
										echo $is_compatible // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
											? QSM_Admin_UI::badge( __( 'Compatible', 'quiz-master-next' ), 'success' )
											: QSM_Admin_UI::badge( __( 'Update Required', 'quiz-master-next' ), 'danger' );
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		}
		?>
		<div class="qsm-ui-migration-progress-wrap">
			<div class="qsm-ui-progress-label">
				<span><?php esc_html_e( 'Progress', 'quiz-master-next' ); ?></span>
				<span class="qsm-database-migration-progress-percent">0%</span>
			</div>
			<?php // qsm-database-migration-script.js sets the width and adds .qsm-migration-completed when done. ?>
			<div class="qsm-ui-progress qsm-database-migration-progress-bar" role="presentation">
				<span class="qsm-database-migration-progress qsm-ui-migration-bar" style="width: 0%;"></span>
			</div>
		</div>

		<div class="qsm-database-migration-status qsm-ui-migration-status" role="status" aria-live="polite"></div>
		<div class="qsm-database-migration-details qsm-ui-migration-details"></div>
		<div class="qsm-ui-row">
			<button type="submit" id="qsm-database-start-migration" class="qsm-database-migration-button qsm-ui-btn qsm-ui-btn-primary" <?php echo $has_block ? 'disabled="disabled" aria-disabled="true"' : ''; ?>>
				<?php echo QSM_Admin_UI::icon( 'download', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
				<?php echo esc_html__( 'Start Migration', 'quiz-master-next' ); ?>
			</button>
		</div>
	</form>
	<?php
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	?>
	<div class="qsm-popup qsm-popup-slide qsm-standard-popup qsm-ui-popup" id="qsm-migration-confirm-popup" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container qsm-ui" role="dialog" aria-modal="true" aria-labelledby="qsm-migration-confirm-popup-title">
				<header class="qsm-popup__header qsm-ui-text">
					<div class="qsm-ui-confirm-head">
						<div class="qsm-ui-modal-icon is-warning"><?php echo QSM_Admin_UI::icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></div>
						<h2 class="qsm-popup__title" id="qsm-migration-confirm-popup-title"><?php esc_html_e( 'Start the migration?', 'quiz-master-next' ); ?></h2>
					</div>
					<a class="qsm-popup__close" href="javascript:void(0)" aria-label="<?php esc_attr_e( 'Close', 'quiz-master-next' ); ?>" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content qsm-ui-text">
					<p><?php echo esc_html__( 'Are you sure? After migration, any quiz results submitted on QSM 11 won’t be accessible if you downgrade to an earlier version. Your existing data will remain safe, but it’s recommended to take a database backup before proceeding.', 'quiz-master-next' ); ?></p>
				</main>
				<footer class="qsm-popup__footer">
					<button type="button" class="qsm-ui-btn qsm-ui-foot-left" data-micromodal-close><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
					<button type="button" class="qsm-ui-btn qsm-ui-btn-primary qsm-migration-confirm-start"><?php esc_html_e( 'Start Migration', 'quiz-master-next' ); ?></button>
				</footer>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Migration tools.
 *
 * @return void
 */
function qsm_tools_migration_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	qsm_migration_database_callback();
}


/**
 * Generates all of the quiz tools that are used
 *
 * @return void
 * @since 6.2.0
 */
function qsm_generate_quiz_tools() {
	if ( ! current_user_can( 'delete_others_qsm_quizzes' ) ) {
		return;
	}
	// Check the active tab
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'qsm_tools_page_audit_trail';
	global $mlwQuizMasterNext;
	$tools_tabs = array(
		'qsm_tools_page_audit_trail'       => array(
			'label'    => __( 'Audit Trail', 'quiz-master-next' ),
			'icon'     => 'list',
			'callback' => 'qsm_audit_box',
		),
		'qsm_tools_page_quiz_setting'      => array(
			'label'    => __( 'Deleted Quiz', 'quiz-master-next' ),
			'icon'     => 'quiz',
			'callback' => 'qsm_restore_function',
		),
		'qsm_tools_page_questions_setting' => array(
			'label'    => __( 'Deleted Questions', 'quiz-master-next' ),
			'icon'     => 'text',
			'callback' => 'qsm_get_deleted_questions_records',
		),
		'qsm_tools_page_results_setting'   => array(
			'label'    => __( 'Deleted Results', 'quiz-master-next' ),
			'icon'     => 'chart',
			'callback' => 'qsm_get_deleted_results_records',
		),
		'qsm_tools_page_migration'         => array(
			'label'    => __( 'Migration', 'quiz-master-next' ),
			'icon'     => 'download',
			'callback' => 'qsm_tools_migration_settings',
		),
	);
	if ( ! isset( $tools_tabs[ $active_tab ] ) ) {
		$active_tab = '';
	}

	// The tools handle their form posts while they render and add alerts then, so render first and print the alerts above.
	ob_start();
	if ( $active_tab ) {
		call_user_func( $tools_tabs[ $active_tab ]['callback'] );
	}
	$tab_content = ob_get_clean();
	?>
	<div class="wrap qsm-tools-page-wrapper qsm-ui">
		<div class="qsm-ui-page-header qsm-ui-text">
			<div>
				<h1 class="qsm-ui-h1"><?php esc_html_e( 'QSM Tools', 'quiz-master-next' ); ?></h1>
				<p class="qsm-ui-subtitle"><?php esc_html_e( 'Review the audit trail, restore or permanently remove deleted items, and migrate the database.', 'quiz-master-next' ); ?></p>
			</div>
		</div>
		<hr class="wp-header-end">
		<nav class="qsm-ui-page-tabs-nav" aria-label="<?php esc_attr_e( 'Tools', 'quiz-master-next' ); ?>">
			<div class="qsm-ui-tabs qsm-ui-page-tabs">
				<?php foreach ( $tools_tabs as $slug => $tab ) { ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=qsm_quiz_tools&tab=' . $slug ) ); ?>" class="qsm-ui-tab"<?php echo $slug === $active_tab ? ' aria-current="page"' : ''; ?>>
						<?php echo QSM_Admin_UI::icon( $tab['icon'], 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
				<?php } ?>
			</div>
		</nav>
		<div class="qsm-alerts">
			<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
		</div>
		<div class="qsm-ui-tools-panel">
			<?php echo $tab_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each tool escapes its own output. ?>
		</div>
		<div style="clear:both"></div>

		<?php qsm_show_adverts(); ?>

	</div>
	<?php
}

/**
 * Previous / next links under a Tools table.
 *
 * @since 11.3.0
 * @param array $args {
 *     @type string $url   Page URL without the page parameter.
 *     @type string $param Query parameter holding the 0-based page.
 *     @type int    $page  Current 0-based page.
 *     @type int    $total Total rows.
 *     @type int    $limit Rows per page.
 *     @type int    $shown Rows on this page.
 * }
 * @return void
 */
function qsm_tools_pagination( $args ) {
	$page  = max( 0, intval( $args['page'] ) );
	$total = intval( $args['total'] );
	$limit = max( 1, intval( $args['limit'] ) );
	$first = $total ? $page * $limit + 1 : 0;
	$last  = $page * $limit + intval( $args['shown'] );
	$prev  = $page > 0;
	$next  = $total > ( $page + 1 ) * $limit;
	?>
	<div class="qsm-ui-pagination">
		<span>
			<?php
			/* translators: 1: first row number, 2: last row number, 3: total rows */
			echo esc_html( sprintf( __( 'Showing %1$s–%2$s of %3$s', 'quiz-master-next' ), number_format_i18n( $first ), number_format_i18n( $last ), number_format_i18n( $total ) ) );
			?>
		</span>
		<?php if ( $prev || $next ) { ?>
			<div class="qsm-ui-row">
				<?php
				if ( $prev ) {
					echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
						__( 'Previous', 'quiz-master-next' ),
						array(
							'size'  => 'sm',
							'href'  => add_query_arg( $args['param'], $page - 1, $args['url'] ),
							'attrs' => array( 'id' => 'prev_page' ),
						)
					);
				}
				if ( $next ) {
					echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
						__( 'Next', 'quiz-master-next' ),
						array(
							'size'  => 'sm',
							'href'  => add_query_arg( $args['param'], $page + 1, $args['url'] ),
							'attrs' => array( 'id' => 'next_page' ),
						)
					);
				}
				?>
			</div>
		<?php } ?>
	</div>
	<?php
}

/**
 * Kit confirm (O-02) on a micromodal popup. The Tools JS opens it by id, writes the
 * message into $message_class and submits the form from the confirm button.
 *
 * @since 11.3.0
 * @param array $args id, title, message, message_class, note, confirm (label), confirm_class.
 * @return void
 */
function qsm_tools_confirm_popup( $args ) {
	?>
	<div class="qsm-popup qsm-popup-slide qsm-standard-popup qsm-ui-popup qsm-ui-tools-confirm" id="<?php echo esc_attr( $args['id'] ); ?>" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container qsm-ui" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $args['id'] ); ?>-title">
				<header class="qsm-popup__header qsm-ui-text">
					<div class="qsm-ui-confirm-head">
						<div class="qsm-ui-modal-icon"><?php echo QSM_Admin_UI::icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></div>
						<h2 class="qsm-popup__title" id="<?php echo esc_attr( $args['id'] ); ?>-title"><?php echo esc_html( $args['title'] ); ?></h2>
					</div>
					<a class="qsm-popup__close" href="javascript:void(0)" aria-label="<?php esc_attr_e( 'Close', 'quiz-master-next' ); ?>" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content qsm-ui-text">
					<p class="<?php echo esc_attr( $args['message_class'] ); ?>"><?php echo esc_html( $args['message'] ); ?></p>
					<?php if ( ! empty( $args['note'] ) ) { ?>
						<p class="qsm-ui-small"><?php echo esc_html( $args['note'] ); ?></p>
					<?php } ?>
				</main>
				<footer class="qsm-popup__footer">
					<button type="button" class="qsm-ui-btn qsm-ui-foot-left" data-micromodal-close><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
					<button type="button" class="qsm-ui-btn qsm-ui-btn-danger <?php echo esc_attr( $args['confirm_class'] ); ?>"><?php echo QSM_Admin_UI::icon( 'trash', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?><?php echo esc_html( $args['confirm'] ); ?></button>
				</footer>
			</div>
		</div>
	</div>
	<?php
}
/**
 * Allows the admin to restore a deleted quiz
 *
 * @return void
 * @since 6.2.0
 */
function qsm_restore_function() {
	global $wpdb;

	// Checks if form was submitted.
	if ( isset( $_POST['restore_quiz_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['restore_quiz_nonce'] ) ), 'restore_quiz_nonce' ) && isset( $_POST['restore_quiz'] ) ) {

		if ( isset($_POST['qsm_delete_permanent_quiz']) ) {
			global $mlwQuizMasterNext;
			$quiz_id     = sanitize_text_field( wp_unslash( $_POST['restore_quiz'] ) );
			$quiz_id     = intval( str_replace( 'QID', '', $quiz_id ) );
			do_action( 'qsm_before_delete_quiz', $quiz_id );
			$quiz_name   = isset( $_POST['delete_quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_quiz_name'] ) ) : '';
			$mlwQuizMasterNext->quizCreator->delete_quiz( $quiz_id, $quiz_name );
			?>
			<?php echo QSM_Admin_UI::notice( esc_html__( 'Quiz Has Been Deleted!', 'quiz-master-next' ), array( 'type' => 'success' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
			<?php
		} elseif ( isset($_POST['qsm_restore_quiz']) ) {
			$restore = $wpdb->update(
				$wpdb->prefix . 'mlw_quizzes',
				array(
					'deleted' => 0,
				),
				array(
					'quiz_id' => intval( $_POST['restore_quiz'] ),
				),
				array(
					'%d',
				),
				array(
					'%d',
				)
			);
			if ( ! $restore ) {
				?>
				<?php echo QSM_Admin_UI::notice( esc_html__( 'There has been an error! Please try again.', 'quiz-master-next' ), array( 'type' => 'danger' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
				<?php
			} else {
				// Restores the quiz post type for the quiz.
				$my_query = new WP_Query(
					array(
						'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' ),
						'post_type'   => 'qsm_quiz',
						'meta_key'    => 'quiz_id',
						'meta_value'  => intval( $_POST['restore_quiz'] ),
					)
				);
				if ( $my_query->have_posts() ) {
					while ( $my_query->have_posts() ) {
						$my_query->the_post();
						$my_post = array(
							'ID'          => get_the_ID(),
							'post_status' => 'publish',
						);
						wp_update_post( $my_post );
					}
				}
				wp_reset_postdata();
				?>
				<?php echo QSM_Admin_UI::notice( esc_html__( 'Quiz Has Been Restored!', 'quiz-master-next' ), array( 'type' => 'success' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
				<?php
			}
		}
	}
	$quizzes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 1" );
	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'       => __( 'Deleted Quizzes', 'quiz-master-next' ),
			'description' => __( 'Restore a deleted quiz, or remove it and its data permanently.', 'quiz-master-next' ),
			'actions'     => $quizzes ? QSM_Admin_UI::badge( number_format_i18n( count( $quizzes ) ), '', false ) : '',
			'class'       => 'qsm-ui-tools-card',
		)
	);
	if ( empty( $quizzes ) ) {
		echo QSM_Admin_UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'icon'  => 'quiz',
				'title' => __( 'No deleted quizzes found!!', 'quiz-master-next' ),
				'text'  => __( 'Quizzes you delete show up here, so you can restore them.', 'quiz-master-next' ),
			)
		);
	} else {
		?>
		<form action='' method="post" id="qsm-tools-restore-quiz-form" class="qsm-ui-text">
			<?php wp_nonce_field( 'restore_quiz_nonce', 'restore_quiz_nonce' ); ?>
			<input type="hidden" name="qsm_delete_from_db" value="1">
			<label class="qsm-ui-field-label" for="qsm-tools-restore-quiz"><?php esc_html_e( 'Choose a quiz in the drop down and then click the button to restore or delete permanent quiz.', 'quiz-master-next' ); ?></label>
			<div class="qsm-ui-row qsm-ui-tools-restore-row">
				<select name="restore_quiz" id="qsm-tools-restore-quiz" class="qsm-ui-select">
					<?php
					foreach ( $quizzes as $quiz ) {
						?>
						<option value="<?php echo esc_attr( $quiz->quiz_id ); ?>"><?php echo wp_kses_post( $quiz->quiz_name ); ?></option>
						<?php
					}
					?>
				</select>
				<button type="submit" class="qsm-ui-btn qsm-ui-btn-primary" name="qsm_restore_quiz"><?php echo QSM_Admin_UI::icon( 'up', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?><?php esc_html_e( 'Restore Quiz', 'quiz-master-next' ); ?></button>
				<button type="submit" class="qsm-ui-btn qsm-ui-btn-danger-ghost qsm-tools-delete-permanent-quiz" name="qsm_delete_permanent_quiz"><?php echo QSM_Admin_UI::icon( 'trash', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?><?php esc_html_e( 'Delete Permanent', 'quiz-master-next' ); ?></button>
			</div>
		</form>
		<?php
		qsm_tools_confirm_popup(
			array(
				'id'            => 'qsm-delete-quiz-tools-page-popup',
				'title'         => __( 'Delete this quiz permanently?', 'quiz-master-next' ),
				'message'       => __( 'The quiz will be removed from the database.', 'quiz-master-next' ),
				'message_class' => 'qsm-tools-page-delete-quiz-message',
				'note'          => __( 'This cannot be undone.', 'quiz-master-next' ),
				'confirm'       => __( 'Delete Permanent', 'quiz-master-next' ),
				'confirm_class' => 'qsm-delete-quiz-tools-page-btn',
			)
		);
	}
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
}

/**
 * Creates the tools page that is used to make audits on the quizzes.
 *
 * @return void
 * @since 6.2.0
 */
function qsm_audit_box() {
	global $wpdb;
	$table_limit = 30;
	$audit_total = (int) $wpdb->get_var( "SELECT COUNT(trail_id) FROM {$wpdb->prefix}mlw_qm_audit_trail" );

	// 0-based page. The old links started at -1 and always read from row 30 after the first page.
	$page         = isset( $_GET['audit_page'] ) ? max( 0, intval( $_GET['audit_page'] ) ) : 0;
	$begin        = $page * $table_limit;
	$audit_trails = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_qm_audit_trail ORDER BY trail_id DESC LIMIT %d, %d", $begin, $table_limit ) );
	wp_localize_script( 'qsm_admin_js', 'qsm_tools_page', array(
		'qsm_delete_audit_logs' => esc_html__( 'Are you sure you want to delete this record? You will not be able to recover this data!', 'quiz-master-next' ),
		'nonce'                 => wp_create_nonce( 'qsm_tools_' . get_current_user_id() ),
	) );

	$actions  = '<div class="qsm-ui-row audit_buttons">';
	$actions .= QSM_Admin_UI::button(
		__( 'Export', 'quiz-master-next' ),
		array(
			'icon'  => 'download',
			'size'  => 'sm',
			'class' => 'btn_export',
			'attrs' => array(
				'id'    => 'btn_export',
				'title' => __( 'Export', 'quiz-master-next' ),
			),
		)
	);
	$actions .= QSM_Admin_UI::button(
		__( 'Clear Audit', 'quiz-master-next' ),
		array(
			'icon'    => 'trash',
			'size'    => 'sm',
			'variant' => 'danger-ghost',
			'class'   => 'btn_clear_logs',
			'attrs'   => array(
				'id'    => 'btn_clear_logs',
				'title' => __( 'Clear Logs', 'quiz-master-next' ),
			),
		)
	);
	$actions .= '</div>';

	echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
		array(
			'title'       => __( 'Audit Trail', 'quiz-master-next' ),
			/* translators: %s: number of actions */
			'description' => sprintf( esc_html__( 'Total actions since QSM installed: %s', 'quiz-master-next' ), '<strong>' . esc_html( number_format_i18n( $audit_total ) ) . '</strong>' ),
			'actions'     => $actions,
			'class'       => 'qsm-ui-tools-card',
		)
	);
	if ( empty( $audit_trails ) ) {
		echo QSM_Admin_UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
			array(
				'icon'  => 'list',
				'title' => __( 'No data found!!', 'quiz-master-next' ),
				'text'  => __( 'Changes made to quizzes are recorded here.', 'quiz-master-next' ),
			)
		);
	} else {
		?>
		<div class="qsm-ui-table-wrap">
			<table class="qsm-ui-table qsm-ui-text">
				<thead>
					<tr>
						<th class="qsm-ui-col-id"><?php esc_html_e( 'ID', 'quiz-master-next' ); ?></th>
						<th><?php esc_html_e( 'User', 'quiz-master-next' ); ?></th>
						<th><?php esc_html_e( 'Action', 'quiz-master-next' ); ?></th>
						<th id="quiz_name"><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?></th>
						<th><?php esc_html_e( 'Time', 'quiz-master-next' ); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php foreach ( $audit_trails as $audit ) { ?>
						<tr>
							<td class="qsm-ui-col-id"><?php echo esc_html( $audit->trail_id ); ?></td>
							<td><?php echo esc_html( $audit->action_user ); ?></td>
							<td>
								<?php if ( ! empty( $audit->form_data ) ) { ?>
									<a href="javascript:void(0)" class="qsm_audit_data qsm-ui-cell-title" data-auditid="<?php echo esc_attr( $audit->form_data ); ?>"><?php echo esc_html( $audit->action ); ?></a>
									<?php
								} else {
									echo esc_html( $audit->action );
								}
								?>
							</td>
							<td>
								<?php echo esc_html( $audit->quiz_name ); ?>
								<span class="qsm-ui-cell-sub"><?php esc_html_e( 'ID:', 'quiz-master-next' ); ?> <?php echo esc_html( $audit->quiz_id ); ?></span>
							</td>
							<td><?php echo esc_html( $audit->time ); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
		qsm_tools_pagination(
			array(
				'url'   => admin_url( 'admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_audit_trail' ),
				'param' => 'audit_page',
				'page'  => $page,
				'total' => $audit_total,
				'limit' => $table_limit,
				'shown' => count( $audit_trails ),
			)
		);
	}
	echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	?>
	<div class="qsm-popup qsm-popup-slide qsm-ui-popup is-lg" id="qsm_fetch_audit_data" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container qsm-ui" role="dialog" aria-modal="true" aria-labelledby="qsm-audit-data-title">
				<header class="qsm-popup__header qsm-ui-text">
					<h3 class="qsm-popup__title" id="qsm-audit-data-title"><?php esc_html_e( 'Settings', 'quiz-master-next' ); ?></h3>
					<a class="qsm-popup__close" href="javascript:void(0)" aria-label="<?php esc_attr_e( 'Close', 'quiz-master-next' ); ?>" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content">
					<div class="qsm_setting__data qsm-ui-audit-data">
						<p></p>
					</div>
				</main>
			</div>
		</div>
	</div>
	<?php
	qsm_tools_confirm_popup(
		array(
			'id'            => 'qsm-clear-audit-tools-page-popup',
			'title'         => __( 'Clear the audit trail?', 'quiz-master-next' ),
			/* translators: %s: number of actions */
			'message'       => sprintf( __( 'All %s recorded actions will be deleted.', 'quiz-master-next' ), number_format_i18n( $audit_total ) ),
			'message_class' => 'qsm-tools-page-clear-audit-message',
			'note'          => __( 'You will not be able to recover this data. Export it first if you need a copy.', 'quiz-master-next' ),
			'confirm'       => __( 'Clear Audit', 'quiz-master-next' ),
			'confirm_class' => 'qsm-clear-audit-tools-page-btn',
		)
	);
}

function qsm_get_deleted_questions_records() {
    global $wpdb, $mlwQuizMasterNext;

    if ( isset($_POST['qsm_delete_selected_questions_nonce_field']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['qsm_delete_selected_questions_nonce_field'])), 'qsm_delete_selected_questions_nonce') ) {
        $current_action = isset( $_POST["qsm_tools_action_name"] ) ? sanitize_text_field( wp_unslash( $_POST['qsm_tools_action_name'] ) ) : "";

        // Handle Selected Questions Deletion
        if ( 'selected' == $current_action && isset($_POST['delete_questions']) && ! empty($_POST['delete_questions']) ) {
            $delete_ids = array_map('absint', wp_unslash($_POST['delete_questions']));

            if ( ! empty($delete_ids) ) {
                $query = $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}mlw_questions WHERE question_id IN (" . implode(',', array_fill(0, count($delete_ids), '%d')) . ")",
                    $delete_ids
                );
                $wpdb->query($query);
                $mlwQuizMasterNext->alertManager->newAlert(__('Selected questions have been deleted.', 'quiz-master-next'), 'success');
            }
        }

        // Handle Delete All Questions
        if ( 'all' == $current_action ) {
            $query = $wpdb->prepare("
                DELETE q FROM {$wpdb->prefix}mlw_questions q
                LEFT JOIN {$wpdb->prefix}mlw_quizzes quiz
                    ON q.quiz_id = quiz.quiz_id
                WHERE q.deleted = %d OR quiz.quiz_id IS NULL",
                1
            );

            $wpdb->query($query);
            $mlwQuizMasterNext->alertManager->newAlert(__('All deleted questions have been removed.', 'quiz-master-next'), 'success');
        }
    }

    $table_limit = 30;

    $questions_total = $wpdb->get_var("SELECT COUNT(q.question_id) FROM {$wpdb->prefix}mlw_questions q LEFT JOIN {$wpdb->prefix}mlw_quizzes quiz ON q.quiz_id = quiz.quiz_id WHERE q.deleted = 1 OR quiz.quiz_id IS NULL");

    $page = isset($_GET['deleted_questions_page']) ? max(0, intval($_GET['deleted_questions_page'])) : 0;
    $begin = $page * $table_limit;
    $filter_order = isset($_POST['qsm_deleted_question_filter']) ? sanitize_text_field(wp_unslash($_POST['qsm_deleted_question_filter'])) : '';
    $order_by = ( 'asc' === $filter_order || 'desc' === $filter_order) ? strtoupper($filter_order) : 'ASC';

    // Secure query execution using prepare
    $query = $wpdb->prepare(
        "SELECT q.*, quiz.quiz_id AS quiz_quiz_id FROM {$wpdb->prefix}mlw_questions q LEFT JOIN {$wpdb->prefix}mlw_quizzes quiz
        ON q.quiz_id = quiz.quiz_id WHERE q.deleted = 1 OR quiz.quiz_id IS NULL
        ORDER BY q.question_id ". esc_sql( $order_by ) . " LIMIT %d, %d",
        $begin,
        $table_limit
    );

    $questions = $wpdb->get_results($query);

    echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
        array(
            'title'       => __( 'Deleted Questions', 'quiz-master-next' ),
            'description' => __( 'List of Questions removed from the quizzes', 'quiz-master-next' ),
            'actions'     => QSM_Admin_UI::badge( sprintf( /* translators: %s: number of questions */ __( 'Total Deleted Questions: %s', 'quiz-master-next' ), number_format_i18n( $questions_total ) ), '', false ),
            'class'       => 'qsm-ui-tools-card questions_pagination',
        )
    );
    ?>
    <form action="" method="post" id="qsm-tools-delete-questions-form" class="qsm-ui-text">
        <input type="hidden" name="qsm_tools_action_name" class="qsm-tools-delete-questions-action-name" value="" >
        <?php if ( $questions ) { ?>
            <div class="qsm-ui-table-toolbar qsm-deleted-question-options-wrap">
                <div class="qsm-ui-row">
                    <label class="qsm-ui-sr" for="qsm-deleted-question-filter"><?php esc_html_e( 'Sort By Question ID', 'quiz-master-next' ); ?></label>
                    <select name="qsm_deleted_question_filter" id="qsm-deleted-question-filter" class="qsm-ui-select qsm-ui-input-sm">
                        <option value="asc" <?php selected( $filter_order, 'asc' ); ?>><?php esc_html_e( 'Question ID: Ascending', 'quiz-master-next' ); ?></option>
                        <option value="desc" <?php selected( $filter_order, 'desc' ); ?>><?php esc_html_e( 'Question ID: Descending', 'quiz-master-next' ); ?></option>
                    </select>
                    <button type="submit" class="qsm-ui-btn qsm-ui-btn-sm"><?php esc_html_e( 'Apply Filter', 'quiz-master-next' ); ?></button>
                </div>
                <div class="qsm-ui-row">
                    <button data-actiontype="selected" type="submit" name="delete_selected" class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-danger-ghost qsm-tools-delete-selected-questions"
                        data-message="<?php esc_attr_e( 'Are you sure you want to delete the selected questions?', 'quiz-master-next' ); ?>">
                        <?php esc_html_e( 'Delete Selected', 'quiz-master-next' ); ?>
                    </button>
                    <button data-actiontype="all" type="submit" name="delete_all_questions" class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-danger qsm-tools-delete-all-questions"
                        data-message="<?php esc_attr_e( 'Are you sure you want to delete all deleted questions?', 'quiz-master-next' ); ?>">
                        <?php echo QSM_Admin_UI::icon( 'trash', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
                        <?php esc_html_e( 'Delete All Questions', 'quiz-master-next' ); ?>
                    </button>
                </div>
            </div>
            <?php wp_nonce_field( 'qsm_delete_selected_questions_nonce', 'qsm_delete_selected_questions_nonce_field' ); ?>
            <div class="qsm-ui-table-wrap">
                <table class="qsm-ui-table">
                    <thead>
                        <tr>
                            <th class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" class="qsm-select-all-deleted-question" id="qsm-select-all-deleted-question" /><span class="qsm-ui-sr"><?php esc_html_e( 'Select all', 'quiz-master-next' ); ?></span></label></th>
                            <th class="qsm-ui-col-id"><?php esc_html_e( 'Question ID', 'quiz-master-next' ); ?></th>
                            <th class="qsm-ui-col-id"><?php esc_html_e( 'Quiz ID', 'quiz-master-next' ); ?></th>
                            <th><?php esc_html_e( 'Question Name', 'quiz-master-next' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-list">
                    <?php
                    foreach ( $questions as $row ) {
                        $settings       = maybe_unserialize( $row->question_settings );
                        $question_title = isset( $settings['question_title'] ) ? $settings['question_title'] : '';
                        ?>
                        <tr>
                            <td class="qsm-ui-col-check">
                                <label class="qsm-ui-check"><input type="checkbox" class="qsm-deleted-question-checkbox" name="delete_questions[]" value="<?php echo esc_attr( $row->question_id ); ?>" /><span class="qsm-ui-sr"><?php echo esc_html( sprintf( /* translators: %s: question ID */ __( 'Select question %s', 'quiz-master-next' ), $row->question_id ) ); ?></span></label>
                            </td>
                            <td class="qsm-ui-col-id"><?php echo esc_html( $row->question_id ); ?></td>
                            <td class="qsm-ui-col-id"><?php echo esc_html( $row->quiz_id ); ?></td>
                            <td><?php echo esc_html( $question_title ); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php
            qsm_tools_pagination(
                array(
                    'url'   => admin_url( 'admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_questions_setting' ),
                    'param' => 'deleted_questions_page',
                    'page'  => $page,
                    'total' => $questions_total,
                    'limit' => $table_limit,
                    'shown' => count( $questions ),
                )
            );
        } else {
            echo QSM_Admin_UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
                array(
                    'icon'  => 'text',
                    'title' => __( 'No Questions found!!', 'quiz-master-next' ),
                    'text'  => __( 'Questions removed from quizzes show up here.', 'quiz-master-next' ),
                )
            );
        }
        ?>
    </form>
    <?php
    echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
    qsm_tools_confirm_popup(
        array(
            'id'            => 'qsm-delete-questions-tools-page-popup',
            'title'         => __( 'Delete Questions', 'quiz-master-next' ),
            'message'       => __( 'Are you sure you want to delete these Questions?', 'quiz-master-next' ),
            'message_class' => 'qsm-tools-page-delete-questions-message',
            'note'          => __( 'This cannot be undone.', 'quiz-master-next' ),
            'confirm'       => __( 'Delete Questions', 'quiz-master-next' ),
            'confirm_class' => 'qsm-delete-questions-tools-page-btn',
        )
    );
}


function qsm_get_deleted_results_records() {
    global $wpdb, $mlwQuizMasterNext;
    if (
        isset($_POST['qsm_delete_selected_results_nonce_field']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['qsm_delete_selected_results_nonce_field'])), 'qsm_delete_selected_results_nonce')
    ) {
        $current_action = isset( $_POST["qsm_tools_action_name"] ) ? sanitize_text_field( wp_unslash( $_POST['qsm_tools_action_name'] ) ) : "";

        if ( 'selected' == $current_action && ! empty($_POST['quiz_results']) ) {
            $selected_ids = array_map('absint', (array) wp_unslash($_POST['quiz_results']));
            if ( ! empty($selected_ids) ) {
                $query = $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}mlw_results WHERE result_id IN (" . implode(',', array_fill(0, count($selected_ids), '%d')) . ")",
                    $selected_ids
                );
                $wpdb->query($query);
                $mlwQuizMasterNext->alertManager->newAlert(__('Selected results have been deleted.', 'quiz-master-next'), 'success');
            }
        }

        if ( 'all' == $current_action ) {
            // Delete all results marked as deleted
            $wpdb->query(
                $wpdb->prepare("DELETE FROM {$wpdb->prefix}mlw_results WHERE deleted = %d", 1)
            );

            // Show success message
            $mlwQuizMasterNext->alertManager->newAlert(__('All deleted results have been removed.', 'quiz-master-next'), 'success');
        }
}

    $table_limit = 30;
    // Fetch deleted results
    $results_total = $wpdb->get_var("SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted = 1");

    // Determine current page
    $page = isset($_GET['deleted_results_page']) ? max(0, intval($_GET['deleted_results_page'])) : 0;
    $begin = $page * $table_limit;

    // Secure query execution using prepare
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted = %d ORDER BY result_id DESC LIMIT %d, %d",
        1,
        $begin,
        $table_limit
    );
    $results = $wpdb->get_results($query);

    echo QSM_Admin_UI::card_open( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
        array(
            'title'       => __( 'Deleted Results', 'quiz-master-next' ),
            'description' => __( 'List of deleted Quiz Results from the quiz result page', 'quiz-master-next' ),
            'actions'     => QSM_Admin_UI::badge( sprintf( /* translators: %s: number of results */ __( 'Total Deleted Results: %s', 'quiz-master-next' ), number_format_i18n( $results_total ) ), '', false ),
            'class'       => 'qsm-ui-tools-card results_pagination',
        )
    );
    ?>
    <form action="" method="post" id="qsm-tools-delete-results-form" class="qsm-ui-text">
        <input type="hidden" name="qsm_tools_action_name" class="qsm-tools-delete-results-action-name" value="" >
        <?php if ( $results ) { ?>
            <div class="qsm-ui-table-toolbar qsm-deleted-question-options-wrap">
                <div></div>
                <div class="qsm-ui-row">
                    <button data-actiontype="selected" type="submit" name="delete_selected" class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-danger-ghost qsm-tools-delete-selected-results"
                        data-message="<?php esc_attr_e( 'Are you sure you want to delete the selected results?', 'quiz-master-next' ); ?>">
                        <?php esc_html_e( 'Delete Selected', 'quiz-master-next' ); ?>
                    </button>
                    <button data-actiontype="all" type="submit" name="delete_all_results" class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-danger qsm-tools-delete-all-results"
                        data-message="<?php esc_attr_e( 'Are you sure you want to delete all deleted results?', 'quiz-master-next' ); ?>">
                        <?php echo QSM_Admin_UI::icon( 'trash', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?>
                        <?php esc_html_e( 'Delete All Deleted Results', 'quiz-master-next' ); ?>
                    </button>
                </div>
            </div>
            <?php wp_nonce_field( 'qsm_delete_selected_results_nonce', 'qsm_delete_selected_results_nonce_field' ); ?>
            <div class="qsm-ui-table-wrap">
                <table class="qsm-ui-table">
                    <thead>
                        <tr>
                            <th class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" class="qsm-select-all-deleted-result" id="qsm-select-all-deleted-result" /><span class="qsm-ui-sr"><?php esc_html_e( 'Select all', 'quiz-master-next' ); ?></span></label></th>
                            <th class="qsm-ui-col-id"><?php esc_html_e( 'Result ID', 'quiz-master-next' ); ?></th>
                            <th><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'quiz-master-next' ); ?></th>
                            <th><?php esc_html_e( 'IP Address', 'quiz-master-next' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-list">
                    <?php foreach ( $results as $row ) { ?>
                        <tr>
                            <td class="qsm-ui-col-check">
                                <label class="qsm-ui-check"><input type="checkbox" class="qsm-deleted-result-checkbox" name="quiz_results[]" value="<?php echo esc_attr( $row->result_id ); ?>" /><span class="qsm-ui-sr"><?php echo esc_html( sprintf( /* translators: %s: result ID */ __( 'Select result %s', 'quiz-master-next' ), $row->result_id ) ); ?></span></label>
                            </td>
                            <td class="qsm-ui-col-id"><?php echo esc_html( $row->result_id ); ?></td>
                            <td><?php echo esc_html( $row->quiz_name ); ?></td>
                            <td><?php echo esc_html( $row->email ); ?></td>
                            <td><?php echo esc_html( $row->user_ip ); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php
            qsm_tools_pagination(
                array(
                    'url'   => admin_url( 'admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_results_setting' ),
                    'param' => 'deleted_results_page',
                    'page'  => $page,
                    'total' => $results_total,
                    'limit' => $table_limit,
                    'shown' => count( $results ),
                )
            );
        } else {
            echo QSM_Admin_UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
                array(
                    'icon'  => 'chart',
                    'title' => __( 'No deleted results found!', 'quiz-master-next' ),
                    'text'  => __( 'Results deleted from the Results page show up here.', 'quiz-master-next' ),
                )
            );
        }
        ?>
    </form>
    <?php
    echo QSM_Admin_UI::card_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
    qsm_tools_confirm_popup(
        array(
            'id'            => 'qsm-delete-results-tools-page-popup',
            'title'         => __( 'Delete Results', 'quiz-master-next' ),
            'message'       => __( 'Are you sure you want to delete these results?', 'quiz-master-next' ),
            'message_class' => 'qsm-tools-page-delete-results-message',
            'note'          => __( 'This will permanently remove all associated data and metadata', 'quiz-master-next' ),
            'confirm'       => __( 'Delete Results', 'quiz-master-next' ),
            'confirm_class' => 'qsm-delete-results-tools-page-btn',
        )
    );
}
