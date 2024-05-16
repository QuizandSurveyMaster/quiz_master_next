<?php
/**
 * Creates the failed submission page within the admin area
 *
 * @package QSM
 * @since 9.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'QmnFailedSubmissions' ) && class_exists( 'WP_List_Table' ) ) {
	/**
	 * This class display failed submission list table
	 *
	 * @since 9.0.2
	 */
	class QmnFailedSubmissions extends WP_List_Table {

		/**
		 * Holds meta_key name
		 *
		 * @var object
		 * @since 9.0.2
		 */
		public $meta_key = '_qmn_log_result_insert_data';

		/**
		 * Holds table_data
		 *
		 * @var object
		 * @since 9.0.2
		 */
		private $table_data = array();

		public function __construct() {
			parent::__construct(
				array(
					'plural'   => 'submissions',
					'singular' => 'submission',
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {
			// QMN Error log.
			$posts = get_posts(
				array(
					'post_type'      => 'qmn_log',
					'meta_key'       => $this->meta_key,
					'fields'         => 'ids',
					'posts_per_page' => -1,
				)
			);

			$posts    = empty( $posts ) ? array() : $posts;
			$per_page = 20;
			// Total rows.
			$this->total_rows = count( $posts );
			if ( ! empty( $posts ) ) {
				$current_page       = intval( $this->get_pagenum() ) - 1;
				$post_start_postion = $per_page * $current_page;

				foreach ( $posts as $index => $postID ) {
					if ( $post_start_postion > $index || $index > ( $post_start_postion + $per_page ) ) {
						continue;
					}
					$data = get_post_meta( $postID, $this->meta_key, true );
					if ( empty( $data ) ) {
						continue;
					}
					$data = maybe_unserialize( $data );
					if ( ! is_array( $data ) || ! empty( $data['deleted'] ) || empty( $data['qmn_array_for_variables'] ) ) {
						continue;
					}
					$data['qmn_array_for_variables']['post_id'] = $postID;
					$this->table_data[]                         = $data['qmn_array_for_variables'];
				}
			}

			// pagination.
			$this->set_pagination_args(
				array(
					'total_items' => $this->total_rows,
					'per_page'    => $per_page,
				)
			);

			// table data.
			$this->items = $this->table_data;

			// table headers.
			$this->_column_headers = array(
				$this->get_columns(),
				$this->get_hidden_columns(),
			);
		}

		/**
		 * Gets a list of columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'cb'                => '<input type="checkbox" />',
				'post_id'           => __( 'ID', 'quiz-master-next' ),
				'quiz_name'         => __( 'Quiz Name', 'quiz-master-next' ),
				'user_name'         => __( 'Name', 'quiz-master-next' ),
				'user_email'        => __( 'Email', 'quiz-master-next' ),
				'submission_action' => __( 'Action', 'quiz-master-next' ),
			);
		}

		/**
		 * Gets a list of hidden columns.
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array(
				'post_id',
			);
		}

		public function column_cb( $submission ) {
			return sprintf(
				'<input type="checkbox" name="post_id[]" value="%d" /> ',
				$submission['post_id']
			);
		}

		public function column_default( $submission, $column_name ) {
			$column_value = '';
			switch ( $column_name ) {
				case 'post_id':
					$column_value = $submission['post_id'];
					break;
				case 'quiz_name':
					$column_value = $submission['quiz_name'];
					break;
				case 'user_name':
					$column_value = $submission['user_name'];
					break;
				case 'user_email':
					$column_value = $submission['user_email'];
					break;
				case 'submission_action':
					$column_value = '<a href="' . esc_url( admin_url( 'admin.php?action=retrieve&post_id=' . esc_attr( $submission['post_id'] ) . '&qmnnonce=' . wp_create_nonce( 'qmn_failed_submission' ) ) ) . '" class="qmn-delete-failed-submission" >' . __( 'Retrieve', 'quiz-master-next' ) . '</a>';
					break;
				default:
					break;
			}

			return $column_value;
		}

		public function get_bulk_actions() {
			return array(
				'retrieve' => __( 'Retrieve', 'quiz-master-next' ),
				'trash'    => __( 'Delete', 'quiz-master-next' ),
			);
		}

		/**
		 * Render page with this table
		 *
		 * @param class $store
		 */
		public function render_list_table() {

			$this->prepare_items();
			// header.
			echo '<div class="qmn-failed-submission wrap">';
			// heading.
			echo '<h2 id="result_details" >' . esc_html__( 'Failed Submissions', 'quiz-master-next' ) . '</h2>';
			// body.
			echo '<div class="qmn-body">';
			echo "<form method='post' name='search_form' action='" . esc_url( admin_url( 'admin.php?page=mlw_quiz_failed_submission' ) ) . "'>";
			echo '<div class="submission-filter-wrapper" >';
			$this->views();
			echo '</div>';
			echo '<input type="hidden" name="qmnnonce" value="' . wp_create_nonce( 'qmn_failed_submission' ) . '" />';
			$this->display();
			echo '</form>';
			echo '</div>';
			echo '</div>';
		}
	}
}
