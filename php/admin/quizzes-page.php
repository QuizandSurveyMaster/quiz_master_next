<?php
/**
 * This file handles the contents on the "Quizzes/Surveys" page.
 *
 * @package QSM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'views_edit-qsm_quiz', 'qsm_quiz_list_views_edit' );

/**
 * Filter views links for quiz post type
 * @param type $views
 * @return type
 */
function qsm_quiz_list_views_edit( $views ) {
	unset( $views['trash'] );
	return $views;
}

add_filter( 'manage_qsm_quiz_posts_columns', 'qsm_set_custom_edit_qsm_quiz_columns' );

/**
 * Add the custom columns to the quiz post type:
 * @param array $columns
 * @return type
 */
function qsm_set_custom_edit_qsm_quiz_columns( $columns ) {
	if ( isset( $_REQUEST['post_status'] ) && 'trash' == $_REQUEST['post_status'] ) {
		return $columns;
	}
	unset( $columns['author'] );
	unset( $columns['comments'] );
	unset( $columns['date'] );
	$columns['shortcode']	 = __( 'Shortcode', 'quiz-master-next' );
	$columns['views']		 = __( 'Views', 'quiz-master-next' );
	$columns['participants'] = __( 'Participants', 'quiz-master-next' );
	$columns['lastActivity'] = __( 'Last Modified', 'quiz-master-next' );
	return $columns;
}

add_action( 'manage_qsm_quiz_posts_custom_column', 'qsm_custom_qsm_quiz_columns', 99, 2 );

/**
 * Add the data to the custom columns for the quiz post type:
 * @param type $column
 * @param type $post_id
 */
function qsm_custom_qsm_quiz_columns( $column, $post_id ) {
	global $wpdb;
	$quiz_id	 = get_post_meta( $post_id, 'quiz_id', true );
	switch ( $column ) {
		case 'shortcode' :
			$shortcode_links = '<a href="#" class="qsm-list-shortcode-view"><span class="dashicons dashicons-welcome-view-site"></span></a>';
			$shortcode_links .= '<div class="sc-content sc-embed">[qsm quiz=' . $quiz_id . ']</div>';
			$shortcode_links .= '<div class="sc-content sc-link">[qsm_link id=' . $quiz_id . ']' . __( 'Click here', 'quiz-master-next' ) . '[/qsm_link]</div>';
			echo wp_kses_post( $shortcode_links );
			break;

		case 'views' :
			$quiz_views	 = $wpdb->get_var( "SELECT `quiz_views` FROM `{$wpdb->prefix}mlw_quizzes` WHERE `quiz_id` = '{$quiz_id}'" );
			$views_html = $quiz_views;
			$views_html .= '<div class="row-actions">';
			$views_html .= '<a class="qsm-action-link qsm-action-link-reset" href="#" data-id="' . esc_attr( $quiz_id ) . '">'.__( 'Reset', 'quiz-master-next' ).'</a>';
			$views_html .= '</div>';
			echo wp_kses_post( $views_html );
			break;

		case 'participants' :
			$quiz_results_count	 = $wpdb->get_var( "SELECT COUNT(result_id) FROM `{$wpdb->prefix}mlw_results` WHERE `deleted`= 0 AND `quiz_id`= '{$quiz_id}'" );
			$participants		 = '<span class="column-comments">';
			$participants		 .= '<span class="post-com-count post-com-count-approved">';
			$participants		 .= '<span class="comment-count-approved" aria-hidden="true">' . $quiz_results_count . '</span>';
			$participants		 .= '<span class="screen-reader-text">' . $quiz_results_count . __( 'Participants', 'quiz-master-next' ) . '</span>';
			$participants		 .= '</span>';
			$participants		 .= '</span>';
			echo wp_kses_post( $participants );
			break;

		case 'lastActivity' :
			$last_activity	 = $wpdb->get_var( "SELECT `last_activity` FROM `{$wpdb->prefix}mlw_quizzes` WHERE `quiz_id` = '{$quiz_id}'" );
			$activity_date	 = gmdate( get_option( 'date_format' ), strtotime( $last_activity ) );
			$activity_time	 = gmdate( 'h:i:s A', strtotime( $last_activity ) );
			$lastActivity	 = '<span class="column-comments">';
			echo wp_kses_post( '<abbr title="' . $activity_date . ' ' . $activity_time . '">' . $activity_date . '</abbr>' );
			break;
	}
}

add_filter( 'post_row_actions', 'qsm_post_row_actions', 10, 2 );

/**
 * Add action links for each quiz post type
 * @param array $actions
 * @param type $post
 * @return string
 */
