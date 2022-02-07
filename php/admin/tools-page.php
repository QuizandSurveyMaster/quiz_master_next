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
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}
	add_meta_box( 'qsm_restore_box', 'Restore Quiz', 'qsm_restore_function', 'quiz_wpss' );
	add_meta_box( 'qsm_audit_box', 'Audit Trail', 'qsm_audit_box', 'quiz_wpss' );
	?>
		<style type="text/css">
			#qsm_restore_box .hndle,
			#qsm_audit_box .hndle{
				padding-left: 15px;
				padding-bottom: 0;
			}
			.qsm-tools-page .handle-order-higher,
			.qsm-tools-page .handle-order-lower,
			.qsm-tools-page .handle-actions{
				display: none;
			}
		</style>
	<div class="wrap qsm-tools-page">
	<h2><?php esc_html_e( 'Tools', 'quiz-master-next' ); ?></h2>

	<div style="float:left; width:100%;" class="inner-sidebar1">
		<?php do_meta_boxes( 'quiz_wpss', 'advanced', null ); ?>
	</div>

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
	if ( isset( $_POST['restore_quiz'] ) ) {
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
					'post_type'  => 'qsm_quiz',
					'meta_key'   => 'quiz_id',
					'meta_value' => intval( $_POST['restore_quiz'] ),
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
			<span style="color:red;"><?php esc_html_e( 'Quiz Has Been Restored!', 'quiz-master-next' ); ?></span>
			<?php
		}
	}
	$quizzes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 1" );
	?>
	<h3><?php esc_html_e( 'Choose a quiz in the drop down and then click the button to restore a deleted quiz.', 'quiz-master-next' ); ?></h3>
	<form action='' method="post">
		<select name="restore_quiz">
			<?php
			foreach ( $quizzes as $quiz ) {
				?>
				<option value="<?php echo esc_attr( $quiz->quiz_id ); ?>"><?php echo wp_kses_post( $quiz->quiz_name ); ?></option>
				<?php
			}
			?>
		</select>
		<button class="button"><?php esc_html_e( 'Restore Quiz', 'quiz-master-next' ); ?></button>
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
	$table_limit = 30;
	$audit_total = $wpdb->get_var( "SELECT COUNT(trail_id) FROM {$wpdb->prefix}mlw_qm_audit_trail" );

	// If user has gone to the next audit page, load current page and beginning.
	// Else, start at 0.
	if ( isset( $_GET['audit_page'] ) ) {
		$page  = intval( $_GET['audit_page'] ) + 1;
		$begin = $table_limit * $begin;
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
								<a href="#" class="qsm_audit_data" data-auditid="<?php echo esc_html( $audit->form_data ); ?>"><?php echo esc_html( $audit->action ); ?></a>
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
						<td colspan="5">No data found!!</td>
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
?>
