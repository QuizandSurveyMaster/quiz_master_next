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
	<h2><?php esc_html_e('Tools', 'quiz-master-next'); ?></h2>

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
			$wpdb->prefix.'mlw_quizzes',
			array(
				'deleted' => 0,
			),
			array(
				'quiz_id' => sanitize_text_field( intval( $_POST['restore_quiz'] ) ),
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
			$my_query = new WP_Query( array(
				'post_type'  => 'qsm_quiz',
				'meta_key'   => 'quiz_id',
				'meta_value' => sanitize_text_field( intval( $_POST['restore_quiz'] ) ),
			));
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
				<option value="<?php echo esc_attr( $quiz->quiz_id ); ?>"><?php echo esc_html( $quiz->quiz_name ); ?></option>
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
	$audit_trails = $wpdb->get_results( $wpdb->prepare( "SELECT trail_id, action_user, action, time
		FROM {$wpdb->prefix}mlw_qm_audit_trail ORDER BY trail_id DESC LIMIT %d, %d", $begin, $table_limit ) );
	?>
        <p><?php esc_html_e('Total actions since QSM installed:', 'quiz-master-next'); ?> <?php echo esc_html( $audit_total ); ?></p>
	<?php

	// Determine which navigation to show.
	if ( $page > 0 ) {
		$previous = $page - 2;
		?>
		<a class="button" id="prev_page" href="?page=qsm_quiz_tools&&audit_page=<?php esc_attr( $previous ); ?>">
			<?php echo sprintf( esc_html__( 'Previous %s Audits', 'quiz-master-next' ), $table_limit ); ?>
		</a>
		<?php
		if ( $left > $table_limit ) {
			?>
			<a class="button" id="next_page" href="?page=qsm_quiz_tools&&audit_page=<?php esc_attr( $page ); ?>">
				<?php echo sprintf( esc_html__( 'Next %s Audits', 'quiz-master-next' ), $table_limit ); ?>
			</a>
			<?php
		}
	} elseif ( $page == 0 ) {
		if ( $left > $table_limit ) {
			?>
			<a class="button" id="next_page" href="?page=qsm_quiz_tools&&audit_page=<?php esc_attr( $page ); ?>">
				<?php echo sprintf( esc_html__( 'Next %s Audits', 'quiz-master-next' ), $table_limit ); ?>
			</a>
			<?php
		}
	} elseif ( $left < $table_limit ) {
		$previous = $page - 2;
		?>
		<a class="button" id="prev_page" href="?page=qsm_quiz_tools&&audit_page=<?php esc_attr( $previous ); ?>">
			<?php echo sprintf( esc_html__( 'Previous %s Audits', 'quiz-master-next' ), $table_limit ); ?>
		</a>
		<?php
	}
	?>
	<table class=widefat>
		<thead>
			<tr>
				<th>ID</th>
				<th><?php esc_html_e( 'User', 'quiz-master-next' ); ?></th>
				<th><?php esc_html_e( 'Action', 'quiz-master-next' ); ?></th>
				<th><?php esc_html_e( 'Time', 'quiz-master-next' ); ?></th>
			</tr>
		</thead>
		<tbody id="the-list">
			<?php
			$alternate = '';
			foreach ( $audit_trails as $audit ) {
				if ( $alternate ) {
					$alternate = '';
				} else {
					$alternate = ' class="alternate"';
				}
				echo "<tr{$alternate}>";
				echo "<td>{$audit->trail_id}</td>";
				echo "<td>{$audit->action_user}</td>";
				echo "<td>{$audit->action}</td>";
				echo "<td>{$audit->time}</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<?php
}
?>
