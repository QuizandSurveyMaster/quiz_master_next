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
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}

	// Retrieves the current stab and all registered tabs.
	global $mlwQuizMasterNext;
	$active_tab = strtolower( str_replace( ' ', '-', isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : __( 'Quiz Results', 'quiz-master-next' ) ) );
	$tab_array  = $mlwQuizMasterNext->pluginHelper->get_admin_results_tabs();

	?>
<div class="wrap">
	<h2><?php esc_html_e( 'Quiz Results', 'quiz-master-next' ); ?></h2>
	<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
	<?php qsm_show_adverts(); ?>
	<h2 class="nav-tab-wrapper">
		<?php
			// Cycles through the tabs and creates the navigation.
			foreach ( $tab_array as $tab ) {
				$active_class = '';
				if ( $active_tab == $tab['slug'] ) {
					$active_class = 'nav-tab-active';
				}
				$tab_url = "?page=mlw_quiz_results&tab={$tab['slug']}";
				?>
		<a href="<?php echo esc_url_raw( $tab_url ); ?>"
			class="nav-tab <?php echo esc_attr( $active_class ); ?>"><?php echo wp_kses_post( $tab['title'] ); ?></a>
		<?php
			}
			?>
	</h2>
	<div class="result-page-wrapper">
		<?php
			// Locates the active tab and calls its content function.
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
 * Adds Overview Tab To Admin Results Page
 *
 * @since 5.0.0
 * @return void
 */
function qsm_results_overview_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_admin_results_tab( __( 'Quiz Results', 'quiz-master-next' ), 'qsm_results_overview_tab_content' );
}

add_action( 'plugins_loaded', 'qsm_results_overview_tab' );

/**
 * Generates HTML For Overview Tab
 *
 * @since 5.0.0
 * @return void
 */
