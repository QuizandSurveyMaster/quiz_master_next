<?php
/**
 * This file handles the contents on the "Quizzes/Surveys" page.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates the quizzes and surveys page
 *
 * @since 5.0
 */
function qsm_generate_quizzes_surveys_page() {

	// Only let admins and editors see this page.
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}

	// Retrieve our globals.
	global $wpdb;
	global $mlwQuizMasterNext;

	// Enqueue our styles and scripts.
	wp_enqueue_script( 'micromodal_script', plugins_url( '../../js/micromodal.min.js', __FILE__ ) );
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
	wp_enqueue_script( 'qsm_admin_script', plugins_url( '../../js/qsm-admin.js', __FILE__ ), array( 'wp-util', 'underscore', 'jquery', 'micromodal_script' ), $mlwQuizMasterNext->version );

	// Create new quiz.
	if ( isset( $_POST['qsm_new_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_new_quiz_nonce'], 'qsm_new_quiz' ) ) {
		$quiz_name = sanitize_textarea_field( htmlspecialchars( stripslashes( $_POST['quiz_name'] ), ENT_QUOTES ) );
		$mlwQuizMasterNext->quizCreator->create_quiz( $quiz_name );
	}

	// Delete quiz.
	if ( isset( $_POST['qsm_delete_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_delete_quiz_nonce'], 'qsm_delete_quiz' ) ) {
		$quiz_id   = intval( $_POST['delete_quiz_id'] );
		$quiz_name = sanitize_text_field( $_POST['delete_quiz_name'] );
		$mlwQuizMasterNext->quizCreator->delete_quiz( $quiz_id, $quiz_name );
	}

	// Duplicate Quiz.
	if ( isset( $_POST['qsm_duplicate_quiz_nonce'] ) && wp_verify_nonce( $_POST['qsm_duplicate_quiz_nonce'], 'qsm_duplicate_quiz' ) ) {
		$quiz_id   = intval( $_POST['duplicate_quiz_id'] );
		$quiz_name = sanitize_text_field( htmlspecialchars( $_POST['duplicate_new_quiz_name'], ENT_QUOTES ) );
		$mlwQuizMasterNext->quizCreator->duplicate_quiz( $quiz_id, $quiz_name, isset( $_POST['duplicate_questions'] ) );
	}

	// Resets stats for a quiz.
	if ( isset( $_POST['qsm_reset_stats_nonce'] ) && wp_verify_nonce( $_POST['qsm_reset_stats_nonce'] , 'qsm_reset_stats' ) ) {
		$quiz_id = intval( $_POST['reset_quiz_id'] );
		$results = $wpdb->update(
			$wpdb->prefix . 'mlw_quizzes',
			array(
				'quiz_views'    => 0,
				'quiz_taken'    => 0,
				'last_activity' => date( 'Y-m-d H:i:s' ),
			),
			array( 'quiz_id' => $quiz_id ),
			array(
				'%d',
				'%d',
				'%s',
			),
			array( '%d' )
		);
		if ( false !== $results ) {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'The stats has been reset successfully.', 'quiz-master-next' ), 'success' );
			$mlwQuizMasterNext->audit_manager->new_audit( "Quiz Stats Have Been Reset For Quiz Number $quiz_id" );
		} else {
			$mlwQuizMasterNext->alertManager->newAlert( __( 'Error trying to reset stats. Please try again.', 'quiz-master-next' ), 'error' );
			$mlwQuizMasterNext->log_manager->add( 'Error resetting stats', $wpdb->last_error . ' from ' . $wpdb->last_query, 0, 'error' );
		}
	}

	// Load our quizzes.
	$quizzes = $mlwQuizMasterNext->pluginHelper->get_quizzes();

	// Load quiz posts.
	$post_to_quiz_array = array();
	$my_query           = new WP_Query( array(
		'post_type'      => 'quiz',
		'posts_per_page' => -1,
                'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private')
	));
	if ( $my_query->have_posts() ) {
		while ( $my_query->have_posts() ) {
			$my_query->the_post();
			$post_to_quiz_array[ get_post_meta( get_the_ID(), 'quiz_id', true ) ] = array(
				'link' => get_the_permalink(get_the_ID()),
				'id'   => get_the_ID(),
				'post_status'   => get_post_status(get_the_ID()),
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
				//'post_status'  => 'publish',
				'post_author'  => $current_user->ID,
				'post_type'    => 'quiz',
			);
			$quiz_post_id = wp_insert_post( $quiz_post );
			add_post_meta( $quiz_post_id, 'quiz_id', $quiz->quiz_id );
			$post_to_quiz_array[ $quiz->quiz_id ] = array(
				'link' => get_permalink( $quiz_post_id ),
				'id'   => $quiz_post_id,
                                'post_status'   => get_post_status($quiz_post_id),
			);
		}

		$activity_date     = date_i18n( get_option( 'date_format' ), strtotime( $quiz->last_activity ) );
		$activity_time     = date( 'h:i:s A', strtotime( $quiz->last_activity ) );
		$quiz_json_array[] = array(
			'id'           => $quiz->quiz_id,
			'name'         => esc_html( $quiz->quiz_name ),
			'link'         => $post_to_quiz_array[ $quiz->quiz_id ]['link'],
			'postID'       => $post_to_quiz_array[ $quiz->quiz_id ]['id'],
			'views'        => $quiz->quiz_views,
			'taken'        => $quiz->quiz_taken,
			'lastActivity' => $activity_date,
			'lastActivityDateTime' => $activity_date . ' ' .$activity_time,
                        'post_status' => $post_to_quiz_array[ $quiz->quiz_id ]['post_status'],
		);
	}
	$total_count = count( $quiz_json_array );        
	wp_localize_script( 'qsm_admin_script', 'qsmQuizObject', $quiz_json_array );
	?>
	<div class="wrap qsm-quizes-page">
		<h1>
                    <?php esc_html_e( 'Quizzes/Surveys', 'quiz-master-next' ); ?>
                    <a id="new_quiz_button" href="#" class="add-new-h2"><?php _e( 'Add New', 'quiz-master-next' ); ?></a>
                </h1>
		<?php $mlwQuizMasterNext->alertManager->showAlerts(); ?>
		<?php
		if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
			?>
			<div class="qsm-info-box">
				<p>Your site is using PHP version <?php echo esc_html( PHP_VERSION ); ?>! Starting in QSM 6.0, your version of PHP will no longer be supported. <a href="http://bit.ly/2lyrrm8" target="_blank">Click here to learn more about QSM's minimum PHP version change.</a></p>
			</div>
			<?php
		}
		?>
		<div class="qsm-quizzes-page-content">
			<div class="<?php if ( 'false' != get_option( 'mlw_advert_shows' ) ) { echo 'qsm-quiz-page-wrapper-with-ads'; } else { echo 'qsm-quiz-page-wrapper'; } ?>">
				<p class="search-box">
					<label class="screen-reader-text" for="quiz_search"><?php esc_html_e( 'Search', 'quiz-master-next' ); ?></label>
					<input type="search" id="quiz_search" name="quiz_search" value="">
					<a href="#" class="button"><?php esc_html_e( 'Search', 'quiz-master-next' ); ?></a>
                                        <?php if(class_exists('QSM_Export_Import')){ ?>
                                            <a class="button button-primary" href="<?php echo admin_url() . 'admin.php?page=qmn_addons&tab=export-and-import'; ?>" target="_blank"><?php _e( 'Import & Export', 'quiz-master-next' ); ?></a>
                                        <?php } else{ ?>
                                            <a id="show_import_export_popup" href="#" style="position: relative;top: 0px;" class="add-new-h2 button-primary"><?php _e( 'Import & Export', 'quiz-master-next' ); ?></a>
                                        <?php } ?>
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
							<th><?php esc_html_e( 'Name', 'quiz-master-next' ); ?></th>
							<th><?php esc_html_e( 'Shortcode', 'quiz-master-next' ); ?></th>
							<th><?php esc_html_e( 'Views/Taken', 'quiz-master-next' ); ?></th>
							<th><?php esc_html_e( 'Last Modified', 'quiz-master-next' ); ?></th>
						</tr>
					</thead>
					<tbody id="the-list">

					</tbody>
					<tfoot>
						<tr>
							<th><?php esc_html_e( 'Name', 'quiz-master-next' ); ?></th>
							<th><?php esc_html_e( 'Shortcode', 'quiz-master-next' ); ?></th>
							<th><?php esc_html_e( 'Views/Taken', 'quiz-master-next' ); ?></th>
							<th><?php esc_html_e( 'Last Modified', 'quiz-master-next' ); ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
			<?php
			if ( 'true' == get_option( 'mlw_advert_shows' ) ) {
				?>
				<div class="qsm-news-ads">
					<h3 class="qsm-news-ads-title"><?php esc_html_e( 'QSM News', 'quiz-master-next' ); ?></h3>
<!--					<div class="qsm-info-widget">
						<h3>Keep your WordPress site healthy and secure!</h3>
						<p>Easily monitor your WordPress sites to ensure they stay up, healthy, and secure with our new WordPress plugin, WP Health!</p>
						<a target="_blank" href="http://bit.ly/2Mr2SqC" class="button-primary"><?php esc_html_e( 'Learn More', 'quiz-master-next' ); ?></a>
					</div>-->
                                        <?php
                                        $xml = qsm_fetch_data_from_xml();
                                        if(isset($xml->qsm_news)){
                                        ?>
					<div class="qsm-info-widget">
                                            <h3><?php echo $xml->qsm_news->title; ?></h3>
                                            <p><?php echo $xml->qsm_news->desc; ?></p>
                                            <a target="_blank" href="<?php echo $xml->qsm_news->link; ?>?utm_source=qsm-quizzes-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=subscribe-to-newsletter" class="button-primary"><?php _e( 'Subscribe Now', 'quiz-master-next' ); ?></a>
					</div>
					<?php
                                        }
					$qmn_rss  = array();
                                        if( false === get_transient('qsm_sidebar_feed_data') ){
                                            $qmn_feed = fetch_feed( 'https://quizandsurveymaster.com/feed' );                                        
                                            if ( ! is_wp_error( $qmn_feed ) ) {
                                                    $qmn_feed_items = $qmn_feed->get_items( 0, 5 );
                                                    foreach ( $qmn_feed_items as $feed_item ) {
                                                            $qmn_rss[] = array(
                                                                    'link'        => $feed_item->get_link(),
                                                                    'title'       => $feed_item->get_title(),
                                                                    'description' => $feed_item->get_description(),
                                                                    'date'        => $feed_item->get_date( 'F j Y' ),
                                                                    'author'      => $feed_item->get_author()->get_name(),
                                                            );
                                                    }
                                            }
                                            set_transient( 'qsm_sidebar_feed_data', $qmn_rss, 60*60*24 );
                                        }else{
                                            $qmn_rss = get_transient('qsm_sidebar_feed_data');
                                        }                                        
					foreach ( $qmn_rss as $item ) {
						?>
						<div class="qsm-info-widget">
							<h3><?php echo esc_html( $item['title'] ); ?></h3>
							<p>By <?php echo esc_html( $item['author'] ); ?></p>
							<div>
								<?php echo esc_html( $item['description'] ); ?>
							</div>
							<a target='_blank' href="<?php echo esc_attr( $item['link'] ); ?>?utm_source=qsm-quizzes-page&utm_medium=plugin&utm_campaign=qsm_plugin" class="button-primary"><?php _e( 'Read More', 'quiz-master-next' ); ?></a>
						</div>
						<?php
					}
					?>
                                        <div class="remove-ads-adv-link">
                                            <a target="_blank" href="https://quizandsurveymaster.com/downloads/advertisement-gone/"><span class="dashicons dashicons-no-alt"></span> Remove Ads</a>
                                        </div>
				</div>
				<?php
			}
			?>
		</div>

		<!-- Popup for resetting stats -->
		<div class="qsm-popup qsm-popup-slide" id="modal-1" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-1-title">Reset stats for this quiz?</h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-1-content">
						<p><?php _e('Are you sure you want to reset the stats to 0? All views and taken stats for this quiz will be reset. This is permanent and cannot be undone.', 'quiz-master-next'); ?></p>
						<form action="" method="post" id="reset_quiz_form">
							<?php wp_nonce_field( 'qsm_reset_stats', 'qsm_reset_stats_nonce' ); ?>
							<input type="hidden" id="reset_quiz_id" name="reset_quiz_id" value="0" />
						</form>
					</main>
					<footer class="qsm-popup__footer">
						<button id="reset-stats-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Reset All Stats For Quiz', 'quiz-master-next'); ?></button>
						<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
					</footer>
				</div>
			</div>
		</div>

		<!-- Popup for new quiz -->
		<div class="qsm-popup qsm-popup-slide" id="modal-2" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-2-title"><?php _e( 'Create New Quiz Or Survey', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-2-content">
						<form action="" method="post" id="new-quiz-form">
							<?php wp_nonce_field( 'qsm_new_quiz', 'qsm_new_quiz_nonce' ); ?>
							<label><?php _e( 'Name', 'quiz-master-next' ); ?></label>
							<input type="text" name="quiz_name" value="" />
						</form>
					</main>
					<footer class="qsm-popup__footer">
						<button id="create-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Create', 'quiz-master-next'); ?></button>
						<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
					</footer>
				</div>
			</div>
		</div>

		<!-- Popup for duplicate quiz -->
		<div class="qsm-popup qsm-popup-slide" id="modal-4" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-4-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-4-title"><?php _e( 'Duplicate', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-4-content">
						<form action='' method='post' id="duplicate-quiz-form">
							<label for="duplicate_questions"><?php _e( 'Duplicate questions also?', 'quiz-master-next' ); ?></label><input type="checkbox" name="duplicate_questions" id="duplicate_questions"/><br />
							<br />
							<label for="duplicate_new_quiz_name"><?php _e( 'Name Of New Quiz Or Survey:', 'quiz-master-next' ); ?></label><input type="text" id="duplicate_new_quiz_name" name="duplicate_new_quiz_name" />
							<input type="hidden" id="duplicate_quiz_id" name="duplicate_quiz_id" />
							<?php wp_nonce_field( 'qsm_duplicate_quiz', 'qsm_duplicate_quiz_nonce' ); ?>
						</form>
					</main>
					<footer class="qsm-popup__footer">
						<button id="duplicate-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Duplicate', 'quiz-master-next'); ?></button>
						<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
					</footer>
				</div>
			</div>
		</div>

		<!-- Popup for delete quiz -->
		<div class="qsm-popup qsm-popup-slide" id="modal-5" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-5-title"><?php _e( 'Delete', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-5-content">
						<form action='' method='post' id="delete-quiz-form">
							<h3><b><?php _e( 'Are you sure you want to delete this quiz or survey?', 'quiz-master-next' ); ?></b></h3>
							<?php wp_nonce_field( 'qsm_delete_quiz', 'qsm_delete_quiz_nonce' ); ?>
							<input type='hidden' id='delete_quiz_id' name='delete_quiz_id' value='' />
							<input type='hidden' id='delete_quiz_name' name='delete_quiz_name' value='' />
						</form>
					</main>
					<footer class="qsm-popup__footer">
						<button id="delete-quiz-button" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Delete', 'quiz-master-next'); ?></button>
						<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
					</footer>
				</div>
			</div>
		</div>
                
                <!-- Popup for export import upsell -->
		<div class="qsm-popup qsm-popup-slide" id="modal-export-import" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-5-title"><?php _e( 'Extend QSM', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-5-content">						
                                            <h3><b><?php _e( 'Export functionality is provided as Premium addon.', 'quiz-master-next' ); ?></b></h3>													
					</main>
					<footer class="qsm-popup__footer">
                                            <a style="color: white;    text-decoration: none;" href="https://quizandsurveymaster.com/downloads/export-import/" target="_blank" class="qsm-popup__btn qsm-popup__btn-primary"><?php _e('Buy Now', 'quiz-master-next'); ?></a>
                                            <button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
					</footer>
				</div>
			</div>
		</div>

                <!-- Popup for delete quiz -->
		<div class="qsm-popup qsm-popup-slide" id="modal-6" aria-hidden="true">
			<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
				<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-5-title">
					<header class="qsm-popup__header">
						<h2 class="qsm-popup__title" id="modal-5-title"><?php _e( 'Shortcode', 'quiz-master-next' ); ?></h2>
						<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
					</header>
					<main class="qsm-popup__content" id="modal-5-content">
                                            <input type="text" value="" id="sc-shortcode-model-text" style="width: 72%;padding: 5px;">
                                            <button class="button button-primary" id="sc-copy-shortcode">Copy Shortcode</button>
					</main>
				</div>
			</div>
		</div>

		<!-- Templates -->
		<script type="text/template" id="tmpl-no-quiz">
			<h2><?php _e( 'You do not have any quizzes or surveys. Click "Add New" to get started.', 'quiz-master-next' ); ?></h2>
			<h2>Is this your first time using this plugin? Check out our <a href="https://docs.quizandsurveymaster.com" target="_blank">Documentation</a> or watch our Getting Started Video below</h2>
			<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/coE5W_WB-48" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</script>

		<script type="text/template" id="tmpl-quiz-row">
			<tr class="qsm-quiz-row" data-id="{{ data.id }}">
				<td class="post-title column-title">
					<a class="row-title" href="admin.php?page=mlw_quiz_options&&quiz_id={{ data.id }}" aria-label="{{ data.name }}">{{ data.name }} <b style="color: #222; text-transform: capitalize;">{{ data.post_status }}</b></a><a target="_blank" class="quiz-preview-link" href="{{ data.link }}"><span class="dashicons dashicons-external"></span></a>
					<div class="row-actions">
						<a class="qsm-action-link" href="admin.php?page=mlw_quiz_options&&quiz_id={{ data.id }}"><?php _e( 'Edit', 'quiz-master-next' ); ?></a> |
                                                <a class="qsm-action-link" href="post.php?post={{ data.postID }}&action=edit"><?php _e( 'Post Settings', 'quiz-master-next' ); ?></a> |
						<a class="qsm-action-link qsm-action-link-duplicate" href="#"><?php _e( 'Duplicate', 'quiz-master-next' ); ?></a> |
						<a class="qsm-action-link qsm-action-link-delete" href="#"><?php _e( 'Delete', 'quiz-master-next' ); ?></a> |
                                                <a class="qsm-action-link" target="_blank" href="{{ data.link }}"><?php _e( 'Preview', 'quiz-master-next' ); ?></a>
					</div>
				</td>
				<td>
                                        <p class="sc-opener"><span class="dashicons dashicons-editor-contract"></span> Embed</p>
                                        <div class="sc-content">[qsm quiz={{ data.id }}]</div>
                                        <p class="sc-opener"><span class="dashicons dashicons-admin-links"></span> Link</p>
                                        <div class="sc-content">[qsm_link id={{ data.id }}]<?php _e( 'Click here', 'quiz-master-next' ); ?>[/qsm_link]</div>
                                </td>
				<td>
					{{ data.views }}/{{ data.taken }}
					<div class="row-actions">
						<a class="qsm-action-link qsm-action-link-reset" href="#"><?php _e( 'Reset', 'quiz-master-next' ); ?></a> |
                                                <a class="qsm-action-link" href="admin.php?page=mlw_quiz_results&&quiz_id={{ data.id }}"><?php _e( 'Results', 'quiz-master-next' ); ?></a>
					</div>
				</td>
				<td><abbr title="{{ data.lastActivityDateTime }}">{{ data.lastActivity }}</abbr></td>
			</tr>
		</script>
	</div>
<?php
}
?>
