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

	wp_enqueue_style(
		'qsm-database-migration',
		QSM_PLUGIN_CSS_URL . '/qsm-database-migration.css',
		array(),
		$mlwQuizMasterNext->version
	);

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
		'confirmMessage'      => __( 'Warning: After migration, any quiz results submitted on QSM 11 will not be accessible if you downgrade to an earlier version. Are you sure you want to proceed?', 'quiz-master-next' ),
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

	<div class="qsm-dashboard-help-center">
		<h3 class="qsm-dashboard-help-center-title"><?php echo esc_html__( 'Database Migration', 'quiz-master-next' ); ?></h3>
		<div class="qsm-database-migration-wrapper qsm-dashboard-page-common-style">

			<form id="qsm-database-migration-form" class="qsm-database-migration-form">
				<div class="qsm-migration-warning">
					<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/warning-message.png' ); ?>" alt="warning-message.png"/>
					<strong><?php echo esc_html__( 'Warning:', 'quiz-master-next' ); ?></strong>
					<?php echo esc_html__( 'After migration, any quiz results submitted on QSM 11 will not be accessible if you downgrade to an earlier version. We strongly recommend taking a full database backup before proceeding.', 'quiz-master-next' ); ?>
				</div>

				<div class="qsm-database-migration-progress-bar">
					<div class="qsm-database-migration-progress" style="width: 0%;"></div>
					<div class="qsm-database-migration-progress-percent">0%</div>
				</div>

				<div class="qsm-database-migration-status"></div>
				<div class="qsm-database-migration-details"></div>
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

				if ( ! empty( $compatibility_addons ) ) {
					$has_block = empty( $compatibility['allowed'] );
					?>
					<div class="qsm-migration-addon-compatibility">

						<?php if ( $has_block && ! empty( $compatibility['message'] ) ) { ?>
							<div class="qsm-migration-addon-compatibility-message">
								<?php echo wp_kses_post( $compatibility['message'] ); ?>
							</div>
						<?php } ?>

						<table class="qsm-migration-addon-compatibility-table widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Addon Name', 'quiz-master-next' ); ?></th>
									<th><?php esc_html_e( 'Required Version', 'quiz-master-next' ); ?></th>
									<th><?php esc_html_e( 'Installed Version', 'quiz-master-next' ); ?></th>
									<th><?php esc_html_e( 'Status', 'quiz-master-next' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $compatibility_addons as $addon ) :

									$name              = isset( $addon['name'] ) ? $addon['name'] : '';
									$required_version  = isset( $addon['required_addon_version'] ) ? $addon['required_addon_version'] : '';
									$installed_version = isset( $addon['installed_version'] ) && $addon['installed_version']
										? $addon['installed_version']
										: esc_html__( 'Not Installed', 'quiz-master-next' );
									$is_compatible     = ! empty( $addon['is_compatible'] );
									?>
									<tr class="<?php echo $is_compatible ? 'qsm-addon-compatible' : 'qsm-addon-not-compatible'; ?>">
										<td><?php echo esc_html( $name ); ?></td>
										<td><?php echo esc_html( $required_version ); ?></td>
										<td><?php echo esc_html( $installed_version ); ?></td>
										<td>
											<span class="qsm-migration-addon-compatibility-status">
												<?php
												echo $is_compatible
													? esc_html__( 'Compatible', 'quiz-master-next' )
													: esc_html__( 'Update Required', 'quiz-master-next' );
												?>
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

					</div>
					<?php
				}
				?>

				<button type="submit" id="qsm-database-start-migration" class="qsm-database-migration-button button button-primary" <?php echo empty( $compatibility['allowed'] ) ? 'disabled="disabled" aria-disabled="true"' : ''; ?> >
					<?php echo esc_html__( 'Start Migration', 'quiz-master-next' ); ?>
				</button>
			</form>
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

	global $wpdb;
	$results_table = $wpdb->prefix . 'mlw_results';
	$results_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$results_table}" );
	if ( 0 === $results_count ) {
		update_option( 'qsm_migration_results_processed', 1 );
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
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('QSM Tools', 'quiz-master-next'); ?></h1>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_audit_trail')); ?>" class="nav-tab <?php echo 'qsm_tools_page_audit_trail' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Audit Trail', 'quiz-master-next'); ?></a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_quiz_setting')); ?>" class="nav-tab <?php echo 'qsm_tools_page_quiz_setting' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Deleted Quiz', 'quiz-master-next'); ?></a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_questions_setting')); ?>" class="nav-tab <?php echo 'qsm_tools_page_questions_setting' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Deleted Questions', 'quiz-master-next'); ?></a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_results_setting')); ?>" class="nav-tab <?php echo 'qsm_tools_page_results_setting' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Deleted Results', 'quiz-master-next'); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_migration' ) ); ?>" class="nav-tab <?php echo 'qsm_tools_page_migration' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Migration', 'quiz-master-next' ); ?></a>
        </h2>
		<div class="qsm-alerts">
			<?php $mlwQuizMasterNext->alertManager->showAlerts() ?>
		</div>
    <?php
        // Handle callbacks based on the active tab
        if ( empty($_GET['tab']) || 'qsm_tools_page_audit_trail' === $active_tab ) {
            qsm_audit_box();
        }

        if ( ! empty($_GET['tab']) && 'qsm_tools_page_quiz_setting' === $active_tab ) {
            qsm_restore_function();
        }

        if ( ! empty($_GET['tab']) && 'qsm_tools_page_questions_setting' === $active_tab ) {
            qsm_get_deleted_questions_records();
        }

        if ( ! empty($_GET['tab']) && 'qsm_tools_page_results_setting' === $active_tab ) {
            qsm_get_deleted_results_records();
        }

		if ( ! empty( $_GET['tab'] ) && 'qsm_tools_page_migration' === $active_tab ) {
			qsm_tools_migration_settings();
		}
    ?>
	<div style="clear:both"></div>

	<?php qsm_show_adverts(); ?>

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
			<span style="color:green;"><?php esc_html_e( 'Quiz Has Been Deleted!', 'quiz-master-next' ); ?></span>
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
				<span style="color:red;"><?php esc_html_e( 'There has been an error! Please try again.', 'quiz-master-next' ); ?></span>
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
				<span style="color:green;"><?php esc_html_e( 'Quiz Has Been Restored!', 'quiz-master-next' ); ?></span>
				<?php
			}
		}
	}
	$quizzes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 1" );
	if ( empty( $quizzes ) ) { ?>
        <h3><?php esc_html_e( 'No deleted quizzes found!!', 'quiz-master-next' ); ?></h3>
    <?php } else { ?>
        <h3><?php esc_html_e( 'Choose a quiz in the drop down and then click the button to restore or delete permanent quiz.', 'quiz-master-next' ); ?></h3>
        <form action='' method="post">
            <?php wp_nonce_field( 'restore_quiz_nonce', 'restore_quiz_nonce' ); ?>
            <input type="hidden" name="qsm_delete_from_db" value="1">
            <select name="restore_quiz">
                <?php
                foreach ( $quizzes as $quiz ) {
                    ?>
                    <option value="<?php echo esc_attr( $quiz->quiz_id ); ?>"><?php echo wp_kses_post( $quiz->quiz_name ); ?></option>
                    <?php
                }
                ?>
            </select>
            <button class="button" name="qsm_restore_quiz"><?php esc_html_e( 'Restore Quiz', 'quiz-master-next' ); ?></button>
            <button class="button" name="qsm_delete_permanent_quiz"><?php esc_html_e( 'Delete Permanent', 'quiz-master-next' ); ?></button>
        </form>
    <?php } ?>
	<?php
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
	$audit_total = $wpdb->get_var( "SELECT COUNT(trail_id) FROM {$wpdb->prefix}mlw_qm_audit_trail" );

	// If user has gone to the next audit page, load current page and beginning.
	// Else, start at 0.
	if ( isset( $_GET['audit_page'] ) ) {
		$page  = intval( $_GET['audit_page'] ) + 1;
		$begin = $table_limit;
	} else {
		$page  = 0;
		$begin = 0;
	}
	$left         = $audit_total - ( $page * $table_limit );
	$audit_trails = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_qm_audit_trail ORDER BY trail_id DESC LIMIT %d, %d", $begin, $table_limit ) );
	?>
	<div class="audit_buttons">
		<p><?php esc_html_e( 'Total actions since QSM installed:', 'quiz-master-next' ); ?> <?php echo esc_html( $audit_total ); ?></p>
		<p><a class='button button-primary btn_export' id="btn_export" title='Export' ><?php esc_html_e( 'Export', 'quiz-master-next' ); ?></a>
		<a class='button button-primary btn_clear_logs' id="btn_clear_logs" title='Clear Logs' ><?php esc_html_e( 'Clear Audit', 'quiz-master-next' ); ?></a></p>
	</div>
	<?php

	// Determine which navigation to show.
	if ( $page > 0 ) {
		$previous = $page - 2;
		?>
		<a class="button" id="prev_page" href="?page=qsm_quiz_tools&&audit_page=<?php echo esc_attr( $previous ); ?>">
			<?php
			/* translators: %s: table limit */
			printf( esc_html__( 'Previous %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
			?>
		</a>
		<?php
		if ( $left > $table_limit ) {
			?>
			<a class="button" id="next_page" href="?page=qsm_quiz_tools&&audit_page=<?php echo esc_attr( $page ); ?>">
			<?php
			/* translators: %s: table limit */
			printf( esc_html__( 'Next %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
			?>
			</a>
			<?php
		}
	} elseif ( 0 == $page ) {
		if ( $left > $table_limit ) {
			?>
			<a class="button" id="next_page" href="?page=qsm_quiz_tools&&audit_page=<?php echo esc_attr( $page ); ?>">
				<?php
				/* translators: %s: table limit */
				printf( esc_html__( 'Next %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
				?>
			</a>
			<?php
		}
	} elseif ( $left < $table_limit ) {
		$previous = $page - 2;
		?>
		<a class="button" id="prev_page" href="?page=qsm_quiz_tools&&audit_page=<?php echo esc_attr( $previous ); ?>">
			<?php
			/* translators: %s: table limit */
			printf( esc_html__( 'Previous %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
			?>
		</a>
		<?php
	}
	?>
	<table class=widefat>
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'quiz-master-next' ); ?></th>
				<th><?php esc_html_e( 'User', 'quiz-master-next' ); ?></th>
				<th><?php esc_html_e( 'Action', 'quiz-master-next' ); ?></th>
				<th id="quiz_name"><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?></th>
				<th><?php esc_html_e( 'Time', 'quiz-master-next' ); ?></th>
			</tr>
		</thead>
		<tbody id="the-list">
			<?php
			wp_localize_script( 'qsm_admin_js', 'qsm_tools_page', array(
				'qsm_delete_audit_logs' => esc_html__( 'Are you sure you want to delete this record? You will not be able to recover this data!', 'quiz-master-next' ),
				'nonce'                 => wp_create_nonce( 'qsm_tools_' . get_current_user_id() ),
			) );
			$alternate = '';
			if ( ! empty( $audit_trails ) ) {
				foreach ( $audit_trails as $audit ) {
					if ( $alternate ) {
						$alternate = '';
					} else {
						$alternate = 'alternate';
					}
					?>
					<tr class="<?php echo esc_attr( $alternate ); ?>">
						<td><?php echo esc_html( $audit->trail_id ); ?></td>
						<td><?php echo esc_html( $audit->action_user ); ?></td>
						<td>
							<?php if ( ! empty( $audit->form_data ) ) { ?>
								<a href="javascript:void(0)" class="qsm_audit_data" data-auditid="<?php echo esc_html( $audit->form_data ); ?>"><?php echo esc_html( $audit->action ); ?></a>
								<?php
							} else {
								echo esc_html( $audit->action );
							}
							?>
						</td>
						<td><?php echo esc_html( $audit->quiz_name ); ?> [ <strong>ID:</strong> <?php echo esc_html( $audit->quiz_id ); ?> ] </td>
						<td><?php echo esc_html( $audit->time ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr class="<?php echo esc_attr( $alternate ); ?>">
						<td colspan="5"><?php esc_html_e( 'No data found!!', 'quiz-master-next' ); ?></td>
				</tr>
				<?php
			}

			?>
		</tbody>
	</table>
	<div class="qsm-popup qsm-popup-slide" id="qsm_fetch_audit_data" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
				<header class="qsm-popup__header">
					<h3 class="qsm-popup__title" id="modal-2-title">
					<?php esc_html_e( 'Settings', 'quiz-master-next' ); ?></h3>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<div class="qsm_setting__data">
					<p></p>
				</div>
			</div>
		</div>
	</div>
	<?php
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
    ?>
    <div class="questions_pagination">
        <p><?php esc_html_e('Total Deleted Questions:', 'quiz-master-next'); ?> <?php echo esc_html($questions_total); ?></p>
    </div>

    <?php
    // Determine which navigation to show
    $left = $questions_total - ($page * $table_limit);
    if ( $page > 0 ) {
        $previous = $page - 1;
        ?>
        <a class="button" id="prev_page" href="admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_questions_setting&deleted_questions_page=<?php echo esc_attr($previous); ?>">
            <?php echo esc_html__( 'Previous ', 'quiz-master-next' ) . esc_html( $table_limit ) . esc_html__( ' Questions', 'quiz-master-next' ); ?>
        </a>
        <?php
        if ( $left > $table_limit ) {
            ?>
            <a class="button" id="next_page" href="admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_questions_setting&deleted_questions_page=<?php echo esc_attr($page + 1); ?>">
                <?php echo esc_html__( 'Next ', 'quiz-master-next' ) . esc_html( $table_limit ) . esc_html__( ' Questions', 'quiz-master-next' ); ?>
            </a>
            <?php
        }
    } elseif ( $left > $table_limit ) {
        ?>
        <a class="button" id="next_page" href="admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_questions_setting&deleted_questions_page=<?php echo esc_attr($page + 1); ?>">
            <?php echo esc_html__( 'Next ', 'quiz-master-next' ) . esc_html( $table_limit ) . esc_html__( ' Questions', 'quiz-master-next' ); ?>
        </a>
        <?php
    }
    ?>
    <form action="" method="post" id="qsm-tools-delete-questions-form">
        <input type="hidden" name="qsm_tools_action_name" class="qsm-tools-delete-questions-action-name" value="" >
        <div class="qsm-deleted-question-options-wrap">
            <p><?php esc_html_e('List of Questions removed from the quizzes', 'quiz-master-next'); ?></p>
            <div>
                <div class="qsm-deleted-question-options-forms">
                    <?php if ( ! empty($questions) ) { ?>
                        <div>
                            <label for="qsm-deleted-question-filter"><?php esc_html_e('Sort By Question ID', 'quiz-master-next'); ?></label>
                            <select name="qsm_deleted_question_filter" id="qsm-deleted-question-filter">
                                <option value="asc" <?php selected($filter_order, 'asc'); ?>><?php esc_html_e('Ascending', 'quiz-master-next'); ?></option>
                                <option value="desc" <?php selected($filter_order, 'desc'); ?>><?php esc_html_e('Descending', 'quiz-master-next'); ?></option>
                            </select>
                            <button type="submit" class="button"><?php esc_html_e('Apply Filter', 'quiz-master-next'); ?></button>
                        </div>
                        <div>
                            <button data-actiontype="selected" type="submit" name="delete_selected" class="button qsm-tools-delete-selected-questions"
                                data-message="<?php esc_attr_e('Are you sure you want to delete the selected questions?', 'quiz-master-next'); ?>">
                                <?php esc_html_e('Delete Selected', 'quiz-master-next'); ?>
                            </button>
                        </div>
                        <div>
                            <button data-actiontype="all" type="submit" name="delete_all_questions" class="button-primary qsm-tools-delete-all-questions"
                                data-message="<?php esc_attr_e('Are you sure you want to delete all deleted questions?', 'quiz-master-next'); ?>">
                                <?php esc_html_e('Delete All Questions', 'quiz-master-next'); ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php
        if ( $questions ) {
            wp_nonce_field('qsm_delete_selected_questions_nonce', 'qsm_delete_selected_questions_nonce_field'); ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="qsm-select-all-deleted-question" id="qsm-select-all-deleted-question" /></th>
                        <th><?php esc_html_e('Question ID', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('Quiz ID', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('Question Name', 'quiz-master-next'); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                <?php foreach ( $questions as $row ) {
                    $settings = maybe_unserialize($row->question_settings);
                    $question_title = isset($settings['question_title']) ? $settings['question_title'] : ''; ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="qsm-deleted-question-checkbox" name="delete_questions[]" value="<?php echo esc_attr($row->question_id); ?>" />
                        </td>
                        <td><?php echo esc_html($row->question_id); ?></td>
                        <td><?php echo esc_html($row->quiz_id); ?></td>
                        <td><?php echo esc_html($question_title); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <table class="widefat">
                <tr>
                    <td colspan="4"><?php esc_html_e('No Questions found!!', 'quiz-master-next'); ?></td>
                </tr>
            </table>
        <?php } ?>
    </form>

    <div class="qsm-popup qsm-popup-slide qsm-standard-popup " id="qsm-delete-questions-tools-page-popup" aria-hidden="false"  style="display:none">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true">
                <header class="qsm-popup__header qsm-delete-result-page-popup-header">
                    <div class="qsm-popup__title qsm-upgrade-box-title" id="modal-2-title"></div>
                    <a class="qsm-popup__close qsm-popup-upgrade-close" aria-label="Close modal" data-micromodal-close></a>
                </header>
                <main class="qsm-popup__content" id="modal-2-content">
                    <div class="qsm-tools-page-delete-questions-message"><?php esc_html_e( 'Are you sure you want to delete these Questions?', 'quiz-master-next' ); ?></div>
                </main>
                <footer class="qsm-popup__footer">
                    <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
                    <button type="submit" class="qsm-popup__btn qsm-delete-questions-tools-page-btn"><span class="dashicons dashicons-warning"></span><?php esc_html_e( 'Delete Questions', 'quiz-master-next' ); ?></button>
                </footer>
			</div>
		</div>
	</div>
    <?php
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
    ?>

    <div class="results_pagination">
        <p><?php esc_html_e('Total Deleted Results:', 'quiz-master-next'); ?> <?php echo esc_html($results_total); ?></p>
    </div>

    <?php
    $left = $results_total - ($page * $table_limit);
    if ( $page > 0 ) {
        $previous = $page - 1;
        ?>
        <a class="button" id="prev_page" href="admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_results_setting&deleted_results_page=<?php echo esc_attr($previous); ?>">
            <?php echo esc_html__( 'Previous ', 'quiz-master-next' ) . esc_html( $table_limit ) . esc_html__( ' Results', 'quiz-master-next' ); ?>
        </a>
        <?php
        if ( $left > $table_limit ) {
            ?>
            <a class="button" id="next_page" href="admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_results_setting&deleted_results_page=<?php echo esc_attr($page + 1); ?>">
                <?php echo esc_html__( 'Next ', 'quiz-master-next' ) . esc_html( $table_limit ) . esc_html__( ' Results', 'quiz-master-next' ); ?>
            </a>
            <?php
        }
    } elseif ( $left > $table_limit ) {
        ?>
        <a class="button" id="next_page" href="admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_results_setting&deleted_results_page=<?php echo esc_attr($page + 1); ?>">
            <?php echo esc_html__( 'Next ', 'quiz-master-next' ) . esc_html( $table_limit ) . esc_html__( ' Results', 'quiz-master-next' ); ?>
        </a>
    <?php }

    ?>
    <form action="" method="post"  id="qsm-tools-delete-results-form">
        <input type="hidden" name="qsm_tools_action_name" class="qsm-tools-delete-results-action-name" value="" >
        <div class="qsm-deleted-question-options-wrap">
            <p><?php esc_html_e('List of deleted Quiz Results from the quiz result page', 'quiz-master-next'); ?></p>
            <div>
                <div class="qsm-deleted-question-options-forms">
                    <?php if ( ! empty($results) ) { ?>
                        <div>
                            <button data-actiontype="selected" type="submit" name="delete_selected" class="button qsm-tools-delete-selected-results"
                                data-message="<?php esc_attr_e('Are you sure you want to delete the selected results?', 'quiz-master-next'); ?>">
                                <?php esc_html_e('Delete Selected', 'quiz-master-next'); ?>
                            </button>
                        </div>
                        <div>
                            <button data-actiontype="all" type="submit" name="delete_all_results" class="button-primary qsm-tools-delete-all-results"
                                data-message="<?php esc_attr_e('Are you sure you want to delete all deleted results?', 'quiz-master-next'); ?>">
                                <?php esc_html_e('Delete All Deleted Results', 'quiz-master-next'); ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        if ( $results ) {
            wp_nonce_field('qsm_delete_selected_results_nonce', 'qsm_delete_selected_results_nonce_field'); ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="qsm-select-all-deleted-result" id="qsm-select-all-deleted-result" /></th>
                        <th><?php esc_html_e('Result ID', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('Quiz Name', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('Email', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('IP Address', 'quiz-master-next'); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                <?php
                foreach ( $results as $row ) {
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="qsm-deleted-result-checkbox" name="quiz_results[]" value="<?php echo esc_attr($row->result_id); ?>" />
                        </td>
                        <td><?php echo esc_html($row->result_id); ?></td>
                        <td><?php echo esc_html($row->quiz_name); ?></td>
                        <td><?php echo esc_html($row->email); ?></td>
                        <td><?php echo esc_html($row->user_ip); ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            ?>
            <table class="widefat">
                <tr>
                    <td colspan="5"><?php esc_html_e('No deleted results found!', 'quiz-master-next'); ?></td>
                </tr>
            </table>
            <?php
        }
        ?>
    </form>
    <div class="qsm-popup qsm-popup-slide qsm-standard-popup " id="qsm-delete-results-tools-page-popup" aria-hidden="false"  style="display:none">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true">
                <header class="qsm-popup__header qsm-delete-result-page-popup-header">
                    <div class="qsm-popup__title qsm-upgrade-box-title" id="modal-2-title"></div>
                    <a class="qsm-popup__close qsm-popup-upgrade-close" aria-label="Close modal" data-micromodal-close></a>
                </header>
                <main class="qsm-popup__content" id="modal-2-content">
                    <div class="qsm-tools-page-delete-results-message">
                        <?php esc_html_e( 'Are you sure you want to delete these results?', 'quiz-master-next' ); ?>
                        <br />
                        <em><?php esc_html_e( 'This will permanently remove all associated data and metadata', 'quiz-master-next' ); ?></em>
                    </div>
                </main>
                <footer class="qsm-popup__footer">
                    <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
                    <button type="submit" class="qsm-popup__btn qsm-delete-results-tools-page-btn"><span class="dashicons dashicons-warning"></span><?php esc_html_e( 'Delete Results', 'quiz-master-next' ); ?></button>
                </footer>
			</div>
		</div>
	</div>
    <?php
}
