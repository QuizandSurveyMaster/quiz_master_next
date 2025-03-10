<?php
/**
 * Generates the content for the tools page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'qsm_tools_page_audit_trail';

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('QSM Tools', 'text-domain'); ?></h1>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_audit_trail')); ?>" 
               class="nav-tab <?php echo $active_tab === 'qsm_tools_page_audit_trail' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Audit Trail', 'text-domain'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_quiz_setting')); ?>" 
               class="nav-tab <?php echo $active_tab === 'qsm_tools_page_quiz_setting' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Quiz Settings', 'text-domain'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_questions_setting')); ?>" 
               class="nav-tab <?php echo $active_tab === 'qsm_tools_page_questions_setting' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Questions Settings', 'text-domain'); ?>
            </a>			
            <a href="<?php echo esc_url(admin_url('admin.php?page=qsm_quiz_tools&tab=qsm_tools_page_results_setting')); ?>" 
               class="nav-tab <?php echo $active_tab === 'qsm_tools_page_results_setting' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Results Settings', 'text-domain'); ?>
            </a>			
        </h2>

        <?php
        // Handle callbacks based on the active tab
        if (empty($_GET['tab']) || 'qsm_tools_page_audit_trail' === $active_tab) {
            qsm_audit_box();
        }

        if (!empty($_GET['tab']) && 'qsm_tools_page_quiz_setting' === $active_tab) {
            qsm_restore_function();
        }

        if (!empty($_GET['tab']) && 'qsm_tools_page_questions_setting' === $active_tab) {
            qsm_get_deleted_questions_records();
        }

        if (!empty($_GET['tab']) && 'qsm_tools_page_results_setting' === $active_tab) {
            qsm_get_deleted_results_records();
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
		
		if(isset($_POST['qsm_delete_permanent_quiz'])) {
			global $mlwQuizMasterNext;
			$quiz_id     = sanitize_text_field( wp_unslash( $_POST['restore_quiz'] ) );
			$quiz_id     = intval( str_replace( 'QID', '', $quiz_id ) );
			do_action( 'qsm_before_delete_quiz', $quiz_id );
			$quiz_name   = isset( $_POST['delete_quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_quiz_name'] ) ) : '';
			$mlwQuizMasterNext->quizCreator->delete_quiz( $quiz_id, $quiz_name );
			?>
			<span style="color:green;"><?php esc_html_e( 'Quiz Has Been Deleted!', 'quiz-master-next' ); ?></span>
			<?php
		} else if(isset($_POST['qsm_restore_quiz'])) {
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
	?>
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
	$table_limit = 1;
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
			echo sprintf( esc_html__( 'Previous %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
			?>
		</a>
		<?php
		if ( $left > $table_limit ) {
			?>
			<a class="button" id="next_page" href="?page=qsm_quiz_tools&&audit_page=<?php echo esc_attr( $page ); ?>">
			<?php
			/* translators: %s: table limit */
			echo sprintf( esc_html__( 'Next %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
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
				echo sprintf( esc_html__( 'Next %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
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
			echo sprintf( esc_html__( 'Previous %s Audits', 'quiz-master-next' ), esc_html( $table_limit ) );
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

	// Handle the deletion process before fetching records
	if ((isset($_POST['delete_selected'])) || isset($_POST['delete_all_questions']) ) {
		if (isset($_POST['qsm_delete_selected_questions_nonce_field']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['qsm_delete_selected_questions_nonce_field'])), 'qsm_delete_selected_questions_nonce')) {
			if (isset($_POST['delete_selected']) && isset($_POST['delete_questions'])){
				$delete_ids = array_map('absint', wp_unslash($_POST['delete_questions']));
				if (!empty($delete_ids)) { 
					$delete_ids = array_map('intval', $delete_ids);
					$placeholders = implode(',', array_fill(0, count($delete_ids), '%d'));
					$query = $wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}mlw_questions WHERE question_id IN ($placeholders)",
						$delete_ids
					);
					$wpdb->query($query);
					$mlwQuizMasterNext->alertManager->newAlert(__('Selected questions have been deleted.', 'quiz-master-next'), 'success');
				}
			} else if (isset($_POST['delete_all_questions'])) {
				$wpdb->query("DELETE FROM {$wpdb->prefix}mlw_questions WHERE deleted = 1 OR deleted_question_bank = 1");
				$mlwQuizMasterNext->alertManager->newAlert(__('All deleted questions have been removed.', 'quiz-master-next'), 'success');
			}
		} else {
			$mlwQuizMasterNext->alertManager->newAlert(__('Nonce verification failed. Please try again.', 'quiz-master-next'), 'error');
			return;
		}
	}
	// Handle filter order
	$filter_order = isset($_POST['qsm_deleted_question_filter']) ? sanitize_text_field(wp_unslash($_POST['qsm_deleted_question_filter'])) : '';
	$order_by = ($filter_order === 'asc' || $filter_order === 'desc') ? strtoupper($filter_order) : 'ASC';

	// Fetch records
	$query = $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}mlw_questions WHERE deleted = %d OR deleted_question_bank = %d ORDER BY question_id $order_by",
		1, 1
	);
	$questions = $wpdb->get_results($query);
	$mlwQuizMasterNext->alertManager->showAlerts(); ?>
	<form action="" method="post">
	<div class="qsm-deleted-question-options-wrap">
		<p><?php esc_html_e('List of Questions removed from the quizzes or question bank', 'quiz-master-next'); ?></p>
		<div>
			<div class="qsm-deleted-question-options-forms">
				<?php if(! empty( $questions )) { ?> 
				<div>
					<label for="qsm-deleted-question-filter"><?php esc_html_e('Sort By Question ID', 'quiz-master-next'); ?></label>
					<select name="qsm_deleted_question_filter" id="qsm-deleted-question-filter">
						<option value=""><?php esc_html_e('Select Order', 'quiz-master-next'); ?></option>
						<option value="asc" <?php selected($filter_order, 'asc'); ?>><?php esc_html_e('Ascending', 'quiz-master-next'); ?></option>
						<option value="desc" <?php selected($filter_order, 'desc'); ?>><?php esc_html_e('Descending', 'quiz-master-next'); ?></option>
					</select>
					<button type="submit" class="button"><?php esc_html_e('Apply Filter', 'quiz-master-next'); ?></button>
				</div>
				<div><button type="submit" name="delete_selected" class="button"><?php esc_html_e('Delete Selected', 'quiz-master-next'); ?></button></div>
				<div><button type="submit" name="delete_all_questions" class="button-primary"><?php esc_html_e('Delete All Questions', 'quiz-master-next'); ?></button></div>
				<?php } ?>
			</div>
		</div>
	</div>

	<?php
	if ($questions) { 
		wp_nonce_field('qsm_delete_selected_questions_nonce', 'qsm_delete_selected_questions_nonce_field'); ?>
		<table class="widefat">
			<thead>
				<tr>
					<th width="5%">
						
					</th>
					<th width="15%"><?php esc_html_e('Question ID', 'quiz-master-next'); ?></th>
					<th><?php esc_html_e('Question Name', 'quiz-master-next'); ?></th>
				</tr>
			</thead>
			<tbody id="the-list">
			<?php
			foreach ($questions as $row) {
				$settings = maybe_unserialize($row->question_settings);
				?>
				<tr>
					<td>
						<input type="checkbox" name="delete_questions[]" value="<?php echo esc_attr($row->question_id); ?>" />
					</td>
					<td><?php echo esc_html($row->question_id); ?></td>
					<td><?php echo esc_html($settings['question_title']); ?></td>
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
				<td colspan="3"><?php esc_html_e('No data found!!', 'quiz-master-next'); ?></td>
			</tr>
		</table>
		<?php
	}
	?></form><?php
}

