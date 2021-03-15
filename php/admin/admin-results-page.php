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
	$active_tab = strtolower( str_replace( ' ', '-', isset( $_GET['tab'] ) ? $_GET['tab'] : __( 'Quiz Results', 'quiz-master-next' ) ) );
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
                   class="nav-tab <?php echo esc_attr( $active_class ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
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

		if ( false === $results ) {
			$error = $wpdb->last_error;
			if ( empty( $error ) ) {
				$error = __( 'Unknown error', 'quiz-master-next' );
			}
			$mlwQuizMasterNext->alertManager->newAlert( sprintf( __( 'There was an error when deleting this result. Error from WordPress: %s', 'quiz-master-next' ), $error ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error deleting result', "Tried {$wpdb->last_query} but got $error.", 0, 'error' );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Your results has been deleted successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Results Has Been Deleted From: $mlw_delete_results_name" );

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

			$mlwQuizMasterNext->audit_manager->new_audit( "Results Have Been Bulk Deleted" );
		}
	}

	// Prepares the SQL to retrieve the results.
	$table_limit       = 40;
	$search_phrase_sql = '';
	$order_by_sql      = 'ORDER BY result_id DESC';
	if ( isset( $_GET['qsm_search_phrase'] ) && ! empty( $_GET['qsm_search_phrase'] ) ) {
		// Sanitizes the search phrase and then uses $wpdb->prepare to properly escape the queries after using $wpdb->esc_like.
		$sanitized_search_phrase = sanitize_text_field( $_GET['qsm_search_phrase'] );
		$search_phrase_percents  = '%' . $wpdb->esc_like( $sanitized_search_phrase ) . '%';
		$search_phrase_sql       = $wpdb->prepare( ' AND (quiz_name LIKE %s OR name LIKE %s OR business LIKE %s OR email LIKE %s OR phone LIKE %s)', $search_phrase_percents, $search_phrase_percents, $search_phrase_percents, $search_phrase_percents, $search_phrase_percents );
	}
	if ( isset( $_GET['quiz_id'] ) && ! empty( $_GET['quiz_id'] ) ) {
		$quiz_id       = intval( $_GET['quiz_id'] );
		$qsm_results_count = $wpdb->get_var( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted=0 AND quiz_id='{$quiz_id}' {$search_phrase_sql}" );
	} else {
		$qsm_results_count = $wpdb->get_var( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted=0 {$search_phrase_sql}" );
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
				$order_by     = 'quiz_name';
				$order_by_sql = ' ORDER BY result_id DESC';
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
		$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted=0 AND quiz_id = %d $search_phrase_sql $order_by_sql LIMIT %d, %d", $quiz_id, $result_begin, $table_limit ) );
	} else {
		$mlw_quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE deleted=0 $search_phrase_sql $order_by_sql LIMIT %d, %d", $result_begin, $table_limit ) );
	}

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'qmn_admin_js', plugins_url( '../../js/admin.js', __FILE__ ) );
	wp_enqueue_style( 'qmn_jquery_redmond_theme', plugins_url( '../../css/jquery-ui.css', __FILE__ ) );
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array() );
	?>
    <script type="text/javascript">
      var $j = jQuery.noConflict();

      function deleteResults(id, quizName) {
        $j("#delete_dialog").dialog({
          autoOpen: false,
          buttons: {
            Cancel: function () {
              $j(this).dialog('close');
            }
          }
        });
        $j("#delete_dialog").dialog('open');
        var idHidden = document.getElementById("result_id");
        var idHiddenName = document.getElementById("delete_quiz_name");
        idHidden.value = id;
        idHiddenName.value = quizName;
      };
    </script>
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
            <span class="displaying-num"><?php echo sprintf( _n( 'One result', '%s results', $qsm_results_count, 'quiz-master-next' ), number_format_i18n( $qsm_results_count ) ); ?></span>
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
                    <a class="prev-page button"
                       href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$mlw_qmn_previous_page$url_query_string" ); ?>"><</a>
                    <span class="paging-input"><?php echo esc_html( $mlw_current_page ); ?> of <?php echo esc_html( $mlw_total_pages ); ?></span>
					<?php
					if ( $results_left > $table_limit ) {
						?>
                        <a class="next-page button"
                           href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$result_page$url_query_string" ); ?>">></a>
						<?php
					}
				} elseif ( 0 == $result_page ) {
					if ( $results_left > $table_limit ) {
						?>
                        <span class="paging-input"><?php echo esc_html( $mlw_current_page ); ?> of <?php echo esc_html( $mlw_total_pages ); ?></span>
                        <a class="next-page button"
                           href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$result_page$url_query_string" ); ?>">></a>
						<?php
					}
				} elseif ( $results_left < $table_limit ) {
					$mlw_qmn_previous_page = $result_page - 2;
					?>
                    <a class="prev-page button"
                       href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$mlw_qmn_previous_page$url_query_string" ); ?>"><</a>
                    <span class="paging-input"><?php echo esc_html( $mlw_current_page ); ?> of <?php echo esc_html( $mlw_total_pages ); ?></span>
                    <a class="next-page button"
                       href="<?php echo esc_url_raw( "?page=mlw_quiz_results&&qsm_results_page=$result_page$url_query_string" ); ?>">></a>
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
                <input type="hidden" name="quiz_id" value="<?php echo esc_attr( intval( $_GET['quiz_id'] ) ); ?>"/>
				<?php
			}
			?>
            <input type="hidden" name="page" value="mlw_quiz_results">
            <p class="search-box">
                <label for="qsm_search_phrase"><?php esc_html_e( 'Search Results', 'quiz-master-next' ); ?></label>
                <input type="search" id="qsm_search_phrase" name="qsm_search_phrase" value="">
                <label for="qmn_order_by"><?php esc_html_e( 'Order By', 'quiz-master-next' ); ?></label>
                <select id="qmn_order_by" name="qmn_order_by">
                    <option value="quiz_name"><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?></option>
                    <option value="name"><?php esc_html_e( 'User Name', 'quiz-master-next' ); ?></option>
                    <option value="point_score"><?php esc_html_e( 'Points', 'quiz-master-next' ); ?></option>
                    <option value="correct_score"><?php esc_html_e( 'Correct Percent', 'quiz-master-next' ); ?></option>
                    <option value="default"><?php esc_html_e( 'Default (Time)', 'quiz-master-next' ); ?></option>
                </select>
                <button class="button"><?php esc_html_e( 'Search Results', 'quiz-master-next' ); ?></button>
            </p>
        </form>
    </div>

    <form action="" method="post" name="bulk_delete_form">
        <input type="hidden" name="bulk_delete" value="confirmation"/>
        <input type="hidden" name="bulk_permanent_delete" id="bulk_permanent_delete" value="0"/>
		<?php wp_nonce_field( 'bulk_delete', 'bulk_delete_nonce' ); ?>
        <table class=widefat>
            <thead>
            <tr>
                <th><input type="checkbox" id="qmn_check_all"/></th>
                <th><?php esc_html_e( 'Quiz Name','quiz-master-next' ); ?></th>
				<?php
				$table_heading_displays = '';
				$table_heading_displays .= '<th>' . esc_html__( 'Score', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'Time To Complete', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'Name', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'Business', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'Email', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'Phone', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'User', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'Time Taken', 'quiz-master-next' ) . '</th>';
				$table_heading_displays .= '<th>' . esc_html__( 'IP Address', 'quiz-master-next' ) . '</th>';

				$table_heading_displays = apply_filters('mlw_qmn_admin_results_page_headings', $table_heading_displays);
				echo $table_heading_displays;
				?>
            </tr>
            </thead>
			<?php
			$quotes_list = "";
			$display     = "";
			$alternate   = "";
			if ( $mlw_quiz_data ) {
				foreach ( $mlw_quiz_data as $mlw_quiz_info ) {
					$quiz_result_item = '';
					$quiz_result_item_inner = '';
					if ( $alternate ) {
						$alternate = '';
					} else {
						$alternate = ' class="alternate"';
					}
					$mlw_complete_time     = '';
					$mlw_qmn_results_array = @unserialize( $mlw_quiz_info->quiz_results );
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
						$mlw_complete_time    .= "$mlw_complete_seconds seconds";
					}

					$out_of_q    = $mlw_quiz_info->total - $hidden_questions;
					$quiz_result_item .= "<tr{$alternate}>";
					$quiz_result_item .= "<td><input type='checkbox' class='qmn_delete_checkbox' name='delete_results[]' value='" . $mlw_quiz_info->result_id . "' /></td>";
					$quiz_result_item .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->quiz_name . "</span><div class='row-actions'><span style='color:green;font-size:16px;'><a href='admin.php?page=qsm_quiz_result_details&&result_id=" . $mlw_quiz_info->result_id . "'>View</a> | <a style='color: red;' onclick=\"deleteResults('" . $mlw_quiz_info->result_id . "','" . esc_js( $mlw_quiz_info->quiz_name ) . "')\" href='#'>Delete</a></span></div></td>";
					$form_type   = isset( $mlw_quiz_info->form_type ) ? $mlw_quiz_info->form_type : 0;
					if ( $form_type == 1 || $form_type == 2 ) {
						$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . __( 'Not Graded', 'quiz-master-next' ) . "</span></td>";
					} else {
						if ( $mlw_quiz_info->quiz_system == 0 ) {
							$quiz_result_item_inner .= "<td class='post-title column-title'><span style='font-size:16px;'>" . $mlw_quiz_info->correct . " out of " . $out_of_q . " or " . $mlw_quiz_info->correct_score . "%</span></td>";
						}
						if ( $mlw_quiz_info->quiz_system == 1 ) {
							$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->point_score . " Points</span></td>";
						}
						if ( $mlw_quiz_info->quiz_system == 3 ) {
							$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->correct . " out of " . $out_of_q . " or " . $mlw_quiz_info->correct_score . "%</span><br/><span style='font-size:16px;'>" . $mlw_quiz_info->point_score . " Points</span></td>";
						}
					}
					$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_complete_time . "</span></td>";
					$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->name . "</span></td>";
					$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->business . "</span></td>";
					$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->email . "</span></td>";
					$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->phone . "</span></td>";
					if ( 0 == $mlw_quiz_info->user ) {
						$quiz_result_item_inner .= "<td><span style='font-size:16px;'>Visitor</span></td>";
					} else {
						$quiz_result_item_inner .= "<td><span style='font-size:16px;'><a href='user-edit.php?user_id=" . $mlw_quiz_info->user . "'>" . $mlw_quiz_info->user . "</a></span></td>";
					}
					$date        = date_i18n( get_option( 'date_format' ), strtotime( $mlw_quiz_info->time_taken ) );
					$time        = date( "h:i:s A", strtotime( $mlw_quiz_info->time_taken ) );
					$quiz_result_item_inner .= "<td><span style='font-size:16px;'><abbr title='$date $time'>$date</abbr></span></td>";
					$quiz_result_item_inner .= "<td><span style='font-size:16px;'>" . $mlw_quiz_info->user_ip . "</span></td>";
					$quiz_result_item .= apply_filters('mlw_qmn_admin_results_page_result', $quiz_result_item_inner, $mlw_quiz_info);
					$quiz_result_item .= "</tr>";
					$quotes_list .= $quiz_result_item;
				}
			} else {
				$quotes_list .= "<tr{$alternate}><td colspan='12' style='text-align: center;'>" . __( 'No record found.', 'quiz-master-next' ) . "</td></tr>";
			}
			$display .= "<tbody id=\"the-list\">{$quotes_list}</tbody>";
			echo $display;
			?>
        </table>
    </form>

    <div id="delete_dialog" title="Delete Results?" style="display:none;">
        <h3><b><?php esc_html_e( 'Are you sure you want to delete these results?', 'quiz-master-next' ); ?></b></h3>
        <form action='' method='post'>
			<?php wp_nonce_field( 'delete_results', 'delete_results_nonce' ); ?>
            <input type='hidden' id='result_id' name='result_id' value=''/>
            <input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value=''/>
            <p class='submit'><input type='submit' class='button-primary'
                                     value='<?php esc_html_e( 'Delete Results', 'quiz-master-next' ); ?>'/></p>
        </form>
    </div>
	<?php
}

?>