function qsm_results_overview_tab_content() {

	global $wpdb;
	global $mlwQuizMasterNext;

	// If nonce is correct, delete results.
	if ( isset( $_POST['delete_results_nonce'] ) && wp_verify_nonce( $_POST['delete_results_nonce'], 'delete_results' ) ) {

		// Variables from delete result form.
		$mlw_delete_results_id   = intval( $_POST['result_id'] );
		$mlw_delete_results_name = sanitize_text_field( $_POST['delete_quiz_name'] );
		do_action('qsm_before_delete_result' , $mlw_delete_results_id);
		// Updates table to mark results as deleted.
		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_results',
			array(
				'deleted' => 1,
			),
			array( 'result_id' => $mlw_delete_results_id ),
			array(
				'%d',
			),
			array( '%d' )
		);
		//get quiz id
		$quiz_id  = isset( $_GET['quiz_id'] ) ? intval( sanitize_text_field( $_GET['quiz_id'] ) ) : 0;
		if ( false === $results ) {
			$error = $wpdb->last_error;
			if ( empty( $error ) ) {
				$error = __( 'Unknown error', 'quiz-master-next' );
			}
			$mlwQuizMasterNext->alertManager->newAlert( sprintf( __( 'There was an error when deleting this result. Error from WordPress: %s', 'quiz-master-next' ), $error ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error deleting result', "Tried {$wpdb->last_query} but got $error.", 0, 'error' );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Your results has been deleted successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Results Has Been Deleted From: $mlw_delete_results_name",  $quiz_id);

		}
	}

	// Check if bulk delete has been selected. If so, verify nonce.
	if ( isset( $_POST["bulk_delete"] ) && wp_verify_nonce( $_POST['bulk_delete_nonce'], 'bulk_delete' ) ) {

		// Ensure the POST variable is an array
		if ( isset( $_POST["delete_results"] ) && is_array( $_POST["delete_results"] ) ) {

			// Cycle through the POST array which should be an array of the result ids of the results the user wishes to delete
			foreach ( $_POST["delete_results"] as $result ) {

				// Santize by ensuring the value is an int
				$result_id = intval( $result );
				if ( isset( $_POST['bulk_permanent_delete'] ) && $_POST['bulk_permanent_delete'] == 1 ) {
					$wpdb->delete(
						$wpdb->prefix . "mlw_results",
						array( 'result_id' => $result_id )
					);
				} else {
					$wpdb->update(
						$wpdb->prefix . "mlw_results",
						array(
							'deleted' => 1,
						),
						array( 'result_id' => $result_id ),
						array(
							'%d'
						),
						array( '%d' )
					);
				}
			}

			$mlwQuizMasterNext->audit_manager->new_audit( "Results Have Been Bulk Deleted", $quiz_id );
		}
	}

	// Prepares the SQL to retrieve the results.
	$table_limit       = 40;
	$search_phrase_sql = '';
	$delete = 'deleted=0';
	$delete  = apply_filters( 'qsm_results_delete_clause', $delete );
	$order_by_sql      = 'ORDER BY time_taken_real DESC';
	if ( isset( $_GET['qsm_search_phrase'] ) && ! empty( $_GET['qsm_search_phrase'] ) ) {
		// Sanitizes the search phrase and then uses $wpdb->prepare to properly escape the queries after using $wpdb->esc_like.
		$sanitized_search_phrase = sanitize_text_field( wp_unslash( $_GET['qsm_search_phrase'] ) );
		$search_phrase_percents  = '%' . esc_sql( $wpdb->esc_like( $sanitized_search_phrase ) ) . '%';
		$search_phrase_sql       = $wpdb->prepare( ' AND (quiz_name LIKE %s OR name LIKE %s OR business LIKE %s OR email LIKE %s OR phone LIKE %s)', $search_phrase_percents, $search_phrase_percents, $search_phrase_percents, $search_phrase_percents, $search_phrase_percents );
	}
	if ( isset( $_GET['quiz_id'] ) && ! empty( $_GET['quiz_id'] ) ) {
		$quiz_id       = intval( $_GET['quiz_id'] );
		$qsm_results_count = $wpdb->get_var( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE {$delete} AND quiz_id='{$quiz_id}' {$search_phrase_sql}" );
	} else {
		$qsm_results_count = $wpdb->get_var( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE {$delete} {$search_phrase_sql}" );
	}

	// Gets the order by arg. Uses switch to create SQL to prevent SQL injection.
	if ( isset( $_GET['qmn_order_by'] ) ) {
		switch ( $_GET['qmn_order_by'] ) {
			case 'quiz_name':
				$order_by     = 'quiz_name';
				$order_by_sql = ' ORDER BY quiz_name DESC';
				break;
			case 'name':
				$order_by     = 'name';
				$order_by_sql = ' ORDER BY name DESC';
				break;
			case 'point_score':
				$order_by     = 'point_score';
				$order_by_sql = ' ORDER BY point_score DESC';
				break;
			case 'correct_score':
				$order_by     = 'correct_score';
				$order_by_sql = ' ORDER BY correct_score DESC';
				break;
			default:
				$order_by     = 'time_taken_real';
				$order_by_sql = ' ORDER BY time_taken_real DESC';
		}
	}

	if ( isset( $_GET['qsm_results_page'] ) ) {
		$result_page  = intval( $_GET['qsm_results_page'] ) + 1;
		$result_begin = $table_limit * $result_page;
	} else {
		$result_page  = 0;
		$result_begin = 0;
	}
	$results_left = $qsm_results_count - ( $result_page * $table_limit );
	if ( isset( $_GET['quiz_id'] ) && ! empty( $_GET['quiz_id'] ) ) {
		$quiz_id       = intval( $_GET['quiz_id'] );
		$mlw_quiz_data = $wpdb->get_results(stripslashes( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE $delete AND quiz_id = %d $search_phrase_sql $order_by_sql LIMIT %d, %d", $quiz_id, $result_begin, $table_limit ) ) );
	} else {
		$mlw_quiz_data = $wpdb->get_results(stripslashes( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE $delete $search_phrase_sql $order_by_sql LIMIT %d, %d", $result_begin, $table_limit ) ) );
	}

	?>
<div class="tablenav top">
	<div class="alignleft actions bulkactions">
		<a id="result_bulkaction" href="javascript: void(0);"
			onclick="if( confirm('Are you sure?') ){ document.bulk_delete_form.submit(); }" class="button action">Bulk
			Delete</a>&nbsp;&nbsp;&nbsp;
		<a href="javascript: void(0);"
			onclick="if( confirm('Are you sure?') ){ document.getElementById('bulk_permanent_delete').value = '1'; document.bulk_delete_form.submit(); }"
			class="button action">Bulk Permanent Delete</a>
	</div>
	<div class="tablenav-pages">
		<span
			class="displaying-num"><?php echo sprintf( _n( 'One result', '%s results', $qsm_results_count, 'quiz-master-next' ), number_format_i18n( $qsm_results_count ) ); ?></span>
		<span class="pagination-links">
			<?php
				$mlw_qmn_previous_page = 0;
				$mlw_current_page      = $result_page + 1;
				$mlw_total_pages       = ceil( $qsm_results_count / $table_limit );

				$url_query_string = '';
				if ( isset( $_GET['quiz_id'] ) && ! empty( $_GET['quiz_id'] ) ) {
					$url_query_string .= '&&quiz_id=' . intval( $_GET['quiz_id'] );
				}

				if ( isset( $_GET['qsm_search_phrase'] ) && ! empty( $_GET['qsm_search_phrase'] ) ) {
					$url_query_string .= "&&qsm_search_phrase=$sanitized_search_phrase";
				}

				if ( isset( $_GET['qmn_order_by'] ) && ! empty( $_GET['qmn_order_by'] ) ) {
					$url_query_string .= "&&qmn_order_by=$order_by";
				}

				if ( $result_page > 0 ) {
					$mlw_qmn_previous_page = $result_page - 2;
					?>
					<a class="prev-page button" href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$mlw_qmn_previous_page$url_query_string" ); ?>"><</a>
					<span class="paging-input"><?php echo esc_html( $mlw_current_page ); ?> of <?php echo esc_html( $mlw_total_pages ); ?></span>
					<?php
					if ( $results_left > $table_limit ) {
						?>
						<a class="next-page button" href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$result_page$url_query_string" ); ?>">></a>
						<?php
					}
				} elseif ( 0 == $result_page ) {
					if ( $results_left > $table_limit ) {
						?>
						<span class="paging-input"><?php echo esc_html( $mlw_current_page ); ?> of <?php echo esc_html( $mlw_total_pages ); ?></span>
						<a class="next-page button" href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$result_page$url_query_string" ); ?>">></a>
						<?php
					}
				} elseif ( $results_left < $table_limit ) {
					$mlw_qmn_previous_page = $result_page - 2;
					?>
					<a class="prev-page button" href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$mlw_qmn_previous_page$url_query_string" ); ?>"><< /a>
					<span class="paging-input"><?php echo esc_html( $mlw_current_page ); ?> of <?php echo esc_html( $mlw_total_pages ); ?></span>
					<a class="next-page button" href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$result_page$url_query_string" ); ?>">></a>
					<?php
				}
				?>
		</span>
		<br class="clear">
	</div>
	<form action='' method="get">
		<?php
			if ( isset( $_GET['quiz_id'] ) ) {
				?>
		<input type="hidden" name="quiz_id" value="<?php echo intval( $_GET['quiz_id'] ); ?>" />
		<?php
			}
			$qsm_search_phrase = ( isset( $_GET['qsm_search_phrase'] ) ) ? sanitize_text_field( $_GET['qsm_search_phrase'] ) : '';
			$qmn_order_by = ( isset( $_GET['qmn_order_by'] ) && ! empty( $_GET['qmn_order_by'] ) ) ? sanitize_text_field( $_GET['qmn_order_by'] ) : 'default';
			?>
		<input type="hidden" name="page" value="mlw_quiz_results">
		<p class="search-box">
			<label for="qsm_search_phrase"><?php esc_html_e( 'Search Results', 'quiz-master-next' ); ?></label>
			<input type="search" id="qsm_search_phrase" name="qsm_search_phrase" value="<?php echo esc_attr($qsm_search_phrase); ?>">
			<label for="qmn_order_by"><?php esc_html_e( 'Order By', 'quiz-master-next' ); ?></label>
			<select id="qmn_order_by" name="qmn_order_by">
				<option value="default" <?php selected( $qmn_order_by, 'default' ); ?>><?php esc_html_e( 'Default (Time)', 'quiz-master-next' ); ?></option>
				<option value="quiz_name" <?php selected( $qmn_order_by, 'quiz_name' ); ?>><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?></option>
				<option value="name" <?php selected( $qmn_order_by, 'name' ); ?>><?php esc_html_e( 'User Name', 'quiz-master-next' ); ?></option>
				<option value="point_score" <?php selected( $qmn_order_by, 'point_score' ); ?>><?php esc_html_e( 'Points', 'quiz-master-next' ); ?></option>
				<option value="correct_score" <?php selected( $qmn_order_by, 'correct_score' ); ?>><?php esc_html_e( 'Correct Percent', 'quiz-master-next' ); ?></option>
			</select>
			<button class="button"><?php esc_html_e( 'Search Results', 'quiz-master-next' ); ?></button>
		</p>
	</form>
</div>

<form action="" method="post" name="bulk_delete_form">
	<input type="hidden" name="bulk_delete" value="confirmation" />
	<input type="hidden" name="bulk_permanent_delete" id="bulk_permanent_delete" value="0" />
	<?php wp_nonce_field( 'bulk_delete', 'bulk_delete_nonce' );
	
	$th_elements = apply_filters( 'mlw_qmn_admin_results_page_headings', array(
		'score' 		=> __( 'Score', 'quiz-master-next' ),
		'time_complete' => __( 'Time To Complete', 'quiz-master-next' ),
		'name' 			=> __( 'Name', 'quiz-master-next' ),
		'business' 		=> __( 'Business', 'quiz-master-next' ),
		'email' 		=> __( 'Email', 'quiz-master-next' ),
		'phone' 		=> __( 'Phone', 'quiz-master-next' ),
		'user' 			=> __( 'User', 'quiz-master-next' ),
		'time_taken' 	=> __( 'Time Taken', 'quiz-master-next' ),
		'ip' 			=> __( 'IP Address', 'quiz-master-next' ),
	) );

	$values = $quiz_infos = [];
	foreach( $th_elements as $key => $th ) {
		$values[$key]['title'] = $th;
	}

	if ( $mlw_quiz_data ) {
		foreach ( $mlw_quiz_data as $mlw_quiz_info ) {
			$quiz_infos[] = $mlw_quiz_info;
			$mlw_complete_time     = '';
			$mlw_qmn_results_array = maybe_unserialize( $mlw_quiz_info->quiz_results );
			$hidden_questions      = isset( $mlw_qmn_results_array['hidden_questions'] ) ? count( $mlw_qmn_results_array['hidden_questions'] ) : 0;
			if ( is_array( $mlw_qmn_results_array ) ) {
				$mlw_complete_hours = floor( $mlw_qmn_results_array[0] / 3600 );
				if ( $mlw_complete_hours > 0 ) {
					$mlw_complete_time .= "$mlw_complete_hours hours ";
				}
				$mlw_complete_minutes = floor( ( $mlw_qmn_results_array[0] % 3600 ) / 60 );
				if ( $mlw_complete_minutes > 0 ) {
					$mlw_complete_time .= "$mlw_complete_minutes minutes ";
				}
				$mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
				$mlw_complete_time .= "$mlw_complete_seconds seconds";
			}

			$out_of_q = $mlw_quiz_info->total - $hidden_questions;
			$form_type = isset( $mlw_quiz_info->form_type ) ? $mlw_quiz_info->form_type : 0;

			if ( isset( $values['score'] ) ) {
				if ( $form_type == 1 || $form_type == 2 ) {
					$values['score']['content'][] = esc_html__( 'Not Graded', 'quiz-master-next' );
				} else {
					if ( $mlw_quiz_info->quiz_system == 0 ) {
						$values['score']['content'][] = sprintf( '%1$s %2$s %3$s %4$s %5$s', esc_html( $mlw_quiz_info->correct ), esc_html__( 'out of', 'quiz-master-next' ), esc_html( $out_of_q ), esc_html__( 'or', 'quiz-master-next' ), esc_html( $mlw_quiz_info->correct_score ) );
					} else if ( $mlw_quiz_info->quiz_system == 1 ) {
						$values['score']['content'][] = sprintf( '%1$s %2$s', esc_html( $mlw_quiz_info->point_score ), esc_html__( 'Points', 'quiz-master-next' ) );
					} else if ( $mlw_quiz_info->quiz_system == 3 ) { 
						$values['score']['content'][] = sprintf( '%1$s %2$s %3$s %4$s %5$s <br /> %6$s %7$s', esc_html( $mlw_quiz_info->correct ), esc_html__( 'out of', 'quiz-master-next' ), esc_html( $out_of_q ), esc_html__( 'or', 'quiz-master-next' ), esc_html( $mlw_quiz_info->correct_score ), esc_html( $mlw_quiz_info->point_score ), esc_html__( 'Points', 'quiz-master-next' ) );
					} else { 
						$values['score']['content'][] = esc_html__( 'Not Graded', 'quiz-master-next' );
					}
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
				if ( 0 == $mlw_quiz_info->user ) {
					$values['user']['content'][] = esc_html__( 'Visitor', 'quiz-master-next' );
				} else {
					$values['user']['content'][] = '<a href="' . esc_url( admin_url( 'user-edit.php?user_id=' . $mlw_quiz_info->user ) ) . '">' . esc_html( $mlw_quiz_info->user ) . '</a>';
				}
			}

			$date = date_i18n( get_option( 'date_format' ), strtotime( $mlw_quiz_info->time_taken ) );
			$time = date( "h:i:s A", strtotime( $mlw_quiz_info->time_taken ) );
			
			if ( isset( $values['time_taken'] ) ) {
				$values['time_taken']['content'][] = '<abbr title="' . esc_attr( $date . $time ) . '">' . esc_html( $date ) . '</abbr>';
			}

			if ( isset( $values['ip'] ) ) {
				$values['ip']['content'][] = $mlw_quiz_info->user_ip;
			}

			foreach( $values as $k => $v ) {
				if ( ! in_array( $k, [ 'score', 'time_complete', 'name', 'business', 'email', 'phone', 'user', 'time_taken', 'ip' ] ) ) {
					$content = apply_filters( 'mlw_qmn_admin_results_page_column_content', '', $mlw_quiz_info, $k );
					if ( isset( $values[$k] ) && ! empty( $content ) ) {
						$values[$k]['content'][] = $content;
					}
				}
			}
		}
	} ?>

	<table class="widefat" aria-label="<?php esc_html_e( 'Results Table','quiz-master-next' ); ?>">
		<thead>
			<tr>
				<th><input type="checkbox" id="qmn_check_all" /></th>
				<th><?php esc_html_e( 'Quiz Name','quiz-master-next' ); ?></th>
				<?php foreach( $values as $k => $v ) {
					if ( ! empty( $v['content'] ) ) {
						echo '<th>' . esc_html( $v['title'] ) . '</th>';
					}
				} ?>
			</tr>
		</thead>
		<tbody id="the-list">
			<?php 
			$co = ! empty( $quiz_infos ) ? count( $quiz_infos ) : 0;
			if ( $co > 0 ) {
				for ( $x = 0; $x < $co; $x++ ) { ?>
					<tr>
						<td><input type="checkbox" class="qmn_delete_checkbox" name="delete_results[]" value="<?php echo esc_attr( $quiz_infos[$x]->result_id ); ?>" /></td>
						<td><span style="font-size:16px;"><?php echo esc_html( $quiz_infos[$x]->quiz_name ); ?></span><div class="row-actions"><span style="color:green;font-size:16px;"><a href="admin.php?page=qsm_quiz_result_details&result_id=<?php echo esc_attr( $quiz_infos[$x]->result_id ); ?>"><?php esc_html_e( 'View', 'quiz-master-next' ); ?></a> | <a style="color: red;" class="delete_table_quiz_results_item" data-quiz-id="<?php echo esc_attr( $quiz_infos[$x]->result_id ); ?>" data-quiz-name="<?php echo esc_attr( $quiz_infos[$x]->quiz_name ); ?>" href='#'><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></a></span></div></td>
						<?php
						foreach( $values as $k => $v ) {
							if ( isset( $v['content'][$x] ) ) {
								echo '<td><span style="font-size:16px;">' . wp_kses_post( apply_filters( 'mlw_qmn_admin_results_page_result', $v['content'][$x], $quiz_infos[$x], $k ) ) . '</span></td>';
							}
						} ?>
					</tr><?php
				}
			} else { ?>
				<tr>
					<td colspan="12" style="text-align: center;"><?php esc_html_e( 'No record found.', 'quiz-master-next' ); ?></td>
				</tr><?php
			} ?>
		</tbody>
	</table>
</form>

<div id="delete_dialog" title="Delete Results?" style="display:none;">
	<h3><b><?php esc_html_e( 'Are you sure you want to delete these results?', 'quiz-master-next' ); ?></b></h3>
	<form action='' method='post'>
		<?php wp_nonce_field( 'delete_results', 'delete_results_nonce' ); ?>
		<input type='hidden' id='result_id' name='result_id' value='' />
		<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />
		<p class='submit'><input type='submit' class='button-primary'
				value='<?php esc_html_e( 'Delete Results', 'quiz-master-next' ); ?>' /></p>
	</form>
</div>
<?php
}
?>
