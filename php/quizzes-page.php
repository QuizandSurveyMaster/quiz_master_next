<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generates the quizzes and surveys page
 *
 * @since 5.0
 */
function qsm_generate_quizzes_surveys_page() {

	// Only let admins and editors see this page
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}

	// Retrieve our globals
	global $wpdb;
	global $mlwQuizMasterNext;

	// Enqueue our styles and scripts
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../css/qsm-admin.css' , __FILE__ ) );
	wp_enqueue_script( 'qsm_admin_script', plugins_url( '../js/qsm-admin.js' , __FILE__ ), array( 'jquery-ui-core' ) );
	wp_enqueue_style( 'qsm_jquery_redmond_theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css' );

	// Create new quiz
	if ( isset( $_POST['qsm_new_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_new_quiz_nonce'], 'qsm_new_quiz' ) ) {
		$quiz_name = htmlspecialchars( stripslashes( $_POST["quiz_name"] ), ENT_QUOTES );
		$mlwQuizMasterNext->quizCreator->create_quiz( $quiz_name );
	}

	// Delete quiz
	if ( isset( $_POST['qsm_delete_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_delete_quiz_nonce'], 'qsm_delete_quiz' ) ) {
		$quiz_id = intval( $_POST["delete_quiz_id"] );
		$quiz_name = sanitize_text_field( $_POST["delete_quiz_name"] );
		$mlwQuizMasterNext->quizCreator->delete_quiz( $quiz_id, $quiz_name );
	}

	// Edit Quiz Name
	if ( isset( $_POST['qsm_edit_name_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_edit_name_quiz_nonce'], 'qsm_edit_name_quiz' ) ) {
		$quiz_id = intval( $_POST["edit_quiz_id"] );
		$quiz_name = htmlspecialchars( stripslashes( $_POST["edit_quiz_name"] ), ENT_QUOTES );
		$mlwQuizMasterNext->quizCreator->edit_quiz_name( $quiz_id, $quiz_name );
	}

	// Duplicate Quiz
	if ( isset( $_POST['qsm_duplicate_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_duplicate_quiz_nonce'], 'qsm_duplicate_quiz' ) ) {
		$quiz_id = intval( $_POST["duplicate_quiz_id"] );
		$quiz_name = htmlspecialchars( $_POST["duplicate_new_quiz_name"], ENT_QUOTES );
		$mlwQuizMasterNext->quizCreator->duplicate_quiz( $quiz_id, $quiz_name, isset( $_POST["duplicate_questions"] ) );
	}

	// Load our quizzes
	$quizzes = $wpdb->get_results( "SELECT quiz_id, quiz_name, quiz_views, quiz_taken, last_activity FROM {$wpdb->prefix}mlw_quizzes WHERE deleted='0' ORDER BY quiz_id DESC" );

	// Load quiz posts
	$post_to_quiz_array = array();
	$my_query = new WP_Query( array( 'post_type' => 'quiz' ) );
	if ( $my_query->have_posts() ) {
	  while ( $my_query->have_posts() ) {
	    $my_query->the_post();
			$post_to_quiz_array[ get_post_meta( get_the_ID(), 'quiz_id', true ) ] = array(
				'link' => get_permalink(),
				'id' => get_the_ID()
			);
	  }
	}
	wp_reset_postdata();

	$quiz_json_array = array();
	foreach ( $quizzes as $quiz ) {
		if ( ! isset( $post_to_quiz_array[ $quiz->quiz_id ] ) ) {
			$current_user = wp_get_current_user();
			$quiz_post = array(
				'post_title'    => $quiz->quiz_name,
				'post_content'  => "[qsm quiz={$quiz->quiz_id}]",
				'post_status'   => 'publish',
				'post_author'   => $current_user->ID,
				'post_type' => 'quiz'
			);
			$quiz_post_id = wp_insert_post( $quiz_post );
			add_post_meta( $quiz_post_id, 'quiz_id', $quiz->quiz_id );
			$post_to_quiz_array[ $quiz->quiz_id ] = array(
				'link' => get_permalink( $quiz_post_id ),
				'id' => $quiz_post_id
			);
		}

		$quiz_json_array[] = array(
			'id' => $quiz->quiz_id,
			'name' => esc_js( $quiz->quiz_name ),
			'link' => $post_to_quiz_array[ $quiz->quiz_id ]['link'],
			'postID' => $post_to_quiz_array[ $quiz->quiz_id ]['id'],
			'views' => $quiz->quiz_views,
			'taken' => $quiz->quiz_taken,
			'lastActivity' => $quiz->last_activity
		);
	}
	$total_count = count( $quiz_json_array );
	wp_localize_script( 'qsm_admin_script', 'qsmQuizObject', $quiz_json_array );
	?>
	<div class="wrap qsm-quizes-page">
		<h1><?php _e( 'Quizzes/Surveys', 'quiz-master-next' ); ?><a id="new_quiz_button" href="#" class="add-new-h2"><?php _e( 'Add New', 'quiz-master-next' ); ?></a></h1>
		<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
		<div class="qsm-quizzes-page-content">
			<div class="<?php if ( 'false' != get_option( 'mlw_advert_shows' ) ) { echo 'qsm-quiz-page-wrapper-with-ads'; } else { echo 'qsm-quiz-page-wrapper'; } ?>">
				<p class="search-box">
					<label class="screen-reader-text" for="quiz_search">Search:</label>
					<input type="search" id="quiz_search" name="quiz_search" value="">
					<a href="#" class="button">Search</a>
				</p>
				<div class="tablenav top">
					<div class="tablenav-pages">
						<span class="displaying-num"><?php echo sprintf( _n( 'One quiz or survey', '%s quizzes or surveys', $total_count, 'quiz-master-next' ), number_format_i18n( $total_count ) ); ?></span>
						<br class="clear">
					</div>
				</div>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php _e( 'Name', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'URL', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Shortcode', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Leaderboard Shortcode', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Views', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Taken', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Last Modified', 'quiz-master-next' ); ?></th>
						</tr>
					</thead>
					<tbody id="the-list">

					</tbody>
					<tfoot>
						<tr>
							<th><?php _e( 'Name', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'URL', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Shortcode', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Leaderboard Shortcode', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Views', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Taken', 'quiz-master-next' ); ?></th>
							<th><?php _e( 'Last Modified', 'quiz-master-next' ); ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
			<?php
			if ( 'true' == get_option( 'mlw_advert_shows' ) ) {
				?>
				<div class="qsm-news-ads">
					<h3 class="qsm-news-ads-title">QSM News</h3>
					<div class="qsm-news-ads-widget">
						<h3>Subscribe to our newsletter!</h3>
						<p>Join our mailing list and receive a discount on your next purchase! Learn about our newest features, receive email-only promotions, receive tips and guides, and more!</p>
						<a target="_blank" href="http://quizandsurveymaster.com/subscribe-to-our-newsletter/?utm_source=qsm-quizzes-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=subscribe-to-newsletter" class="button-primary">Subscribe Now</a>
					</div>
					<?php
					$qmn_rss = array();
					$qmn_feed = fetch_feed( 'http://quizandsurveymaster.com/feed' );
					if ( ! is_wp_error( $qmn_feed ) ) {
						$qmn_feed_items = $qmn_feed->get_items( 0, 5 );
						foreach ( $qmn_feed_items as $feed_item ) {
						    $qmn_rss[] = array(
						        'link' => $feed_item->get_link(),
						        'title' => $feed_item->get_title(),
						        'description' => $feed_item->get_description(),
										'date' => $feed_item->get_date( 'F j Y' ),
										'author' => $feed_item->get_author()->get_name()
						    );
						}
					}
					foreach( $qmn_rss as $item ) {
						?>
						<div class="qsm-news-ads-widget">
							<h3><?php echo $item['title']; ?></h3>
							<p>By <?php echo $item['author']; ?></p>
							<div>
								<?php echo $item['description']; ?>
							</div>
							<a target='_blank' href="<?php echo $item['link']; ?>" class="button-primary">Read More</a>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>
		</div>

		<!--New Quiz Dialog-->
		<div id="new_quiz_dialog" title="Create New Quiz Or Survey" style="display:none;">
			<form action="" method="post" class="qsm-dialog-form">
				<?php wp_nonce_field( 'qsm_new_quiz','qsm_new_quiz_nonce' ); ?>
				<h3><?php _e( 'Create New Quiz Or Survey', 'quiz-master-next' ); ?></h3>
				<label><?php _e( 'Name', 'quiz-master-next' ); ?></label><input type="text" name="quiz_name" value="" />
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e( 'Create', 'quiz-master-next' ); ?>' /></p>
			</form>
		</div>

		<!--Edit Quiz Name Dialog-->
		<div id="edit_dialog" title="Edit Name" style="display:none;">
			<form action='' method='post' class="qsm-dialog-form">
				<label><?php _e( 'Name', 'quiz-master-next' ); ?></label>
				<input type="text" id="edit_quiz_name" name="edit_quiz_name" />
				<input type="hidden" id="edit_quiz_id" name="edit_quiz_id" />
				<?php wp_nonce_field( 'qsm_edit_name_quiz','qsm_edit_name_quiz_nonce' ); ?>
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e( 'Edit', 'quiz-master-next' ); ?>' /></p>
			</form>
		</div>

		<!--Duplicate Quiz Dialog-->
		<div id="duplicate_dialog" title="Duplicate Quiz Or Survey" style="display:none;">
			<form action='' method='post' class="qsm-dialog-form">
				<label for="duplicate_questions"><?php _e( 'Duplicate questions also?', 'quiz-master-next' ); ?></label><input type="checkbox" name="duplicate_questions" id="duplicate_questions"/><br />
				<br />
				<label for="duplicate_new_quiz_name"><?php _e( 'Name Of New Quiz Or Survey:', 'quiz-master-next' ); ?></label><input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" />
				<input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
				<?php wp_nonce_field( 'qsm_duplicate_quiz','qsm_duplicate_quiz_nonce' ); ?>
				<p class='submit'><input type='submit' class='button-primary' value='<?php _e( 'Duplicate', 'quiz-master-next' ); ?>' /></p>
			</form>
		</div>

		<!--Delete Quiz Dialog-->
		<div id="delete_dialog" title="Delete Quiz Or Survey?" style="display:none;">
		<form action='' method='post' class="qsm-dialog-form">
			<h3><b><?php _e( 'Are you sure you want to delete this quiz or survey?', 'quiz-master-next' ); ?></b></h3>
			<?php wp_nonce_field( 'qsm_delete_quiz','qsm_delete_quiz_nonce' ); ?>
			<input type='hidden' id='delete_quiz_id' name='delete_quiz_id' value='' />
			<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />
			<p class='submit'><input type='submit' class='button-primary' value='<?php _e( 'Delete', 'quiz-master-next' ); ?>' /></p>
		</form>
		</div>
	</div>
<?php
}
?>