function qsm_post_row_actions( $actions, $post ) {
	$post_status = isset( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : 'all';
	if ( 'qsm_quiz' == $post->post_type && 'trash' != $post_status ) {
		$quiz_id = get_post_meta( $post->ID, 'quiz_id', true );
		$actions = array(
			'edit'			 => '<a class="qsm-action-link" href="admin.php?page=mlw_quiz_options&quiz_id=' . esc_attr( $quiz_id ) . '">' . esc_html( 'Edit', 'quiz-master-next' ) . '</a>',
			'duplicate'		 => '<a class="qsm-action-link qsm-action-link-duplicate" href="#" data-id="' . esc_attr( $quiz_id ) . '">' . esc_html( 'Duplicate', 'quiz-master-next' ) . '</a>',
			'delete'		 => '<a class="qsm-action-link qsm-action-link-delete" href="#" data-id="' . esc_attr( $quiz_id ) . '" data-name="' . esc_attr( $post->post_title ) . '">' . esc_html( 'Delete', 'quiz-master-next' ) . '</a>',
			'view_results'	 => '<a class="qsm-action-link" href="admin.php?page=mlw_quiz_results&quiz_id=' . esc_attr( $quiz_id ) . '">' . esc_html( 'View Results', 'quiz-master-next' ) . '</a>',
			'view'			 => '<a class="qsm-action-link" target="_blank" rel="noopener" href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( 'Preview', 'quiz-master-next' ) . '</a>',
		);
	}
	return $actions;
}

add_action( 'load-edit.php', 'qsm_process_post_row_actions', -99 );
/**
 * Handle custom link actions.
 * @global type $wpdb
 * @global type $mlwQuizMasterNext
 */
function qsm_process_post_row_actions() {
	global $wpdb, $pagenow, $mlwQuizMasterNext;
	if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'qsm_quiz' == $_GET['post_type'] ) {
		// Delete quiz.
		if ( isset( $_POST['qsm_delete_quiz_nonce'], $_POST['delete_quiz_id'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_delete_quiz_nonce'] ) ), 'qsm_delete_quiz' ) ) {
			$quiz_id	 = sanitize_text_field( wp_unslash( $_POST['delete_quiz_id'] ) );
			$quiz_id	 = intval( str_replace( 'QID', '', $quiz_id ) );
			do_action( 'qsm_before_delete_quiz', $quiz_id );
			$quiz_name	 = isset( $_POST['delete_quiz_name'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_quiz_name'] ) ) : '';
			$mlwQuizMasterNext->quizCreator->delete_quiz( $quiz_id, $quiz_name );
		}

		// Duplicate Quiz.
		if ( isset( $_POST['qsm_duplicate_quiz_nonce'], $_POST['duplicate_quiz_id'], $_POST['duplicate_new_quiz_name'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_duplicate_quiz_nonce'] ) ), 'qsm_duplicate_quiz' ) ) {
			$quiz_id	 = sanitize_text_field( wp_unslash( $_POST['duplicate_quiz_id'] ) );
			$quiz_id	 = intval( str_replace( 'QID', '', $quiz_id ) );
			$quiz_name	 = isset( $_POST['duplicate_new_quiz_name'] ) ? htmlspecialchars( sanitize_text_field( wp_unslash( $_POST['duplicate_new_quiz_name'] ) ), ENT_QUOTES ) : '';
			$mlwQuizMasterNext->quizCreator->duplicate_quiz( $quiz_id, $quiz_name, isset( $_POST['duplicate_questions'] ) ? sanitize_text_field( wp_unslash( $_POST['duplicate_questions'] ) ) : 0  );
		}
		
		// Resets stats for a quiz.
		if ( isset( $_POST['qsm_reset_stats_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_reset_stats_nonce'] ) ), 'qsm_reset_stats' ) ) {
			$quiz_id		 = isset( $_POST['reset_quiz_id'] ) ? intval( $_POST['reset_quiz_id'] ) : '';
			$quiz_post_id	 = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'quiz_id' AND meta_value = '$quiz_id'" );
			if ( empty( $quiz_post_id ) || ! current_user_can( 'edit_post', $quiz_post_id ) ) {
				$mlwQuizMasterNext->alertManager->newAlert( __( 'Sorry, you are not allowed to reset this quiz.', 'quiz-master-next' ), 'error' );
			} else {
				$results = $wpdb->update( $wpdb->prefix . 'mlw_quizzes', array(
					'quiz_views'	 => 0,
					'quiz_taken'	 => 0,
					'last_activity'	 => gmdate( 'Y-m-d H:i:s' )
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
		@setcookie( 'QSMAlertManager', wp_json_encode( $QSMAlertManager ), time() + 86400, COOKIEPATH, COOKIE_DOMAIN );
	}
}

add_action( 'admin_notices', 'qsm_quiz_list_header', 0 );
function qsm_quiz_list_header() {
	global $wpdb, $pagenow, $mlwQuizMasterNext;
	if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'qsm_quiz' == $_GET['post_type'] ) {
		?>
		<div class="wrap qsm-quizes-page">
			<h1>
				<?php esc_html_e( 'Quizzes & Surveys', 'quiz-master-next' ); ?>
				<a id="new_quiz_button" href="#" class="add-new-h2"><?php esc_html_e( 'Add New', 'quiz-master-next' ); ?></a>
			</h1>
		</div>
		<div class="clear"></div>
		<?php
		if ( isset( $_COOKIE['QSMAlertManager'] ) && ! empty($_COOKIE['QSMAlertManager'])) {
			$mlwQuizMasterNext->alertManager->alerts = json_decode(wp_unslash($_COOKIE['QSMAlertManager']), true);
			unset($_COOKIE['QSMAlertManager']);
		}
		$mlwQuizMasterNext->alertManager->showAlerts();
	}
}

/**
 * Generates the quizzes and surveys page
 *
 * @since 5.0
 */
function qsm_generate_quizzes_surveys_page() {
	// Only let admins and editors see this page.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	// Retrieve our globals.
	global $wpdb;
	global $mlwQuizMasterNext;

	// Pagination.
	$paged = filter_input( INPUT_GET, 'paged' ) ? absint( filter_input( INPUT_GET, 'paged' ) ) : 1;
	$limit = 10; // number of rows in page.

	$current_user  = get_current_user_id();
	if ( empty( $limit ) || $limit < 1 ) {
		// get the default value if none is set
		$limit = $screen->get_option( 'per_page', 'default' );
	}
	$offset = ( $paged - 1 ) * $limit;
	$where  = '';
	$search = '';
	if ( isset( $_REQUEST['s'] ) && '' !== $_REQUEST['s'] ) {
		$search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		$where  = " quiz_name LIKE '%$search%'";
	}

	// Multiple Delete quiz.
	if ( isset( $_POST['qsm_search_multiple_delete_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qsm_search_multiple_delete_nonce'] ) ), 'qsm_search_multiple_delete' ) ) {
		if ( ( isset( $_POST['qsm-ql-action-top'] ) && 'delete_pr' === sanitize_text_field( wp_unslash( $_POST['qsm-ql-action-top'] ) ) ) || ( isset( $_POST['qsm-ql-action-bottom'] ) && 'delete_pr' === sanitize_text_field( wp_unslash( $_POST['qsm-ql-action-bottom'] ) ) ) ) {
			if ( isset( $_POST['chk_remove_all'] ) ) {
				$c_all = array_map( 'sanitize_text_field', wp_unslash( $_POST['chk_remove_all'] ) );
				foreach ( $c_all as $quiz_id ) {
					$mlwQuizMasterNext->quizCreator->delete_quiz( intval( $quiz_id ), intval( $quiz_id ) );
				}
			}
		}
	}
	/*Set Request To Post as form method is Post.(AA)*/
	if ( isset( $_POST['btnSearchQuiz'] ) && isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
		$search       = htmlspecialchars( sanitize_text_field( wp_unslash( $_POST['s'] ) ), ENT_QUOTES );
		$condition    = " WHERE deleted=0 AND quiz_name LIKE '%$search%'";
		$qry          = stripslashes( $wpdb->prepare( "SELECT COUNT('quiz_id') FROM {$wpdb->prefix}mlw_quizzes%1s", $condition ) );
		$total        = $wpdb->get_var( $qry );
		$num_of_pages = ceil( $total / $limit );
	} else {
		$condition    = ' WHERE deleted=0';
		$condition    = apply_filters( 'quiz_query_condition_clause', $condition );
		$total        = $wpdb->get_var( stripslashes( $wpdb->prepare( "SELECT COUNT(`quiz_id`) FROM {$wpdb->prefix}mlw_quizzes %1s", $condition ) ) );
		$num_of_pages = ceil( $total / $limit );
	}

	// Next and previous page.
	$next_page = (int) $paged + 1;

	if ( $next_page > $num_of_pages ) {
		$next_page = $num_of_pages;
	}

	$prev_page = (int) $paged - 1;

	if ( $prev_page < 1 ) {
		$prev_page = 1;
	}

	// Check user role and fetch the quiz
	$user = wp_get_current_user();
	if ( in_array( 'author', (array) $user->roles, true ) ) {
		$post_arr['author__in'] = array( $user->ID );
	}
	if ( isset( $_GET['order'] ) && 'asc' === sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) {
		$post_arr['orderby'] = isset( $_GET['orderby'] ) && 'title' === sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ? 'title' : 'last_activity';
		$post_arr['order']   = 'ASC';
		// Load our quizzes.
		$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes( false, $post_arr['orderby'], 'ASC', (array) $user->roles, $user->ID, $limit, $offset, $where );
	} elseif ( isset( $_GET['order'] ) && 'desc' === sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) {
		$post_arr['orderby'] = isset( $_GET['orderby'] ) && 'title' === sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ? 'title' : 'last_activity';
		$post_arr['order']   = 'DESC';
		// Load our quizzes.
		$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes( false, $post_arr['orderby'], 'DESC', (array) $user->roles, $user->ID, $limit, $offset, $where );
	} else {
		// Load our quizzes.
		$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes( false, '', '', (array) $user->roles, $user->ID, $limit, $offset, $where );
	}
	/*Written to get results form search.(AA)*/
	if ( isset( $_POST['btnSearchQuiz'] ) && isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
		$search_quiz = htmlspecialchars( sanitize_text_field( wp_unslash( $_POST['s'] ) ), ENT_QUOTES );
		$condition   = " WHERE deleted=0 AND quiz_name LIKE '%$search_quiz%'";
		$condition   = apply_filters( 'quiz_query_condition_clause', $condition );
		$qry         = stripslashes( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes%1s", $condition ) );
		$quizzes     = $wpdb->get_results( $qry );

	}

	// Load quiz posts.
	$post_to_quiz_array = array();
	// Query for post
	$post_arr = array(
		'post_type'      => 'qsm_quiz',
		'paged'          => $paged,
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private' ),
	);
	$my_query = new WP_Query( $post_arr );
	if ( $my_query->have_posts() ) {
		while ( $my_query->have_posts() ) {
			$my_query->the_post();
			$post_to_quiz_array[ get_post_meta( get_the_ID(), 'quiz_id', true ) ] = array(
				'link'        => get_the_permalink( get_the_ID() ),
				'id'          => get_the_ID(),
				'post_status' => get_post_status( get_the_ID() ),
			);
		}
	}
	wp_reset_postdata();
	$quiz_json_array = array();
	foreach ( $quizzes as $quiz ) {
		if ( ! isset( $post_to_quiz_array[ $quiz->quiz_id ] ) ) {
			$current_user = wp_get_current_user();
			$quiz_post    = array(
				'post_title'   => $quiz->quiz_name,
				'post_content' => "[qsm quiz={$quiz->quiz_id}]",
				// 'post_status'  => 'publish',
				'post_author'  => $current_user->ID,
				'post_type'    => 'qsm_quiz',
			);
			$quiz_post_id = wp_insert_post( $quiz_post );
			add_post_meta( $quiz_post_id, 'quiz_id', $quiz->quiz_id );
			$post_to_quiz_array[ $quiz->quiz_id ] = array(
				'link'        => get_permalink( $quiz_post_id ),
				'id'          => $quiz_post_id,
				'post_status' => get_post_status( $quiz_post_id ),
			);
		}

		$quiz_results_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(result_id) FROM {$wpdb->prefix}mlw_results WHERE `deleted`= 0 AND `quiz_id`= %d", $quiz->quiz_id ) );

		$activity_date = gmdate( get_option( 'date_format' ), strtotime( $quiz->last_activity ) );
		$activity_time = gmdate( 'h:i:s A', strtotime( $quiz->last_activity ) );

		$quiz_json_array[] = array(
			'id'                   => $quiz->quiz_id,
			'name'                 => $quiz->quiz_name,
			'link'                 => $post_to_quiz_array[ $quiz->quiz_id ]['link'],
			'postID'               => $post_to_quiz_array[ $quiz->quiz_id ]['id'],
			'views'                => $quiz->quiz_views,
			'taken'                => $quiz_results_count,
			'lastActivity'         => $activity_date,
			'lastActivityDateTime' => $activity_date . ' ' . $activity_time,
			'post_status'          => $post_to_quiz_array[ $quiz->quiz_id ]['post_status'],
		);
	}
	$total_count = count( $quiz_json_array );
	wp_localize_script( 'qsm_admin_js', 'qsmQuizObject', $quiz_json_array );
	?>
	<div class="wrap qsm-quizes-page">
		<h1>
			<?php esc_html_e( 'Quizzes/Surveys', 'quiz-master-next' ); ?>
			<a id="new_quiz_button" href="#" class="add-new-h2"><?php esc_html_e( 'Add New', 'quiz-master-next' ); ?></a>
		</h1>
		<?php
		if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
			?>
			<div class="qsm-info-box">
				<p><?php esc_html_e( 'Your site is using PHP version', 'quiz-master-next' ); ?>
					<?php echo esc_html( PHP_VERSION ); ?>!
					<?php esc_html_e( 'Starting in QSM 6.0, your version of PHP will no longer be supported.', 'quiz-master-next' ); ?>
					<a href="https://quizandsurveymaster.com/increased-minimum-php-version-qsm-6-0/?utm_campaign=qsm_plugin&utm_medium=plugin&utm_source=minimum-php-notice" target="_blank"
						rel="noopener"><?php esc_html_e( "Click here to learn more about QSM's minimum PHP version change.", 'quiz-master-next' ); ?></a>
				</p>
			</div>
			<?php
		}
		?>
		<form method="POST" id="posts-filter">
			<?php wp_nonce_field( 'qsm_search_multiple_delete', 'qsm_search_multiple_delete_nonce' ); ?>
			<div class="qsm-quizzes-page-content">
				<div class="
				<?php
				if ( 'false' !== get_option( 'mlw_advert_shows' ) ) {
					echo 'qsm-quiz-page-wrapper-with-ads';
				} else {
					echo 'qsm-quiz-page-wrapper';
				}
				?>
					">
					<p class="search-box">
						<label class="screen-reader-text"
							for="quiz_search"><?php esc_html_e( 'Search', 'quiz-master-next' ); ?></label>
						<!-- Changed Request to Post -->
						<input type="search" id="quiz_search" name="s"
							value="<?php echo isset( $_POST['s'] ) && '' !== $_POST['s'] ? esc_attr( sanitize_text_field( wp_unslash( $_POST['s'] ) ) ) : ''; ?>">
						<input id="search-submit" class="button" type="submit" name="btnSearchQuiz" value="Search Quiz">
					</p>
					<div class="tablenav top">
						<div class="alignleft actions bulkactions">
							<select id="bulk-action-top" name="qsm-ql-action-top">
								<option selected="selected" value="none"><?php esc_html_e( 'Bulk Actions', 'quiz-master-next' ); ?>
								</option>
								<option value="delete_pr"><?php esc_html_e( 'Delete Permanently', 'quiz-master-next' ); ?></option>
							</select>
							<input id="bulk-submit" name="bulk-submit-top" class="button" type="button"
								value="<?php esc_attr_e( 'Apply', 'quiz-master-next' ); ?>">
						</div>
						<div class="tablenav-pages">
							<span
								class="displaying-num"><?php echo esc_html( number_format_i18n( $total ) . ' ' . sprintf( _n( 'item', 'items', $total, 'quiz-master-next' ), number_format_i18n( $total ) ) ); ?></span>
							<span class="pagination-links"
							<?php
							if ( (int) $num_of_pages <= 1 ) {
								echo 'style="display:none;"';
							}
							?>
							>
								<?php if ( '1' == $paged ) { ?>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
								<?php } else { ?>
								<a class="first-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=1&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the first page', 'quiz-master-next' ); ?>">&laquo;</a>
								<a class="prev-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=' . esc_attr( $prev_page ) . '&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the previous page', 'quiz-master-next' ); ?>">&lsaquo;</a>
								<?php } ?>
								<span class="paging-input">
									<span class="total-pages"><?php echo esc_html( $paged ); ?></span>
									<?php esc_html_e( 'of', 'quiz-master-next' ); ?>
									<span class="total-pages"><?php echo esc_html( $num_of_pages ); ?></span>
								</span>
								<?php if ( $paged === $num_of_pages ) { ?>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
								<?php } else { ?>
								<a class="next-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=' . esc_attr( $next_page ) . '&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the next page', 'quiz-master-next' ); ?>">&rsaquo;</a>
								<a class="last-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=' . esc_attr( $num_of_pages ) . '&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the last page', 'quiz-master-next' ); ?>">&raquo;</a>
								<?php } ?>
							</span>
						</div>
					</div>
					<table class="widefat">
						<?php
							$orderby_slug      = '&orderby=title&order=asc';
							$orderby_date_slug = '&orderby=date&order=asc';
							$orderby_class     = $orderby_date_class = 'sortable desc';
							// Title order
						if ( isset( $_GET['orderby'] ) && sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) === 'title' ) {
							if ( isset( $_GET['order'] ) && sanitize_text_field( wp_unslash( $_GET['order'] ) ) === 'asc' ) {
								$orderby_slug  = '&orderby=title&order=desc';
								$orderby_class = 'sorted asc';
							} elseif ( isset( $_GET['order'] ) && sanitize_text_field( wp_unslash( $_GET['order'] ) ) === 'desc' ) {
								$orderby_slug  = '&orderby=title&order=asc';
								$orderby_class = 'sorted desc';
							}
						} elseif ( isset( $_GET['orderby'] ) && sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) === 'date' ) {
							if ( isset( $_GET['order'] ) && sanitize_text_field( wp_unslash( $_GET['order'] ) ) === 'asc' ) {
								$orderby_date_slug  = '&orderby=date&order=desc';
								$orderby_date_class = 'sorted asc';
							} elseif ( isset( $_GET['order'] ) && sanitize_text_field( wp_unslash( $_GET['order'] ) ) === 'desc' ) {
								$orderby_date_slug  = '&orderby=date&order=asc';
								$orderby_date_class = 'sorted desc';
							}
						}
						?>
						<thead>
							<tr>
								<td class="manage-column column-cb check-column" id="cb"><input type="checkbox"
										name="delete-all-shortcodes-1" id="delete-all-shortcodes-1" value="0"></td>
								<th class="<?php echo esc_attr( $orderby_class ); ?>">
									<?php
										$paged_slug    = isset( $_GET['paged'] ) && '' !== $_GET['paged'] ? '&paged=' . sanitize_text_field( wp_unslash( $_GET['paged'] ) ) : '';
										$searched_slug = isset( $_GET['s'] ) && '' !== $_GET['s'] ? '&s=' . sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
										$sorting_url   = '?page=mlw_quiz_list' . $paged_slug . $searched_slug;
									?>
									<a href="<?php echo esc_url( $sorting_url . $orderby_slug ); ?>">
										<span><?php esc_html_e( 'Title', 'quiz-master-next' ); ?></span>
										<span class="sorting-indicator"></span>
									</a>
								</th>
								<th><?php esc_html_e( 'Shortcode', 'quiz-master-next' ); ?></th>
								<th><?php esc_html_e( 'Views', 'quiz-master-next' ); ?></th>
								<th><?php esc_html_e( 'Participants', 'quiz-master-next' ); ?></th>
								<th class="<?php echo esc_attr( $orderby_date_class ); ?>">
									<a href="<?php echo esc_url( $sorting_url . $orderby_date_slug ); ?>">
										<span><?php esc_html_e( 'Last Modified', 'quiz-master-next' ); ?></span>
										<span class="sorting-indicator"></span>
									</a>
								</th>
							</tr>
						</thead>
						<tbody id="the-list">
							<?php
							if ( $quiz_json_array ) {
								foreach ( $quiz_json_array as $key => $single_arr ) {
									?>
									<tr class="qsm-quiz-row" data-id="<?php echo esc_attr( $single_arr['id'] ); ?>">
										<th class="check-column">
											<input type="checkbox" class="chk_remove_all" name="chk_remove_all[]"
												id="chk_remove_all" value="<?php echo esc_attr( $single_arr['id'] ); ?>">
										</th>
										<td class="post-title column-title">
											<a class="row-title" href="admin.php?page=mlw_quiz_options&&quiz_id=<?php echo esc_attr( $single_arr['id'] ); ?>" aria-label="<?php echo esc_attr( $single_arr['name'] ); ?>"><?php echo esc_html( $single_arr['name'] ); ?> <strong style="color: #222; text-transform: capitalize;"><?php echo esc_html( 'publish' !== $single_arr['post_status'] ? '— ' . $single_arr['post_status'] : '' ); ?></strong>
											</a>
											<div class="row-actions">
												<a class="qsm-action-link" href="admin.php?page=mlw_quiz_options&quiz_id=<?php echo esc_attr( $single_arr['id'] ); ?>"><?php esc_html_e( 'Edit', 'quiz-master-next' ); ?></a> |
												<a class="qsm-action-link qsm-action-link-duplicate" href="#" data-id="<?php echo esc_attr( $single_arr['id'] ); ?>"><?php esc_html_e( 'Duplicate', 'quiz-master-next' ); ?></a> |
												<a class="qsm-action-link qsm-action-link-delete" href="#" data-id="<?php echo esc_attr( $single_arr['id'] ); ?>"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></a> |
												<a class="qsm-action-link" href="admin.php?page=mlw_quiz_results&quiz_id=<?php echo esc_attr( $single_arr['id'] ); ?>"><?php esc_html_e( 'View Results', 'quiz-master-next' ); ?></a> |
												<a class="qsm-action-link" target="_blank" rel="noopener" href="<?php echo esc_url( $single_arr['link'] ); ?>"><?php esc_html_e( 'Preview', 'quiz-master-next' ); ?></a>
											</div>
										</td>
										<td>
											<a href="#" class="qsm-list-shortcode-view">
												<span class="dashicons dashicons-welcome-view-site"></span>
											</a>
											<div class="sc-content sc-embed">[qsm quiz=<?php echo esc_attr( $single_arr['id'] ); ?>]</div>
											<div class="sc-content sc-link">[qsm_link id=<?php echo esc_attr( $single_arr['id'] ); ?>]<?php esc_html_e( 'Click here', 'quiz-master-next' ); ?>[/qsm_link]
											</div>
										</td>
										<td>
											<?php echo esc_html( $single_arr['views'] ); ?>
											<div class="row-actions">
												<a class="qsm-action-link qsm-action-link-reset" href="#" data-id="<?php echo esc_attr( $single_arr['id'] ); ?>"><?php esc_html_e( 'Reset', 'quiz-master-next' ); ?></a>
											</div>
										</td>
										<td class="comments column-comments" style="text-align: left;">
											<span class="post-com-count post-com-count-approved">
												<span class="comment-count-approved" aria-hidden="true"><?php echo esc_html( $single_arr['taken'] ); ?></span>
												<span class="screen-reader-text"><?php echo esc_html( $single_arr['taken'] . __( 'Participants', 'quiz-master-next' ) ); ?></span>
											</span>
										</td>
										<td>
											<abbr title="<?php echo esc_html( $single_arr['lastActivityDateTime'] ); ?>"><?php echo esc_html( $single_arr['lastActivity'] ); ?></abbr>
										</td>
									</tr>
									<?php
								}
							} else {
								?>
								<tr>
									<td colspan="6" style="text-align: center;">
										<?php esc_html_e( 'No Quiz found!', 'quiz-master-next' ); ?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td class="manage-column column-cb check-column" id="cb"><input type="checkbox"
										name="delete-all-shortcodes-2" id="delete-all-shortcodes-2" value="0"></td>
								<th class="<?php echo esc_attr( $orderby_class ); ?>" scope="col">
									<a href="<?php echo esc_url( $sorting_url . $orderby_slug ); ?>">
										<span><?php esc_html_e( 'Title', 'quiz-master-next' ); ?></span>
										<span class="sorting-indicator"></span>
									</a>
								</th>
								<th><?php esc_html_e( 'Shortcode', 'quiz-master-next' ); ?></th>
								<th><?php esc_html_e( 'Views', 'quiz-master-next' ); ?></th>
								<th><?php esc_html_e( 'Participants', 'quiz-master-next' ); ?></th>
								<th class="<?php echo esc_attr( $orderby_date_class ); ?>" scope="col">
									<a href="<?php echo esc_url( $sorting_url . $orderby_date_slug ); ?>">
										<span><?php esc_html_e( 'Last Modified', 'quiz-master-next' ); ?></span>
										<span class="sorting-indicator"></span>
									</a>
								</th>
							</tr>
						</tfoot>
					</table>
					<div class="tablenav bottom">
						<select id="bulk-action-bottom" name="qsm-ql-action-bottom">
							<option selected="selected" value="none"><?php esc_html_e( 'Bulk Actions', 'quiz-master-next' ); ?>
							</option>
							<option value="delete_pr"><?php esc_html_e( 'Delete Permanently', 'quiz-master-next' ); ?></option>
						</select>
						<input id="bulk-submit" name="bulk-submit-bottom" class="button" type="button"
								value="<?php esc_attr_e( 'Apply', 'quiz-master-next' ); ?>">
						<div class="tablenav-pages">
							<span
								class="displaying-num"><?php echo esc_html( number_format_i18n( $total ) . ' ' . sprintf( _n( 'item', 'items', $total, 'quiz-master-next' ), number_format_i18n( $total ) ) ); ?></span>
							<span class="pagination-links"
							<?php
							if ( (int) $num_of_pages <= 1 ) {
								echo 'style="display:none;"';
							}
							?>
							>
								<?php if ( '1' == $paged ) { ?>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
								<?php } else { ?>
								<a class="first-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=1&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the first page', 'quiz-master-next' ); ?>">&laquo;</a>
								<a class="prev-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=' . esc_attr( $prev_page ) . '&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the previous page', 'quiz-master-next' ); ?>">&lsaquo;</a>
								<?php } ?>
								<span class="paging-input">
									<span class="total-pages"><?php echo esc_html( $paged ); ?></span>
									<?php esc_html_e( 'of', 'quiz-master-next' ); ?>
									<span class="total-pages"><?php echo esc_html( $num_of_pages ); ?></span>
								</span>
								<?php if ( $paged === $num_of_pages ) { ?>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
								<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
								<?php } else { ?>
								<a class="next-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=' . esc_attr( $next_page ) . '&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the next page', 'quiz-master-next' ); ?>">&rsaquo;</a>
								<a class="last-page button"
									href="<?php echo '?page=mlw_quiz_list&paged=' . esc_attr( $num_of_pages ) . '&s=' . esc_attr( $search ); ?>"
									title="<?php esc_attr_e( 'Go to the last page', 'quiz-master-next' ); ?>">&raquo;</a>
								<?php } ?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	add_action( 'admin_footer', 'qsm_generate_quizzes_surveys_page_template' );
}

add_action( 'admin_footer', 'qsm_admin_footer_text' );
function qsm_admin_footer_text() {
	global $mlwQuizMasterNext;
	if ( (isset( $_REQUEST['post_type'] ) && 'qsm_quiz' == $_REQUEST['post_type']) || (isset( $_REQUEST['page'] ) && 'mlw_quiz_list' == $_REQUEST['page']) ) {
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
		<div class="qsm-popup qsm-popup-slide" id="modal-4" aria-hidden="true">
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
					<footer class="qsm-popup__footer">
						<button id="duplicate-quiz-button"
							class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Duplicate', 'quiz-master-next' ); ?></button>
						<button class="qsm-popup__btn" data-micromodal-close
							aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
					</footer>
				</div>
			</div>
		</div>
		<!-- Popup for delete quiz -->
		<div class="qsm-popup qsm-popup-slide" id="modal-5" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-5-title"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-5-content">
						<form action='' method='post' id="delete-quiz-form" style="display:flex; flex-direction:column;">
							<h3><b><?php esc_html_e( 'Are you sure you want to delete this quiz or survey?', 'quiz-master-next' ); ?></b>
							</h3>
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
					<footer class="qsm-popup__footer">
						<button id="delete-quiz-button"
							class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></button>
						<button class="qsm-popup__btn" data-micromodal-close
							aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
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
							<h3><b><?php esc_html_e( 'Are you sure you want to delete selected quiz or survey?', 'quiz-master-next' ); ?></b>
							</h3>
							<label>
								<input type="checkbox" name="qsm_delete_question_from_qb" checked="checked" />
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
		<div class="qsm-popup qsm-popup-slide" id="modal-export-import" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-5-title"><?php esc_html_e( 'Extend QSM', 'quiz-master-next' ); ?>
						</h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-5-content">
						<h3><b><?php esc_html_e( 'Export functionality is provided as Premium addon.', 'quiz-master-next' ); ?></b>
						</h3>
					</main>
					<footer class="qsm-popup__footer">
						<a style="color: white;    text-decoration: none;"
							href="https://quizandsurveymaster.com/downloads/export-import/" target="_blank"
							class="qsm-popup__btn qsm-popup__btn-primary"><?php esc_html_e( 'Buy Now', 'quiz-master-next' ); ?></a>
						<button class="qsm-popup__btn" data-micromodal-close
							aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
					</footer>
				</div>
			</div>
		</div>
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
					<a class="button button-secondary button-hero" href="https://quizandsurveymaster.com/docs/" target="_blank"><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Read Documentation', 'quiz-master-next' ); ?></a>
				</div>
				<h3><?php esc_html_e( 'or watch the below video to get started', 'quiz-master-next' ); ?></h3>
				<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/coE5W_WB-48" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</script>
		<?php
	}
}


add_action('manage_posts_extra_tablenav', 'qsm_manage_posts_extra_tablenav', 99, 1);
function qsm_manage_posts_extra_tablenav($which) {
	if ( 'top' === $which ) {
		if ( class_exists( 'QSM_Export_Import' ) ) {
			?><a class="button button-primary" href="<?php echo esc_url( admin_url() . 'admin.php?page=qmn_addons&tab=export-and-import' ); ?>" style="position: relative;top: 0px;" target="_blank" rel="noopener"><?php esc_html_e( 'Import & Export', 'quiz-master-next' ); ?></a><?php 
		} else {
			?><a id="show_import_export_popup" href="#" style="position: relative;top: 0px;" class="add-new-h2 button-primary"><?php esc_html_e( 'Import & Export', 'quiz-master-next' ); ?></a><?php 
		}
	}
}