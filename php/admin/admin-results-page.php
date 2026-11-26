<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function generates the admin side quiz results page
 *
 * @return void
 * @since 4.4.0
 */
function qsm_generate_admin_results_page() {

	// Makes sure user has the right privledges.
	if ( ! current_user_can( 'view_qsm_quiz_result' ) ) {
		return;
	}

	// Retrieves the current stab and all registered tabs.
	global $mlwQuizMasterNext;
	$active_tab  = strtolower( str_replace( ' ', '-', isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : __( 'Quiz Results', 'quiz-master-next' ) ) );
	$tab_array   = $mlwQuizMasterNext->pluginHelper->get_admin_results_tabs();
	?>
	<div class="wrap">
		<div>
			<h2 id="result_details">
				<?php esc_html_e( 'Quiz Results', 'quiz-master-next' ); ?>
			</h2>
		</div>
		<?php 
			qsm_show_results_migration_warning();
		?>
		<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
		<?php qsm_show_adverts(); ?>
		<nav class="qsm-ui qsm-ui-quiz-tabs-nav qsm-ui-results-tabs-nav" aria-label="<?php esc_attr_e( 'Results sections', 'quiz-master-next' ); ?>">
			<div class="qsm-ui-tabs qsm-ui-quiz-tabs">
				<?php
				// Same kit tab bar as the quiz editor. Add-on tabs (registered with register_admin_results_tab) render the same way.
				$tab_icons = array(
					'quiz-results'           => 'list',
					'export-results'         => 'download',
					'reporting-and-analysis' => 'chart',
					'proctor-reports'        => 'eye',
				);
				foreach ( $tab_array as $tab ) {
					$tab_url = "?page=mlw_quiz_results&tab={$tab['slug']}";
					?>
					<a href="<?php echo esc_url( $tab_url ); ?>" class="qsm-ui-tab" data-tab="<?php echo esc_attr( $tab['slug'] ); ?>"<?php echo $active_tab === $tab['slug'] ? ' aria-current="page"' : ''; ?>>
						<?php
						if ( isset( $tab_icons[ $tab['slug'] ] ) ) {
							echo QSM_Admin_UI::icon( $tab_icons[ $tab['slug'] ], 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
						}
						echo wp_kses_post( $tab['title'] );
						?>
					</a>
					<?php
				}
				?>
			</div>
		</nav>
		<div class="result-page-wrapper">
			<?php
			// Locates the active tab and calls its content function.
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
 * Adds Overview Tab To Admin Results Page
 *
 * @since 5.0.0
 * @return void
 */
function qsm_results_overview_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_admin_results_tab( __( 'Quiz Results', 'quiz-master-next' ), 'qsm_results_overview_tab_content', 1 );
	if ( ! class_exists( 'QSM_Exporting' ) ) {
		$mlwQuizMasterNext->pluginHelper->register_admin_results_tab( __( 'Export Results', 'quiz-master-next' ), 'qsm_export_results_tabs_content', 5 );
	}
	if ( ! class_exists( 'QSM_Analysis' ) ) {
		$mlwQuizMasterNext->pluginHelper->register_admin_results_tab( __( 'Reporting And Analysis', 'quiz-master-next' ), 'qsm_reporting_analysis_tabs_content', 10 );
	}
	if ( ! class_exists( 'QSM_Proctoring_Quiz' ) ) {
		$mlwQuizMasterNext->pluginHelper->register_admin_results_tab( __( 'Proctor Reports', 'quiz-master-next' ), 'qsm_proctor_quiz_tabs_content', 12 );
	}
}

add_action( 'init', 'qsm_results_overview_tab' );

/**
 * Generates HTML For Overview Tab
 *
 * @since 5.0.0
 * @return void
 */
function qsm_delete_results_attachments( $rows_before_update ) {
	global $mlwQuizMasterNext;
    // Loop through each row in the results
    foreach ( $rows_before_update as $row ) {
        // Unserialize the quiz results
		$is_new_format = $mlwQuizMasterNext->pluginHelper->is_new_format_result( $row );
		if ( $is_new_format ) {
			// Load new format result structure
			$mlw_qmn_results_array = $mlwQuizMasterNext->pluginHelper->get_formated_result_data( $row->result_id );
		} else {
			$mlw_qmn_results_array = maybe_unserialize( $row->quiz_results );
		}
        // Ensure the results array exists and has the expected structure
		foreach ( $mlw_qmn_results_array[1] as $key => $value ) {
			// Check if the question type is 11 and user answer is not empty
			if ( 11 == $value['question_type'] && ! empty( $value['user_answer'] ) && isset( $value['user_answer']['file_id'] ) ) {
				$attachment_id = $value['user_answer']['file_id'];
				// Delete the attachment
				wp_delete_attachment( $attachment_id, true );
			}
		}
    }
}
/**
 * Query args that keep the current filters and sort on the results list.
 *
 * @since 11.3.0
 * @param array $overrides Args to set; null removes one.
 * @return array
 */
function qsm_results_list_query_args( $overrides = array() ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
	$args = array( 'page' => 'mlw_quiz_results' );
	if ( ! empty( $_GET['quiz_id'] ) ) {
		$args['quiz_id'] = intval( $_GET['quiz_id'] );
	}
	if ( isset( $_GET['qsm_search_phrase'] ) && '' !== $_GET['qsm_search_phrase'] ) {
		$args['qsm_search_phrase'] = sanitize_text_field( wp_unslash( $_GET['qsm_search_phrase'] ) );
	}
	if ( ! empty( $_GET['qmn_order_by'] ) && 'default' !== $_GET['qmn_order_by'] ) {
		$args['qmn_order_by'] = sanitize_key( wp_unslash( $_GET['qmn_order_by'] ) );
	}
	// phpcs:enable
	foreach ( $overrides as $key => $value ) {
		if ( null === $value ) {
			unset( $args[ $key ] );
		} else {
			$args[ $key ] = $value;
		}
	}
	return $args;
}

/**
 * Generates HTML For Overview Tab
 *
 * New UI (CU-14ypaj0p5kk): kit data table (D-01), filters, bulk actions and
 * delete confirms (O-02). Form field names, nonces and the add-on hooks are
 * unchanged.
 *
 * @since 5.0.0
 * @return void
 */
function qsm_results_overview_tab_content() {
	$get_subtabs = apply_filters( 'qsm_ultimate_get_subtabs_options', array() );
	$current_subtab = isset( $_GET['subtab'] ) ? sanitize_key( $_GET['subtab'] ) : '';
	do_action('qsm_before_admin_show_results_list', $get_subtabs, $current_subtab);

	if ( empty( $get_subtabs ) || '' === $current_subtab ) {

	global $wpdb;
	global $mlwQuizMasterNext;
	// The alerts above the tabs have already printed, so delete outcomes are shown in the list itself.
	$list_notice = array();

	// If nonce is correct, delete results.
	if ( isset( $_POST['delete_results_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['delete_results_nonce'] ) ), 'delete_results' ) && current_user_can( 'administrator' ) ) {

		$mlw_delete_results_id   = isset( $_POST['result_id'] ) ? intval( $_POST['result_id'] ) : 0;
		$mlw_delete_results_name = isset( $_POST['delete_quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_quiz_name'] ) ) : '';
		do_action( 'qsm_before_delete_result', $mlw_delete_results_id );
		// Updates table to mark results as deleted.
		$results                 = $wpdb->update( $wpdb->prefix . 'mlw_results', array( 'deleted' => 1 ), array( 'result_id' => $mlw_delete_results_id ), array( '%d' ), array( '%d' ) );
		// Get the row before the update
		$row_before_update = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d",
				$mlw_delete_results_id
			)
		);

		if ( false === $results ) {
			$error = $wpdb->last_error;
			if ( empty( $error ) ) {
				$error = __( 'Unknown error', 'quiz-master-next' );
			}
			/* translators: %s: error */
			$mlwQuizMasterNext->alertManager->newAlert( sprintf( __( 'There was an error when deleting this result. Error from WordPress: %s', 'quiz-master-next' ), $error ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error deleting result', "Tried {$wpdb->last_query} but got $error.", 0, 'error' );
			/* translators: %s: error */
			$list_notice = array( 'danger', sprintf( __( 'There was an error when deleting this result. Error from WordPress: %s', 'quiz-master-next' ), $error ) );
		} else {
			qsm_delete_results_attachments( $row_before_update );
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Your results has been deleted successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( 'Results Has Been Deleted From:', $mlw_delete_results_name, '' );
			$list_notice = array( 'success', __( 'The result has been deleted.', 'quiz-master-next' ) );
		}
	}

	// Check if bulk delete has been selected. If so, verify nonce.
	if ( isset( $_POST["bulk_delete"], $_POST['bulk_delete_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bulk_delete_nonce'] ) ), 'bulk_delete' ) ) {

		// Ensure the POST variable is an array
		if ( isset( $_POST["delete_results"] ) && is_array( $_POST["delete_results"] ) ) {
			$delete_results = array_map( 'sanitize_text_field', wp_unslash( $_POST["delete_results"] ) );
			$table_name = $wpdb->prefix . 'mlw_results';
			$query      = $wpdb->prepare(
				"SELECT * FROM $table_name WHERE result_id IN (" . implode( ',', array_fill( 0, count( $delete_results ), '%d' ) ) . ')',
				$delete_results
			);
			$row_before_update = $wpdb->get_results( $query );
			$is_permanent      = isset( $_POST['bulk_permanent_delete'] ) && 1 === intval( sanitize_text_field( wp_unslash( $_POST['bulk_permanent_delete'] ) ) );

			// Cycle through the POST array which should be an array of the result ids of the results the user wishes to delete
			foreach ( $delete_results as $result ) {

				// Santize by ensuring the value is an int
				$result_id = intval( $result );
				if ( $is_permanent ) {
					$wpdb->delete(
						$wpdb->prefix . "mlw_results", array( 'result_id' => $result_id )
					);
				} else {
					$wpdb->update(
						$wpdb->prefix . "mlw_results", array( 'deleted' => 1 ), array( 'result_id' => $result_id ), array( '%d' ), array( '%d' )
					);
				}
			}
			qsm_delete_results_attachments( $row_before_update );
			$mlwQuizMasterNext->audit_manager->new_audit( 'Results Have Been Bulk Deleted', '', '' );
			$deleted_count = count( $delete_results );
			$list_notice   = array(
				'success',
				$is_permanent
					/* translators: %s: number of results */
					? sprintf( _n( '%s result has been permanently deleted.', '%s results have been permanently deleted.', $deleted_count, 'quiz-master-next' ), number_format_i18n( $deleted_count ) )
					/* translators: %s: number of results */
					: sprintf( _n( '%s result has been deleted.', '%s results have been deleted.', $deleted_count, 'quiz-master-next' ), number_format_i18n( $deleted_count ) ),
			);
		}
	}

	// Prepares the SQL to retrieve the results.
	$table_limit         = 40;
	$search_phrase_sql   = '';
	$delete              = 'deleted=0';
	$delete              = apply_filters( 'qsm_results_delete_clause', $delete );
	$order_by            = 'time_taken_real';
	$order_by_sql        = 'ORDER BY time_taken_real DESC';
	$quiz_id             = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	$qsm_search_phrase   = isset( $_GET['qsm_search_phrase'] ) ? sanitize_text_field( wp_unslash( $_GET['qsm_search_phrase'] ) ) : '';
	if ( '' !== $qsm_search_phrase ) {
		// Sanitizes the search phrase and then uses $wpdb->prepare to properly escape the queries after using $wpdb->esc_like.
		$sanitized_search_phrase = htmlentities( $qsm_search_phrase );
		$search_phrase_percents  = '%' . esc_sql( $wpdb->esc_like( $sanitized_search_phrase ) ) . '%';
		$search_phrase_sql       = $wpdb->prepare( ' AND (quiz_name LIKE %s OR name LIKE %s OR business LIKE %s OR email LIKE %s OR phone LIKE %s)', $search_phrase_percents, $search_phrase_percents, $search_phrase_percents, $search_phrase_percents, $search_phrase_percents );
	}
	if ( $quiz_id > 0 ) {
		$qsm_results_count   = $wpdb->get_var( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE {$delete} AND quiz_id='{$quiz_id}' {$search_phrase_sql}" );
	} else {
		$qsm_results_count = $wpdb->get_var( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE {$delete} {$search_phrase_sql}" );
	}

	// Gets the order by arg. Uses switch to create SQL to prevent SQL injection.
	if ( isset( $_GET['qmn_order_by'] ) ) {
		switch ( $_GET['qmn_order_by'] ) {
			case 'quiz_name':
				$order_by        = 'quiz_name';
				$order_by_sql    = ' ORDER BY quiz_name DESC';
				break;
			case 'name':
				$order_by        = 'name';
				$order_by_sql    = ' ORDER BY name DESC';
				break;
			case 'point_score':
				$order_by        = 'point_score';
				$order_by_sql    = ' ORDER BY point_score DESC';
				break;
			case 'correct_score':
				$order_by        = 'correct_score';
				$order_by_sql    = ' ORDER BY correct_score DESC';
				break;
			default:
				$order_by        = 'time_taken_real';
				$order_by_sql    = ' ORDER BY time_taken_real DESC';
		}
	}

	// qsm_results_page (old links, 0 = page 2) and goto (1-based) are both still read.
	if ( isset( $_GET['qsm_results_page'] ) || isset( $_GET['goto'] ) ) {
		$result_page = isset( $_GET['qsm_results_page'] ) ? intval( $_GET['qsm_results_page'] ) + 1 : intval( $_GET['goto'] ) - 1;
	} else {
		$result_page = 0;
	}
	$mlw_total_pages = max( 1, (int) ceil( $qsm_results_count / $table_limit ) );
	$result_page     = max( 0, min( $result_page, $mlw_total_pages - 1 ) );
	$result_begin    = $table_limit * $result_page;
	if ( $quiz_id > 0 ) {
		$mlw_quiz_data   = $wpdb->get_results( stripslashes( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE $delete AND quiz_id = %d $search_phrase_sql $order_by_sql LIMIT %d, %d", $quiz_id, $result_begin, $table_limit ) ) );
	} else {
		$mlw_quiz_data = $wpdb->get_results( stripslashes( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE $delete $search_phrase_sql $order_by_sql LIMIT %d, %d", $result_begin, $table_limit ) ) );
	}
	apply_filters('qsm_user_role_permission_page_view_results',true);

	//process screen options
	$user_id             = get_current_user_id();
	if ( isset( $_POST["results-screen_option_nonce"] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST["results-screen_option_nonce"] ) ), 'results_screen_option' ) ) {
		$results_screen_option['page_url']   = isset( $_POST['page_url'] ) ? sanitize_text_field( wp_unslash( $_POST['page_url'] ) ) : "0";
		$results_screen_option['page_name']  = isset( $_POST['page_name'] ) ? sanitize_text_field( wp_unslash( $_POST['page_name'] ) ) : "0";
		$results_screen_option['view_result_page'] = isset( $_POST['view_result_page'] ) ? sanitize_text_field( wp_unslash( $_POST['view_result_page'] ) ) : "0";
		$results_screen_option['business']   = isset( $_POST['business'] ) ? sanitize_text_field( wp_unslash( $_POST['business'] ) ) : "0";
		$results_screen_option['phone']      = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : "0";
		$results_screen_option['ip_address'] = isset( $_POST['ip_address'] ) ? sanitize_text_field( wp_unslash( $_POST['ip_address'] ) ) : "0";
		$results_screen_option['start_date'] = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : "0";
		$results_screen_option['time_taken'] = isset( $_POST['time_taken'] ) ? sanitize_text_field( wp_unslash( $_POST['time_taken'] ) ) : "0";
		//set screen option as user meta
		update_user_meta( $user_id, 'results_screen_option', $results_screen_option );
	} else {
		$results_screen_option   = get_user_meta( $user_id, 'results_screen_option', true );
		$results_screen_option   = ! empty( $results_screen_option ) ? $results_screen_option : '';
		$default_results_screen_option = array(
			'page_url'         => '0',
			'view_result_page' => '0',
			'page_name'        => '0',
			'business'         => '1',
			'phone'            => '1',
			'ip_address'       => '1',
		);
		$results_screen_option = wp_parse_args( $results_screen_option, $default_results_screen_option );
		if ( ! isset( $results_screen_option['start_date'] ) ) {
			$results_screen_option['start_date'] = "0";
		}
		if ( ! isset( $results_screen_option['time_taken'] ) ) {
			$results_screen_option['time_taken'] = "0";
		}
	}

	$th_elements = apply_filters( 'mlw_qmn_admin_results_page_headings', array(
		'score'            => __( 'Score', 'quiz-master-next' ),
		'time_complete'    => __( 'Time To Complete', 'quiz-master-next' ),
		'name'             => __( 'Name', 'quiz-master-next' ),
		'business'         => __( 'Business', 'quiz-master-next' ),
		'email'            => __( 'Email', 'quiz-master-next' ),
		'phone'            => __( 'Phone', 'quiz-master-next' ),
		'user'             => __( 'User', 'quiz-master-next' ),
		'start_date'       => __( 'Start Date', 'quiz-master-next' ),
		'time_taken'       => __( 'End Date', 'quiz-master-next' ),
		'ip'               => __( 'IP Address', 'quiz-master-next' ),
		'page_name'        => __( 'Page Name', 'quiz-master-next' ),
		'page_url'         => __( 'Page URL', 'quiz-master-next' ),
		'view_result_page' => __( 'Result Page', 'quiz-master-next' ),
	) );

	$values      = $quiz_infos   = array();
	foreach ( $th_elements as $key => $th ) {
		$values[ $key ]['title']  = $th;
		$values[ $key ]['hidden'] = false;
	}

	// Columns the Columns dialog (Screen Options) can hide: column key => option key.
	$hideable_columns = array(
		'page_url'         => 'page_url',
		'page_name'        => 'page_name',
		'business'         => 'business',
		'phone'            => 'phone',
		'ip'               => 'ip_address',
		'start_date'       => 'start_date',
		'time_taken'       => 'time_taken',
		'view_result_page' => 'view_result_page',
	);
	foreach ( $hideable_columns as $column => $option ) {
		if ( isset( $values[ $column ] ) && "0" === $results_screen_option[ $option ] ) {
			$values[ $column ]['hidden'] = true;
		}
	}
	if ( $mlw_quiz_data ) {
		foreach ( $mlw_quiz_data as $mlw_quiz_info ) {
			$quiz_infos[]            = $mlw_quiz_info;
			$mlw_complete_time       = '';
			$is_new_format = $mlwQuizMasterNext->pluginHelper->is_new_format_result( $mlw_quiz_info );
			if ( $is_new_format ) {
				// Load new format result structure
				$mlw_qmn_results_array = $mlwQuizMasterNext->pluginHelper->get_formated_result_data( $mlw_quiz_info->result_id );
			} else {
				$mlw_qmn_results_array = maybe_unserialize( $mlw_quiz_info->quiz_results );
			}
			$hidden_questions        = 0;
			if ( ! empty( $mlw_qmn_results_array['hidden_questions'] ) && is_array( $mlw_qmn_results_array['hidden_questions'] ) ) {
				// Only graded hidden questions are part of the total, so skip ungraded ones (e.g. Text/HTML).
				$answered_types = array();
				if ( isset( $mlw_qmn_results_array[1] ) && is_array( $mlw_qmn_results_array[1] ) ) {
					foreach ( $mlw_qmn_results_array[1] as $answer ) {
						if ( isset( $answer['id'], $answer['question_type'] ) ) {
							$answered_types[ intval( $answer['id'] ) ] = $answer['question_type'];
						}
					}
				}
				foreach ( $mlw_qmn_results_array['hidden_questions'] as $hidden_question_id ) {
					$hidden_question_id = intval( $hidden_question_id );
					if ( ! isset( $answered_types[ $hidden_question_id ] ) || $mlwQuizMasterNext->pluginHelper->is_question_type_graded( $answered_types[ $hidden_question_id ] ) ) {
						++$hidden_questions;
					}
				}
			}
			if ( is_array( $mlw_qmn_results_array ) ) {
				$mlw_complete_hours = floor( $mlw_qmn_results_array[0] / 3600 );
				if ( $mlw_complete_hours > 0 ) {
					$mlw_complete_time .= "$mlw_complete_hours hours ";
				}
				$mlw_complete_minutes = floor( ( $mlw_qmn_results_array[0] % 3600 ) / 60 );
				if ( $mlw_complete_minutes > 0 ) {
					$mlw_complete_time .= "$mlw_complete_minutes minutes ";
				}
				$mlw_complete_seconds    = $mlw_qmn_results_array[0] % 60;
				$mlw_complete_time       .= "$mlw_complete_seconds seconds";
			}

			$out_of_q    = $mlw_quiz_info->total - $hidden_questions;
			$form_type   = isset( $mlw_quiz_info->form_type ) ? $mlw_quiz_info->form_type : 0;

			if ( isset( $values['score'] ) ) {
				if ( 1 === intval( $form_type ) || 2 === intval( $form_type ) ) {
					$values['score']['content'][] = esc_html__( 'Not Graded', 'quiz-master-next' );
				} elseif ( 0 === intval( $mlw_quiz_info->quiz_system ) ) {
						$values['score']['content'][] = sprintf( '%1$s %2$s %3$s %4$s %5$s', esc_html( $mlw_quiz_info->correct ), esc_html__( 'out of', 'quiz-master-next' ), esc_html( $out_of_q ), esc_html__( 'or', 'quiz-master-next' ), esc_html( $mlw_quiz_info->correct_score ) );
					} elseif ( 1 === intval( $mlw_quiz_info->quiz_system ) ) {
						$values['score']['content'][] = sprintf( '%1$s %2$s', esc_html( $mlw_quiz_info->point_score ), esc_html__( 'Points', 'quiz-master-next' ) );
					} elseif ( 3 === intval( $mlw_quiz_info->quiz_system ) ) {
						$values['score']['content'][] = sprintf( '%1$s %2$s %3$s %4$s %5$s <br /> %6$s %7$s', esc_html( $mlw_quiz_info->correct ), esc_html__( 'out of', 'quiz-master-next' ), esc_html( $out_of_q ), esc_html__( 'or', 'quiz-master-next' ), esc_html( $mlw_quiz_info->correct_score ), esc_html( $mlw_quiz_info->point_score ), esc_html__( 'Points', 'quiz-master-next' ) );
					} else {
						$values['score']['content'][] = esc_html__( 'Not Graded', 'quiz-master-next' );
				}
			}

			if ( isset( $values['time_complete'] ) ) {
				$values['time_complete']['content'][] = $mlw_complete_time;
			}

			if ( isset( $values['name'] ) ) {
				$values['name']['content'][] = $mlw_quiz_info->name;
			}

			if ( isset( $values['business'] ) ) {
				$values['business']['content'][] = $mlw_quiz_info->business;
			}

			if ( isset( $values['email'] ) ) {
				$values['email']['content'][] = $mlw_quiz_info->email;
			}

			if ( isset( $values['phone'] ) ) {
				$values['phone']['content'][] = $mlw_quiz_info->phone;
			}

			if ( isset( $values['user'] ) ) {
				if ( 0 === intval( $mlw_quiz_info->user ) ) {
					$values['user']['content'][] = esc_html__( 'Visitor', 'quiz-master-next' );
				} else {
					$values['user']['content'][] = '<a href="' . esc_url( admin_url( 'user-edit.php?user_id=' . $mlw_quiz_info->user ) ) . '">' . esc_html( $mlw_quiz_info->user ) . '</a>';
				}
			}

			if ( isset( $values['start_date'] ) ) {
				if ( isset($mlw_qmn_results_array['quiz_start_date']) ) {
					$values['start_date']['content'][] = $mlw_qmn_results_array['quiz_start_date'];
				} else {
					$values['start_date']['content'][] = ' ';
				}
			}

			if ( isset( $values['time_taken'] ) ) {
				$values['time_taken']['content'][] = $mlw_quiz_info->time_taken;
			}
			if ( isset( $values['ip'] ) ) {
				$values['ip']['content'][] = $mlw_quiz_info->user_ip;
			}
			if ( isset( $values['page_name'] ) ) {
				$values['page_name']['content'][] = $mlw_quiz_info->page_name;
			}
			if ( isset( $values['page_url'] ) ) {
				$quiz_page_url = $mlw_quiz_info->page_url;
				if ( ! empty( $mlw_qmn_results_array['page_url'] ) ) {
					$quiz_page_url = $mlw_qmn_results_array['page_url'];
				}
				$values['page_url']['content'][] = '<a href="' . esc_url( $quiz_page_url ) . '">' . esc_html( $quiz_page_url ) . '</a>';
			}
			if ( isset( $values['view_result_page']) ) {
				$quiz_page_url = $mlw_quiz_info->page_url;
				$unique_id = $mlw_quiz_info->unique_id;
				$values['view_result_page']['content'][] = '<a target="_blank" class="qsm-ui-btn qsm-ui-btn-sm" href="' . esc_url( $quiz_page_url ) . '?result_id=' . esc_attr( $unique_id ) . '">' . esc_html__( 'View', 'quiz-master-next' ) . '</a>';
			}
			foreach ( $values as $k => $v ) {
				if ( ! in_array( $k, array( 'score', 'time_complete', 'name', 'business', 'email', 'phone', 'user', 'time_taken', 'ip', 'page_name', 'page_url', 'view_result_page' ), true ) ) {
					$content = apply_filters( 'mlw_qmn_admin_results_page_column_content', '', $mlw_quiz_info, $k );
					if ( isset( $values[ $k ] ) && ! empty( $content ) ) {
						$values[ $k ]['content'][] = $content;
					}
				}
			}
		}
	}

	// Filters, sorting and pagination.
	$quiz_list       = $wpdb->get_results( "SELECT quiz_id, quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0 ORDER BY quiz_name ASC" );
	$active_filters  = ( '' !== $qsm_search_phrase ? 1 : 0 ) + ( $quiz_id > 0 ? 1 : 0 );
	$qmn_order_by    = 'time_taken_real' === $order_by ? 'default' : $order_by;
	$is_admin        = current_user_can( 'administrator' );
	$mlw_current_page = $result_page + 1;
	$co               = ! empty( $quiz_infos ) ? count( $quiz_infos ) : 0;
	// Header sort links (D-01). The list only sorts newest / highest first, so a sorted column shows "descending".
	$sortable_columns = array(
		'quiz_name'  => 'quiz_name',
		'name'       => 'name',
		'time_taken' => 'default',
	);
	$sort_header = function ( $label, $sort_key ) use ( $qmn_order_by ) {
		$is_sorted = $sort_key === $qmn_order_by;
		$url       = add_query_arg( urlencode_deep( qsm_results_list_query_args( array( 'qmn_order_by' => 'default' === $sort_key ? null : $sort_key ) ) ), admin_url( 'admin.php' ) );
		return array(
			'aria' => $is_sorted ? 'descending' : 'none',
			'html' => '<a class="qsm-ui-sort" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>',
		);
	};
	// Columns with content on this page, as the table shows them.
	$columns = array();
	foreach ( $values as $k => $v ) {
		if ( ! empty( $v['content'] ) ) {
			$columns[ $k ] = $v;
		}
	}
	$hidden_attr = ' style="display:none;"';
	?>
	<div class="qsm-ui qsm-ui-results" id="qsm-results-list">
		<?php
		if ( $list_notice ) {
			echo QSM_Admin_UI::notice( esc_html( $list_notice[1] ), array( 'type' => $list_notice[0], 'dismissible' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup, message escaped above.
		}
		?>
		<div class="qsm-ui-table-toolbar">
			<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="get" class="qsm-ui-row qsm-ui-results-filters" role="search">
				<input type="hidden" name="page" value="mlw_quiz_results">
				<div class="qsm-ui-input-icon qsm-ui-results-search">
					<?php echo QSM_Admin_UI::icon( 'search', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit icon. ?>
					<input type="search" class="qsm-ui-input qsm-ui-input-sm" id="qsm_search_phrase" name="qsm_search_phrase" value="<?php echo esc_attr( $qsm_search_phrase ); ?>" placeholder="<?php esc_attr_e( 'Search results…', 'quiz-master-next' ); ?>" aria-label="<?php esc_attr_e( 'Search Results', 'quiz-master-next' ); ?>">
				</div>
				<select class="qsm-ui-select qsm-ui-input-sm qsm-ui-results-quiz" name="quiz_id" aria-label="<?php esc_attr_e( 'Filter by quiz', 'quiz-master-next' ); ?>">
					<option value=""><?php esc_html_e( 'All quizzes and surveys', 'quiz-master-next' ); ?></option>
					<?php
					$quiz_listed = false;
					foreach ( (array) $quiz_list as $quiz_item ) {
						$quiz_listed = $quiz_listed || intval( $quiz_item->quiz_id ) === $quiz_id;
					}
					if ( $quiz_id > 0 && ! $quiz_listed ) {
						?>
						<option value="<?php echo esc_attr( $quiz_id ); ?>" selected="selected"><?php /* translators: %d: quiz ID */ echo esc_html( sprintf( __( 'Quiz #%d', 'quiz-master-next' ), $quiz_id ) ); ?></option>
						<?php
					}
					foreach ( (array) $quiz_list as $quiz_item ) {
						?>
						<option value="<?php echo esc_attr( $quiz_item->quiz_id ); ?>" <?php selected( $quiz_id, intval( $quiz_item->quiz_id ) ); ?>><?php echo esc_html( $quiz_item->quiz_name ); ?></option>
					<?php } ?>
				</select>
				<select class="qsm-ui-select qsm-ui-input-sm qsm-ui-results-order" id="qmn_order_by" name="qmn_order_by" aria-label="<?php esc_attr_e( 'Order By', 'quiz-master-next' ); ?>">
					<option value="default" <?php selected( $qmn_order_by, 'default' ); ?>><?php esc_html_e( 'Newest first', 'quiz-master-next' ); ?></option>
					<option value="quiz_name" <?php selected( $qmn_order_by, 'quiz_name' ); ?>><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?></option>
					<option value="name" <?php selected( $qmn_order_by, 'name' ); ?>><?php esc_html_e( 'User Name', 'quiz-master-next' ); ?></option>
					<option value="point_score" <?php selected( $qmn_order_by, 'point_score' ); ?>><?php esc_html_e( 'Points', 'quiz-master-next' ); ?></option>
					<option value="correct_score" <?php selected( $qmn_order_by, 'correct_score' ); ?>><?php esc_html_e( 'Correct Percent', 'quiz-master-next' ); ?></option>
				</select>
				<button type="submit" class="qsm-ui-btn qsm-ui-btn-sm">
					<?php echo QSM_Admin_UI::icon( 'filter', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit icon. ?>
					<?php esc_html_e( 'Apply filters', 'quiz-master-next' ); ?>
					<?php if ( $active_filters ) { ?>
						<span class="qsm-ui-filter-count"><?php echo esc_html( $active_filters ); ?></span>
					<?php } ?>
				</button>
				<?php
				if ( $active_filters ) {
					echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
						__( 'Clear filters', 'quiz-master-next' ),
						array(
							'variant' => 'quiet',
							'size'    => 'sm',
							'href'    => add_query_arg( urlencode_deep( qsm_results_list_query_args( array( 'quiz_id' => null, 'qsm_search_phrase' => null ) ) ), admin_url( 'admin.php' ) ),
						)
					);
				}
				?>
			</form>
			<div class="qsm-ui-row">
				<?php
				echo QSM_Admin_UI::button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
					__( 'Columns', 'quiz-master-next' ),
					array(
						'size'  => 'sm',
						'icon'  => 'settings',
						'attrs' => array(
							'id'               => 'qsm-results-columns-button',
							'data-qsm-ui-open' => '#qsm-results-columns-dialog',
						),
					)
				);
				?>
			</div>
		</div>

		<?php // Same selection bar as the Questions tab (G-02). ?>
		<div class="qsm-ui-board-bar qsm-ui-text" data-qsm-results-bulkbar hidden>
			<span class="qsm-ui-board-count" data-qsm-results-count aria-live="polite"></span>
			<button type="button" class="qsm-ui-btn is-danger" data-qsm-results-bulk="delete"><?php echo QSM_Admin_UI::icon( 'trash', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit icon. ?><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></button>
			<button type="button" class="qsm-ui-btn is-danger" data-qsm-results-bulk="permanent"><?php echo QSM_Admin_UI::icon( 'trash', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit icon. ?><?php esc_html_e( 'Delete permanently', 'quiz-master-next' ); ?></button>
			<button type="button" class="qsm-ui-btn qsm-ui-btn-quiet qsm-ui-board-clear" data-qsm-results-clear><?php esc_html_e( 'Clear', 'quiz-master-next' ); ?></button>
		</div>

		<form action="" method="post" name="bulk_delete_form" id="qsm-results-bulk-form" class="qsm-ui-results-bulk-form">
			<input type="hidden" name="bulk_delete" value="confirmation" />
			<input type="hidden" name="bulk_permanent_delete" id="bulk_permanent_delete" value="0" />
			<?php wp_nonce_field( 'bulk_delete', 'bulk_delete_nonce' ); ?>
			<div class="qsm-ui-table-wrap">
				<?php if ( $co > 0 ) { ?>
				<table class="qsm-ui-table qsm-ui-results-table" aria-label="<?php esc_attr_e( 'Results Table', 'quiz-master-next' ); ?>">
					<thead>
						<tr>
							<th class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" id="qmn_check_all" aria-label="<?php esc_attr_e( 'Select all results on this page', 'quiz-master-next' ); ?>" /></label></th>
							<?php $quiz_name_sort = $sort_header( __( 'Quiz Name', 'quiz-master-next' ), 'quiz_name' ); ?>
							<th aria-sort="<?php echo esc_attr( $quiz_name_sort['aria'] ); ?>"><?php echo $quiz_name_sort['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $sort_header. ?></th>
							<?php
							foreach ( $columns as $k => $v ) {
								$hidden = $v['hidden'] ? $hidden_attr : '';
								if ( isset( $sortable_columns[ $k ] ) ) {
									$sort = $sort_header( $v['title'], $sortable_columns[ $k ] );
									echo '<th' . $hidden . ' aria-sort="' . esc_attr( $sort['aria'] ) . '">' . $sort['html'] . '</th>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute, escaped in $sort_header.
								} else {
									echo '<th' . $hidden . '>' . esc_html( $v['title'] ) . '</th>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute.
								}
							}
							?>
						</tr>
					</thead>
					<tbody id="the-list">
						<?php
						for ( $x = 0; $x < $co; $x++ ) {
							$quiz_info     = $quiz_infos[ $x ];
							$qsm_row_class = apply_filters( 'qsm_admin_results_table_row_class', '', $quiz_info );
							$details_url   = admin_url( 'admin.php?page=qsm_quiz_result_details&result_id=' . intval( $quiz_info->result_id ) );
							?>
							<tr<?php echo '' !== $qsm_row_class ? ' class="' . esc_attr( $qsm_row_class ) . '"' : ''; ?>>
								<td class="qsm-ui-col-check">
									<label class="qsm-ui-check"><input type="checkbox" class="qmn_delete_checkbox" name="delete_results[]" value="<?php echo esc_attr( $quiz_info->result_id ); ?>" aria-label="<?php /* translators: %s: quiz name */ echo esc_attr( sprintf( __( 'Select result for %s', 'quiz-master-next' ), $quiz_info->quiz_name ) ); ?>" /></label>
								</td>
								<td class="<?php echo esc_attr( apply_filters( 'qsm_results_quiz_name_class', '', $quiz_info->result_id ) ); ?>">
									<a class="qsm-ui-cell-title" href="<?php echo esc_url( $details_url ); ?>"><?php echo esc_html( $quiz_info->quiz_name ); ?></a>
									<?php do_action( 'qsm_admin_results_quiz_name_after', $quiz_info ); ?>
									<div class="qsm-ui-row-actions">
										<?php
										do_action( 'qsm_admin_quiz_results_page_rowactions_before', $quiz_info );
										if ( ( current_user_can( 'view_qsm_quiz_result' ) && get_current_user_id() == $quiz_info->user ) || current_user_can( 'delete_others_qsm_quizzes' ) ) {
											?>
											<a href="<?php echo esc_url( $details_url ); ?>"><?php esc_html_e( 'View', 'quiz-master-next' ); ?></a>
										<?php } ?>
										<?php if ( $is_admin ) { ?>
											<a class="is-danger qsm-ui-result-delete" data-result-id="<?php echo esc_attr( $quiz_info->result_id ); ?>" data-quiz-name="<?php echo esc_attr( $quiz_info->quiz_name ); ?>" href="#"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></a>
										<?php } ?>
										<?php if ( ! class_exists( 'QSM_Proctoring_Quiz' ) ) { ?>
											<a class="qsm-quiz-proctor-addon" href="#"><?php esc_html_e( 'Proctor Reports', 'quiz-master-next' ); ?></a>
										<?php } ?>
										<?php do_action( 'qsm_admin_quiz_results_page_rowactions_after', $quiz_info ); ?>
									</div>
								</td>
								<?php
								foreach ( $values as $k => $v ) {
									if ( isset( $v['content'][ $x ] ) ) {
										$cell_data = apply_filters( 'mlw_qmn_admin_results_page_result', $v['content'][ $x ], $quiz_info, $k );
										if ( 'start_date' === $k || 'time_taken' === $k ) {
											$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
											$cell_data = '<abbr title="' . gmdate( $date_time_format, strtotime( $cell_data ) ) . '">' . gmdate( get_option( 'date_format' ), strtotime( $cell_data ) ) . '<br>' . gmdate( get_option( 'time_format' ), strtotime( $cell_data ) ) . '</abbr>';
										}
										echo '<td' . ( $v['hidden'] ? $hidden_attr : '' ) . '><span>' . wp_kses_post( $cell_data ) . '</span></td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute, cell passed through wp_kses_post.
									}
								}
								?>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
					<?php
				} elseif ( $active_filters ) {
					echo QSM_Admin_UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
						array(
							'icon'    => 'search',
							'title'   => __( 'No results match your filters', 'quiz-master-next' ),
							'text'    => __( 'Try a different search or quiz, or clear the filters.', 'quiz-master-next' ),
							'actions' => QSM_Admin_UI::button(
								__( 'Clear filters', 'quiz-master-next' ),
								array(
									'size' => 'sm',
									'href' => add_query_arg( urlencode_deep( qsm_results_list_query_args( array( 'quiz_id' => null, 'qsm_search_phrase' => null ) ) ), admin_url( 'admin.php' ) ),
								)
							),
						)
					);
				} else {
					echo QSM_Admin_UI::empty_state( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
						array(
							'icon'  => 'list',
							'title' => __( 'No results yet', 'quiz-master-next' ),
							'text'  => __( 'Results appear here once someone completes one of your quizzes or surveys.', 'quiz-master-next' ),
						)
					);
				}
				?>
			</div>
		</form>

		<?php if ( $qsm_results_count > 0 ) { ?>
		<div class="qsm-ui-pagination">
			<span>
				<?php
				printf(
					/* translators: 1: first result shown, 2: last result shown, 3: total results */
					esc_html__( 'Showing %1$s–%2$s of %3$s results', 'quiz-master-next' ),
					esc_html( number_format_i18n( $co ? $result_begin + 1 : 0 ) ),
					esc_html( number_format_i18n( $result_begin + $co ) ),
					esc_html( number_format_i18n( $qsm_results_count ) )
				);
				?>
			</span>
			<?php if ( $mlw_total_pages > 1 ) { ?>
			<div class="qsm-ui-row qsm-ui-results-pager">
			<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="get" class="qsm-ui-pages" aria-label="<?php esc_attr_e( 'Results pages', 'quiz-master-next' ); ?>">
				<?php
				foreach ( qsm_results_list_query_args() as $arg => $arg_value ) {
					echo '<input type="hidden" name="' . esc_attr( $arg ) . '" value="' . esc_attr( $arg_value ) . '">';
				}
				// First, last, and two pages either side of the current one.
				$page_numbers = array_unique( array_merge( array( 1, $mlw_total_pages ), range( max( 1, $mlw_current_page - 2 ), min( $mlw_total_pages, $mlw_current_page + 2 ) ) ) );
				sort( $page_numbers );
				?>
				<button type="submit" name="goto" value="<?php echo esc_attr( $mlw_current_page - 1 ); ?>" aria-label="<?php esc_attr_e( 'Previous page', 'quiz-master-next' ); ?>" <?php disabled( $mlw_current_page <= 1 ); ?>>‹</button>
				<?php
				$previous_number = 0;
				foreach ( $page_numbers as $page_number ) {
					if ( $page_number - $previous_number > 1 ) {
						echo '<span class="qsm-ui-pages-gap" aria-hidden="true">…</span>';
					}
					$previous_number = $page_number;
					?>
					<button type="submit" name="goto" value="<?php echo esc_attr( $page_number ); ?>" <?php echo $page_number === $mlw_current_page ? 'aria-current="page"' : ''; ?>><?php echo esc_html( number_format_i18n( $page_number ) ); ?></button>
				<?php } ?>
				<button type="submit" name="goto" value="<?php echo esc_attr( $mlw_current_page + 1 ); ?>" aria-label="<?php esc_attr_e( 'Next page', 'quiz-master-next' ); ?>" <?php disabled( $mlw_current_page >= $mlw_total_pages ); ?>>›</button>
			</form>
			<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="get" class="qsm-ui-pages-jump">
				<?php
				// Own form: in the page-button form an empty field named goto would override the clicked button.
				foreach ( qsm_results_list_query_args() as $arg => $arg_value ) {
					echo '<input type="hidden" name="' . esc_attr( $arg ) . '" value="' . esc_attr( $arg_value ) . '">';
				}
				?>
				<input type="number" class="qsm-ui-input qsm-ui-input-sm qsm-ui-pages-goto" name="goto" min="1" max="<?php echo esc_attr( $mlw_total_pages ); ?>" placeholder="<?php echo esc_attr( $mlw_current_page ); ?>" aria-label="<?php esc_attr_e( 'Go to page', 'quiz-master-next' ); ?>" required>
			</form>
			</div>
			<?php } ?>
		</div>
		<?php } ?>

		<?php
		// Cancel sits on the left of the dialog footer, as on the Questions tab.
		$cancel_button = QSM_Admin_UI::button(
			__( 'Cancel', 'quiz-master-next' ),
			array(
				'class' => 'qsm-ui-foot-left',
				'attrs' => array( 'data-qsm-ui-close' => true, 'data-qsm-ui-initial-focus' => true ),
			)
		);
		if ( $is_admin ) {
			// O-02, small: one result. Same form fields and nonce as before.
			?>
			<form action="" method="post" id="qsm-results-delete-form">
				<?php
				echo QSM_Admin_UI::dialog( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
					array(
						'id'          => 'qsm-results-delete-dialog',
						'title'       => __( 'Delete this result?', 'quiz-master-next' ),
						'description' => '<span data-qsm-results-delete-text></span>',
						'danger'      => true,
						'body'        => wp_nonce_field( 'delete_results', 'delete_results_nonce', true, false )
							. '<input type="hidden" id="result_id" name="result_id" value="" />'
							. '<input type="hidden" id="delete_quiz_name" name="delete_quiz_name" value="" />'
							. '<p class="qsm-ui-small">' . esc_html__( 'It is removed from the results list, and any files uploaded with it are deleted.', 'quiz-master-next' ) . '</p>',
						'footer'      => $cancel_button . QSM_Admin_UI::button( __( 'Delete result', 'quiz-master-next' ), array( 'variant' => 'danger', 'type' => 'submit' ) ),
					)
				);
				?>
			</form>
			<?php
		}
		// O-02, small: the selected results are moved out of the list.
		echo QSM_Admin_UI::dialog( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
			array(
				'id'          => 'qsm-results-bulk-dialog',
				'title'       => __( 'Delete the selected results?', 'quiz-master-next' ),
				'description' => esc_html__( 'They are removed from the results list, and any files uploaded with them are deleted.', 'quiz-master-next' ),
				'danger'      => true,
				'footer'      => $cancel_button . QSM_Admin_UI::button( __( 'Delete results', 'quiz-master-next' ), array( 'variant' => 'danger', 'attrs' => array( 'data-qsm-results-bulk-submit' => 'delete' ) ) ),
			)
		);
		// O-02, with type-to-confirm: the selected results are removed from the database.
		echo QSM_Admin_UI::dialog( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
			array(
				'id'          => 'qsm-results-permanent-dialog',
				'title'       => __( 'Permanently delete the selected results?', 'quiz-master-next' ),
				'description' => esc_html__( 'This cannot be undone.', 'quiz-master-next' ),
				'danger'      => true,
				'body'        => '<ul class="qsm-ui-consequences">'
					. '<li>' . QSM_Admin_UI::icon( 'alert', 'qsm-ui-icon-sm' ) . '<span>' . esc_html__( 'The results and their answers are removed from the database.', 'quiz-master-next' ) . '</span></li>'
					. '<li>' . QSM_Admin_UI::icon( 'alert', 'qsm-ui-icon-sm' ) . '<span>' . esc_html__( 'Files uploaded with them are deleted.', 'quiz-master-next' ) . '</span></li>'
					. '</ul>',
				'footer'      => $cancel_button . QSM_Admin_UI::button(
					__( 'Delete permanently', 'quiz-master-next' ),
					array(
						'variant' => 'danger',
						'attrs'   => array(
							'id'                           => 'qsm-results-permanent-submit',
							'data-qsm-results-bulk-submit' => 'permanent',
						),
					)
				),
			)
		);

		// Columns (Screen Options): same form, fields and nonce as before.
		$column_options = array(
			'page_url'         => __( 'Page URL', 'quiz-master-next' ),
			'view_result_page' => __( 'Result Page', 'quiz-master-next' ),
			'page_name'        => __( 'Page Name', 'quiz-master-next' ),
			'business'         => __( 'Business', 'quiz-master-next' ),
			'phone'            => __( 'Phone', 'quiz-master-next' ),
			'ip_address'       => __( 'IP Address', 'quiz-master-next' ),
			'start_date'       => __( 'Start Date', 'quiz-master-next' ),
			'time_taken'       => __( 'End Date', 'quiz-master-next' ),
		);
		$column_checks  = '';
		foreach ( $column_options as $option => $label ) {
			$column_checks .= '<label class="qsm-ui-check"><input type="checkbox" name="' . esc_attr( $option ) . '" value="1"' . checked( $results_screen_option[ $option ], '1', false ) . ' /><span>' . esc_html( $label ) . '</span></label>';
		}
		?>
		<form action="" method="post" id="results-screen-option-form">
			<?php
			echo QSM_Admin_UI::dialog( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kit markup.
				array(
					'id'          => 'qsm-results-columns-dialog',
					'title'       => __( 'Columns', 'quiz-master-next' ),
					'description' => esc_html__( 'Choose the columns to show in the results list.', 'quiz-master-next' ),
					'body'        => $column_checks . wp_nonce_field( 'results_screen_option', 'results-screen_option_nonce', true, false ),
					'footer'      => $cancel_button . QSM_Admin_UI::button( __( 'Save', 'quiz-master-next' ), array( 'variant' => 'primary', 'type' => 'submit', 'attrs' => array( 'id' => 'save-results-screen-option-button' ) ) ),
				)
			);
			?>
		</form>
	</div>
	<?php
	if ( ! class_exists( 'QSM_Proctoring_Quiz' ) ) {
		$qsm_pop_up_arguments = array(
			"id"           => 'modal-proctor-quiz',
			"title"        => __('Secure Your Quizzes with Proctoring', 'quiz-master-next'),
			"description"  => __('Monitor and prevent cheating with the Quiz Proctor Addon.', 'quiz-master-next'),
			"chart_image"  => plugins_url('', __DIR__) . '/images/proctor_quiz_chart.png',
			"information"  => __('QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today OR you can buy Quiz Proctor Addon separately.', 'quiz-master-next'),
			"buy_btn_text" => __('Buy Quiz Proctor Addon', 'quiz-master-next'),
			"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/quiz-proctor/', 'quiz-documentation', 'plugin', 'quiz-proctor', 'qsm_plugin_upsell' ),
			"upgrade_link" => qsm_get_plugin_link( 'pricing', 'quiz-documentation', 'plugin', 'quiz-proctor', 'qsm_plugin_upsell' ),
			"addon_link"   => qsm_get_plugin_link( 'downloads/quiz-proctor', 'quiz-documentation', 'plugin', 'quiz-proctor', 'qsm_plugin_upsell' ),
			"list_items"   => array(
				__("Detect tab switching & enforce full-screen mode", "quiz-master-next"),
				__("Capture test-taker images during the quiz", "quiz-master-next"),
				__("Ensure fair and secure assessments", "quiz-master-next"),
			),
		);
		qsm_admin_upgrade_popup($qsm_pop_up_arguments);
	}
	}
	do_action('qsm_ultimate_display_actiivity_report_section');
}

function qsm_export_results_tabs_content() {
	$args = array(
		"id"           => 'export-results',
		"title"        => __( 'Effortlessly Export Quiz Data', 'quiz-master-next' ),
		"description"  => __( 'Manage quiz results with the Export Results Addon.', 'quiz-master-next' ),
		"chart_image"  => plugins_url( '', __DIR__ ) . '/images/export_results.png',
		"warning"      => __( 'Export Results Addon required', 'quiz-master-next' ),
		"information"  => __( 'QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today. OR you can buy Export Results Addon separately.', 'quiz-master-next' ),
		"buy_btn_text" => __( 'Buy Export Results Addon', 'quiz-master-next' ),
		"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/export-results', 'result_page', 'export_results', 'result-exportresults-upsell_read_documentation', 'qsm_plugin_upsell' ),
		"upgrade_link" => qsm_get_plugin_link( 'pricing', 'result_page', 'export_results', 'result-exportresults-upsell_upgrade', 'qsm_plugin_upsell' ),
		"addon_link"   => qsm_get_plugin_link( 'downloads/export-results', 'result_page', 'export_results', 'result-exportresults-upsell_buy_addon', 'qsm_plugin_upsell' ),
		"list_items"   => array(
			__("Export responses to CSV for analysis", "quiz-master-next"),
			__("Allow participants to download results as PDFs", "quiz-master-next"),
			__("Simplify reporting & record-keeping", "quiz-master-next"),
		),
	);
	qsm_admin_upgrade_popup( $args, 'page' );
}
function qsm_reporting_analysis_tabs_content() {
	$args = array(
		"id"           => 'reporting-analysis',
		"title"        => __( 'Gain Powerful Insights with In-Depth Reports', 'quiz-master-next' ),
		"description"  => __( 'Analyze performance trends with the Reporting & Analysis Addon.', 'quiz-master-next' ),
		"chart_image"  => plugins_url( '', __DIR__ ) . '/images/report_analysis_chart.png',
		"warning"      => __( 'Reporting & Analysis Addon required', 'quiz-master-next' ),
		"information"  => __( 'QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today. OR you can buy Reporting & Analysis Addon separately.', 'quiz-master-next' ),
		"buy_btn_text" => __( 'Buy Reporting & Analysis Addon', 'quiz-master-next' ),
		"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/reporting-analysis', 'result_page', 'result_analysis', 'result-reportanalysis-upsell_read_documentation', 'qsm_plugin_upsell' ),
		"upgrade_link" => qsm_get_plugin_link( 'pricing', 'result_page', 'result_analysis', 'result-reportanalysis-upsell_upgrade', 'qsm_plugin_upsell' ),
		"addon_link"   => qsm_get_plugin_link( 'downloads/results-analysis', 'result_page', 'result_analysis', 'result-reportanalysis-ups_buy_addon', 'qsm_plugin_upsell' ),
		"list_items"   => array(
			__("Track quiz performance over time", "quiz-master-next"),
			__("Customize reports for deeper insights", "quiz-master-next"),
			__("Optimize quiz results with data-driven decisions", "quiz-master-next"),
		),
	);
	qsm_admin_upgrade_popup( $args, 'page' );
}

function qsm_proctor_quiz_tabs_content() {
	$args = array(
		"id"           => 'proctoring-quiz',
		"title"        => __('Secure Your Quizzes with Proctoring', 'quiz-master-next'),
		"description"  => __('Monitor and prevent cheating with the Quiz Proctor Addon.', 'quiz-master-next'),
		"chart_image"  => plugins_url( '', __DIR__ ) . '/images/proctor_quiz_chart.png',
		"warning"      => __( 'Missing Feature - Quiz Proctor Add-on required', 'quiz-master-next' ),
		"information"  => __( 'QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today. OR you can buy Proctoring Quiz Addon separately.', 'quiz-master-next' ),
		"buy_btn_text" => __( 'Buy Quiz Proctor Addon', 'quiz-master-next' ),
		"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/quiz-proctor', 'quiz-documentation', 'plugin', 'quiz-proctor', 'qsm_plugin_upsell' ),
		"upgrade_link" => qsm_get_plugin_link( 'pricing', 'quiz-documentation', 'plugin', 'quiz-proctor', 'qsm_plugin_upsell' ),
		"addon_link"   => qsm_get_plugin_link( 'downloads/quiz-proctor', 'quiz-documentation', 'plugin', 'quiz-proctor', 'qsm_plugin_upsell' ),
		"list_items"   => array(
			__("Detect tab switching & enforce full-screen mode", "quiz-master-next"),
			__("Capture test-taker images during the quiz", "quiz-master-next"),
			__("Ensure fair and secure assessments", "quiz-master-next"),
		),
	);
	qsm_admin_upgrade_popup( $args, 'page' );
}
