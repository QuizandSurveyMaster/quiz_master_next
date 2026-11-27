<?php
/**
 * Access control for File Upload question submissions.
 *
 * Files uploaded through a File Upload question are stored as media-library
 * attachments (see QSM_Question_Review_File_Upload::set_user_answer()). Because
 * those attachments have post_status "inherit" and no parent post, WordPress core
 * treats them as public and serves them through the media REST route, so an
 * unauthenticated visitor could list and download every quiz taker's upload
 * (for example ID scans) while QSM's own result screens keep that data to admins.
 *
 * This file restricts those uploads to users who can view quiz results:
 *  - they are removed from attachment listing queries (/wp/v2/media and the
 *    admin Media Library grid), and
 *  - their file URLs are stripped from single-item media REST responses
 *    (/wp/v2/media/<id>), which are otherwise reachable by guessing the id.
 *
 * Matching is by the fixed "qsmfileupload_" title prefix, so both new and
 * already-stored uploads are covered without a data migration.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current user is allowed to see quiz file uploads.
 *
 * Mirrors the capability gate QSM uses for its own result screens
 * (see QMNQuizManager, class-qmn-quiz-manager.php).
 *
 * @return bool
 */
function qsm_can_view_quiz_uploads() {
	return current_user_can( 'manage_options' ) || current_user_can( 'qsm_view_results' );
}

/**
 * Whether an attachment is a QSM File Upload submission.
 *
 * @param WP_Post|int $post Attachment post or id.
 * @return bool
 */
function qsm_is_quiz_upload( $post ) {
	$post = get_post( $post );
	return ( $post instanceof WP_Post ) && 0 === strpos( (string) $post->post_title, 'qsmfileupload_' );
}

/**
 * Exclude QSM uploads from the media REST listing (/wp/v2/media) for users who
 * cannot view quiz results.
 *
 * @param array           $args    WP_Query args built by the REST controller.
 * @param WP_REST_Request $request The REST request. Unused.
 * @return array
 */
function qsm_rest_attachment_query( $args, $request ) {
	if ( ! qsm_can_view_quiz_uploads() ) {
		add_filter( 'posts_where', 'qsm_exclude_quiz_uploads_where', 10, 2 );
	}
	return $args;
}
add_filter( 'rest_attachment_query', 'qsm_rest_attachment_query', 10, 2 );

/**
 * Exclude QSM uploads from the admin Media Library grid (wp.media ajax) for
 * users who cannot view quiz results.
 *
 * @param array $args WP_Query args.
 * @return array
 */
function qsm_ajax_attachment_args( $args ) {
	if ( ! qsm_can_view_quiz_uploads() ) {
		add_filter( 'posts_where', 'qsm_exclude_quiz_uploads_where', 10, 2 );
	}
	return $args;
}
add_filter( 'ajax_query_attachments_args', 'qsm_ajax_attachment_args' );

/**
 * WHERE clause that hides QSM uploads from an attachment query. Self-removes so
 * it only affects the single query it was armed for.
 *
 * @param string   $where The WHERE clause.
 * @param WP_Query $query The query. Unused.
 * @return string
 */
function qsm_exclude_quiz_uploads_where( $where, $query ) {
	global $wpdb;
	remove_filter( 'posts_where', 'qsm_exclude_quiz_uploads_where', 10 );
	$like   = $wpdb->esc_like( 'qsmfileupload_' ) . '%';
	$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title NOT LIKE %s", $like );
	return $where;
}

/**
 * Strip the file URL from a single-item media REST response for a QSM upload
 * when the current user cannot view quiz results. The listing query above
 * removes these from collections; this covers /wp/v2/media/<id>, which is
 * otherwise reachable by guessing the (sequential) attachment id.
 *
 * @param WP_REST_Response $response The response object.
 * @param WP_Post          $post     The attachment post.
 * @param WP_REST_Request  $request  The request. Unused.
 * @return WP_REST_Response
 */
function qsm_rest_prepare_attachment( $response, $post, $request ) {
	if ( qsm_is_quiz_upload( $post ) && ! qsm_can_view_quiz_uploads() ) {
		$data = $response->get_data();
		unset( $data['guid'], $data['source_url'], $data['media_details'] );
		if ( isset( $data['description']['rendered'] ) ) {
			$data['description']['rendered'] = '';
		}
		$response->set_data( $data );
	}
	return $response;
}
add_filter( 'rest_prepare_attachment', 'qsm_rest_prepare_attachment', 10, 3 );