function qsm_get_deleted_results_records() {
    global $wpdb;

    // Handle "Delete All Results" action
    if (isset($_POST['delete_all_results'])) {
        if (
            isset($_POST['qsm_delete_selected_questions_nonce_field']) &&
            wp_verify_nonce($_POST['qsm_delete_selected_questions_nonce_field'], 'qsm_delete_selected_questions_nonce')
        ) {
            // Delete all results marked as deleted
            $wpdb->query("DELETE FROM {$wpdb->prefix}mlw_results WHERE deleted = 1");

            // Redirect to avoid form resubmission issues
            wp_safe_redirect(add_query_arg('message', 'deleted_all', $_SERVER['REQUEST_URI']));
            exit;
        } else {
            wp_die(__('Nonce verification failed. Action not allowed.', 'quiz-master-next'));
        }
    }

    // Handle "Delete Selected" action
    if (isset($_POST['delete_selected'])) {
        if (
            isset($_POST['qsm_delete_selected_questions_nonce_field']) &&
            wp_verify_nonce($_POST['qsm_delete_selected_questions_nonce_field'], 'qsm_delete_selected_questions_nonce')
        ) {
            if (!empty($_POST['quiz_results'])) {
                $selected_ids = array_map('intval', $_POST['quiz_results']);
                $ids_placeholders = implode(',', array_fill(0, count($selected_ids), '%d'));

                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}mlw_results WHERE result_id IN ($ids_placeholders)",
                    ...$selected_ids
                ));
            }

            // Redirect to avoid form resubmission issues
            wp_safe_redirect(add_query_arg('message', 'deleted_selected', $_SERVER['REQUEST_URI']));
            exit;
        } else {
            wp_die(__('Nonce verification failed. Action not allowed.', 'quiz-master-next'));
        }
    }

    // Get the filter value from the POST request
    $filtered_quiz_id = isset($_POST['qsm_deleted_result_filter']) ? intval($_POST['qsm_deleted_result_filter']) : 0;

    // Prepare the query for deleted results
    if ($filtered_quiz_id) {
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted = %d AND quiz_id = %d ORDER BY result_id DESC",
            1,
            $filtered_quiz_id
        );
    } else {
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted = %d ORDER BY result_id DESC",
            1
        );
    }

    $results = $wpdb->get_results($query);

    ?>
    <form action="" method="post">
        <div class="qsm-deleted-question-options-wrap">
            <p><?php esc_html_e('List of deleted Quiz Results from the quiz result page', 'quiz-master-next'); ?></p>
            <div>
                <div class="qsm-deleted-question-options-forms">
                    <?php
                    // Retrieve all non-deleted quizzes
                    $quizzes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0");
                    if (!empty($quizzes)) { ?> 
                        <div>
                            <label for="qsm-deleted-result-filter"><?php esc_html_e('Sort By Quiz', 'quiz-master-next'); ?></label>
                            <select name="qsm_deleted_result_filter" id="qsm-deleted-result-filter">
                                <option value=""><?php esc_html_e('Select Quiz', 'quiz-master-next'); ?></option>
                                <?php
                                foreach ($quizzes as $quiz) {
                                    ?>
                                    <option value="<?php echo esc_attr($quiz->quiz_id); ?>" <?php selected($filtered_quiz_id, $quiz->quiz_id); ?>>
                                        <?php echo wp_kses_post($quiz->quiz_name); ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                            <button type="submit" class="button"><?php esc_html_e('Apply Filter', 'quiz-master-next'); ?></button>
                        </div>
                        <div>
                            <button type="submit" name="delete_selected" class="button"><?php esc_html_e('Delete Selected', 'quiz-master-next'); ?></button>
                        </div>
                        <div>
                            <button type="submit" name="delete_all_results" class="button-primary"><?php esc_html_e('Delete All Deleted Results', 'quiz-master-next'); ?></button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php 
        if ($results) { 
            wp_nonce_field('qsm_delete_selected_questions_nonce', 'qsm_delete_selected_questions_nonce_field'); ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th width="5%"></th>
                        <th width="15%"><?php esc_html_e('Result ID', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('Quiz Name', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('Email', 'quiz-master-next'); ?></th>
                        <th><?php esc_html_e('IP Address', 'quiz-master-next'); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                <?php
                foreach ($results as $row) {
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="quiz_results[]" value="<?php echo esc_attr($row->result_id); ?>" />
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
                    <td colspan="5"><?php esc_html_e('No data found!', 'quiz-master-next'); ?></td>
                </tr>
            </table>
            <?php
        }
        ?>
    </form>
    <?php
}
